<?php
// +----------------------------------------------------------------------
// | likeadmin快速开发前后端分离管理后台（PHP版）
// +----------------------------------------------------------------------

namespace app\api\controller;

use app\common\model\user\UserMessage;
use app\common\logic\user\MessageService;
use app\common\model\exam\TenantExamCommentReward;
use app\api\logic\MessageLogic;
use app\common\model\user\User;
use think\response\Json;

/**
 * 消息中心控制器
 * Class MessageController
 * @package app\api\controller
 */
class MessageController extends BaseApiController
{
    public array $notNeedLogin = ['unreadCount'];

    /**
     * @notes 获取消息列表
     * @return Json
     */
    public function lists()
    {
        // 获取所有请求参数（包括GET和POST）
        $params = $this->request->param();
        // 添加用户ID
        $params['user_id'] = $this->userId;
        
        $lists = MessageLogic::lists($params);
        return $this->success('获取成功', $lists);
    }

    /**
     * @notes 获取未读消息数量
     * @return Json
     */
    public function unreadCount()
    {
        $userId = $this->userId;
        $counts = MessageService::getUnreadCount($userId);

        return $this->success('获取成功', $counts);
    }

    /**
     * @notes 标记消息为已读
     * @return Json
     */
    public function markRead()
    {
        $messageIds = $this->request->post('message_ids', null); // null表示全部已读
        $userId = $this->userId;

        $result = MessageService::markAsRead($userId, $messageIds);

        if ($result !== false) {
            return $this->success('操作成功');
        } else {
            return $this->fail('操作失败');
        }
    }

    /**
     * @notes 删除消息
     * @return Json
     */
    public function delete()
    {
        $messageIds = $this->request->post('message_ids/a', []);
        $userId = $this->userId;

        if (empty($messageIds)) {
            return $this->fail('请选择要删除的消息');
        }

        $result = UserMessage::where('user_id', $userId)
            ->whereIn('id', $messageIds)
            ->update(['delete_time' => time()]);

        if ($result !== false) {
            return $this->success('删除成功');
        } else {
            return $this->fail('删除失败');
        }
    }

    /**
     * @notes 评论赞赏
     * @return Json
     */
    public function rewardComment()
    {
        $commentId = $this->request->post('comment_id/d', 0);
        $integral = $this->request->post('integral/d', 0);
        $remark = $this->request->post('remark/s', '');
        $userId = $this->userId;

        if (empty($commentId)) {
            return $this->fail('评论ID不能为空');
        }

        if ($integral <= 0) {
            return $this->fail('赞赏积分必须大于0');
        }

        // 获取评论信息
        $comment = \app\common\model\exam\TenantExamComment::with(['user'])->find($commentId);
        if (!$comment) {
            return $this->fail('评论不存在');
        }

        // 不能赞赏自己
        if ($comment['user_id'] == $userId) {
            return $this->fail('不能赞赏自己的评论');
        }

        // 检查用户积分是否足够
        $user = User::find($userId);
        if ($user['integral'] < $integral) {
            return $this->fail('积分不足');
        }

        try {
            // 开启事务
            \think\facade\Db::startTrans();

            // 扣除赞赏者积分
            User::where('id', $userId)->dec('integral', $integral)->update();

            // 增加被赞赏者积分
            User::where('id', $comment['user_id'])->inc('integral', $integral)->update();

            // 记录赞赏
            TenantExamCommentReward::create([
                'comment_id' => $commentId,
                'comment_user_id' => $comment['user_id'],
                'user_id' => $userId,
                'tenant_id' => $comment['tenant_id'] ?? 1,
                'qid' => $comment['qid'],
                'type' => $comment['type'] ?? 1,
                'integral' => $integral,
                'remark' => $remark,
                'create_time' => time()
            ]);

            // 记录积分变化
            MessageService::integralChangeNotify($userId, '减少', $integral, '赞赏评论');
            MessageService::integralChangeNotify($comment['user_id'], '增加', $integral, '收到评论赞赏');

            // 发送消息通知
            $type = $comment['type'] == 2 ? 'article_comment' : 'question_comment';
            
            MessageService::commentRewardNotify(
                $comment['user_id'],
                $userId,
                $commentId,
                $integral,
                $type,
                $comment['qid']
            );

            \think\facade\Db::commit();
            return $this->success('赞赏成功');

        } catch (\Exception $e) {
            \think\facade\Db::rollback();
            return $this->fail('赞赏失败：' . $e->getMessage());
        }
    }
}
