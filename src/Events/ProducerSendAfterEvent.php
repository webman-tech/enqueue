<?php

namespace WebmanTech\Enqueue\Events;

use Interop\Queue\Message;
use Interop\Queue\Producer;

class ProducerSendAfterEvent
{
    public Producer $producer;
    public Message $message;

    public function __construct(Producer $producer, Message $message)
    {
        $this->producer = $producer;
        $this->message = $message;
    }
}
