<?php

declare(strict_types=1);

namespace App\Common\Model;

use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class SysDictItemModel extends Model
{
    use SoftDeletes;

    protected ?string $table = 'sys_dict_item';
    
    public bool $timestamps = false;

    
    public function dict()
    {
        return $this->belongsTo(SysDictModel::class, 'dict_id', 'id');
    }
}
