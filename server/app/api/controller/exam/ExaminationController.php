<?php
declare(strict_types=1);
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 精解析答题系统开发者版权所有，拥有最终解释权。
namespace app\api\controller\exam;

use app\api\controller\BaseApiController;
use app\api\logic\exam\ExaminationLogic;
use app\api\validate\CommonValidate;
use app\api\validate\exam\ExaminationValidate;
use app\api\validate\exam\QuestionValidate;

class ExaminationController extends BaseApiController
{
    public array $notNeedLogin = [
        'mockExaminationConfig',
        'mockQuestionList',
        'mockExaminationList',
        'examinationList',
        'examinationContent',
    ];

    /**
     * 提交模拟考试配置
     * @return \think\response\Json
     */
    public function mockExaminationConfig(): \think\response\Json
    {
        $params = (new ExaminationValidate())->post()->goCheck('mock');
        $params['user_uid'] = $this->userId;
        $configResult = ExaminationLogic::mockExaminationConfig($params);
        if (!empty($configResult)) {
            return $this->success('提交成功', ['history_uid' => $configResult]);
        }
        return $this->fail(ExaminationLogic::getError());
    }

    /**
     * 模拟考试试题查询
     * @return \think\response\Json
     */
    public function mockQuestionList(): \think\response\Json
    {
        $params = (new QuestionValidate())->get()->goCheck('detail');
        $params['user_uid'] = $this->userId;
        return $this->success('试题获取成功', ExaminationLogic::mockExamList($params));
    }

    /**
     * 保存模拟考试数据
     * @return \think\response\Json
     */
    public function saveMockExamination(): \think\response\Json
    {
        $result = ExaminationLogic::saveMockExamination($this->request->param());
        if (count($result)) {
            return $this->success('计算成功', $result);
        }
        return $this->fail(ExaminationLogic::getError());
    }

    /**
     * 模拟考试历史记录
     * @return \think\response\Json
     */
    public function mockExaminationList(): \think\response\Json
    {
        (new QuestionValidate())->get()->goCheck('detail');
        (new CommonValidate())->get()->goCheck('page');
        $params = $this->request->param();
        $params['user_uid'] = $this->userId;
        return $this->success('获取成功', ExaminationLogic::mockExaminationList($params));
    }

    /**
     * 考试列表
     * @return \think\response\Json
     * @author 精解析答题
     */
    public function examinationList(): \think\response\Json
    {
        (new CommonValidate())->get()->goCheck('page');
        return $this->success('考试查询成功', ExaminationLogic::examinationList($this->request->param()));
    }

    /**
     * 考试详情
     * @return \think\response\Json
     * @author 精解析答题
     */
    public function examinationContent(): \think\response\Json
    {
        (new CommonValidate())->get()->goCheck('uid');
        $params = $this->request->param();
        $params['user_uid'] = $this->userId;
        return $this->success('考试查询成功', ExaminationLogic::examinationContent($params));
    }

    /**
     * 提交在线考试数据
     * @return \think\response\Json
     * @author 精解析答题
     */
    public function submitExamination(): \think\response\Json
    {
        try {
            $params =(new CommonValidate())->post()->goCheck('uid');
            $params['user_uid'] = $this->userId;
            $result = ExaminationLogic::submitExamination($params);
            if (count($result)) {
                return $this->success('提交成功', $result);
            }
            return $this->fail(ExaminationLogic::getError());
        } catch (\Exception $e) {
            // 返回错误信息而不是500 HTML
            return $this->fail('submitExamination error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
        }
    }

    /**
     * 考试历史
     * @return \think\response\Json
     * @author 精解析答题
     */
    public function getExaminationHistory(): \think\response\Json
    {
        $params = (new CommonValidate())->get()->goCheck('page');
        $params['user_uid'] = $this->userId;
        return $this->success('查询成功', ExaminationLogic::getExaminationHistory($params));
    }

    /**
     * 获取考试历史详情
     * @return \think\response\Json
     * @author 精解析答题
     */
    public function getExaminationHistoryDetail(): \think\response\Json
    {
        // 修复：先获取请求参数，然后添加 user_uid
        $params = $this->request->param();
        $params['user_uid'] = $this->userId;
        return $this->success('查询成功', ExaminationLogic::getExaminationHistoryDetail($params));
    }
}