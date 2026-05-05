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
use App\Common\Constant\SysMenuConst;
use Hyperf\Context\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function Hyperf\Config\config;

/*
 * 获取真实ip
 */
if (! function_exists('get_real_ip')) {
    function get_real_ip()
    {
        $request = Context::get(ServerRequestInterface::class);
        if (! $request) {
            return '127.0.0.1';
        }

        $serverParams = $request->getServerParams();
        $headers = $request->getHeaders();

        if (isset($headers['x-forwarded-for'][0])) {
            // 部分CDN会获取多层代理IP，所以转成数组取第一个值
            $arr = explode(',', $headers['x-forwarded-for'][0]);
            return trim($arr[0]);
        }
        if (isset($headers['remoteip'][0])) {
            return $headers['remoteip'][0];
        }
        if (isset($headers['x-real-ip'][0])) {
            return $headers['x-real-ip'][0];
        }

        return $serverParams['remote_addr'] ?? '127.0.0.1';
    }
}

/*
 * 对用户的密码进行加密
 *
 * @param $password
 * @param $encrypt //传入加密串，在修改密码时做认证
 *
 * @return array/password
 */
if (! function_exists('password')) {
    function password($password, $encrypt = '')
    {
        $pwd = [];
        $pwd['encrypt'] = $encrypt ? $encrypt : createRandomstr();
        $pwd['password'] = md5(md5(trim($password)) . $pwd['encrypt']);
        return $encrypt ? $pwd['password'] : $pwd;
    }
}

/*
 * 产生随机字符串
 *
 * @param int $length 输出长度
 * @param string $chars 可选的 ，默认为 0123456789
 *
 * @return   string     字符串
 */
if (! function_exists('random')) {
    function random($length, $chars = '0123456789')
    {
        $hash = '';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; ++$i) {
            $hash .= $chars[mt_rand(0, $max)];
        }
        return $hash;
    }
}

/*
 * 生成随机字符串
 *
 * @param string $lenth 长度
 *
 * @return string 字符串
 */
if (! function_exists('createRandomstr')) {
    function createRandomstr($lenth = 8)
    {
        return random($lenth, '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ');
    }
}

/*
 * 递归获取当级和所有下级
 */
if (! function_exists('make_tree_lower')) {
    /**
     * 把返回的数据集转换成Tree.
     * @param array $list 要转换的数据集
     * @param mixed $get_id
     * @return array
     */
    function make_tree_lower($list, $get_id = 0)
    {
        static $ret = [];
        foreach ($list as $key => $value) {
            if ($value['pid'] == $get_id) {
                $ret[] = $value['id'];
                unset($list[$key]);
                make_tree_lower($list, $value['id']);
            }
        }
        return $ret;
    }
}

/*
 * 菜单无限极分类包含按钮权限
 */
if (! function_exists('make_tree_menu_button')) {
    /**
     * 把返回的数据集转换成Tree.
     * @param array $list 要转换的数据集
     * @param string $id 自增字段（栏目id）
     * @param string $pid parent标记字段
     * @param mixed $child
     * @param mixed $root
     * @param mixed $is_have_button
     * @return array
     */
    function make_tree_menu_button($list, $id = 'id', $pid = 'parent_id', $child = 'children', $root = 0, $is_have_button = true)
    {
        $tree = [];
        $buttonData = [];
        $packData = [];
        foreach ($list as $data) {
            if (! empty($data['auth_code'])) {
                $buttonData[] = $data['auth_code'];
            }
            if (! $is_have_button && $data['type'] == SysMenuConst::MENU_TYPE_BUTTON) {
                continue;
            }

            $data = [
                'id' => $data['id'],
                'pid' => $data['pid'],
                'name' => $data['name'],
                'status' => $data['status'],
                'type' => $data['type'],
                'path' => $data['path'],
                'component' => $data['component'],
                'auth_code' => $data['auth_code'],
                'meta' => $data,
            ];
            $unset_field = ['name', 'status', 'type', 'path', 'auth_code', 'component', 'create_time', 'update_time'];
            foreach ($unset_field as $field) {
                unset($data['meta'][$field]);
            }

            $packData[$data['meta'][$id]] = $data;
        }

        foreach ($packData as $key => $val) {
            if ($val['meta'][$pid] == $root) {// 代表跟节点
                $tree[] = &$packData[$key];
            } else {
                // 找到其父类
                $packData[$val['meta'][$pid]][$child][] = &$packData[$key];
            }
        }

        return ['menu_tree' => $tree, 'button' => $buttonData];
    }
}

if (! function_exists('shouldPassThrough')) {
    function shouldPassThrough(array $except_arr, string $path): bool
    {
        foreach ($except_arr as $except) {
            $except = trim($except, '/');
            $path = trim($path, '/');
            if ($path === $except) {
                return true;
            }
        }
        return false;
    }
}

if (! function_exists('jsonResponse')) {
    function jsonResponse(array $data,$status = 200): ResponseInterface
    {
        $response = Context::get(ResponseInterface::class);
        $response = $response->withHeader('Content-Type', 'application/json; charset=utf-8')->withStatus($status);
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response;
    }
}


/**
 * 过滤器
 * 支持二维数组过滤
 * 与验证器结合使用时，如果出现数据重复查询，请将验证交给过滤器处理
 * 需要调用自身时，务必防止死循环！！！
 */
