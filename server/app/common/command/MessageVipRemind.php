<?php
declare(strict_types=1);

namespace app\common\command;

use app\common\logic\user\MessageService;
use app\common\model\user\User;
use think\console\Command;
use think\console\Input;
use think\console\Output;

/**
 * 会员到期提醒定时任务
 * 每日检查即将到期的会员，提前3天、1天发送提醒
 * Class MessageVipRemind
 * @package app\common\command
 */
class MessageVipRemind extends Command
{
    protected function configure()
    {
        $this->setName('message:vip-remind')
            ->setDescription('会员到期提醒（提前3天、1天通知）');
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
        $lockFile = runtime_path() . 'message_vip_remind.lock';
        
        if (file_exists($lockFile)) {
            $lockTime = filemtime($lockFile);
            // 如果锁文件存在且在30分钟内，跳过执行
            if (time() - $lockTime < 1800) {
                $output->warning('⚠ 会员到期提醒任务正在执行中，跳过本次执行');
                return 0;
            }
            // 超过30分钟的锁文件视为异常，删除后继续执行
            @unlink($lockFile);
        }
        
        // 创建锁文件
        file_put_contents($lockFile, date('Y-m-d H:i:s'));
        
        $startTime = microtime(true);
        $output->writeln('========== 开始检查会员到期情况 ==========');
        $output->writeln('执行时间: ' . date('Y-m-d H:i:s'));

        try {
            $totalReminded = 0;
            
            // 提前3天提醒
            $threeDaysLater = strtotime('+3 days', strtotime(date('Y-m-d 00:00:00')));
            $threeDaysEnd = strtotime('+3 days', strtotime(date('Y-m-d 23:59:59')));
            
            $users3Days = User::where('vip_state', 3)
                ->where('vip_endTime', '>=', $threeDaysLater)
                ->where('vip_endTime', '<=', $threeDaysEnd)
                ->field('id,nickname,vip_endTime')
                ->select();
            
            foreach ($users3Days as $user) {
                MessageService::vipExpireRemind($user['id'], 3);
                $totalReminded++;
            }
            
            $output->writeln("✓ 提前3天提醒：发送 {$users3Days->count()} 条通知");
            
            // 提前1天提醒
            $oneDayLater = strtotime('+1 day', strtotime(date('Y-m-d 00:00:00')));
            $oneDayEnd = strtotime('+1 day', strtotime(date('Y-m-d 23:59:59')));
            
            $users1Day = User::where('vip_state', 3)
                ->where('vip_endTime', '>=', $oneDayLater)
                ->where('vip_endTime', '<=', $oneDayEnd)
                ->field('id,nickname,vip_endTime')
                ->select();
            
            foreach ($users1Day as $user) {
                MessageService::vipExpireRemind($user['id'], 1);
                $totalReminded++;
            }
            
            $output->writeln("✓ 提前1天提醒：发送 {$users1Day->count()} 条通知");
            
            // 检查过期会员并更新状态
            $expiredCount = $this->updateExpiredVipStatus($output);
            
            $endTime = microtime(true);
            $useTime = round($endTime - $startTime, 2);
            
            $output->writeln('========== 检查完成 ==========');
            $output->writeln("总共发送提醒: {$totalReminded} 条");
            $output->writeln("更新过期状态: {$expiredCount} 个用户");
            $output->writeln("执行耗时: {$useTime}秒");
            $output->info('✓ 会员到期提醒任务执行成功');
            
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
    
    /**
     * 更新过期会员状态
     * @param Output $output
     * @return int 更新数量
     */
    private function updateExpiredVipStatus(Output $output): int
    {
        $now = time();
        
        // 查找已过期但状态仍为3的会员
        $expiredUsers = User::where('vip_state', 3)
            ->where('vip_endTime', '<', $now)
            ->field('id,nickname,vip_endTime')
            ->select();
        
        $count = 0;
        foreach ($expiredUsers as $user) {
            // 更新为过期状态
            User::where('id', $user['id'])->update(['vip_state' => 2]);
            $count++;
        }
        
        if ($count > 0) {
            $output->writeln("✓ 更新过期会员状态：{$count} 个用户");
        }
        
        return $count;
    }
}
