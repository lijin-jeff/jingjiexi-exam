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
use app\common\model\exam\Help;

/**
 * 帮助中心验证
 * Class HelpValidate
 * @package app\tenantapi\validate\exam
 */
class HelpValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|checkHelp',
        'title' => 'require|length:1,255',
        'is_show' => 'require|in:0,1',
    ];

    protected $message = [
        'id.require' => 'id不能为空',
        'title.require' => '标题不能为空',
        'title.length' => '标题长度须在1-255位字符',
    ];

    /**
     * @notes  添加场景
     * @return HelpValidate
     * @author heshihu
     * @date 2022/2/22 9:57
     */
    public function sceneAdd()
    {
        return $this->remove(['id'])
            ->remove('id','require|checkHelp');
    }

    /**
     * @notes  详情场景
     * @return HelpValidate
     * @author heshihu
     * @date 2022/2/22 10:15
     */
    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    /**
     * @notes  更改状态场景
     * @return HelpValidate
     * @author heshihu
     * @date 2022/2/22 10:18
     */
    public function sceneStatus()
    {
        return $this->only(['id', 'is_show']);
    }

    public function sceneEdit()
    {
    }

    /**
     * @notes  删除场景
     * @return HelpValidate 
     * @author heshihu
     * @date 2022/2/22 10:17
     */
    public function sceneDelete()
    {
        return $this->only(['id']);
    }

    /**
     * @notes  检查指定帮助中心是否存在
     * @param $value
     * @return bool|string
     * @author heshihu
     * @date 2022/2/22 10:11
     */
    public function checkHelp($value)
    {
        $help = Help::findOrEmpty($value);
        if ($help->isEmpty()) {
            return '帮助中心不存在';
        }
        return true;
    }

}