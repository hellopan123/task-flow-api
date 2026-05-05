<?php

declare(strict_types=1);

namespace App\Common\Model;

use Hyperf\DbConnection\Model\Model;

class TkProjectMemberModel extends Model
{
    protected ?string $table = 'tk_project_member';
    
    public bool $timestamps = false;

    protected array $guarded = [];

    public function project()
    {
        return $this->belongsTo(TkProjectModel::class, 'project_id', 'id');
    }
    
    public function user()
    {
        return $this->belongsTo(TkUserModel::class, 'user_id', 'id');
    }
}
