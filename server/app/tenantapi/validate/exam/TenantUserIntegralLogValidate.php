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

namespace app\tenantapi\validate\exam;


use app\common\validate\BaseValidate;


/**
 * integral验证器
 * Class TenantUserIntegralLogValidate
 * @package app\tenantapi\validate\exam
 */
class TenantUserIntegralLogValidate extends BaseValidate
{

     /**
      * 设置校验规则
      * @var string[]
      */
    protected $rule = [
        'id' => 'require',
        'user_id' => 'require',
        'action' => 'require',
        'change_amount' => 'require',
        'title' => 'require',
        'change_type' => 'require',
        'action_type' => 'require',
    ];


    /**
     * 参数描述
     * @var string[]
     */
    protected $field = [
        'id' => 'id',
        'user_id' => '用户ID',
        'action' => '动作',
        'change_amount' => '变动数量',
        'title' => '积分名称',
        'change_type' => '变动类型',
        'action_type' => '操作类型',
    ];


    /**
     * @notes 添加场景
     * @return TenantUserIntegralLogValidate
     * @author 精解析题库
     * @date 2025/08/26 09:14
     */
    public function sceneAdd()
    {
        return $this->only(['user_id','action','change_amount','title','change_type','action_type']);
    }


    /**
     * @notes 编辑场景
     * @return TenantUserIntegralLogValidate
     * @author 精解析题库
     * @date 2025/08/26 09:14
     */
    public function sceneEdit()
    {
        return $this->only(['id','user_id','action','change_amount','title','change_type','action_type']);
    }


    /**
     * @notes 删除场景
     * @return TenantUserIntegralLogValidate
     * @author 精解析题库
     * @date 2025/08/26 09:14
     */
    public function sceneDelete()
    {
        return $this->only(['id']);
    }


    /**
     * @notes 详情场景
     * @return TenantUserIntegralLogValidate
     * @author 精解析题库
     * @date 2025/08/26 09:14
     */
    public function sceneDetail()
    {
        return $this->only(['id']);
    }

}