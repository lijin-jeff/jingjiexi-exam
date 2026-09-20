<?php
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 访问官网：https://www.tutudati.com
// | 官方邮箱：tutudati@outlook.com
// | 精解析答题系统开发者版权所有，拥有最终解释权。
declare (strict_types=1);

namespace app\tenantapi\logic\exam\resource;


use app\common\logic\BaseLogic;
use app\common\model\resource\TenantResource;
use think\facade\Db;


/**
 * 资源管理逻辑
 * Class TenantResourceLogic
 * @package app\platform\logic\resource
 */
class TenantResourceLogic extends BaseLogic
{


    /**
     * @notes 添加资源管理
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/06/17 00:37
     */
    public static function add(array $params): bool
    {
        try {
            TenantResource::create([
                'title'        => $params['title'],
                'is_show'      => $params['is_show'],
                'sort'         => $params['sort'],
                'image'        => $params['image'],
                'remark'       => $params['remark'],
                'category_parent_uid' => $params['category_parent_uid'],
                'category_uid' => $params['category_uid'],
                'exam_category_uid' => $params['exam_category_uid'],
                'author'       => $params['author'],
                'free_state'   => $params['free_state'],
                'money'        => empty($params['money']) ? 0 : $params['money'],
                'year'         => $params['year'],
                'file_input_type' => $params['file_input_type'],
                'file_url'     => $params['file_url'],
                'uid'          => uid(),
                'tenant_id'    => $params['tenant_id'],
            ]);
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }


    /**
     * @notes 编辑资源管理
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/06/17 00:37
     */
    public static function edit(array $params): bool
    {
        try {
            TenantResource::where([
                ['id', '=', $params['id']],
                ['tenant_id', '=', $params['tenant_id']]
            ])->update([
                'title'        => $params['title'],
                'is_show'      => $params['is_show'],
                'sort'         => $params['sort'],
                'image'        => $params['image'],
                'remark'       => $params['remark'],
                'category_parent_uid' => $params['category_parent_uid'],
                'category_uid' => $params['category_uid'],
                'exam_category_uid' => $params['exam_category_uid'],
                'author'       => $params['author'],
                'free_state'   => $params['free_state'],
                'file_input_type' => $params['file_input_type'],
                'file_url'     => $params['file_url'],
                'money'        => empty($params['money']) ? 0 : $params['money'],
                'year'         => $params['year']
            ]);
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }


    /**
     * @notes 删除资源管理
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/06/17 00:37
     */
    public static function delete(array $params): bool
    {
        return TenantResource::destroy($params['id']);
    }


    /**
     * @notes 获取资源管理详情
     * @param $params
     * @return array
     * @author 精解析答题
     * @date 2025/06/17 00:37
     */
    public static function detail($params): array
    {
        return TenantResource::query()->where([
            ['id', '=', $params['id']],
        ])->findOrEmpty();
    }
    /**
     * @notes 复制和批量复制资源管理
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/06/17 00:37
     */
    public static function copy(array $params): bool
    {
        try {
            $resourceIds = $params['id'];
            $tenantId = $params['tenant_id'];
            // 确保 $resourceIds 是数组
            if (!is_array($resourceIds)) {
                $resourceIds = [$resourceIds];
            }
            // 复制资源
            foreach ($resourceIds as $resourceId) {
                $resource = TenantResource::query()->where([
                    ['id', '=', $resourceId],
                    ['tenant_id', '=', $tenantId]
                ])->findOrEmpty();
                if ($resource->isEmpty()) {
                    continue;
                }
                $resourceData = $resource->toArray();
                // 移除 id 字段，让 MySQL 自动生成自增 ID
                unset($resourceData['id']);
                // 为新资源生成新的 uid
                $resourceData['uid'] = uid();
                // 重新设置 create_time 和 update_time 为当前时间戳
                $currentTime = time();
                $resourceData['create_time'] = $currentTime;
                $resourceData['update_time'] = $currentTime;
                // 重置统计字段
                $resourceData['view_count'] = 0;
                $resourceData['download_count'] = 0;
                $resourceData['tenant_id'] = $tenantId;
                TenantResource::create($resourceData);
            }
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    } 
}