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


use app\common\model\exam\TenantExamRankingDaily;
use app\common\logic\BaseLogic;
use think\facade\Db;


/**
 * tenantRankingDaily逻辑
 * Class TenantExamRankingDailyLogic
 * @package app\tenantapi\logic\exam
 */
class TenantExamRankingDailyLogic extends BaseLogic
{


    /**
     * @notes 添加tenantRankingDaily
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/08/24 10:49
     */
    public static function add(array $params): bool
    {
        Db::startTrans();
        try {
            TenantExamRankingDaily::create([

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
     * @notes 编辑tenantRankingDaily
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/08/24 10:49
     */
    public static function edit(array $params): bool
    {
        Db::startTrans();
        try {
            TenantExamRankingDaily::where('id', $params['id'])->update([
                'correct_count' => $params['correct_count'],
                'total_count' => $params['total_count'],
                'accuracy' => $params['accuracy'],
                'integral' => $params['integral'],
                'ranking' => $params['ranking'],
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
     * @notes 删除tenantRankingDaily
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/08/24 10:49
     */
    public static function delete(array $params): bool
    {
        return TenantExamRankingDaily::destroy($params['id']);
    }


    /**
     * @notes 获取tenantRankingDaily详情
     * @param $params
     * @return array
     * @author 精解析答题
     * @date 2025/08/24 10:49
     */
    public static function detail($params): array
    {
        return TenantExamRankingDaily::findOrEmpty($params['id'])->toArray();
    }
}