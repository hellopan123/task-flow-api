<?php

declare(strict_types=1);

namespace App\Common\Model;

use Hyperf\DbConnection\Model\Model;

class TkSmsCodeModel extends Model
{
    protected ?string $table = 'tk_sms_code';
    
    public bool $timestamps = false;

    protected array $guarded = [];

}
