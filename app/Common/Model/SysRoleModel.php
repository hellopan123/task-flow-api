<?php

declare(strict_types=1);

namespace App\Common\Model;

use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class SysRoleModel extends Model
{
    use SoftDeletes;

    protected ?string $table = 'sys_role';
    
    public bool $timestamps = false;

    public function menuIds()
    {
        return $this->hasMany(SysRoleMenuModel::class, 'role_id', 'id');
    }
}
