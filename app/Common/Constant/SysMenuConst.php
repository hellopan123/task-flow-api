<?php
/**
 * Created by PhpStorm.
 * User: Hello pan
 * Date: 12/18/25
 * Time: 5:18 PM
 */

namespace App\Common\Constant;

class SysMenuConst extends BaseConst
{

    /**
     * 菜单类型
     */
    const MENU_TYPE_CATALOG = 'catalog';
    const MENU_TYPE_MENU = 'menu';
    const MENU_TYPE_EMBEDDED = 'embedded';
    const MENU_TYPE_LINK = 'link';
    const MENU_TYPE_BUTTON = 'button';

    /**
     * 菜单类型集合
     */
    const MENU_TYPE_LIST = [
        self::MENU_TYPE_CATALOG,
        self::MENU_TYPE_MENU,
        self::MENU_TYPE_EMBEDDED,
        self::MENU_TYPE_LINK,
        self::MENU_TYPE_BUTTON,
    ];

    /**
     * 徽标样式
     */
    const BADGE_VARIANTS_PRIMARY = 'primary';
    const BADGE_VARIANTS_SUCCESS = 'success';
    const BADGE_VARIANTS_INFO = 'info';
    const BADGE_VARIANTS_WARNING = 'warning';
    const BADGE_VARIANTS_DANGER = 'danger';


    /**
     * 徽标样式集合
     */
    const BADGE_VARIANTS_LIST = [
        self::BADGE_VARIANTS_PRIMARY,
        self::BADGE_VARIANTS_SUCCESS,
        self::BADGE_VARIANTS_INFO,
        self::BADGE_VARIANTS_WARNING,
        self::BADGE_VARIANTS_DANGER,
    ];

    /**
     * 徽标类型
     */
    const BADGE_TYPE_DOT = 'dot';
    const BADGE_TYPE_NORMAL = 'normal';



}