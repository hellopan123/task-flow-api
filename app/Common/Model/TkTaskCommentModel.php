<?php

declare(strict_types=1);

namespace App\Common\Model;

use Hyperf\DbConnection\Model\Model;

class TkTaskCommentModel extends Model
{
    protected ?string $table = 'tk_task_comment';
    
    public bool $timestamps = false;
    
    public function task()
    {
        return $this->belongsTo(TkTaskModel::class, 'task_id', 'id');
    }
    
    public function user()
    {
        return $this->belongsTo(TkUserModel::class, 'user_id', 'id');
    }
    
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }
    
    public function replies()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }
}
