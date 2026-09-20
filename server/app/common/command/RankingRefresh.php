<?php
declare(strict_types=1);
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 访问官网：精解析答题
// | 官方邮箱：精解析答题
// | 精解析答题系统开发者版权所有，拥有最终解释权。
// +----------------------------------------------------------------------

namespace app\common\command;

use app\api\logic\exam\RankLogic;
use think\console\Command;
use think\console\Input;
use think\console\Output;

/**
 * 排行榜定时刷新命令
 * 每日00:00:00自动执行，更新日榜、周榜、月榜、总榜
 * Class RankingRefresh
 * @package app\common\command
 */
class RankingRefresh extends Command
{
    protected function configure()
    {
        $this->setName('ranking:refresh')
            ->setDescription('刷新排行榜数据（日榜/周榜/月榜/总榜）');
    }

    /**
     * @notes 执行命令
     * @param Input $input
     * @param Output $output
     * @return int
     * @author 精解析答题
     * @date 2025/12/20
     */
    protected function execute(Input $input, Output $output)
    {
        // 检查执行锁，防止重复执行
        $lockFile = runtime_path() . 'ranking_refresh.lock';
        
        if (file_exists($lockFile)) {
            $lockTime = filemtime($lockFile);
            // 如果锁文件存在且在1小时内，说明正在执行或异常未清理
            if (time() - $lockTime < 3600) {
                $output->warning('⚠ 排行榜刷新任务正在执行中，跳过本次执行');
                return 0;
            }
            // 超过1小时的锁文件视为异常，删除后继续执行
            @unlink($lockFile);
        }
        
        // 创建锁文件
        file_put_contents($lockFile, date('Y-m-d H:i:s'));
        
        $startTime = microtime(true);
        $output->writeln('========== 开始刷新排行榜 ==========');
        $output->writeln('执行时间: ' . date('Y-m-d H:i:s'));

        try {
            $rankLogic = new RankLogic();
            $result = $rankLogic->dailyRefreshAllRankings();

            if ($result) {
                $endTime = microtime(true);
                $useTime = round($endTime - $startTime, 2);
                
                $output->writeln('========== 刷新完成 ==========');
                $output->writeln("执行耗时: {$useTime}秒");
                $output->info('✓ 排行榜刷新成功');
                
                // 删除锁文件
                @unlink($lockFile);
                return 0; // 成功
            } else {
                $error = $rankLogic->getError();
                $output->error('✗ 排行榜刷新失败: ' . $error);
                
                // 删除锁文件
                @unlink($lockFile);
                return 1; // 失败
            }
        } catch (\Exception $e) {
            $output->error('✗ 执行异常: ' . $e->getMessage());
            $output->writeln('错误位置: ' . $e->getFile() . ' Line:' . $e->getLine());
            
            // 删除锁文件
            @unlink($lockFile);
            return 1; // 异常
        }
    }
}
