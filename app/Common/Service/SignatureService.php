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

use App\Common\Cache\db00\ApiNonceStringCache;
use App\Common\Exception\AppException;
use Hyperf\Redis\Redis;
use function Hyperf\Config\config;

/**
 * 请求签名服务
 */
class SignatureService
{
    /**
     * 配置参数
     * @var array
     */
    protected array $options = [
        'filter_empty' => true,
        'charset' => 'UTF-8',
        'url_encode' => true,
    ];

    public function __construct(protected Redis $redis) {}

    /**
     * Desc: 验证请求签名
     * Auth: hello pan
     * Date: 2/6/26 6:03 PM
     * @param array $params 请求参数
     * @param array $headers 请求header
     * @return bool
     * @throws AppException
     */
    public function verifyRequest(array $params, array $headers): bool
    {
        // 验证时间戳偏移量
        if (! $this->checkTimestamp($headers['timestamp'] ?? 0)) {
            return false;
        }

        // 验证nonce是否重复
        if (! $this->checkNonce($headers['nonce'] ?? '')) {
            return false;
        }


        $secret = config('sign_key');
        if (empty($secret)) {
            throw new AppException('签名密钥未配置');
        }

        // 验证sign是否匹配
        $signString = $this->buildSignString($params, $headers);
        $expectedSign = $this->generateSignature($signString, $secret);

        $checkResult = hash_equals($expectedSign, $headers['sign'] ?? '');

        if ($checkResult) {
            $this->setNonce($headers['nonce']);
        }

        return $checkResult;
    }

    public function buildSignString(array $params, array $headers): string
    {
        $dataString = $this->processData($params, $this->options);
        return sprintf('%s&timestamp=%s&nonce=%s', $dataString, $headers['timestamp'], $headers['nonce']);
    }

    public function generateSignature(string $data, string $secret, string $algorithm = 'md5'): string
    {
        if ($algorithm === 'md5') {
            return md5($data . $secret);
        }
        return hash_hmac($algorithm, $data, $secret);
    }

    protected function checkTimestamp(int $timestamp): bool
    {
        $current = time();
        $maxDrift = config('sign_timestamp_drift', 300);
        $timestamp = strlen((string) $timestamp) > 10 ? (int) ($timestamp / 1000) : $timestamp;
        return abs($current - $timestamp) <= $maxDrift;
    }

    protected function checkNonce(string $nonce): bool
    {
        if (empty($nonce)) {
            return false;
        }
        $cache = new ApiNonceStringCache($this->redis);
        $cache->setKeyParameter([$nonce]);
        return ! $cache->exists();
    }

    protected function setNonce(string $nonce): bool
    {
        $cache = new ApiNonceStringCache($this->redis);
        $cache->setKeyParameter([$nonce]);
        return $cache->setEx(1);
    }

    protected function processData($data, array $options, string $prefix = ''): string
    {
        $parts = [];

        if (! is_array($data)) {
            $data = (array) $data;
        }

        ksort($data, SORT_STRING | SORT_FLAG_CASE);

        foreach ($data as $key => $value) {
            if ($options['filter_empty'] && $this->isEmpty($value)) {
                continue;
            }

            $fullKey = $prefix ? $prefix . '[' . $key . ']' : $key;

            if (is_array($value) || is_object($value)) {
                $nestedResult = $this->processData($value, $options, $fullKey);
                if ($nestedResult !== '') {
                    $parts[] = $nestedResult;
                }
            } else {
                $value = $this->normalizeString((string) $value, $options['charset']);

                if ($options['url_encode']) {
                    $parts[] = rawurlencode($fullKey) . '=' . rawurlencode($value);
                } else {
                    $parts[] = $fullKey . '=' . $value;
                }
            }
        }

        return implode('&', $parts);
    }

    protected function isEmpty($value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        if (is_array($value) && empty($value)) {
            return true;
        }
        if (is_object($value) && empty((array) $value)) {
            return true;
        }
        return false;
    }

    protected function normalizeString(string $string, string $charset): string
    {
        if (function_exists('mb_convert_encoding')) {
            $currentCharset = mb_detect_encoding($string, ['UTF-8', 'GBK', 'GB2312', 'ASCII'], true);
            if ($currentCharset && strtoupper($currentCharset) !== strtoupper($charset)) {
                $string = mb_convert_encoding($string, $charset, $currentCharset);
            }
        }

        $string = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $string);
        return str_replace(["\r\n", "\r"], "\n", $string);
    }
}
