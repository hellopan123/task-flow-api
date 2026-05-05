<?php

declare(strict_types=1);

namespace App\Common\Model;

use Hyperf\DbConnection\Model\Model;

class TkFeedbackModel extends Model
{
    protected ?string $table = 'tk_feedback';
    
    public bool $timestamps = false;

    public function user()
    {
        return $this->belongsTo(TkUserModel::class, 'user_id', 'id');
    }
}
