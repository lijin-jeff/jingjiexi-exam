<?php
declare(strict_types=1);
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 精解析答题系统开发者版权所有，拥有最终解释权。
namespace app\api\logic\exam;

use app\common\logic\BaseLogic;
use app\common\model\exam\TenantExamComment;
use app\common\model\exam\TenantExamCommentLike;

class CommentLogic extends BaseLogic
{

/**
 * @description 全局添加评论
 * @return { Promise }
 * @author 精解析题库
 * @date 2025/08/26 09:13
 */
public static function addComment($params)
{
    // 1. 基础参数处理
    $tenantId = request()->tenantId;
    $pid = 0;
    $parentComment = null;
    
    // 2. 简化PID校验（仅确保是数字，无需担心外键）
    if (!empty($params['pid']) && is_numeric($params['pid'])) {
        $pid = (int)$params['pid'];
        // 仅当pid>0时，查询父评论（用于发送通知，非外键校验）
        if ($pid > 0) {
            $parentComment = TenantExamComment::field('id,user_id,type,qid,status')
                ->where('id', $pid)
                ->where('tenant_id', $tenantId)
                ->where('status', 1)
                ->whereNull('delete_time')
                ->find();
            // 父评论不存在，仍保留pid（外键已删除，不影响插入）
            if (!$parentComment) {
                \think\facade\Log::warning("父评论不存在: pid={$pid}, tenant_id={$tenantId}");
            }
        }
    }

    // 3. 处理其他参数
    $libraryUid = $params['library_uid'] ?? '';
    $categoryUid = $params['category_uid'] ?? '';
    $currentTime = time();

    // 4. 插入数据库（此时无外键约束，pid=0/任意值都可插入）
    try {
        $newComment = TenantExamComment::create([
            'type'         => $params['type'] ?? 1,
            'qid'          => $params['qid'] ?? '',
            'library_uid'  => $libraryUid,
            'category_uid' => $categoryUid,
            'user_id'      => $params['user_id'] ?? 0,
            'pid'          => $pid, // 无需兜底，外键已删除
            'tenant_id'    => $tenantId,
            'ip'           => request()->ip(),
            'content'      => $params['content'] ?? '',
            'comments'     => 0,
            'likes'        => 0,
            'status'       => 1,
            'create_time'  => $currentTime,
            'update_time'  => $currentTime
        ]);
    } catch (\Exception $e) {
        \think\facade\Log::error("插入评论失败: " . $e->getMessage() . " | 参数: " . json_encode($params));
        throw new \Exception("添加评论失败", 412);
    }

    // 5. 发送回复通知（仅当父评论存在且非自己回复时）
    if ($pid > 0 && $parentComment && $parentComment['user_id'] != $params['user_id']) {
        $subType = $params['type'] == 2 ? 'article_comment' : 'question_comment';
        \app\common\logic\user\MessageService::commentReplyNotify(
            $parentComment['user_id'],
            $params['user_id'],
            $newComment->id,
            $params['content'],
            $subType,
            $params['qid']
        );
    }

    return true;
}

    /**
     * @description 全局编辑评论
     * @return { Promise }
     * @author 精解析题库
     * @date 2025/08/26 09:13
     */
    public static function editComment($params)
    {
        // 检查评论是否存在
        $comment = TenantExamComment::where('id', $params['id'])
            ->where('tenant_id', request()->tenantId)
            ->find();
            
        if (!$comment) {
            self::setError('评论不存在');
            return false;
        }
        
        // 检查用户权限
        if ($comment->user_id !== $params['user_id']) {
            self::setError('无权限操作该笔记');
            return false;
        }
            
        // 更新评论内容和更新时间
        $comment->content = $params['content'];
        $comment->update_time = time(); // 使用Unix时间戳格式
        $comment->save();
            
        return true;
    }

    /**
     * @description 全局删除评论（逻辑删除）
     * @return { Promise }
     * @author 精解析题库
     * @date 2025/08/26 09:13
     */
    public static function deleteComment($params)
    {
        $tenantId = request()->tenantId;
        // 检查评论是否存在
        $comment = TenantExamComment::where('id', $params['id'])
            ->where('tenant_id', $tenantId)
            ->find();
            
        if (!$comment) {
            self::setError('评论不存在');
            return false;
        }
        
        // 检查用户权限
        if ($comment->user_id !== $params['user_id']) {
            self::setError('无权限操作该笔记');
            return false;
        }
        self::deleteChildComments($params['id'], $tenantId);
        // 删除评论
        $comment->delete();
            
        return true;
    }

