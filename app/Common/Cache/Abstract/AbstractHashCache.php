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

abstract class AbstractHashCache extends AbstractCache
{
    protected array $fieldParams = [];

    public function setFieldParameter(array $params = []): static
    {
        $this->fieldParams = $params;
        return $this;
    }

    public function set(string $field, $value): bool
    {
        return (bool) $this->redis->hSet($this->key, $field, is_string($value) ? $value : json_encode($value));
    }

    public function get(string $field): mixed
    {
        $value = $this->redis->hGet($this->key, $field);
        if ($value === false) {
            return null;
        }
        $decoded = json_decode($value, true);
        return $decoded !== null ? $decoded : $value;
    }

    public function getAll(): array
    {
        return $this->redis->hGetAll($this->key);
    }

    public function hDel(string $field): bool
    {
        return (bool) $this->redis->hDel($this->key, $field);
    }

    public function fieldExists(string $field): bool
    {
        return (bool) $this->redis->hExists($this->key, $field);
    }
}
