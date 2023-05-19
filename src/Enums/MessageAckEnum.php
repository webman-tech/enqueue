<?php

namespace WebmanTech\Enqueue\Enums;

class MessageAckEnum
{
    const ACK = 'ack'; // 处理成功
    const REJECT = 'reject'; // 拒绝处理
    const REQUEUE = 'requeue'; // 处理失败，重新进入队列
}
