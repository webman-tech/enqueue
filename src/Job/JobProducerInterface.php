<?php

namespace WebmanTech\Enqueue\Job;

use Interop\Queue\Context;
use Interop\Queue\Message;

interface JobProducerInterface
{
    /**
     * @param Context $context
     */
    public function setProducerContext(Context $context): void;

    /**
     * @return Message
     */
    public function getMessageForProducer(): Message;

    /**
     * 该消息默认的延迟，单位秒
     * @return float
     */
    public function getDefaultDelay(): float;

    /**
     * 该消息默认的权重
     * @return int|null
     */
    public function getDefaultPriority(): ?int;

    /**
     * 该消息默认的有效期，单位秒
     * @return float
     */
    public function getDefaultTimeToLive(): float;
}
