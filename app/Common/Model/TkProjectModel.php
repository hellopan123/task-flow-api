<?php

declare(strict_types=1);

namespace App\Common\Model;

use Hyperf\DbConnection\Model\Model;

class TkProjectModel extends Model
{
    protected ?string $table = 'tk_project';
    
    public bool $timestamps = false;
    
    public function owner()
    {
        return $this->belongsTo(TkUserModel::class, 'owner_id', 'id');
    }
    
    public function members()
    {
        return $this->hasMany(TkProjectMemberModel::class, 'project_id', 'id');
    }
    
    public function requirements()
    {
        return $this->hasMany(TkRequirementModel::class, 'project_id', 'id');
    }
}
