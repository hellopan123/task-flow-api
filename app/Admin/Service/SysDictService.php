<?php

declare(strict_types=1);

namespace App\Admin\Service;

use App\Common\Exception\AppException;
use App\Common\Model\SysDictItemModel;
use App\Common\Model\SysDictModel;
use Hyperf\DbConnection\Db;
use Psr\Log\LoggerInterface;

class SysDictService
{
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getList(array $params = []): array
    {

        $where = quickFilterWhereKey($params,['dict_name|like','dict_code|like','status']);;
        $query = SysDictModel::query()->where($where);
        
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 15;
        
        $total = $query->count();
        $list = $query->forPage($page,$limit)
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();
        
        return ['list' => $list, 'count' => $total];
    }

    public function getAll(): array
    {
        return SysDictModel::where('status', 1)
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();
    }

    public function getInfoById(int $id): ?array
    {
        $dict = SysDictModel::where('id', $id)->first();
        if (!$dict) {
            return null;
        }
        
        $data = $dict->toArray();
        $data['items'] = SysDictItemModel::where('dict_id', $id)
            ->orderBy('sort', 'asc')
            ->get()
            ->toArray();
        
        return $data;
    }

    public function getItemsByCode(string $dictCode): array
    {
        $dict = SysDictModel::where('dict_code', $dictCode)
            ->where('status', 1)
            ->first();
        
        if (!$dict) {
            return [];
        }
        
        return SysDictItemModel::where('dict_id', $dict->id)
            ->where('status', 1)
            ->orderBy('sort', 'asc')
            ->get()
            ->toArray();
    }

    public function create(array $data): int
    {
        $exists = SysDictModel::where('dict_code', $data['dict_code'])
            ->exists();
        
        if ($exists) {
            throw new AppException('字典编码已存在');
        }
        
        return SysDictModel::insertGetId($data);
    }

    public function update(array $params): bool
    {
        $model = SysDictModel::where('id', $params['id'])->first();
        if (!$model) {
            throw new AppException('字典不存在');
        }
        
        if ($model->dict_code !== $params['dict_code']) {
            $exists = SysDictModel::where('dict_code', $params['dict_code'])
                ->where('id', '<>', $params['id'])
                ->exists();
            if ($exists) {
                throw new AppException('字典编码已存在');
            }
        }
        
        return $model->where('id',$params['id'])->update($params) > 0;
    }

    public function updateStatus(array $params): bool
    {
        $model = SysDictModel::where('id', $params['id'])->first();
        if (!$model) {
            throw new AppException('字典不存在');
        }
        return $model->update(['status' => $params['status']]) > 0;
    }

    public function delete(int $id): bool
    {
        $model = SysDictModel::where('id', $id)->first();
        if (!$model) {
            throw new AppException('字典不存在');
        }
        
        Db::beginTransaction();
        try {
            $model->destroy($id);
            SysDictItemModel::where('dict_id', $id)->update(['deleted_at' => date('Y-m-d H:i:s')]);
            Db::commit();
            return true;
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error('删除字典失败: ' . $e->getMessage());
            throw new AppException('删除失败');
        }
    }

    public function updateItems(string $dictCode, array $items): bool
    {
        $dict = SysDictModel::where('dict_code', $dictCode)->first();
        if (!$dict) {
            throw new AppException('字典不存在');
        }
        
        Db::beginTransaction();
        try {
            SysDictItemModel::where('dict_id', $dict->id)->update(['deleted_at' => date('Y-m-d H:i:s')]);
            
            if (!empty($items)) {
                $itemData = [];
                foreach ($items as $item) {
                    $itemData[] = [
                        'dict_id' => $dict->id,
                        'item_label' => $item['item_label'],
                        'item_value' => $item['item_value'],
                        'item_style' => $item['item_style'] ?? '',
                        'sort' => $item['sort'] ?? 0,
                        'status' => $item['status'] ?? 1,
                        'remark' => $item['remark'] ?? '',
                        'create_time' => date('Y-m-d H:i:s'),
                    ];
                }
                SysDictItemModel::insert($itemData);
            }
            Db::commit();
            return true;
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error('更新字典项失败: ' . $e->getMessage());
            throw new AppException('更新字典项失败');
        }
    }
}
