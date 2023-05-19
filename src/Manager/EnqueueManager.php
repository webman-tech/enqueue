<?php

namespace WebmanTech\Enqueue\Manager;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Events\Dispatcher;
use Interop\Queue\ConnectionFactory;
use Interop\Queue\Context;
use Interop\Queue\Destination;
use InvalidArgumentException;
use support\Container;
use WebmanTech\Enqueue\Client\ConsumerClient;
use WebmanTech\Enqueue\Client\ProducerClient;
use WebmanTech\Enqueue\DTO\ConnectionDTO;
use WebmanTech\Enqueue\DTO\ConsumerDefinitionDTO;
use WebmanTech\Enqueue\DTO\ProducerDefinitionDTO;
use WebmanTech\Enqueue\Helper\ArrayHelper;
use WebmanTech\Enqueue\Process\ConsumerListen;

class EnqueueManager
{
    protected DispatcherContract $events;
    protected array $config = [
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
            ],
            'consumer' => [
                // $name => ConsumerDefinitionDTO
            ],
        ],
        'connections' => [
            // $name => ConnectionDTO
        ],
    ];

    public function __construct(DispatcherContract $events = null)
    {
        if (!$events) {
            $events = new Dispatcher();
        }
        $this->events = $events;
        if ($config = config('plugin.webman-tech.enqueue.enqueue', []))
            $this->config = ArrayHelper::merge($this->config, $config);
    }

    private array $connectionConfigs = [];

    /**
     * @param string $connectionName
     * @return ConnectionDTO
     */
    protected function getConnectionConfig(string $connectionName): ConnectionDTO
    {
        if (!isset($this->connectionConfigs[$connectionName])) {
            $data = $this->config['connections'][$connectionName] ?? null;
            if (!$data) {
                throw new InvalidArgumentException("connection not found: {$connectionName}");
            }
            $this->connectionConfigs[$connectionName] = new ConnectionDTO(array_merge(
                ['name' => $connectionName],
                $data
            ));
        }

        return $this->connectionConfigs[$connectionName];
    }

    /**
     * 获取连接
     * @param string $connectionName
     * @return ConnectionFactory
     */
    public function getConnection(string $connectionName): ConnectionFactory
    {
        return $this->getConnectionConfig($connectionName)->getConnection();
    }

    /**
     * 创建 context
     * @param string $connectionName
     * @return Context
     */
    public function createContext(string $connectionName): Context
    {
        return $this->getConnection($connectionName)->createContext();
    }

    /**
     * 创建 queue / topic
     * @param Context $context
     * @param string $connectionName
     * @param array $config
     * @return Destination
     */
    public function createDestination(Context $context, string $connectionName, array $config): Destination
    {
        return $this->getConnectionConfig($connectionName)->createDestination($context, $config);
    }

    /**
     * 获取 producer/consumer 定义配置
     * @param string $type
     * @param string $name
     * @return array
     */
    protected function getDefinitionConfig(string $type, string $name): array
    {
        $data = $this->config['definitions'][$type][$name] ?? null;
        if (!$data) {
            throw new InvalidArgumentException("{$type} definition not found: {$name}");
        }
        return $data;
    }

    protected array $definitionConfigs = [];

    /**
     * 获取 producer 定义
     * @param string $name
     * @return ProducerDefinitionDTO
     */
    protected function getProducerDefinition(string $name): ProducerDefinitionDTO
    {
        $type = 'producer';
        if (!isset($this->definitionConfigs[$type][$name])) {
            $this->definitionConfigs[$type][$name] = new ProducerDefinitionDTO(array_merge(
                ['name' => $name],
                $this->getDefinitionConfig($type, $name)
            ));
        }

        return $this->definitionConfigs[$type][$name];
    }

    /**
     * 获取 consumer 定义
     * @param string $name
     * @return ConsumerDefinitionDTO
     */
    protected function getConsumerDefinition(string $name): ConsumerDefinitionDTO
    {
        $type = 'consumer';
        if (!isset($this->definitionConfigs[$type][$name])) {
            $this->definitionConfigs[$type][$name] = new ConsumerDefinitionDTO(array_merge(
                ['name' => $name],
                $this->getDefinitionConfig($type, $name)
            ));
        }

        return $this->definitionConfigs[$type][$name];
    }

    /**
     * 创建 producer
     * @param string $name
     * @return ProducerClient
     */
    public function createProducerClient(string $name): ProducerClient
    {
        $definition = $this->getProducerDefinition($name);
        $context = $this->createContext($definition->connection);

        return Container::make(ProducerClient::class, [
            'context' => $context,
            'destination' => $this->createDestination($context, $definition->connection, $definition->destination_config),
            'events' => $this->events,
            'config' => $this->config['producer'],
        ]);
    }

    /**
     * 创建 consumer
     * @param string $name
     * @return ConsumerClient
     */
    public function createConsumerClient(string $name): ConsumerClient
    {
        $definition = $this->getConsumerDefinition($name);
        $context = $this->createContext($definition->connection);

        return Container::make(ConsumerClient::class, [
            'context' => $context,
            'destination' => $this->createDestination($context, $definition->connection, $definition->destination_config),
            'events' => $this->events,
            'config' => $this->config['consumer'],
        ]);
    }

    /**
     * 获取所有消费进程配置
     * @param array $config
     * @return array
     */
    public function getConsumerProcesses(array $config = []): array
    {
        $config = array_merge([
            'process_name_prefix' => 'consumer_',
        ], $config);

        $data = [];
        foreach (array_keys($this->config['definitions']['consumer']) as $name) {
            $definition = $this->getConsumerDefinition($name);
            for ($i = 0; $i < $definition->count; $i++) {
                $processName = $config['process_name_prefix'] . $definition->name;
                if ($definition->count > 1) {
                    $processName .= '_' . ($i + 1);
                }
                $data[$processName] = [
                    'handler' => ConsumerListen::class,
                    'constructor' => [
                        'name' => $definition->name,
                    ],
                ];
            }
        }
        return $data;
    }
}