<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Common\Cache\Abstract;

use Hyperf\Context\ApplicationContext;
use Hyperf\Redis\Redis;
use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\RedisProxy;

abstract class AbstractCache
{
    protected static string $prefix = '';

    protected int $expire = 0;

    protected string $poolName = 'default';

    protected RedisProxy|Redis $redis;

    protected string $key = '';

    public function __construct()
    {
        $container = ApplicationContext::getContainer();
        $this->redis = $container->get(RedisFactory::class)->get($this->poolName);
    }

    public function setKeyParameter(array $parameter = []): static
    {
        // 自动转义参数中的冒号，防止 Key 结构混乱
        $parameter = array_map(function ($item) {
            return str_replace(':', '_', (string) $item);
        }, $parameter);
        
        $this->key = $this::key($parameter);
        return $this;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function del(): bool
    {
        return (bool) $this->redis->del($this->key);
    }

    public function exists(): bool
    {
        return (bool) $this->redis->exists($this->key);
    }

    /**
     * 静态工厂方法，快速创建实例并设置 Key 参数
     * 
     * @param array $parameter Key 参数
     * @return static
     */
    public static function make(array $parameter = []): static
    {
        $instance = new static();
        $instance->setKeyParameter($parameter);
        return $instance;
    }

    abstract protected static function key(array $parameter): string;
}
