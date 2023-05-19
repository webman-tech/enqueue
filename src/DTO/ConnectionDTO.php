<?php

namespace WebmanTech\Enqueue\DTO;

use Closure;
use Illuminate\Support\Fluent;
use Interop\Queue\ConnectionFactory;
use Interop\Queue\Context;
use Interop\Queue\Destination;
use InvalidArgumentException;

/**
 * @property-read string $name 连接名
 * @property-read Closure $connection 创建 ConnectionFactory
 * @property-read Closure $destination 创建 Destination
 */
final class ConnectionDTO extends Fluent
{
    public function __construct($attributes = [])
    {
        if (!isset($attributes['name'])) {
            throw new InvalidArgumentException('name is required');
        }
        if (!isset($attributes['connection'])) {
            throw new InvalidArgumentException('connection is required');
        }
        if (!isset($attributes['destination'])) {
            throw new InvalidArgumentException('destination is required');
        }
        parent::__construct($attributes);
    }

    private ?ConnectionFactory $connectionFactory = null;

    /**
     * @return ConnectionFactory
     */
    public function getConnection(): ConnectionFactory
    {
        if ($this->connectionFactory === null) {
            $this->connectionFactory = call_user_func($this->connection);
        }
        return $this->connectionFactory;
    }

    /**
     * @param Context $context
     * @param array $config
     * @return Destination
     */
    public function createDestination(Context $context, array $config): Destination
    {
        return call_user_func($this->destination, $context, $config);
    }
}