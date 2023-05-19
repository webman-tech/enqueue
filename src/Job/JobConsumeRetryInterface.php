<?php

namespace WebmanTech\Enqueue\Job;

use Interop\Queue\Message;
use WebmanTech\Enqueue\Exceptions\ConsumeFailedException;

interface JobConsumeRetryInterface
{
    /**
     * 是否需要重试
     * @param Message $message
     * @param ConsumeFailedException|null $consumeFailedException
     * @return bool
     */
    public function shouldRetry(Message $message, ?ConsumeFailedException $consumeFailedException): bool;

    /**
     * 重试延迟时间，单位：秒
     * @param Message $message
     * @param ConsumeFailedException|null $consumeFailedException
     * @return float
     */
    public function retryDelay(Message $message, ?ConsumeFailedException $consumeFailedException): float;
}