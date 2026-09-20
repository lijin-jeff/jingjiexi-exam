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


use app\common\model\exam\TenantExamQuestionCorrections;
use app\common\logic\BaseLogic;
use think\facade\Db;


/**
 * corrections逻辑
 * Class TenantExamQuestionCorrectionsLogic
 * @package app\tenantapi\logic\exam
 */
class TenantExamQuestionCorrectionsLogic extends BaseLogic
{


    /**
     * @notes 添加corrections
     * @param array $params
     * @return bool
     * @author 精解析题库
     * @date 2025/08/05 23:16
     */
    public static function add(array $params): bool
    {
        Db::startTrans();
        try {
            TenantExamQuestionCorrections::create([

            ]);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }


    /**
     * @notes 编辑corrections
     * @param array $params
     * @return bool
     * @author 精解析题库
     * @date 2025/08/05 23:16
     */
    public static function edit(array $params): bool
    {
        Db::startTrans();
        try {
            TenantExamQuestionCorrections::where('id', $params['id'])->update([
                'platform_feedback' => $params['platform_feedback'],
                'feedback_time' => date('Y-m-d H:i:s')
            ]);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }


    /**
     * @notes 删除corrections
     * @param array $params
     * @return bool
     * @author 精解析题库
     * @date 2025/08/05 23:16
     */
    public static function delete(array $params): bool
    {
        return TenantExamQuestionCorrections::destroy($params['id']);
    }


    /**
     * @notes 获取corrections详情
     * @param $params
     * @return array
     * @author 精解析题库
     * @date 2025/08/05 23:16
     */
    public static function detail($params): array
    {
        return TenantExamQuestionCorrections::findOrEmpty($params['id'])->toArray();
    }
}