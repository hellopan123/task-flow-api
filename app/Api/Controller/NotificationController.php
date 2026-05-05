<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Api\Filter\NotificationFilter;
use App\Api\Service\NotificationService;
use App\Api\Validate\NotificationRequest;
use App\Common\Controller\AbstractController;
use App\Common\Exception\AppException;
use App\Common\Utils\Result;
use Hyperf\Di\Annotation\Inject;

class NotificationController extends AbstractController
{
    #[Inject]
    protected NotificationService $notificationService;

    /**
     * Desc: 获取列表
     * Auth: hello pan
     * Date: 5/2/26 PM3:50
     * @return array
     * @throws AppException
     */
    public function getList(): array
    {
        $params = $this->request->all();
        $this->filter(NotificationFilter::class, $params, 'list');
        $data = $this->notificationService->getList($params);
        return Result::success($data);
    }

    /**
     * Desc: 获取未读数量
     * Auth: hello pan
     * Date: 5/2/26 PM3:50
     * @return array
     */
    public function getUnreadCount(): array
    {
        $count = $this->notificationService->getUnreadCount();
        return Result::success(['count' => $count]);
    }

    /**
     * Desc: 已读操作
     * Auth: hello pan
     * Date: 5/2/26 PM3:50
     * @return array
     * @throws AppException
     */
    public function markRead(): array
    {
        $this->check(NotificationRequest::class, 'mark_read');
        $id = (int) $this->request->input('id');
        
        $this->notificationService->markRead($id);
        return Result::success();
    }

    /**
     * Desc: 全部标记已读
     * Auth: hello pan
     * Date: 5/2/26 PM3:50
     * @return array
     */
    public function markAllRead(): array
    {
        $count = $this->notificationService->markAllRead();
        return Result::success(['count' => $count]);
    }

    /**
     * Desc: 删除操作
     * Auth: hello pan
     * Date: 5/2/26 PM3:51
     * @return array
     * @throws AppException
     */
    public function delete(): array
    {
        $this->check(NotificationRequest::class, 'delete');
        $id = (int) $this->request->input('id');
        
        $this->notificationService->delete($id);
        return Result::success();
    }
}
