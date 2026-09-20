<?php
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 精解析答题系统开发者版权所有，拥有最终解释权。
declare (strict_types=1);

namespace app\tenantapi\lists\exam;

use app\tenantapi\lists\BaseAdminDataLists;
use app\common\model\exam\TenantExamPaper;
use app\common\lists\ListsSearchInterface;


/**
 * 试卷管理列表
 * Class TenantExamPaperLists
 * @package app\platform\listsexam
 */
class TenantExamPaperLists extends BaseAdminDataLists implements ListsSearchInterface
{
    /**
     * @notes 获取试卷管理列表总数
     * @return int
     * @author 精解析答题
     * @date 2025/06/10 13:57
     */
    public function count(): int
    {
        try {
            return TenantExamPaper::where($this->searchWhere)
                ->count();
        } catch (\Exception $e) {
            \think\facade\Log::error('获取试卷数量失败: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * @notes 设置搜索条件
     */
    public function setSearch(): array
    {
        return [
            '='      => ['is_show', 'is_rand', 'id'],
            '%like%' => ['title', 'uid'],
        ];
    }

    /**
     * @notes 获取试卷管理列表
     */
    public function lists(): array
    {
        try {
            $lists = TenantExamPaper::where($this->searchWhere)
                ->field([
                    'id', 'uid', 'title', 'is_show', 'sort', 
                    'create_time', 'update_time', 'is_rand', 'image', 
                    'option_count', 'option_score', 'remark'
                ])
                ->limit($this->limitOffset, $this->limitLength)
                ->order(['sort' => 'desc', 'id' => 'desc'])
                ->select()
                ->toArray();
            
            // 格式化数据
            foreach ($lists as &$item) {
                $item['option_count'] = $item['option_count'] ?? 0;
                $item['option_score'] = $item['option_score'] ?? '0.00';
                $item['image'] = $item['image'] ?? '';
            }
            
            return $lists;
        } catch (\Exception $e) {
            \think\facade\Log::error('获取试卷列表失败: ' . $e->getMessage());
            return [];
        }
    }
}