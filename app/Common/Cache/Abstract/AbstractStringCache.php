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

abstract class AbstractStringCache extends AbstractCache
{
    public function set($value): bool
    {
        return (bool) $this->redis->set($this->key, is_string($value) ? $value : json_encode($value));
    }

    public function setEx($value, int $ttl = 0): bool
    {
        $ttl = $ttl > 0 ? $ttl : $this->expire;
        return (bool) $this->redis->setex($this->key, $ttl, is_string($value) ? $value : json_encode($value));
    }

    public function get(): mixed
    {
        $value = $this->redis->get($this->key);
        if ($value === false) {
            return null;
        }
        
        // 尝试解析 JSON，如果解析失败或不是数组/对象，则返回原值
        // 增加 JSON_BIGINT_AS_STRING 选项，防止大整数精度丢失
        $decoded = json_decode($value, true, 512, JSON_BIGINT_AS_STRING);
        
        // 只有当解析成功且为数组时才返回解析后的数据
        // 避免数字字符串 "123" 被解析为 int 123
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }
        
        return $value;
    }

    public function increase(int $step = 1): int
    {
        return $this->redis->incrBy($this->key, $step);
    }

    public function expire(int $ttl): bool
    {
        return $this->redis->expire($this->key, $ttl);
    }

    public function setNx($value,$ttl=-1): bool
    {
        $value = is_string($value) ? $value : json_encode($value);

        if ($ttl == 0) {
            $res = $this->redis->setnx($this->key, $value);
            $this->redis->persist($this->key);
        } elseif ($ttl > 0) {
            $res = $this->redis->set($this->key, $value,['nx', 'ex' => $ttl]);
        } else {
            $res = $this->redis->set($this->key, $value,['nx', 'ex' => $this->expire]);
        }
        return (bool) $res;
    }
}
