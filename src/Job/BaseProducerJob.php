<?php

namespace WebmanTech\Enqueue\Job;

/**
 * 仅发送消息的任务，不处理任务
 * 一般用于与其他系统对接，这边发送，那边处理
 */
abstract class BaseProducerJob implements JobProducerInterface
{
    use JobProducerTrait;
}
