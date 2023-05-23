<?php

namespace WebmanTech\Enqueue\Job;

/**
 * 生产者和消费者结合
 */
interface JobProducerConsumerLinkInterface extends JobProducerInterface, JobConsumerInterface
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
}
