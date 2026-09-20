<?php
/**
 * 修复 runtime 目录权限
 * 访问此文件会自动设置 runtime 目录的权限为 777
 */

$runtimePath = dirname(__DIR__) . '/runtime';

function setPermissions($dir) {
    if (!is_dir($dir)) {
        return false;
    }
    
    // 设置目录权限为 777
    @chmod($dir, 0777);
    // 修改目录所有者为 www (如果系统支持)
    if (function_exists('chown') && function_exists('chgrp')) {
        @chown($dir, 'www');
        @chgrp($dir, 'www');
    }
    
    // 递归处理子目录
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            setPermissions($path);
        } else {
            @chmod($path, 0666);
            // 修改文件所有者为 www (如果系统支持)
            if (function_exists('chown') && function_exists('chgrp')) {
                @chown($path, 'www');
                @chgrp($path, 'www');
            }
        }
    }
    
    return true;
}

echo "<h2>开始修复 runtime 目录权限...</h2>";
echo "<p>目录路径: {$runtimePath}</p>";

if (setPermissions($runtimePath)) {
    echo "<p style='color: green;'><strong>✓ 权限修复成功！</strong></p>";
    echo "<p>已设置：</p>";
    echo "<ul>";
    echo "<li>目录权限：777（rwxrwxrwx）</li>";
    echo "<li>文件权限：666（rw-rw-rw-）</li>";
    echo "</ul>";
} else {
    echo "<p style='color: red;'><strong>✗ 权限修复失败！</strong></p>";
    echo "<p>请手动设置权限：</p>";
    echo "<pre>chmod -R 777 {$runtimePath}</pre>";
}

echo "<hr>";
echo "<h3>测试日志写入</h3>";

// 创建测试日志目录
$logDir = $runtimePath . '/log/' . date('Ym');
if (!is_dir($logDir)) {
    @mkdir($logDir, 0777, true);
}

$testFile = $logDir . '/' . date('d') . '_test.log';
$testContent = date('Y-m-d H:i:s') . " - 权限测试日志\n";

if (@file_put_contents($testFile, $testContent, FILE_APPEND)) {
    echo "<p style='color: green;'><strong>✓ 日志写入测试成功！</strong></p>";
    echo "<p>测试文件: {$testFile}</p>";
} else {
    echo "<p style='color: red;'><strong>✗ 日志写入测试失败！</strong></p>";
    echo "<p>错误信息: " . error_get_last()['message'] . "</p>";
}

echo "<hr>";
echo "<p><a href='javascript:history.back()'>返回</a></p>";
