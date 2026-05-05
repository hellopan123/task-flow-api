<?php

declare(strict_types=1);

namespace App\Api\Filter;

use App\Common\Filter\BaseFilter;
use Hyperf\Context\Context;

class FeedbackFilter extends BaseFilter
{
    protected array $scene = [
        'create' => [
            'feedback_type',
            'content',
            'images',
            'contact',
            'user_id' => '__OperateId',
            'status' => 1,
            'create_time' => '__CurrentTime',
        ],
        'list' => ['page', 'limit', 'status'],
    ];
}
