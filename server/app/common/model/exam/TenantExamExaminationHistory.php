<?php
// +----------------------------------------------------------------------
// | 精解析答题开发团队介绍
// +----------------------------------------------------------------------
// | 欢迎你使用本套系统，本套系统由精解析答题开发团队全力开发。
// | 如果本套系统是商业系统，请严格遵守系统使用相关协议，出现违背协议的法律行为，所有违法行为均与精解析答题无关。

// | 精解析答题团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | 作者: 精解析答题开发团队
// +----------------------------------------------------------------------
namespace app\common\model\exam;

use app\common\model\BaseModel;
use think\model\concern\SoftDelete;

class TenantExamExaminationHistory extends BaseModel
{
    use SoftDelete;

    protected $name = 'tenant_exam_examination_history';

    protected $deleteTime = 'delete_time';

    public function examination(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo('app\common\model\exam\TenantExamExamination', 'examination_uid', 'uid');
    }

    /**
     * @notes 关联用户信息
     * @return \think\model\relation\HasOne
     * @author 精解析答题
     * @date 2026/01/01
     */
    public function user(): \think\model\relation\HasOne
    {
        return $this->hasOne('app\common\model\user\User', 'id', 'user_uid')
            ->field('id,nickname,avatar,sn');
    }

    /**
     * @notes 关联题库信息
     * @return \think\model\relation\HasOne
     * @author 精解析答题
     * @date 2026/01/01
     */
    public function library()
    {
        return $this->hasOne('app\common\model\exam\TenantExamLibrary', 'uid', 'examination_uid')
            ->field('uid,title');
    }

    /**
     * @notes 关联试卷信息
     * @return \think\model\relation\HasOne
     * @author 精解析答题
     * @date 2026/01/01
     */
    public function paper()
    {
        return $this->hasOne('app\common\model\exam\TenantExamExamination', 'uid', 'paper_uid')
            ->field('uid,title');
    }

    /**
     * @notes 获取器-考试时间格式化
     * @param $value
     * @return string
     * @author 精解析答题
     * @date 2026/01/01
     */
    public function getCreateTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', substr($value, 0, 10)) : '';
    }

    /**
     * @notes 获取器-提交时间格式化
     * @param $value
     * @return string
     * @author 精解析答题
     * @date 2026/01/01
     */
    public function getSubmitTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', substr($value, 0, 10)) : '';
    }

    /**
     * @notes 获取器-正确率
     * @param $value
     * @return string
     * @author 精解析答题
     * @date 2026/01/01
     */
    public function getAccuracyRateAttr($value)
    {
        return number_format((float)$value, 2);
    }

}