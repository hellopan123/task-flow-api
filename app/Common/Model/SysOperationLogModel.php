<?php

declare(strict_types=1);

namespace App\Common\Model;

use Hyperf\DbConnection\Model\Model;

class SysOperationLogModel extends Model
{
    protected ?string $table = 'sys_operation_log';
    
    public bool $timestamps = false;

    protected array $fillable = [
        'module',
        'operation_type',
        'operation_desc',
        'request_method',
        'request_url',
        'request_params',
        'response_result',
        'ip',
        'location',
        'user_agent',
        'operator_id',
        'operator_name',
        'status',
        'error_msg',
        'cost_time',
    ];

    protected array $casts = [
        'operator_id' => 'integer',
        'status' => 'integer',
        'cost_time' => 'integer',
    ];
}
