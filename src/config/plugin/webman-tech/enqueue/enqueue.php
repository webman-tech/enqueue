<?php

return [
    /**
     * @see EnqueueManager::$config
     */
    'producer' => [
        /**
         * @see ProducerClient::$config
         */
    ],
    'consumer' => [
        /**
         * @see ConsumerClient::$config
         */
    ],
    'definitions' => [
        'producer' => [
            // $name => ProducerDefinitionDTO
            'default' => ['connection' => 'null'],
        ],
        'consumer' => [
            // $name => ConsumerDefinitionDTO
            'default' => ['connection' => 'null', 'count' => 1],
        ],
    ],
    'connections' => [
        // $name => ConnectionDTO
        'null' => [
            'connection' => fn() => new \Enqueue\Null\NullConnectionFactory(),
            'destination' => fn(\Enqueue\Null\NullContext $context, array $config) => $context->createQueue($config['queue'] ?? 'queue'),
        ],
        'file' => [
            'connection' => fn() => new \Enqueue\Fs\FsConnectionFactory([
                'path' => runtime_path() . '/enqueue'
            ]),
            'destination' => fn(\Enqueue\Fs\FsContext $context, array $config) => $context->createQueue($config['queue'] ?? 'queue'),
        ],
        'amqp' => [
            'connection' => fn() => new \Enqueue\AmqpBunny\AmqpConnectionFactory([
                'host' => '127.0.0.1',
                'port' => 5672,
                'vhost' => '/',
                'user' => 'user',
                'pass' => 'pass',
                'persisted' => false,
            ]),
            'destination' => function (\Enqueue\AmqpBunny\AmqpContext $context, array $config) {
                $exchange = $context->createTopic($config['exchange'] ?? 'exchange');
                //$exchange->setType(\Interop\Amqp\AmqpTopic::TYPE_DIRECT);
                //$context->declareTopic($exchange);
                $queue = $context->createQueue($config['queue'] ?? 'queue');
                //$queue->addFlag(\Interop\Amqp\AmqpQueue::FLAG_DURABLE);
                //$context->declareQueue($queue);
                $context->bind(new \Interop\Amqp\Impl\AmqpBind($exchange, $queue));

                return $queue;
            },
        ],
        'redis' => [
            'connection' => fn() => new \Enqueue\Redis\RedisConnectionFactory([
                'host' => '127.0.0.1',
                'port' => 6379,
                'scheme_extensions' => ['phpredis'],
            ]),
            'destination' => fn(\Enqueue\Redis\RedisContext $context, array $config) => $context->createQueue($config['queue'] ?? 'queue'),
        ],
    ],
];
