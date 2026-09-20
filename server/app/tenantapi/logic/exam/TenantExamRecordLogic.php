<?php
// +----------------------------------------------------------------------
// | likeadmin快速开发前后端分离管理后台（PHP版）
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 开源版本可自由商用，可去除界面版权logo
// | gitee下载：https://gitee.com/likeshop_gitee/likeadmin
// | github下载：https://github.com/likeshop-github/likeadmin
// | 访问官网：https://www.likeadmin.cn
// | likeadmin团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeadminTeam
// +----------------------------------------------------------------------

namespace app\tenantapi\logic\exam;


use app\common\model\exam\TenantExamRecord;
use app\common\logic\BaseLogic;
use think\facade\Db;

/**
 * 做题记录表逻辑
 * Class TenantExamRecordLogic
 * @package app\tenantapi\logic\exam
 */
class TenantExamRecordLogic extends BaseLogic
{


    /**
     * @notes 添加做题记录表
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/07/19 14:47
     */
    public static function add(array $params): bool
    {
        // 基础验证
        if (empty($params['uid']) || empty($params['questions_type'])) {
            self::setError('用户ID和题目类型为必填项');
            return false;
        }
        try {
            TenantExamRecord::create([
                'uid' => $params['uid'],
                'questions_type' => $params['questions_type'],
                'user_score' => $params['user_score'],
                'paper_score' => $params['paper_score'],
                'tenant_id' => $params['tenant_id'],
                'exam_time' => $params['exam_time'],
                'exam_submit_time' => $params['exam_submit_time'],
                'correct_count' => $params['correct_count'],
                'error_count' => $params['error_count'],
                'question_uid' => $params['question_uid'],
                'user_uid' => $params['user_uid'],
            ]);
            return true;
        } catch (\Exception $e) {
            self::setError('添加失败：系统异常');
            // 建议记录详细错误日志
            // Log::error('Exam record add failed: ' . $e->getMessage());
            return false;
        }
    }


    /**
     * @notes 编辑做题记录表
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/07/19 14:47
     */
    public static function edit(array $params): bool
    {
        Db::startTrans();
        try {
            TenantExamRecord::where('id', $params['id'])->update([
                'uid' => $params['uid'],
                'questions_type' => $params['questions_type'],
                'user_score' => $params['user_score'],
                'paper_score' => $params['paper_score'],
                'tenant_id' => $params['tenant_id'],
                'exam_time' => $params['exam_time'],
                'exam_submit_time' => $params['exam_submit_time'],
                'correct_count' => $params['correct_count'],
                'error_count' => $params['error_count'],
                'question_uid' => $params['question_uid'],
                'user_uid' => $params['user_uid'],
            ]);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            // 生产环境使用通用错误提示
            self::setError('操作失败，请稍后重试');
            // 记录详细错误日志（开发环境可输出完整信息）
            \think\facade\Log::error(__METHOD__ . ' error: ' . $e->getMessage() . ' ' . $e->getTraceAsString());
            return false;
        }
    }


    /**
     * @notes 删除做题记录表
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/07/19 14:47
     */
    public static function delete(array $params): bool
    {
        return TenantExamRecord::destroy($params['id']);
    }


    /**
     * @notes 获取做题记录表详情
     * @param $params
     * @return array
     * @author 精解析答题
     * @date 2025/07/19 14:47
     */
    public static function detail($params): array
    {
        return TenantExamRecord::findOrEmpty($params['id'])->toArray();
    }
}