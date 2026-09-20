<?php
// +----------------------------------------------------------------------
// | likeadmin快速开发前后端分离管理后台（PHP版）
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 开源版本可自由商用，可去除界面版权logo
// | gitee下载：https://gitee.com/likeshop_gitee/likeadmin
// | github下载：https://github.com/likeshop-github/likeadmin
// | 访问官网：https://www.likeadmin.cn
// | likeadmin团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeadminTeam
// +----------------------------------------------------------------------


namespace app\tenantapi\controller\exam;


use app\common\model\exam\TenantExamRankingSettings;
use app\tenantapi\controller\BaseAdminController;
use app\tenantapi\logic\exam\TenantExamRankingSettingsLogic;
use app\tenantapi\validate\exam\TenantExamRankingSettingsValidate;


/**
 * rankSetting控制器
 * Class TenantExamRankingSettingsController
 * @package app\tenantapi\controller\exam
 */
class TenantExamRankingSettingsController extends BaseAdminController
{
    /**
     * @notes 编辑rankSetting
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/08/24 13:16
     */
    public function edit()
    {
        // 获取所有参数，包括数组类型的参数
        $params = $this->request->param();
        $params['tenant_id'] = $this->tenantId;
        
        // 验证参数
        $validate = new TenantExamRankingSettingsValidate();
        $scene = isset($params['id']) ? 'edit' : 'add';
        
        if (!$validate->scene($scene)->check($params)) {
            return $this->fail($validate->getError());
        }
        
        // 修复：直接使用模型查询，确保每个租户只有一条记录
        $existingRecord = TenantExamRankingSettings::where('tenant_id', $params['tenant_id'])->find();
        
        if ($existingRecord) {
            // 已有记录，执行编辑
            $params['id'] = $existingRecord->id;
            $result = TenantExamRankingSettingsLogic::edit($params);
        } else {
            // 没有记录，执行添加
            $result = TenantExamRankingSettingsLogic::add($params);
        }
        
        if (true === $result) {
            return $this->success('编辑成功', [], 1, 1);
        }
        return $this->fail(TenantExamRankingSettingsLogic::getError());
    }

    /**
     * @notes 获取rankSetting详情
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/08/24 13:16
     */
    public function detail()
    {
        $params['tenant_id'] = $this->tenantId;
        $result = TenantExamRankingSettingsLogic::detail($params);
        return $this->data($result);
    }

}