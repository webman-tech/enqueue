<?php

namespace WebmanTech\Enqueue\Enums;

final class MessagePropertyEnum
{
    public const JOB = 'enqueue.job.class';
    public const RETRY = 'enqueue.retry';
    public const DELAY = 'enqueue.delay';
    public const RETRY_DELAY = 'enqueue.retryDelay';
    public const PRIORITY = 'enqueue.priority';
    public const TIME_TO_LIVE = 'enqueue.timeToLive';

    public const ALREADY_RETRY = 'enqueue.alreadyRetry';
}
