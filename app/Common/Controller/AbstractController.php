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

namespace App\Common\Controller;

use App\Common\Exception\ValidationException;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\Request\FormRequest;
use Psr\Container\ContainerInterface;

abstract class AbstractController
{
    #[Inject]
    protected ContainerInterface $container;

    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected ResponseInterface $response;

    #[Inject]
    protected ValidatorFactoryInterface $validationFactory;

    /**
     * 数据验证
     */
    protected function validate(array $data, array $rules, array $messages = []): void
    {
        $validator = $this->validationFactory->make($data, $rules, $messages);
        if ($validator->fails()) {
            throw new ValidationException($validator->errors()->first());
        }
    }


    protected function check($class, $scene = '')
    {
        $request = $this->container->get($class);
        $request->scene($scene)->validateResolved();
        return $request;
    }

    /**
     * Desc: 数据过滤
     * Auth: hello pan
     * Date: 12/18/25 7:36 PM
     * @param string $filter_class  过滤器类名
     * @param array $data          过滤数据
     * @param string $scene         过滤场景
     * @param bool $check_func    是否检查过滤函数
     * @return true
     */
    protected function filter($filter_class, &$data, $scene = [], $check_func = true)
    {
        if(!empty($scene) && is_string($scene)) {
            request_filter($data, $filter_class, $scene, $check_func);
        }
        return true;
    }

}
