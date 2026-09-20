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
 * tenantSettingBanner验证器
 * Class TenantSettingBannerValidate
 * @package app\tenantapi\validate\exam
 */
class TenantSettingBannerValidate extends BaseValidate
{

     /**
      * 设置校验规则
      * @var string[]
      */
    protected $rule = [
        'id' => 'require',
        'uid' => 'require',
        'title' => 'require',
        'is_show' => 'require',
        'sort' => 'require',
        'position' => 'require',
        'client' => 'require',
        'image_type' => 'require',
    ];


    /**
     * 参数描述
     * @var string[]
     */
    protected $field = [
        'id' => 'id',
        'title' => 'title',
        'is_show' => 'is_show',
        'sort' => 'sort',
        'position' => '位置',
        'client' => '平台',
        'image_type' => '类型',
    ];


    /**
     * @notes 添加场景
     * @return TenantSettingBannerValidate
     * @author likeadmin
     * @date 2025/08/19 08:53
     */
    public function sceneAdd()
    {
        return $this->only(['title','is_show','sort','position','client','image_type']);
    }


    /**
     * @notes 编辑场景
     * @return TenantSettingBannerValidate
     * @author likeadmin
     * @date 2025/08/19 08:53
     */
    public function sceneEdit()
    {
        return $this->only(['id','title','is_show','sort','position','client','image_type']);
    }


    /**
     * @notes 删除场景
     * @return TenantSettingBannerValidate
     * @author likeadmin
     * @date 2025/08/19 08:53
     */
    public function sceneDelete()
    {
        return $this->only(['id']);
    }


    /**
     * @notes 详情场景
     * @return TenantSettingBannerValidate
     * @author likeadmin
     * @date 2025/08/19 08:53
     */
    public function sceneDetail()
    {
        return $this->only(['id']);
    }

}