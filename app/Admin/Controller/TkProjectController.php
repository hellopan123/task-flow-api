<?php

declare(strict_types=1);

namespace App\Admin\Controller;

use App\Admin\Service\TkProjectService;
use App\Admin\Validate\TkProjectRequest;
use App\Common\Controller\AbstractController;
use App\Common\Exception\AppException;
use App\Common\Filter\TkProjectFilter;
use App\Common\Utils\Result;
use Hyperf\Di\Annotation\Inject;

class TkProjectController extends AbstractController
{
    #[Inject]
    protected TkProjectService $projectService;

    public function getList(): array
    {
        $params = $this->request->all();
        $this->filter(TkProjectFilter::class, $params, 'list');
        $data = $this->projectService->getList($params);
        return Result::success($data);
    }

    public function getInfo(): array
    {
        $id = $this->request->input('id');
        if (empty($id)) {
            throw new AppException('项目ID不能为空');
        }

        $data = $this->projectService->getInfoById((int) $id);
        if (!$data) {
            throw new AppException('项目不存在');
        }

        return Result::success($data);
    }

    public function add(): array
    {
        $this->check(TkProjectRequest::class, 'add');
        $params = $this->request->all();
        $this->filter(TkProjectFilter::class, $params, 'add');
        $this->projectService->create($params);
        return Result::success();
    }

    public function update(): array
    {
        $this->check(TkProjectRequest::class, 'update');
        $params = $this->request->all();
        $this->filter(TkProjectFilter::class, $params, 'update');
        $this->projectService->update($params);
        return Result::success();
    }

    public function updateStatus(): array
    {
        $this->check(TkProjectRequest::class, 'update_status');
        $params = $this->request->all();
        $this->filter(TkProjectFilter::class, $params, 'update_status');
        $this->projectService->updateStatus($params);
        return Result::success();
    }

    public function updateProgress(): array
    {
        $this->check(TkProjectRequest::class, 'update_progress');
        $params = $this->request->all();
        $this->filter(TkProjectFilter::class, $params, 'update_progress');
        $this->projectService->updateProgress($params);
        return Result::success();
    }

    public function delete(): array
    {
        $this->check(TkProjectRequest::class, 'delete');
        $id = $this->request->input('id');
        $this->projectService->delete((int) $id);
        return Result::success();
    }

    public function getAll(): array
    {
        $data = $this->projectService->getAllProjects();
        return Result::success($data);
    }
}
