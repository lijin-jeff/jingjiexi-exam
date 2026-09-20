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


use app\common\model\exam\TenantExamComment;
use app\common\logic\BaseLogic;
use think\facade\Db;


/**
 * comment逻辑
 * Class TenantExamCommentLogic
 * @package app\tenantapi\logic\exam
 */
class TenantExamCommentLogic extends BaseLogic
{


    /**
     * @notes 添加comment
     * @param array $params
     * @return bool
     * @author likeadmin
     * @date 2025/07/19 23:21
     */
    public static function add(array $params): bool
    {
        Db::startTrans();
        try {
            TenantExamComment::create([
                'user_id' => $params['user_id'],
                'qid' => $params['qid'],
                'pid' => $params['pid'],
                'content' => $params['content'],
                'ip' => $params['ip'],
                'subscribe' => $params['subscribe'],
                'status' => $params['status'],
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
     * @notes 编辑comment
     * @param array $params
     * @return bool
     * @author likeadmin
     * @date 2025/07/19 23:21
     */
    public static function edit(array $params): bool
    {
        Db::startTrans();
        try {
            TenantExamComment::where('id', $params['id'])->update([
                'user_id' => $params['user_id'],
                'qid' => $params['qid'],
                'pid' => $params['pid'],
                'content' => $params['content'],
                'comments' => $params['comments'],
                'likes' => $params['likes'],
                'ip' => $params['ip'],
                'subscribe' => $params['subscribe'],
                'create_time' => $params['create_time'],
                'update_time' => $params['update_time'],
                'delete_time' => $params['delete_time'],
                'status' => $params['status'],
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
     * @notes 删除comment
     * @param array $params
     * @return bool
     * @author likeadmin
     * @date 2025/07/19 23:21
     */
    public static function delete(array $params): bool
    {
        return TenantExamComment::destroy($params['id']);
    }


    /**
     * @notes 获取comment详情
     * @param $params
     * @return array
     * @author likeadmin
     * @date 2025/07/19 23:21
     */
    public static function detail($params): array
    {
        return TenantExamComment::findOrEmpty($params['id'])->toArray();
    }
}