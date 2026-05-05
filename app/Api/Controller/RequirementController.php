<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Api\Filter\RequirementFilter;
use App\Api\Service\RequirementService;
use App\Api\Validate\RequirementRequest;
use App\Common\Controller\AbstractController;
use App\Common\Exception\AppException;
use App\Common\Utils\Result;
use Hyperf\Di\Annotation\Inject;

class RequirementController extends AbstractController
{
    #[Inject]
    protected RequirementService $requirementService;

    public function getList(): array
    {
        $params = $this->request->all();
        $this->filter(RequirementFilter::class, $params, 'list');
        $data = $this->requirementService->getList($params);
        return Result::success($data);
    }

    public function getByProject(): array
    {
        $this->check(RequirementRequest::class, 'by_project');
        $projectId = (int) $this->request->input('project_id');
        $params = $this->request->all();
        $data = $this->requirementService->getByProject($projectId, $params);
        return Result::success($data);
    }

    /**
     * Desc: 获取详情
     * Auth: hello pan
     * Date: 4/4/26 8:23 PM
     * @return array
     * @throws AppException
     */
    public function getDetail(): array
    {
        $this->check(RequirementRequest::class, 'detail');
        $id = (int) $this->request->input('id');
        
        $data = $this->requirementService->getDetail($id);
        if (!$data) {
            throw new AppException('需求不存在');
        }
        
        return Result::success($data);
    }

    /**
     * Desc: 添加需求操作
     * Auth: hello pan
     * Date: 4/4/26 4:36 PM
     * @return array
     * @throws AppException
     */
    public function create(): array
    {
        $this->check(RequirementRequest::class, 'create');
        $params = $this->request->all();
        $this->filter(RequirementFilter::class, $params, 'create');
        
        $data = $this->requirementService->create($params);
        return Result::success($data);
    }

    public function update(): array
    {
        $this->check(RequirementRequest::class, 'update');
        $params = $this->request->all();
        $this->filter(RequirementFilter::class, $params, 'update');
        
        $this->requirementService->update($params);
        return Result::success();
    }

    public function updateStatus(): array
    {
        $this->check(RequirementRequest::class, 'update_status');
        $params = $this->request->all();
        $this->filter(RequirementFilter::class, $params, 'update_status');
        
        $this->requirementService->updateStatus($params['id'], $params['status']);
        return Result::success();
    }

    public function delete(): array
    {
        $this->check(RequirementRequest::class, 'delete');
        $id = (int) $this->request->input('id');

        $this->requirementService->delete($id);
        return Result::success();
    }

    // ========== 需求导出 ==========
    public function export(): array
    {
        $requirementId = (int) $this->request->input('requirement_id');
        $exportType = $this->request->input('export_type', 'excel');

        $filePath = $this->requirementService->export($requirementId, $exportType);

        return Result::success(['file_path' => $filePath]);
    }

    public function getExportRecords(): array
    {
        $requirementId = $this->request->input('requirement_id');

        $data = $this->requirementService->getExportRecords($requirementId);
        return Result::success($data);
    }
}
