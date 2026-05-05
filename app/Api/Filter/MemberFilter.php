<?php

declare(strict_types=1);

namespace App\Api\Filter;

use App\Common\Filter\BaseFilter;

class MemberFilter extends BaseFilter
{
    protected array $scene = [
        'list' => ['page', 'limit', 'group_id', 'keyword'],
    ];
}
