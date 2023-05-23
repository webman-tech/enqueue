<?php

namespace WebmanTech\Enqueue\Job;

use Interop\Queue\Message;
use WebmanTech\Enqueue\Enums\MessagePropertyEnum;
use WebmanTech\Enqueue\Events\ConsumerConsumeAfterEvent;
use WebmanTech\Enqueue\Exceptions\ConsumeFailedException;

trait JobConsumerTrait
{
    /**
     * 单个任务最大执行时长，为 -1 时表示以默认配置为准，为 0 时表示不限制
     * @var int
     */
    protected int $maxExecTime = -1;
    /**
     * 单个任务最大使用内存，为 -1 时表示以默认配置为准，为 0 时表示不限制
     * 由于无法捕获内存溢出的错误，因此当内存使用超过此设定值时会自动结束进程
     * @var int
     */
    protected int $maxMemoryUse = -1;
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

    /**
     * @inheritDoc
     */
    public function getMaxExecTime(): ?int
    {
        if ($this->maxExecTime < 0) {
            return null;
        }
        return $this->maxExecTime;
    }

    /**
     * @inheritDoc
     */
    public function getMaxMemoryUse(): ?int
    {
        if ($this->maxMemoryUse < 0) {
            return null;
        }
        return $this->maxMemoryUse;
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

    /**
     * @inheritDoc
     */
    public function shouldRetry(Message $message, ?ConsumeFailedException $consumeFailedException): bool
    {
        $alreadyCount = (int)$message->getProperty(MessagePropertyEnum::ALREADY_RETRY, 0);
        $retryCount = (int)$message->getProperty(MessagePropertyEnum::RETRY, 0);
        return $retryCount > $alreadyCount;
    }

    /**
     * @inheritDoc
     */
    public function retryDelay(Message $message, ?ConsumeFailedException $consumeFailedException): float
    {
        return (float)$message->getProperty(MessagePropertyEnum::RETRY_DELAY, 0);
    }

    /**
     * @inheritDoc
     */
    public function handleConsumeFailed(ConsumerConsumeAfterEvent $event): void
    {
    }
}
