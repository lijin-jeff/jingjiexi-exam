<?php
// +----------------------------------------------------------------------
// | likeadmin快速开发前后端分离管理后台（PHP版）
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// +----------------------------------------------------------------------
// | 开源版本可自由商用，可去除界面版权logo
// +----------------------------------------------------------------------
// | gitee下载：https://gitee.com/likeshop_gitee/likeadmin
// | github下载：https://github.com/likeshop-github/likeadmin
// | 访问官网：https://www.likeadmin.cn
// | likeadmin团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeadminTeam
// +----------------------------------------------------------------------

namespace app\tenantapi\validate\exam\countdown;

use app\common\validate\BaseValidate;
use app\common\model\exam\countdown\TenantExamCountdown;

/**
 * 倒计时验证器
 * Class CountdownValidate
 * @package app\tenantapi\validate\exam\countdown
 */
class CountdownValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|checkCountdown',
        'title' => 'require|length:1,255',
        'target_date' => 'require|date',
        'status' => 'require|in:0,1',
        'sort' => 'number',
    ];

    protected $message = [
        'id.require' => 'id不能为空',
        'title.require' => '标题不能为空',
        'title.length' => '标题长度须在1-255位字符',
        'target_date.require' => '目标日期不能为空',
        'target_date.date' => '目标日期格式不正确',
        'status.require' => '状态不能为空',
        'status.in' => '状态值不合法',
        'sort.number' => '排序必须为数字',
    ];

    /**
     * @notes  添加场景
     * @return CountdownValidate
     * @author likeadmin
     * @date 2024/01/08
     */
    public function sceneAdd()
    {
        return $this->only(['title', 'target_date', 'description', 'status', 'sort']);
    }

    /**
     * @notes  详情场景
     * @return CountdownValidate
     * @author likeadmin
     * @date 2024/01/08
     */
    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    /**
     * @notes  更改状态场景
     * @return CountdownValidate
     * @author likeadmin
     * @date 2024/01/08
     */
    public function sceneStatus()
    {
        return $this->only(['id', 'status']);
    }

    /**
     * @notes  编辑场景
     * @return CountdownValidate
     * @author likeadmin
     * @date 2024/01/08
     */
    public function sceneEdit()
    {
        return $this->only(['id', 'title', 'target_date', 'description', 'status', 'sort']);
    }

    /**
     * @notes  删除场景
     * @return CountdownValidate
     * @author likeadmin
     * @date 2024/01/08
     */
    public function sceneDelete()
    {
        return $this->only(['id']);
    }

    /**
     * @notes  检查指定倒计时是否存在
     * @param $value
     * @return bool|string
     * @author likeadmin
     * @date 2024/01/08
     */
    public function checkCountdown($value)
    {
        $countdown = TenantExamCountdown::findOrEmpty($value);
        if ($countdown->isEmpty()) {
            return '倒计时不存在';
        }
        return true;
    }
}
