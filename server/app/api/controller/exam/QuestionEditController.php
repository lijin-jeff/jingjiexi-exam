<?php
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 精解析答题系统开发者版权所有，拥有最终解释权。

namespace app\api\controller\exam;

use app\api\controller\BaseApiController;
use app\api\logic\exam\QuestionEditLogic;
use app\api\validate\exam\QuestionValidate;

/**
 * 租户试题管理控制器
 * Class QuestionEditController
 * @package app\api\controller\exam
 */
class QuestionEditController extends BaseApiController
{

    /**
     * @notes 添加试题
     * @return \think\response\Json
     * @date 2026/01/25
     */
    public function questionAdd()
    {
        $params = $this->request->param();
        $params['tenant_id'] = request()->tenantId;
        try {
            if (QuestionEditLogic::add($params)) {
                return $this->success('添加试题成功');
            }
            return $this->fail(QuestionEditLogic::getError());
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    
    /**
     * @notes 编辑试题
     * @return \think\response\Json
     * @date 2026/01/25
     */
    public function questionEdit()
    {
        $params = $this->request->param();
        $params['tenant_id'] = request()->tenantId;
        
        try {
            if (QuestionEditLogic::edit($params)) {
                return $this->success('编辑试题成功');
            }
            return $this->fail(QuestionEditLogic::getError());
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    // 隐藏试题
    public function questionShow()
    {
        $params = (new QuestionValidate())->post()->goCheck('show');
        $params['tenant_id'] = request()->tenantId;
        try {
            if (QuestionEditLogic::questionShow($params)) {
                return $this->success('隐藏试题成功');
            }
            return $this->fail(QuestionEditLogic::getError());
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * @notes 删除试题
     * @return \think\response\Json
     * @date 2026/01/25
     */
    public function questionDelete()
    {
        $params = (new QuestionValidate())->post()->goCheck('delete');
        $params['tenant_id'] = request()->tenantId;
        
        try {
            if (QuestionEditLogic::delete($params)) {
                return $this->success('删除试题成功');
            }
            return $this->fail(QuestionEditLogic::getError());
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }
    
    /**
     * @notes 批量删除试题
     * @return \think\response\Json
     * @date 2026/01/25
     */
    public function batchDelete()
    {
        $params = (new QuestionValidate())->post()->goCheck('batchDelete');
        $params['tenant_id'] = request()->tenantId;
        
        try {
            if (QuestionEditLogic::batchDelete($params)) {
                return $this->success('批量删除试题成功');
            }
            return $this->fail(QuestionEditLogic::getError());
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }


    /**
     * @notes 文本批量导入试题
     * @return \think\response\Json
     * @date 2026/01/25
     */
    public function textAdd()
    {
        $params = (new QuestionValidate())->post()->goCheck('textAdd');
        $params['tenant_id'] = request()->tenantId;
        $params['uid'] = $this->userId;
        
        try {
            if (QuestionEditLogic::textAdd($params)) {
                return $this->success('批量导入试题成功');
            }
            return $this->fail(QuestionEditLogic::getError());
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * @notes 试题详情
     * @return \think\response\Json
     * @date 2026/01/25
     */
    public function questionDetail()
    {
        $params = (new QuestionValidate())->get()->goCheck('detail');
        $params['tenant_id'] = request()->tenantId;
        
        try {
            $result = QuestionEditLogic::questionDetail($params);
            if ($result) {
                return $this->success('获取试题详情成功', $result);
            }
            return $this->fail(QuestionEditLogic::getError());
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }
}