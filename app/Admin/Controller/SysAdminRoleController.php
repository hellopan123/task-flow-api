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

use App\Admin\Service\SysAdminRoleService;
use App\Admin\Validate\SysAdminRoleRequest;
use App\Common\Controller\AbstractController;
use App\Common\Exception\AppException;
use App\Common\Filter\SysAdminRoleFilter;
use App\Common\Utils\Result;
use Hyperf\Di\Annotation\Inject;

class SysAdminRoleController extends AbstractController
{
    #[Inject]
    protected SysAdminRoleService $sysAdminRoleService;

    /**
     * Desc: 获取列表
     * Auth: hello pan
     * Date: 2/15/26 10:08 PM
     * @return array
     */
    public function getList(): array
    {
        $this->check(SysAdminRoleRequest::class,'list');

        $params = $this->request->all();

        $res = $this->sysAdminRoleService->getList($params);

        return Result::success($res);
    }

    /**
     * Desc: 获取详情
     * Auth: hello pan
     * Date: 2/15/26 10:08 PM
     * @return array
     * @throws AppException
     */
    public function getInfo(): array
    {
        $id = $this->request->input('id');
        if (empty($id)) {
            throw new AppException('角色ID不能为空');
        }
        return Result::success(['id' => $this->sysAdminRoleService->getInfoById($id)]);
    }

    /**
     * Desc: 添加操作
     * Auth: hello pan
     * Date: 2/15/26 10:19 PM
     * @return array
     * @throws AppException
     */
    public function add(): array
    {
        // 验证参数
        $params = $this->request->all();
        $this->check(SysAdminRoleRequest::class,'add');
        $this->filter(SysAdminRoleFilter::class,$params,'add');

        return Result::success(['id' => $this->sysAdminRoleService->add($params)]);
    }

    /**
     * Desc: 更新操作
     * Auth: hello pan
     * Date: 2/15/26 10:29 PM
     * @return array
     * @throws AppException
     */
    public function update(): array
    {
        $params = $this->request->all();
        $this->check(SysAdminRoleRequest::class,'update');
        $this->filter(SysAdminRoleFilter::class,$params,'update');
        $this->sysAdminRoleService->update($params);
        return Result::success();
    }


    /**
     * Desc: 更新状态
     * Auth: hello pan
     * Date: 2/15/26 10:29 PM
     * @return array
     * @throws \App\Common\Exception\ValidationException
     */
    public function updateStatus(): array
    {
        $params = $this->request->all();
        $this->check(SysAdminRoleRequest::class,'update_status');
        $this->filter(SysAdminRoleFilter::class,$params,'update_status');
        $this->sysAdminRoleService->update($params);

        return Result::success();
    }
}
