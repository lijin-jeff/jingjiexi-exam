<?php
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 精解析答题系统开发者版权所有，拥有最终解释权。

namespace app\tenantapi\logic\exam;


use app\common\logic\BaseLogic;
use app\common\model\exam\TenantExamKnowledge;


/**
 * 题库章节知识点逻辑
 * Class TenantExamKnowledgeLogic
 * @package app\platform\logic\exam
 */
class TenantExamKnowledgeLogic extends BaseLogic
{


    /**
     * 查询分类
     * @param array $params
     * @return array
     */
    public static function parentList(array $params): array
    {
        return TenantExamKnowledge::where([
            ['tenant_id', '=', $params['tenant_id']],
            ['chapter_uid', '=', $params['chapter_uid']],
            ['library_uid', '=', $params['library_uid']],
            ["parent_uid", "=", '']
        ])->column(["uid", "title"]);
    }

    /**
     * @notes 添加题库分类（支持批量添加）
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/04/01 01:51
     */
    public static function add(array $params): bool
    {
        try {
            // 分割知识点名称和描述，支持多行输入
            // 换行分割
            $plainTitle = $params['title'];
            $rawTitles = array_filter(array_map('trim', explode(PHP_EOL, $plainTitle)));
            $contents = [];
            if (!empty($params['content'])) {
                $contents = array_filter(array_map('trim', explode(PHP_EOL, $params['content'])));
            }
            
            // 验证输入
            if (empty($rawTitles)) {
                self::setError('请输入知识点名称');
                return false;
            }
            
            // 准备批量插入数据
            $insertData = [];
            foreach ($rawTitles as $index => $rawTitle) {
                if (empty($rawTitle)) continue;
                
                // 检查是否使用“：”分隔知识点名称和描述
                $title = $rawTitle;
                $content = $contents[$index] ?? '';
                
                if (strpos($rawTitle, '：') !== false) {
                    // 使用中文冒号分割
                    $parts = explode('：', $rawTitle, 2);
                    $title = trim($parts[0]);
                    $colonContent = trim($parts[1]);
                    // 如果存在“：”分割的描述，则优先使用
                    if (!empty($colonContent)) {
                        $content = $colonContent;
                    }
                } elseif (strpos($rawTitle, ':') !== false) {
                    // 兼容英文冒号
                    $parts = explode(':', $rawTitle, 2);
                    $title = trim($parts[0]);
                    $colonContent = trim($parts[1]);
                    // 如果存在“:”分割的描述，则优先使用
                    if (!empty($colonContent)) {
                        $content = $colonContent;
                    }
                }
                
                // 限制标题长度（数据库字段 varchar(32)）
                if (mb_strlen($title) > 32) {
                    self::setError('知识点名称不能超过 32 个字符：' . $title);
                    return false;
                }
                
                $insertData[] = [
                    'title'                   => $title,
                    'content'                 => $content,
                    'is_show'                 => $params['is_show'],
                    'sort'                    => $params['sort'],
                    'uid'                     => uid(),
                    "parent_uid"              => $params["parent_uid"] ?? "",
                    'tenant_id'               => $params['tenant_id'],
                    "chapter_uid"             => $params['chapter_uid'],
                    'library_uid'             => $params['library_uid'],
                    'create_time'             => time(),
                    'update_time'             => time()
                ];
            }
            
            // 批量插入
            if (!empty($insertData)) {
                $result = TenantExamKnowledge::insertAll($insertData);
                return $result !== false;
            }
            
            return false;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }


    /**
     * @notes 编辑题库分类
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/04/01 01:51
     */
    public static function edit(array $params): bool
    {
        try {
            // 检查是否循环引用（不能将自己设为父级，也不能将自己的子级设为父级）
            $currentUid = TenantExamKnowledge::where([
                ['id', '=', $params['id']],
                ['tenant_id', '=', $params['tenant_id']]
            ])->value('uid');
            
            if (!empty($params['parent_uid']) && $params['parent_uid'] === $currentUid) {
                self::setError('不能将自身设置为父级知识点');
                return false;
            }
            
            // 检查是否将子级设为父级（循环引用）
            if (!empty($params['parent_uid'])) {
                $isChild = self::isChildKnowledge($currentUid, $params['parent_uid'], $params['tenant_id']);
                if ($isChild) {
                    self::setError('不能将子级知识点设置为父级');
                    return false;
                }
            }
            
            $updateData = [
                'title'                   => strip_tags($params['title']),
                'content'                 => $params['content'] ?? '',
                'is_show'                 => $params['is_show'],
                'sort'                    => $params['sort'],
                'chapter_uid'             => $params['chapter_uid'] ?? '',
                'library_uid'             => $params['library_uid'] ?? '',
                'parent_uid'              => $params['parent_uid'] ?? '',
                'update_time'             => time()
            ];
            
            $row = TenantExamKnowledge::where([
                ['id', '=', $params['id']],
                ['tenant_id', '=', $params['tenant_id']]
            ])->update($updateData);
            
            return $row > 0;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }
    
    /**
     * 检查目标是否是当前知识点的子级
     * @param string $currentUid 当前知识点UID
     * @param string $targetUid 目标知识点UID
     * @param int $tenantId 租户ID
     * @return bool
     */
    private static function isChildKnowledge(string $currentUid, string $targetUid, int $tenantId): bool
    {
        $children = TenantExamKnowledge::where([
            ['parent_uid', '=', $currentUid],
            ['tenant_id', '=', $tenantId]
        ])->column('uid');
        
        if (empty($children)) {
            return false;
        }
        
        if (in_array($targetUid, $children)) {
            return true;
        }
        
        // 递归检查子级的子级
        foreach ($children as $childUid) {
            if (self::isChildKnowledge($childUid, $targetUid, $tenantId)) {
                return true;
            }
        }
        
        return false;
    }


    /**
     * @notes 删除题库分类
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/04/01 01:51
     */
    public static function delete(array $params): bool
    {
        if (is_array($params["id"])) {
            return TenantExamKnowledge::whereIn("id", $params["id"])->where([
                    ['tenant_id', '=', $params['tenant_id']],
                ])->update(['delete_time' => time()]) > 0;
        }
        return TenantExamKnowledge::where([
                ['id', '=', $params['id']],
                ['tenant_id', '=', $params['tenant_id']],
            ])->update(['delete_time' => time()]) > 0;
    }


    /**
     * @notes 获取知识点详情
     * @param $params
     * @return array
     * @author 精解析答题
     * @date 2025/04/01 01:51
     */
    public static function detail($params): array
    {
        $detail = TenantExamKnowledge::where([
            ['id', '=', $params['id']],
            ['tenant_id', '=', $params['tenant_id']]
        ])
        ->with(["chapter"])
        ->find();
        
        if (empty($detail)) {
            return [];
        }
        
        $result = $detail->toArray();
        
        // 确保parent_uid字段存在
        if (!isset($result['parent_uid'])) {
            $result['parent_uid'] = '';
        }
        
        return $result;
    }

    /**
     * 分类树
     * @param array $params
     * @return array
     * @date 2024/4/21 00:45
     * @author 精解析答题
     */
    public static function categoryTree(array $params): array
    {
        // 定义递归函数来处理子节点查询
        $withRecursiveChildren = function ($query) use (&$withRecursiveChildren) {
            $query->field(["uid", "parent_uid", "title"]) // 只选择必要的uid和title字段，移除不必要的parent_uid
                ->with(["children" => $withRecursiveChildren]);
        };
        
        $items = TenantExamKnowledge::where([
            ['tenant_id', '=', $params['tenant_id']],
            ["parent_uid", "=", ""],
            ['chapter_uid', '=', $params['chapter_uid']]
        ])->with(["children" => $withRecursiveChildren])
          ->field(["uid", "parent_uid", "title"]) // 只选择必要的uid和title字段
          ->select()
          ->toArray();
        
        // 递归处理数据格式 - 只移除parent_uid，保留uid字段
        $formatData = function (&$items) use (&$formatData) {
            foreach ($items as $key => &$value) {
                // 保留uid字段，移除不必要的parent_uid字段
                unset($value["parent_uid"]);
                
                if (isset($value["children"])) {
                    foreach ($value["children"] as $k => &$v) {
                        unset($v["parent_uid"]); // 只移除不必要的parent_uid字段
                        
                        if (isset($v["children"])) {
                            $formatData($v["children"]);
                        }
                    }
                }
            }
        };

        $formatData($items);
        return $items;
    }
}