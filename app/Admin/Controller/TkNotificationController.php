<?php

declare(strict_types=1);

namespace App\Admin\Controller;

use App\Admin\Service\TkNotificationService;
use App\Admin\Validate\TkNotificationRequest;
use App\Common\Controller\AbstractController;
use App\Common\Exception\AppException;
use App\Common\Filter\TkNotificationFilter;
use App\Common\Utils\Result;
use Hyperf\Di\Annotation\Inject;

class TkNotificationController extends AbstractController
{
    #[Inject]
    protected TkNotificationService $notificationService;

    public function getList(): array
    {
        $params = $this->request->all();
        $this->filter(TkNotificationFilter::class, $params, 'list');
        $data = $this->notificationService->getList($params);
        return Result::success($data);
    }

    public function getInfo(): array
    {
        $id = $this->request->input('id');
        if (empty($id)) {
            throw new AppException('通知ID不能为空');
        }

        $data = $this->notificationService->getInfoById((int) $id);
        if (!$data) {
            throw new AppException('通知不存在');
        }

        return Result::success($data);
    }

    public function add(): array
    {
        $this->check(TkNotificationRequest::class, 'add');
        $params = $this->request->all();
        $this->filter(TkNotificationFilter::class, $params, 'add');
        $this->notificationService->create($params);
        return Result::success();
    }

    public function markRead(): array
    {
        $this->check(TkNotificationRequest::class, 'mark_read');
        $id = $this->request->input('id');
        $this->notificationService->markAsRead((int) $id);
        return Result::success();
    }

    public function markAllRead(): array
    {
        $userId = $this->request->input('user_id');
        if (empty($userId)) {
            throw new AppException('用户ID不能为空');
        }

        $count = $this->notificationService->markAllAsRead((int) $userId);
        return Result::success(['count' => $count]);
    }

    public function delete(): array
    {
        $this->check(TkNotificationRequest::class, 'delete');
        $id = $this->request->input('id');
        $this->notificationService->delete((int) $id);
        return Result::success();
    }

    public function getUnreadCount(): array
    {
        $userId = $this->request->input('user_id');
        if (empty($userId)) {
            throw new AppException('用户ID不能为空');
        }

        $count = $this->notificationService->getUnreadCount((int) $userId);
        return Result::success(['count' => $count]);
    }
}