    /**
     * @description 递归删除子评论
     * @param int $parentId 父评论ID
     * @param int $tenantId 租户ID
     */
    private static function deleteChildComments($parentId, $tenantId)
    {
        // 1. 查询当前父评论的所有子评论ID
        $childIds = TenantExamComment::where('pid', $parentId)
            ->where('tenant_id', $tenantId)
            ->column('id');
        
        // 2. 递归删除子评论的子评论
        foreach ($childIds as $childId) {
            self::deleteChildComments($childId, $tenantId);
        }
        
        // 3. 删除当前层级的子评论
        if (!empty($childIds)) {
            TenantExamComment::whereIn('id', $childIds)
                ->where('tenant_id', $tenantId)
                ->delete(); // 软删除同理：update(['delete_time' => time()])
        }
    }

        /**
     * @notes 获取评论详情（笔记详情）
     * @param int $commentId 评论ID
     * @param int|null $userId 用户ID
     * @return array|bool
     * @author 精解析题库
     * @date 2025/12/27
     */
    public static function getCommentDetail($commentId, $userId = null)
    {
        try {
            // 获取当前租户ID
            $tenantId = request()->tenantId;
            
            // 调试日志
            \think\facade\Log::info('getCommentDetail 开始: ' . json_encode([
                'comment_id' => $commentId,
                'user_id' => $userId,
                'tenant_id' => $tenantId
            ], JSON_UNESCAPED_UNICODE));
            
            // 查询主评论（不使用with，避免关联查询问题）
            $comment = TenantExamComment::where('id', $commentId)
                ->where('tenant_id', $tenantId)
                ->where('status', 1)
                ->where('type', 1)
                ->with('question')
                ->field('id,user_id,qid,pid,content,comments,likes,create_time,type,tenant_id')
                ->find();
            
            if (!$comment) {
                \think\facade\Log::warning('getCommentDetail 评论不存在: ' . json_encode([
                    'comment_id' => $commentId,
                    'tenant_id' => $tenantId
                ], JSON_UNESCAPED_UNICODE));
                
                self::setError('评论不存在');
                return false;
            }
            
            $comment = $comment->toArray();
            
            // 手动添加reward_integral字段（数据库中不存在，但前端需要）
            $comment['reward_integral'] = 0;
            
            // 手动查询主评论的用户信息
            $mainUser = \app\common\model\user\User::where('id', $comment['user_id'])
                ->field('id,nickname,avatar')
                ->find();
            $comment['user'] = $mainUser ? $mainUser->toArray() : null;
            
            \think\facade\Log::info('getCommentDetail 主评论查询成功: ' . json_encode([
                'comment_id' => $comment['id'],
                'user_id' => $comment['user_id'],
                'has_user' => !empty($comment['user'])
            ], JSON_UNESCAPED_UNICODE));
            
            // 查询所有子评论（不分页）
            $children = TenantExamComment::where('pid', $commentId)
                ->where('tenant_id', $tenantId)
                ->where('status', 1)
                ->where('type', 1)
                ->field('id,user_id,qid,pid,content,comments,likes,create_time')
                ->order(['create_time' => 'asc'])
                ->select()
                ->toArray();
            
            \think\facade\Log::info('getCommentDetail 子评论查询成功: ' . json_encode([
                'children_count' => count($children)
            ], JSON_UNESCAPED_UNICODE));
            
            // 为每个子评论手动查询用户信息
            foreach ($children as $key => $child) {
                $childUser = \app\common\model\user\User::where('id', $child['user_id'])
                    ->field('id,nickname,avatar')
                    ->find();
                $children[$key]['user'] = $childUser ? $childUser->toArray() : null;
                // 手动添加reward_integral字段
                $children[$key]['reward_integral'] = 0;
                
                // 查询该子评论的回复（三级评论）
                $replies = TenantExamComment::where('pid', $child['id'])
                    ->where('tenant_id', $tenantId)
                    ->where('status', 1)
                    ->field('id,user_id,qid,pid,content,comments,likes,create_time')
                    ->order(['create_time' => 'asc'])
                    ->select()
                    ->toArray();
                
                // 为每个三级评论查询用户信息
                foreach ($replies as $rKey => $reply) {
                    $replyUser = \app\common\model\user\User::where('id', $reply['user_id'])
                        ->field('id,nickname,avatar')
                        ->find();
                    $replies[$rKey]['user'] = $replyUser ? $replyUser->toArray() : null;
                    $replies[$rKey]['reward_integral'] = 0;
                }
                
                $children[$key]['replies'] = $replies;
                $children[$key]['replies_count'] = count($replies);
            }
            
            $comment['children'] = $children;
            $comment['children_count'] = count($children);
            
            // 收集所有评论id（主评论 + 子评论 + 三级评论）
            $commentIds = [$comment['id']];
            foreach ($children as $child) {
                $commentIds[] = $child['id'];
                // 添加三级评论的ID
                if (!empty($child['replies'])) {
                    foreach ($child['replies'] as $reply) {
                        $commentIds[] = $reply['id'];
                    }
                }
            }
            
            // 批量查询点赞用户列表（每个评论前5名）
            $likersData = self::getLikersForComments($commentIds, $tenantId);
            
            // 设置主评论的点赞用户
            $comment['like_users'] = $likersData[$comment['id']] ?? [];
            
            // 设置子评论的点赞用户
            foreach ($comment['children'] as $key => $child) {
                $comment['children'][$key]['like_users'] = $likersData[$child['id']] ?? [];
                // 设置三级评论的点赞用户
                if (!empty($child['replies'])) {
                    foreach ($child['replies'] as $rKey => $reply) {
                        $comment['children'][$key]['replies'][$rKey]['like_users'] = $likersData[$reply['id']] ?? [];
                    }
                }
            }
            
            \think\facade\Log::info('getCommentDetail 查询结果: ' . json_encode([
                'comment_id' => $comment['id'],
                'children_count' => $comment['children_count'],
                'has_user' => isset($comment['user'])
            ], JSON_UNESCAPED_UNICODE));
            
            // 如果用户已登录，查询点赞状态
            if ($userId) {
                \think\facade\Log::info('getCommentDetail 开始查询点赞状态: ' . json_encode([
                    'user_id' => $userId,
                    'tenant_id' => $tenantId
                ], JSON_UNESCAPED_UNICODE));
                            
                // 收集所有评论id
                $commentIds = [$comment['id']];
                foreach ($children as $child) {
                    $commentIds[] = $child['id'];
                }
                            
                // 查询点赞记录（必须加tenant_id）
                $likeRecords = TenantExamCommentLike::where('user_id', $userId)
                    ->where('tenant_id', $tenantId)  // 关键：添加租户ID条件
                    ->where('comment_id', 'IN', $commentIds)
                    ->column('comment_id');
                            
                \think\facade\Log::info('getCommentDetail 点赞记录查询结果: ' . json_encode([
                    'like_count' => count($likeRecords),
                    'comment_ids_count' => count($commentIds)
                ], JSON_UNESCAPED_UNICODE));
                            
                $likedComments = array_flip($likeRecords);
                            
                // 设置主评论点赞状态
                $comment['user_like'] = isset($likedComments[$comment['id']]);
                            
                // 设置子评论点赞状态
                foreach ($comment['children'] as $key => $child) {
                    $comment['children'][$key]['user_like'] = isset($likedComments[$child['id']]);
                    // 设置三级评论点赞状态
                    if (!empty($child['replies'])) {
                        foreach ($child['replies'] as $rKey => $reply) {
                            $comment['children'][$key]['replies'][$rKey]['user_like'] = isset($likedComments[$reply['id']]);
                        }
                    }
                }
            } else {
                // 未登录，默认未点赞
                $comment['user_like'] = false;
                foreach ($comment['children'] as $key => $child) {
                    $comment['children'][$key]['user_like'] = false;
                    // 设置三级评论默认未点赞
                    if (!empty($child['replies'])) {
                        foreach ($child['replies'] as $rKey => $reply) {
                            $comment['children'][$key]['replies'][$rKey]['user_like'] = false;
                        }
                    }
                }
            }
            
            \think\facade\Log::info('getCommentDetail 成功完成: ' . json_encode([
                'comment_id' => $comment['id'],
                'has_user_like' => isset($comment['user_like'])
            ], JSON_UNESCAPED_UNICODE));
            
            return $comment;
            
        } catch (\Exception $e) {
            \think\facade\Log::error('getCommentDetail 异常: ' . json_encode([
                'comment_id' => $commentId,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString()
            ], JSON_UNESCAPED_UNICODE));
            
            self::setError('获取评论详情失败: ' . $e->getMessage());
            return false;
        } catch (\Throwable $e) {
            \think\facade\Log::error('getCommentDetail 严重错误: ' . json_encode([
                'comment_id' => $commentId,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ], JSON_UNESCAPED_UNICODE));
            
            self::setError('获取评论详情失败');
            return false;
        }
    }

