<?php

declare(strict_types=1);

namespace App\Admin\Service;

use App\Common\Exception\AppException;
use App\Common\Model\TkFeedbackModel;
use Psr\Log\LoggerInterface;

class TkFeedbackService
{
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getList(array $params = []): array
    {
        $where = [];
        
        if (!empty($params['user_id'])) {
            $where[] = ['user_id', '=', (int) $params['user_id']];
        }
        
        if (isset($params['feedback_type']) && $params['feedback_type'] !== '') {
            $where[] = ['feedback_type', '=', (int) $params['feedback_type']];
        }
        
        if (isset($params['status']) && $params['status'] !== '') {
            $where[] = ['status', '=', (int) $params['status']];
        }
        
        $query = TkFeedbackModel::query()->where($where);
        
        $page = $params['page'] ?? 1;
        $pageSize = $params['pageSize'] ?? 15;
        
        $total = $query->count();
        $list = $query->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();
        
        return [
            'list' => $list,
            'total' => $total,
        ];
    }

    public function getInfoById(int $id): ?array
    {
        $feedback = TkFeedbackModel::find($id);
        
        if (!$feedback) {
            return null;
        }
        
        return $feedback->toArray();
    }

    public function create(array $data): TkFeedbackModel
    {
        return TkFeedbackModel::create($data);
    }

    public function reply(array $params): bool
    {
        $model = TkFeedbackModel::find($params['id']);
        
        if (!$model) {
            throw new AppException('反馈不存在');
        }
        
        return $model->update([
            'reply' => $params['reply'],
            'status' => 1,
            'reply_time' => date('Y-m-d H:i:s'),
            'reply_by' => $params['reply_by'] ?? 0,
        ]) > 0;
    }

    public function delete(int $id): bool
    {
        $model = TkFeedbackModel::find($id);
        
        if (!$model) {
            throw new AppException('反馈不存在');
        }
        
        return $model->delete() > 0;
    }
}
