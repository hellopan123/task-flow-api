<?php

declare(strict_types=1);

namespace App\Api\Validate;

use Hyperf\Validation\Request\FormRequest;

class NotificationRequest extends FormRequest
{
    protected array $scenes = [
        'list' => ['page', 'limit'],
        'mark_read' => ['id'],
        'delete' => ['id'],
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|integer|min:1',
            'page' => 'integer|min:1',
            'limit' => 'integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => '通知ID不能为空',
            'id.integer' => '通知ID格式错误',
        ];
    }
}