    /**
     * @notes 批量获取评论的点赞用户列表（每个评论只返回前5名）
     * @param array $commentIds 评论 ID数组
     * @param int $tenantId 租户ID
     * @return array 格式：[comment_id => [{user_id, nickname, avatar}, ...]]
     */
    private static function getLikersForComments(array $commentIds, $tenantId): array
    {
        if (empty($commentIds)) {
            return [];
        }
        
        // 一次性查询所有评论的点赞记录，按点赞时间排序
        $likeRecords = TenantExamCommentLike::whereIn('comment_id', $commentIds)
            ->where('tenant_id', $tenantId)
            ->field('comment_id,user_id,create_time')
            ->order('create_time', 'asc') // 按点赞时间正序，先点赞的在前
            ->select()
            ->toArray();
        
        // 收集所有用户ID
        $userIds = array_unique(array_column($likeRecords, 'user_id'));
        
        // 批量查询用户信息
        $users = [];
        if (!empty($userIds)) {
            $userList = \app\common\model\user\User::whereIn('id', $userIds)
                ->field('id,nickname,avatar')
                ->select()
                ->toArray();
            foreach ($userList as $user) {
                $users[$user['id']] = $user;
            }
        }
        
        // 按评论 ID分组，每组只取5个
        $result = [];
        foreach ($likeRecords as $record) {
            $commentId = $record['comment_id'];
            
            if (!isset($result[$commentId])) {
                $result[$commentId] = [];
            }
            
            // 每个评论最多返回5个点赞用户
            if (count($result[$commentId]) < 5 && isset($users[$record['user_id']])) {
                $user = $users[$record['user_id']];
                $result[$commentId][] = [
                    'user_id' => $record['user_id'],
                    'nickname' => $user['nickname'] ?? '匿名用户',
                    'avatar' => $user['avatar'] ?? ''
                ];
            }
        }
        
        return $result;
    }

