<?php
// +----------------------------------------------------------------------
// | 精解析答题开发团队介绍
// +----------------------------------------------------------------------
// | 欢迎你使用本套系统，本套系统由精解析答题开发团队全力开发。
// | 如果本套系统是商业系统，请严格遵守系统使用相关协议，出现违背协议的法律行为，所有违法行为均与精解析答题无关。
// | 官网地址：
// | 官方邮箱：
// | 精解析答题团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | 作者: 精解析答题开发团队
// +----------------------------------------------------------------------
namespace app\api\logic\resource;

use app\common\logic\BaseLogic;
use app\common\model\resource\TenantResource;
use app\common\model\resource\TenantResourceCategory;
use app\common\model\resource\TenantResourceCollect;
use app\api\logic\IntegralLogic;
use app\common\enum\{IntegralEnum};
use app\common\model\exam\TenantUserIntegralLog;
use think\facade\Db;

class ResourceLogic extends BaseLogic
{
    /**
     * 资源分类树
     * @return array
     */
    public static function categoryList(array $params): array
    {
        $items = TenantResourceCategory::where([
            ["parent_uid", "=", ""],
            ["exam_category_uid", "=", $params['exam_category_uid']]
        ])->with(["children" => function ($query) {
            $query->field(["parent_uid", "uid", "title as label"]);
        }])->field(["parent_uid", "uid", "title as label"])->select()->toArray();
        foreach ($items as $key => $value) {
            $items[$key]["value"] = $value["uid"];
            foreach ($value["children"] as $k => $v) {
                $items[$key]["children"][$k]["value"] = $v["uid"];
                unset($items[$key]["children"][$k]["uid"]);
            }
            unset($items[$key]["uid"]);
        }
        return $items;
    }

    /**
     * 资源列表
     * @param array $params
     * @return array
     */
    public static function resourceList(array $params): array
    {
        $pageNo = (int)(isset($params['page_no']) ? $params['page_no'] : 1);
        $pageSize = (int)(isset($params['page_size']) ? $params['page_size'] : 20);
        $pageSize = min($pageSize, 20);
        //print_r($params);exit;


       try {
        $items = TenantResource::query()->where([['is_show', '=', 1]])
            ->where(function ($query) use ($params) {
                if (!empty($params['exam_category_uid'])) {
                    $query->where('exam_category_uid', '=', $params['exam_category_uid']);
                }
                if (!empty($params['category_parent_uid'])) {
                    $query->where('category_parent_uid', '=', $params['category_parent_uid']);
                }
                if (!empty($params['category_uid'])) {
                    $query->where('category_uid', '=', $params['category_uid']);
                }
                if (!empty($params['keywords'])) {
                    $query->whereLike('title', '%' . $params['keywords'] . '%');
                }
            })
            ->with(['category' => function ($query) {
                $query->field(['uid', 'title']);
            }])
            ->field(['uid', 'category_parent_uid', 'category_uid', 'exam_category_uid','title', 'image', 'year', 'free_state', 'view_count', 'download_count','remark', 'update_time'])
            ->paginate([
                'list_rows' => $pageSize,
                'page'      => $pageNo
            ]);
        } catch (\Exception $e) {
            \think\facade\Log::error('分类查询异常：' . $e->getMessage() . ' 查询条件：' . json_encode($params));
            throw new \think\Exception('数据加载失败，请稍后重试');
        }

        return [
            'lists'     => $items->items(),
            'page_no'   => $pageNo,
            'page_size' => $pageSize,
            'count'     => $items->total(),
            'extend'    => []
        ];
    }

    /**
     * 资源详情
     * @param array $params
     * @return array
     * @link 
     * @email 
     * @author 精解析答题
     */
    public static function resourceContent(array $params): array
    {
        $resource = TenantResource::query()->where([
            ['uid', '=', $params['uid']],
            ['is_show', '=', 1]
        ])->field(['uid', 'title', 'remark', 'free_state', 'money', 'author', 'year', 'view_count', 'download_count'])->findOrEmpty();
        if ($resource->isEmpty()) return [];
        TenantResource::query()->where([
            ['uid', '=', $params['uid']]
        ])->update(['view_count' => Db::raw('view_count + 1')]);
        $resource = $resource->toArray();
        $resource['collection_state'] = false;
        $resource['purchase_state'] = false;
        if (!empty($params['user_uid'])) {
            // 检查用户是否收藏了该资源
            $collection = TenantResourceCollect::query()->where([
                ['resource_uid', '=', $params['uid']],
                ['user_uid', '=', $params['user_uid']]
            ])->value('id');
            if (!empty($collection)) $resource['collection_state'] = true;
            // 检查用户是否购买过此资源
            $purchase = TenantUserIntegralLog::query()->where([
                ['change_type', '=', IntegralEnum::DOWNLOAD_INTEGRAL],
                ['user_id', '=', $params['user_uid']],
               ["extra->resource_uid", "=", $params['uid']]
            ])->value('id');
            if (!empty($purchase)) $resource['purchase_state'] = true;
        }
        return $resource;
    }

