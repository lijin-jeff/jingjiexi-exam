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

namespace app\tenantapi\logic\exam;


use app\common\model\exam\TenantExamActivationCode;
use app\common\logic\BaseLogic;
use think\facade\Db;


/**
 * 激活码逻辑
 * Class TenantExamActivationCodeLogic
 * @package app\tenantapi\logic\exam
 */
class TenantExamActivationCodeLogic extends BaseLogic
{
    //修改激活码状态
    public static function updateStatus($params){
        return TenantExamActivationCode::where('id',$params['id'])->update(['status'=>$params['status']]);
    }
}