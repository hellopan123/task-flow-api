<?php

declare(strict_types=1);

namespace App\Admin\Controller;

use App\Admin\Service\SysOperationLogService;
use App\Common\Controller\AbstractController;
use App\Common\Exception\AppException;
use App\Common\Utils\Result;
use Hyperf\Di\Annotation\Inject;

class SysOperationLogController extends AbstractController
{
    #[Inject]
    protected SysOperationLogService $logService;

    public function getList(): array
    {
        $params = $this->request->all();
        $data = $this->logService->getList($params);
        return Result::success($data);
    }

    public function getInfo(): array
    {
        $id = $this->request->input('id');
        if (empty($id)) {
            throw new AppException('日志ID不能为空');
        }

        $data = $this->logService->getInfoById((int) $id);
        if (!$data) {
            throw new AppException('日志不存在');
        }

        return Result::success($data);
    }

    public function delete(): array
    {
        $id = $this->request->input('id');
        if (empty($id)) {
            throw new AppException('日志ID不能为空');
        }

        $this->logService->delete((int) $id);
        return Result::success();
    }

    public function clear(): array
    {
        $count = $this->logService->clear();
        return Result::success(['count' => $count]);
    }
}
