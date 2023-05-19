<?php

namespace WebmanTech\Enqueue\Facades;

use WebmanTech\Enqueue\Manager\EnqueueManager;

class Enqueue
{
    protected static ?EnqueueManager $manager = null;

    public static function getManager(): EnqueueManager
    {
        if (static::$manager === null) {
            static::$manager = config('plugin.webman-tech.enqueue.app.enqueue_manager', function () {
                return new EnqueueManager();
            })();
        }

        return static::$manager;
    }
}