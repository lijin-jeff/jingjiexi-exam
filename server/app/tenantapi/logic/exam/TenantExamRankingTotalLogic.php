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


use app\common\model\exam\TenantExamRankingTotal;
use app\common\logic\BaseLogic;
use think\facade\Db;


/**
 * tenantRankingTotal逻辑
 * Class TenantExamRankingTotalLogic
 * @package app\tenantapi\logic\exam
 */
class TenantExamRankingTotalLogic extends BaseLogic
{


    /**
     * @notes 添加tenantRankingTotal
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/08/24 10:49
     */
    public static function add(array $params): bool
    {
        Db::startTrans();
        try {
            TenantExamRankingTotal::create([

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
     * @notes 编辑tenantRankingTotal
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/08/24 10:49
     */
    public static function edit(array $params): bool
    {
        Db::startTrans();
        try {
            TenantExamRankingTotal::where('id', $params['id'])->update([
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
     * @notes 删除tenantRankingTotal
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/08/24 10:49
     */
    public static function delete(array $params): bool
    {
        return TenantExamRankingTotal::destroy($params['id']);
    }


    /**
     * @notes 获取tenantRankingTotal详情
     * @param $params
     * @return array
     * @author 精解析答题
     * @date 2025/08/24 10:49
     */
    public static function detail($params): array
    {
        return TenantExamRankingTotal::findOrEmpty($params['id'])->toArray();
    }
}