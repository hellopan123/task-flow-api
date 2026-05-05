<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Common\Controller\AbstractController;
use App\Common\Model\SysConfigModel;
use App\Common\Model\SysDictItemModel;
use App\Common\Model\SysDictModel;
use App\Common\Utils\Result;
use App\Common\Utils\UploadFile;

class CommonController extends AbstractController
{

    /**
     * showdoc
     * @catalog 公共接口
     * @title 文件上传
     * @description 文件上传的接口
     * @method post
     * @url /api/upload_file
     * @param file 必选 file 文件
     * @return {"code":"000000","msg":"操作成功","data":{"original_name":"刷卡@3x.png","file_path":"","file_url":"","resp_code":"1","resp_msg":"上传失败"}}
     * @return_param code string 错误码
     * @return_param msg string 错误信息
     * @return_param data object 数据
     * @return_param data.original_name string 原文件名称
     * @return_param data.file_path string 文件半路径
     * @return_param data.file_url string 文件全路径
     * @return_param data.resp_code string 上传状态：0成功、1失败
     * @return_param data.resp_msg string 失败原因
     * @remark file可传单个或多个，多个以数组形式传输；单个返回值为对象，多个返回为数组
     * @number 99
     */
    public function uploadFile()
    {
        $files = $this->request->file('file');
        if(!$files) {
            return Result::error('请选择文件');
        }
        $is_array = true;
        if (!is_array($files)) {
            $files = [$files];
            $is_array = false;
        }
        $result = [];
        foreach ($files as $file) {
            $upload = UploadFile::upload($file);
            $result[] = [
                'original_name' => $file->getClientFilename(),
                'file_path' => false === $upload ? '' : $upload['save_name'],
                'file_url' => false === $upload ? '' : get_image_url($upload['save_name']),
                'resp_code' => false === $upload ? '1' : '0',
                'resp_msg' => false === $upload ? '上传失败' : '上传成功',
            ];
        }
        // 提交
        $result = $is_array ? $result : array_shift($result);
        return Result::success($result);
    }

    /**
     * Desc: 获取字典
     * Auth: hello pan
     * Date: 4/25/26 PM5:45
     * @return array
     */
    public function getDictItems(): array
    {
        $dictCode = $this->request->input('dict_code');
        if (!$dictCode) {
            return Result::success([]);
        }
        
        $dict = SysDictModel::where('dict_code', $dictCode)
            ->where('status', 1)
            ->where('deleted_at', null)
            ->first();
        
        if (!$dict) {
            return Result::success([]);
        }
        
        $items = SysDictItemModel::where('dict_id', $dict->id)
            ->where('status', 1)
            ->where('deleted_at', null)
            ->orderBy('sort', 'asc')
            ->get(['item_label as label', 'item_value as value', 'item_style as style'])
            ->toArray();
        
        return Result::success($items);
    }

    /**
     * Desc: 获取字典数据
     * @param string $codes 字典编码，多个用逗号隔开
     * @return array
     * Auth: hello pan
     * Date: 4/25/26 PM5:46
     * @return array
     */
    public function getDicts(): array
    {
        $codes = $this->request->input('codes', []);
        if (empty($codes)) {
            return Result::success([]);
        }
        
        if (is_string($codes)) {
            $codes = explode(',', $codes);
        }
        
        $dicts = SysDictModel::whereIn('dict_code', $codes)
            ->where('status', 1)
            ->where('deleted_at', null)
            ->get(['id', 'dict_code'])
            ->keyBy('dict_code')
            ->toArray();
        
        $result = [];
        foreach ($codes as $code) {
            if (!isset($dicts[$code])) {
                $result[$code] = [];
                continue;
            }
            
            $result[$code] = SysDictItemModel::where('dict_id', $dicts[$code]['id'])
                ->where('status', 1)
                ->where('deleted_at', null)
                ->orderBy('sort', 'asc')
                ->get(['item_label as label', 'item_value as value', 'item_style as style'])
                ->toArray();
        }
        
        return Result::success($result);
    }

    /**
     * Desc: 获取配置数据
     * Auth: hello pan
     * Date: 4/25/26 PM5:45
     * @return array
     */
    public function getConfig(): array
    {
        $group = $this->request->input('group');
        $keys = $this->request->input('keys', []);
        
        $query = SysConfigModel::where('status', 1)
            ->where('deleted_at', null);
        
        if ($group) {
            $query->where('config_group', $group);
        }
        
        if (!empty($keys)) {
            if (is_string($keys)) {
                $keys = explode(',', $keys);
            }
            $query->whereIn('config_key', $keys);
        }
        
        $configs = $query->get(['config_key', 'config_value', 'config_type'])
            ->keyBy('config_key')
            ->toArray();
        
        $result = [];
        foreach ($configs as $key => $config) {
            $value = $config['config_value'];
            
            switch ($config['config_type']) {
                case 'number':
                    $value = (float) $value;
                    break;
                case 'boolean':
                    $value = $value === 'true' || $value === '1';
                    break;
                case 'json':
                case 'array':
                    $value = json_decode($value, true) ?: [];
                    break;
            }
            
            $result[$key] = $value;
        }
        
        return Result::success($result);
    }
}
