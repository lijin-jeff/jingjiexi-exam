<?php
/*
 * Copyright (c) 2024-2025 精解析答题系统
 * 用户章节统计模型
 */

declare(strict_types=1);

namespace app\common\model\exam;

use app\common\model\BaseModel;
use think\model\concern\SoftDelete;

/**
 * 用户章节做题统计模型
 * @author 精解析答题
 * @date 2025/12/24
 */
class TenantUserChapterStatistics extends BaseModel
{
    use SoftDelete;

    // 表名
    protected $name = 'tenant_user_chapter_statistics';
    
    // 主键
    protected $pk = 'id';
    
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
    
    // 创建时间字段
    protected $createTime = 'create_time';
    
    // 更新时间字段
    protected $updateTime = 'update_time';
    
    // 软删除字段
    protected $deleteTime = 'delete_time';
    
    // 默认软删除字段内容
    protected $defaultSoftDelete = NULL;

    /**
     * 获取器 - 格式化正确率显示
     * @param $value
     * @return string
     */
    public function getAccuracyRateAttr($value): string
    {
        return number_format((float)$value, 2);
    }

    /**
     * 关联用户表
     * @return \think\model\relation\HasOne
     */
    public function user()
    {
        return $this->hasOne('app\common\model\user\User', 'uid', 'user_uid');
    }

    /**
     * 关联章节表
     * @return \think\model\relation\HasOne
     */
    public function chapter()
    {
        return $this->hasOne('app\common\model\exam\TenantExamChapter', 'uid', 'chapter_uid');
    }

    /**
     * 关联题库表
     * @return \think\model\relation\HasOne
     */
    public function library()
    {
        return $this->hasOne('app\common\model\exam\TenantExamLibrary', 'uid', 'library_uid');
    }

    /**
     * 搜索器 - 按用户UID搜索
     * @param $query
     * @param $value
     */
    public function searchUserUidAttr($query, $value)
    {
        if (!empty($value)) {
            $query->where('user_uid', '=', $value);
        }
    }

    /**
     * 搜索器 - 按章节UID搜索
     * @param $query
     * @param $value
     */
    public function searchChapterUidAttr($query, $value)
    {
        if (!empty($value)) {
            $query->where('chapter_uid', '=', $value);
        }
    }

    /**
     * 搜索器 - 按题库UID搜索
     * @param $query
     * @param $value
     */
    public function searchLibraryUidAttr($query, $value)
    {
        if (!empty($value)) {
            $query->where('library_uid', '=', $value);
        }
    }

    /**
     * 搜索器 - 按租户ID搜索
     * @param $query
     * @param $value
     */
    public function searchTenantIdAttr($query, $value)
    {
        if (!empty($value)) {
            $query->where('tenant_id', '=', $value);
        }
    }
}
