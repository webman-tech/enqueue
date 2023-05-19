<?php

namespace WebmanTech\Enqueue\Events;

use Interop\Queue\Message;

class MessageConsumeAcknowledgeEvent
{
    public Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }
}