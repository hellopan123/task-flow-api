<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Admin\Controller;

use App\Common\Controller\AbstractController;
use App\Common\Utils\Result;
use App\Common\Utils\UploadFile;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;

class CommonController extends AbstractController
{
    #[Inject]
    protected RequestInterface $request;

    public function captcha(): array
    {
        // TODO: 实现验证码生成逻辑
        return Result::success([
            'captcha_id' => '',
            'captcha_img' => '',
        ]);
    }

    /**
     * showdoc
     * @catalog 公共接口
     * @title 文件上传
     * @description 文件上传的接口
     * @method post
     * @url /admin/upload_file
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
        $result = $field = [];
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
}
