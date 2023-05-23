<?php

namespace WebmanTech\Enqueue\Client\Traits;

use Closure;
use Interop\Queue\Message;
use InvalidArgumentException;
use RuntimeException;
use WebmanTech\Enqueue\Client\ConsumerClient\NotSupportMessageDefaultSolver;
use WebmanTech\Enqueue\Client\ConsumerClient\NotSupportMessageSolverInterface;
use WebmanTech\Enqueue\Enums\MessagePropertyEnum;

trait MessageNotSupportSupport
{
    private ?NotSupportMessageSolverInterface $notSupportMessageSolver = null;

    /**
     * 检查消息是否 job 支持，并且处理不支持的情况
     * @param Message $message
     * @return void
     */
    protected function checkMessageJobSupportAndSolve(Message $message): void
    {
        if (!$message->getProperty(MessagePropertyEnum::JOB)) {
            $message = $this->getNotSupportMessageSolver()->solve($message);
            if (!$message->getProperty(MessagePropertyEnum::JOB)) {
                throw new RuntimeException('NotSupportMessageSolver solve must return a message with job property');
            }
        }
    }

    /**
     * 获取消息不支持时的处理器
     * @return NotSupportMessageSolverInterface
     */
    private function getNotSupportMessageSolver(): NotSupportMessageSolverInterface
    {
        if ($this->notSupportMessageSolver === null) {
            $this->notSupportMessageSolver = (function () {
                $handler = $this->config['not_support_message_solver'] ?? null;
                if (!$handler) {
                    return new NotSupportMessageDefaultSolver();
                }
                if (!$handler instanceof Closure) {
                    throw new InvalidArgumentException('not_support_message_solver must be a closure');
                }
                return $handler();
            })();
        }
        return $this->notSupportMessageSolver;
    }
}