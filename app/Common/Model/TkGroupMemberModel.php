<?php

declare(strict_types=1);

namespace App\Common\Model;

use Hyperf\DbConnection\Model\Model;

class TkGroupMemberModel extends Model
{
    protected ?string $table = 'tk_group_member';
    
    public bool $timestamps = false;

    
    public function group()
    {
        return $this->belongsTo(TkTaskGroupModel::class, 'group_id', 'id');
    }
    
    public function user()
    {
        return $this->belongsTo(TkUserModel::class, 'user_id', 'id');
    }
}
