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

namespace app\tenantapi\lists\exam;

use app\tenantapi\lists\BaseAdminDataLists;
use app\common\model\exam\TenantExamExaminationHistory;
use app\common\lists\ListsSearchInterface;


/**
 * 做题记录表列表
 * Class TenantExamRecordLists
 * @package app\tenantapi\list\sexam
 */
class TenantExamRecordLists extends BaseAdminDataLists implements ListsSearchInterface
{


    /**
     * @notes 搜索字段
     * @return array
     * @author 精解析答题
     * @date 2025/07/19 14:47
     */
    public function setSearch(): array
    {
        return [
            '=' => ['questions_type', 'exam_time', 'submit_time', 'user_uid'],
        ];
    }

    /**
     * @notes 搜索条件
     * @author 段誉
     * @date 2023/2/24 15:26
     */
    public function queryWhere()
    {
        $where = [];

        if (!empty($this->params['exam_start_time'])) {
            $where[] = ['create_time', '>=', strtotime($this->params['exam_start_time']) * 1000];
        }

        if (!empty($this->params['exam_end_time'])) {
            $where[] = ['create_time', '<=', strtotime($this->params['exam_end_time']) * 1000];
        }

        if (!empty($this->params['submit_start_time'])) {
            $where[] = ['submit_time', '>=', strtotime($this->params['submit_start_time']) * 1000];
        }

        if (!empty($this->params['submit_end_time'])) {
            $where[] = ['submit_time', '<=', strtotime($this->params['submit_end_time']) * 1000];
        }
        
        return $where;
    }                                                                                    

    /**                                                                                
     * @notes 获取做题记录表列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 精解析答题
     * @date 2025/07/19 14:47
     */
    public function lists(): array
    {    
        try {
            // 当做题类型 questions_type 为 0 时，表示查询所有类型的做题记录
            if ($this->params['questions_type'] == 0) {
               $this->searchWhere[0] = ['questions_type', '<>', 0];
            }else{
                $this->searchWhere[0] = ['questions_type', '=', $this->params['questions_type']];
            }
            // 记录查询条件
            trace($this->searchWhere, 'record_search_conditions');
            
            $lists = TenantExamExaminationHistory::where($this->searchWhere)
                ->where($this->queryWhere())
                ->field([
                    'id', 'uid', 'questions_type', 'user_score', 'paper_score', 
                    'tenant_id', 'create_time', 'submit_time', 'correct_count', 
                    'error_count', 'user_uid', 'title', 'examination_uid', 'paper_uid',
                    'total_count', 'answered_count', 'accuracy_rate', 'practice_duration'
                ])
                ->with([
                    'user' => function($query) {
                        $query->field('id,nickname,avatar,sn');
                    },
                    'library' => function($query) {
                        $query->field('uid,title');
                    },
                ])
                ->limit($this->limitOffset, $this->limitLength)
                ->order(['id' => 'desc'])
                ->select()
                ->toArray();
            
            // 处理数据
            foreach ($lists as &$item) {
                // 用户信息
                $item['user_nickname'] = $item['user']['nickname'] ?? '-';
                $item['user_avatar'] = $item['user']['avatar'] ?? '';
                $item['user_sn'] = $item['user']['sn'] ?? '-';
                
                // 题库/考试信息
                $item['library_title'] = $item['library']['title'] ?? ($item['paper']['title'] ?? '-');
                
                // 时间格式化
                $item['exam_time'] = $item['create_time'];
                $item['exam_submit_time'] = $item['submit_time'];
                
                // 删除关联数据，避免前端接收冗余信息
                unset($item['user'], $item['library'], $item['paper']);
            }
            
            return $lists;
        } catch (\Exception $e) {
            // 记录异常信息
            trace($e->getMessage(), 'exercise_record_error');
            trace($e->getTraceAsString(), 'exercise_record_trace');
            throw $e; // 保持原有错误响应
        }
    }


    /**
     * @notes 获取做题记录表数量
     * @return int
     * @author 精解析答题
     * @date 2025/07/19 14:47
     */
    public function count(): int
    {
        try {
            return TenantExamExaminationHistory::where($this->searchWhere)->where([])->count();
        } catch (\Exception $e) {
            // 记录日志
            \think\facade\Log::error('获取考试管理数量失败: ' . $e->getMessage());
            return 0;
        }
    }

}