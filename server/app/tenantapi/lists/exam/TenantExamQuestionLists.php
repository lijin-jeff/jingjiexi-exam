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
use app\common\model\exam\TenantExamQuestion;
use think\facade\Log;
use app\common\model\exam\TenantExamLabel;
use app\common\model\exam\TenantExamChapter;

/**
 * 试题管理列表
 * Class TenantExamQuestionLists
 * @package app\platform\listsexam
 */
class TenantExamQuestionLists extends BaseAdminDataLists implements ListsSearchInterface
{


    /**
     * @notes 设置搜索条件
     * @return \string[][]
     * @author 精解析答题
     * @date 2025/04/10 19:22
     */
    public function setSearch(): array
    {
        return [
            '='      => ['exam_type', 'is_show', 'exam_level', 'library_uid', 'chapter_uid', 'uid'],
            '%like%' => ['title'],
            'in' => ['label_uid', 'knowledge_uid'],
        ];
    }


    /**
     * @notes 获取试题管理列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 精解析答题
     * @date 2025/04/10 19:22
     */
    public function lists(): array
    {
        //print_r($this);
        // 1. 处理章节查询，包含下级章节
        // 将searchWhere转换为索引数组格式，确保where()方法能正确处理
        $searchWhere = [];
        foreach ($this->searchWhere as $key => $value) {
            if (is_array($value)) {
                // 已经是索引数组格式，直接添加
                $searchWhere[] = $value;
            } else {
                // 关联数组格式，转换为索引数组格式
                $searchWhere[] = [$key, '=', $value];
            }
        }
        
        // 临时存储chapter_uid的值，用于后续处理
        $chapterUid = null;
        $hasChapterFilter = false;
        
        // 查找章节筛选条件
        foreach ($searchWhere as $key => $condition) {
            if (is_array($condition) && count($condition) === 3 && $condition[0] === 'chapter_uid' && $condition[1] === '=') {
                $chapterUid = $condition[2];
                $hasChapterFilter = true;
                // 移除原有的章节筛选条件
                unset($searchWhere[$key]);
                break;
            }
        }
        
        // 如果有章节筛选，递归获取所有子章节ID
        if ($hasChapterFilter && !empty($chapterUid)) {
            // 递归获取所有子章节ID
            $getAllChapterUids = function ($parentUid) use (&$getAllChapterUids) {
                $uids = [$parentUid];
                $children = TenantExamChapter::query()->where([
                    ['parent_uid', '=', $parentUid],
                    ['is_show', '=', 1]
                ])->column('uid');
                foreach ($children as $childUid) {
                    $uids = array_merge($uids, $getAllChapterUids($childUid));
                }
                return $uids;
            };
            
            $chapterUids = $getAllChapterUids($chapterUid);
            
            // 添加包含所有子章节ID的IN条件
            $searchWhere[] = ['chapter_uid', 'IN', $chapterUids];
        }
        
        // 2. 执行查询
        try {
            $items = TenantExamQuestion::where($searchWhere)
                ->field(['id', 'uid', 'title', 'option', 'integral', 'score', 'answer', 'exam_type', 'sort', 'is_show', 
                         'create_time', 'exam_level', 'chapter_uid', 'knowledge_uid', 'label_uid'])
                ->append(["knowledge"])
                ->limit($this->limitOffset, $this->limitLength)
                ->order(['id' => 'desc']);
            
            $items = $items->select()->toArray();
        } catch (\Exception $e) {
            Log::error('获取试题列表失败: ' . $e->getMessage() . '，章节ID：' . $chapterUid . '，查询条件：' . json_encode($searchWhere));
            return [];
        }
        //print_r($items);
        // 2. 优化循环处理，避免引用变量风险
        foreach ($items as $key => $item) {
            // 3. 添加空值检查和默认值
            $title = $item["title"] ?? '';
            $items[$key]["title"] = strip_tags(html_entity_decode($title));
            
            // 4. 处理answer字段可能为非数组的情况
            $answer = $item["answer"] ?? [];
            $items[$key]["answer"] = strip_tags(html_entity_decode(is_array($answer) ? implode(",", $answer) : (string)$answer));
            //print_r($item["label_uid"]);
            if($item["label_uid"]){
                $labelUids = $item["label_uid"] ? explode(',', $item["label_uid"]) : []; // 转换为数组
                $labelList = TenantExamLabel::query()->where([
                    ['uid', 'in', $labelUids],
                ])->select();

                 $items[$key]["labels"] = $labelList;
            }

        }
        return $items;
    }


    /**
     * @notes 获取试题管理数量
     * @return int
     * @author 精解析答题
     * @date 2025/04/10 19:22
     */
    public function count(): int
    {
        // 处理章节查询，包含下级章节
        // 将searchWhere转换为索引数组格式，确保where()方法能正确处理
        $searchWhere = [];
        foreach ($this->searchWhere as $key => $value) {
            if (is_array($value)) {
                // 已经是索引数组格式，直接添加
                $searchWhere[] = $value;
            } else {
                // 关联数组格式，转换为索引数组格式
                $searchWhere[] = [$key, '=', $value];
            }
        }
        
        // 临时存储chapter_uid的值，用于后续处理
        $chapterUid = null;
        $hasChapterFilter = false;
        
        // 查找章节筛选条件
        foreach ($searchWhere as $key => $condition) {
            if (is_array($condition) && count($condition) === 3 && $condition[0] === 'chapter_uid' && $condition[1] === '=') {
                $chapterUid = $condition[2];
                $hasChapterFilter = true;
                // 移除原有的章节筛选条件
                unset($searchWhere[$key]);
                break;
            }
        }
        
        // 如果有章节筛选，递归获取所有子章节ID
        if ($hasChapterFilter && !empty($chapterUid)) {
            // 递归获取所有子章节ID
            $getAllChapterUids = function ($parentUid) use (&$getAllChapterUids) {
                $uids = [$parentUid];
                $children = TenantExamChapter::query()->where([
                    ['parent_uid', '=', $parentUid],
                    ['is_show', '=', 1]
                ])->column('uid');
                foreach ($children as $childUid) {
                    $uids = array_merge($uids, $getAllChapterUids($childUid));
                }
                return $uids;
            };
            
            $chapterUids = $getAllChapterUids($chapterUid);
            
            // 添加包含所有子章节ID的IN条件
            $searchWhere[] = ['chapter_uid', 'IN', $chapterUids];
        }
        
        return TenantExamQuestion::where($searchWhere)->count();
    }

}