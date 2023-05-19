<?php

namespace WebmanTech\Enqueue\Facades;

use WebmanTech\Enqueue\Client\ConsumerClient;

/**
 * @method static ConsumerClient default()
 */
class EnqueueConsumer extends Enqueue
{
    public static function __callStatic($name, $arguments)
    {
        return static::getManager()->createConsumerClient($name);
    }
}