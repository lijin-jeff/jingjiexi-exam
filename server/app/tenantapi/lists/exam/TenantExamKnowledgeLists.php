<?php
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 访问官网：
// | 官方邮箱：
// | 精解析答题系统开发者版权所有，拥有最终解释权。

namespace app\tenantapi\lists\exam;

use app\tenantapi\lists\BaseAdminDataLists;
use app\common\lists\ListsSearchInterface;
use app\common\model\exam\TenantExamKnowledge;


/**
 * 题库章节列表
 * Class TenantExamKnowledgeLists
 * @package app\tenantapi\lists\exam
 */
class TenantExamKnowledgeLists extends BaseAdminDataLists implements ListsSearchInterface
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
            '='      => ['is_show', 'chapter_uid','library_uid'],
            '%like%' => ['title'],
        ];
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
        try{
            // 获取当前章节下的所有知识点（包括子级）
            $chapterUid = $this->request->param('chapter_uid', '');
            
            $query = TenantExamKnowledge::where($this->searchWhere)
                ->field(['id', 'uid', 'title','content', 'is_show', 'sort', 'parent_uid', 'create_time', 'update_time','chapter_uid','library_uid'])
                ->with(["chapter"]);
            
            // 如果指定了章节，获取该章节下的所有知识点
            if (!empty($chapterUid)) {
                $query->where(['chapter_uid' => $chapterUid]);
            }
            
            return $query->limit($this->limitOffset, $this->limitLength)
                ->order(['sort' => 'desc', 'id' => 'asc'])
                ->select()
                ->toArray();
        }catch(\Exception $e){
            \think\facade\Log::error('TenantExamKnowledgeLists error: ' . $e->getMessage());
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
        $chapterUid = $this->request->param('chapter_uid', '');
        
        $query = TenantExamKnowledge::where($this->searchWhere);
        
        // 如果指定了章节，统计该章节下的所有知识点
        if (!empty($chapterUid)) {
            $query->where(['chapter_uid' => $chapterUid]);
        }
        
        return $query->count();
    }

}