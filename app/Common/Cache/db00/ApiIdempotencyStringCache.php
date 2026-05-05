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

namespace App\Common\Cache\db00;

use App\Common\Cache\Abstract\AbstractStringCache;

/**
 * API 幂等性缓存.
 */
class ApiIdempotencyStringCache extends AbstractStringCache
{
    protected static string $prefix = 'api:idempotency';

    protected int $expire = 5;

    protected static function key(array $parameter): string
    {
        return self::$prefix . ':' . implode(':', $parameter);
    }
}
