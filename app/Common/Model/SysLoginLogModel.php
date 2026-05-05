<?php

declare(strict_types=1);

namespace App\Common\Model;

use Hyperf\DbConnection\Model\Model;

class SysLoginLogModel extends Model
{
    protected ?string $table = 'sys_login_log';
    
    public bool $timestamps = false;

    protected array $fillable = [
        'username',
        'ip',
        'location',
        'browser',
        'os',
        'login_type',
        'login_status',
        'login_msg',
        'login_time',
    ];

    protected array $casts = [
        'login_status' => 'integer',
    ];
}
