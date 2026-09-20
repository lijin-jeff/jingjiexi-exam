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
use app\common\model\exam\TenantExamCategory;


/** 
 * 题库分类列表
 * Class ExamCategoryLists
 * @package app\platform\listsexam
 */
class ExamCategoryLists extends BaseAdminDataLists implements ListsSearchInterface
{
    /**
     * @notes 设置搜索条件
     * @return \string[][]
     * @author 精解析答题
     * @date 2025/04/01 01:51
     */
    public function setSearch(): array
    {
        return [
            '='      => ['is_show', 'is_recommend'],
            '%like%' => ['title'],
        ];
    }


    /**
     * @notes 获取题库分类列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 精解析答题
     * @date 2025/04/01 01:51
     */
    public function lists(): array
    {
        try {
           $data = TenantExamCategory::where($this->searchWhere)
                ->with(["children" => function ($query) {
                   $query->append(['category_count']);
                }])
                ->where([
                    ['parent_uid', '=', '']
                 ])
                ->field(['id', 'uid', 'title', 'exam_time', 'is_show', 'sort', 'cover', 'is_recommend', 'icon', "parent_uid"])
                ->limit($this->limitOffset, $this->limitLength)
                ->order(['id' => 'desc'])
                ->append(['category_count'])
                ->select()
                ->toArray();
                // 考试时间的时间戳转换为格式化时间，递归处理所有层级
                function formatExamTime(&$items) {
                    foreach ($items as &$item) {
                        $item['exam_time'] = $item['exam_time'] ? date('Y-m-d', $item['exam_time']) : '';
                        // 递归处理子分类
                        if (isset($item['children']) && is_array($item['children'])) {
                            formatExamTime($item['children']);
                        }
                    }
                }
                
                formatExamTime($data);
                return $data;
        } catch (\Exception $e) {
            \think\facade\Log::error('分类查询异常：' . $e->getMessage() . ' 查询条件：' . json_encode($this->searchWhere));
            throw new \think\Exception('数据加载失败，请稍后重试');
        }
    }

    /**
     * @notes 获取题库分类数量
     * @return int
     * @author 精解析答题
     * @date 2025/04/01 01:51
     */
    public function count(): int
    {
        try {
           // print_r($this->searchWhere);
            return TenantExamCategory::where($this->searchWhere)->where([
                ["parent_uid", "=", ""]
            ])->with(["children" => function ($query) {
                $query->count();
            }])->count();
        } catch (\Exception $e) {
            \think\facade\Log::error('分类数量查询异常：' . $e->getMessage() . ' 查询条件：' . json_encode($this->searchWhere));
            throw new \think\Exception('数据加载失败，请稍后重试');
        }
    }

}