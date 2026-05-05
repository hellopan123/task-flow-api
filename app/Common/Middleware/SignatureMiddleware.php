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

namespace App\Common\Middleware;

use App\Common\Exception\AppException;
use App\Common\Service\SignatureService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function Hyperf\Config\config;

/**
 * 签名验证中间件
 */
class SignatureMiddleware implements MiddlewareInterface
{
    /**
     * 不需要验签的地址
     * @var array|string[]
     */
    protected array $except = [
        '/admin/common/upload_file',
        '/admin/common/captcha',
    ];

    /**
     * 请求头必须字段
     * @var array|string[]
     */
    protected array $requiredHeaders = ['timestamp', 'sign', 'nonce'];

    /**
     * 请求对象
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * 签名服务
     * @var SignatureService
     */
    #[Inject]
    protected SignatureService $signatureService;


    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Desc: 处理请求
     * Auth: hello pan
     * Date: 2/6/26 6:00 PM
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws AppException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 是否需要验签
        if (! config('is_check_sign', true)) {
            return $handler->handle($request);
        }

        // 验证请求地址是否验签
        $path = $request->getUri()->getPath();
        if (shouldPassThrough($this->except, $path)) {
            return $handler->handle($request);
        }

        // 请求头参数验证
        $headerValues = [];
        foreach ($this->requiredHeaders as $field) {
            $value = $request->getHeaderLine($field);
            if (empty($value)) {
                throw new AppException("请求头缺失: {$field}");
            }
            $headerValues[$field] = $value;
        }

        if (! is_numeric($headerValues['timestamp'])) {
            throw new AppException('请求参数timestamp必须为数字');
        }

        if (strlen($headerValues['nonce']) < 16 || strlen($headerValues['nonce']) > 32) {
            throw new AppException('请求参数nonce必须为16-32位字符串');
        }

        $params = $this->request->all();
        if (! $this->signatureService->verifyRequest($params, $headerValues)) {
            throw new AppException('签名验证失败');
        }

        return $handler->handle($request);
    }

}
