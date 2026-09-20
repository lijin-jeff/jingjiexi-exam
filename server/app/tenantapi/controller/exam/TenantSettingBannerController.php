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

use app\common\model\tenant\Tenant;
use app\tenantapi\controller\BaseAdminController;
use app\tenantapi\lists\exam\TenantSettingBannerLists;
use app\tenantapi\logic\exam\TenantSettingBannerLogic;
use app\tenantapi\validate\exam\TenantSettingBannerValidate;


/**
 * tenantBanner控制器
 * Class TenantSettingBannerController
 * @package app\tenantapi\controller\exam
 */
class TenantSettingBannerController extends BaseAdminController
{


    /**
     * @notes 获取tenantBanner列表
     * @return \think\response\Json
     * @author likeadmin
     * @date 2025/08/19 08:53
     */
    public function lists()
    {
       // print_r($this->request->param());
        return $this->dataLists(new TenantSettingBannerLists());
    }


    /**
     * @notes 添加tenantBanner
     * @return \think\response\Json
     * @author likeadmin
     * @date 2025/08/19 08:53
     */
    public function add()
    {
        $params = (new TenantSettingBannerValidate())->post()->goCheck('add');
        $params['tenant_id'] = $this->tenantId;
        $result = TenantSettingBannerLogic::add($params);
        if (true === $result) {
            return $this->success('添加成功', [], 1, 1);
        }
        return $this->fail(TenantSettingBannerLogic::getError());
    }


    /**
     * @notes 编辑tenantBanner
     * @return \think\response\Json
     * @author likeadmin
     * @date 2025/08/19 08:53
     */
    public function edit()
    {
        $params = (new TenantSettingBannerValidate())->post()->goCheck('edit');
        $result = TenantSettingBannerLogic::edit($params);
        if (true === $result) {
            return $this->success('编辑成功', [], 1, 1);
        }
        return $this->fail(TenantSettingBannerLogic::getError());
    }


    /**
     * @notes 删除tenantBanner
     * @return \think\response\Json
     * @author likeadmin
     * @date 2025/08/19 08:53
     */
    public function delete()
    {
        $params = (new TenantSettingBannerValidate())->post()->goCheck('delete');
        TenantSettingBannerLogic::delete($params);
        return $this->success('删除成功', [], 1, 1);
    }


    /**
     * @notes 获取tenantBanner详情
     * @return \think\response\Json
     * @author likeadmin
     * @date 2025/08/19 08:53
     */
    public function detail()
    {
        $params = (new TenantSettingBannerValidate())->goCheck('detail');
        $result = TenantSettingBannerLogic::detail($params);
        return $this->data($result);
    }


}