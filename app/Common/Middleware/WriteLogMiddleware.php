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

use App\Common\Model\SysOperationLogModel;
use Hyperf\Context\Context;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

/**
 * 日志中间件.
 */
class WriteLogMiddleware implements MiddlewareInterface
{
    protected ContainerInterface $container;

    protected RequestInterface $request;

    protected HttpResponse $response;

    protected LoggerInterface $logger;

    #[Inject]
    protected SysOperationLogModel $log_model;

    public function __construct(ContainerInterface $container, HttpResponse $response, RequestInterface $request, LoggerFactory $loggerFactory)
    {
        $this->container = $container;
        $this->response = $response;
        $this->request = $request;
        $this->logger = $loggerFactory->get('log', 'default');
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        Context::set('req_start_time',microtime(true));
        $data = [
            'url' => $this->request->url(),
            'method' => $this->request->getMethod(),
            'headers' => $this->request->getHeaders(),
            'params' => $this->request->all(),
        ];
        $this->logger->info('REQUEST-INFO', $data);

        $response = $handler->handle($request);

        $this->logger->info('RESPONSE-INFO', ['body' => $response->getBody()->getContents()]);
        // 增加操作记录
        //$this->addOperationLog($response);

        return $response;
    }


    /**
     * Desc: 增加操作记录
     * Auth: hello pan
     * Date: 2/23/26 10:44 PM
     * @param ResponseInterface $response
     */
    public function addOperationLog(ResponseInterface $response)
    {
        // 这里应该使用异步处理
        try {
            $url = $this->request->getUri()->getPath();
            $url_info = explode('/',trim($url,'/'));
            $response_body = json_decode($response->getBody()->getContents(),true);
            $log = [
                'module' => $url_info[0] ?? '',
                'operation_type' => end($url_info),
                'operation_desc' => '',
                'request_method' => $this->request->getMethod(),
                'request_url' => $this->request->url(),
                'request_params' => json_encode(['header' => $this->request->getHeaders(),'params' => $this->request->all()],256),
                'response_result' => substr($response->getBody()->getContents(),0,5000),
                'ip' => get_real_ip(),
                'location' => '',
                'user_agent' => $this->request->getHeaderLine('user-agent'),
                'operator_id' => Context::get('user_id'),
                'operator_name' => Context::get('user_name'),
                'status' => $response->getStatusCode() == 200 ? 1 : 2 ,
                'error_msg' => $response_body['msg'] ?? '',
                'cost_time' => number_format((microtime(true) - Context::get('req_start_time')) * 1000 ,0),
            ];
            $this->log_model->insert($log);

        }catch (\Throwable $e){
            $this->logger->info('增加操作记录失败：'.$e->getMessage());
        }
    }

}
