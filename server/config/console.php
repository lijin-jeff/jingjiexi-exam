<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------
return [
    // 指令定义
    'commands' => [
        // 定时任务
        'crontab' => 'app\common\command\Crontab',
        // 退款查询
        'query_refund' => 'app\common\command\QueryRefund',
        // 排行榜刷新
        'ranking:refresh' => 'app\common\command\RankingRefresh',
        // 会员到期提醒
        'message:vip-remind' => 'app\common\command\MessageVipRemind',
        // 每周学习总结
        'message:weekly-summary' => 'app\common\command\MessageWeeklySummary',
    ],
];
