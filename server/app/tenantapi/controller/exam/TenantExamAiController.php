<?php
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 精解析答题系统开发者版权所有，拥有最终解释权。

namespace app\tenantapi\controller\exam;

use app\tenantapi\controller\BaseAdminController;
use app\tenantapi\logic\exam\TenantExamAiLogic;

/**
 * AI 试题识别控制器
 * Class TenantExamAiController
 * @package app\tenantapi\controller\exam
 */
class TenantExamAiController extends BaseAdminController
{
    /**
     * @notes AI 识别试题
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/04/12 15:53
     */
    public function recognize(): \think\response\Json
    {
        try {
            $params = $this->request->param();
            $params['tenant_id'] = $this->tenantId;
            
            // 获取上传的图片文件
            $imageFile = $this->request->file('image');
            if (!$imageFile) {
                return $this->fail('请上传图片');
            }
            
            $params['image'] = $imageFile;
            
            $result = TenantExamAiLogic::recognize($params);
            
            if (isset($result['error'])) {
                return $this->fail($result['error']);
            }
            
            return $this->success('识别成功', $result);
        } catch (\Exception $e) {
            return $this->fail('识别失败：' . $e->getMessage());
        }
    }
}
