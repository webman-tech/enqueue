<?php

namespace WebmanTech\Enqueue\Client;

use Enqueue\AmqpTools\DelayStrategyAware;
use Enqueue\AmqpTools\RabbitMqDlxDelayStrategy;
use Illuminate\Contracts\Events\Dispatcher;
use Interop\Amqp\AmqpMessage;
use Interop\Queue\Context;
use Interop\Queue\Destination;
use Interop\Queue\Exception\DeliveryDelayNotSupportedException;
use Interop\Queue\Exception\PriorityNotSupportedException;
use Interop\Queue\Exception\TimeToLiveNotSupportedException;
use Interop\Queue\Message;
use Interop\Queue\Producer;
use InvalidArgumentException;
use WebmanTech\Enqueue\Enums\MessagePropertyEnum;
use WebmanTech\Enqueue\Events\ProducerAbilityNotSupportTriggerEvent;
use WebmanTech\Enqueue\Events\ProducerSendAfterEvent;
use WebmanTech\Enqueue\Events\ProducerSendBeforeEvent;
use WebmanTech\Enqueue\Job\JobProducerInterface;

class ProducerClient extends BaseClient
{
    protected array $config = [
        'default_retry' => 0, // 默认重试次数，重试不占用首次执行次数
        'default_retry_delay' => 0, // 默认重试延迟
        'default_priority' => null, // 默认优先级
    ];

    private ?float $delay = null;

    /**
     * 延迟
     * @param float $second
     * @return $this
     */
    public function delay(float $second): self
    {
        $this->delay = $second;
        return $this;
    }

    private ?int $priority = null;

    /**
     * 优先级
     * 值大优先消费
     * rabbitMQ 需要是优先级队列
     * @param int $priority
     * @return $this
     */
    public function priority(int $priority): self
    {
        $this->priority = $priority;
        return $this;
    }

    private ?float $timeToLive = null;

    /**
     * N 秒内有效
     * @param float $second
     * @return $this
     */
    public function timeToLive(float $second): self
    {
        if ($second < 0) {
            return $this;
        }
        $this->timeToLive = $second;
        return $this;
    }

    /**
     * 在某个时间以内有效
     * @param int $timestamp 时间戳
     * @return $this
     */
    public function expiredAt(int $timestamp): self
    {
        $timeToLive = $timestamp - time();
        if ($timeToLive < 0) {
            throw new InvalidArgumentException('timestamp must larger than current time');
        }
        return $this->timeToLive($timeToLive);
    }

    private ?int $retry = null;
    private ?float $retryDelay = null;

    /**
     * 最大重试次数
     * @param int|null $count
     * @param float|null $delay
     * @return $this
     */
    public function retry(?int $count = null, ?float $delay = null): self
    {
        $this->retry = $count;
        $this->retryDelay = $delay;
        return $this;
    }

    /**
     * 发送消息
     * @param string|Message|JobProducerInterface $message
     * @return void
     */
    public function send($message)
    {
        $producer = $this->context->createProducer();

        $message = $this->createMessage($message);
        $this->configMessage($producer, $message);

        $this->events->dispatch(new ProducerSendBeforeEvent($producer, $message));

        $producer->send($this->destination, $message);

        $this->events->dispatch(new ProducerSendAfterEvent($producer, $message));
    }

    /**
     * 创建消息
     * @param string|Message|JobProducerInterface $message
     * @return Message
     */
    protected function createMessage($message): Message
    {
        if ($message instanceof JobProducerInterface) {
            $message->setProducerContext($this->context);
            $message = $message->getMessageForProducer();
        }
        if ($message instanceof Message) {
            return $message;
        }
        if (is_string($message)) {
            return $this->context->createMessage($message);
        }
        // 不对 array 等类型做支持，因为后续可能存在与其他系统的对接，序列化或json化php的数组将导致前后不一致，因此如果需要，请手动转成json
        throw new InvalidArgumentException('message only support string type');
    }

    /**
     * 配置消息
     * @param Producer $producer
     * @param Message $message
     * @return void
     */
    protected function configMessage(Producer $producer, Message $message)
    {
        // delay
        $delay = $this->delay ?? $message->getProperty(MessagePropertyEnum::DELAY);
        if ($delay !== null && $delay > 0) {
            $message->setProperty(MessagePropertyEnum::DELAY, $delay);
            try {
                if ($producer instanceof DelayStrategyAware) {
                    $producer->setDelayStrategy(new RabbitMqDlxDelayStrategy());
                }
                $producer->setDeliveryDelay($delay * 1000);
            } catch (DeliveryDelayNotSupportedException $e) {
                $this->events->dispatch(new ProducerAbilityNotSupportTriggerEvent($producer, $e));
            }
        }

        // priority
        $priority = $this->priority ?? $message->getProperty(MessagePropertyEnum::PRIORITY) ?? $this->config['default_priority'];
        if ($priority !== null) {
            $message->setProperty(MessagePropertyEnum::PRIORITY, $priority);
            try {
                $producer->setPriority($priority);
            } catch (PriorityNotSupportedException $e) {
                $this->events->dispatch(new ProducerAbilityNotSupportTriggerEvent($producer, $e));
            }
        }

        // timeToLive
        $timeToLive = $this->timeToLive ?? $message->getProperty(MessagePropertyEnum::TIME_TO_LIVE);
        if ($timeToLive !== null && $timeToLive > 0) {
            $message->setProperty(MessagePropertyEnum::TIME_TO_LIVE, $timeToLive);
            try {
                $producer->setTimeToLive($timeToLive * 1000);
            } catch (TimeToLiveNotSupportedException $e) {
                $this->events->dispatch(new ProducerAbilityNotSupportTriggerEvent($producer, $e));
            }
        }

        // retry
        $retry = $this->retry ?? $message->getProperty(MessagePropertyEnum::RETRY) ?? $this->config['default_retry'];
        if ($retry !== null && $retry > 0) {
            $message->setProperty(MessagePropertyEnum::RETRY, $retry);
        }

        // retry delay
        $retryDelay = $this->retryDelay ?? $message->getProperty(MessagePropertyEnum::RETRY_DELAY) ?? $this->config['default_retry_delay'];
        if ($retryDelay !== null && $retryDelay >= 0) {
            $message->setProperty(MessagePropertyEnum::RETRY_DELAY, $retryDelay);
        }
    }
}
