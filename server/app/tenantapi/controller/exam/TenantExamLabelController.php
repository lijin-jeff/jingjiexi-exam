<?php
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 精解析答题系统开发者版权所有，拥有最终解释权。


namespace app\tenantapi\controller\exam;

use app\tenantapi\controller\BaseAdminController;
use app\tenantapi\lists\exam\TenantExamLabelLists;
use app\tenantapi\logic\exam\TenantExamLabelLogic;
use app\tenantapi\validate\exam\TenantExamLabelValidate;

/**
 * 题库标签控制器
 * Class TenantExamLabelController
 * @package app\platform\controller\exam
 */
class TenantExamLabelController extends BaseAdminController
{


    /**
     * @notes 获取题库标签列表  
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/04/12 15:53
     */
    public function lists(): \think\response\Json
    {
       // print_r($this->tenantId);exit;
        return $this->dataLists(new TenantExamLabelLists($this->tenantId));
    }


    /**
     * @notes 添加题库标签
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/04/12 15:53
     */
    public function add(): \think\response\Json
    {
        $params = (new TenantExamLabelValidate())->post()->goCheck('add');
        $result = TenantExamLabelLogic::add(array_merge($params, ['tenant_id' => $this->tenantId]));
        if (true === $result) {
            return $this->success('添加成功', [], 1, 1);
        }
        return $this->fail(TenantExamLabelLogic::getError());
    }


    /**
     * @notes 编辑题库标签
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/04/12 15:53
     */
    public function edit(): \think\response\Json
    {
        $params = (new TenantExamLabelValidate())->post()->goCheck('edit');
        $result = TenantExamLabelLogic::edit(array_merge($params, ['tenant_id' => $this->tenantId]));
        if (true === $result) {
            return $this->success('编辑成功', [], 1, 1);
        }
        return $this->fail(TenantExamLabelLogic::getError());
    }


    /**
     * @notes 删除题库标签
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/04/12 15:53
     */
    public function delete(): \think\response\Json
    {
        $params = (new TenantExamLabelValidate())->post()->goCheck('delete');
        $result = TenantExamLabelLogic::delete(array_merge($params, ['tenant_id' => $this->tenantId]));
        if (true === $result) {
            return $this->success('删除成功', [], 1, 1);
        }
        return $this->fail(TenantExamLabelLogic::getError());
    }


    /**
     * @notes 获取题库标签详情
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/04/12 15:53
     */
    public function detail(): \think\response\Json
    {
        $params = (new TenantExamLabelValidate())->goCheck('detail');
        //print_r($this->tenantId);
        $result = TenantExamLabelLogic::detail(array_merge($params, ['tenant_id' => $this->tenantId]));
        //print_r($result);
        return $this->data($result);
    }

}