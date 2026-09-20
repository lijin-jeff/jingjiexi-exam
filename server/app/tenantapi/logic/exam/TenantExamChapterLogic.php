<?php
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 精解析答题系统开发者版权所有，拥有最终解释权。

namespace app\tenantapi\logic\exam;


use app\common\logic\BaseLogic;
use app\common\model\exam\TenantExamChapter;


/**
 * 题库章节逻辑
 * Class TenantExamChapterLogic
 * @package app\tenantapi\logic\exam
 */
class TenantExamChapterLogic extends BaseLogic
{


    /**
     * 查询分类
     * @param array $params
     * @return array
     */
    public static function parentList(array $params): array
    {
        return TenantExamChapter::where([
            ['tenant_id', '=', $params['tenant_id']],
            ['library_uid', '=', $params['library_uid']]
            // ["parent_uid", "=", '']
        ])->column(["uid", "title"]);
    }

    /**
     * @notes 添加题库分类
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/04/01 01:51
     */
    public static function add(array $params): bool
    {
        try {
            //处理章节名称，一行一个时，可以同时添加多条
            $titles = explode("\n", $params['title']);
            $successCount = 0;
            $errorCount = 0;
            
            // 循环处理每个标题
            foreach ($titles as $title) {
                $title = trim($title);
                // 跳过空行
                if (empty($title)) {
                    continue;
                }
                
                $model = TenantExamChapter::create([
                    'title'                   => $title,
                    'is_show'                 => $params['is_show'],
                    'sort'                    => $params['sort'],
                    'uid'                     => uid(),
                    "parent_uid"              => $params["parent_uid"] ?? 0,
                    'tenant_id'               => $params['tenant_id'],
                    "library_uid" => $params['library_uid'],
                ]);
                
                if ($model->getKey()) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
            }
            
            // 如果有至少一个成功，就返回true
            if ($successCount > 0) {
                // 如果有错误，记录错误信息
                if ($errorCount > 0) {
                    self::setError("成功添加 {$successCount} 个章节，失败 {$errorCount} 个章节");
                }
                return true;
            } else {
                self::setError("添加章节失败，请检查输入");
                return false;
            }
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
            $row = TenantExamChapter::where([
                ['id', '=', $params['id']],
                ['tenant_id', '=', $params['tenant_id']]
            ])->update([
                'title'                   => $params['title'],
                'is_show'                 => $params['is_show'],
                'sort'                    => $params['sort'],
                "library_uid" => $params['library_uid'],
                "parent_uid"              => $params["parent_uid"] ?? '',
            ]);
            return $row > 0;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }


    /**
     * @notes 删除题库分类（递归删除子章节）
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/04/01 01:51
     */
    public static function delete(array $params): bool
    {
        $tenantId = $params['tenant_id'];
        $ids = is_array($params["id"]) ? $params["id"] : [$params["id"]];
        
        // 收集所有需要删除的 ID（包括子章节）
        $allIds = self::collectAllChildIds($ids, $tenantId);
        
        if (empty($allIds)) {
            return false;
        }
        
        // 批量删除所有章节
        return TenantExamChapter::whereIn("id", $allIds)
            ->where([['tenant_id', '=', $tenantId]])
            ->update(['delete_time' => time()]) > 0;
    }
    
    /**
     * @notes 递归收集所有子章节 ID
     * @param array $ids 父章节 ID 列表
     * @param int $tenantId 租户 ID
     * @return array 所有需要删除的 ID
     * @author 精解析答题
     * @date 2026/03/08
     */
    private static function collectAllChildIds(array $ids, int $tenantId): array
    {
        $allIds = $ids;
        
        // 首先获取这些章节的 uid（因为 parent_uid 存储的是 uid 而不是 id）
        $parentUids = TenantExamChapter::whereIn("id", $ids)
            ->where([['tenant_id', '=', $tenantId], ['delete_time', '=', 0]])
            ->column("uid");
        
        if (empty($parentUids)) {
            return $allIds;
        }
        
        // 查询这些章节的所有直接子章节（使用 parent_uid IN (...) 查询）
        $children = TenantExamChapter::whereIn("parent_uid", $parentUids)
            ->where([['tenant_id', '=', $tenantId], ['delete_time', '=', 0]])
            ->column("id");
        
        if (!empty($children)) {
            // 递归查找子章节的子章节
            $grandChildren = self::collectAllChildIds($children, $tenantId);
            $allIds = array_merge($allIds, $grandChildren);
        }
        
        return array_unique($allIds);
    }
    
    /**
     * @notes 测试方法：获取章节及其所有子章节 ID
     * @param array $params
     * @return array
     * @author 精解析答题
     * @date 2026/03/08
     */
    public static function getChapterWithChildrenIds(array $params): array
    {
        $tenantId = $params['tenant_id'];
        $ids = is_array($params["id"]) ? $params["id"] : [$params["id"]];
        
        $allIds = self::collectAllChildIds($ids, $tenantId);
        
        // 获取所有章节详情
        $chapters = TenantExamChapter::whereIn("id", $allIds)
            ->where([['tenant_id', '=', $tenantId]])
            ->field("id, parent_uid, title, level")
            ->select()
            ->toArray();
        
        return [
            'delete_ids' => $allIds,
            'chapters' => $chapters
        ];
    }


    /**
     * @notes 获取题库分类详情
     * @param $params
     * @return array
     * @author 精解析答题
     * @date 2025/04/01 01:51
     */
    public static function detail($params): array
    {
        //print_r($params);
        $where = [];
        if (isset($params['id'])) {
            $where[] = ['id', '=', $params['id']];
        }
        if (isset($params['uid'])) {
            $where[] = ['uid', '=', $params['uid']];
        }
       // $where[] = ['tenant_id', '=', $params['tenant_id']];

        $query = TenantExamChapter::where($where);
        //print_r($query->find()->toArray());
        echo $query->getLastSql();
        return $query->find()->toArray();
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
        $where = [];
        if (isset($params['library_uid'])) {
            $where[] = ['library_uid', '=', $params['library_uid']];
        }
        try {
            $items = TenantExamChapter::where($where)
              ->order(['sort' => 'desc', 'id' => 'asc'])
              ->select();

        $newItems = [];
        foreach ($items as $row) {
            $newItems[] = [
                'pid' => $row['parent_uid'],  // 将 parent_uid 重命名为 pid
                'id' => $row['uid'],          // 将 uid 重命名为 id
                'name' => $row['title']       // 将 title 重命名为 name
                // 如需保留其他字段，请在此处添加
            ];
        }
        return linear_to_tree($newItems, 'children');
        } catch (\Exception $e) {
            // 记录错误日志
            \think\facade\Log::error('查询TenantExamChapter数据出错：' . $e->getMessage());
            return [];
        }
        
    }

    /**
     * @notes 从txt文件导入章节
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2026/03/08
     */
    public static function importFromTxt(array $params): bool
    {
        try {
            $content = $params['content'] ?? '';
            $libraryUid = $params['library_uid'] ?? '';
            $parentUid = $params['parent_uid'] ?? '0';
            $tenantId = $params['tenant_id'];
            
            if (empty($content)) {
                self::setError('文件内容为空');
                return false;
            }
            
            if (empty($libraryUid)) {
                self::setError('请选择题库');
                return false;
            }
            
            $chapters = self::parseChapterText($content);
            
            if (empty($chapters)) {
                self::setError('未能解析出有效的章节结构');
                return false;
            }
            
            $successCount = 0;
            $errorCount = 0;
            $chapterUidMap = [];
            
            foreach ($chapters as $chapter) {
                self::createChapterWithChildren(
                    $chapter,
                    $libraryUid,
                    $parentUid,
                    $tenantId,
                    $chapterUidMap,
                    $successCount,
                    $errorCount
                );
            }
            
            if ($successCount > 0) {
                if ($errorCount > 0) {
                    self::setError("成功导入 {$successCount} 个章节，失败 {$errorCount} 个章节");
                }
                return true;
            } else {
                self::setError("导入章节失败，请检查文件格式");
                return false;
            }
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 解析章节文本
     * @param string $content
     * @return array
     * @author 精解析答题
     * @date 2026/03/08
     */
    private static function parseChapterText(string $content): array
    {
        $lines = explode("\n", $content);
        $chapterMap = [];
        $order = 0;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }
            
            // 一级章节：第 X 章 章节名称
            if (preg_match('/^第\s*(\d+)\s*章\s+(.+)$/u', $line, $matches)) {
                $chapterNumber = $matches[1];
                if (!isset($chapterMap[$chapterNumber])) {
                    $chapterMap[$chapterNumber] = [
                        'level' => 1,
                        'number' => $chapterNumber,
                        'title' => '第' . $chapterNumber . '章 ' . trim($matches[2]),
                        'children' => [],
                        '_order' => $order++
                    ];
                }
            } 
            // 三级章节：X.Y.Z 章节名称（必须先于二级匹配）
            elseif (preg_match('/^(\d+)\.(\d+)\.(\d+)\s+(.+)$/u', $line, $matches)) {
                $chapterNumber = $matches[1];
                $sectionNumber = $chapterNumber . '.' . $matches[2];
                $subSectionNumber = $sectionNumber . '.' . $matches[3];
                
                // 确保章节存在
                if (!isset($chapterMap[$chapterNumber])) {
                    $chapterMap[$chapterNumber] = [
                        'level' => 1,
                        'number' => $chapterNumber,
                        'title' => '第' . $chapterNumber . '章',
                        'children' => [],
                        '_order' => $order++
                    ];
                }
                
                // 查找或创建二级目录
                $sectionIdx = null;
                foreach ($chapterMap[$chapterNumber]['children'] as $idx => $sec) {
                    if ($sec['number'] === $sectionNumber) {
                        $sectionIdx = $idx;
                        break;
                    }
                }
                if ($sectionIdx === null) {
                    $chapterMap[$chapterNumber]['children'][] = [
                        'level' => 2,
                        'number' => $sectionNumber,
                        'title' => $sectionNumber,
                        'children' => []
                    ];
                    $sectionIdx = count($chapterMap[$chapterNumber]['children']) - 1;
                }
                
                // 添加三级目录（去重）
                $subExists = false;
                foreach ($chapterMap[$chapterNumber]['children'][$sectionIdx]['children'] as $sub) {
                    if ($sub['number'] === $subSectionNumber) {
                        $subExists = true;
                        break;
                    }
                }
                if (!$subExists) {
                    $chapterMap[$chapterNumber]['children'][$sectionIdx]['children'][] = [
                        'level' => 3,
                        'number' => $subSectionNumber,
                        'title' => $subSectionNumber . ' ' . trim($matches[4]),
                        'children' => []
                    ];
                }
            } 
            // 二级章节：X.Y 章节名称
            elseif (preg_match('/^(\d+)\.(\d+)\s+(.+)$/u', $line, $matches)) {
                $chapterNumber = $matches[1];
                $sectionNumber = $chapterNumber . '.' . $matches[2];
                
                // 确保章节存在
                if (!isset($chapterMap[$chapterNumber])) {
                    $chapterMap[$chapterNumber] = [
                        'level' => 1,
                        'number' => $chapterNumber,
                        'title' => '第' . $chapterNumber . '章',
                        'children' => [],
                        '_order' => $order++
                    ];
                }
                
                // 检查是否已存在二级目录
                $sectionExists = false;
                foreach ($chapterMap[$chapterNumber]['children'] as $sec) {
                    if ($sec['number'] === $sectionNumber) {
                        $sectionExists = true;
                        break;
                    }
                }
                if (!$sectionExists) {
                    $chapterMap[$chapterNumber]['children'][] = [
                        'level' => 2,
                        'number' => $sectionNumber,
                        'title' => $sectionNumber . ' ' . trim($matches[3]),
                        'children' => []
                    ];
                }
            }
        }
        
        // 按 _order 排序
        usort($chapterMap, function($a, $b) {
            return ($a['_order'] ?? 0) <=> ($b['_order'] ?? 0);
        });
        
        // 移除 _order 字段
        $result = [];
        foreach ($chapterMap as $chapter) {
            unset($chapter['_order']);
            $result[] = $chapter;
        }
        
        return $result;
    }

    /**
     * @notes 递归创建章节及其子章节
     * @param array $chapter
     * @param string $libraryUid
     * @param string $parentUid
     * @param int $tenantId
     * @param array $uidMap
     * @param int $successCount
     * @param int $errorCount
     * @return bool
     * @author 精解析答题
     * @date 2026/03/08
     */
    private static function createChapterWithChildren(
        array $chapter,
        string $libraryUid,
        string $parentUid,
        int $tenantId,
        array &$uidMap,
        int &$successCount,
        int &$errorCount
    ): bool {
        try {
            $chapterNumber = $chapter['number'] ?? '';
            $cacheKey = $libraryUid . '_' . $parentUid . '_' . $chapterNumber;
            
            // 检查当前导入批次中是否已创建过此章节
            if (isset($uidMap[$cacheKey])) {
                $uid = $uidMap[$cacheKey];
                if (!empty($chapter['children'])) {
                    foreach ($chapter['children'] as $child) {
                        if (!empty($child) && is_array($child)) {
                            self::createChapterWithChildren(
                                $child,
                                $libraryUid,
                                $uid,
                                $tenantId,
                                $uidMap,
                                $successCount,
                                $errorCount
                            );
                        }
                    }
                }
                return true;
            }
            
            // 检查数据库中是否已存在相同章节
            $existingChapter = TenantExamChapter::where([
                ['title', '=', $chapter['title']],
                ['parent_uid', '=', $parentUid],
                ['library_uid', '=', $libraryUid],
                ['tenant_id', '=', $tenantId],
                ['delete_time', '=', 0]
            ])->find();
            
            if ($existingChapter) {
                $uid = $existingChapter->uid;
                $uidMap[$cacheKey] = $uid;
                if (!empty($chapter['children'])) {
                    foreach ($chapter['children'] as $child) {
                        if (!empty($child) && is_array($child)) {
                            self::createChapterWithChildren(
                                $child,
                                $libraryUid,
                                $uid,
                                $tenantId,
                                $uidMap,
                                $successCount,
                                $errorCount
                            );
                        }
                    }
                }
                return true;
            }
            
            $uid = uid();
            $model = TenantExamChapter::create([
                'title' => $chapter['title'],
                'is_show' => 1,
                'sort' => 0,
                'uid' => $uid,
                'parent_uid' => $parentUid,
                'tenant_id' => $tenantId,
                'library_uid' => $libraryUid,
            ]);
            
            if ($model->getKey()) {
                $successCount++;
                $uidMap[$cacheKey] = $uid;
                
                if (!empty($chapter['children'])) {
                    foreach ($chapter['children'] as $child) {
                        if (!empty($child) && is_array($child)) {
                            self::createChapterWithChildren(
                                $child,
                                $libraryUid,
                                $uid,
                                $tenantId,
                                $uidMap,
                                $successCount,
                                $errorCount
                            );
                        }
                    }
                }
                return true;
            } else {
                $errorCount++;
                return false;
            }
        } catch (\Exception $e) {
            $errorCount++;
            return false;
        }
    }
}