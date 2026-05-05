<?php
declare (strict_types=1);

namespace App\Common\Filter;


use App\Common\Exception\FilterException;
use Hyperf\Context\Context;
use Hyperf\Stringable\Str;

/**
 * 过滤器基类
 */
class BaseFilter
{
    /**
     * 方法缓存，避免重复反射检查
     */
    protected static array $methodMap = [];

    /**
     * @var array 过滤场景
     * @example ['name', 'status'] | ['name', 'other_status' => 'status']]
     * @remark 例1 name、status为结果集，优先匹配formatName、formatStatus方法，其次匹配变量值，再次则跳过
     * @remark 例2 name、other_status为结果集，优先匹配formatName、formatStatus方法，其次匹配变量值，再次则跳过
     */
    protected array $scene = [];

    public function __construct()
    {
        // 预热当前类的方法映射
        $class = static::class;
        if (! isset(self::$methodMap[$class])) {
            $methods = get_class_methods($this);
            $map = [];
            foreach ($methods as $method) {
                if (str_starts_with($method, 'format')) {
                    // 提取 formatFilter 后的部分作为 key，例如 formatFilterName -> name
                    // 这里简化逻辑，直接存全名，调用时通过规则匹配
                    $map[$method] = true;
                }
            }
            self::$methodMap[$class] = $map;
        }
    }

    /**
     * Desc: 数据过滤器，支持生成键值对
     * @param array $data
     * @param string $scene
     * @param bool $check_func
     * @return array
     * @throws FilterException
     */
    public function format(array &$data = [], string $scene = '', bool $check_func = true): array
    {
        if (empty($this->scene[$scene])) {
            return $data;
        }
        $rule = $this->scene[$scene]; // 验证规则
        $result = []; // 结果集
        try {
            $this->filter($data, $rule, $check_func, $result);
        } catch (\Throwable $e) {
            throw new FilterException((string) $e->getMessage());
        }
        $data = $result;
        return $data;
    }

    /**
     * Desc: 过滤处理
     * Auth: hello pan
     * Date: 2/12/26 12:23 PM
     * @param array $source
     * @param array $rule
     * @param bool $check_func
     * @param $result
     */
    private function filter(array $source = [], array $rule = [], bool $check_func = true, &$result = []): void
    {
        foreach ($rule as $key => $val) {
            if (empty($val)) {
                continue;
            }
            if (is_numeric($key)) {
                $key = $val;
            }

            // 优先级1：递归处理数组
            if (is_array($val)) {
                if (! empty($source[$key]) && is_array($source[$key])) {
                    $tmp = key($source[$key]);
                    if (is_numeric($tmp) && is_array($source[$key][$tmp])) {
                        foreach ($source[$key] as $k => $v) {
                            $this->filter($v, $val, $check_func, $result[$key][$k]);
                        }
                        continue;
                    }
                }
                $this->filter($source, $val, $check_func, $result[$key]);
                continue;
            }

            // 优先级2：调用自定义格式化方法
            // 优化：避免每次循环都进行复杂的字符串操作和 method_exists
            // 约定方法名为 formatFilter + PascalCase(字段名)
            // 例如：user_name -> formatFilterUserName
            // 注意：原代码逻辑 str_replace('__', 'filter_', $val) 看起来是为了支持特殊前缀，这里保留兼容性

            $methodName = 'format' . Str::studly(str_replace('__', 'filter_', $val));

            $class = static::class;
            if ($check_func && isset(self::$methodMap[$class][$methodName])) {
                $value = isset($source[$key]) ? (is_string($source[$key]) ? trim($source[$key]) : $source[$key]) : '';
                $callback = $this->$methodName($value, $source, $result);
                if (! is_bool($callback)) {
                    $result[$key] = $callback;
                }
                continue;
            }

            // 优先级3：场景嵌套
            if (isset($this->scene[$key])) {
                $this->filter($source, $this->scene[$key], $check_func, $result[$key]);
                continue;
            }

            // 优先级4：直接赋值
            if (! isset($source[$val])) {
                continue;
            }
            $result[$key] = is_string($source[$val]) ? trim($source[$val]) : $source[$val];
        }
    }


