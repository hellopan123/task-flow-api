<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Admin\Validate;


use app\common\model\SysAdminRoleModel;
use Hyperf\Validation\Request\FormRequest;

class SysAdminRoleRequest extends FormRequest
{
    protected array $scenes = [
        'list'  => ['page', 'limit'],
        'info'  => ['id'],
        'add'  => ['name', 'remark','status','menu_ids'],
        'update'  => ['id','name', 'remark','status','menu_ids'],
        'update_status'  => ['id','status'],
    ];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'page'          => 'required|integer',
            'limit'         => 'required|integer',
            'id'            => [
                'required',
                'integer',
                function ($attribute, $value, $fail){
                    $info = SysAdminRoleModel::find($value);
                    if (!$info) {
                        $fail('角色不存在');
                    }
                }
            ],
            'name'          => [
                'required',
                function($attribute, $value, $fail){
                    $map = [
                        ['name','=',$value],
                    ];
                    if (!empty($attribute['id'])) $map[] = ['id','<>',$attribute['id']];

                    $role = SysAdminRoleModel::where($map)->count();
                    if ($role > 0) {
                        $fail('角色名称已存在');
                    }
                }
            ],
            'status'        => 'required|in:1,2',
            'menu_ids'      => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'page.required'              => '页码不能为空!',
            'page.integer'              => '页码格式错误!',
            'limit.required'             => '每页数量不能为空!',
            'limit.integer'             => '每页数量格式错误!',
            'id.required'                => '角色ID不能为空!',
            'name.required'              => '角色名称不能为空!',
            'status.required'            => '状态不能为空!',
            'status.in'                 => '状态格式错误!',
            'menu_ids.required'          => '授权菜单ID不能为空!',
        ];
    }


}
