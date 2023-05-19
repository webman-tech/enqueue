<?php

namespace WebmanTech\Enqueue\Exceptions;

use Interop\Queue\Message;
use Throwable;
use WebmanTech\Enqueue\Enums\MessageAckEnum;

class ConsumeFailedException extends \RuntimeException
{
    /**
     * @var Message
     */
    private Message $queueMessage;
    /**
     * @var string|null
     */
    private ?string $ack = null;
    /**
     * @var Throwable
     */
    private Throwable $exception;

    public function __construct(Message $message, Throwable $exception)
    {
        $this->queueMessage = $message;
        $this->exception = $exception;

        $error = $exception->getMessage();
        parent::__construct("Failed consume message {$message->getMessageId()} with Exception: {$error}", 0, $exception);
    }

    public function getQueueMessage(): Message
    {
        return $this->queueMessage;
    }

    public function getOriginException(): Throwable
    {
        return $this->exception;
    }

    public function setAck(string $ack)
    {
        $this->ack = $ack;
    }

    public function getAck(): string
    {
        if ($this->ack) {
            return $this->ack;
        }
        $previous = $this->getPrevious();
        if ($previous instanceof AckSupportExceptionInterface) {
            return $previous->getAck();
        }

        return MessageAckEnum::REQUEUE;
    }
}
