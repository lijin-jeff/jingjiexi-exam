<?php

namespace app\tenantapi\lists\exam;

use app\tenantapi\lists\BaseAdminDataLists;
use app\common\lists\ListsSearchInterface;
use app\common\model\exam\TenantExamLabel;


/**
 * 题库标签列表
 * Class TenantExamLabelLists
 * @package app\platform\listsexam
 */
class TenantExamLabelLists extends BaseAdminDataLists implements ListsSearchInterface
{


    /**
     * @notes 设置搜索条件
     * @return \string[][]
     * @author 精解析答题
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
     * @notes 获取题库标签列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 精解析答题
     * @date 2025/04/12 15:53
     */
 public function lists(): array
    {
        // 定义递归关联查询函数
        //var_dump($this->params);exit;
        $query = TenantExamLabel::where(function($where) {
            // 复制原始搜索条件
            foreach ($this->searchWhere as $key => $value) {
                // 如果是 library_uid 条件，则特殊处理
                if (is_array($key)) {
                    // 处理复杂条件
                    $where->where($key[0], $key[1], $key[2]);
                } else {
                    if ($key === 'library_uid') {
                        // 同时查询当前 library_uid 和 library_uid=0
                        $where->where(function($subWhere) use ($value) {
                            $subWhere->where('library_uid', $value)
                                     ->whereOr('library_uid', 0);
                        });
                    } else {
                        $where->where($key, $value);
                    }
                }
            }
        })->with(['library' => function($query){
                $query->field(['uid', 'title']);
            }])
            ->field(['id', 'uid', 'title', 'library_uid', 'is_show', 'sort', 'create_time', 'update_time'])
            ->limit($this->limitOffset, $this->limitLength)
            ->order(['sort' => 'desc', 'id' => 'asc']);
           // echo 'SQL: ' . $query->buildSql() . PHP_EOL;
        
        $query = $query->select()->toArray();
        return $query;
    }


    /**
     * @notes 获取题库标签数量
     * @return int
     * @author 精解析答题
     * @date 2025/04/12 15:53
     */
    public function count(): int
    {
        return TenantExamLabel::where(function($where) {
            // 复制原始搜索条件
            foreach ($this->searchWhere as $key => $value) {
                // 如果是 library_uid 条件，则特殊处理
                if (is_array($key)) {
                    // 处理复杂条件
                    $where->where($key[0], $key[1], $key[2]);
                } else {
                    if ($key === 'library_uid') {
                        // 同时查询当前 library_uid 和 library_uid=0
                        $where->where(function($subWhere) use ($value) {
                            $subWhere->where('library_uid', $value)
                                     ->whereOr('library_uid', 0);
                        });
                    } else {
                        $where->where($key, $value);
                    }
                }
            }
        })->withCount(['library'])
            ->count();
    }

}