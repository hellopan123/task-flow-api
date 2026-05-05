<?php

declare(strict_types=1);

namespace App\Api\Listener;

use App\Api\Event\TaskStatusChangedEvent;
use App\Common\Model\TkNotificationModel;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Psr\Log\LoggerInterface;

#[Listener]
class TaskStatusChangedListener implements ListenerInterface
{
    protected LoggerInterface $logger;

    protected array $statusText = [
        1 => '待处理',
        2 => '进行中',
        3 => '已完成',
        4 => '已取消',
    ];

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function listen(): array
    {
        return [
            TaskStatusChangedEvent::class,
        ];
    }

    public function process(object $event): void
    {
        if (!$event instanceof TaskStatusChangedEvent) {
            return;
        }

        $oldStatusText = $this->statusText[$event->oldStatus] ?? '未知';
        $newStatusText = $this->statusText[$event->newStatus] ?? '未知';

        $notifications = [];
        $now = date('Y-m-d H:i:s');

        foreach ($event->assigneeIds as $assigneeId) {
            if ($assigneeId === $event->operatorId) {
                continue;
            }

            $notifications[] = [
                'user_id' => $assigneeId,
                'notification_type' => '1',
                'title' => '任务状态变更',
                'content' => "任务「{$event->title}」状态从「{$oldStatusText}」变更为「{$newStatusText}」",
                'related_id' => $event->taskId,
                'related_type' => 'task',
                'is_read' => 2,
                'create_time' => $now,
            ];
        }

        if (!empty($notifications)) {
            TkNotificationModel::insert($notifications);
            $this->logger->info('TaskStatusChangedEvent: 创建通知成功', [
                'task_id' => $event->taskId,
                'old_status' => $event->oldStatus,
                'new_status' => $event->newStatus,
            ]);
        }
    }
}
