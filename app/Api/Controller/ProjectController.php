<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Api\Filter\ProjectFilter;
use App\Api\Service\ProjectService;
use App\Api\Validate\ProjectRequest;
use App\Common\Controller\AbstractController;
use App\Common\Exception\AppException;
use App\Common\Utils\Result;
use Hyperf\Di\Annotation\Inject;

class ProjectController extends AbstractController
{
    #[Inject]
    protected ProjectService $projectService;


    /**
     * Desc: 获取项目列表
     * Auth: hello pan
     * Date: 2/28/26 10:19 PM
     * @return array
     */
    public function getDict(): array
    {
        $data = $this->projectService->getDict();
        return Result::success($data);
    }

    public function getList(): array
    {
        $params = $this->request->all();
        $this->filter(ProjectFilter::class, $params, 'list');
        $data = $this->projectService->getList($params);
        return Result::success($data);
    }

    public function getDetail(): array
    {
        $this->check(ProjectRequest::class, 'detail');
        $id = (int) $this->request->input('id');
        
        $data = $this->projectService->getDetail($id);
        if (!$data) {
            throw new AppException('项目不存在');
        }
        
        return Result::success($data);
    }

    public function create(): array
    {
        $this->check(ProjectRequest::class, 'create');
        $params = $this->request->all();
        $this->filter(ProjectFilter::class, $params, 'create');
        
        $data = $this->projectService->create($params);
        return Result::success(['id'=>$data]);
    }

    public function update(): array
    {
        $this->check(ProjectRequest::class, 'update');
        $params = $this->request->all();
        $this->filter(ProjectFilter::class, $params, 'update');
        
        $this->projectService->update($params);
        return Result::success();
    }

    public function delete(): array
    {
        $this->check(ProjectRequest::class, 'delete');
        $id = (int) $this->request->input('id');
        
        $this->projectService->delete($id);
        return Result::success();
    }

    /**
     * Desc: 添加项目成员
     * Auth: hello pan
     * Date: 4/4/26 3:24 PM
     * @return array
     * @throws AppException
     */
    public function addMembers(): array
    {
        $this->check(ProjectRequest::class, 'add_members');
        $params = $this->request->all();
        $this->filter(ProjectFilter::class, $params, 'add_members');
        $this->projectService->addMembers((int)$params['id'], $params['member_ids']);
        return Result::success();
    }

    public function removeMember(): array
    {
        $this->check(ProjectRequest::class, 'remove_member');
        $id = (int) $this->request->input('id');
        $memberId = (int) $this->request->input('member_id');
        
        $this->projectService->removeMember($id, $memberId);
        return Result::success();
    }

    public function getMembers(): array
    {
        $id = (int) $this->request->input('id');
        if (!$id) {
            throw new AppException('项目ID不能为空');
        }
        
        $data = $this->projectService->getMembers($id);
        return Result::success($data);
    }

    /**
     * Desc: 获取项目可添加成员列表
     * Auth: hello pan
     * Date: 4/4/26 3:24 PM
     * @return array
     * @throws AppException
     */
    public function getAvailableMembers(): array
    {
        $id = (int) $this->request->input('id');
        if (!$id) {
            throw new AppException('项目ID不能为空');
        }
        
        $data = $this->projectService->getAvailableMembers($id);
        return Result::success($data);
    }
}
