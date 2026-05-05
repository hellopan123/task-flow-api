<?php

declare(strict_types=1);

namespace App\Api\Validate;

use Hyperf\Validation\Request\FormRequest;

class FeedbackRequest extends FormRequest
{
    protected array $scenes = [
        'create' => ['feedback_type', 'content'],
        'list' => ['page', 'limit'],
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'feedback_type' => 'required|in:1,2,3',
            'content' => 'required|string|max:1000',
            'images' => 'string|max:2000',
            'contact' => 'string|max:100',
            'page' => 'integer|min:1',
            'limit' => 'integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'feedback_type.required' => '反馈类型不能为空',
            'feedback_type.in' => '反馈类型错误',
            'content.required' => '反馈内容不能为空',
            'content.max' => '反馈内容最多1000个字符',
            'images.max' => '图片链接过长',
            'contact.max' => '联系方式最多100个字符',
        ];
    }
}
