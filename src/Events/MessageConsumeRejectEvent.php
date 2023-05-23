<?php

namespace WebmanTech\Enqueue\Events;

class MessageConsumeRejectEvent
{
    public ConsumerConsumeAfterEvent $event;

    public function __construct(ConsumerConsumeAfterEvent $event)
    {
        $this->event = $event;
    }
}