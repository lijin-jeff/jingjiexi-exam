<?php
// 测试日志写入
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../app/provider.php';

// 写入测试日志
\think\facade\Log::info('=== 测试日志写入 ===', [
    'time' => date('Y-m-d H:i:s'),
    'test' => 'log_test'
]);

\think\facade\Log::error('测试错误日志', [
    'error' => 'test_error'
]);

\think\facade\Log::warning('测试警告日志', [
    'warning' => 'test_warning'
]);

echo "日志写入测试完成！<br>";
echo "请检查 runtime/log/" . date('Ym') . "/" . date('d') . "_info.log 文件<br>";
echo "请检查 runtime/log/" . date('Ym') . "/" . date('d') . "_error.log 文件<br>";
echo "请检查 runtime/log/" . date('Ym') . "/" . date('d') . "_warning.log 文件<br>";
