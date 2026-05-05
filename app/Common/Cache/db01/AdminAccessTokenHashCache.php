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

namespace App\Common\Cache\db01;

use App\Common\Cache\Abstract\AbstractHashCache;

/**
 * 管理后台登录token
 */
class AdminAccessTokenHashCache extends AbstractHashCache
{
    protected static string $prefix = 'admin:token';

    protected string $poolName = 'db01';

    protected static function key(array $parameter): string
    {
        return self::$prefix . ':' . $parameter['user_id'] . ':' . $parameter['app_id'];
    }
}
