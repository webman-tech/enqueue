<?php

namespace WebmanTech\Enqueue\Client;

use Interop\Queue\Consumer;
use Interop\Queue\Message;
use Interop\Queue\SubscriptionConsumer;
use RuntimeException;
use Throwable;
use WebmanTech\Enqueue\Client\Traits\ConsumerJobMaxExecTimeSupport;
use WebmanTech\Enqueue\Client\Traits\ConsumerJobMaxMemoryUseSupport;
use WebmanTech\Enqueue\Client\Traits\MessageNotSupportSupport;
use WebmanTech\Enqueue\Client\Traits\MessageRepeatConsumeSupport;
use WebmanTech\Enqueue\Enums\MessageAckEnum;
use WebmanTech\Enqueue\Enums\MessagePropertyEnum;
use WebmanTech\Enqueue\Events\ConsumerConsumeAfterEvent;
use WebmanTech\Enqueue\Events\ConsumerConsumeBeforeEvent;
use WebmanTech\Enqueue\Events\MessageConsumeAcknowledgeEvent;
use WebmanTech\Enqueue\Events\MessageConsumeRejectEvent;
use WebmanTech\Enqueue\Exceptions\ConsumeFailedException;
use WebmanTech\Enqueue\Exceptions\NotSupportMessageException;
use WebmanTech\Enqueue\Job\JobConsumeFailedInterface;
use WebmanTech\Enqueue\Job\JobConsumeRetryInterface;
use WebmanTech\Enqueue\Job\JobConsumerInterface;

class ConsumerClient extends BaseClient
{
    use ConsumerJobMaxMemoryUseSupport;
    use ConsumerJobMaxExecTimeSupport;
    use MessageNotSupportSupport;
    use MessageRepeatConsumeSupport;

    protected array $config = [
        'default_retry' => 0, // 默认重试次数
        'max_exec_time' => -1, // 最大执行时长，单位秒，<=0 为不限制
        'max_memory_use' => -1, // 最大内存使用量，单位 M，-1 表示不限制，0 为不限制
        /**
         * 不支持消息处理器
         * @see MessageNotSupportSupport::getNotSupportMessageSolveHandler()
         */
        'not_support_message_solver' => null,
        /**
         * 消息重复消费的处理器
         * @see MessageRepeatConsumeSupport::getMessageRepeatConsumerHandler()
         */
        'message_repeat_consume_handler' => null,
        /**
         * 消息重复消费时用于界定的 key
         * @see MessageRepeatConsumeSupport::getMessageRepeatConsumerKey()
         */
        'message_repeat_consume_key' => null,
    ];

    /**
     * @return Consumer
     */
    private function createConsumer(): Consumer
    {
        return $this->context->createConsumer($this->destination);
    }

    /**
     * @return SubscriptionConsumer
     * @throws \Interop\Queue\Exception\SubscriptionConsumerNotSupportedException
     */
    private function createSubscriptionConsumer(): SubscriptionConsumer
    {
        return $this->context->createSubscriptionConsumer();
    }

    /**
     * 消费所有已经存在的消息并退出
     * @params int $max
     * @return int 处理完成的消息数
     */
    public function consume(int $max = 0): int
    {
        $consumer = $this->createConsumer();

        $count = 0;
        while (true) {
            if ($max > 0 && $max <= $count) {
                break;
            }
            $message = $consumer->receiveNoWait();
            if (!$message) {
                break;
            }
            $this->handle($message, $consumer);
            $count++;
        }

        return $count;
    }

    /**
     * 监听并处理消息
     * @throws \Interop\Queue\Exception\SubscriptionConsumerNotSupportedException
     */
    public function listen(): void
    {
        $subscriptionConsumer = $this->createSubscriptionConsumer();
        $consumer = $this->createConsumer();

        $subscriptionConsumer->subscribe($consumer, function (Message $message, Consumer $consumer) {
            $this->handle($message, $consumer);

            return true;
        });
    }

