<?php
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。

// | 精解析答题系统开发者版权所有，拥有最终解释权。

namespace app\common\model\exam;


use app\common\model\BaseModel;
use think\model\concern\SoftDelete;
use think\model\relation\HasMany;


/**
 * 题库章节知识点模型
 * Class TenantExamKnowledge
 * @package app\common\model\exam
 */
class TenantExamKnowledge extends BaseModel
{
    use SoftDelete;

    protected $name = 'tenant_exam_knowledge';

    protected $deleteTime = 'delete_time';

    /**
     * 获取章节内容
     * @return \think\model\relation\BelongsTo
     */
    public function chapter(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(TenantExamChapter::class, 'chapter_uid', 'uid');
    }

    /**
     * 获取子类
     * @return HasMany
     * @author 精解析答题
     * @date 2024/4/21 00:51
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, "parent_uid", "uid");
    }

    /**
     * 子级章节知识点
     * @param $value
     * @param $data
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 精解析答题 
     * @date 2025/5/10 03:24
     */
    public function getChildrenKnowledgeAttr($value, $data): array
    {
        return self::query()->where([
            ['parent_uid', '=', $data['uid']],
            ['is_show', '=', 1]
        ])->append(['exam_count'])->field(['uid', 'title', 'parent_uid'])->select()->toArray();
    }

    /**
     * 查询章节知识点下的试题总数
     * @param $value
     * @param $data
     * @return int
     * @throws \think\db\exception\DbException
     * @date 2025/5/10 03:27
     */
    public function getExamCountAttr($value, $data): int
    {
        return TenantExamQuestion::query()->where([
            ['knowledge_uid', '=', $data['uid']],
            ['is_show', '=', 1]
        ])->count();
    }
}