<?php

declare(strict_types=1);

namespace App\Common\Model;

use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class SysConfigModel extends Model
{
    use SoftDeletes;

    protected ?string $table = 'sys_config';

    public bool $timestamps = false;


}
