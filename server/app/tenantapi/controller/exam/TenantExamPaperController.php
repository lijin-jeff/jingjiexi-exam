<?php
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 访问官网：
// | 官方邮箱：
// | 精解析答题系统开发者版权所有，拥有最终解释权。
declare (strict_types=1);

namespace app\tenantapi\controller\exam;


use app\tenantapi\controller\BaseAdminController;
use app\tenantapi\lists\exam\TenantExamPaperLists;
use app\tenantapi\logic\exam\TenantExamPaperLogic;
use app\tenantapi\validate\exam\TenantExamPaperValidate;


/**
 * 试卷管理控制器
 * Class TenantExamPaperController
 * @package app\platform\controller\exam
 */
class TenantExamPaperController extends BaseAdminController
{


    /**
     * @notes 获取试卷管理列表
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/06/10 13:57
     */
    public function lists()
    {
        return $this->dataLists(new TenantExamPaperLists($this->tenantId));
    }


    /**
     * @notes 添加试卷管理
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/06/10 13:57
     */
    public function add()
    {
        $params = (new TenantExamPaperValidate())->post()->goCheck('add');
        $result = TenantExamPaperLogic::add(array_merge($params, $this->tenantInfo));
        if (true === $result) {
            return $this->success('添加成功', [], 1, 1);
        }
        return $this->fail(TenantExamPaperLogic::getError());
    }


    /**
     * @notes 编辑试卷管理
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/06/10 13:57
     */
    public function edit()
    {
        $params = (new TenantExamPaperValidate())->post()->goCheck('edit');
        $result = TenantExamPaperLogic::edit(array_merge($params, $this->tenantInfo));
        if (true === $result) {
            return $this->success('编辑成功', [], 1, 1);
        }
        return $this->fail(TenantExamPaperLogic::getError());
    }


    /**
     * @notes 删除试卷管理
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/06/10 13:57
     */
    public function delete()
    {
        $params = (new TenantExamPaperValidate())->post()->goCheck('delete');
        TenantExamPaperLogic::delete(array_merge($params, $this->tenantInfo));
        return $this->success('删除成功', [], 1, 1);
    }

    /**
     * 试卷组卷详情
     * @return \think\response\Json
     */
    public function paperDetail(): \think\response\Json
    {
        return $this->success('查询成功', TenantExamPaperLogic::paperDetail(array_merge($this->request->all(), $this->tenantInfo)));
    }

    /**
     * @notes 获取试卷管理详情
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/06/10 13:57
     */
    public function detail()
    {
        $params = (new TenantExamPaperValidate())->goCheck('detail');
        $result = TenantExamPaperLogic::detail(array_merge($params, $this->tenantInfo));
        return $this->data($result);
    }

    /**
     * 保存组卷数据
     * @return \think\response\Json
     */
    public function savePaper(): \think\response\Json
    {
        $result = TenantExamPaperLogic::savePaper(array_merge($this->request->all(), $this->tenantInfo));
        if ($result) {
            return $this->success('保存成功');
        }
        return $this->fail(TenantExamPaperLogic::getError() ?? '保存失败');
    }
}