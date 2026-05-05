<?php

declare(strict_types=1);

namespace App\Common\Model;

use Hyperf\DbConnection\Model\Model;

class TkTaskModel extends Model
{
    protected ?string $table = 'tk_task';
    
    public bool $timestamps = false;

    protected array $guarded = [];
    
    public function requirement()
    {
        return $this->belongsTo(TkRequirementModel::class, 'requirement_id', 'id');
    }
    
    public function creator()
    {
        return $this->belongsTo(TkUserModel::class, 'creator_id', 'id');
    }
    
    public function assignees()
    {
        return $this->hasMany(TkTaskAssigneeModel::class, 'task_id', 'id');
    }
    
    public function comments()
    {
        return $this->hasMany(TkTaskCommentModel::class, 'task_id', 'id');
    }
    
    public function attachments()
    {
        return $this->hasMany(TkTaskAttachmentModel::class, 'task_id', 'id');
    }
}
