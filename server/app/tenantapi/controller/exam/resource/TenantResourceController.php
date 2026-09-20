<?php
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 访问官网：精解析答题
// | 官方邮箱：精解析答题
// | 精解析答题系统开发者版权所有，拥有最终解释权。
declare (strict_types=1);

namespace app\tenantapi\controller\exam\resource;

use app\tenantapi\controller\BaseAdminController;
use app\tenantapi\lists\exam\resource\TenantResourceLists;
use app\tenantapi\logic\exam\resource\TenantResourceLogic;
use app\tenantapi\validate\exam\resource\TenantResourceValidate;


/**
 * 资源管理控制器
 * Class TenantResourceController
 * @package app\tenantapi\controller\exam\resource
 */
class TenantResourceController extends BaseAdminController
{


    /**
     * @notes 获取资源管理列表
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/06/17 00:37
     */
    public function lists(): \think\response\Json
    {
        return $this->dataLists(new TenantResourceLists($this->tenantId));
    }


    /**
     * @notes 添加资源管理
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/06/17 00:37
     */
    public function add(): \think\response\Json
    {
        $params = (new TenantResourceValidate())->post()->goCheck('add');
        $params['tenant_id'] = $this->tenantId;
        $result = TenantResourceLogic::add($params);
        if (true === $result) {
            return $this->success('添加成功', [], 1, 1);
        }
        return $this->fail(TenantResourceLogic::getError());
    }


    /**
     * @notes 编辑资源管理
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/06/17 00:37
     */
    public function edit(): \think\response\Json
    {
        $params = (new TenantResourceValidate())->post()->goCheck('edit');
        $params['tenant_id'] = $this->tenantId;
        $result = TenantResourceLogic::edit($params);
        if (true === $result) {
            return $this->success('编辑成功', [], 1, 1);
        }
        return $this->fail(TenantResourceLogic::getError());
    }


    /**
     * @notes 删除资源管理
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/06/17 00:37
     */
    public function delete(): \think\response\Json
    {
        $params = (new TenantResourceValidate())->post()->goCheck('delete');
        TenantResourceLogic::delete($params);
        return $this->success('删除成功', [], 1, 1);
    }


    /**
     * @notes 获取资源管理详情
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/06/17 00:37
     */
    public function detail(): \think\response\Json
    {
        $params = (new TenantResourceValidate())->goCheck('detail');
        $params['tenant_id'] = $this->tenantId;
        $result = TenantResourceLogic::detail($params);
        return $this->data($result);
    }
    
    /**
     * @notes 批量复制资源管理
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/06/17 00:37
     */
    public function copy(): \think\response\Json
    {
        $params = (new TenantResourceValidate())->post()->goCheck('copy');
        $params['tenant_id'] = $this->tenantId;
        $result = TenantResourceLogic::copy($params);
        if (true === $result) {
            return $this->success('批量复制成功', [], 1, 1);
        }
        return $this->fail(TenantResourceLogic::getError());
    }


}