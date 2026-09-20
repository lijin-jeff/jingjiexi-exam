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

namespace app\tenantapi\logic\exam\countdown;

use app\common\logic\BaseLogic;
use app\common\model\exam\countdown\TenantExamCelebrityQuote;
use think\Exception;

/**
 * 名人名言管理逻辑
 * Class CelebrityLogic
 * @package app\tenantapi\logic\exam\countdown
 */
class CelebrityLogic extends BaseLogic
{
    /**
     * 获取名人名言详情
     * @param array $params
     * @return array
     */
    public static function detail(array $params): array
    {
        try {
            $detail = TenantExamCelebrityQuote::findOrEmpty($params['id'])->toArray();
            if (empty($detail)) {
                self::setError('名人名言不存在');
                return [];
            }
            
            return $detail;
        } catch (Exception $e) {
            self::setError($e->getMessage());
            return [];
        }
    }
    
    /**
     * 添加名人名言（支持批量添加）
     * @param array $params
     * @return bool
     */
    public static function add(array $params): bool
    {
        try {
            // 分割内容，支持多行输入，处理各种换行符
            $contents = preg_split('/\r?\n/', $params['content']);
            $contents = array_filter(array_map('trim', $contents));
            
            // 验证输入
            if (empty($contents)) {
                self::setError('请输入名人名言内容');
                return false;
            }
            
            // 准备批量插入数据
            $insertData = [];
            $tenantId = $params['tenant_id'] ?? 0;
            $category = $params['category'] ?? '';
            
            foreach ($contents as $content) {
                if (empty($content)) continue;
                
                // 检查内容长度，确保不超过数据库限制（255字符）
                $content = mb_substr(trim($content), 0, 255, 'UTF-8');
                if (empty($content)) continue;
                
                $insertData[] = [
                    'content' => $content,
                    'category' => $category,
                    'tenant_id' => $tenantId,
                    'create_time' => time()
                ];
            }
            
            // 批量插入
            if (!empty($insertData)) {
                $model = new TenantExamCelebrityQuote();
                // 使用事务确保数据一致性
                $model->startTrans();
                try {
                    $result = $model->insertAll($insertData);
                    
                    if ($result === false) {
                        $model->rollback();
                        self::setError('数据插入失败：' . $model->getError());
                        return false;
                    }
                    
                    $model->commit();
                    return true;
                } catch (Exception $e) {
                    $model->rollback();
                    self::setError('事务处理失败：' . $e->getMessage());
                    return false;
                }
            }
            
            self::setError('没有要插入的数据');
            return false;
        } catch (Exception $e) {
            self::setError('添加失败：' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 编辑名人名言
     * @param array $params
     * @return bool
     */
    public static function edit(array $params): bool
    {
        try {
            TenantExamCelebrityQuote::update([
                'content' => $params['content'],
                'category' => $params['category'] ?? '',
                'update_time' => time()
            ], ['id' => $params['id']]);
            return true;
        } catch (Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }
    
    /**
     * 删除名人名言
     * @param array $params
     * @return bool
     */
    public static function delete(array $params): bool
    {
        try {
            TenantExamCelebrityQuote::destroy($params['id']);
            return true;
        } catch (Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }
    
    /**
     * 获取随机名人名言
     * @param array $params
     * @return array
     */
    public static function random(array $params = []): array
    {
        try {
            return TenantExamCelebrityQuote::getRandomQuote();
        } catch (Exception $e) {
            self::setError($e->getMessage());
            return [];
        }
    }
    
    /**
     * 获取所有分类
     * @param array $params
     * @return array
     */
    public static function getCategories(array $params = []): array
    {
        try {
            return TenantExamCelebrityQuote::distinct()
                ->where('category', '<>', '')
                ->column('category');
        } catch (Exception $e) {
            self::setError($e->getMessage());
            return [];
        }
    }
}