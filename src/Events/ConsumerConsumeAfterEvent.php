<?php

namespace WebmanTech\Enqueue\Events;

use Interop\Queue\Consumer;
use Interop\Queue\Message;
use WebmanTech\Enqueue\Exceptions\ConsumeFailedException;

class ConsumerConsumeAfterEvent
{
    public Consumer $consumer;
    public Message $message;
    public ?string $ack = null;
    public ?ConsumeFailedException $exception = null;

    public function __construct(Consumer $consumer, Message $message)
    {
        $this->consumer = $consumer;
        $this->message = $message;
    }

    public function setAck(string $ack)
    {
        $this->ack = $ack;
    }

    public function setException(ConsumeFailedException $e)
    {
        $this->exception = $e;
    }

    public function isSuccess(): bool
    {
        return $this->exception === null;
    }
}
