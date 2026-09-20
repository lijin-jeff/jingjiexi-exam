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

use think\facade\Route;

// 倒计时相关路由
Route::group('countdown', function() {
    // 倒计时管理
    Route::group('/', function() {
        // 获取倒计时列表
        Route::get('lists', 'api/countdown/Countdown/lists');
        
        // 获取倒计时详情
        Route::get('detail', 'api/countdown/Countdown/detail');
        
        // 创建倒计时
        Route::post('create', 'api/countdown/Countdown/create');
        
        // 更新倒计时
        Route::post('update', 'api/countdown/Countdown/update');
        
        // 删除倒计时
        Route::post('delete', 'api/countdown/Countdown/delete');
        
        // 关注/取消关注倒计时
        Route::post('follow', 'api/countdown/Countdown/follow');
        
        // 获取用户关注的倒计时列表
        Route::get('followedList', 'api/countdown/Countdown/followedList');
        
        // 获取用户创建的倒计时列表
        Route::get('createdList', 'api/countdown/Countdown/createdList');
    });
    
    // 名人名言管理
    Route::group('celebrity-quote', function() {
        // 获取随机名人名言
        Route::get('random', 'api/countdown/CelebrityQuote/random');
        
        // 获取名人名言列表
        Route::get('lists', 'api/countdown/CelebrityQuote/lists');
    });
});