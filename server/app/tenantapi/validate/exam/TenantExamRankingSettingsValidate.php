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
 * rankingSettings验证器
 * Class TenantExamRankingSettingsValidate
 * @package app\tenantapi\validate\exam
 */
class TenantExamRankingSettingsValidate extends BaseValidate
{

     /**
      * 设置校验规则
      * @var string[]
      */
    protected $rule = [
        'id' => 'number',
        'ranking_type' => 'require|array|min:1',
        'ranking_dimension' => 'require|min:1',
        'display_top_count' => 'require|number|between:10,100',
        'reset_time_day' => 'require',
        'reset_time_week' => 'require',
        'reset_time_month' => 'require',
        'reset_time_total' => 'require',
        'reward_rule' => 'require',
        'is_show' => 'require|in:0,1',
    ];


    /**
     * 参数描述
     * @var string[]
     */
    protected $field = [
        'id' => 'ID',
        'ranking_type' => '排行榜类型',
        'ranking_dimension' => '显示维度',
        'display_top_count' => '展示数量',
        'reset_time_day' => '日榜重置时间',
        'reset_time_week' => '周榜重置时间',
        'reset_time_month' => '月榜重置时间',
        'reset_time_total' => '总榜重置时间',   
        'reward_rule' => '奖励规则',
        'is_show' => '是否启用',
    ];


    /**
     * @notes 添加场景
     * @return TenantExamRankingSettingsValidate
     * @author 精解析答题
     * @date 2025/08/24 13:16
     */
    public function sceneAdd()
    {
        return $this->only(['ranking_type','ranking_dimension','display_top_count','reset_time_day','reset_time_week','reset_time_month','reset_time_total','reward_rule','is_show']);
    }


    /**
     * @notes 编辑场景
     * @return TenantExamRankingSettingsValidate
     * @author 精解析答题
     * @date 2025/08/24 13:16
     */
    public function sceneEdit()
    {
        return $this->only(['id','ranking_type','ranking_dimension','display_top_count','reset_time_day','reset_time_week','reset_time_month','reset_time_total','reward_rule','is_show']);
    }


    /**
     * @notes 删除场景
     * @return TenantExamRankingSettingsValidate
     * @author 精解析答题
     * @date 2025/08/24 13:16
     */
    public function sceneDelete()
    {
        return $this->only(['id'])->append(['id' => 'require|number']);
    }

}