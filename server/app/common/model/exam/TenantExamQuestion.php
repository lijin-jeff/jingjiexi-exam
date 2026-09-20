<?php
// +----------------------------------------------------------------------
// | 答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 系统开发者版权所有，拥有最终解释权。

namespace app\common\model\exam;


use app\common\model\BaseModel;
use app\common\model\dict\TenantDictData;
use think\model\concern\SoftDelete;


/**
 * 试题管理模型
 * Class TenantExamQuestion
 * @package app\common\model\exam
 */
class TenantExamQuestion extends BaseModel
{
    use SoftDelete;

    protected $name = 'tenant_exam_question';

    protected $deleteTime = 'delete_time';


    // 通过chapter_uid关联试题章节uid和标题，一个题目只有一个章节，通过判断parent_uid是否存在来判断是否存在父级章节
    public function chapter(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(TenantExamChapter::class, 'chapter_uid', 'uid')
            ->where('is_show', 1)
            ->field('uid,title,parent_uid')
            ->with(['parent' => function ($query) {
                $query->where('is_show', 1)->field('uid,title');
            }]);
    }
    
     /**
     * 关联试题知识点id和标题
     * @return array
     */
    public function getKnowledgeAttr(): array
    {
        if (empty($this->knowledge_uid)) {
            return [];
        }
        // 处理逗号分隔的多个标签ID
        $knowledgeIds = array_filter(explode(',', $this->knowledge_uid));
        if (empty($knowledgeIds)) {
            return [];
        }
        return TenantExamKnowledge::query()->where([
            ['is_show', '=', 1],
            ['uid', 'in', $knowledgeIds]
        ])->column('uid,title,content');
    }
    
    /**
     * 获取当前试题的标签数据（兼容原有功能）
     * @return array
     */
    public function getLabelDataAttr(): array
    {
        if (empty($this->label_uid)) {
            return [];
        }
        
        // 处理逗号分隔的多个标签ID
        $labelIds = array_filter(explode(',', $this->label_uid));
        if (empty($labelIds)) {
            return [];
        }
        
        return TenantExamLabel::query()->where([
            ['is_show', '=', 1],
            ['uid', 'in', $labelIds]
        ])->column('uid,title');
    }

    /**
     * 选项属性访问器
     * 将数据库中存储的JSON字符串转换为数组格式
     * @param mixed $name 数据库中存储的JSON字符串
     * @return array 解析后的选项数组
     */
    public function getOptionAttr(mixed $name): array
    {
        return json_decode($name, true);
    }

    /**
     * 分数属性访问器
     * 将数据库中存储的分数值转换为浮点型
     * @param mixed $score 数据库中存储的分数值（可能为字符串或数值类型）
     * @return float 转换后的浮点型分数
     */
    public function getScoreAttr(mixed $score): float
    {
        return floatval($score);
    }
    
    /**
     * 积分属性访问器
     * 将数据库中存储的积分值转换为浮点型
     * @param mixed $integral 数据库中存储的积分值（可能为字符串或数值类型）
     * @return float 转换后的浮点型积分
     */
    public function getIntegralAttr(mixed $integral): float
    {
        return floatval($integral);
    }

    /**
     * 处理试题类型
     * @param $value
     * @param $data
     * @return string
     */
    public function getExamTypeNameAttr($value, $data): string
    {
        $dictData = TenantDictData::query()->where([
            ['tenant_id', '=', request()->tenantId],
            ['type_id', '=', 13],
            ['status', '=', 1]
        ])->whereNull('delete_time')
        ->column(['value', 'name']);
        $dictDataGroup = [];
        foreach ($dictData as $item) {
            $dictDataGroup[$item['value']] = $item['name'];
        }
        return $dictDataGroup[$data['exam_type']] ?? '未知题型';
    }
    
    /**
     * 获取全站作答人次
     * @param $value
     * @param $data
     * @return int
     */
    public function getAnswerCountAttr($value, $data): int
    {
        return (int)($data['total_attempts'] ?? 0);
    }
    
    /**
     * 获取全站正确率
     * @param $value
     * @param $data
     * @return string
     */
    public function getCorrectRateAttr($value, $data): string
    {
        // 优先使用数据库中已计算的 accuracy_rate 字段
        if (isset($data['accuracy_rate'])) {
            return round($data['accuracy_rate'], 2) . '%';
        }
        
        // 兼容处理：同时支持 correct_attempts（新字段）和 total_correct（旧字段）
        $totalAttempts = (int)($data['total_attempts'] ?? 0);
        // 优先使用 correct_attempts 字段（新字段），兼容旧的 total_correct 字段
        $totalCorrect = (int)(isset($data['correct_attempts']) ? $data['correct_attempts'] : ($data['total_correct'] ?? 0));
        
        if ($totalAttempts === 0) {
            return '0%';
        }
        
        $rate = ($totalCorrect / $totalAttempts) * 100;
        return round($rate, 2) . '%';
    }
    
    /**
     * 获取易错项
     * @param $value
     * @param $data
     * @return string
     */
    public function getWrongOptionAttr($value, $data): string
    {
        if (empty($data['easy_mistakes'])) {
            return '-';
        }
        
        // 解析JSON数据
        $easyMistakes = json_decode($data['easy_mistakes'], true);
        
        if (!is_array($easyMistakes) || empty($easyMistakes)) {
            return '-';
        }
        
        // 按错误次数降序排序
        arsort($easyMistakes);
        
        // 返回错误次数最多的选项
        return (string)key($easyMistakes);
    }
}