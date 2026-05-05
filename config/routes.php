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

use App\Common\Middleware\AuthCheckMiddleware;
use Hyperf\HttpServer\Router\Router;

Router::addGroup('/admin', function () {
    // 认证相关（不需要登录）
    Router::addGroup('/auth', function () {
        Router::post('/login', 'App\Admin\Controller\AuthController@login');
        Router::post('/fs_login', 'App\Admin\Controller\AuthController@loginByFsCode');
        Router::post('/refresh_token', 'App\Admin\Controller\AuthController@refreshToken');
        Router::get('/logout', 'App\Admin\Controller\AuthController@logout');
    });

    // 公共接口
    Router::addGroup('/common', function () {
        Router::get('/captcha', 'App\Admin\Controller\CommonController@captcha');
        Router::post('/upload_file', 'App\Admin\Controller\CommonController@uploadFile');
    });

    // 需要登录的接口
    Router::get('/user_info', 'App\Admin\Controller\AuthController@getUserInfo');

    // 管理员管理
    Router::addGroup('/sys_admin', function () {
        Router::get('/list', 'App\Admin\Controller\SysAdminController@getList');
        Router::get('/info', 'App\Admin\Controller\SysAdminController@getInfo');
        Router::post('/add', 'App\Admin\Controller\SysAdminController@add');
        Router::post('/update', 'App\Admin\Controller\SysAdminController@update');
        Router::post('/update_status', 'App\Admin\Controller\SysAdminController@updateStatus');
        Router::post('/delete', 'App\Admin\Controller\SysAdminController@delete');
    });

    // 角色管理
    Router::addGroup('/sys_admin_role', function () {
        Router::get('/list', 'App\Admin\Controller\SysAdminRoleController@getList');
        Router::get('/info', 'App\Admin\Controller\SysAdminRoleController@getInfo');
        Router::post('/add', 'App\Admin\Controller\SysAdminRoleController@add');
        Router::post('/update', 'App\Admin\Controller\SysAdminRoleController@update');
        Router::post('/update_status', 'App\Admin\Controller\SysAdminRoleController@updateStatus');
    });

    // 菜单管理
    Router::addGroup('/menu', function () {
        Router::get('/list', 'App\Admin\Controller\SysMenuController@getList');
        Router::get('/info', 'App\Admin\Controller\SysMenuController@getInfo');
        Router::get('/nav_menu', 'App\Admin\Controller\SysMenuController@getNavMenu');
        Router::post('/add', 'App\Admin\Controller\SysMenuController@add');
        Router::post('/update', 'App\Admin\Controller\SysMenuController@update');
        Router::post('/delete', 'App\Admin\Controller\SysMenuController@delete');
        Router::get('/check_exist', 'App\Admin\Controller\SysMenuController@checkExist');
    });

    // 角色管理
    Router::addGroup('/role', function () {
        Router::get('/list', 'App\Admin\Controller\SysRoleController@getList');
        Router::get('/info', 'App\Admin\Controller\SysRoleController@getInfo');
        Router::get('/all', 'App\Admin\Controller\SysRoleController@getAll');
        Router::post('/add', 'App\Admin\Controller\SysRoleController@add');
        Router::post('/update', 'App\Admin\Controller\SysRoleController@update');
        Router::post('/update_status', 'App\Admin\Controller\SysRoleController@updateStatus');
        Router::post('/delete', 'App\Admin\Controller\SysRoleController@delete');
        Router::post('/assign_menu', 'App\Admin\Controller\SysRoleController@assignMenu');
    });

    // 字典管理
    Router::addGroup('/dict', function () {
        Router::get('/list', 'App\Admin\Controller\SysDictController@getList');
        Router::get('/info', 'App\Admin\Controller\SysDictController@getInfo');
        Router::get('/all', 'App\Admin\Controller\SysDictController@getAll');
        Router::get('/items_by_code', 'App\Admin\Controller\SysDictController@getItemsByCode');
        Router::post('/add', 'App\Admin\Controller\SysDictController@add');
        Router::post('/update', 'App\Admin\Controller\SysDictController@update');
        Router::post('/update_status', 'App\Admin\Controller\SysDictController@updateStatus');
        Router::post('/delete', 'App\Admin\Controller\SysDictController@delete');
        Router::post('/update_items', 'App\Admin\Controller\SysDictController@updateItems');
        // 字典项管理
        Router::addGroup('/item', function () {
            Router::get('/list', 'App\Admin\Controller\SysDictItemController@getList');
            Router::post('/add', 'App\Admin\Controller\SysDictItemController@add');
            Router::post('/update', 'App\Admin\Controller\SysDictItemController@update');
            Router::post('/update_status', 'App\Admin\Controller\SysDictItemController@updateStatus');
            Router::post('/delete', 'App\Admin\Controller\SysDictItemController@delete');
        });
    });

    // 配置管理
    Router::addGroup('/config', function () {
        Router::get('/list', 'App\Admin\Controller\SysConfigController@getList');
        Router::get('/info', 'App\Admin\Controller\SysConfigController@getInfo');
        Router::get('/groups', 'App\Admin\Controller\SysConfigController@getGroups');
        Router::get('/by_group', 'App\Admin\Controller\SysConfigController@getByGroup');
        Router::post('/add', 'App\Admin\Controller\SysConfigController@add');
        Router::post('/update', 'App\Admin\Controller\SysConfigController@update');
        Router::post('/update_status', 'App\Admin\Controller\SysConfigController@updateStatus');
        Router::post('/delete', 'App\Admin\Controller\SysConfigController@delete');
        Router::post('/batch_update', 'App\Admin\Controller\SysConfigController@batchUpdate');
    });

    // 用户管理
    Router::addGroup('/user', function () {
        Router::get('/list', 'App\Admin\Controller\TkUserController@getList');
        Router::get('/info', 'App\Admin\Controller\TkUserController@getInfo');
        Router::get('/all', 'App\Admin\Controller\TkUserController@getAll');
        Router::post('/add', 'App\Admin\Controller\TkUserController@add');
        Router::post('/update', 'App\Admin\Controller\TkUserController@update');
        Router::post('/update_status', 'App\Admin\Controller\TkUserController@updateStatus');
        Router::post('/delete', 'App\Admin\Controller\TkUserController@delete');
    });

    // 任务分组管理
    Router::addGroup('/tk_task_group', function () {
        Router::get('/list', 'App\Admin\Controller\TkTaskGroupController@getList');
        Router::get('/info', 'App\Admin\Controller\TkTaskGroupController@getInfo');
        Router::get('/all', 'App\Admin\Controller\TkTaskGroupController@getAll');
        Router::post('/add', 'App\Admin\Controller\TkTaskGroupController@add');
        Router::post('/update', 'App\Admin\Controller\TkTaskGroupController@update');
        Router::post('/update_status', 'App\Admin\Controller\TkTaskGroupController@updateStatus');
        Router::post('/delete', 'App\Admin\Controller\TkTaskGroupController@delete');
    });

    // 项目管理
    Router::addGroup('/project', function () {
        Router::get('/list', 'App\Admin\Controller\TkProjectController@getList');
        Router::get('/info', 'App\Admin\Controller\TkProjectController@getInfo');
        Router::get('/all', 'App\Admin\Controller\TkProjectController@getAll');
        Router::post('/add', 'App\Admin\Controller\TkProjectController@add');
        Router::post('/update', 'App\Admin\Controller\TkProjectController@update');
        Router::post('/update_status', 'App\Admin\Controller\TkProjectController@updateStatus');
        Router::post('/update_progress', 'App\Admin\Controller\TkProjectController@updateProgress');
        Router::post('/delete', 'App\Admin\Controller\TkProjectController@delete');
    });

    // 需求管理
    Router::addGroup('/tk_requirement', function () {
        Router::get('/list', 'App\Admin\Controller\TkRequirementController@getList');
        Router::get('/info', 'App\Admin\Controller\TkRequirementController@getInfo');
        Router::get('/by_project', 'App\Admin\Controller\TkRequirementController@getByProject');
        Router::post('/add', 'App\Admin\Controller\TkRequirementController@add');
        Router::post('/update', 'App\Admin\Controller\TkRequirementController@update');
        Router::post('/update_status', 'App\Admin\Controller\TkRequirementController@updateStatus');
        Router::post('/delete', 'App\Admin\Controller\TkRequirementController@delete');
    });

    // 任务管理
    Router::addGroup('/tk_task', function () {
        Router::get('/list', 'App\Admin\Controller\TkTaskController@getList');
        Router::get('/info', 'App\Admin\Controller\TkTaskController@getInfo');
        Router::get('/by_group', 'App\Admin\Controller\TkTaskController@getByGroup');
        Router::get('/by_requirement', 'App\Admin\Controller\TkTaskController@getByRequirement');
        Router::post('/add', 'App\Admin\Controller\TkTaskController@add');
        Router::post('/update', 'App\Admin\Controller\TkTaskController@update');
        Router::post('/update_status', 'App\Admin\Controller\TkTaskController@updateStatus');
        Router::post('/update_progress', 'App\Admin\Controller\TkTaskController@updateProgress');
        Router::post('/delete', 'App\Admin\Controller\TkTaskController@delete');
    });

    // 通知管理
    Router::addGroup('/tk_notification', function () {
        Router::get('/list', 'App\Admin\Controller\TkNotificationController@getList');
        Router::get('/info', 'App\Admin\Controller\TkNotificationController@getInfo');
        Router::get('/unread_count', 'App\Admin\Controller\TkNotificationController@getUnreadCount');
        Router::post('/add', 'App\Admin\Controller\TkNotificationController@add');
        Router::post('/mark_read', 'App\Admin\Controller\TkNotificationController@markRead');
        Router::post('/mark_all_read', 'App\Admin\Controller\TkNotificationController@markAllRead');
        Router::post('/delete', 'App\Admin\Controller\TkNotificationController@delete');
    });

    // 反馈管理
    Router::addGroup('/tk_feedback', function () {
        Router::get('/list', 'App\Admin\Controller\TkFeedbackController@getList');
        Router::get('/info', 'App\Admin\Controller\TkFeedbackController@getInfo');
        Router::post('/add', 'App\Admin\Controller\TkFeedbackController@add');
        Router::post('/reply', 'App\Admin\Controller\TkFeedbackController@reply');
        Router::post('/delete', 'App\Admin\Controller\TkFeedbackController@delete');
    });

    // FAQ管理
    Router::addGroup('/tk_faq', function () {
        Router::get('/list', 'App\Admin\Controller\TkFaqController@getList');
        Router::get('/info', 'App\Admin\Controller\TkFaqController@getInfo');
        Router::get('/categories', 'App\Admin\Controller\TkFaqController@getCategories');
        Router::post('/add', 'App\Admin\Controller\TkFaqController@add');
        Router::post('/update', 'App\Admin\Controller\TkFaqController@update');
        Router::post('/update_status', 'App\Admin\Controller\TkFaqController@updateStatus');
        Router::post('/delete', 'App\Admin\Controller\TkFaqController@delete');
    });

    // 操作日志
    Router::addGroup('/operation_log', function () {
        Router::get('/list', 'App\Admin\Controller\SysOperationLogController@getList');
        Router::get('/info', 'App\Admin\Controller\SysOperationLogController@getInfo');
        Router::post('/delete', 'App\Admin\Controller\SysOperationLogController@delete');
        Router::post('/clear', 'App\Admin\Controller\SysOperationLogController@clear');
    });

    // 登录日志
    Router::addGroup('/login_log', function () {
        Router::get('/list', 'App\Admin\Controller\SysLoginLogController@getList');
        Router::get('/info', 'App\Admin\Controller\SysLoginLogController@getInfo');
        Router::post('/delete', 'App\Admin\Controller\SysLoginLogController@delete');
        Router::post('/clear', 'App\Admin\Controller\SysLoginLogController@clear');
    });
},['middleware' => [AuthCheckMiddleware::class]]);

