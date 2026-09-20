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
use app\common\model\exam\TenantVersionUpdate;

/**
 * 版本更新验证
 * Class VersionUpdateValidate
 * @package app\tenantapi\validate\exam
 */
class TenantVersionUpdateValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|checkVersionUpdate',
        'version' => 'require|regex:/^\d+\.\d+\.\d+$/|length:1,20',
        'info' => 'require|length:1,500',
        'status' => 'require|in:0,1',
    ];

    protected $message = [
        'id.require' => 'id不能为空',
        'version.require' => '版本号不能为空',
        'version.regex' => '版本号格式错误，应为x.y.z',
        'version.length' => '版本号长度须在1-20位字符',
        'info.require' => '更新内容不能为空',
        'info.length' => '更新内容长度须在1-500位字符',
        'status.require' => '状态不能为空',
        'status.in' => '状态值错误',
    ];

    /**
     * @notes  添加场景
     * @return TenantVersionUpdateValidate
     * @author heshihu
     * @date 2022/2/22 9:57
     */
    public function sceneAdd()
    {
        return $this->only(['version', 'info', 'status']);
    }

    /**
     * @notes  详情场景
     * @return TenantVersionUpdateValidate
     * @author heshihu
     * @date 2022/2/22 10:15
     */
    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    /**
     * @notes  更改状态场景
     * @return TenantVersionUpdateValidate
     * @author heshihu
     * @date 2022/2/22 10:18
     */
    public function sceneStatus()
    {
        return $this->only(['id', 'status']);
    }

    /**
     * @notes  编辑场景
     * @return TenantVersionUpdateValidate
     * @author heshihu
     * @date 2022/2/22 10:12
     */
    public function sceneEdit()
    {
        return $this->only(['id', 'version', 'info', 'status']);
    }

    /**
     * @notes  删除场景
     * @return TenantVersionUpdateValidate
     * @author heshihu
     * @date 2022/2/22 10:17
     */
    public function sceneDelete()
    {
        return $this->only(['id']);
    }

    /**
     * @notes  检查指定版本更新是否存在
     * @param $value
     * @return bool|string
     * @author heshihu
     * @date 2022/2/22 10:11
     */
    public function checkVersionUpdate($value)
    {
        $versionUpdate = TenantVersionUpdate::findOrEmpty($value);
        if ($versionUpdate->isEmpty()) {
            return '版本更新不存在';
        }
        return true;
    }

}