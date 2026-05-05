<?php

declare(strict_types=1);

namespace App\Common\Model;

use Hyperf\DbConnection\Model\Model;

class TkUserModel extends Model
{
    protected ?string $table = 'tk_user';
    
    public bool $timestamps = false;

    public array $guarded = [];
    
    public function dept()
    {
        return $this->belongsTo(SysDeptModel::class, 'dept_id', 'id');
    }
}
