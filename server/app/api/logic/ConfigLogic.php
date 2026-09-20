<?php
declare(strict_types=1);
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 访问官网：https://www.jingjiexi.com
// | 官方邮箱：jingjiexi@outlook.com
// | 精解析答题系统开发者版权所有，拥有最终解释权。
namespace app\api\logic;

use app\common\logic\BaseLogic;
use app\common\model\exam\TenantBanner;
use think\facade\Db;

class ConfigLogic extends BaseLogic
{
    /**
     * 获取图片配置
     * @param array $params
     * @return array
     * @link https://www.jingjiexi.com
     * @email jingjiexi@outlook.com
     * @date 2025/5/2 18:13
     * @author 精解析答题 <jingjiexi@outlook.com>
     */
    public static function imageList(array $params): array
    {
        // 定义必要参数键名数组
        $requiredKeys = ['type', 'client', 'position'];
        
        // 检查参数是否包含必要键名
        foreach ($requiredKeys as $key) {
            if (!isset($params[$key])) {
                return [];
            }
        }
        $query = TenantBanner::query()
            ->where('image_type', $params['type'])
            ->where('client', 'LIKE', "%{$params['client']}%")
            ->where('position', $params['position'])
            ->where('is_show', 1)
            ->order('sort desc')
            ->field(['title', 'url', 'icon', 'image', 'close_position', 'display_mode'])
            ->select()
            ->toArray();
        // 打印SQL语句
        //echo $query->buildSql();
        // 处理url，提取相应的url
        foreach ($query as &$item) {
            if (isset($item['url'])) {
                $decoded = json_decode($item['url'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    // 如果是custom类型且包含query中的url，则提取query.url(自定义链接)
                    if (isset($decoded['type']) && $decoded['type'] === 'custom' && 
                        isset($decoded['query']) && isset($decoded['query']['url'])) {
                        $item['url'] = $decoded['query']['url']??'';
                    }else{
                        // 否则，提取path(系统默认链接)
                        $item['url'] = $item['url'] = $decoded['path']??'';;
                    }
                }
            }
        }
        unset($item);

        return $query;
    }

    /**
     * 字典类型列表
     * @return array
     * @link https://www.jingjiexi.com
     * @email jingjiexi@outlook.com
     * @date 2025/5/10 02:04
     * @author 精解析答题 <jingjiexi@outlook.com>
     */
    public static function dictType(): array
    {
        return Db::name('tenant_dict_type')->where([
            ['tenant_id', '=', request()->tenantId],
            ['status', '=', 1]
        ])->column(['name', 'type', 'remark']);
    }

    /**
     * 字典数据列表
     * @param array $params
     * @return array
     * @link https://www.jingjiexi.com
     * @email jingjiexi@outlook.com
     * @date 2025/5/10 02:05
     * @author 精解析答题 <jingjiexi@outlook.com>
     */
    public static function dictData(array $params): array
    {
        if (empty($params['type'])) return [];
        $typeArray = explode(',', $params['type']);
        return Db::name('tenant_dict_data')->where([
            ['tenant_id', '=', request()->tenantId],
            ['status', '=', 1],
            ['delete_time', '=', NULL],
        ])
        ->whereIn('type_value', $typeArray)->order('sort desc')->column(['name', 'value']);
    }
}