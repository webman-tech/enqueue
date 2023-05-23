<?php

namespace WebmanTech\Enqueue\Job;

use WebmanTech\Enqueue\Job\Traits\LogTrait;

/**
 * 基础任务，既用于发送，也用于消费
 */
abstract class BaseJob implements JobProducerConsumerLinkInterface
{
    use JobProducerTrait;
    use JobConsumerTrait;
    use LogTrait;


    final public function __construct()
    {
        // 初始化不支持传参
    }

    /**
     * @inheritDoc
     */
    public function getJobConsumerClass(): string
    {
        return static::class;
    }

    /**
     * @inheritDoc
     */
    public function getMessageId(): string
    {
        return uniqid(basename($this->getJobConsumerClass()), true);
    }
}
