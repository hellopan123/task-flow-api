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

use App\Admin\Service\AdminService;
use App\Admin\Validate\SysAdminRequest;
use App\Common\Controller\AbstractController;
use App\Common\Exception\AppException;
use App\Common\Filter\SysAdminFilter;
use App\Common\Utils\Result;
use Hyperf\Context\Context;
use Hyperf\Di\Annotation\Inject;

class SysAdminController extends AbstractController
{
    #[Inject]
    protected AdminService $adminService;

    /**
     * Desc: 获取列表
     * Auth: hello pan
     * Date: 2/14/26 8:03 PM
     * @return array
     */
    public function getList(): array
    {
        $params = $this->request->all();
        $data = $this->adminService->getList($params);
        return Result::success($data);
    }


    /**
     * Desc: 获取用户信息
     * Auth: hello pan
     * Date: 2/14/26 10:29 PM
     * @return array
     * @throws AppException
     */
    public function getInfo(): array
    {
        $id = $this->request->input('id');
        if (empty($id)) {
            throw new AppException('管理员ID不能为空');
        }

        $data = $this->adminService->getInfoById((int) $id);
        if (! $data) {
            throw new AppException('管理员不存在');
        }

        return Result::success($data);
    }

    /**
     * Desc: 添加操作
     * Auth: hello pan
     * Date: 2/14/26 9:40 PM
     * @return array
     */
    public function add(): array
    {
        $this->check(SysAdminRequest::class,'add');
        $params = $this->request->all();
        $this->filter(SysAdminFilter::class,$params,'add');
        $this->adminService->create($params);
        return Result::success();
    }

    /**
     * Desc: 更新操作
     * Auth: hello pan
     * Date: 2/14/26 10:00 PM
     * @return array
     * @throws AppException
     */
    public function update(): array
    {
        $params = $this->request->all();

        $this->check(SysAdminRequest::class, 'update');

        $this->filter(SysAdminFilter::class, $params, 'update');

        $this->adminService->update($params);
        return Result::success();
    }


    /**
     * Desc: 更新状态
     * Auth: hello pan
     * Date: 2/14/26 10:29 PM
     * @return array
     * @throws AppException
     */
    public function updateStatus(): array
    {
        $params = $this->request->all();
        // 验证参数
        $this->check(SysAdminRequest::class,'update_status');

        $this->filter(SysAdminFilter::class,$params,'update_status');
        // 更新状态
        $this->adminService->updateStatus($params);
        return Result::success();
    }

    /**
     * Desc: 软删除
     * Auth: hello pan
     * Date: 2/14/26 10:29 PM
     * @return array
     * @throws AppException
     */
    public function delete(): array
    {
        $id = $this->request->input('id');
        if (empty($id)) {
            throw new AppException('管理员ID不能为空');
        }

        $this->adminService->delete((int) $id);
        return Result::success();
    }
}
