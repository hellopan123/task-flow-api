<?php

declare(strict_types=1);

namespace App\Api\Event;

use Hyperf\Contract\Arrayable;

class TaskAssignedEvent implements Arrayable
{
    public int $taskId;
    public int $operatorId;
    public array $newAssigneeIds;
    public array $oldAssigneeIds;
    public string $title;
    public int $timestamp;

    public function __construct(
        int $taskId,
        int $operatorId,
        array $newAssigneeIds,
        array $oldAssigneeIds,
        string $title
    ) {
        $this->taskId = $taskId;
        $this->operatorId = $operatorId;
        $this->newAssigneeIds = $newAssigneeIds;
        $this->oldAssigneeIds = $oldAssigneeIds;
        $this->title = $title;
        $this->timestamp = time();
    }

    public function toArray(): array
    {
        return [
            'task_id' => $this->taskId,
            'operator_id' => $this->operatorId,
            'new_assignee_ids' => $this->newAssigneeIds,
            'old_assignee_ids' => $this->oldAssigneeIds,
            'title' => $this->title,
            'timestamp' => $this->timestamp,
        ];
    }
}
