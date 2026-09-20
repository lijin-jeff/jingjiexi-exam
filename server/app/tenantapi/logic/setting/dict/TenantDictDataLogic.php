<?php
// +----------------------------------------------------------------------
// | likeadmin快速开发前后端分离管理后台（PHP版）
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 开源版本可自由商用，可去除界面版权logo
// | gitee下载：https://gitee.com/likeshop_gitee/likeadmin
// | github下载：https://github.com/likeshop-github/likeadmin
// | 访问官网：https://www.likeadmin.cn
// | likeadmin团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeadminTeam
// +----------------------------------------------------------------------

namespace app\tenantapi\logic\setting\dict;

use app\common\logic\BaseLogic;
use app\common\model\dict\TenantDictData;
use app\common\model\dict\TenantDictType;


/**
 * 字典数据逻辑
 * Class TenantDictDataLogic
 * @package app\tenantapi\logic\setting\dict    
 */
class TenantDictDataLogic extends BaseLogic
{

    /**
     * @notes 添加编辑
     * @param array $params
     * @return TenantDictData|\think\Model
     * @author 段誉
     * @date 2022/6/20 17:13
     */
    public static function save(array $params)
    {
        $data = [
            'tenant_id' => $params['tenant_id'],
            'name' => $params['name'],
            'value' => $params['value'],
            'sort' => $params['sort'] ?? 0,
            'status' => $params['status'],
            'remark' => $params['remark'] ?? '',
        ];

        if (!empty($params['id'])) {
            return TenantDictData::where(['id' => $params['id']])->update($data);
        } else {
            $dictType = TenantDictType::findOrEmpty($params['type_id']);
            $data['type_id'] = $params['type_id'];
            $data['type_value'] = $dictType['type'];
            return TenantDictData::create($data);
        }
    }


    /**
     * @notes 删除字典数据
     * @param array $params
     * @return bool
     * @author 段誉
     * @date 2022/6/20 17:01
     */
    public static function delete(array $params)
    {
        return TenantDictData::destroy($params['id']);
    }


    /**
     * @notes 获取字典数据详情
     * @param $params
     * @return array
     * @author 段誉
     * @date 2022/6/20 17:01
     */
    public static function detail($params): array
    {
        return TenantDictData::findOrEmpty($params['id'])->toArray();
    }


}