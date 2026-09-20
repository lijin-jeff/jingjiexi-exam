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
 * 名人名言模型
 * Class TenantExamCelebrityQuote
 * @package app\common\model\exam\countdown
 */
class TenantExamCelebrityQuote extends BaseModel
{
    // 设置表名
    protected $name = 'tenant_exam_celebrity_quote';
    
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
    
    // 时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    /**
     * 获取随机名人名言
     * @param array $where
     * @return array
     */
    public static function getRandomQuote($where = [])
    {
        // 首先获取所有符合条件的名言ID列表
        $ids = self::where($where)->column('id');
        
        if (empty($ids)) {
            // 没有数据时返回默认名言
            return [
                'content' => '时间就像海绵里的水，只要愿挤，总还是有的',
                'author' => '鲁迅'
            ];
        }
        
        // 从ID列表中随机选择一个ID
        $randomId = $ids[array_rand($ids)];
        
        // 根据随机ID获取名言
        $quote = self::find($randomId)->toArray();
        
        return $quote;
    }
    
    /**
     * 获取名人名言列表
     * @param array $where
     * @param int $page
     * @param int $limit
     * @param array $order
     * @return array
     */
    public static function getQuoteList($where = [], $page = 1, $limit = 10, $order = [])
    {
        $count = self::where($where)->count();
        $query = self::where($where);
        
        // 使用传入的排序参数，如果没有则使用默认排序
        if (!empty($order)) {
            $query->order($order);
        } else {
            $query->order('id desc');
        }
        
        $list = $query
            ->page($page, $limit)
            ->select()
            ->toArray();
        
        return [
            'count' => $count,
            'list' => $list
        ];
    }
}