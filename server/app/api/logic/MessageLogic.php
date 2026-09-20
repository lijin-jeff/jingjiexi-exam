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

namespace app\api\logic;

use app\common\logic\BaseLogic;
use app\common\model\user\UserMessage;

/**
 * 消息逻辑
 * Class MessageLogic
 * @package app\api\logic
 */
class MessageLogic extends BaseLogic
{

    public static function lists($params)
    {
       try {
            $type = $params['type'] ?? null; // 消息类型
            $pageNo = $params['page_no'] ?? 1;
            $pageSize = $params['page_size'] ?? 20;
            $userId = $params['user_id'] ?? null;
            $query = UserMessage::where('user_id', $userId)
                ->where('delete_time', null)
                ->order('create_time', 'desc');

            // 按类型筛选（将type转为整数）
            if ($type !== null && $type !== '') {
                $typeInt = (int)$type;  // 强制转换为整数
                $query->where('type', $typeInt);
                \think\facade\Log::info('添加类型筛选', ['type' => $type, 'type_int' => $typeInt]);
            }

            // 先获取总数，再分页查询
            $count = $query->count();
            $lists = $query->page($pageNo, $pageSize)->select()->toArray();


            // 格式化数据
            foreach ($lists as &$item) {
                // extra字段已由模型获取器自动解析，无需重复处理
                // 只需确保它是数组格式
                if (!is_array($item['extra'])) {
                    $item['extra'] = [];
                }
                
                // 格式化时间（处理字符串和整数两种格式）
                $createTime = $item['create_time'];
                // 如果是字符串格式，转为时间戳
                if (is_string($createTime)) {
                    $createTime = strtotime($createTime);
                } else {
                    $createTime = (int)$createTime;
                }
                // 如果时间戳为0或转换失败，使用当前时间
                if ($createTime === 0 || $createTime === false) {
                    $createTime = time();
                }
                $item['create_time'] = $createTime;  // 统一转为时间戳格式
                $item['create_time_text'] = date('Y-m-d H:i', $createTime);
                $item['time_ago'] = self::timeAgo($createTime);
            }

            return  [
                'lists' => $lists,
                'count' => $count,
                'page_no' => $pageNo,
                'page_size' => $pageSize,
                'more' => $count > ($pageNo * $pageSize)
            ];
        } catch (\Exception $e) {
            // 记录详细错误信息
            \think\facade\Log::error('获取消息列表失败: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine());
            return [];
        }
    }
   


    /**
     * @notes 时间转换为"几分钟前"格式
     * @param int $time
     * @return string
     */
    private static function timeAgo($time)
    {
        $diff = time() - $time;
        
        if ($diff < 60) {
            return '刚刚';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . '分钟前';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . '小时前';
        } elseif ($diff < 604800) {
            return floor($diff / 86400) . '天前';
        } else {
            return date('Y-m-d', $time);
        }
    }

}