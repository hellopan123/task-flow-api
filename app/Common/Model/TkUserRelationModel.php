<?php

declare(strict_types=1);

namespace App\Common\Model;

use Hyperf\DbConnection\Model\Model;

class TkUserRelationModel extends Model
{
    protected ?string $table = 'tk_user_relation';
    
    protected array $fillable = [
        'user_id',
        'related_user_id',
        'relation_type',
    ];
    
    protected array $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'related_user_id' => 'integer',
        'relation_type' => 'integer',
    ];
}
