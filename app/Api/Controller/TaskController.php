<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Api\Filter\TaskFilter;
use App\Api\Service\TaskService;
use App\Api\Validate\TaskRequest;
use App\Common\Controller\AbstractController;
use App\Common\Exception\AppException;
use App\Common\Utils\Result;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;

class TaskController extends AbstractController
{
    #[Inject]
    protected TaskService $taskService;

    #[Inject]
    protected RequestInterface $request;


    /**
     * Desc: 获取用户任务统计
     * Auth: hello pan
     * Date: 3/29/26 8:37 PM
     */
    public function getTaskStats()
    {
        $this->check(TaskRequest::class, 'user_task_stats');
        $owner_id = (int) $this->request->input('owner_id');
        $data = $this->taskService->getTaskStats($owner_id);
        return Result::success($data);
    }
    /**
     * Desc: 获取任务列表
     * Auth: hello pan
     * Date: 5/2/26 PM4:18
     * @return array
     */
    public function getTaskListStats(): array
    {
        $params = $this->request->all();
        $this->filter(TaskFilter::class, $params, 'list');
        $data = $this->taskService->getTaskListStats($params);
        return Result::success($data);
    }

    /**
     * Desc: 获取任务列表
     * Auth: hello pan
     * Date: 5/2/26 PM4:18
     * @return array
     */
    public function getList(): array
    {
        $params = $this->request->all();
        $this->filter(TaskFilter::class, $params, 'list');
        $data = $this->taskService->getList($params);
        return Result::success($data);
    }

    /**
     * Desc: 获取我的任务
     * Auth: hello pan
     * Date: 5/2/26 PM4:18
     * @return array
     */
    public function getMyTasks(): array
    {
        $params = $this->request->all();
        $data = $this->taskService->getMyTasks($params);
        return Result::success($data);
    }

    public function getDetail(): array
    {
        $this->check(TaskRequest::class, 'detail');
        $id = (int) $this->request->input('id');

        $data = $this->taskService->getDetail($id);
        if (!$data) {
            throw new AppException('任务不存在');
        }

        return Result::success($data);
    }

    public function create(): array
    {
        $this->check(TaskRequest::class, 'create');
        $params = $this->request->all();
        $this->filter(TaskFilter::class, $params, 'create');

        $data = $this->taskService->create($params);
        return Result::success($data);
    }

    public function batchCreate(): array
    {
        $this->check(TaskRequest::class, 'batch_create');
        $params = $this->request->all();
        $this->filter(TaskFilter::class, $params, 'batch_create');

        $count = $this->taskService->batchCreate($params['tasks']);
        return Result::success(['count' => $count]);
    }

    public function update(): array
    {
        $this->check(TaskRequest::class, 'update');
        $params = $this->request->all();
        $this->filter(TaskFilter::class, $params, 'update');

        $this->taskService->update($params);
        return Result::success();
    }

    public function updateStatus(): array
    {
        $this->check(TaskRequest::class, 'update_status');
        $params = $this->request->all();
        $this->filter(TaskFilter::class, $params, 'update_status');

        $this->taskService->updateStatus($params['id'], $params['status'],$params['remark'] ?? '');
        return Result::success();
    }


    public function delete(): array
    {
        $this->check(TaskRequest::class, 'delete');
        $id = (int) $this->request->input('id');

        $this->taskService->delete($id);
        return Result::success();
    }

    /**
     * Desc: 分派任务
     * Auth: hello pan
     * Date: 4/6/26 8:36 PM
     * @return array
     * @throws AppException
     */
    public function assign(): array
    {
        $this->check(TaskRequest::class, 'assign');
        $params = $this->request->all();

        $this->taskService->assign($params['id'], $params['owner_id']);
        return Result::success();
    }

    public function batchAssign(): array
    {
        $this->check(TaskRequest::class, 'batch_assign');
        $params = $this->request->all();

        $count = $this->taskService->batchAssign($params['task_ids'], $params['owner_id']);
        return Result::success(['count' => $count]);
    }

    public function getMetrics(): array
    {
        $data = $this->taskService->getMetrics();
        return Result::success($data);
    }

    // ========== 任务日志 ==========
    public function getLogs(): array
    {
        $taskId = (int) $this->request->input('task_id');
        $data = $this->taskService->getLogs($taskId);
        return Result::success($data);
    }

    // ========== 任务附件 ==========
    public function getAttachments(): array
    {
        $taskId = (int) $this->request->input('task_id');
        $data = $this->taskService->getAttachments($taskId);
        return Result::success($data);
    }

    /**
     * Desc: 上传任务附件
     * Auth: hello pan
     * Date: 4/25/26 PM10:24
     * @return array
     */
    public function uploadAttachment(): array
    {
        $taskId = (int) $this->request->input('task_id');
        $file = $this->request->file('file');

        $attachment = $this->taskService->uploadAttachment($taskId, $file);
        return Result::success($attachment);
    }

    /**
     * Desc: 删除任务附件
     * Auth: hello pan
     * Date: 4/25/26 PM10:25
     * @return array
     * @throws AppException
     */
    public function deleteAttachment(): array
    {
        $id = (int) $this->request->input('id');
        $this->taskService->deleteAttachment($id);
        return Result::success(['success' => true]);
    }

    // ========== 任务评论 ==========
    public function getComments(): array
    {
        $taskId = (int) $this->request->input('task_id');
        $data = $this->taskService->getComments($taskId);
        return Result::success($data);
    }

    public function createComment(): array
    {
        $params = $this->request->all();
        $comment = $this->taskService->createComment($params);
        return Result::success($comment);
    }

    public function deleteComment(): array
    {
        $id = (int) $this->request->input('id');
        $this->taskService->deleteComment($id);
        return Result::success(['success' => true]);
    }

    // ========== 任务导入 ==========
    public function importPreview(): array
    {
        $file = $this->request->file('file');
        $data = $this->taskService->importPreview($file);
        return Result::success($data);
    }

    public function importExecute(): array
    {
        $params = $this->request->all();
        $result = $this->taskService->importExecute($params);
        return Result::success($result);
    }

    public function getImportRecords(): array
    {
        $requirementId = $this->request->input('requirement_id');
        $data = $this->taskService->getImportRecords($requirementId);
        return Result::success($data);
    }
}
