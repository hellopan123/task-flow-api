<?php

declare(strict_types=1);

namespace App\Common\Filter;

class TkNotificationFilter extends BaseFilter
{
    protected array $scene = [
        'list' => ['page', 'pageSize', 'user_id', 'notification_type', 'is_read'],
        'info' => ['id'],
        'add' => ['user_id', 'title', 'content', 'notification_type', 'is_read', 'related_type', 'related_id', 'create_time' => '__Now'],
        'update' => ['id', 'title', 'content'],
        'mark_read' => ['id', 'is_read', 'read_time' => '__Now'],
    ];
}
