<?php

namespace WebmanTech\Enqueue\Job;

use WebmanTech\Enqueue\Job\Traits\LogTrait;

/**
 * 基础任务，既用于发送，也用于消费
 */
abstract class BaseJob implements JobProducerInterface, JobConsumerInterface, JobProducerConsumerLinkInterface
{
    use JobProducerTrait;
    use JobConsumerTrait;
    use LogTrait;

    /**
     * 延迟时间，单位秒，为0时表示不延迟
     * @var float
     */
    protected float $delay = 0;
    /**
     * 优先级，为 null 表示以全局配置的为准
     * @var int|null
     */
    protected ?int $priority = null;
    /**
     * 任务在 N 秒内可以被接收处理，为 0 表示一直有效
     * @var float
     */
    protected float $timeToLive = 0;
    /**
     * 最大重试次数
     * 为 null 时以全局配置为准
     * 重试不占用首次执行次数
     * 此配置仅针对 producer 时生效，仅消费模式下该值无效
     * @var int|null
     */
    protected ?int $retry = null;
    /**
     * 重试延迟，单位秒
     * 为 null 时以全局配置为准
     * @var float|null
     */
    protected ?float $retryDelay = null;


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

    /**
     * @inheritDoc
     */
    public function getDefaultDelay(): float
    {
        return $this->delay;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultPriority(): ?int
    {
        return $this->priority;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultTimeToLive(): float
    {
        return $this->timeToLive;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultRetry(): ?int
    {
        return $this->retry;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultRetryDelay(): ?float
    {
        return $this->retryDelay;
    }
}
