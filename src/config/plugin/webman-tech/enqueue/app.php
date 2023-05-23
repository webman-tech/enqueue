<?php

use Illuminate\Events\Dispatcher;
use WebmanTech\Enqueue\Manager\EnqueueManager;

return [
    'enable' => true,
    'enqueue_manager' => function (): EnqueueManager {
        return new EnqueueManager(new Dispatcher());
    },
    'log' => [
        /**
         * @see \WebmanTech\Enqueue\Job\Traits\LogTrait::log()
         */
        'channel' => null, // 为 null 时不记录日志
    ],
];
