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
}
