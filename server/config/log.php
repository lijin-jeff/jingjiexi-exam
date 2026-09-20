<?php

// +----------------------------------------------------------------------
// | 日志设置
// +----------------------------------------------------------------------
return [
    // 默认日志记录通道
    'default'      => env('log.channel', 'file'),
    // 日志记录级别（空数组表示记录所有级别）
    'level'        => ['info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency', 'sql', 'debug'],
    // 日志类型记录的通道 ['error'=>'email',...]
    'type_channel' => [],
    // 关闭全局日志写入
    'close'        => false,
    // 全局日志处理 支持闭包
    'processor'    => null,

    // 日志通道列表
    'channels'     => [
        'file' => [
            // 日志记录方式
            'type'           => 'File',
            // 日志保存目录
            'path'           => '',
            // 单文件日志写入
            'single'         => false,
            // 独立日志级别
            'apart_level'    => ['error', 'warning', 'notice', 'info', 'sql'],
            // 最大日志文件数量
            'max_files'      => 0,
            // 使用JSON格式记录
            'json'           => false,
            // 日志处理
            'processor'      => null,
            // 关闭通道日志写入
            'close'          => false,
            // 日志输出格式化
            'format'         => '[%s][%s] %s %s',
            // 是否实时写入（开启后立即写入文件）
            'realtime_write' => true,
            // 日志文件权限（0777 表示所有用户可读写执行，不推荐生产环境使用）
            'file_permission' => 0777,
        ],
        // 其它日志通道配置
    ],

];
