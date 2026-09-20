<?php
// +----------------------------------------------------------------------
// | likeadmin快速开发前后端分离管理后台（PHP版）
// +----------------------------------------------------------------------

namespace app\common\logic\user;

use app\common\model\user\UserMessage;
use app\common\model\user\User;
use think\facade\Db;

/**
 * 消息服务逻辑类
 * Class MessageService
 * @package app\common\logic\user
 */
class MessageService
{
    /**
     * @notes 处理取消订阅
     * @param int $userId 用户ID
     * @param string $templateId 模板ID
     * @param int $tenantId 租户ID
     * @return bool
     */
    public static function handleUnsubscribe($userId, $templateId, $tenantId = 1)
    {
        try {
            $now = time();
            \think\facade\Db::execute(
                'INSERT INTO la_user_subscribe (user_id, tenant_id, template_id, status, create_time, update_time) 
                 VALUES (:user_id, :tenant_id, :template_id, 0, :create_time, :update_time) 
                 ON DUPLICATE KEY UPDATE status = 0, update_time = :update_time',
                [
                    'user_id' => $userId,
                    'tenant_id' => $tenantId,
                    'template_id' => $templateId,
                    'create_time' => $now,
                    'update_time' => $now
                ]
            );
            return true;
        } catch (\Exception $e) {
            \think\facade\Log::error('handleUnsubscribe 处理失败: ' . json_encode([
                'user_id' => $userId,
                'tenant_id' => $tenantId,
                'template_id' => $templateId,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE));
            return false;
        }
    }

    /**
     * @notes 检查用户是否订阅了指定模板
     * @param int $userId 用户ID
     * @param string $templateId 模板ID
     * @param int $tenantId 租户ID
     * @return bool
     */
    public static function checkSubscription($userId, $templateId, $tenantId = 1)
    {
        try {
            $count = \think\facade\Db::table('la_user_subscribe')
                ->where('user_id', $userId)
                ->where('tenant_id', $tenantId)
                ->where('template_id', $templateId)
                ->where('status', 1)
                ->count();
            return $count > 0;
        } catch (\Exception $e) {
            \think\facade\Log::error('checkSubscription 检查失败: ' . json_encode([
                'user_id' => $userId,
                'tenant_id' => $tenantId,
                'template_id' => $templateId,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE));
            return false;
        }
    }

    /**
     * @notes 检查消息发送频率
     * @param int $userId 用户ID
     * @param string $templateId 模板ID
     * @return bool
     */
    public static function checkFrequency($userId, $templateId)
    {
        try {
            $now = time();
            $oneHourAgo = $now - 3600; // 1小时前
            $oneDayAgo = $now - 86400; // 1天前
            
            // 1. 检查同一用户同一模板的发送频率（每小时最多5条）
            $userTemplateCount = \think\facade\Db::table('user_message')
                ->where('user_id', $userId)
                ->where('create_time', '>', $oneHourAgo)
                ->where('extra->template_id', '=', $templateId)
                ->count();
            
            if ($userTemplateCount >= 5) {
                \think\facade\Log::warning('checkFrequency 用户模板发送频率过高: ' . json_encode([
                    'user_id' => $userId,
                    'template_id' => $templateId,
                    'count' => $userTemplateCount
                ], JSON_UNESCAPED_UNICODE));
                return false;
            }
            
            // 2. 检查同一模板的总发送频率（每小时最多100条）
            $templateTotalCount = \think\facade\Db::table('user_message')
                ->where('create_time', '>', $oneHourAgo)
                ->where('extra->template_id', '=', $templateId)
                ->count();
            
            if ($templateTotalCount >= 100) {
                \think\facade\Log::warning('checkFrequency 模板总发送频率过高: ' . json_encode([
                    'template_id' => $templateId,
                    'count' => $templateTotalCount
                ], JSON_UNESCAPED_UNICODE));
                return false;
            }
            
            // 3. 检查同一用户的总发送频率（每天最多50条）
            $userTotalCount = \think\facade\Db::table('user_message')
                ->where('user_id', $userId)
                ->where('create_time', '>', $oneDayAgo)
                ->count();
            
            if ($userTotalCount >= 50) {
                \think\facade\Log::warning('checkFrequency 用户总发送频率过高: ' . json_encode([
                    'user_id' => $userId,
                    'count' => $userTotalCount
                ], JSON_UNESCAPED_UNICODE));
                return false;
            }
            
            return true;
        } catch (\Exception $e) {
            \think\facade\Log::error('checkFrequency 检查失败: ' . json_encode([
                'user_id' => $userId,
                'template_id' => $templateId,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE));
            return true; // 频率检查失败时，允许发送消息
        }
    }

    /**
     * @notes 创建消息通知
     * @param int $userId 接收用户ID
     * @param int $type 消息类型
     * @param string $title 消息标题
     * @param string $content 消息内容
     * @param array $extra 扩展数据
     * @param string|null $subType 子类型
     * @param string|null $templateId 订阅消息模板ID
     * @param int $tenantId 租户ID
     * @return bool
     */
    public static function createMessage($userId, $type, $title, $content, $extra = [], $subType = null, $templateId = null, $tenantId = 1)
    {
        try {
            // 如果指定了模板ID，检查用户是否订阅
            if ($templateId) {
                if (!self::checkSubscription($userId, $templateId, $tenantId)) {
                    \think\facade\Log::info('createMessage 用户未订阅模板: ' . json_encode([
                        'user_id' => $userId,
                        'template_id' => $templateId,
                        'tenant_id' => $tenantId
                    ], JSON_UNESCAPED_UNICODE));
                    return false;
                }
                
                // 检查发送频率
                if (!self::checkFrequency($userId, $templateId)) {
                    \think\facade\Log::warning('createMessage 发送频率过高: ' . json_encode([
                        'user_id' => $userId,
                        'template_id' => $templateId
                    ], JSON_UNESCAPED_UNICODE));
                    return false;
                }
                
                // 将模板ID添加到extra中，用于频率统计
                $extra['template_id'] = $templateId;
            }
            
            $result = UserMessage::create([
                'user_id' => $userId,
                'type' => $type,
                'sub_type' => $subType,
                'title' => $title,
                'content' => $content,
                'extra' => $extra,
                'is_read' => 0,
                'create_time' => time()
            ]);
            
            return true;
        } catch (\Exception $e) {
            \think\facade\Log::error('createMessage 创建失败: ' . json_encode([
                'user_id' => $userId,
                'type' => $type,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE));
            return false;
        }
    }

    /**
     * @notes 评论回复通知
     * @param int $commentUserId 被回复者用户ID
     * @param int $replyUserId 回复者用户ID
     * @param int $commentId 评论ID
     * @param string $content 回复内容
     * @param string $type 类型: question_comment 或 article_comment
     * @param int $qid 关联ID
     * @return bool
     */
    public static function commentReplyNotify($commentUserId, $replyUserId, $commentId, $content, $type, $qid)
    {
        // 不给自己发消息
        if ($commentUserId == $replyUserId) {
            \think\facade\Log::info('commentReplyNotify - 不给自己发消息', [
                'comment_user_id' => $commentUserId,
                'reply_user_id' => $replyUserId
            ]);
            return false;
        }

        $replyUser = User::field('id,nickname,avatar')->find($replyUserId);
        if (!$replyUser) {
            \think\facade\Log::error('commentReplyNotify - 用户不存在', [
                'reply_user_id' => $replyUserId
            ]);
            return false;
        }

        $title = $replyUser['nickname'] . ' 回复了你的评论';
        $extra = [
            'sender_id' => $replyUserId,
            'sender_nickname' => $replyUser['nickname'],
            'sender_avatar' => $replyUser['avatar'],
            'comment_id' => $commentId,
            'qid' => $qid,
            'reply_content' => mb_substr($content, 0, 50) // 截取前50个字符
        ];
        

        return self::createMessage(
            $commentUserId,
            UserMessage::TYPE_COMMENT_REPLY,
            $title,
            $content,
            $extra,
            $type
        );
    }

    /**
     * @notes 评论点赞通知
     * @param int $commentUserId 评论作者用户ID
     * @param int $likeUserId 点赞者用户ID
     * @param int $commentId 评论ID
     * @param string $commentContent 评论内容
     * @param string $type 类型
     * @param int $qid 关联ID
     * @return bool
     */
    public static function commentLikeNotify($commentUserId, $likeUserId, $commentId, $commentContent, $type, $qid)
    {
        if ($commentUserId == $likeUserId) {
            \think\facade\Log::info('commentLikeNotify - 不给自己发消息', [
                'comment_user_id' => $commentUserId,
                'like_user_id' => $likeUserId
            ]);
            return false;
        }

        $likeUser = User::field('id,nickname,avatar')->find($likeUserId);
        if (!$likeUser) {
            \think\facade\Log::error('commentLikeNotify - 用户不存在', [
                'like_user_id' => $likeUserId
            ]);
            return false;
        }

        $title = $likeUser['nickname'] . ' 赞了你的评论';
        $content = mb_substr($commentContent, 0, 100);
        $extra = [
            'sender_id' => $likeUserId,
            'sender_nickname' => $likeUser['nickname'],
            'sender_avatar' => $likeUser['avatar'],
            'comment_id' => $commentId,
            'qid' => $qid
        ];
        

        return self::createMessage(
            $commentUserId,
            UserMessage::TYPE_COMMENT_LIKE,
            $title,
            $content,
            $extra,
            $type
        );
    }

    /**
     * @notes 评论赞赏通知
     * @param int $commentUserId 被赞赏者用户ID
     * @param int $rewardUserId 赞赏者用户ID
     * @param int $commentId 评论ID
     * @param int $integral 赞赏积分
     * @param string $type 类型
     * @param int $qid 关联ID
     * @return bool
     */
    public static function commentRewardNotify($commentUserId, $rewardUserId, $commentId, $integral, $type, $qid)
    {
        if ($commentUserId == $rewardUserId) {
            \think\facade\Log::info('commentRewardNotify - 不给自己发消息', [
                'comment_user_id' => $commentUserId,
                'reward_user_id' => $rewardUserId
            ]);
            return false;
        }

        $rewardUser = User::field('id,nickname,avatar')->find($rewardUserId);
        if (!$rewardUser) {
            \think\facade\Log::error('commentRewardNotify - 用户不存在', [
                'reward_user_id' => $rewardUserId
            ]);
            return false;
        }

        $title = $rewardUser['nickname'] . ' 赞赏了你的评论';
        $content = '赞赏 ' . $integral . ' 积分';
        $extra = [
            'sender_id' => $rewardUserId,
            'sender_nickname' => $rewardUser['nickname'],
            'sender_avatar' => $rewardUser['avatar'],
            'comment_id' => $commentId,
            'integral' => $integral,
            'qid' => $qid
        ];
        

        return self::createMessage(
            $commentUserId,
            UserMessage::TYPE_COMMENT_REWARD,
            $title,
            $content,
            $extra,
            $type
        );
    }

    /**
     * @notes 积分变化通知
     * @param int $userId 用户ID
     * @param string $action 动作: 增加/减少
     * @param int $integral 积分数量
     * @param string $reason 原因
     * @return bool
     */
    public static function integralChangeNotify($userId, $action, $integral, $reason)
    {
        $title = '积分' . $action;
        $content = $reason . '，' . $action . $integral . '积分';
        $extra = [
            'action' => $action,
            'integral' => $integral,
            'reason' => $reason
        ];

        return self::createMessage(
            $userId,
            UserMessage::TYPE_SYSTEM,
            $title,
            $content,
            $extra,
            UserMessage::SUBTYPE_INTEGRAL_CHANGE
        );
    }

    /**
     * @notes 会员到期提醒
     * @param int $userId 用户ID
     * @param int $days 剩余天数
     * @return bool
     */
    public static function vipExpireRemind($userId, $days)
    {
        $title = '会员到期提醒';
        $content = '您的会员将在 ' . $days . ' 天后到期，请及时续费';
        $extra = [
            'days' => $days
        ];

        return self::createMessage(
            $userId,
            UserMessage::TYPE_SYSTEM,
            $title,
            $content,
            $extra,
            UserMessage::SUBTYPE_VIP_REMIND
        );
    }

    /**
     * @notes 会员状态变更通知
     * @param int $userId 用户ID
     * @param string $status 状态描述
     * @param int $vipState 会员状态值
     * @return bool
     */
    public static function vipStatusChangeNotify($userId, $status, $vipState)
    {
        $title = '会员状态变更';
        $content = '您的会员状态已变更为：' . $status;
        $extra = [
            'status' => $status,
            'vip_state' => $vipState
        ];

        return self::createMessage(
            $userId,
            UserMessage::TYPE_SYSTEM,
            $title,
            $content,
            $extra,
            UserMessage::SUBTYPE_VIP_CHANGE
        );
    }

    /**
     * @notes 每周学习总结
     * @param int $userId 用户ID
     * @param int $answerCount 答题数量
     * @param int $errorCount 错题数量
     * @param float $accuracy 正确率
     * @return bool
     */
    public static function weeklySummaryNotify($userId, $answerCount, $errorCount, $accuracy)
    {
        $title = '每周学习总结';
        $content = '本周答题 ' . $answerCount . ' 题，错题 ' . $errorCount . ' 题，正确率 ' . $accuracy . '%';
        $extra = [
            'answer_count' => $answerCount,
            'error_count' => $errorCount,
            'accuracy' => $accuracy
        ];

        return self::createMessage(
            $userId,
            UserMessage::TYPE_SYSTEM,
            $title,
            $content,
            $extra,
            UserMessage::SUBTYPE_WEEKLY_SUMMARY
        );
    }

    /**
     * @notes 获取未读消息数量（分组统计）
     * @param int $userId 用户ID
     * @return array
     */
    public static function getUnreadCount($userId)
    {
        $userId = intval($userId) ?? 0;
        if (empty($userId) || $userId <= 0) {
            return [];
        }
        $counts = UserMessage::where('user_id', $userId)
            ->where('is_read', 0)
            ->where('delete_time', null)
            ->group('type')
            ->column('count(*) as count', 'type');

        return [
            'comment' => intval($counts[UserMessage::TYPE_COMMENT_REPLY] ?? 0), // 评论回复
            'like' => intval($counts[UserMessage::TYPE_COMMENT_LIKE] ?? 0),     // 爱心
            'reward' => intval($counts[UserMessage::TYPE_COMMENT_REWARD] ?? 0), // 赞赏
            'system' => intval($counts[UserMessage::TYPE_SYSTEM] ?? 0),         // 系统
            'total' => array_sum($counts)                                       // 总数
        ];
    }

    /**
     * @notes 标记消息为已读
     * @param int $userId 用户ID
     * @param array|int|null $messageIds 消息ID（null表示全部已读）
     * @return bool
     */
    public static function markAsRead($userId, $messageIds = null)
    {
        $query = UserMessage::where('user_id', $userId)
            ->where('is_read', 0);

        if ($messageIds !== null) {
            $query->whereIn('id', is_array($messageIds) ? $messageIds : [$messageIds]);
        }

        return $query->update([
            'is_read' => 1,
            'read_time' => time()
        ]);
    }

    /**
     * @notes 版本更新通知
     * @param string $version 版本号
     * @param string $info 更新内容
     * @param int $releaseTime 发布时间
     * @param int $tenantId 租户ID
     * @return bool
     */
    public static function versionUpdateNotify($version, $info, $releaseTime, $tenantId = 1)
    {
        try {
            // 获取所有订阅了版本更新通知的用户
            $subscribedUsers = \think\facade\Db::table('la_user_subscribe')
                ->where('tenant_id', $tenantId)
                ->where('template_id', 'app_version_update')
                ->where('status', 1)
                ->column('user_id');

            if (empty($subscribedUsers)) {
                \think\facade\Log::info('versionUpdateNotify 没有订阅用户', [
                    'tenant_id' => $tenantId,
                    'template_id' => 'app_version_update'
                ]);
                return false;
            }

            $title = '新版本发布通知';
            $content = $version . ' 版本已发布';
            $extra = [
                'version' => $version,
                'info' => $info,
                'release_time' => $releaseTime,
                'release_time_format' => date('Y-m-d H:i:s', $releaseTime)
            ];

            $successCount = 0;
            foreach ($subscribedUsers as $userId) {
                $result = self::createMessage(
                    $userId,
                    UserMessage::TYPE_SYSTEM,
                    $title,
                    $content,
                    $extra,
                    UserMessage::SUBTYPE_VERSION_UPDATE,
                    'app_version_update',
                    $tenantId
                );

                if ($result) {
                    $successCount++;
                }
            }

            \think\facade\Log::info('versionUpdateNotify 发送完成', [
                'tenant_id' => $tenantId,
                'version' => $version,
                'total_users' => count($subscribedUsers),
                'success_count' => $successCount
            ]);

            return $successCount > 0;
        } catch (\Exception $e) {
            \think\facade\Log::error('versionUpdateNotify 发送失败: ' . json_encode([
                'tenant_id' => $tenantId,
                'version' => $version,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE));
            return false;
        }
    }
}
