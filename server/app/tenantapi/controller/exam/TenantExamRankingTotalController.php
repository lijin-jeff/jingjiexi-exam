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


namespace app\tenantapi\controller\exam;


use app\tenantapi\controller\BaseAdminController;
use app\tenantapi\lists\exam\TenantExamRankingTotalLists;
use app\tenantapi\logic\exam\TenantExamRankingTotalLogic;
use app\tenantapi\validate\exam\TenantExamRankingTotalValidate;


/**
 * tenantRankingTotal控制器
 * Class TenantExamRankingTotalController
 * @package app\tenantapi\controller\exam
 */
class TenantExamRankingTotalController extends BaseAdminController
{


    /**
     * @notes 获取tenantRankingTotal列表
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/08/24 10:49
     */
    public function lists()
    {
        return $this->dataLists(new TenantExamRankingTotalLists());
    }


    /**
     * @notes 添加tenantRankingTotal
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/08/24 10:49
     */
    public function add()
    {
        $params = (new TenantExamRankingTotalValidate())->post()->goCheck('add');
        $result = TenantExamRankingTotalLogic::add($params);
        if (true === $result) {
            return $this->success('添加成功', [], 1, 1);
        }
        return $this->fail(TenantExamRankingTotalLogic::getError());
    }


    /**
     * @notes 编辑tenantRankingTotal
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/08/24 10:49
     */
    public function edit()
    {
        $params = (new TenantExamRankingTotalValidate())->post()->goCheck('edit');
        $result = TenantExamRankingTotalLogic::edit($params);
        if (true === $result) {
            return $this->success('编辑成功', [], 1, 1);
        }
        return $this->fail(TenantExamRankingTotalLogic::getError());
    }


    /**
     * @notes 删除tenantRankingTotal
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/08/24 10:49
     */
    public function delete()
    {
        $params = (new TenantExamRankingTotalValidate())->post()->goCheck('delete');
        TenantExamRankingTotalLogic::delete($params);
        return $this->success('删除成功', [], 1, 1);
    }


    /**
     * @notes 获取tenantRankingTotal详情
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/08/24 10:49
     */
    public function detail()
    {
        $params = (new TenantExamRankingTotalValidate())->goCheck('detail');
        $result = TenantExamRankingTotalLogic::detail($params);
        return $this->data($result);
    }


}