        /**
     * @notes 获取评论点赞用户列表
     * @param int $commentId 评论id
     * @param int $limit 返回数量
     * @return array|bool
     * @author 精解析题库
     * @date 2025/12/27
     */
    public static function getCommentLikeUsers($commentId, $limit = 5)
    {
        try {
            // 获取当前租户ID
            $tenantId = request()->tenantId;
            
            // 查询点赞该评论的用户
            $likeUsers = TenantExamCommentLike::where('comment_id', $commentId)
                ->where('tenant_id', $tenantId)
                ->order(['create_time' => 'desc'])
                ->limit($limit)
                ->column('user_id');
            
            if (empty($likeUsers)) {
                return [];
            }
            
            // 查询用户信息
            $users = \app\common\model\user\User::whereIn('id', $likeUsers)
                ->field('id,nickname,avatar')
                ->select()
                ->toArray();
            
            return $users;
            
        } catch (\Exception $e) {
            \think\facade\Log::error('getCommentLikeUsers 异常: ' . json_encode([
                'comment_id' => $commentId,
                'error_message' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE));
            
            self::setError('获取点赞用户列表失败');
            return false;
        }
    }

      /**
     * @notes 添加评论点赞逻辑
     * @param array $params 所需参数
     * @return bool
     * @author 示例作者
     * @date 2025/08/26 09:13
     */
    public static function addCommentLike(array $params)
    {
        // 添加点赞逻辑
        $params['tenant_id'] = request()->tenantId;
        $params['ip'] = request()->ip();
        $params['update_time'] = time();
        TenantExamCommentLike::create($params);
        
        // 增加对应评论的点赞数
        TenantExamComment::where('id', $params['comment_id'])->inc('likes')->update();
        
        // 获取评论信息，用于发送消息通知
        $comment = TenantExamComment::field('id,user_id,content,type,qid')
            ->find($params['comment_id']);
                
        if ($comment && $comment['user_id'] != $params['user_id']) {
            // 确定消息子类型
            $subType = $comment['type'] == 2 ? 'article_comment' : 'question_comment';
                    
                    
            // 发送评论点赞通知
            \app\common\logic\user\MessageService::commentLikeNotify(
                $comment['user_id'],        // 评论作者用户ID
                $params['user_id'],         // 点赞者用户ID
                $params['comment_id'],      // 评论id
                $comment['content'],        // 评论内容
                $subType,                   // 类型
                $comment['qid']             // 关联ID
            );
        } else {
            \think\facade\Log::info('不发送点赞通知: ' . ($comment ? '点赞自己的评论' : '评论不存在'));
        }
        
        return true;
    }
    
   /**
     * @notes 取消评论点赞逻辑
     * @param array $params 所需参数
     * @return bool
     * @author 精解析题库
     * @date 2025/08/26 09:13
     */
    public static function cancelCommentLike(array $params)
    {
        // 此处可添加具体的取消点赞逻辑
        TenantExamCommentLike::where('comment_id', $params['comment_id'])
            ->where('user_id', $params['user_id'])
            ->delete();
        // 减少对应评论的点赞数，将点赞数减1
        TenantExamComment::where('id', $params['comment_id'])
            ->where('likes', '>', 0)
            ->dec('likes')
            ->update();
        return true;
    }


}