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

namespace app\api\lists\exam;

use app\api\lists\BaseApiDataLists;
use app\common\model\exam\TenantExamComment;
use app\common\model\exam\TenantExamCommentLike;


/**
 * 评论列表
 * Class CommentLists
 * @package app\api\lists\exam
 */
class CommentLists extends BaseApiDataLists
{


    // 我的笔记列表
    public function myCommentList(array $params): array
    {
        $user_id = $params['user_id'] ?? $this->userId;
        
        // 获取我的笔记列表
        try {
            // 主查询构建，默认只查询当前用户的笔记
            $queryBuilder = TenantExamComment::where([
                'pid' => 0,
                'status' => 1,
                'user_id' => $user_id
            ]);
            
            // 添加题库分类 筛选
            if (isset($params['category_uid']) && $params['category_uid'] != '') {
                $queryBuilder->where('category_uid', $params['category_uid']);
            }
            
            // 添加题库科目筛选
            if (isset($params['library_uid']) && $params['library_uid'] != '') {
                $queryBuilder->where('library_uid', $params['library_uid']);
            }

            // 添加type筛选
            if (isset($params['type'])) {
                $queryBuilder->where('type', $params['type']);
            }
            
            // 添加qid筛选（可选）
            if (isset($params['qid']) && !empty($params['qid'])) {
                $queryBuilder->where('qid', $params['qid']);
            }

            
            // 根据评论类型确定关联关系
            // 类型1: 试题评论，关联问题和用户信息
            // 类型2: 文章评论，关联文章和用户信息
            // 类型3: 资源评论，关联资源和用户信息
            $relationMap = [
                1 => ['user','question'],
                2 => ['user','article'],
                3 => ['user','resource']
            ];
            $with = $relationMap[$params['type'] ?? 1] ?? ['user','question'];
            
            // 先获取全部评论，再在PHP层面实现排序
            // 逐个调用with方法，确保所有关联关系都能正确加载
            $query = $queryBuilder
                ->field('id,user_id,qid,pid,content,comments,likes,create_time,tenant_id,type')
                ->with($with);
            
            $allComments = $query
                ->order(['create_time' => 'desc']) // 我的笔记按创建时间降序
                ->select()->toArray();
            
            // 查询所有评论的赞赏总积分
            if (!empty($allComments)) {
                $commentIds = array_column($allComments, 'id');
                
                // 批量查询赞赏统计
                $rewardStats = \think\facade\Db::name('tenant_exam_comment_reward')
                    ->field('comment_id, SUM(integral) as total_reward')
                    ->whereIn('comment_id', $commentIds)
                    ->group('comment_id')
                    ->select()
                    ->toArray();
                
                // 转换为关联数组
                $rewardMap = [];
                foreach ($rewardStats as $stat) {
                    $rewardMap[$stat['comment_id']] = (int)$stat['total_reward'];
                }
                
                // 更新评论数据，添加reward_integral字段
                foreach ($allComments as $key => $comment) {
                    $allComments[$key]['reward_integral'] = $rewardMap[$comment['id']] ?? 0;
                }
            }
            
            // 分页截取
            $query = array_slice($allComments, $this->limitOffset, $this->limitLength);
            
            // 如果用户已登录，优化点赞状态查询
            if ($user_id && !empty($query)) {
                // 获取所有评论 ID和子评论 ID
                $commentIds = [];
                foreach ($query as $comment) {
                    $commentIds[] = $comment['id'];
                }
                            
                // 去重
                $commentIds = array_unique($commentIds);
                            
                // 一次性查询所有点赞记录
                $likeRecords = TenantExamCommentLike::where('user_id', $user_id)
                    ->where('comment_id', 'IN', $commentIds)
                    ->column('comment_id');
                            
                // 批量查询点赞用户列表（前5名）
                $likersData = $this->getLikersForComments($commentIds);
                            
                // 使用数组映射设置点赞状态
                $likedComments = array_flip($likeRecords);
                            
                // 更新评论列表的点赞状态和点赞用户列表
                foreach ($query as $key => $comment) {
                    $query[$key]['user_like'] = isset($likedComments[$comment['id']]) ? true : false;
                    $query[$key]['like_users'] = $likersData[$comment['id']] ?? [];
                    
                    // 查询该评论的子评论总数（不受limit限制）
                    $childrenCount = TenantExamComment::where('pid', $comment['id'])
                        ->where('status', 1)
                        ->count();
                    $query[$key]['children_count'] = $childrenCount;
                                
                }
            } else {
                // 用户未登录，设置默认点赞状态但仍然查询点赞用户列表
                $commentIds = [];
                foreach ($query as $comment) {
                    $commentIds[] = $comment['id'];
                }
                            
                $commentIds = array_unique($commentIds);
                $likersData = $this->getLikersForComments($commentIds);
                            
                foreach ($query as $key => $comment) {
                    $query[$key]['user_like'] = false;
                    $query[$key]['like_users'] = $likersData[$comment['id']] ?? [];
                    
                    // 查询该评论的子评论总数（不受limit限制）
                    $childrenCount = TenantExamComment::where('pid', $comment['id'])
                        ->where('status', 1)
                        ->count();
                    $query[$key]['children_count'] = $childrenCount;
                                
                }
            }
            return $query;
        } catch (\Exception $e) {
            // 记录错误日志，包含更多上下文信息
            \think\facade\Log::error('获取我的笔记列表失败: ' . $e->getMessage() . ', 参数: ' . json_encode($params));
            throw new \Exception('获取我的笔记列表失败，请稍后重试');
        }
    }
    