    /**
     * 下载资源
     * @param array $params
     * @return array
     * @link 
     * @email 
     * @author 精解析答题
     */
    public static function resourceDownload(array $params): array
    {
        $resource = TenantResource::query()->where([
            ['uid', '=', $params['uid']],
            ['is_show', '=', 1]
        ])->field(['file_url'])->findOrEmpty();
        if ($resource->isEmpty()) return [];
        return $resource->toArray();
    }

    /**
     * 积分下载资源
     * @param array $params
     * @return array|false
     * @link 
     * @email 
     * @author 精解析答题
     */
    public static function resourceDownloadPoints(array $params): array|false
    {
        // 检查用户是否登录
        if(empty($params['user_uid'])) {
            self::setError('请先登录');
            return false;
        }
        
        // 获取资源信息
        $resource = TenantResource::query()->where([
            ['uid', '=', $params['uid']],
            ['is_show', '=', 1]
        ])->field(['file_url', 'free_state', 'money', 'title'])->findOrEmpty();
        
        if($resource->isEmpty()) {
            self::setError('资源不存在');
            return false;
        }
        
        $resource = $resource->toArray();
        
        // 检查是否为付费资源
        if($resource['free_state'] != 2) {
            self::setError('该资源不需要积分下载');
            return false;
        }
        
        // 检查用户积分
        $user = \app\common\model\user\User::query()->where([
            ['id', '=', $params['user_uid']]
        ])->field(['integral', 'id'])->findOrEmpty();
        
        if($user->isEmpty()) {
            self::setError('用户不存在');
            return false;
        }
        
        $user = $user->toArray();
        $needPoints = (float)$resource['money'];
        
        if($user['integral'] < $needPoints) {
            self::setError('积分不足，请先获取积分');
            return false;
        }
        
        // 扣除积分并更新下载次数
        Db::startTrans();
        try {
            // 使用IntegralLogic::addIntegral方法扣除积分
            // 参数说明：用户ID, 动作类型(2-减少), 积分类型(假设为1-资源下载), 积分数量, 备注, 扩展数据
            $integralResult = IntegralLogic::addIntegral(
                $user['id'], // 用户ID
                2, // 动作类型：2-减少积分
                IntegralEnum::DOWNLOAD_INTEGRAL, // 积分类型：7为资源下载
                $needPoints, // 积分数量
                '下载资源：' . $resource['title'], // 备注
                ['resource_uid' => $params['uid']] // 扩展数据
            );
            
            if(!$integralResult) {
                Db::rollback();
                self::setError('积分扣除失败，请稍后重试');
                return false;
            }
            
            // 更新资源下载次数
            TenantResource::query()->where([
                ['uid', '=', $params['uid']]
            ])->update(['download_count' => Db::raw('download_count + 1')]);
            
            Db::commit();
            return ['file_url' => $resource['file_url']];
        } catch (\Exception $e) {
            Db::rollback();
            self::setError('积分扣除失败，请稍后重试');
            return false;
        }
    }

    /**
     * 资源收藏
     * @param array $params
     * @return bool
     * @link 
     * @email 
     * @author 精解析答题
     */
    public static function resourceCollection(array $params): bool
    {
        try {
            TenantResourceCollect::create([
                'resource_uid' => $params['uid'],
                'user_uid'     => $params['user_uid']
            ]);
            return true;
        } catch (\Exception $exception) {
            $str = "";
            preg_match("/t_tenant_resource_collect.idx_ur/", $exception->getMessage(), $str);
            if (!empty($str)) {
                self::setError('已收藏');
            } else {
                self::setError($exception->getMessage());
            }
        }
        return false;
    }
}
