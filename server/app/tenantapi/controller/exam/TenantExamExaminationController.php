<?php
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 访问官网：
// | 官方邮箱：
// | 精解析答题系统开发者版权所有，拥有最终解释权。


namespace app\tenantapi\controller\exam;


use app\tenantapi\controller\BaseAdminController;
use app\tenantapi\lists\exam\TenantExamExaminationLists;
use app\tenantapi\logic\exam\TenantExamExaminationLogic;
use app\tenantapi\validate\exam\TenantExamExaminationValidate;


/**
 * 考试管理控制器
 * Class TenantExamExaminationController
 * @package app\platform\controller\exam
 */
class TenantExamExaminationController extends BaseAdminController
{

    /**
     * @notes 获取考试管理列表
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/04/13 21:43
     */
    public function lists(): \think\response\Json
    {
        return $this->dataLists(new TenantExamExaminationLists($this->tenantId));
    }


    /**
     * @notes 添加考试管理
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/04/13 21:43
     */
    public function add(): \think\response\Json
    {
        $params = (new TenantExamExaminationValidate())->post()->goCheck('add');
        $result = TenantExamExaminationLogic::add(array_merge($params, $this->tenantInfo));
        if (true === $result) {
            return $this->success('添加成功', [], 1, 1);
        }
        return $this->fail(TenantExamExaminationLogic::getError());
    }


    /**
     * @notes 编辑考试管理
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/04/13 21:43
     */
    public function edit(): \think\response\Json
    {
        $params = (new TenantExamExaminationValidate())->post()->goCheck('edit');
        $result = TenantExamExaminationLogic::edit(array_merge($params, $this->tenantInfo));
        if (true === $result) {
            return $this->success('编辑成功', [], 1, 1);
        }
        return $this->fail(TenantExamExaminationLogic::getError());
    }


    /**
     * @notes 删除考试管理
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/04/13 21:43
     */
    public function delete(): \think\response\Json
    {
        $params = (new TenantExamExaminationValidate())->post()->goCheck('delete');
        TenantExamExaminationLogic::delete(array_merge($params, $this->tenantInfo));
        return $this->success('删除成功', [], 1, 1);
    }


    /**
     * @notes 获取考试管理详情
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/04/13 21:43
     */
    public function detail(): \think\response\Json
    {
        $params = (new TenantExamExaminationValidate())->goCheck('detail');
        $result = TenantExamExaminationLogic::detail(array_merge($params, $this->tenantInfo));
        return $this->data($result);
    }

    /**
     * 考试绑定试卷
     * @return \think\response\Json
     */
    public function savePaper(): \think\response\Json
    {
        $setResult = TenantExamExaminationLogic::savePaper(array_merge($this->request->all(), $this->tenantInfo));
        if ($setResult) {
            return $this->success('绑定成功');
        }
        return $this->fail(TenantExamExaminationLogic::getError());
    }
}