// Agent 路由组
Router::addGroup('/agent', function () {
    // Agent 相关路由...
});

// Staff 路由组
Router::addGroup('/staff', function () {
    // Staff 相关路由...
});

// api 路由组 (H5应用接口)
Router::addGroup('/api', function () {
    // 认证相关（不需要登录）
    Router::addGroup('/auth', function () {
        Router::post('/register', 'App\Api\Controller\AuthController@register');
        Router::post('/login', 'App\Api\Controller\AuthController@login');
        Router::post('/send_code', 'App\Api\Controller\AuthController@sendCode');
        Router::post('/reset_password', 'App\Api\Controller\UserController@resetPassword');
        Router::post('/change_password', 'App\Api\Controller\UserController@changePassword');
        Router::post('/forgot_password', 'App\Api\Controller\UserController@forgotPassword');
        Router::post('/refresh_token', 'App\Api\Controller\AuthController@refreshToken');
        Router::post('/logout', 'App\Api\Controller\AuthController@logout');
    });
    
    // 用户相关（需要登录）
    Router::addGroup('/user', function () {
        Router::get('/info', 'App\Api\Controller\UserController@info');
        Router::post('/update_profile', 'App\Api\Controller\UserController@updateProfile');
        Router::post('/update_password', 'App\Api\Controller\UserController@updatePassword');
    });
    
    // 项目相关
    Router::addGroup('/project', function () {
        Router::get('/list', 'App\Api\Controller\ProjectController@getList');
        Router::get('/dict', 'App\Api\Controller\ProjectController@getDict');
        Router::get('/detail', 'App\Api\Controller\ProjectController@getDetail');
        Router::post('/create', 'App\Api\Controller\ProjectController@create');
        Router::post('/update', 'App\Api\Controller\ProjectController@update');
        Router::delete('/delete', 'App\Api\Controller\ProjectController@delete');
        Router::post('/add_members', 'App\Api\Controller\ProjectController@addMembers');
        Router::delete('/remove_member', 'App\Api\Controller\ProjectController@removeMember');
        Router::get('/members', 'App\Api\Controller\ProjectController@getMembers');
        Router::get('/available_members', 'App\Api\Controller\ProjectController@getAvailableMembers');
    });
    
    // 成员相关
    Router::addGroup('/member', function () {
        Router::get('/list', 'App\Api\Controller\MemberController@getList');
        Router::get('/info', 'App\Api\Controller\MemberController@getInfo');
        Router::get('/search', 'App\Api\Controller\MemberController@search');
        Router::post('/add', 'App\Api\Controller\MemberController@add');
        Router::post('/remove', 'App\Api\Controller\MemberController@remove');
        Router::get('/search_user', 'App\Api\Controller\MemberController@searchUser');
    });
    
    // 需求相关
    Router::addGroup('/requirement', function () {
        Router::get('/list', 'App\Api\Controller\RequirementController@getList');
        Router::get('/detail', 'App\Api\Controller\RequirementController@getDetail');
        Router::get('/by_project', 'App\Api\Controller\RequirementController@getByProject');
        Router::post('/create', 'App\Api\Controller\RequirementController@create');
        Router::post('/update', 'App\Api\Controller\RequirementController@update');
        Router::post('/update_status', 'App\Api\Controller\RequirementController@updateStatus');
        Router::post('/delete', 'App\Api\Controller\RequirementController@delete');

        // 需求导出
        Router::post('/export', 'App\Api\Controller\RequirementController@export');
        Router::get('/export_records', 'App\Api\Controller\RequirementController@getExportRecords');
    });
    
    // 任务相关
    Router::addGroup('/task', function () {
        Router::get('/list', 'App\Api\Controller\TaskController@getList');
        Router::get('/my', 'App\Api\Controller\TaskController@getMyTasks');
        Router::get('/detail', 'App\Api\Controller\TaskController@getDetail');
        Router::get('/metrics', 'App\Api\Controller\TaskController@getMetrics');
        Router::post('/create', 'App\Api\Controller\TaskController@create');
        Router::post('/batch', 'App\Api\Controller\TaskController@batchCreate');
        Router::post('/update', 'App\Api\Controller\TaskController@update');
        Router::post('/update_status', 'App\Api\Controller\TaskController@updateStatus');
        Router::post('/update_progress', 'App\Api\Controller\TaskController@updateProgress');
        Router::post('/delete', 'App\Api\Controller\TaskController@delete');
        Router::post('/assign', 'App\Api\Controller\TaskController@assign');
        Router::post('/batch_assign', 'App\Api\Controller\TaskController@batchAssign');
        Router::get('/stats', 'App\Api\Controller\TaskController@getTaskStats');
        Router::get('/list_stats', 'App\Api\Controller\TaskController@getTaskListStats');

        // 任务日志
        Router::get('/logs', 'App\Api\Controller\TaskController@getLogs');

        // 任务附件
        Router::get('/attachments', 'App\Api\Controller\TaskController@getAttachments');
        Router::post('/attachment/upload', 'App\Api\Controller\TaskController@uploadAttachment');
        Router::post('/attachment/delete', 'App\Api\Controller\TaskController@deleteAttachment');

        // 任务评论
        Router::get('/comments', 'App\Api\Controller\TaskController@getComments');
        Router::post('/comment/create', 'App\Api\Controller\TaskController@createComment');
        Router::post('/comment/delete', 'App\Api\Controller\TaskController@deleteComment');

        // 任务导入
        Router::post('/import/preview', 'App\Api\Controller\TaskController@importPreview');
        Router::post('/import/execute', 'App\Api\Controller\TaskController@importExecute');
        Router::get('/import/records', 'App\Api\Controller\TaskController@getImportRecords');
    });
    
    // 通知相关
    Router::addGroup('/notification', function () {
        Router::get('/list', 'App\Api\Controller\NotificationController@getList');
        Router::get('/unread_count', 'App\Api\Controller\NotificationController@getUnreadCount');
        Router::post('/mark_read', 'App\Api\Controller\NotificationController@markRead');
        Router::post('/mark_all_read', 'App\Api\Controller\NotificationController@markAllRead');
        Router::post('/delete', 'App\Api\Controller\NotificationController@delete');
    });
    
    // 反馈相关
    Router::addGroup('/feedback', function () {
        Router::get('/list', 'App\Api\Controller\FeedbackController@getList');
        Router::get('/detail', 'App\Api\Controller\FeedbackController@getDetail');
        Router::post('/create', 'App\Api\Controller\FeedbackController@create');
    });
    
    // 应用相关
    Router::get('/app/info', 'App\Api\Controller\AppController@info');
    Router::get('/app/check_update', 'App\Api\Controller\AppController@checkUpdate');
    Router::get('/faq', 'App\Api\Controller\AppController@faq');
    
    // 公共接口
    Router::addGroup('/common', function () {
        Router::get('/dict_items', 'App\Api\Controller\CommonController@getDictItems');
        Router::get('/dicts', 'App\Api\Controller\CommonController@getDicts');
        Router::get('/config', 'App\Api\Controller\CommonController@getConfig');
        Router::post('/upload_file', 'App\Api\Controller\CommonController@uploadFile');
    });
});

Router::get('/favicon.ico', function () {
    return '';
});
