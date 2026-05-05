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

namespace App\Common\Cache\db02;

use App\Common\Cache\Abstract\AbstractStringCache;
use App\Common\Model\SysAdminRoleModel;
use App\Common\Model\SysMenuModel;

/**
* 系统管理员角色缓存
 */
class SysAdminRoleStringCache extends AbstractStringCache
{
    protected static string $prefix = 'sys:admin:role';

    protected string $poolName = 'db02';

    protected static function key(array $parameter): string
    {
        return self::$prefix . ':' . $parameter[0];
    }
}
