<?php

namespace WebmanTech\Enqueue\Enums;

final class MessagePropertyEnum
{
    // 生产相关
    public const DELAY = 'enqueue.delay';
    public const PRIORITY = 'enqueue.priority';
    public const TIME_TO_LIVE = 'enqueue.timeToLive';
    // 消费相关
    public const RETRY = 'enqueue.retry';
    public const RETRY_DELAY = 'enqueue.retryDelay';
    public const ALREADY_RETRY = 'enqueue.alreadyRetry';
    // 生产消费关联相关
    public const JOB = 'enqueue.job.class';
}
