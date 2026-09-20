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

namespace app\tenantapi\lists\exam;


use app\tenantapi\lists\BaseAdminDataLists;
use app\common\model\exam\TenantExamComment;
use app\common\lists\ListsSearchInterface;


/**
 * comment列表
 * Class TenantExamCommentLists
 * @package app\tenantapi\listsexam
 */
class TenantExamCommentLists extends BaseAdminDataLists implements ListsSearchInterface
{


    /**
     * @notes 设置搜索条件
     * @return \string[][]
     * @author likeadmin
     * @date 2025/07/19 23:21
     */
    public function setSearch(): array
    {
        return [
            '=' => ['user_id', 'qid', 'pid', 'ip', 'status'],
            'like' => ['content'],
        ];
    }


    /**
     * @notes 获取comment列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author likeadmin
     * @date 2025/07/19 23:21
     */
    public function lists(): array
    {
        $params = $this->params;
        $params['type'] = $params['params']['type'] ?? 1;
        $lists = TenantExamComment::where($this->searchWhere)
            ->where('type', $params['type'])
            ->field(['id', 'type', 'user_id', 'qid', 'pid', 'content', 'comments', 'likes', 'ip', 'create_time', 'status'])
            ->order(['id' => 'desc'])
            ->select()
            ->toArray();

        return linear_to_tree($lists, 'children', 'id', 'pid');
    }


    /**
     * @notes 获取comment数量
     * @return int
     * @author likeadmin
     * @date 2025/07/19 23:21
     */
    public function count(): int
    {
        return TenantExamComment::where($this->searchWhere)->count();
    }

}