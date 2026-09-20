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
use app\tenantapi\lists\exam\TenantExamActivationCodeBatchLists;
use app\tenantapi\logic\exam\TenantExamActivationCodeBatchLogic;
use app\tenantapi\validate\exam\TenantExamActivationCodeBatchValidate;


/**
 * codeBatch控制器
 * Class TenantExamActivationCodeBatchController
 * @package app\tenantapi\controller\exam
 */
class TenantExamActivationCodeBatchController extends BaseAdminController
{


    /**
     * @notes 获取codeBatch列表
     * @return \think\response\Json
     * @author likeadmin
     * @date 2025/08/06 08:55
     */
    public function lists()
    {
        return $this->dataLists(new TenantExamActivationCodeBatchLists());
    }


    /**
     * @notes 添加codeBatch
     * @return \think\response\Json
     * @author likeadmin
     * @date 2025/08/06 08:55
     */
    public function add()
    {
        //print_r($this->request->param());exit;
        $params = (new TenantExamActivationCodeBatchValidate())->post()->goCheck('add');
        $params['tenant_id'] = $this->tenantId;
        $result = TenantExamActivationCodeBatchLogic::add($params);
        if (true === $result) {
            return $this->success('添加成功', [], 1, 1);
        }
        return $this->fail(TenantExamActivationCodeBatchLogic::getError());
    }


    /**
     * @notes 编辑codeBatch
     * @return \think\response\Json
     * @author likeadmin
     * @date 2025/08/06 08:55
     */
    public function edit()
    {
        $params = (new TenantExamActivationCodeBatchValidate())->post()->goCheck('edit');
        $result = TenantExamActivationCodeBatchLogic::edit($params);
        if (true === $result) {
            return $this->success('编辑成功', [], 1, 1);
        }
        return $this->fail(TenantExamActivationCodeBatchLogic::getError());
    }


    /**
     * @notes 删除codeBatch
     * @return \think\response\Json
     * @author likeadmin
     * @date 2025/08/06 08:55
     */
    public function delete()
    {
        $params = (new TenantExamActivationCodeBatchValidate())->post()->goCheck('delete');
        TenantExamActivationCodeBatchLogic::delete($params);
        return $this->success('删除成功', [], 1, 1);
    }


    /**
     * @notes 获取codeBatch详情
     * @return \think\response\Json
     * @author likeadmin
     * @date 2025/08/06 08:55
     */
    public function detail()
    {
        $params = (new TenantExamActivationCodeBatchValidate())->goCheck('detail');
        $result = TenantExamActivationCodeBatchLogic::detail($params);
        return $this->data($result);
    }


}