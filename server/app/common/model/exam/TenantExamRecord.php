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
use think\model\concern\SoftDelete;


/**
 * 做题记录表模型
 * Class TenantExamRecord
 * @package app\common\model\exam
 */
class TenantExamRecord extends BaseModel
{
    use SoftDelete;
    protected $name = 'tenant_exam_mock_examination';
    protected $deleteTime = 'delete_time';

    /**
     * @notes 关联用户uid
     * @return \think\model\relation\HasOne
     * @author 精解析答题
     * @date 2025/07/19 14:47
     */
    public function user()
    {
        return $this->hasOne(\app\common\model\user\User::class, 'id', 'user_uid');
    }

    /**
     * @notes 关联题库试题uid
     * @return \think\model\relation\HasOne
     * @author 精解析答题
     * @date 2025/07/19 14:47
     */
    public function question()
    {
        return $this->hasOne(\app\common\model\exam\TenantExamQuestion::class, 'uid', 'question_uid');
    }

    /**
     * @notes 关联题库uid
     * @return \think\model\relation\HasOne
     * @author 精解析答题
     * @date 2025/07/19 14:47
     */
    public function library()
    {
        return $this->hasOne(\app\common\model\exam\TenantExamLibrary::class, 'uid', 'library_uid');
    }

}