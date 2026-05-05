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

namespace App\Common\Logic;

use Hyperf\Cache\Annotation\Cacheable;

class IndexLogic
{
    /**
     * Desc: 获取信息
     * Auth: hello pan
     * Date: 1/31/26 10:08 PM.
     * @return array
     */
    #[Cacheable(prefix: 'test', ttl: 60, value: '_#{id}')]
    public function getInfo(int $id)
    {
        return [
            'key' => '测试cache',
            'id' => $id,
        ];
    }
}
