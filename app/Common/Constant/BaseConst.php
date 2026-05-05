<?php
/**
 * Created by PhpStorm.
 * User: Hello pan
 * Date: 12/23/25
 * Time: 10:52 AM
 */

namespace App\Common\Constant;

class BaseConst
{
    // 状态常量 开启
    const STATUS_OPEN = 1;
    // 状态常量 关闭
    const STATUS_CLOSE = 2;

    //开启状态
    const STATUS_LIST = [
        self::STATUS_OPEN  => '开启',
        self::STATUS_CLOSE => '关闭',
    ];

    // 应用来源-用户类型常量 管理后台
    const APP_USER_TYPE_ADMIN = 'admin';
    // 应用来源-用户类型常量 销售小程序
    const APP_USER_TYPE_SALES = 'sales';
    // 应用来源-用户类型常量 代理小程序
    const APP_USER_TYPE_AGENT = 'agent';
    // 应用来源-用户类型常量 员工小程序
    const APP_USER_TYPE_STAFF = 'staff';


    // 应用来源-用户类型常量-列表
    const APP_USER_TYPE_LIST = [
        self::APP_USER_TYPE_ADMIN => '管理员',
        self::APP_USER_TYPE_SALES => '销售',
        self::APP_USER_TYPE_AGENT => '代理',
        self::APP_USER_TYPE_STAFF => '员工',
    ];

    // 应用来源-用户类型常量-映射
    const APP_USER_TYPE_MAP = [
        self::APP_USER_TYPE_ADMIN => 1,
        self::APP_USER_TYPE_SALES => 2,
        self::APP_USER_TYPE_AGENT => 3,
        self::APP_USER_TYPE_STAFF => 4,
    ];
}