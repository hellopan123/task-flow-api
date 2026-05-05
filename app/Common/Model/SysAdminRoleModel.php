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

namespace App\Common\Model;

use Hyperf\DbConnection\Model\Model;

class SysAdminRoleModel extends Model
{
    protected ?string $table = 'sys_admin_role';

    public bool $timestamps = false;

}
