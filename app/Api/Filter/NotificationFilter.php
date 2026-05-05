<?php

declare(strict_types=1);

namespace App\Api\Filter;

use App\Common\Filter\BaseFilter;

class NotificationFilter extends BaseFilter
{
    protected array $scene = [
        'list' => ['page', 'limit', 'is_read', 'notification_type'],
    ];
}
