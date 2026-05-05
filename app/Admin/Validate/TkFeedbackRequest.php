<?php

declare(strict_types=1);

namespace App\Admin\Validate;

use Hyperf\Validation\Request\FormRequest;

class TkFeedbackRequest extends FormRequest
{
    protected array $scenes = [
        'add' => ['user_id', 'feedback_type', 'content'],
        'update' => ['id', 'feedback_type', 'content'],
        'reply' => ['id', 'reply'],
        'delete' => ['id'],
        'info' => ['id'],
        'list' => ['page', 'pageSize'],
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
            'feedback_type' => 'required|integer|in:1,2,3,4',
            'content' => 'required|string|max:2000',
            'images' => 'string|max:2000',
            'contact' => 'string|max:100',
            'status' => 'integer|in:0,1,2',
            'reply' => 'required|string|max:1000',
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => '反馈ID不能为空',
            'id.integer' => '反馈ID格式错误',
            'id.min' => '反馈ID必须大于0',
            'user_id.required' => '用户ID不能为空',
            'user_id.integer' => '用户ID格式错误',
            'feedback_type.required' => '反馈类型不能为空',
            'feedback_type.in' => '反馈类型值错误',
            'content.required' => '反馈内容不能为空',
            'content.max' => '反馈内容最多2000个字符',
            'reply.required' => '回复内容不能为空',
            'reply.max' => '回复内容最多1000个字符',
        ];
    }
}
