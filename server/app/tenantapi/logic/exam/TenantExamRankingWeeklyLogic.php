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


use app\common\model\exam\TenantExamRankingWeekly;
use app\common\logic\BaseLogic;
use think\facade\Db;


/**
 * tenantRankingWeekly逻辑
 * Class TenantExamRankingWeeklyLogic
 * @package app\tenantapi\logic\exam    
 */
class TenantExamRankingWeeklyLogic extends BaseLogic
{


    /**
     * @notes 添加tenantRankingWeekly
     * @param array $params
     * @return bool
     * @author likeadmin
     * @date 2025/08/24 10:48
     */
    public static function add(array $params): bool
    {
        Db::startTrans();
        try {
            TenantExamRankingWeekly::create([

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
     * @notes 编辑tenantRankingWeekly
     * @param array $params
     * @return bool
     * @author likeadmin
     * @date 2025/08/24 10:48
     */
    public static function edit(array $params): bool
    {
        Db::startTrans();
        try {
            TenantExamRankingWeekly::where('id', $params['id'])->update([
                'correct_count' => $params['correct_count'],
                'ranking' => $params['ranking'],
                'week_start_date' => $params['week_start_date'],
                'week_end_date' => $params['week_end_date'],
                'week_number' => $params['week_number'],
                'year' => $params['year'],
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
     * @notes 删除tenantRankingWeekly
     * @param array $params
     * @return bool
     * @author likeadmin
     * @date 2025/08/24 10:48
     */
    public static function delete(array $params): bool
    {
        return TenantExamRankingWeekly::destroy($params['id']);
    }


    /**
     * @notes 获取tenantRankingWeekly详情
     * @param $params
     * @return array
     * @author likeadmin
     * @date 2025/08/24 10:48
     */
    public static function detail($params): array
    {
        return TenantExamRankingWeekly::findOrEmpty($params['id'])->toArray();
    }
}