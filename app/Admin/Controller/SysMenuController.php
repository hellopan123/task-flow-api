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

namespace App\Admin\Controller;

use App\Admin\Service\SysMenuService;
use App\Admin\Validate\SysMenuRequest;
use App\Common\Controller\AbstractController;
use App\Common\Filter\SysMenuFilter;
use App\Common\Utils\Result;
use Hyperf\Di\Annotation\Inject;

class SysMenuController extends AbstractController
{
    #[Inject]
    protected SysMenuService $sysMenuService;

    /**
     * Desc: 获取菜单列表
     * Auth: hello pan
     * Date: 2/11/26 10:47 PM
     * @return array
     */
    public function getList(): array
    {
        return Result::success($this->sysMenuService->getList());
    }

    /**
     * Desc: 获取菜单详情
     * Auth: hello pan
     * Date: 2/15/26 10:47 PM
     * @return array
     */
    public function getInfo(): array
    {
        $this->check(SysMenuRequest::class, 'info');
        $id = (int) $this->request->input('id');
        return Result::success($this->sysMenuService->getInfo($id));
    }

    /**
     * Desc: 获取导航菜单
     * Auth: hello pan
     * Date: 2/15/26 10:47 PM
     * @return array
     */
    public function getNavMenu(): array
    {
        return Result::success($this->sysMenuService->getNavMenu());
    }

    /**
     * Desc: 添加菜单
     * Auth: hello pan
     * Date: 2/15/26 10:47 PM
     * @return array
     */
    public function add(): array
    {
        $params = $this->request->all();
        $this->check(SysMenuRequest::class, 'add');
        $this->filter(SysMenuFilter::class, $params, 'add');
        $id = $this->sysMenuService->add($params);
        return Result::success(['id' => $id]);
    }

    /**
     * Desc: 更新菜单
     * Auth: hello pan
     * Date: 2/15/26 10:47 PM
     * @return array
     */
    public function update(): array
    {
        $params = $this->request->all();
        $this->check(SysMenuRequest::class, 'update');
        $this->filter(SysMenuFilter::class, $params, 'update');
        $this->sysMenuService->update($params);
        return Result::success();
    }

    /**
     * Desc: 删除菜单
     * Auth: hello pan
     * Date: 2/15/26 10:47 PM
     * @return array
     */
    public function delete(): array
    {
        $this->check(SysMenuRequest::class, 'delete');
        $id = (int) $this->request->input('id');
        $this->sysMenuService->delete($id);
        return Result::success();
    }

    /**
     * 检查菜单是否存在
     * @title 检查菜单是否存在
     * @method post
     * @url /admin/menu/check_exist
     * @param check_type 必选 int 检查类型：1检查名称是否存在，2检查地址是否存在
     * @param id 可选 int 菜单ID
     * @param name 可选 string 菜单名
     * @param path 可选 string 地址
     *
     * @link
     */
    public function checkExist()
    {
        // 验证数据
        try {
            $this->check(SysMenuRequest::class, 'check_exist');
            return Result::success(['exist' => 2]);
        }catch (\Throwable $e){
            return Result::success(['exist' => 1]);
        }

    }
}
