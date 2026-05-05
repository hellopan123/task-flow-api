<?php

declare(strict_types=1);

namespace App\Admin\Controller;

use App\Admin\Service\SysConfigService;
use App\Admin\Validate\SysConfigRequest;
use App\Common\Controller\AbstractController;
use App\Common\Exception\AppException;
use App\Common\Filter\SysConfigFilter;
use App\Common\Utils\Result;
use Hyperf\Di\Annotation\Inject;

class SysConfigController extends AbstractController
{
    #[Inject]
    protected SysConfigService $configService;

    public function getList(): array
    {
        $params = $this->request->all();
        $this->filter(SysConfigFilter::class, $params, 'list');
        $data = $this->configService->getList($params);
        return Result::success($data);
    }

    public function getInfo(): array
    {
        $id = $this->request->input('id');
        if (empty($id)) {
            throw new AppException('配置ID不能为空');
        }

        $data = $this->configService->getInfoById((int) $id);
        if (!$data) {
            throw new AppException('配置不存在');
        }

        return Result::success($data);
    }

    public function getByGroup(): array
    {
        $group = $this->request->input('config_group');
        if (empty($group)) {
            throw new AppException('配置分组不能为空');
        }

        $data = $this->configService->getByGroup($group);
        return Result::success($data);
    }

    public function getGroups(): array
    {
        $data = $this->configService->getGroups();
        return Result::success($data);
    }

    public function add(): array
    {
        $this->check(SysConfigRequest::class, 'add');
        $params = $this->request->all();
        $this->filter(SysConfigFilter::class, $params, 'add');
        $this->configService->create($params);
        return Result::success();
    }

    public function update(): array
    {
        $this->check(SysConfigRequest::class, 'update');
        $params = $this->request->all();
        $this->filter(SysConfigFilter::class, $params, 'update');
        $this->configService->update($params);
        return Result::success();
    }

    public function updateStatus(): array
    {
        $this->check(SysConfigRequest::class, 'update_status');
        $params = $this->request->all();
        $this->filter(SysConfigFilter::class, $params, 'update_status');
        $this->configService->updateStatus($params);
        return Result::success();
    }

    public function delete(): array
    {
        $this->check(SysConfigRequest::class, 'delete');
        $id = $this->request->input('id');
        $this->configService->delete((int) $id);
        return Result::success();
    }

    public function batchUpdate(): array
    {
        $configs = $this->request->input('configs', []);
        if (empty($configs)) {
            throw new AppException('配置数据不能为空');
        }

        $this->configService->batchUpdate($configs);
        return Result::success();
    }
}
