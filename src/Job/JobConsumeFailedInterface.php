<?php

namespace WebmanTech\Enqueue\Job;

use Interop\Queue\Message;
use WebmanTech\Enqueue\Exceptions\ConsumeFailedException;

interface JobConsumeFailedInterface
{
    /**
     * 处理消费失败
     * 一般用于记录日志等，切勿执行耗时的操作
     * 此方法如果抛出异常，程序会忽略，继续执行，请自行处理该方法中的异常
     * @param Message $message
     * @param bool $requeue 是否会重试
     * @param ConsumeFailedException|null $consumeFailedException 本次执行失败的异常
     * @return void
     */
    public function handleConsumeFailed(Message $message, bool $requeue, ?ConsumeFailedException $consumeFailedException): void;
}
