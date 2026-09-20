<?php
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 访问官网：
// | 官方邮箱：
// | 精解析答题系统开发者版权所有，拥有最终解释权。
namespace app\common\enum;

/**
 * 微信公众号枚举
 * Class OfficialAccountEnum
 * @package app\common\enum
 */
class OfficialAccountEnum
{
    /**
     * 菜单类型
     * click - 关键字
     * view - 跳转网页链接
     * miniprogram - 小程序
     */
    const MENU_TYPE = ['click', 'view', 'miniprogram'];

    /**
     * 关注回复
     */
    const REPLY_TYPE_FOLLOW = 1;

    /**
     * 关键字回复
     */
    const REPLY_TYPE_KEYWORD = 2;

    /**
     * 默认回复
     */
    const REPLY_TYPE_DEFAULT= 3;

    /**
     * 回复类型
     * follow - 关注回复
     * keyword - 关键字回复
     * default - 默认回复
     */
    const REPLY_TYPE = [
        self::REPLY_TYPE_FOLLOW => 'follow',
        self::REPLY_TYPE_KEYWORD => 'keyword',
        self::REPLY_TYPE_DEFAULT => 'default'
    ];

    /**
     * 匹配类型 - 全匹配
     */
    const MATCHING_TYPE_FULL = 1;

    /**
     * 匹配类型 - 模糊匹配
     */
    const MATCHING_TYPE_FUZZY = 2;

    /**
     * 消息类型 - 事件
     */
    const MSG_TYPE_EVENT = 'event';

    /**
     * 消息类型 - 文本
     */
    const MSG_TYPE_TEXT = 'text';

    /**
     * 事件类型 - 关注
     */
    const EVENT_SUBSCRIBE = 'subscribe';

    /**
     * @notes 获取类型英文名称
     * @param $type
     * @return string
     * @author Tab
     * @date 2021/7/29 16:32
     */
    public static function getReplyType($type)
    {
        return self::REPLY_TYPE[$type] ?? '';
    }
}