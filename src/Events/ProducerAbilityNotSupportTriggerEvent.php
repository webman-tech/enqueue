<?php

namespace WebmanTech\Enqueue\Events;

use Interop\Queue\Producer;
use Throwable;

class ProducerAbilityNotSupportTriggerEvent
{
    public Producer $producer;
    public Throwable $exception;

    public function __construct(Producer $producer, Throwable $e)
    {
        $this->producer = $producer;
        $this->exception = $e;
    }
}
