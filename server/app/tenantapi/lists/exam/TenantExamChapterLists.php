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
use app\common\lists\ListsExcelInterface;
use app\common\model\exam\TenantExamChapter;


/**
 * 题库章节列表
 * Class TenantExamChapterLists
 * @package app\tenantapi\listsexam
 */
class TenantExamChapterLists extends BaseAdminDataLists implements ListsSearchInterface, ListsExcelInterface
{


    /**
     * @notes 设置搜索条件
     * @return \string[][]\n     * @author 精解析答题
     * @date 2025/04/12 15:53
     */
    public function setSearch(): array
    {
        return [
            '='      => ['is_show', 'library_uid'],
            '%like%' => ['title'],
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
            'title' => '章节名称',
            'level_text' => '章节层级',
            'is_show_text' => '显示状态',
            'sort' => '显示权重',
            'parent_title' => '父级章节',
            'create_time_text' => '创建时间',
            'update_time_text' => '更新时间',
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
        return '章节列表';
    }


    /**
     * @notes 获取题库章节列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 精解析答题
     * @date 2025/04/12 15:53
     */
    public function lists(): array
    {
        if ($this->export) {
            return $this->getAllChaptersForExport();
        }

        $withRecursive = function ($query) use (&$withRecursive) {
            $query->with(['children' => $withRecursive]);
        };

        try {
            return TenantExamChapter::where($this->searchWhere)
                ->field(['id', 'uid', 'title', 'is_show', 'sort', 'parent_uid', 'create_time', 'update_time'])
                ->where([
                    ["parent_uid", "=", 0]
                ])
                ->with(["children" => $withRecursive])
                ->limit($this->limitOffset, $this->limitLength)
                ->order(['sort' => 'desc', 'id' => 'asc'])
                ->select()
                ->toArray();
        } catch (\Exception $e) {
            \think\facade\Log::error('获取题库章节列表失败: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * @notes 获取所有章节用于导出（扁平化处理，按树形结构顺序）
     * @return array
     */
    protected function getAllChaptersForExport(): array
    {
        try {
            $withRecursive = function ($query) use (&$withRecursive) {
                $query->with(['children' => $withRecursive]);
            };

            // 获取树形结构数据
            $treeData = TenantExamChapter::where($this->searchWhere)
                ->field(['id', 'uid', 'title', 'is_show', 'sort', 'parent_uid', 'create_time', 'update_time'])
                ->where([["parent_uid", "=", 0]])
                ->with(["children" => $withRecursive])
                ->order(['sort' => 'desc', 'id' => 'asc'])
                ->select()
                ->toArray();

            // 扁平化树形结构，保持父子顺序
            $result = [];
            $levelMap = [0 => '一级', '二级', '三级', '四级', '五级'];

            // 递归遍历树形结构
            $flattenTree = function ($nodes, $level = 0) use (&$flattenTree, &$result, $levelMap) {
                foreach ($nodes as $node) {
                    $parentTitle = '';
                    if (!empty($node['parent_uid'])) {
                        // 查找父级标题
                        $parent = TenantExamChapter::where('uid', $node['parent_uid'])->field('title')->find();
                        if ($parent) {
                            $parentTitle = $parent['title'];
                        }
                    }

                    $result[] = [
                        'title' => $node['title'],
                        'level_text' => $levelMap[$level] ?? ($level + 1) . '级',
                        'is_show_text' => $node['is_show'] == 1 ? '显示' : '隐藏',
                        'sort' => $node['sort'],
                        'parent_title' => $parentTitle ?: '-',
                        'create_time_text' => is_numeric($node['create_time']) ? date('Y-m-d H:i:s', $node['create_time']) : $node['create_time'],
                        'update_time_text' => is_numeric($node['update_time']) ? date('Y-m-d H:i:s', $node['update_time']) : $node['update_time'],
                    ];

                    // 递归处理子节点
                    if (!empty($node['children'])) {
                        $flattenTree($node['children'], $level + 1);
                    }
                }
            };

            $flattenTree($treeData, 0);

            $offset = $this->limitOffset;
            $length = $this->limitLength;
            return array_slice($result, $offset, $length);
        } catch (\Exception $e) {
            \think\facade\Log::error('获取导出章节列表失败: ' . $e->getMessage());
            return [];
        }
    }


    /**
     * @notes 获取题库章节数量
     * @return int
     * @author 精解析答题
     * @date 2025/04/12 15:53
     */
    public function count(): int
    {
        try {
            if ($this->export) {
                return TenantExamChapter::where($this->searchWhere)->count();
            }
            return TenantExamChapter::where($this->searchWhere)
                ->where([
                    ["parent_uid", "=", 0]
                ])
                ->count();
        } catch (\Exception $e) {
            \think\facade\Log::error('获取题库章节数量失败: ' . $e->getMessage());
            return 0;
        }
    }

}