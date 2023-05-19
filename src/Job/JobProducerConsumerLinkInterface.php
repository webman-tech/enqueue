<?php

namespace WebmanTech\Enqueue\Job;

/**
 * 生产者和消费者结合
 */
interface JobProducerConsumerLinkInterface
{
    /**
     * 消费该消息的类名
     * @return string
     */
    public function getJobConsumerClass(): string;

    /**
     * MessageId
     * @return string
     */
    public function getMessageId(): string;

    /**
     * 该消息默认的延迟，单位秒
     * @return float
     */
    public function getDefaultDelay(): float;

    /**
     * 该消息默认的权重
     * @return int|null
     */
    public function getDefaultPriority(): ?int;

    /**
     * 该消息默认的有效期，单位秒
     * @return float
     */
    public function getDefaultTimeToLive(): float;

    /**
     * 该消息默认的重试次数
     * 实际执行次数=初次执行+重试次数
     * @return int 大于0为有效值，小于0或null表示使用全局默认值，等于0表示不重试
     */
    public function getDefaultRetry(): ?int;

    /**
     * 重试延迟，单位秒
     * 为 null 时以全局配置为准
     * @return float|null
     */
    public function getDefaultRetryDelay(): ?float;
}
