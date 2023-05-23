<?php

namespace WebmanTech\Enqueue\Job;

use Interop\Queue\Message;

interface JobConsumerInterface extends JobConsumeRetryInterface, JobConsumeFailedInterface
{
    /**
     * 处理消息
     *
     * 注意 handle 方法内不要使用当前类下的其他参数，因为处理消息是新 new 出来的实例，没有上下文语境
     *
     * amqp 下针对超长时间的运行任务，需要使用 declare(ticks=1) {} 维持心跳，ticks 值自行调整，不建议用 1
     * @see https://php-enqueue.github.io/transport/amqp_lib/#long-running-task-and-heartbeat-and-timeouts
     *
     * @param Message $message
     * @return bool|string|void|null
     * string 值见 MessageAckEnum；
     * 为 true|null|void 时结果为 MessageAckEnum::ACK；
     * 为 false|Exception 时结果为 MessageAckEnum::REQUEUE，会进行重试；
     */
    public function handle(Message $message);

    /**
     * 单个任务最大执行时长
     * 为 -1 时表示以默认配置为准，为 0 时表示不限制
     * @return int|null
     */
    public function getMaxExecTime(): ?int;

    /**
     * 单个任务最大使用内存
     * 为 -1 时表示以默认配置为准，为 0 时表示不限制
     * 由于无法捕获内存溢出的错误，因此当内存使用超过此设定值时会自动结束进程
     * @return int|null
     */
    public function getMaxMemoryUse(): ?int;

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
