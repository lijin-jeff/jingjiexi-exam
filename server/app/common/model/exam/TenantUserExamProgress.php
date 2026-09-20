<?php
// +----------------------------------------------------------------------
// | 精解析答题开发团队介绍
// +----------------------------------------------------------------------
// | 欢迎你使用本套系统，本套系统由精解析答题开发团队全力开发。
// | 如果本套系统是商业系统，请严格遵守系统使用相关协议，出现违背协议的法律行为，所有违法行为均与精解析答题无关。

// | 精解析答题团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | 作者: 精解析答题开发团队
// +----------------------------------------------------------------------
namespace app\common\model\exam;

use app\common\model\BaseModel;

/**
 * 用户答题进度模型
 * Class TenantUserExamProgress
 * @package app\common\model\exam
 */
class TenantUserExamProgress extends BaseModel
{
    protected $name = 'tenant_user_exam_progress';

    /**
     * 进度数据访问器 - 自动解析JSON
     * @param mixed $value
     * @return array
     */
    public function getProgressDataAttr(mixed $value): array
    {
        if (empty($value)) {
            return [];
        }
        
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        
        return is_array($value) ? $value : [];
    }

    /**
     * 进度数据修改器 - 自动转换为JSON
     * @param mixed $value
     * @return string
     */
    public function setProgressDataAttr(mixed $value): string
    {
        if (empty($value)) {
            return '{}';
        }
        
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        
        return is_string($value) ? $value : '{}';
    }

    /**
     * 时间戳访问器 - 统一为13位毫秒时间戳
     * @param mixed $value
     * @return int
     */
    public function getUpdatedAtAttr(mixed $value): int
    {
        if (empty($value)) {
            return 0;
        }
        
        // 如果是10位时间戳，转换为13位
        if (is_numeric($value) && $value < 10000000000) {
            return $value * 1000;
        }
        
        return (int)$value;
    }

    /**
     * 时间戳访问器 - 统一为13位毫秒时间戳
     * @param mixed $value
     * @return int
     */
    public function getCreatedAtAttr(mixed $value): int
    {
        if (empty($value)) {
            return 0;
        }
        
        // 如果是10位时间戳，转换为13位
        if (is_numeric($value) && $value < 10000000000) {
            return $value * 1000;
        }
        
        return (int)$value;
    }
}
