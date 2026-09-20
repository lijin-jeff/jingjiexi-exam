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
use app\common\model\exam\TenantExamQuestionCorrections;
use app\common\lists\ListsSearchInterface;


/**
 * corrections列表
 * Class TenantExamQuestionCorrectionsLists
 * @package app\tenantapi\listsexam
 */
class TenantExamQuestionCorrectionsLists extends BaseAdminDataLists implements ListsSearchInterface
{


    /**
     * @notes 设置搜索条件
     * @return \string[][]
     * @author 精解析题库
     * @date 2025/08/05 23:16
     */
    public function setSearch(): array
    {
        return [
            '=' => ['question_uid', 'user_id'],
            '%like%' => ['question_name', 'correction_reason', 'user_nickname'],
        ];
    }


    /**
     * @notes 获取corrections列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 精解析题库
     * @date 2025/08/05 23:16
     */
    public function lists(): array
    {
        return TenantExamQuestionCorrections::where($this->searchWhere)
            ->field(['id', 'question_uid',  'question_name', 'exam_type', 'correction_reason', 'user_id', 'user_nickname', 'platform_feedback', 'feedback_time', 'create_time'])
            ->limit($this->limitOffset, $this->limitLength)
            ->order(['id' => 'desc'])
            ->select()
            ->toArray();
    }


    /**
     * @notes 获取corrections数量
     * @return int
     * @author 精解析题库
     * @date 2025/08/05 23:16
     */
    public function count(): int
    {
        return TenantExamQuestionCorrections::where($this->searchWhere)->count();
    }

}