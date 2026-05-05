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

use App\Common\Constant\SysMenuConst;
use App\Common\Exception\AppException;
use App\Common\Model\SysMenuModel;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Rule;

class SysMenuRequest extends FormRequest
{
    protected array $scenes = [
        'add'           => ['type', 'pid', 'name', 'title', 'path', 'order', 'status'],
        'update'        => ['id', 'type', 'pid', 'name', 'title', 'path', 'auth_code', 'order', 'status'],
        'delete'        => ['id'],
        'info'          => ['id'],
        'check_exist'   => ['check_type'],
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'         => 'required',
            'order'         => 'required',
            'badge_type'    => 'sometimes|in:' . SysMenuConst::BADGE_TYPE_DOT . ',' . SysMenuConst::BADGE_TYPE_NORMAL,
            'badge'         => 'require_if:badge_type,' . SysMenuConst::BADGE_TYPE_NORMAL,
            'badge_variants' => ['sometimes',Rule::notIn(SysMenuConst::BADGE_VARIANTS_LIST)],
            'id'           => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $detail = SysMenuModel::find($value);
                    if (empty($detail)) throw new AppException('菜单不存在');
                    return true;
                }
            ],
            'type'         => ['required',Rule::in(SysMenuConst::MENU_TYPE_LIST)],
            'pid'          => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $data = $this->all();
                    if ($value == 0) {
                        if (empty($data['icon'])) throw new AppException('请输入图标');
                        if ($data['type'] == SysMenuConst::MENU_TYPE_BUTTON) throw new AppException('一级菜单不能为按钮');
                        return true;
                    }

                    $sub_menus = SysMenuModel::find($value);
                    if (empty($sub_menus)) throw new AppException('上级菜单不存在');
                    $sub_menus = $sub_menus->toArray();
                    // 按钮 不能添加子菜单
                    if ($sub_menus['type'] == SysMenuConst::MENU_TYPE_BUTTON) {
                        throw new AppException('上级菜单为按钮，不支持添加子菜单');
                    }

                    // 目录、内嵌或外链 不能添加按钮
                    if ($sub_menus['type'] != SysMenuConst::MENU_TYPE_MENU && $data['type'] == SysMenuConst::MENU_TYPE_BUTTON) {
                        throw new AppException('上级菜单为目录或内嵌或外链，不支持添加按钮');
                    }

                    return true;
                }
            ],
            'name'         => [
                'required',
                'string',
                'alpha_num',
                'max:50',
                function ($attribute, $value, $fail) {
                    $data = $this->all();
                    $map = [];
                    if (isset($data['id'])) $map[] = ['id', '<>', $data['id']];
                    $map[] = ['name', '=', $value];
                    $count = SysMenuModel::where($map)->count();
                    return $count === 0;
                }
            ],
            'path'         => [
                'sometimes',
                'string',
                'max:100',
                function ($attribute, $value, $fail) {
                    $data = $this->all();
                    if ($data['type'] == SysMenuConst::MENU_TYPE_BUTTON || $data['type'] == SysMenuConst::MENU_TYPE_LINK) {
                        return true;
                    }
                    if (empty($value)) return false;
                    return true;
                }
            ],
            'status'       => 'required|in:1,2',
            'auth_code'    => 'required_if:type,'.SysMenuConst::MENU_TYPE_BUTTON.'|string|max:100',
            'check_type'    => [
                'required',
                'in:1,2',
                function ($attribute, $value, $fail) {

                    $data = $this->all();
                    $map = [];
                    if (isset($data['id'])) $map[] = ['id', '<>', $data['id']];
                    if ($value == 1) {
                        if (empty($data['name'])) throw new AppException('请输入菜单名称');
                        $map[] = ['name', '=', $data['name']];
                    } else {
                        if (empty($data['path'])) throw new AppException('请输入菜单路径');
                        $map[] = ['path', '=', $data['path']];
                        // 验证除按钮和外链, 以外的菜单类型
                        $map[] = ['type', 'not in', [SysMenuConst::MENU_TYPE_BUTTON, SysMenuConst::MENU_TYPE_LINK]];
                    }
                    $count = SysMenuModel::where($map)->count();
                    if($count > 0){
                        throw new AppException($value == 1 ? '菜单名称已存在' :'菜单路径已存在');
                    }

                    return true;
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'id.required'              => '菜单ID不能为空!',
            'id.integer'               => '菜单ID格式错误!',
            'name.required'            => '菜单名称不能为空!',
            'name.string'              => '菜单名称格式错误!',
            'name.max'                 => '菜单名称长度不能超过50!',
            'path.string'              => '菜单路径格式错误!',
            'path.max'                 => '菜单路径长度不能超过100!',
            'status.required'        => '请输入状态',
            'status.in'                => '状态格式错误!',
            'auth_code.requireIf'   => '请输入权限码',
            'auth_code.string'         => '权限码格式错误!',
            'auth_code.max'            => '权限码长度不能超过100!',
            'type.required'            => '菜单类型不能为空!',
            'type.in'                  => '菜单类型格式错误',
            'pid.required'           => '请输入上级菜单ID',
            'pid.integer'           => '父级ID格式错误!',
            'name.alpha_num'         => '菜单名称只能包含字母、数字',
            'title.required'         => '请输入标题',
            'path.required'          => '请输入菜单路径',
            'order.required'         => '请输入排序',
            'badge_type.in'         => '徽标类型错误',
            'badge.require_if'       => '请输入徽标',
            'check_type.required'    => '请输入校验类型',
            'check_type.in'         => '校验类型有误',
        ];
    }
}
