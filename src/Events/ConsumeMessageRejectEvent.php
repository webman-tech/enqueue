<?php

namespace WebmanTech\Enqueue\Events;

use Interop\Queue\Consumer;
use Interop\Queue\Message;

class ConsumeMessageRejectEvent
{
    public Consumer $consumer;
    public Message $message;

    public function __construct(Consumer $consumer, Message $message)
    {
        $this->consumer = $consumer;
        $this->message = $message;
    }
}
