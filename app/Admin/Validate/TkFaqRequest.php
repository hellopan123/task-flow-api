<?php

declare(strict_types=1);

namespace App\Admin\Validate;

use Hyperf\Validation\Request\FormRequest;

class TkFaqRequest extends FormRequest
{
    protected array $scenes = [
        'add' => ['question', 'answer', 'category'],
        'update' => ['id', 'question', 'answer', 'category'],
        'update_status' => ['id', 'status'],
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
            'question' => 'required|string|max:500',
            'answer' => 'required|string|max:5000',
            'category' => 'required|string|max:50',
            'sort' => 'integer|min:0',
            'status' => 'required|in:0,1',
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'FAQ ID不能为空',
            'id.integer' => 'FAQ ID格式错误',
            'id.min' => 'FAQ ID必须大于0',
            'question.required' => '问题不能为空',
            'question.max' => '问题最多500个字符',
            'answer.required' => '答案不能为空',
            'answer.max' => '答案最多5000个字符',
            'category.required' => '分类不能为空',
            'category.max' => '分类最多50个字符',
            'sort.integer' => '排序必须是整数',
            'sort.min' => '排序不能小于0',
            'status.required' => '状态不能为空',
            'status.in' => '状态值错误',
        ];
    }
}
