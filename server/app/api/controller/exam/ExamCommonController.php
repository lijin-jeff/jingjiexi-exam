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
use app\api\logic\exam\ExamCommonLogic;

class ExamCommonController extends BaseApiController
{
    /**
     * 积分兑换会员
     * @return \think\response\Json
     */
    public function vipIntegralPay(): \think\response\Json
    {
        $params = $this->request->param();
        $params['user_uid'] = $this->userId;
        if (ExamCommonLogic::vipIntegralPay($params)) {
            return $this->success('积分兑换成功');
        }
        
        return $this->fail(ExamCommonLogic::getError());
    }
    
    /**
     * 激活码兑换会员
     * @return \think\response\Json
     */
    public function vipActivationCode(): \think\response\Json
    {

        $params = $this->request->param();
        $params['user_uid'] = $this->userId;
        if (ExamCommonLogic::vipActivationCode($params)) {
            return $this->success('激活码兑换成功');
        }
        
        return $this->fail(ExamCommonLogic::getError());
    }
}