<?php

declare(strict_types=1);

namespace App\Admin\Service;

use App\Common\Exception\AppException;
use App\Common\Model\TkFaqModel;
use Psr\Log\LoggerInterface;

class TkFaqService
{
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getList(array $params = []): array
    {
        $where = [];
        
        if (!empty($params['question'])) {
            $where[] = ['question', 'like', '%' . $params['question'] . '%'];
        }
        
        if (!empty($params['category'])) {
            $where[] = ['category', '=', $params['category']];
        }
        
        if (isset($params['status']) && $params['status'] !== '') {
            $where[] = ['status', '=', (int) $params['status']];
        }
        
        $query = TkFaqModel::query()->where($where);
        
        $page = $params['page'] ?? 1;
        $pageSize = $params['pageSize'] ?? 15;
        
        $total = $query->count();
        $list = $query->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->orderBy('sort', 'asc')
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
        $faq = TkFaqModel::find($id);
        
        if (!$faq) {
            return null;
        }
        
        return $faq->toArray();
    }

    public function create(array $data): TkFaqModel
    {
        if (!isset($data['view_count'])) {
            $data['view_count'] = 0;
        }
        
        return TkFaqModel::create($data);
    }

    public function update(array $params): bool
    {
        $model = TkFaqModel::find($params['id']);
        
        if (!$model) {
            throw new AppException('FAQ不存在');
        }
        
        return $model->update($params) > 0;
    }

    public function updateStatus(array $params): bool
    {
        $model = TkFaqModel::find($params['id']);
        
        if (!$model) {
            throw new AppException('FAQ不存在');
        }
        
        return $model->update(['status' => $params['status']]) > 0;
    }

    public function delete(int $id): bool
    {
        $model = TkFaqModel::find($id);
        
        if (!$model) {
            throw new AppException('FAQ不存在');
        }
        
        return $model->delete() > 0;
    }

    public function incrementViewCount(int $id): bool
    {
        return TkFaqModel::where('id', $id)->increment('view_count') > 0;
    }

    public function getCategories(): array
    {
        return TkFaqModel::where('status', 1)
            ->select('category')
            ->distinct()
            ->pluck('category')
            ->toArray();
    }
}
