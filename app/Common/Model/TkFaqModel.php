<?php

declare(strict_types=1);

namespace App\Common\Model;

use Hyperf\DbConnection\Model\Model;

class TkFaqModel extends Model
{
    protected ?string $table = 'tk_faq';
    
    public bool $timestamps = false;

}
