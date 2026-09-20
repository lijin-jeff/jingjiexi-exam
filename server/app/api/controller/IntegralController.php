<?php
declare(strict_types=1);
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 精解析答题系统开发者版权所有，拥有最终解释权。
namespace app\api\controller;

use app\api\logic\IntegralLogic;
use app\api\validate\CommonValidate;

class IntegralController extends BaseApiController
{
    /**
     * 用户积分明细
     * @return \think\response\Json
     */
    public function integralList(): \think\response\Json
    {
        (new CommonValidate())->get()->goCheck('page');
        $params = $this->request->param();
        $params['user_id'] = $this->userId;
        return $this->success('积分查询成功', IntegralLogic::integralList($params));
    }
    
    
    /**
     * 获取用户积分信息
     * @return \think\response\Json
     */
    public function userIntegral(): \think\response\Json
    {
        try {
            $userIntegral = IntegralLogic::userIntegral($this->userId);
            return $this->success('获取用户积分成功', [
                'integral' => $userIntegral
            ]);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage() ?: '获取用户积分失败');
        }
    }

    // 从用户表查询用户积分列表（用于积分排行榜）
    public function userIntegralRanking(): \think\response\Json
    {
        return $this->success('用户积分排行榜查询成功', IntegralLogic::userIntegralRanking($this->userId));
    }
}