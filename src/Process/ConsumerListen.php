<?php

namespace WebmanTech\Enqueue\Process;

use Interop\Queue\Exception\SubscriptionConsumerNotSupportedException;
use WebmanTech\Enqueue\Facades\Enqueue;
use Workerman\Timer;

class ConsumerListen
{
    protected string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function onWorkerStart()
    {
        $consumerClient = Enqueue::getManager()->createConsumerClient($this->name);

        try {
            $consumerClient->listen();
        } catch (SubscriptionConsumerNotSupportedException $e) {
            // use consume
            Timer::add(1, fn() => $consumerClient->consume());
        }
    }
}