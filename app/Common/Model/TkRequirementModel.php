<?php

declare(strict_types=1);

namespace App\Common\Model;

use Hyperf\DbConnection\Model\Model;

class TkRequirementModel extends Model
{
    protected ?string $table = 'tk_requirement';
    
    public bool $timestamps = false;

    protected array $guarded = [];

    public function project()
    {
        return $this->belongsTo(TkProjectModel::class, 'project_id', 'id');
    }
    
    public function owner()
    {
        return $this->belongsTo(TkUserModel::class, 'owner_id', 'id');
    }
    
    public function creator()
    {
        return $this->belongsTo(TkUserModel::class, 'creator_id', 'id');
    }
    
    public function tasks()
    {
        return $this->hasMany(TkTaskModel::class, 'requirement_id', 'id');
    }
}
