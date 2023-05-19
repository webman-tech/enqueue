<?php

namespace WebmanTech\Enqueue\Job;

use Interop\Queue\Message;
use WebmanTech\Enqueue\Exceptions\ConsumeFailedException;

/**
 * 仅处理消息的任务，不能用于发送
 * 一般用于与其他系统对接，那边发送，这边处理
 */
abstract class BaseConsumerJob implements JobConsumerInterface
{
    use JobConsumerTrait;

    final public function __construct()
    {
        // 初始化不支持传参
    }
}
