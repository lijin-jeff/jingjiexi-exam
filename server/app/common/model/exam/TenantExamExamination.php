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


/**
 * 考试管理模型
 * Class TenantExamExamination
 * @package app\common\model\exam
 */
class TenantExamExamination extends BaseModel
{
    use SoftDelete;

    protected $name = 'tenant_exam_examination';

    protected $deleteTime = 'delete_time';

    public function paper(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(TenantExamPaper::class, 'paper_uid', 'uid');
    }

    /**
     * 处理考试时间状态
     * @param $value
     * @param $data
     * @return array
     * @author 精解析答题
     */
    public function getStatusAttr($value, $data): array
    {
        $examinationInfo = static::query()->where('uid', '=', $data['uid'])->field(['start_time', 'end_time'])->findOrEmpty();
        if (empty($examinationInfo)) {
            return [
                'status' => 0,
                'text'   => '考试不存在...',
            ];
        }
        $startTime = strtotime($examinationInfo['start_time']);
        $endTime = strtotime($examinationInfo['end_time']);
        if (time() < $startTime) {
            return [
                'status' => 1,
                'text'   => '考试未开始...',
            ];
        } else if (time() > $endTime) {
            return [
                'status' => 2,
                'text'   => '考试已结束...',
            ];
        } else {
            return [
                'status' => 3,
                'text'   => '考试进行中...',
            ];
        }
    }

    // 处理考试提交次数状态
    public function getSubmitCountStatusAttr($value, $data): array
    {
        $examinationInfo = static::query()->where('uid', '=', $data['uid'])->field(['exam_submit_type', 'submit_count_value'])->findOrEmpty();
        if (empty($examinationInfo)) {
            return [
                'status' => false,
                'text'   => '考试不存在',
                'remaining' => 0,
            ];
        }
        $examSubmitType = $examinationInfo['exam_submit_type'];
        $submitCountValue = $examinationInfo['submit_count_value'];
        if ($examSubmitType === 1) {
            return [
                'status' => true,
                'text'   => '不限次数',
                'remaining' => -1,
            ];
        } else if ($examSubmitType === 2) {
            // 查找时间范围今日00:00:00到今日23:59:59的答题次数
            $startOfDay = date('Y-m-d 00:00:00');
            $endOfDay = date('Y-m-d 00:00:00', strtotime('+1 day')); // 获取明天的 00:00:00
            $startOfDay = strtotime($startOfDay);
            $startOfDay = $startOfDay * 1000;
            $endOfDay = strtotime($endOfDay);
            $endOfDay = $endOfDay * 1000;
            
            $remaining = TenantExamExaminationHistory::query()
                ->where('examination_uid', '=', $data['uid'])
                ->where('tenant_id', '=', request()->tenantId)
                ->where('user_uid', '=', request()->userId)
                ->whereTime('create_time', '>=', $startOfDay)
                ->whereTime('create_time', '<', $endOfDay)
                ->count();
            return [
                'status' => true,
                'text'   => $submitCountValue . '次/天',
                'remaining' => $submitCountValue - $remaining,
            ];
        } else if ($examSubmitType === 3) {
            // 查找时间范围本周一00:00:00到本周日23:59:59的答题次数
            // 'monday this week' 会强制获取本周一的时间戳
            $startOfWeek = date('Y-m-d 00:00:00', strtotime('monday this week'));
            $startOfWeek = strtotime($startOfWeek);
            $startOfWeek = $startOfWeek * 1000;
            
            // 'sunday this week' 会强制获取本周日的时间戳
            $endOfWeek = date('Y-m-d 23:59:59', strtotime('sunday this week'));
            $endOfWeek = strtotime($endOfWeek);
            $endOfWeek = $endOfWeek * 1000; 

            $remaining = TenantExamExaminationHistory::query()
                ->where('examination_uid', '=', $data['uid'])
                ->where('tenant_id', '=', request()->tenantId)
                ->where('user_uid', '=', request()->userId)
                ->whereBetween('create_time', [$startOfWeek, $endOfWeek])
                ->count();

            return [
                'status' => true,
                'text'   => $submitCountValue . '次/周',
                'remaining' => $submitCountValue - $remaining,   
            ];
        } else if ($examSubmitType === 4) {
            // 查找时间范围本月1日00:00:00到本月最后一天23:59:59的答题次数
            //转换为13位时间戳
            $startOfMonth = date('Y-m-01 00:00:00');
            $startOfMonth = strtotime($startOfMonth);
            $startOfMonth = $startOfMonth * 1000;

            // 'last day of this month' 是一个非常有用的 strtotime 相对时间格式
            $endOfMonthDate = date('Y-m-t', strtotime('last day of this month'));
            $endOfMonth = $endOfMonthDate . ' 23:59:59';
            $endOfMonth = strtotime($endOfMonth);
            $endOfMonth = $endOfMonth * 1000;

            $remaining = TenantExamExaminationHistory::query()
                ->where('examination_uid', '=', $data['uid'])
                ->where('tenant_id', '=', request()->tenantId)
                ->where('user_uid', '=', request()->userId)
                ->whereBetween('create_time', [$startOfMonth, $endOfMonth])
                ->count();
            return [
                'status' => true,
                'text'   => $submitCountValue . '次/月',
                'remaining' => $submitCountValue - $remaining,      
            ];
        }
        // 兜底返回，防止未匹配到任何条件
        return [
            'status' => false,
            'text'   => '未知提交类型',
            'remaining' => 0,
        ];
    }

}