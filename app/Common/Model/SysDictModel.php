<?php

declare(strict_types=1);

namespace App\Common\Model;

use Hyperf\DbConnection\Model\Model;

class SysDictModel extends Model
{
    protected ?string $table = 'sys_dict';
    
    public bool $timestamps = false;

    
    public function items()
    {
        return $this->hasMany(SysDictItemModel::class, 'dict_id', 'id');
    }
}
