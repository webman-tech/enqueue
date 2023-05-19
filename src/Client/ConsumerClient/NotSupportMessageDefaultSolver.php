<?php

namespace WebmanTech\Enqueue\Client\ConsumerClient;

use Interop\Queue\Message;
use WebmanTech\Enqueue\Enums\MessagePropertyEnum;

class NotSupportMessageDefaultSolver implements NotSupportMessageSolverInterface
{
    /**
     * @inheritDoc
     */
    public function solve(Message $message): Message
    {
        $message->setProperty(MessagePropertyEnum::JOB, NotSupportMessageJob::class);
        return $message;
    }
}
