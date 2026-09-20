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
use app\tenantapi\logic\exam\TenantIntegralSettingsLogic;


/**
 * integralSetting控制器
 * Class TenantIntegralSettingsController
 * @package app\tenantapi\controller\exam
 */
class TenantIntegralSettingsController extends BaseAdminController
{

    /**
     * @notes 编辑integralSetting
     * @return \think\response\Json
     * @author 精解析题库
     * @date 2025/08/26 09:13
     */
    public function edit()
    {
        $params = $this->request->param();
        $params['tenant_id'] = $this->tenantId;
        $resultdetail = TenantIntegralSettingsLogic::detail($params);
        if (empty($resultdetail['id'])) {
            $result = TenantIntegralSettingsLogic::add($params);    
        } else {
            $result = TenantIntegralSettingsLogic::edit($params);
        }
        if (true === $result) {
            return $this->success('编辑成功', [], 1, 1);
        }
        return $this->fail(TenantIntegralSettingsLogic::getError());
    }

    /**
     * @notes 获取integralSetting详情
     * @return \think\response\Json
     * @author 精解析题库
     * @date 2025/08/26 09:13
     */
    public function detail()
    {
        $params['tenant_id'] = $this->tenantId;
        $result = TenantIntegralSettingsLogic::detail($params);
        return $this->data($result);
    }


}