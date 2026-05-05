<?php

declare(strict_types=1);

namespace App\Api\Listener;

use App\Api\Event\TaskAssignedEvent;
use App\Common\Model\TkNotificationModel;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Psr\Log\LoggerInterface;

#[Listener]
class TaskAssignedListener implements ListenerInterface
{
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function listen(): array
    {
        return [
            TaskAssignedEvent::class,
        ];
    }

    public function process(object $event): void
    {
        if (!$event instanceof TaskAssignedEvent) {
            return;
        }

        $newAssigneeIds = array_diff($event->newAssigneeIds, $event->oldAssigneeIds);

        var_dump($newAssigneeIds);
        if (empty($newAssigneeIds)) {
            return;
        }

        $notifications = [];
        $now = date('Y-m-d H:i:s');

        foreach ($newAssigneeIds as $assigneeId) {
            if ($assigneeId === $event->operatorId) {
                continue;
            }

            $notifications[] = [
                'user_id' => $assigneeId,
                'notification_type' => '1',
                'title' => '任务分配通知',
                'content' => "您被分配了新任务：{$event->title}",
                'related_id' => $event->taskId,
                'related_type' => 'task',
                'is_read' => 2,
                'create_time' => $now,
            ];
        }

        if (!empty($notifications)) {
            TkNotificationModel::insert($notifications);
            $this->logger->info('TaskAssignedEvent: 创建通知成功', [
                'task_id' => $event->taskId,
                'new_assignee_count' => count($newAssigneeIds),
            ]);
        }
    }
}