    /**
     * 消费消息处理总流程
     * @param Message $message
     * @param Consumer $consumer
     * @return void
     */
    protected function handle(Message $message, Consumer $consumer)
    {
        $afterEvent = new ConsumerConsumeAfterEvent($consumer, $message);
        try {
            $this->events->dispatch(new ConsumerConsumeBeforeEvent($consumer, $message));
            $this->checkMessageRepeatAndMark($message);

            $ack = $this->jobHandle($message);
            $afterEvent->setAck($ack);
        } catch (Throwable $e) {
            $failedException = new ConsumeFailedException($message, $e);
            $afterEvent->setException($failedException);
            $ack = null;
        }

        $this->events->dispatch($afterEvent);
        if ($ack === null && $afterEvent->exception) {
            $ack = $afterEvent->exception->getAck();
        }

        switch ($ack) {
            case MessageAckEnum::ACK:
                $this->events->dispatch(new MessageConsumeAcknowledgeEvent($message));
                $consumer->acknowledge($message);
                break;
            case MessageAckEnum::REJECT:
                $this->reject($message, $consumer, false, $afterEvent->exception);
                break;
            case MessageAckEnum::REQUEUE:
                $this->redeliver($message, $consumer, $afterEvent->exception);
                break;
            default:
                throw new RuntimeException('ack value error: ' . $ack);
        }
    }

    /**
     * Job 处理流程
     * @param Message $message
     * @return bool|string|null
     */
    protected function jobHandle(Message $message)
    {
        $this->checkMessageJobSupportAndSolve($message);

        $job = $this->tryTransferToJob($message);
        if (!$job instanceof JobConsumerInterface) {
            throw new NotSupportMessageException($message);
        }

        $this->setJobMaxMemoryUse($job->getMaxMemoryUse() ?? $this->config['max_memory_use']);
        $this->setJobMaxExecTime($job->getMaxExecTime() ?? $this->config['max_exec_time']);

        $result = $job->handle($message); // 处理业务

        $this->resetJobMaxMemoryUse();
        $this->resetJobMaxExecTime();

        // result -> ack
        if ($result === true || $result === null) {
            $ack = MessageAckEnum::ACK;
        } elseif ($result === false) {
            $ack = MessageAckEnum::REQUEUE;
        } elseif (in_array($result, [MessageAckEnum::ACK, MessageAckEnum::REQUEUE, MessageAckEnum::REJECT])) {
            $ack = $result;
        } else {
            throw new RuntimeException('job handle result error: ' . $result);
        }

        return $ack;
    }

    /**
     * 消息处理失败
     * @param Message $message
     * @param Consumer $consumer
     * @param bool $requeue
     * @param ConsumeFailedException|null $consumeFailedException
     * @return void
     */
    protected function reject(Message $message, Consumer $consumer, bool $requeue, ?ConsumeFailedException $consumeFailedException)
    {
        $job = $this->tryTransferToJob($message);
        if ($job instanceof JobConsumeFailedInterface) {
            try {
                $job->handleConsumeFailed($message, $requeue, $consumeFailedException);
            } catch (Throwable $e) {
                // 忽略异常
            }
        }

        $this->events->dispatch(new MessageConsumeRejectEvent($message, $requeue, $consumeFailedException));
        $consumer->reject($message, $requeue);
    }

    /**
     * 重试
     * @param Message $message
     * @param Consumer $consumer
     * @param ConsumeFailedException|null $consumeFailedException
     * @return void
     */
    protected function redeliver(Message $message, Consumer $consumer, ?ConsumeFailedException $consumeFailedException)
    {
        $requeue = false;

        $job = $this->tryTransferToJob($message);
        if ($job instanceof JobConsumeRetryInterface) {
            // 设置默认的重试次数
            $retryCount = $message->getProperty(MessagePropertyEnum::RETRY);
            if ($retryCount === null && $this->config['default_retry'] > 0) {
                $message->setProperty(MessagePropertyEnum::RETRY, $this->config['default_retry']);
            }
            // 允许重试
            if ($job->shouldRetry($message, $consumeFailedException)) {
                // 设置重试延迟
                $delay = $job->retryDelay($message, $consumeFailedException) * 1000;
                if (method_exists($message, 'setDeliveryDelay')) {
                    $message->setDeliveryDelay($delay);
                } else {
                    // 当 transport 不支持时，使用 sleep 模拟延迟
                    usleep($delay * 1000);
                }
                $alreadyRetryCount = (int)$message->getProperty(MessagePropertyEnum::ALREADY_RETRY, 0);
                $message->setProperty(MessagePropertyEnum::ALREADY_RETRY, $alreadyRetryCount + 1); // 增加已重试次数

                $requeue = true;
            }
        }

        if ($requeue) {
            $this->markMessageCanReconsume($message);
        }

        $this->reject($message, $consumer, $requeue, $consumeFailedException);
    }

    /**
     * 从 message 创建出 job
     * @param Message $message
     * @return mixed|null|JobConsumerInterface
     */
    protected function tryTransferToJob(Message $message)
    {
        $job = null;
        if ($jobClass = $message->getProperty(MessagePropertyEnum::JOB)) {
            $job = new $jobClass();
        }
        return $job;
    }
}
