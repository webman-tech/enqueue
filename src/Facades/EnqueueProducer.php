<?php

namespace WebmanTech\Enqueue\Facades;

use WebmanTech\Enqueue\Client\ProducerClient;

/**
 * @method static ProducerClient default()
 */
class EnqueueProducer extends Enqueue
{
    public static function __callStatic($name, $arguments)
    {
        return static::getManager()->createProducerClient($name);
    }
}