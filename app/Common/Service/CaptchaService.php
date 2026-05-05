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

namespace App\Common\Service;

use App\Common\Cache\db00\CaptchaStringCache;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;

class CaptchaService
{
    #[Inject]
    protected Redis $redis;

    public function create(): array
    {
        $requestId = bin2hex(random_bytes(16));
        $code = $this->generateCode();
        $key = password_hash(strtolower($code), PASSWORD_BCRYPT, ['cost' => 10]);

        $cache = new CaptchaStringCache($this->redis);
        $cache->setKeyParameter([$requestId]);
        $cache->setEx($key, 300);

        $imageData = $this->generateImage($code);

        return [
            'request_id' => $requestId,
            'image' => $imageData,
        ];
    }

    public function verifyCode(string $code, string $requestId): bool
    {
        $cache = new CaptchaStringCache($this->redis);
        $cache->setKeyParameter([$requestId]);
        $checkValue = $cache->get();

        if (empty($checkValue)) {
            return false;
        }

        $result = password_verify(strtolower($code), $checkValue);

        if ($result) {
            $cache->del();
        }

        return $result;
    }

    protected function generateCode(int $length = 5): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';

        for ($i = 0; $i < $length; ++$i) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $code;
    }

    protected function generateImage(string $code): string
    {
        $width = 120;
        $height = 40;
        $image = imagecreatetruecolor($width, $height);

        $bgColor = imagecolorallocate($image, 255, 255, 255);
        $textColor = imagecolorallocate($image, 0, 0, 0);
        $lineColor = imagecolorallocate($image, 200, 200, 200);

        imagefill($image, 0, 0, $bgColor);

        for ($i = 0; $i < 5; ++$i) {
            imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $lineColor);
        }

        for ($i = 0; $i < 100; ++$i) {
            imagesetpixel($image, rand(0, $width), rand(0, $height), $lineColor);
        }

        $fontSize = 16;
        $x = 10;
        $y = 28;

        for ($i = 0; $i < strlen($code); ++$i) {
            $char = $code[$i];
            $angle = rand(-15, 15);
            imagestring($image, 5, $x, $y - 10, $char, $textColor);
            $x += 20;
        }

        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);

        return 'data:image/png;base64,' . base64_encode($imageData);
    }
}
