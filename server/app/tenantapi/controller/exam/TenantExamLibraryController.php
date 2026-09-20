<?php
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 精解析答题系统开发者版权所有，拥有最终解释权。


namespace app\tenantapi\controller\exam;


use app\tenantapi\controller\BaseAdminController;
use app\tenantapi\lists\exam\TenantExamLibraryLists;
use app\tenantapi\validate\exam\TenantExamLibraryValidate;
use app\tenantapi\logic\exam\TenantExamLibraryLogic;


/**
 * 题库管理控制器
 * Class TenantExamLibraryController
 * @package app\tenantapi\controller\exam
 */
class TenantExamLibraryController extends BaseAdminController
{


    /**
     * @notes 获取题库管理列表
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/04/01 04:41
     */
    public function lists(): \think\response\Json
    {
        return $this->dataLists(new TenantExamLibraryLists());
    }


    /**
     * @notes 添加题库管理
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/04/01 04:41
     */
    public function add(): \think\response\Json
    {
        $params = (new TenantExamLibraryValidate())->post()->goCheck('add');
        $result = TenantExamLibraryLogic::add(array_merge($params, $this->tenantInfo));
        if (true === $result) {
            return $this->success('添加成功', [], 1, 1);
        }
        return $this->fail(TenantExamLibraryLogic::getError());
    }


    /**
     * @notes 编辑题库管理
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/04/01 04:41
     */
    public function edit(): \think\response\Json
    {
        $params = (new TenantExamLibraryValidate())->post()->goCheck('edit');
        $result = TenantExamLibraryLogic::edit(array_merge($params, $this->tenantInfo));
        if (true === $result) {
            return $this->success('编辑成功', [], 1, 1);
        }
        return $this->fail(TenantExamLibraryLogic::getError());
    }


    /**
     * @notes 删除题库管理
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/04/01 04:41
     */
    public function delete(): \think\response\Json
    {
        $params = (new TenantExamLibraryValidate())->post()->goCheck('delete');
        TenantExamLibraryLogic::delete(array_merge($params, $this->tenantInfo));
        return $this->success('删除成功', [], 1, 1);
    }


    /**
     * @notes 获取题库管理详情
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/04/01 04:41
     */
    public function detail(): \think\response\Json
    {
        $params = (new TenantExamLibraryValidate())->goCheck('detail');
        $result = TenantExamLibraryLogic::detail(array_merge($params, $this->tenantInfo));
        return $this->data($result);
    }

    /**
     * @notes 题库导出
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2026/01/18
     */
    public function export(): \think\response\Json
    {
        return $this->dataLists(new TenantExamLibraryLists());
    }

    /**
     * @notes 题库导入
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2026/01/18
     */
    public function import(): \think\response\Json
    {
        $file = $this->request->file('file');
        if (empty($file)) {
            return $this->fail('请选择要导入的文件');
        }
        
        $result = TenantExamLibraryLogic::import($file, $this->tenantId);
        return $this->success('', $result);
    }


}