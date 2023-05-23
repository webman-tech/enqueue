<?php

namespace WebmanTech\Enqueue\Job;

use WebmanTech\Enqueue\Job\Traits\LogTrait;

/**
 * 仅处理消息的任务，不能用于发送
 * 一般用于与其他系统对接，那边发送，这边处理
 */
abstract class BaseConsumerJob implements JobConsumerInterface
{
    use JobConsumerTrait;
    use LogTrait;

    final public function __construct()
    {
        // 初始化不支持传参
    }
}
