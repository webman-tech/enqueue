<?php

namespace WebmanTech\Enqueue\Job;

use Interop\Amqp\AmqpMessage;
use Interop\Queue\Context;
use Interop\Queue\Message;
use WebmanTech\Enqueue\Enums\MessagePropertyEnum;

trait JobProducerTrait
{
    protected Context $producerContext;

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
     * @return array
     */
    protected function messageProperties(): array
    {
        return [];
    }

    /**
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
        $messageId = null;
        if ($this instanceof JobProducerConsumerLinkInterface) {
            $messageId = $this->getMessageId();
            // job
            $properties[MessagePropertyEnum::JOB] = $this->getJobConsumerClass();
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
        if (!$messageId) {
            $messageId = uniqid('auto', true);
        }

        $message = $this->producerContext->createMessage(
            $this->messageBody(),
            $properties,
            $this->messageHeaders()
        );
        $message->setMessageId($messageId);
        $message->setTimestamp(time());

        return $message;
    }
}
