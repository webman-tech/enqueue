<?php

namespace WebmanTech\Enqueue\Client\ConsumerClient;

use Interop\Queue\Message;

interface NotSupportMessageSolverInterface
{
    /**
     * 处理不支持的消息
     * 必须在 $message 中 $message->setProperty(MessagePropertyEnum::JOB, XxxJob::class)
     * @param Message $message
     * @return Message
     */
    public function solve(Message $message): Message;
}