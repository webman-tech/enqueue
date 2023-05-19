<?php

namespace WebmanTech\Enqueue\DTO;

use Illuminate\Support\Fluent;

/**
 * @property-read string $name 消费者的名字
 * @property-read string $connection 连接名
 * @property-read array $destination_config destination的配置
 * @property-read int|null $count 消费者数量
 */
final class ConsumerDefinitionDTO extends Fluent
{
    public function __construct($attributes = [])
    {
        if (!isset($attributes['name'])) {
            throw new \InvalidArgumentException('name is required');
        }
        if (!isset($attributes['connection'])) {
            throw new \InvalidArgumentException('connection is required');
        }
        if (!isset($attributes['destination_config'])) {
            $attributes['destination_config'] = [];
        }
        if (!isset($attributes['count'])) {
            $attributes['count'] = 1;
        }
        parent::__construct($attributes);
    }
}