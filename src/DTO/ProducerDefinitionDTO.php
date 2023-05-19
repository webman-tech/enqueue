<?php

namespace WebmanTech\Enqueue\DTO;

use Illuminate\Support\Fluent;

/**
 * @property-read string $name 生产者的名字
 * @property-read string $connection 连接名
 * @property-read array $destination_config destination的配置
 */
final class ProducerDefinitionDTO extends Fluent
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
        parent::__construct($attributes);
    }
}