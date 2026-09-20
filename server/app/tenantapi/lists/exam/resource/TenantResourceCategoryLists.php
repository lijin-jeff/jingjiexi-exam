<?php
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 访问官网：精解析答题
// | 官方邮箱：精解析答题
// | 精解析答题系统开发者版权所有，拥有最终解释权。
declare (strict_types=1);

namespace app\tenantapi\lists\exam\resource;


use app\tenantapi\lists\BaseAdminDataLists;
use app\common\model\resource\TenantResourceCategory;
use app\common\lists\ListsSearchInterface;


/**
 * 资源分类列表
 * Class TenantResourceCategoryLists
 * @package app\platform\listsresource
 */
class TenantResourceCategoryLists extends BaseAdminDataLists implements ListsSearchInterface
{
    /**
     * @notes 设置搜索条件
     * @return \string[][]
     * @author 精解析答题
     * @date 2025/06/16 23:36
     */
    public function setSearch(): array
    {
        return [
            '='      => ['is_show','exam_category_uid'],
            '%like%' => ['title'],
        ];
    }


    /**
     * @notes 获取资源分类列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 精解析答题
     * @date 2025/06/16 23:36
     */
    public function lists(): array
    {
        try {
            // 1. 获取所有符合条件的分类数据
            $data = TenantResourceCategory::where($this->searchWhere)
                ->with([
                    "children" => function ($query) {
                      $query->append(['category_count']);
                    },
                    "examCategory" => function ($query) {
                        $query->field(['uid', 'title']);
                    }
                ])
                ->where([
                    ['parent_uid', '=', '']
                 ])
                ->field(['id', 'exam_category_uid', 'title', 'is_show', 'sort', 'parent_uid', 'create_time', 'update_time', 'image', 'uid'])
                ->order(['id' => 'desc'])
                ->select()
                ->toArray();

                return $data;
        } catch (\Exception $e) {
            // 记录异常信息，可根据实际需求修改异常处理逻辑
            throw new \RuntimeException('获取资源分类列表失败: ' . $e->getMessage(), 500);
        }
    }


    /**
     * @notes 获取资源分类数量
     * @return int
     * @author 精解析答题
     * @date 2025/06/16 23:36
     */
    public function count(): int
    {
        try {
            return TenantResourceCategory::where($this->searchWhere)
                ->where(function($query) {
                    $query->where('parent_uid', '=', '')
                          ->whereOr('parent_uid', '=', '0');
                })
                ->count();
        } catch (\Exception $e) {
            // 记录异常信息，可根据实际需求修改异常处理逻辑
            throw new \RuntimeException('获取资源分类数量失败: ' . $e->getMessage(), 500);
        }
    }

}