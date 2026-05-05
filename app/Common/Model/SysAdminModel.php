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

use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class SysAdminModel extends Model
{
    use SoftDeletes;

    protected ?string $table = 'sys_admin';

    //不自动维护时间
    public bool $timestamps = false;

    public function roles()
    {
        return $this->hasMany(SysAdminRoleModel::class, 'admin_id', 'id');
    }
}
