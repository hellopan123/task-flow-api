<?php
/**
 * Created by PhpStorm
 * User: XINGZAI
 * Date: 2023/6/28
 * Time: 16:28
 */

namespace App\Common\Utils;


use Hyperf\Context\Context;
use Hyperf\HttpMessage\Upload\UploadedFile;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use function Hyperf\Support\make;
use function Sentry\logger;

class UploadFile
{
    /**
     * 文件上传
     * @param UploadedFile|null $file
     * @param string $title
     * @param string $save_name
     * @return array|false
     * @throws \League\Flysystem\FilesystemException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public static function upload(UploadedFile $file = null, $title = '', $save_name = '', $is_unlink = true) {
        $original_name = $file->getClientFilename();
        $file_ext = $file->getExtension();
        $file_ext = empty($file_ext) ? 'jpeg' : $file_ext;

        $file_size = $file->getSize();

        if ($title === '') {
            $file_name = vsprintf("%s%s.%s", [session_create_id(date('His')), rand(100000, 999999), $file_ext]);
        } else {
            $file_name = vsprintf("%s%s.%s", [$title, createRandomstr(10), $file_ext]);
        }
        if ($save_name === '') {
            $save_name = vsprintf("%s%s", [date('Y/m/d/'), $file_name]);
        }

        $res = self::writeStream($file->getRealPath(),$save_name, $is_unlink);
        if(false === $res) return false;
        return [
            'original_name' => $original_name,
            'file_name' => $file_name,
            'file_ext' => $file_ext,
            'save_name' => $save_name,
            'file_size' => $file_size,
        ];
    }


    /**
     * 文件系统存储
     * @param $file_path
     * @param $save_name
     * @return bool
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public static function writeStream($file_path,$save_name, $is_unlink = true)
    {
        try {
            $stream = fopen($file_path, 'r+');
            $filesystem = make(Filesystem::class);
            $filesystem->writeStream(
                $save_name,
                $stream
            );
            if ($is_unlink === true) @unlink($file_path);
            return true;
        } catch (\Exception $e) {
            logger('upload-error: ' . $e->getMessage());
            //@unlink($file_path);
            return false;
        }
    }

    /**
     * @param $save_name
     * @return bool
     * @throws FilesystemException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public static function deleted($save_name) {
        try {
            $filesystem = make(Filesystem::class);
            $filesystem->delete($save_name);
            return true;
        } catch (\Exception $e) {
            logger('upload-error: ' . $e->getMessage());
            return false;
        }
    }
}