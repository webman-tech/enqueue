<?php

namespace WebmanTech\Enqueue\Exceptions;

use Interop\Queue\Message;
use RuntimeException;
use WebmanTech\Enqueue\Enums\MessageAckEnum;

class NotSupportMessageException extends RuntimeException implements AckSupportExceptionInterface
{
    private Message $queueMessage;

    public function __construct(Message $message)
    {
        $this->queueMessage = $message;

        parent::__construct('This Message is not Support');
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
