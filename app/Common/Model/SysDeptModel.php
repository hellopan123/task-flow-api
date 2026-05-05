<?php

declare(strict_types=1);

namespace App\Common\Model;

use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class SysDeptModel extends Model
{
    use SoftDeletes;

    protected ?string $table = 'sys_dept';
    
    public bool $timestamps = false;

    
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }
    
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }
}