    // 全局评论列表
    public function lists(): array
    {
        $params = $this->params;
        $user_id = $this->userId;
        
        // 获取全局评论列表
        try {
            // 主查询构建，不包含user_id筛选，用于全局评论列表
            $queryBuilder = TenantExamComment::where([
                'pid' => 0,
                'status' => 1
            ]);
            
            // 添加type筛选
            if (isset($params['type'])) {
                $queryBuilder->where('type', $params['type']);
            }
            
            // 添加qid筛选（可选）
            if (isset($params['qid']) && !empty($params['qid'])) {
                $queryBuilder->where('qid', $params['qid']);
            }

            // 先获取全部评论，再在PHP层面实现排序
            // 逐个调用with方法，确保所有关联关系都能正确加载
            $query = $queryBuilder
                ->field('id,user_id,qid,pid,content,comments,likes,create_time,tenant_id,type')
                ->with([
                    'children' => function ($query2){
                    $query2->where('status', 1)
                        ->field(['id,user_id,qid,pid,content,comments,likes,create_time,type'])
                        ->with('user')
                        ->limit(3)  // 子评论最多返回3条
                        ->order(['create_time' => 'asc']); // 子评论按时间正序
                },
                'user' => function($query) {
                        $query->field('id,nickname,avatar,sn');
                    },
            ]);
        
            
            $allComments = $query
                ->order(['likes' => 'desc', 'create_time' => 'desc']) // 全局评论按点赞数降序，再按时间降序
                ->select()->toArray();
            
            // 查询所有评论的赞赏总积分
            if (!empty($allComments)) {
                $commentIds = array_column($allComments, 'id');
                
                // 批量查询赞赏统计
                $rewardStats = \think\facade\Db::name('tenant_exam_comment_reward')
                    ->field('comment_id, SUM(integral) as total_reward')
                    ->whereIn('comment_id', $commentIds)
                    ->group('comment_id')
                    ->select()
                    ->toArray();
                
                // 转换为关联数组
                $rewardMap = [];
                foreach ($rewardStats as $stat) {
                    $rewardMap[$stat['comment_id']] = (int)$stat['total_reward'];
                }
                
                // 同时查询子评论的赞赏
                $childCommentIds = [];
                foreach ($allComments as $comment) {
                    if (!empty($comment['children'])) {
                        foreach ($comment['children'] as $child) {
                            $childCommentIds[] = $child['id'];
                        }
                    }
                }
                
                if (!empty($childCommentIds)) {
                    $childRewardStats = \think\facade\Db::name('tenant_exam_comment_reward')
                        ->field('comment_id, SUM(integral) as total_reward')
                        ->whereIn('comment_id', $childCommentIds)
                        ->group('comment_id')
                        ->select()
                        ->toArray();
                    
                    foreach ($childRewardStats as $stat) {
                        $rewardMap[$stat['comment_id']] = (int)$stat['total_reward'];
                    }
                }
                
                // 更新评论数据，添加reward_integral字段
                foreach ($allComments as $key => $comment) {
                    $allComments[$key]['reward_integral'] = $rewardMap[$comment['id']] ?? 0;
                    
                    // 更新子评论的reward_integral
                    if (!empty($comment['children'])) {
                        foreach ($comment['children'] as $childKey => $child) {
                            $allComments[$key]['children'][$childKey]['reward_integral'] = $rewardMap[$child['id']] ?? 0;
                        }
                    }
                }
            }
            
            // 自定义排序：前5名按点赞数，第6名开始按时间
            if (count($allComments) > 5) {
                // 分离前5名和其余评论
                $top5 = array_slice($allComments, 0, 5);
                $rest = array_slice($allComments, 5);
                
                // 剩余评论按时间降序排序
                usort($rest, function($a, $b) {
                    return strtotime($b['create_time']) - strtotime($a['create_time']);
                });
                
                // 合并
                $allComments = array_merge($top5, $rest);
            }
            
            // 分页截取
            $query = array_slice($allComments, $this->limitOffset, $this->limitLength);
            
            // 如果用户已登录，优化点赞状态查询
            if ($user_id && !empty($query)) {
                // 获取所有评论 ID和子评论 ID
                $commentIds = [];
                foreach ($query as $comment) {
                    $commentIds[] = $comment['id'];
                    // 即使 children 为空也进行处理，避免 undefined 索引
                    if (!empty($comment['children'])) {
                        foreach ($comment['children'] as $child) {
                            $commentIds[] = $child['id'];
                        }
                    }
                }
                            
                // 去重
                $commentIds = array_unique($commentIds);
                            
                // 一次性查询所有点赞记录
                $likeRecords = TenantExamCommentLike::where('user_id', $user_id)
                    ->where('comment_id', 'IN', $commentIds)
                    ->column('comment_id');
                            
                // 批量查询点赞用户列表（前5名）
                $likersData = $this->getLikersForComments($commentIds);
                            
                // 使用数组映射设置点赞状态
                $likedComments = array_flip($likeRecords);
                            
                // 更新评论列表的点赞状态和点赞用户列表
                foreach ($query as $key => $comment) {
                    $query[$key]['user_like'] = isset($likedComments[$comment['id']]) ? true : false;
                    $query[$key]['like_users'] = $likersData[$comment['id']] ?? [];
                    
                    // 查询该评论的子评论总数（不受limit限制）
                    $childrenCount = TenantExamComment::where('pid', $comment['id'])
                        ->where('status', 1)
                        ->count();
                    $query[$key]['children_count'] = $childrenCount;
                                
                    // 处理子评论的点赞状态和点赞用户列表
                    if (!empty($comment['children'])) {
                        foreach ($comment['children'] as $childKey => $child) {
                            $query[$key]['children'][$childKey]['user_like'] = isset($likedComments[$child['id']]) ? true : false;
                            $query[$key]['children'][$childKey]['like_users'] = $likersData[$child['id']] ?? [];
                        }
                    }
                }
            } else {
                // 用户未登录，设置默认点赞状态但仍然查询点赞用户列表
                $commentIds = [];
                foreach ($query as $comment) {
                    $commentIds[] = $comment['id'];
                    if (!empty($comment['children'])) {
                        foreach ($comment['children'] as $child) {
                            $commentIds[] = $child['id'];
                        }
                    }
                }
                            
                $commentIds = array_unique($commentIds);
                $likersData = $this->getLikersForComments($commentIds);
                            
                foreach ($query as $key => $comment) {
                    $query[$key]['user_like'] = false;
                    $query[$key]['like_users'] = $likersData[$comment['id']] ?? [];
                    
                    // 查询该评论的子评论总数（不受limit限制）
                    $childrenCount = TenantExamComment::where('pid', $comment['id'])
                        ->where('status', 1)
                        ->count();
                    $query[$key]['children_count'] = $childrenCount;
                                
                    // 子评论也设置默认点赞状态和点赞用户列表
                    if (!empty($comment['children'])) {
                        foreach ($comment['children'] as $childKey => $child) {
                            $query[$key]['children'][$childKey]['user_like'] = false;
                            $query[$key]['children'][$childKey]['like_users'] = $likersData[$child['id']] ?? [];
                        }
                    }
                }
            }
            return $query;
        } catch (\Exception $e) {
            // 记录错误日志，包含更多上下文信息
            \think\facade\Log::error('获取全局评论列表失败: ' . $e->getMessage() . ', 参数: ' . json_encode($params));
            throw new \Exception('获取全局评论列表失败，请稍后重试');
        }
    }

    
    /**
     * @notes 批量获取评论的点赞用户列表（每个评论只返回前5名）
     * @param array $commentIds 评论 ID数组
     * @return array 格式：[comment_id => [{user_id, nickname, avatar}, ...]]
     */
    private function getLikersForComments(array $commentIds): array
    {
        if (empty($commentIds)) {
            return [];
        }
        
        // 一次性查询所有评论的点赞记录，按点赞时间排序
        $likeRecords = TenantExamCommentLike::whereIn('comment_id', $commentIds)
            ->with([
                'user' => function($query) {
                $query->field('id,nickname,avatar');
            }
            ])
            ->field('comment_id,user_id,create_time')
            ->order('create_time', 'asc') // 按点赞时间正序，先点赞的在前
            ->select()
            ->toArray();
        
        // 按评论 ID分组，每组只取前5个
        $result = [];
        foreach ($likeRecords as $record) {
            $commentId = $record['comment_id'];
            
            if (!isset($result[$commentId])) {
                $result[$commentId] = [];
            }
            
            // 每个评论最多返回5个点赞用户
            if (count($result[$commentId]) < 5 && !empty($record['user'])) {
                $avatar = $record['user']['avatar'] ?? '';
                
                // 调试日志：输出用户头像
                \think\facade\Log::info('点赞用户头像: ' . json_encode([
                    'user_id' => $record['user_id'],
                    'nickname' => $record['user']['nickname'] ?? '匿名用户',
                    'avatar' => $avatar,
                    'comment_id' => $commentId
                ], JSON_UNESCAPED_UNICODE));
                
                $result[$commentId][] = [
                    'user_id' => $record['user_id'],
                    'nickname' => $record['user']['nickname'] ?? '匿名用户',
                    'avatar' => $avatar
                ];
            }
        }
        
        return $result;
    }

    /**
     * @notes 获取评论数量（全局评论）
     * @return int
     * @author 段誉
     * @date 2022/9/16 18:55
     */
    public function count(): int
    {
        $query = TenantExamComment::where([
            'pid' => 0,
            'status' => 1
        ]);
        
        // 添加type筛选
        if (isset($this->params['type'])) {
            $query->where('type', $this->params['type']);
        }
        
        // 添加qid筛选（可选）
        if (isset($this->params['qid']) && !empty($this->params['qid'])) {
            $query->where('qid', $this->params['qid']);
        }
        
        return $query->count();
    }
    
    /**
     * @notes 获取我的笔记数量
     * @return int
     */
    public function myCommentCount(): int
    {
        $query = TenantExamComment::where([
            'pid' => 0,
            'status' => 1,
            'user_id' => $this->userId
        ]);
        
        // 添加type筛选
        if (isset($this->params['type'])) {
            $query->where('type', $this->params['type']);
        }
        
        // 添加qid筛选（可选）
        if (isset($this->params['qid']) && !empty($this->params['qid'])) {
            $query->where('qid', $this->params['qid']);
        }
        
        return $query->count();
    }
}