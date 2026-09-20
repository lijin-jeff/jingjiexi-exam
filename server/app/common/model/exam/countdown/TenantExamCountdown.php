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

namespace app\common\model\exam\countdown;


use app\common\model\BaseModel;

/**
 * 倒计时模型
 * Class TenantExamCountdown
 * @package app\common\model\exam\countdown
 */
class TenantExamCountdown extends BaseModel
{
    // 设置表名
    protected $name = 'tenant_exam_countdown';
    
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
    
    // 时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    
    /**
     * 计算倒计时天数
     * @param string $targetDate
     * @return int
     */
    public static function calculateDays($targetDate)
    {
        $targetTimestamp = strtotime($targetDate);
        $nowTimestamp = time();
        
        if ($targetTimestamp <= $nowTimestamp) {
            return 0;
        }
        
        $diffSeconds = $targetTimestamp - $nowTimestamp;
        return ceil($diffSeconds / (24 * 60 * 60));
    }
}