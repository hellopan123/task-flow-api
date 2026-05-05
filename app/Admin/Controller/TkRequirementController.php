<?php

declare(strict_types=1);

namespace App\Admin\Controller;

use App\Admin\Service\TkRequirementService;
use App\Admin\Validate\TkRequirementRequest;
use App\Common\Controller\AbstractController;
use App\Common\Exception\AppException;
use App\Common\Filter\TkRequirementFilter;
use App\Common\Utils\Result;
use Hyperf\Di\Annotation\Inject;

class TkRequirementController extends AbstractController
{
    #[Inject]
    protected TkRequirementService $requirementService;

    public function getList(): array
    {
        $params = $this->request->all();
        $this->filter(TkRequirementFilter::class, $params, 'list');
        $data = $this->requirementService->getList($params);
        return Result::success($data);
    }

    public function getInfo(): array
    {
        $id = $this->request->input('id');
        if (empty($id)) {
            throw new AppException('需求ID不能为空');
        }

        $data = $this->requirementService->getInfoById((int) $id);
        if (!$data) {
            throw new AppException('需求不存在');
        }

        return Result::success($data);
    }

    public function add(): array
    {
        $this->check(TkRequirementRequest::class, 'add');
        $params = $this->request->all();
        $this->filter(TkRequirementFilter::class, $params, 'add');
        $this->requirementService->create($params);
        return Result::success();
    }

    public function update(): array
    {
        $this->check(TkRequirementRequest::class, 'update');
        $params = $this->request->all();
        $this->filter(TkRequirementFilter::class, $params, 'update');
        $this->requirementService->update($params);
        return Result::success();
    }

    public function updateStatus(): array
    {
        $this->check(TkRequirementRequest::class, 'update_status');
        $params = $this->request->all();
        $this->filter(TkRequirementFilter::class, $params, 'update_status');
        $this->requirementService->updateStatus($params);
        return Result::success();
    }

    public function delete(): array
    {
        $this->check(TkRequirementRequest::class, 'delete');
        $id = $this->request->input('id');
        $this->requirementService->delete((int) $id);
        return Result::success();
    }

    public function getByProject(): array
    {
        $projectId = $this->request->input('project_id');
        if (empty($projectId)) {
            throw new AppException('项目ID不能为空');
        }

        $data = $this->requirementService->getByProjectId((int) $projectId);
        return Result::success($data);
    }
}
