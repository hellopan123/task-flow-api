<?php

declare(strict_types=1);

namespace App\Admin\Service;

use App\Common\Exception\AppException;
use App\Common\Model\SysConfigModel;
use Psr\Log\LoggerInterface;

class SysConfigService
{
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getList(array $params = []): array
    {
        $where = quickFilterWhereKey($params,['config_group','config_key|like','config_name|like','status']);
        
        $query = SysConfigModel::query()->where($where);
        
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 15;
        
        $total = $query->count();
        $list = $query->forPage($page,$limit)
            ->orderBy('sort', 'asc')
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();
        
        return ['list' => $list, 'count' => $total];
    }

    public function getInfoById(int $id): ?array
    {
        $config = SysConfigModel::where('id', $id)
            ->first();
        
        if (!$config) {
            return null;
        }
        
        return $config->toArray();
    }

    public function getByGroup(string $group): array
    {
        return SysConfigModel::where('config_group', $group)
            ->where('status', 1)
            ->orderBy('sort', 'asc')
            ->get()
            ->toArray();
    }

    public function getValue(string $group, string $key): ?string
    {
        $config = SysConfigModel::where('config_group', $group)
            ->where('config_key', $key)
            ->where('status', 1)
            ->first();

        return $config?->config_value;
    }

    public function getGroups(): array
    {

        return [
            ['name' => '基础配置','value' => 'basic'],
            ['name' => '短信配置','value' => 'sms'],
            ['name' => '任务配置','value' => 'task'],
            ['name' => '上传配置','value' => 'upload'],
            ['name' => '微信配置','value' => 'wechat'],
            ['name' => '需求配置','value' => 'requirement'],
            ['name' => '导入配置','value' => 'import'],
        ];

//        return SysConfigModel::select('config_group')
//            ->distinct()
//            ->pluck('config_group')
//            ->toArray();
    }

    public function create(array $data): int
    {
        $exists = SysConfigModel::where('config_group', $data['config_group'])
            ->where('config_key', $data['config_key'])
            ->exists();
        
        if ($exists) {
            throw new AppException('配置键已存在');
        }
        
        return SysConfigModel::insertGetId($data);
    }

    public function update(array $params): bool
    {
        $model = SysConfigModel::where('id', $params['id'])
            ->first();
        
        if (!$model) {
            throw new AppException('配置不存在');
        }
        
        if ($model->config_key !== $params['config_key'] || $model->config_group !== $params['config_group']) {
            $exists = SysConfigModel::where('config_group', $params['config_group'])
                ->where('config_key', $params['config_key'])
                ->where('id', '<>', $params['id'])
                ->exists();
            
            if ($exists) {
                throw new AppException('配置键已存在');
            }
        }
        
        return $model->where('id',$params['id'])->update($params) > 0;
    }

    public function updateStatus(array $params): bool
    {
        $model = SysConfigModel::where('id', $params['id'])
            ->first();
        
        if (!$model) {
            throw new AppException('配置不存在');
        }
        
        return $model->where('id',$params['id'])->update(['status' => $params['status']]) > 0;
    }

    public function delete(int $id): bool
    {
        $model = SysConfigModel::where('id', $id)
            ->first();
        
        if (!$model) {
            throw new AppException('配置不存在');
        }
        
        return $model->destroy($id) > 0;
    }

    public function batchUpdate(array $configs): bool
    {
        foreach ($configs as $config) {
            if (empty($config['config_group']) || empty($config['config_key'])) {
                continue;
            }
            
            SysConfigModel::where('config_group', $config['config_group'])
                ->where('config_key', $config['config_key'])
                ->update(['config_value' => $config['config_value']]);
        }
        
        return true;
    }
}
