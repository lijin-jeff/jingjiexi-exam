<?php
// +----------------------------------------------------------------------
// | 精解析答题开发团队介绍
// +----------------------------------------------------------------------
// | 欢迎你使用本套系统，本套系统由精解析答题开发团队全力开发。
// | 如果本套系统是商业系统，请严格遵守系统使用相关协议，出现违背协议的法律行为，所有违法行为均与精解析答题无关。
// | 精解析答题团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | 作者: 精解析答题开发团队
// +----------------------------------------------------------------------
namespace app\common\model\exam;

use app\common\model\BaseModel;
use app\common\model\exam\TenantExamQuestionErrorLog;
use think\model\concern\SoftDelete;


class TenantExamQuestionError extends BaseModel
{
    use  SoftDelete;

    protected $name = 'tenant_exam_question_error';

    protected $deleteTime = 'delete_time';

    // 定义关联模型
    public function examLibrary()
    {
        return $this->hasOne(TenantExamLibrary::class, 'uid', 'library_uid');
    }

    //查询已消灭错题的数量
    public function getEliminatedErrorCountAttr($value, $data): int
    {
        try {
            return TenantExamQuestionErrorLog::query()->where([
                ['question_error_uid', '=', $data['id']],
                ['new_status', '=', 1] // 只统计已消灭状态的记录
            ])->count();
        } catch (\Exception $e) {
            \think\facade\Log::error('获取已消灭错题数量失败: ' . $e->getMessage());
            return 0;
        }
    }
    
    // 定义与消灭日志的关联关系
    public function errorEliminateLogs()
    {
        return $this->hasMany(TenantExamQuestionErrorLog::class, 'question_error_uid', 'id');
    }
}