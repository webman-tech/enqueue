<?php

namespace WebmanTech\Enqueue\Exceptions;

use Interop\Queue\Message;
use RuntimeException;
use Throwable;
use WebmanTech\Enqueue\Enums\MessageAckEnum;

class MessageRepeatConsumeException extends RuntimeException implements AckSupportExceptionInterface
{
    private Message $queueMessage;

    public function __construct(Message $queueMessage, $message = "Message Repeat Consume", $code = 0, Throwable $previous = null)
    {
        $this->queueMessage = $queueMessage;

        parent::__construct($message, $code, $previous);
    }

    public function getQueueMessage(): Message
    {
        return $this->queueMessage;
    }

    /**
     * @inheritDoc
     */
    public function getAck(): string
    {
        return MessageAckEnum::REJECT;
    }
}