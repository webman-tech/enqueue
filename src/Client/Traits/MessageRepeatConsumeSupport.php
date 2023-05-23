<?php

namespace WebmanTech\Enqueue\Client\Traits;

use Closure;
use Interop\Queue\Message;
use InvalidArgumentException;
use WebmanTech\Enqueue\Client\ConsumerClient\MessageRepeatConsumeInterface;
use WebmanTech\Enqueue\Exceptions\MessageRepeatConsumeException;

trait MessageRepeatConsumeSupport
{
    /**
     * 检查消息是否重复消费，并且标记不能重复消费
     * @param Message $message
     * @return void
     */
    protected function checkMessageRepeatAndMark(Message $message): void
    {
        if ($handler = $this->getMessageRepeatConsumerHandler()) {
            $key = $this->getMessageRepeatConsumerKey($message);
            if ($key === null) {
                return;
            }
            if ($handler->isConsumed($key)) {
                throw new MessageRepeatConsumeException($message);
            }
            $handler->markConsumeStatus($key, true);
        }
    }

    /**
     * 标记消息可以再次消费
     * @param Message $message
     * @return void
     */
    protected function markMessageCanReconsume(Message $message): void
    {
        if ($handler = $this->getMessageRepeatConsumerHandler()) {
            $key = $this->getMessageRepeatConsumerKey($message);
            $handler->markConsumeStatus($key, false);
        }
    }

    /**
     * 获取重复消费处理器
     * @return MessageRepeatConsumeInterface|null
     */
    private function getMessageRepeatConsumerHandler(): ?MessageRepeatConsumeInterface
    {
        $handler = $this->config['message_repeat_consume_handler'] ?? null;
        if (!$handler) {
            return null;
        }
        if (!$handler instanceof Closure) {
            throw new InvalidArgumentException('message_repeat_consume_handler must be a closure');
        }
        return $handler();
    }

    /**
     * 获取重复消费记录的 key
     * @param Message $message
     * @return string|null 返回 null 表示不判断重复
     */
    private function getMessageRepeatConsumerKey(Message $message): ?string
    {
        $keyHandler = $this->config['message_repeat_consume_key'] ?? null;
        if (!$keyHandler) {
            return $message->getMessageId();
        }
        if (!$keyHandler instanceof Closure) {
            throw new InvalidArgumentException('message_repeat_consume_key must be a closure');
        }
        return $keyHandler($message);
    }
}