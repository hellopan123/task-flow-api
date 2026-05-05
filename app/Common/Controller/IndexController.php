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

use App\Common\Logic\IndexLogic;
use Hyperf\Di\Annotation\Inject;

class IndexController extends AbstractController
{
    #[Inject]
    protected IndexLogic $indexLogic;

    public function index()
    {
        $user = $this->request->input('user', 'Hyperf');
        $method = $this->request->getMethod();

        return [
            'method' => $method,
            'message' => "Hello1111 {$user}.",
        ];
    }

    public function getInfo()
    {
        return $this->indexLogic->getInfo(111);
    }
}
