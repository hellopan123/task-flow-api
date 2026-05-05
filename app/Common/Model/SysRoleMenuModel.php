<?php

declare(strict_types=1);

namespace App\Common\Model;

use Hyperf\DbConnection\Model\Model;

class SysRoleMenuModel extends Model
{
    protected ?string $table = 'sys_role_menu';
    
    public bool $timestamps = false;

}
