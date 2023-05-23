<?php

namespace WebmanTech\Enqueue\Events;

use Interop\Queue\Consumer;
use Interop\Queue\Message;
use WebmanTech\Enqueue\Exceptions\ConsumeFailedException;
use WebmanTech\Enqueue\Job\JobConsumerInterface;

class ConsumerConsumeAfterEvent
{
    private Consumer $consumer;
    private Message $message;
    private ?JobConsumerInterface $job = null;
    private ?string $ack = null;
    private ?ConsumeFailedException $exception = null;

    public function __construct(Consumer $consumer, Message $message)
    {
        $this->consumer = $consumer;
        $this->message = $message;
    }

    public function setJob(JobConsumerInterface $job)
    {
        $this->job = $job;
    }

    public function setAck(string $ack)
    {
        $this->ack = $ack;
    }

    public function setException(ConsumeFailedException $e)
    {
        $this->exception = $e;
    }

    public function getConsumer(): Consumer
    {
        return $this->consumer;
    }

    public function getMessage(): Message
    {
        return $this->message;
    }

    public function getJob(): ?JobConsumerInterface
    {
        return $this->job;
    }

    public function getAck(): ?string
    {
        return $this->ack;
    }

    public function getException(): ?ConsumeFailedException
    {
        return $this->exception;
    }

    public function isSuccess(): bool
    {
        return $this->exception === null;
    }
}
