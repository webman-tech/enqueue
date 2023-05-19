<?php

namespace WebmanTech\Enqueue\Events;

use Interop\Queue\Message;
use WebmanTech\Enqueue\Exceptions\ConsumeFailedException;

class MessageConsumeRejectEvent
{
    public Message $message;
    public bool $requeue;
    public ?ConsumeFailedException $consumeFailedException;

    public function __construct(Message $message, bool $requeue, ?ConsumeFailedException $consumeFailedException)
    {
        $this->message = $message;
        $this->requeue = $requeue;
        $this->consumeFailedException = $consumeFailedException;
    }
}