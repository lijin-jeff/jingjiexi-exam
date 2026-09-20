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

namespace app\api\logic\exam\countdown;

use app\common\logic\BaseLogic;
use app\common\model\exam\countdown\TenantExamCelebrityQuote;

/**
 * 倒计时-名人名言逻辑
 * Class CelebrityQuoteLogic
 * @package app\api\logic\exam\countdown 
 */
class CelebrityQuoteLogic extends BaseLogic
{
    /**
     * 获取随机倒计时-名人名言
     * @param array $params
     * @return array
     */
    public static function random(array $params = []): array
    {
        try {
            $where = [];
            
            // 支持按分类获取随机名言
            if (!empty($params['category'])) {
                $where['category'] = $params['category'];
            }
            
            $quote = TenantExamCelebrityQuote::getRandomQuote($where);
            return ['success' => true, 'data' => $quote, 'msg' => '获取随机名言成功'];
        } catch (\Exception $e) {
            return ['success' => false, 'msg' => $e->getMessage(), 'error' => $e->getCode(), 'data' => []];
        }
    }
    
    /**
     * 获取倒计时-名人名言列表  
     * @param array $params
     * @return array
     */
    public static function lists(array $params): array
    {
        try {
            $page = (int)($params['page_no'] ?? 1);
            $limit = (int)($params['page_size'] ?? 10);
            
            $where = [];
            
            // 分类筛选
            if (!empty($params['category'])) {
                $where['category'] = $params['category'];
            }
            
            // 关键词搜索
            if (!empty($params['keyword'])) {
                $keyword = $params['keyword'];
                $where[] = ['content|author', 'like', '%' . $keyword . '%'];
            }
            
            // 排序规则
            $order = [];
            switch ($params['sort_by'] ?? 'default') {
                case 'create_time':
                    $order = ['create_time' => 'desc'];
                    break;
                default:
                    $order = ['id' => 'desc'];
                    break;
            }
            
            $result = TenantExamCelebrityQuote::getQuoteList($where, $page, $limit, $order);
            return ['success' => true, 'data' => $result, 'msg' => '获取名言列表成功'];
        } catch (\Exception $e) {
            return ['success' => false, 'msg' => $e->getMessage(), 'error' => $e->getCode(), 'data' => []];
        }
    }
    
    /**
     * 获取名言分类列表
     * @return array
     */
    public static function categories(): array
    {
        try {
            $categories = TenantExamCelebrityQuote::distinct()->column('category');
            $categoryList = array_filter($categories, function($category) {
                return !empty($category);
            });
            return ['success' => true, 'data' => ['list' => $categoryList], 'msg' => '获取分类列表成功'];
        } catch (\Exception $e) {
            return ['success' => false, 'msg' => $e->getMessage(), 'error' => $e->getCode(), 'data' => []];
        }
    }
    
    /**
     * 添加名人名言
     * @param array $params
     * @return array
     */
    public static function add(array $params): array
    {
        try {
            // 参数验证
            if (empty($params['content'])) {
                return ['success' => false, 'msg' => '请输入名言内容', 'error' => 400, 'data' => []];
            }
            
            if (empty($params['author'])) {
                return ['success' => false, 'msg' => '请输入作者名称', 'error' => 400, 'data' => []];
            }
            
            // 验证内容长度
            if (mb_strlen($params['content']) > 255) {
                return ['success' => false, 'msg' => '名言内容不能超过255个字符', 'error' => 400, 'data' => []];
            }
            
            // 验证作者长度
            if (mb_strlen($params['author']) > 50) {
                return ['success' => false, 'msg' => '作者名称不能超过50个字符', 'error' => 400, 'data' => []];
            }
            
            // 验证分类长度
            if (isset($params['category']) && mb_strlen($params['category']) > 50) {
                return ['success' => false, 'msg' => '分类名称不能超过50个字符', 'error' => 400, 'data' => []];
            }
            
            // 创建名人名言
            $quote = new TenantExamCelebrityQuote();
            $quote->content = $params['content'];
            $quote->author = $params['author'];
            $quote->category = $params['category'] ?? '';
            $quote->create_time = time();
            
            if ($quote->save()) {
                return ['success' => true, 'data' => $quote->toArray(), 'msg' => '添加名人名言成功'];
            }
            
            return ['success' => false, 'msg' => '添加名人名言失败', 'error' => 500, 'data' => []];
        } catch (\Exception $e) {
            return ['success' => false, 'msg' => $e->getMessage(), 'error' => $e->getCode(), 'data' => []];
        }
    }
    
    /**
     * 更新名人名言
     * @param array $params
     * @return array
     */
    public static function update(array $params): array
    {
        try {
            $id = (int)($params['id'] ?? 0);
            
            if ($id <= 0) {
                return ['success' => false, 'msg' => '名言ID不能为空', 'error' => 400, 'data' => []];
            }
            
            // 查找名言
            $quote = TenantExamCelebrityQuote::findOrEmpty($id);
            
            if ($quote->isEmpty()) {
                return ['success' => false, 'msg' => '名人名言不存在', 'error' => 404, 'data' => []];
            }
            
            // 参数验证
            if (isset($params['content'])) {
                if (empty($params['content'])) {
                    return ['success' => false, 'msg' => '请输入名言内容', 'error' => 400, 'data' => []];
                }
                if (mb_strlen($params['content']) > 255) {
                    return ['success' => false, 'msg' => '名言内容不能超过255个字符', 'error' => 400, 'data' => []];
                }
                $quote->content = $params['content'];
            }
            
            if (isset($params['author'])) {
                if (empty($params['author'])) {
                    return ['success' => false, 'msg' => '请输入作者名称', 'error' => 400, 'data' => []];
                }
                if (mb_strlen($params['author']) > 50) {
                    return ['success' => false, 'msg' => '作者名称不能超过50个字符', 'error' => 400, 'data' => []];
                }
                $quote->author = $params['author'];
            }
            
            if (isset($params['category'])) {
                if (mb_strlen($params['category']) > 50) {
                    return ['success' => false, 'msg' => '分类名称不能超过50个字符', 'error' => 400, 'data' => []];
                }
                $quote->category = $params['category'];
            }
            
            if ($quote->save()) {
                return ['success' => true, 'data' => $quote->toArray(), 'msg' => '更新名人名言成功'];
            }
            
            return ['success' => false, 'msg' => '更新名人名言失败', 'error' => 500, 'data' => []];
        } catch (\Exception $e) {
            return ['success' => false, 'msg' => $e->getMessage(), 'error' => $e->getCode()];
        }
    }
    
    /**
     * 删除名人名言
     * @param array $params
     * @return array
     */
    public static function delete(array $params): array
    {
        try {
            $id = (int)($params['id'] ?? 0);
            
            if ($id <= 0) {
                return ['success' => false, 'msg' => '名言ID不能为空', 'error' => 400, 'data' => []];
            }
            
            // 查找名言
            $quote = TenantExamCelebrityQuote::findOrEmpty($id);
            
            if ($quote->isEmpty()) {
                return ['success' => false, 'msg' => '名人名言不存在', 'error' => 404, 'data' => []];
            }
            
            if ($quote->delete()) {
                return ['success' => true, 'msg' => '删除名人名言成功', 'data' => []];
            }
            
            return ['success' => false, 'msg' => '删除名人名言失败', 'error' => 500, 'data' => []];
        } catch (\Exception $e) {
            return ['success' => false, 'msg' => $e->getMessage(), 'error' => $e->getCode()];
        }
    }
}