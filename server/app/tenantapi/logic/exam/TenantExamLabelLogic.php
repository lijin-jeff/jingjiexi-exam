<?php

namespace app\tenantapi\logic\exam;


use app\common\logic\BaseLogic;
use app\common\model\exam\TenantExamLabel;


/**
 * 题库标签逻辑
 * Class TenantExamLabelLogic
 * @package app\tenantapi\logic\exam
 */
class TenantExamLabelLogic extends BaseLogic
{


    /**
     * 查询分类
     * @param array $params
     * @return array
     */
    public static function labelList(array $params): array
    {
        return TenantExamLabel::where([
            ['tenant_id', '=', $params['tenant_id']],
            ['library_uid', '=', $params['library_uid']],
            ['is_show', '=', 1]
        ])->column(["uid", "title"]);
    }

    /**
     * @notes 添加题库标签（支持批量添加）
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/04/01 01:51
     */
    public static function add(array $params): bool
    {
        try {
            // 分割标签名称，支持多行输入
            $titles = array_filter(array_map('trim', explode(PHP_EOL, $params['title'])));
            
            // 验证输入
            if (empty($titles)) {
                self::setError('请输入标签名称');
                return false;
            }
            
            // 准备批量插入数据
            $insertData = [];
            foreach ($titles as $title) {
                if (empty($title)) continue;
                
                $insertData[] = [
                    'title'                   => $title,
                    'is_show'                 => $params['is_show'],
                    'sort'                    => $params['sort'],
                    'uid'                     => uid(),
                    'tenant_id'               => $params['tenant_id'],
                    "library_uid"             => $params['library_uid'] ?? '0',
                    'create_time'             => time(),
                    'update_time'             => time()
                ];
            }
            
            // 批量插入
            if (!empty($insertData)) {
                $result = TenantExamLabel::insertAll($insertData);
                return $result !== false;
            }
            
            return false;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }


    /**
     * @notes 编辑题库标签
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/04/01 01:51
     */
    public static function edit(array $params): bool
    {
        try {
            $row = TenantExamLabel::where([
                ['id', '=', $params['id']],
                ['tenant_id', '=', $params['tenant_id']]
            ])->update([
                'title'                   => $params['title'],
                'is_show'                 => $params['is_show'],
                'sort'                    => $params['sort'],
                "library_uid"             => $params['library_uid'] ?? '0'  
            ]);
            return $row > 0;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }


    /**
     * @notes 删除题库标签
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/04/01 01:51
     */
    public static function delete(array $params): bool
    {
        if (is_array($params["id"])) {
            return TenantExamLabel::whereIn("id", $params["id"])->where([   
                ['tenant_id', '=', $params['tenant_id']],
            ])->update(['delete_time' => time()]) > 0;
        }
        return TenantExamLabel::where([
                ['id', '=', $params['id']],
                ['tenant_id', '=', $params['tenant_id']],
            ])->update(['delete_time' => time()]) > 0;
    }


    /**
     * @notes 获取题库标签详情
     * @param $params
     * @return array
     * @author 精解析答题
     * @date 2025/04/01 01:51
     */
    public static function detail($params): array
    {
        //print_r($params);
        $query = TenantExamLabel::where([
            ['id', '=', $params['id']],
            ['tenant_id', '=', $params['tenant_id']],
            ["library_uid", "=", $params["library_uid"]]
        ]);
        //print_r($query->find()->toArray());
        //echo $query->getLastSql();
        return $query->find()->toArray();
    }

}