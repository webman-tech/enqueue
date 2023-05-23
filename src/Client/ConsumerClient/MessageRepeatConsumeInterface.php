<?php

namespace WebmanTech\Enqueue\Client\ConsumerClient;

interface MessageRepeatConsumeInterface
{
    /**
     * 是否消费过
     * @param string $key
     * @return bool
     */
    public function isConsumed(string $key): bool;

    /**
     * 标记消息是否已消费
     * @param string $key
     * @param bool $isConsumed
     * @return void
     */
    public function markConsumeStatus(string $key, bool $isConsumed): void;
}