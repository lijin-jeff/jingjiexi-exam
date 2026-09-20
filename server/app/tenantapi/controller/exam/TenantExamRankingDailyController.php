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
use app\tenantapi\lists\exam\TenantExamRankingDailyLists;
use app\tenantapi\logic\exam\TenantExamRankingDailyLogic;
use app\tenantapi\validate\exam\TenantExamRankingDailyValidate;


/**
 * tenantRankingDaily控制器
 * Class TenantExamRankingDailyController
 * @package app\tenantapi\controller\exam
 */
class TenantExamRankingDailyController extends BaseAdminController
{


    /**
     * @notes 获取tenantRankingDaily列表
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/08/24 10:49
     */
    public function lists()
    {
        return $this->dataLists(new TenantExamRankingDailyLists());
    }


    /**
     * @notes 添加tenantRankingDaily
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/08/24 10:49
     */
    public function add()
    {
        $params = (new TenantExamRankingDailyValidate())->post()->goCheck('add');
        $result = TenantExamRankingDailyLogic::add($params);
        if (true === $result) {
            return $this->success('添加成功', [], 1, 1);
        }
        return $this->fail(TenantExamRankingDailyLogic::getError());
    }


    /**
     * @notes 编辑tenantRankingDaily
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/08/24 10:49
     */
    public function edit()
    {
        $params = (new TenantExamRankingDailyValidate())->post()->goCheck('edit');
        $result = TenantExamRankingDailyLogic::edit($params);
        if (true === $result) {
            return $this->success('编辑成功', [], 1, 1);
        }
        return $this->fail(TenantExamRankingDailyLogic::getError());
    }


    /**
     * @notes 删除tenantRankingDaily
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/08/24 10:49
     */
    public function delete()
    {
        $params = (new TenantExamRankingDailyValidate())->post()->goCheck('delete');
        TenantExamRankingDailyLogic::delete($params);
        return $this->success('删除成功', [], 1, 1);
    }


    /**
     * @notes 获取tenantRankingDaily详情
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/08/24 10:49
     */
    public function detail()
    {
        $params = (new TenantExamRankingDailyValidate())->goCheck('detail');
        $result = TenantExamRankingDailyLogic::detail($params);
        return $this->data($result);
    }


}