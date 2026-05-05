<?php

declare(strict_types=1);

namespace App\Common\Filter;

class TkFeedbackFilter extends BaseFilter
{
    protected array $scene = [
        'list' => ['page', 'pageSize', 'user_id', 'feedback_type', 'status'],
        'info' => ['id'],
        'add' => ['user_id', 'feedback_type', 'content', 'images', 'contact', 'status', 'create_time' => '__Now'],
        'update' => ['id', 'feedback_type', 'content', 'images', 'contact'],
        'reply' => ['id', 'reply', 'status', 'reply_time' => '__Now', 'reply_by' => '__OperateId'],
    ];
}