    /**
     * Desc:全局操作ID
     * Auth: hello pan
     * Date: 12/18/25 7:25 PM
     * @param $value
     * @param $data
     * @param $result
     * @return array|mixed
     */
    protected function formatFilterOperateId($value = '', $data = [], $result = [])
    {
        return Context::get('user_id');
    }

    /**
     * Desc: 全局操作人
     * Auth: hello pan
     * Date: 12/18/25 7:25 PM
     * @param $value
     * @param $data
     * @param $result
     * @return array|mixed
     */
    protected function formatFilterOperateName($value = '', $data = [], $result = [])
    {
        return Context::get('user_name');
    }

    /**
     * Desc: 全局操作人角色ID
     * Auth: hello pan
     * Date: 12/18/25 7:25 PM
     * @param $value
     * @param $data
     * @param $result
     * @return array|mixed
     */
    protected function formatFilterOperateRoleId($value = '', $data = [], $result = [])
    {
        $userInfo = Context::get('user_info');
        return $userInfo['role_id'] ?? 0;
    }


    /**
     * Desc: 当前时间
     * Auth: hello pan
     * Date: 12/18/25 7:25 PM
     * @param $value
     * @param $data
     * @param $result
     * @return string
     */
    protected function formatFilterCurrentTime($value = '', $data = [], $result = [])
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * Desc:空值
     * Auth: hello pan
     * Date: 12/18/25 7:25 PM
     * @param $value
     * @param $data
     * @param $result
     * @return string
     */
    protected function formatFilterEmptyStr($value = '', $data = [], $result = [])
    {
        return '';
    }


    /**
     * Desc:盐值
     * Auth: hello pan
     * Date: 12/18/25 7:17 PM
     * @param $value
     * @param $data
     * @param $result
     * @return string
     */
    protected function formatFilterSalt($value = '', $data = [], $result = []):string
    {
        return createRandomstr();
    }


    /**
     * Desc:更新密码
     * Auth: hello pan
     * Date: 12/18/25 7:18 PM
     * @param $value
     * @param $data
     * @param $result
     * @return array|string
     */
    protected function formatFilterNewPassword($value = '', $data = [], $result = [])
    {
        return password($value, $result['salt']);
    }

    /**
     * Desc:标识为 是
     * Auth: hello pan
     * Date: 12/18/25 7:18 PM
     * @param $value
     * @param $data
     * @param $result
     * @return int
     */
    protected function formatFilterTurnOn($value = '', $data = [], $result = [])
    {
        return 1;
    }

    /**
     * Desc:标识为 否
     * Auth: hello pan
     * Date: 12/18/25 7:18 PM
     * @param $value
     * @param $data
     * @param $result
     * @return int
     */
    protected function formatFilterTurnOff($value = '', $data = [], $result = [])
    {
        return 2;
    }


    /**
     * Desc: 初始密码
     * Auth: hello pan
     * Date: 12/18/25 7:26 PM
     * @param $value
     * @param $data
     * @param $result
     * @return mixed
     */
    protected function formatInitPassword($value = '', $data = [], $result = [])
    {
        $pwd = empty($value) ? 'aa123456' : $value;
        return password($pwd, $result['salt']);
    }


    /**
     * Desc: JSON编码
     * Auth: hello pan
     * Date: 12/18/25 7:26 PM
     * @param $value
     * @param $data
     * @param $result
     * @return mixed
     */
    protected function formatJsonEncode($value = '', $data = [], $result = [])
    {
        return is_array($value) ? json_encode($value,256) : $value;
    }

    /**
     * Desc: 登录用户信息
     * Auth: hello pan
     * Date: 12/18/25 7:26 PM
     * @param $value
     * @param $data
     * @param $result
     * @return mixed
     */
    protected function formatLoginUserInfo($value = '', $data = [], $result = [])
    {
        return Context::get('user_info');
    }


    /**
     * Desc: JSON编码登录用户信息
     * Auth: hello pan
     * Date: 12/18/25 7:26 PM
     * @param $value
     * @param $data
     * @param $result
     * @return mixed
     */
    protected function formatJsonLoginUserInfo($value = '', $data = [], $result = [])
    {
        return json_encode( Context::get('user_info'),256);
    }
}

