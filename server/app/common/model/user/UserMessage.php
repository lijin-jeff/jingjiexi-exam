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
 * 用户消息模型
 * Class UserMessage
 * @package app\common\model\user
 */
class UserMessage extends BaseModel
{
    protected $name = 'user_message';
    
    // 禁用租户ID全局作用域（消息表是用户维度，不需要租户隔离）
    protected $globalScope = [];

    // 消息类型常量
    const TYPE_COMMENT_REPLY = 1;    // 评论回复
    const TYPE_COMMENT_LIKE = 2;     // 评论点赞
    const TYPE_COMMENT_REWARD = 3;   // 评论赞赏
    const TYPE_SYSTEM = 4;           // 系统通知

    // 子类型常量
    const SUBTYPE_ARTICLE_COMMENT = 'article_comment';      // 文章评论
    const SUBTYPE_QUESTION_COMMENT = 'question_comment';    // 试题评论
    const SUBTYPE_INTEGRAL_CHANGE = 'integral_change';      // 积分变化
    const SUBTYPE_VIP_REMIND = 'vip_remind';                // 会员提醒
    const SUBTYPE_VIP_CHANGE = 'vip_change';                // 会员变更
    const SUBTYPE_WEEKLY_SUMMARY = 'weekly_summary';        // 每周总结
    const SUBTYPE_RESOURCE_ADD = 'resource_add';            // 资料添加
    const SUBTYPE_ARTICLE_ADD = 'article_add';              // 文章添加
    const SUBTYPE_QUESTION_ADD = 'question_add';            // 试题添加
    const SUBTYPE_VERSION_UPDATE = 'version_update';          // 版本更新

    /**
     * @notes 获取器 - extra字段JSON解析
     */
    public function getExtraAttr($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * @notes 修改器 - extra字段JSON编码
     */
    public function setExtraAttr($value)
    {
        return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
    }

    /**
     * @notes 关联发送者用户信息
     */
    public function sender()
    {
        return $this->hasOne(\app\common\model\user\User::class, 'id', 'sender_id')
            ->field('id,nickname,avatar')
            ->bind(['sender_nickname' => 'nickname', 'sender_avatar' => 'avatar']);
    }

    /**
     * @notes 关联接收者用户信息
     */
    public function receiver()
    {
        return $this->hasOne(\app\common\model\user\User::class, 'id', 'user_id')
            ->field('id,nickname,avatar');
    }
}
