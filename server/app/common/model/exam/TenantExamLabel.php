<?php
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 访问官网：
// | 官方邮箱：
// | 精解析答题系统开发者版权所有，拥有最终解释权。

namespace app\common\model\exam;


use app\common\model\BaseModel;
use think\model\concern\SoftDelete;


/**
 * 题库标签模型
 * Class TenantExamLabel
 * @package app\common\model\exam
 */
class TenantExamLabel extends BaseModel
{
    use SoftDelete;

    protected $name = 'tenant_exam_label';

    protected $deleteTime = 'delete_time';

    /**
     * 获取题库内容
     * @return \think\model\relation\BelongsTo
     */
    public function library(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(TenantExamLibrary::class, 'library_uid', 'uid');
    }

    /**
     * 查询标签下的试题总数
     * @param $value
     * @param $data
     * @return int
     * @throws \think\db\exception\DbException
     * @author 精解析答题 <>
     * @link 
     * @email 
     * @date 2025/5/10 03:27
     */
    public function getExamCountAttr($value, $data): int
    {
        return TenantExamQuestion::query()->where([
            ['label_uid', '=', $data['uid']],
            ['is_show', '=', 1]
        ])->count();
    }
}