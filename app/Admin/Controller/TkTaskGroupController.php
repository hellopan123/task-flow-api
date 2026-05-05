<?php

declare(strict_types=1);

namespace App\Admin\Controller;

use App\Admin\Service\TkTaskGroupService;
use App\Admin\Validate\TkTaskGroupRequest;
use App\Common\Controller\AbstractController;
use App\Common\Exception\AppException;
use App\Common\Filter\TkTaskGroupFilter;
use App\Common\Utils\Result;
use Hyperf\Di\Annotation\Inject;

class TkTaskGroupController extends AbstractController
{
    #[Inject]
    protected TkTaskGroupService $groupService;

    public function getList(): array
    {
        $params = $this->request->all();
        $this->filter(TkTaskGroupFilter::class, $params, 'list');
        $data = $this->groupService->getList($params);
        return Result::success($data);
    }

    public function getInfo(): array
    {
        $id = $this->request->input('id');
        if (empty($id)) {
            throw new AppException('分组ID不能为空');
        }

        $data = $this->groupService->getInfoById((int) $id);
        if (!$data) {
            throw new AppException('分组不存在');
        }

        return Result::success($data);
    }

    public function add(): array
    {
        $this->check(TkTaskGroupRequest::class, 'add');
        $params = $this->request->all();
        $this->filter(TkTaskGroupFilter::class, $params, 'add');
        $this->groupService->create($params);
        return Result::success();
    }

    public function update(): array
    {
        $this->check(TkTaskGroupRequest::class, 'update');
        $params = $this->request->all();
        $this->filter(TkTaskGroupFilter::class, $params, 'update');
        $this->groupService->update($params);
        return Result::success();
    }

    public function updateStatus(): array
    {
        $this->check(TkTaskGroupRequest::class, 'update_status');
        $params = $this->request->all();
        $this->filter(TkTaskGroupFilter::class, $params, 'update_status');
        $this->groupService->updateStatus($params);
        return Result::success();
    }

    public function delete(): array
    {
        $this->check(TkTaskGroupRequest::class, 'delete');
        $id = $this->request->input('id');
        $this->groupService->delete((int) $id);
        return Result::success();
    }

    public function getAll(): array
    {
        $data = $this->groupService->getAllGroups();
        return Result::success($data);
    }
}
