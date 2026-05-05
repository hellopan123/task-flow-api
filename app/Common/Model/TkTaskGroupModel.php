<?php

declare(strict_types=1);

namespace App\Common\Model;

use Hyperf\DbConnection\Model\Model;

class TkTaskGroupModel extends Model
{
    protected ?string $table = 'tk_task_group';
    
    public bool $timestamps = false;
    
    public function creator()
    {
        return $this->belongsTo(TkUserModel::class, 'creator_id', 'id');
    }
    
    public function members()
    {
        return $this->hasMany(TkGroupMemberModel::class, 'group_id', 'id');
    }
}
