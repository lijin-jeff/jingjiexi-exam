<?php
// +----------------------------------------------------------------------
// | likeadmin快速开发前后端分离管理后台（PHP版）
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 开源版本可自由商用，可去除界面版权logo
// | gitee下载：https://gitee.com/likeshop_gitee/likeadmin
// | github下载：https://github.com/likeshop-github/likeadmin
// | 访问官网：https://www.likeadmin.cn
// | likeadmin团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeadminTeam
// +----------------------------------------------------------------------

namespace app\common\model\user;

use app\common\model\BaseModel;

/**
 * 用户订阅模型
 * Class UserSubscribe
 * @package app\common\model\user
 */
class UserSubscribe extends BaseModel
{
    protected $name = 'user_subscribe';
    
    // 启用租户ID全局作用域
    protected $globalScope = ['tenant'];

        // 主键字段
    protected $pk = 'id';
    
    // 自动写入时间戳（tp8 默认识别 int 类型的 create_time/update_time）
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    // 字段类型转换（确保时间戳等字段类型正确）
    protected $type = [
        'subscribe_time' => 'integer',
        'is_pushed'      => 'boolean',
        'create_time'    => 'integer',
        'update_time'    => 'integer',
        'tenant_id'      => 'integer',
    ];
}

