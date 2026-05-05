<?php

declare(strict_types=1);

namespace App\Admin\Service;

use App\Common\Exception\AppException;
use App\Common\Model\SysDictItemModel;
use App\Common\Model\SysDictModel;
use Psr\Log\LoggerInterface;

class SysDictItemService
{
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getList(int $dictId): array
    {
        return SysDictItemModel::where('dict_id', $dictId)
            ->orderBy('sort', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->toArray();
    }

    public function create(array $data): int
    {
        $dict = SysDictModel::where('id', $data['dict_id'])
            ->first();
        
        if (!$dict) {
            throw new AppException('字典不存在');
        }
        
        $exists = SysDictItemModel::where('dict_id', $data['dict_id'])
            ->where('item_value', $data['item_value'])
            ->exists();
        
        if ($exists) {
            throw new AppException('字典项值已存在');
        }
        
        return SysDictItemModel::insertGetId($data);
    }

    public function update(array $params): bool
    {
        $model = SysDictItemModel::where('id', $params['id'])
            ->first();
        
        if (!$model) {
            throw new AppException('字典项不存在');
        }
        
        if ($model->item_value !== $params['item_value']) {
            $exists = SysDictItemModel::where('dict_id', $model->dict_id)
                ->where('item_value', $params['item_value'])
                ->where('id', '<>', $params['id'])
                ->exists();
            
            if ($exists) {
                throw new AppException('字典项值已存在');
            }
        }
        
        return $model->where('id',$params['id'])->update($params) > 0;
    }

    public function updateStatus(array $params): bool
    {
        $model = SysDictItemModel::where('id', $params['id'])
            ->first();
        
        if (!$model) {
            throw new AppException('字典项不存在');
        }
        
        return $model->where('id',$params['id'])->update(['status' => $params['status']]) > 0;
    }

    public function delete(int $id): bool
    {
        $model = SysDictItemModel::where('id', $id)
            ->first();
        
        if (!$model) {
            throw new AppException('字典项不存在');
        }
        
        return $model->destroy($id) > 0;
    }
}
