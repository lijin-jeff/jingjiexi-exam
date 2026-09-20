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
use app\tenantapi\lists\exam\TenantExamActivationCodeLists;
use app\tenantapi\logic\exam\TenantExamActivationCodeLogic;
use app\tenantapi\validate\exam\TenantExamActivationCodeValidate;


/**
 * code控制器
 * Class TenantExamActivationCodeController
 * @package app\tenantapi\controller\exam
 */
    class TenantExamActivationCodeController extends BaseAdminController
{


    /**
     * @notes 获取code列表
     * @return \think\response\Json
     * @author likeadmin
     * @date 2025/08/06 16:34
     */
    public function lists()
    {
        $params = $this->request->param();
        return $this->dataLists(new TenantExamActivationCodeLists($params));
    }

    /**
     * @notes 更新状态
     * @return \think\response\Json
     * @author likeadmin
     * @date 2025/08/06 16:34
     */
    public function updateStatus(){
        
        $params = (new TenantExamActivationCodeValidate())->post()->goCheck('status');
        $result = TenantExamActivationCodeLogic::updateStatus($params);
        if($result){
            return $this->success('更新成功');
        }
        return $this->fail(TenantExamActivationCodeLogic::getError());
    }


}