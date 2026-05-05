<?php

declare(strict_types=1);

namespace App\Admin\Controller;

use App\Admin\Service\SysRoleService;
use App\Admin\Validate\SysRoleRequest;
use App\Common\Controller\AbstractController;
use App\Common\Exception\AppException;
use App\Common\Filter\SysRoleFilter;
use App\Common\Utils\Result;
use Hyperf\Di\Annotation\Inject;

class SysRoleController extends AbstractController
{
    #[Inject]
    protected SysRoleService $roleService;

    public function getList(): array
    {
        $params = $this->request->all();
        $data = $this->roleService->getList($params);
        return Result::success($data);
    }

    public function getInfo(): array
    {
        $id = $this->request->input('id');
        if (empty($id)) {
            throw new AppException('角色ID不能为空');
        }

        $data = $this->roleService->getInfoById((int) $id);
        if (!$data) {
            throw new AppException('角色不存在');
        }

        return Result::success($data);
    }

    public function add(): array
    {
        $this->check(SysRoleRequest::class, 'add');
        $params = $this->request->all();
        $this->filter(SysRoleFilter::class, $params, 'add');
        $this->roleService->create($params);
        return Result::success();
    }

    public function update(): array
    {
        $this->check(SysRoleRequest::class, 'update');
        $params = $this->request->all();
        $this->filter(SysRoleFilter::class, $params, 'update');
        $this->roleService->update($params);
        return Result::success();
    }

    public function updateStatus(): array
    {
        $this->check(SysRoleRequest::class, 'update_status');
        $params = $this->request->all();
        $this->filter(SysRoleFilter::class, $params, 'update_status');
        $this->roleService->updateStatus($params);
        return Result::success();
    }

    public function delete(): array
    {
        $id = $this->request->input('id');
        if (empty($id)) {
            throw new AppException('角色ID不能为空');
        }

        $this->roleService->delete((int) $id);
        return Result::success();
    }


    public function getAll(): array
    {
        $data = $this->roleService->getAllRoles();
        return Result::success($data);
    }
}
