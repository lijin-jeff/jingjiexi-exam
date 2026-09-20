<?php
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 精解析答题系统开发者版权所有，拥有最终解释权。

namespace app\tenantapi\lists\exam;

use app\tenantapi\lists\BaseAdminDataLists;
use app\common\lists\ListsSearchInterface;
use app\common\model\exam\TenantExamExamination;


/**
 * 考试管理列表
 * Class TenantExamExaminationLists
 * @package app\platform\listsexam
 */
class TenantExamExaminationLists extends BaseAdminDataLists implements ListsSearchInterface
{


    /**
     * @notes 设置搜索条件
     * @return \string[][]
     * @author 精解析答题
     * @date 2025/04/13 21:43
     */
    public function setSearch(): array
    {
        return [
            '='      => ['is_show', 'privilege', 'submit_count_type', 'library_uid', 'library_category_uid'],
            '%like%' => ['title'],
            ">="     => ['start_time'],
            "<="     => ['end_time'],
        ];
    }


    /**
     * @notes 获取考试管理列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 精解析答题
     * @date 2025/04/13 21:43
     */
    public function lists(): array
    {
        try {
            return TenantExamExamination::where($this->searchWhere)
            ->where([])
            ->with(['paper' => function ($query) {
                $query->field(['uid', 'title']);
            }])
            ->field(['id', 'title', 'library_uid', 'library_category_uid', 'score', 'sort', 'is_show', 'create_time', 'start_time', 'end_time', 'privilege', 'exam_time', 'exam_submit_type', 'login_style', 'content', 'paper_uid', 'image'])
            ->limit($this->limitOffset, $this->limitLength)
            ->order(['id' => 'desc'])
            ->select()
            ->toArray();
        } catch (\Exception $e) {
            // 记录日志
            \think\facade\Log::error('获取考试管理列表失败: ' . $e->getMessage());
            return [];
        }
    }


    /**
     * @notes 获取考试管理数量
     * @return int
     * @author 精解析答题
     * @date 2025/04/13 21:43
     */
    public function count(): int
    {
        try {
            return TenantExamExamination::where($this->searchWhere)->where([])->count();
        } catch (\Exception $e) {
            // 记录日志
            \think\facade\Log::error('获取考试管理数量失败: ' . $e->getMessage());
            return 0;
        }
    }

}