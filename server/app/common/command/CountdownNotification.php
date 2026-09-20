<?php
declare(strict_types=1);

namespace app\common\command;

use app\api\logic\SubscribeLogic;
use app\common\model\user\UserSubscribe;
use app\common\model\exam\countdown\TenantExamCountdown;
use think\console\Command;
use think\console\Input;
use think\console\Output;

/**
 * 倒计时提醒定时任务
 * 检查倒计时并在指定时间发送提醒
 * Class CountdownNotification
 * @package app\common\command
 */
class CountdownNotification extends Command
{
    protected function configure()
    {
        $this->setName('countdown:notification')
            ->setDescription('倒计时提醒通知（每天早上8:30发送第二天的倒计时提醒）');
    }

    /**
     * @notes 执行命令
     * @param Input $input
     * @param Output $output
     * @return int
     */
    protected function execute(Input $input, Output $output)
    {
        // 检查执行锁，防止重复执行
        $lockFile = runtime_path() . 'countdown_notification.lock';
        
        if (file_exists($lockFile)) {
            $lockTime = filemtime($lockFile);
            // 如果锁文件存在且在30分钟内，跳过执行
            if (time() - $lockTime < 1800) {
                $output->warning('⚠ 倒计时提醒任务正在执行中，跳过本次执行');
                return 0;
            }
            // 超过30分钟的锁文件视为异常，删除后继续执行
            @unlink($lockFile);
        }
        
        // 创建锁文件
        file_put_contents($lockFile, date('Y-m-d H:i:s'));
        
        $startTime = microtime(true);
        $output->writeln('========== 开始检查倒计时提醒情况 ==========');
        $output->writeln('执行时间: ' . date('Y-m-d H:i:s'));

        try {
            $totalNotified = 0;
            
            // 获取明天的倒计时（目标日期为明天，因为我们要在当天早上8:30提醒用户明天的倒计时）
            $tomorrow = strtotime(date('Y-m-d', strtotime('+1 day')));
            $tomorrowStr = date('Y-m-d', $tomorrow);
            
            $tomorrowCountdowns = TenantExamCountdown::where('target_date', $tomorrowStr)
                ->where('status', 1) // 已发布
                ->field('id, title, description, target_date')
                ->select();

            foreach ($tomorrowCountdowns as $countdown) {
                // 获取订阅了此倒计时的用户
                $subscribers = UserSubscribe::where([
                    'type' => 'countdown',
                    'related_id' => $countdown['id'],
                    'is_pushed' => 0, // 未推送
                    'subscribe_status' => 1 // 已订阅
                ])->select();
                
                foreach ($subscribers as $subscribe) {
                    // 使用数据库锁确保并发安全
                    $lockedSubscribe = UserSubscribe::where('id', $subscribe['id'])->lock(true)->find();
                    
                    // 检查是否已经被其他进程处理
                    if (!$lockedSubscribe || $lockedSubscribe->is_pushed != 0) {
                        continue; // 跳过已被处理的订阅
                    }
                    
                    // 准备发送参数
                    $params = [
                        'user_id' => $lockedSubscribe['user_id'],
                        'template_id' => $lockedSubscribe['template_id'],
                        'type' => 'countdown',
                        'related_id' => $countdown['id'],
                        'message_data' => [
                            'thing1' => ['value' => $countdown['title']],
                            'thing2' => ['value' => $countdown['description'] ?? '倒计时提醒'],
                            'time3' => ['value' => is_numeric($countdown['target_date']) ? date('Y-m-d', $countdown['target_date']) : $countdown['target_date']]
                        ]
                    ];
                    
                    // 发送订阅消息
                    $result = SubscribeLogic::sendOneTimeSubscribeMsg($params);
                    $resultData = json_decode($result->getContent(), true);
                    
                    if ($resultData['code'] === 0) {
                        $totalNotified++;
                        $output->writeln("✓ 倒计时提醒发送成功: {$countdown['title']} (用户ID: {$lockedSubscribe['user_id']})");
                        
                        // 更新推送状态
                        $lockedSubscribe->is_pushed = 1;
                        $lockedSubscribe->save();
                    } else {
                        $output->writeln("✗ 倒计时提醒发送失败: {$countdown['title']} (用户ID: {$lockedSubscribe['user_id']}) - {$resultData['msg']}");
                    }
                }
            }
            
            // 获取推送统计信息
            $stats = \app\api\logic\SubscribeLogic::getPushStats('countdown', 1);
            
            $endTime = microtime(true);
            $useTime = round($endTime - $startTime, 2);
            
            $output->writeln('========== 检查完成 ==========');
            $output->writeln("总共发送提醒: {$totalNotified} 条");
            $output->writeln("推送统计 - 总数: {$stats['total']}, 成功: {$stats['success']}, 失败: {$stats['failure']}, 成功率: {$stats['success_rate']}%");
            $output->writeln("执行耗时: {$useTime}秒");
            $output->info('✓ 倒计时提醒任务执行成功');
            
            // 删除锁文件
            @unlink($lockFile);
            return 0; // 成功
            
        } catch (\Exception $e) {
            $output->error('✗ 执行异常: ' . $e->getMessage());
            $output->writeln('错误位置: ' . $e->getFile() . ' Line:' . $e->getLine());
            
            // 删除锁文件
            @unlink($lockFile);
            return 1; // 异常
        }
    }
}