<?php

namespace WebmanTech\Enqueue\Job;

use Interop\Queue\Context;
use Interop\Queue\Message;
use WebmanTech\Enqueue\Enums\MessagePropertyEnum;

trait JobProducerTrait
{
    protected Context $producerContext;
    /**
     * 延迟时间，单位秒，为0时表示不延迟
     * @var float
     */
    protected float $delay = 0;
    /**
     * 优先级，为 null 表示以全局配置的为准
     * @var int|null
     */
    protected ?int $priority = null;
    /**
     * 任务在 N 秒内可以被接收处理，为 0 表示一直有效
     * @var float
     */
    protected float $timeToLive = 0;

    /**
     * @inheritDoc
     */
    public function setProducerContext(Context $context): void
    {
        $this->producerContext = $context;
    }

    /**
     * 消息内容
     * @return string
     */
    abstract protected function messageBody(): string;

    /**
     * 消息属性
     * @return array
     */
    protected function messageProperties(): array
    {
        return [];
    }

    /**
     * 消息头信息
     * @return array
     */
    protected function messageHeaders(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getMessageForProducer(): Message
    {
        $properties = [];

        // delay
        $value = $this->getDefaultDelay();
        if ($value > 0) {
            $properties[MessagePropertyEnum::DELAY] = $value;
        }
        // priority
        $value = $this->getDefaultPriority();
        if ($value !== null) {
            $properties[MessagePropertyEnum::PRIORITY] = $value;
        }
        // timeToLive
        $value = $this->getDefaultTimeToLive();
        if ($value > 0) {
            $properties[MessagePropertyEnum::TIME_TO_LIVE] = $value;
        }

        $isConsumerLinked = $this instanceof JobProducerConsumerLinkInterface;
        if ($isConsumerLinked) {
            // job
            $properties[MessagePropertyEnum::JOB] = $this->getJobConsumerClass();
            // retry
            $value = $this->getDefaultRetry();
            if ($value !== null && $value >= 0) {
                $properties[MessagePropertyEnum::RETRY] = $value;
            }
            // retryDelay
            $value = $this->getDefaultRetryDelay();
            if ($value !== null && $value >= 0) {
                $properties[MessagePropertyEnum::RETRY_DELAY] = $value;
            }
        }

        $properties = array_merge($properties, $this->messageProperties());

        $message = $this->producerContext->createMessage(
            $this->messageBody(),
            $properties,
            $this->messageHeaders()
        );
        if ($isConsumerLinked) {
            $message->setMessageId($this->getMessageId());
            $message->setTimestamp(time());
        }

        return $message;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultDelay(): float
    {
        return $this->delay;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultPriority(): ?int
    {
        return $this->priority;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultTimeToLive(): float
    {
        return $this->timeToLive;
    }
}
