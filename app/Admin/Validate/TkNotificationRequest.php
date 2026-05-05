<?php

declare(strict_types=1);

namespace App\Admin\Validate;

use Hyperf\Validation\Request\FormRequest;

class TkNotificationRequest extends FormRequest
{
    protected array $scenes = [
        'add' => ['user_id', 'title', 'content', 'notification_type'],
        'update' => ['id', 'title', 'content'],
        'delete' => ['id'],
        'info' => ['id'],
        'list' => ['page', 'pageSize'],
        'mark_read' => ['id'],
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|integer|min:1',
            'user_id' => 'required|integer|min:1',
            'title' => 'required|string|max:200',
            'content' => 'required|string|max:2000',
            'notification_type' => 'required|integer|in:1,2,3,4',
            'is_read' => 'integer|in:0,1',
            'related_type' => 'string|max:50',
            'related_id' => 'integer|min:1',
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => '通知ID不能为空',
            'id.integer' => '通知ID格式错误',
            'id.min' => '通知ID必须大于0',
            'user_id.required' => '用户ID不能为空',
            'user_id.integer' => '用户ID格式错误',
            'title.required' => '通知标题不能为空',
            'title.max' => '通知标题最多200个字符',
            'content.required' => '通知内容不能为空',
            'content.max' => '通知内容最多2000个字符',
            'notification_type.required' => '通知类型不能为空',
            'notification_type.in' => '通知类型值错误',
        ];
    }
}
