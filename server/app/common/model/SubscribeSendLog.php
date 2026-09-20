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

namespace app\common\model;

use app\common\model\BaseModel;
use app\common\model\user\User;

/**
 * 订阅消息发送日志模型
 * Class SubscribeSendLog
 * @package app\common\model
 */
class SubscribeSendLog extends BaseModel
{
    protected $name = 'tenant_subscribe_send_log';

    // 字段类型转换
    protected $type = [
        'send_time' => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
        'send_status' => 'tinyint',
        'retry_times' => 'tinyint',
        'error_code' => 'integer',
        'tenant_id' => 'integer',
        'user_id' => 'integer',
        'related_id' => 'integer',
    ];
    
    // 字段映射
    protected $field = [
        'id' => 'id',
        'tenant_id' => 'tenant_id',
        'user_id' => 'user_id',
        'openid' => 'openid',
        'type' => 'type',
        'related_id' => 'related_id',
        'template_id' => 'template_id',
        'message_data' => 'data',
        'send_status' => 'send_status',
        'send_time' => 'send_time',
        'retry_times' => 'retry_times',
        'error_code' => 'error_code',
        'error_msg' => 'error_msg',
        'response_data' => 'result',
        'create_time' => 'create_time',
        'update_time' => 'update_time',
    ];

    /**
     * 发送状态获取器
     * @return string
     */
    public function getSendStatusDescAttr()
    {
        $statusMap = [
            0 => '待发送',
            1 => '发送成功',
            2 => '发送失败'
        ];
        return $statusMap[$this->getData('send_status')] ?? '未知';
    }
    
    /**
     * 关联用户模型
     * @return \think\model\relation\HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id')->field('id, nickname, avatar, openid');
    }
}