<?php

namespace WebmanTech\Enqueue\Events;

class ConsumerJobTimeoutEvent
{
    public int $maxExecTime;

    public function __construct(int $maxExecTime)
    {
        $this->maxExecTime = $maxExecTime;
    }
}
