<?php

declare(strict_types=1);

namespace App\Common\Model;

use Hyperf\DbConnection\Model\Model;

class TkNotificationModel extends Model
{
    protected ?string $table = 'tk_notification';
    
    public bool $timestamps = false;

    public array $guarded = [];
    
    public function user()
    {
        return $this->belongsTo(TkUserModel::class, 'user_id', 'id');
    }
}