if (!function_exists('request_filter')) {
    function request_filter(&$data = [], $filter_class = null, $scene = '', $check_func = true)
    {
        $data = $data ?? [];
        if (empty($scene) || !is_string($scene)) return true;
        if ($data && is_numeric(key($data))) {
            foreach ($data as &$val) {
                (new $filter_class)->format($val, $scene, $check_func);
            }
            unset($val);
            return $data;
        }
        return (new $filter_class)->format($data, $scene, $check_func);
    }
}

/**
 * 数组变量过滤
 *
 * @param      $var
 * @param      $key
 * @param bool $default
 *
 * @return bool|string|array
 */
if (!function_exists('isSetEmpty')) {
    function isSetEmpty($var, $key, $default = false)
    {
        $data = isset($var[$key]) && !empty($var[$key]) ? $var[$key] : $default;
        if (!empty($data) && is_string($data)) {
            return trim($data);
        }
        return $data;
    }
}

/**
 * 快捷过滤查询条件
 *
 * @param $params array 查询参数
 * @param $quickFilterWhereKey array 过滤条件配置
 *
 * @return array
 */
if (!function_exists('quickFilterWhereKey')) {
    function quickFilterWhereKey($params, $quickFilterWhereKey)
    {
        $where = [];
        foreach ($quickFilterWhereKey as $value) {
            $value = explode('|', $value);
            $key   = $value[0];
            if (count($value) == 1) {
                isSetEmpty($params, $key, false) && $where[] = [$key, '=', $params[$key]];
                continue;
            }

            switch ($value[1]) {
                case 'like':
                    isSetEmpty($params, $key, false) && $where[] = [$key, 'like', '%' . $params[$key] . '%'];
                    break;
                case 'in':
                    isSetEmpty($params, $key, false) && $where[] = [$key, 'in', $params[$key]];
                    break;
                case 'date_between':
                    [$tableField, $startDateKey, $endDateKey] = explode('-', $key);

                    $startDate = isSetEmpty($params, $startDateKey, false);
                    $endDate   = isSetEmpty($params, $endDateKey, false);

                    $startTime = $startDate && strtotime($startDate) > 0 ? date('Y-m-d', strtotime($startDate)) : false;
                    $endTime   = $endDate && strtotime($endDate) > 0 ? date('Y-m-d 23:59:59', strtotime($endDate)) : false;

                    if ($startTime && $endTime) {
                        $where[] = [$tableField, 'between', [$startTime, $endTime]];
                    } else if ($startTime) {
                        $where[] = [$tableField, '>=', $startTime];
                        $where[] = [$tableField, '>', 0];
                    } else if ($endTime) {
                        $where[] = [$tableField, '<=', $endTime];
                        $where[] = [$tableField, '>', 0];
                    }
                    break;
            }
        }
        return $where;
    }
}

/**
 * 解析请求浏览器和操作系统
 */
if(!function_exists('parseUserAgent')){
    function parseUserAgent(?string $userAgent): array
    {
        if (empty($userAgent)) {
            return ['browser' => 'Unknown', 'os' => 'Unknown'];
        }

        $browser = 'Unknown';
        $os = 'Unknown';

        // 解析浏览器类型
        if (stripos($userAgent, 'Chrome') !== false) {
            $browser = 'Chrome';
        } elseif (stripos($userAgent, 'Firefox') !== false) {
            $browser = 'Firefox';
        } elseif (stripos($userAgent, 'Safari') !== false && stripos($userAgent, 'Chrome') === false) {
            $browser = 'Safari';
        } elseif (stripos($userAgent, 'Edge') !== false) {
            $browser = 'Edge';
        } elseif (stripos($userAgent, 'MSIE') !== false || stripos($userAgent, 'Trident') !== false) {
            $browser = 'Internet Explorer';
        }

        // 解析操作系统
        if (stripos($userAgent, 'Windows NT 10.0') !== false) {
            $os = 'Windows 10';
        } elseif (stripos($userAgent, 'Windows NT 6.3') !== false) {
            $os = 'Windows 8.1';
        } elseif (stripos($userAgent, 'Windows NT 6.2') !== false) {
            $os = 'Windows 8';
        } elseif (stripos($userAgent, 'Windows NT 6.1') !== false) {
            $os = 'Windows 7';
        } elseif (stripos($userAgent, 'Mac OS X') !== false) {
            $os = 'macOS';
        } elseif (stripos($userAgent, 'Linux') !== false) {
            $os = 'Linux';
        } elseif (stripos($userAgent, 'Android') !== false) {
            $os = 'Android';
        } elseif (stripos($userAgent, 'iPhone') !== false || stripos($userAgent, 'iPad') !== false) {
            $os = 'iOS';
        }

        return [
            'browser' => $browser,
            'os' => $os,
            'raw_ua' => $userAgent
        ];
    }
}

/**
 * 获取OSS资源路径
 */
if (!function_exists('get_image_url')) {
    function get_image_url($file_path = '', $default = ''): string
    {
        $file_path = trim($file_path);
        if (empty($file_path)) $file_path = trim($default);
        if (empty($file_path)) return '';
        return !str_contains($file_path, 'http') ? (config('file.storage.oss.base_url') . $file_path) : $file_path;
    }
}


