<?php
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 精解析答题系统开发者版权所有，拥有最终解释权。

namespace app\tenantapi\lists\exam;

use app\tenantapi\lists\BaseAdminDataLists;
use app\common\model\exam\TenantExamLibrary;
use app\common\lists\ListsSearchInterface;
use app\common\lists\ListsExcelInterface;
use think\facade\Log;

/**
 * 题库管理列表 (根据分类层级查询)
 * Class TenantExamLibraryLists
 * @package app\tenantapi\listsexam
 */
class TenantExamLibraryLists extends BaseAdminDataLists implements ListsSearchInterface, ListsExcelInterface
{
    /**
     * @notes 设置搜索条件
     * @return array
     * @author 精解析答题
     * @date 2025/04/01 04:41
     */
    public function setSearch(): array
    {
        return [
            '='      => ['is_show', 'category_uid', 'free_state', 'year', 'recommend_state', 'hot_state'],
            '%like%' => ['title', 'author'],
        ];
    }
    
    /**
     * @notes 设置导出字段
     * @return string[]
     * @author 精解析答题
     * @date 2026/01/18
     */
    public function setExcelFields(): array
    {
        return [
            'title' => '题库名称',
            'author' => '作者',
            'is_show' => '显示状态',
            'free_state' => '收费状态',
            'money' => '价格',
            'discount' => '折扣',
            'year' => '年份',
            'recommend_state' => '推荐状态',
            'hot_state' => '热门状态',
            'create_time' => '创建时间',
        ];
    }
    
    /**
     * @notes 设置导出文件名
     * @return string
     * @author 精解析答题
     * @date 2026/01/18
     */
    public function setFileName(): string
    {
        return '题库列表';
    }

    /**
     * @notes 获取题库列表
     * @return array
     * @author 精解析答题
     * @date 2025/04/01 04:41
     */
    public function lists(): array
    {
        try {
            $query = TenantExamLibrary::getBaseQuery($this->searchWhere, $this->request);

            return $query->with(["category" => function ($query) {
                    $query->field('uid,title');
                }])
                ->append(['exam_count'])
                ->field(['id', 'uid', 'title', 'is_show', 'sort', 'create_time', 'image', 'remark', 'category_uid', 'author', 'free_state', 'money', 'discount', 'year', 'recommend_state', 'hot_state'])
                ->limit($this->limitOffset, $this->limitLength)
                ->order(['sort' => 'desc', 'id' => 'asc'])
                ->select()
                ->toArray();
        } catch (\Exception $e) {
            Log::error('获取题库列表失败: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * @notes 获取题库管理数量
     * @return int
     * @author 精解析答题
     * @date 2025/04/01 04:41
     */
    public function count(): int
    {
        try {
            return TenantExamLibrary::getBaseQuery($this->searchWhere, $this->request)->count();
        } catch (\Exception $e) {
            Log::error('获取题库数量失败: ' . $e->getMessage());
            return 0;
        }
    }
}