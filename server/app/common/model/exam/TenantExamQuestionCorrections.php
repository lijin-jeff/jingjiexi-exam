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

namespace app\common\model\exam;


use app\common\model\BaseModel;



/**
 * corrections模型
 * Class TenantExamQuestionCorrections
 * @package app\common\model\exam
 */
class TenantExamQuestionCorrections extends BaseModel
{
    
    protected $name = 'tenant_exam_question_corrections';
    

    
    /**
     * @notes 关联question_id
     * @return \think\model\relation\HasOne
     * @author 精解析题库
     * @date 2025/08/05 23:16
     */
    public function question_uid()
    {
        return $this->hasOne(\app\common\model\exam\TenantExamQuestion::class, 'id', 'question_uid');
    }

    /**
     * @notes 关联user_id
     * @return \think\model\relation\HasOne
     * @author 精解析题库
     * @date 2025/08/05 23:16
     */
    public function user_id()
    {
        return $this->hasOne(\app\common\model\user\User::class, 'id', 'user_id');
    }

}