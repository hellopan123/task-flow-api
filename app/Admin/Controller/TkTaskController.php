<?php

declare(strict_types=1);

namespace App\Admin\Controller;

use App\Admin\Service\TkTaskService;
use App\Admin\Validate\TkTaskRequest;
use App\Common\Controller\AbstractController;
use App\Common\Exception\AppException;
use App\Common\Filter\TkTaskFilter;
use App\Common\Utils\Result;
use Hyperf\Di\Annotation\Inject;

class TkTaskController extends AbstractController
{
    #[Inject]
    protected TkTaskService $taskService;

    public function getList(): array
    {
        $params = $this->request->all();
        $this->filter(TkTaskFilter::class, $params, 'list');
        $data = $this->taskService->getList($params);
        return Result::success($data);
    }

    public function getInfo(): array
    {
        $id = $this->request->input('id');
        if (empty($id)) {
            throw new AppException('任务ID不能为空');
        }

        $data = $this->taskService->getInfoById((int) $id);
        if (!$data) {
            throw new AppException('任务不存在');
        }

        return Result::success($data);
    }

    public function add(): array
    {
        $this->check(TkTaskRequest::class, 'add');
        $params = $this->request->all();
        $this->filter(TkTaskFilter::class, $params, 'add');
        $this->taskService->create($params);
        return Result::success();
    }

    public function update(): array
    {
        $this->check(TkTaskRequest::class, 'update');
        $params = $this->request->all();
        $this->filter(TkTaskFilter::class, $params, 'update');
        $this->taskService->update($params);
        return Result::success();
    }

    public function updateStatus(): array
    {
        $this->check(TkTaskRequest::class, 'update_status');
        $params = $this->request->all();
        $this->filter(TkTaskFilter::class, $params, 'update_status');
        $this->taskService->updateStatus($params);
        return Result::success();
    }

    public function updateProgress(): array
    {
        $this->check(TkTaskRequest::class, 'update_progress');
        $params = $this->request->all();
        $this->filter(TkTaskFilter::class, $params, 'update_progress');
        $this->taskService->updateProgress($params);
        return Result::success();
    }

    public function delete(): array
    {
        $this->check(TkTaskRequest::class, 'delete');
        $id = $this->request->input('id');
        $this->taskService->delete((int) $id);
        return Result::success();
    }

    public function getByGroup(): array
    {
        $groupId = $this->request->input('group_id');
        if (empty($groupId)) {
            throw new AppException('分组ID不能为空');
        }

        $data = $this->taskService->getByGroupId((int) $groupId);
        return Result::success($data);
    }

    public function getByRequirement(): array
    {
        $requirementId = $this->request->input('requirement_id');
        if (empty($requirementId)) {
            throw new AppException('需求ID不能为空');
        }

        $data = $this->taskService->getByRequirementId((int) $requirementId);
        return Result::success($data);
    }
}
