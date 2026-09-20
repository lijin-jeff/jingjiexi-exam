<?php
declare(strict_types=1);
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 精解析答题系统开发者版权所有，拥有最终解释权。
namespace app\api\logic\exam;

use app\common\logic\BaseLogic;
use app\common\model\exam\TenantExamChapter;
use app\common\model\exam\TenantExamExamination;
use app\common\model\exam\TenantExamLibrary;
use app\common\model\exam\TenantExamQuestionCollection;
use app\common\model\exam\TenantExamQuestionError;
use app\common\model\exam\TenantExamPaper;
use app\common\model\exam\TenantExamQuestion;
use app\common\model\exam\TenantExamExaminationHistory;
use app\common\model\exam\TenantExamQuestionCorrections;
use app\common\model\exam\TenantExamQuestionLike;
use app\common\model\exam\TenantUserChapterStatistics;
use app\common\model\exam\TenantExamKnowledge;
use app\common\model\exam\TenantExamLabel;
use think\facade\Db;


class QuestionlibLogic extends BaseLogic
{

    
    private static function queryWhere(array $params): array
    {
        $where[] = ['is_show', '=', 1];
        if (!empty($params['uid'])) {
            $where[] = ['uid', '=', $params['uid']];
        }
        return $where;
    }

    /**
     * 处理通用参数和构建查询条件
     * @param array $params
     * @return array
     */
    private static function prepareQuestionQuery(array $params): array
    {
        // 处理通用参数
        $libraryUid = $params['uid'] ?? $params['library_uid'] ?? '';
        $userId = $params['user_uid'] ?? null;
        $selecteType = $params['selecte_type'] ?? 1;
        $randomType = $params['random_type'] ?? 2;
        $questionCount = intval($params['question_count'] ?? 0);
        $offset = intval($params['offset'] ?? 0);
        $chapterUid = $params['chapter_uid'] ?? null;
        $analysisQuid = $params['analysis_quid'] ?? null;
        
        // 兼容处理：ratio_params可能是URL编码的JSON字符串或JSON字符串
        $ratioParams = $params['ratio_params'] ?? [];
        if (is_string($ratioParams)) {
            // 尝试解码URL编码的字符串
            $decodedRatioParams = urldecode($ratioParams);
            // 尝试解析JSON字符串
            $parsedRatioParams = json_decode($decodedRatioParams, true);
            if (is_array($parsedRatioParams)) {
                $ratioParams = $parsedRatioParams;
            }
        }
        
        // 兼容处理：exam_type可能是逗号分隔的字符串
        $examType = $params['exam_type'] ?? [];
        if (is_string($examType)) {
            $examType = array_filter(explode(',', $examType), function($val) {
                return $val !== '';
            });
        }
        
        $examLevel = $params['exam_level'] ?? 0;
        
        // 构建章节 ID 列表
        $chapterUids = [];
        if (!empty($chapterUid) && $chapterUid !== 0) {
            $getAllChapterUids = function ($parentUid) use (&$getAllChapterUids) {
                $uids = [$parentUid];
                $children = TenantExamChapter::query()->where([
                    ['parent_uid', '=', $parentUid],
                    ['is_show', '=', 1]
                ])->column('uid');
                foreach ($children as $childUid) {
                    $uids = array_merge($uids, $getAllChapterUids($childUid));
                }
                return $uids;
            };
            $chapterUids = $getAllChapterUids($chapterUid);
        }
        
        // 构建题目 UID 列表（用于未做、已做、错题）
        $optionUid = [];
        $doneQuestionIds = [];
        if (empty($analysisQuid)) {
            switch ($selecteType) {
                case 2:
                    // 类型2：未做题目
                    // 获取已做题目 ID 列表
                    $optionUid = TenantExamExaminationHistory::query()
                        ->where(['user_uid' => $userId])
                        ->field('JSON_EXTRACT(options, "$[*].uid") as uids')
                        ->select()
                        ->map(function($item) {
                            $uids = $item['uids'] ?? '[]';
                            return json_decode($uids, true) ?? [];
                        })
                        ->toArray();
                    $flattened = [];
                    foreach ($optionUid as $subArray) {
                        if (is_array($subArray)) {
                            $flattened = array_merge($flattened, $subArray);
                        }
                    }
                    $optionUid = array_unique($flattened); // 添加去重操作
                    break;
                case 3:
                    // 类型3：已做题目
                    // 获取已做题目 ID 列表
                    $doneQuestionIds = TenantExamExaminationHistory::query()
                        ->where(['user_uid' => $userId])
                        ->field('JSON_EXTRACT(options, "$[*].uid") as uids')
                        ->select()
                        ->map(function($item) {
                            $uids = $item['uids'] ?? '[]';
                            return json_decode($uids, true) ?? [];
                        })
                        ->toArray();
                    $flattened = [];
                    foreach ($doneQuestionIds as $subArray) {
                        if (is_array($subArray)) {
                            $flattened = array_merge($flattened, $subArray);
                        }
                    }
                    $doneQuestionIds = $flattened;
                    break;
                case 4:
                    // 类型4：错题题目
                    // 获取未被消灭的错题 ID 列表
                    $doneQuestionIds = self::getErrorQuestionIds($userId, $params['uid'] ?? null);
                    break;
                default:
                    // 类型1：默认全部题目
                    $doneQuestionIds = [];
                    break;
            }
        } else {
            // 处理 analysis_quid 参数
            if (is_string($analysisQuid)) {
                $doneQuestionIds = explode(',', $analysisQuid);
                $doneQuestionIds = array_filter(array_map('trim', $doneQuestionIds));
            } else if (is_array($analysisQuid)) {
                $doneQuestionIds = $analysisQuid;
            } else {
                $doneQuestionIds = [$analysisQuid];
            }
            if (!is_array($doneQuestionIds)) {
                $doneQuestionIds = [];
            }
        }
        
        // 构建基础查询条件
        $query = Db::name('tenant_exam_question')->where([
            ['is_show', '=', 1],
            ['delete_time', '=', NULL]
        ]);
        
        // 构建题库、章节、类型、难度等查询条件
        // 如果 questions_error=1，不限制 library_uid，因为错题可能来自其他题库
        $questionsError = $params['questions_error'] ?? 0;
        if (!empty($libraryUid) && $questionsError != 1) {
            $query->where('library_uid', '=', $libraryUid);
        }
        if (!empty($chapterUids)) {
            $query->where('chapter_uid', 'IN', $chapterUids);
        }
        if (!empty($examType)) {
            $query->where('exam_type', 'IN', $examType);
        }
        if (!empty($examLevel) && $examLevel !== 0) {
            $query->where('exam_level', '=', $examLevel);
        }
        if ($selecteType == 2) {
            // 类型2：未做题目，查询未做题目
            if (!empty($optionUid)) {
                $query->where('uid', 'NOT IN', $optionUid);
            } else {
                // 如果optionUid为空，则查询所有题目
                // 不添加任何查询条件
            }
        } else if (!empty($optionUid)) {
            $query->where('uid', 'NOT IN', $optionUid);
        }
        if (!empty($doneQuestionIds)) {
            $query->where('uid', 'IN', $doneQuestionIds);
        }
        
        // 构建排序规则
        $sortMethod = $randomType;
        $typeCounts = [];
        
        // 只有当 questions_type=8 且 ratioParams 存在时才按比例获取题目
        if (isset($params['questions_type']) && $params['questions_type'] == 8 && isset($ratioParams) && is_array($ratioParams) && !empty($ratioParams)) {
            $totalQuestionCount =  intval($params['question_count']);
            $questionCount = intval($params['limit']);
            $totalCount = $totalQuestionCount;
            
            // 如果 examType 为空，从 ratioParams 中提取题型列表
            if (empty($examType)) {
                $examType = array_keys($ratioParams);
            }
            
            // 先从数据库中查询各个题型的实际题目数量
            $typeQuestionCounts = [];
            foreach ($examType as $type) {
                $count = Db::name('tenant_exam_question')
                    ->where([
                        ['library_uid', '=', $libraryUid],
                        ['is_show', '=', 1],
                        ['delete_time', '=', null],
                        ['exam_type', '=', $type]
                    ])
                    ->when(!empty($chapterUids), function ($query) use ($chapterUids) {
                        $query->where('chapter_uid', 'IN', $chapterUids);
                    })
                    ->count();
                $typeQuestionCounts[$type] = $count;
            }
            
            // 计算各题型理论值
            $idealCounts = [];
            foreach ($ratioParams as $type => $percentage) {
                $idealCounts[$type] = (int)round($totalCount * $percentage / 100);
            }
            
            // 计算题库实际总题数
            $totalAvailable = array_sum($typeQuestionCounts);
            $actualTotal = min($totalCount, $totalAvailable);
            
            // 按优先级分配：从 type 1 开始，不足的往后补
            $sortedTypes = array_keys($ratioParams);
            $remainingNeeded = $actualTotal;
            
            foreach ($sortedTypes as $type) {
                if ($remainingNeeded <= 0) {
                    $typeCounts[$type] = 0;
                    continue;
                }
                
                $available = $typeQuestionCounts[$type] ?? 0;
                $ideal = $idealCounts[$type] ?? 0;
                
                // 按优先级分配：优先分配理想值，不足的从后续题型补
                $allocated = min($ideal, $available);
                $typeCounts[$type] = $allocated;
                $remainingNeeded -= $allocated;
            }
            
            // 第二轮：补齐差额（从有剩余的题型继续分配，不限制理论值）
            if ($remainingNeeded > 0) {
                foreach ($sortedTypes as $type) {
                    if ($remainingNeeded <= 0) break;
                    
                    $allocated = $typeCounts[$type] ?? 0;
                    $available = $typeQuestionCounts[$type] ?? 0;
                    
                    // 从还有剩余的题型分配，不限制理论值
                    $canAdd = min($remainingNeeded, $available - $allocated);
                    if ($canAdd > 0) {
                        $typeCounts[$type] += $canAdd;
                        $remainingNeeded -= $canAdd;
                    }
                }
            }
        }
        
        // 返回结果
        return [
            'query' => $query,
            'params' => [
                'libraryUid' => $libraryUid,
                'userId' => $userId,
                'selecteType' => $selecteType,
                'randomType' => $randomType,
                'questionCount' => $questionCount,
                'offset' => $offset,
                'chapterUids' => $chapterUids,
                'optionUid' => $optionUid,
                'doneQuestionIds' => $doneQuestionIds,
                'ratioParams' => $ratioParams,
                'examType' => $examType,
                'examLevel' => $examLevel
            ],
            'sortMethod' => $sortMethod,
            'typeCounts' => $typeCounts
        ];
    }

    /**
     * 题库详情
     * @param array $params
     * @return array
     * @date 2025/5/3 01:23
     * @author 精解析答题
     */
    public static function questionLibDetail(array $params): array
    {
        $questionLibrary = TenantExamLibrary::query()->where(self::queryWhere($params))
            ->append(['question_count'])
            ->field(['uid', 'title', 'create_time', 'author', 'image'])
            ->findOrEmpty();
        if ($questionLibrary->isEmpty()) return [];
        $questionLibrary = $questionLibrary->toArray();
        $questionLibrary['create_time'] = date('Y-m-d', strtotime($questionLibrary['create_time']));
        return $questionLibrary;
    }

    /**
     * 随机练习试题列表
     * @param array $params
     * @return array
     * @date 2025/5/3 23:27
     * @author 精解析答题
     */
    public static function randOptionList(array $params): array
    {
        $items = TenantExamQuestion::query()->where([
            ['library_uid', '=', $params['uid']],
            ['is_show', '=', 1]
        ])->field(['uid', 'library_uid', 'title', 'answer', 'option', 'analysis','commentaries' , 'exam_type', 'exam_level', 'total_attempts', 'total_correct', 'accuracy_rate', 'easy_mistakes'])
            ->append(['exam_type_name', 'answer_count', 'correct_rate', 'wrong_option'])
            ->orderRaw('RAND()')
            ->limit((int)$params['question_count'])
            ->order('sort desc, id desc')
            ->select()
            ->toArray();
        foreach ($items as &$item) {
            $item['status'] = 'default';
            $item['is_selected'] = false;
            foreach ($item['option'] as &$option) {
                $option['is_selected'] = false;
                $option['status'] = 'default';
            }
        }

        // 添加参数验证和异常处理
    try {
        // 检查用户ID是否有效
        $userId = intval($params['user_uid']) ?? 0;
        $libraryUid = $params['library_uid'];
        // 检查题目列表是否有效
        if (is_array($items) && !empty($items)) {
            $items = self::questionIsCollection($userId, $libraryUid, $items);
            $items = self::questionIsLike($userId, $libraryUid, $items);
        }
    } catch (\Exception $e) {
        // 记录异常
        \think\facade\Log::error('随机练习试题列表失败: ' . $e->getMessage());
    }

    return is_array($items) ? $items : [];
    }

    /**
     * 试题搜索列表
     * @param array $params
     * @return array
     * @date 2025/5/3 23:27
     * @author 精解析答题
     */
    public static function searchOptionList(array $params): array
    {
        try {
            // 参数验证和安全处理
            if (empty($params['uid'])) {
                return [
                    'lists'     => [],
                    'page_no'   => 1,
                    'page_size' => 20,
                    'count'     => 0,
                    'extend'    => []
                ];
            }
            
            // 安全处理分页参数
            $pageNo = (int)(isset($params['page_no']) && is_numeric($params['page_no']) ? $params['page_no'] : 1);
            $pageSize = (int)(isset($params['page_size']) && is_numeric($params['page_size']) ? $params['page_size'] : 20);
            $pageSize = min($pageSize, 20); // 限制最大页大小，防止恶意请求
            $pageNo = max($pageNo, 1); // 确保页码至少为1
            
            // 安全处理关键词
            $keywords = isset($params['keywords']) ? trim($params['keywords']) : '';
            
            // 构建查询条件
            $queryParams = [
                ['library_uid', '=', $params['uid']],
            ];
            if (empty($params['is_edit'])) {
                $queryParams[] = ['is_show', '=', 1]; // 只查询显示的试题
            }
           if (!empty($params['chapter_uid'])) {
                $queryParams[] = ['chapter_uid', '=', $params['chapter_uid']];
            }
            if (!empty($params['knowledge_uid'])) {
                $queryParams[] = ['knowledge_uid', '=', $params['knowledge_uid']];
            }
            if (!empty($params['exam_type'])) {
                $queryParams[] = ['exam_type', '=', $params['exam_type']];
            }
            if (!empty($params['exam_level'])) {
                $queryParams[] = ['exam_level', '=', $params['exam_level']];
            }
            
            $query = TenantExamQuestion::query()
                ->where($queryParams)
                ->whereNull('delete_time')
                ->where(function ($query) use ($keywords) {
                    if (!empty($keywords)) {
                        // 使用参数绑定防止SQL注入
                        $query->whereLike('title', '%' . $keywords . '%')
                              ->whereOr('analysis', 'like', '%' . $keywords . '%')
                              ->whereOr('commentaries', 'like', '%' . $keywords . '%')
                              ->whereOr('option', 'like', '%' . $keywords . '%')
                              ->whereOr('answer', 'like', '%' . $keywords . '%');
                    }
                })
                // 预加载关联数据，优化性能
                ->with(['chapter'])
                ->field([
                    'uid', 
                    'library_uid', 
                    'title', 
                    'answer', 
                    'option', 
                    'analysis',
                    'commentaries', 
                    'exam_type', 
                    'exam_level', 
                    'score',
                    'chapter_uid',
                    'knowledge_uid',
                    'label_uid',
                    'sort',
                    'create_time',
                    'update_time',
                    'collect_count',
                    'like_count',
                    'is_show',
                    'total_attempts',
                    'total_correct',
                    'accuracy_rate'
                ]);
            
            // 处理排序
            if (!empty($params['sort_by'])) {
                $sortBy = $params['sort_by'];
                switch ($sortBy) {
                    case 'collect_count':
                        $query->order('collect_count desc');// 收藏数降序
                        break;
                    case 'like_count':
                        $query->order('like_count desc');// 点赞数降序
                        break;
                    case 'do_count':
                        $query->order('total_attempts desc');// 总做题人次降序
                        break;
                    case 'correct_count':
                        $query->order('total_correct desc');// 总正确次数降序
                        break;
                    case 'correct_rate':
                        $query->order('accuracy_rate desc');// 正确率降序
                        break;
                    case 'default':
                    default:
                        $query->order('update_time desc');// 默认按更新时间排序
                        break;
                }
            } else {
                // 默认按更新时间排序
                $query->order('update_time desc');
            }
            
            $query->append(['exam_type_name', 'label_data','Knowledge']);
            
            // 使用paginate获取分页数据
            $paginator = $query->paginate([
                'list_rows' => $pageSize,
                'page'      => $pageNo
            ]);
            
            // 获取分页数据
            $items = $paginator->items();
            $items = is_array($items) ? $items : [];
            
            // 如果没有数据，直接返回
            if (empty($items)) {
                return [
                    'lists'     => [],
                    'page_no'   => $pageNo,
                    'page_size' => $pageSize,
                    'count'     => 0,
                    'extend'    => []
                ];
            }
            
            // 获取问题ID列表
            $questionIds = array_column($items, 'uid');
            
            // 处理收藏状态和收藏数量
            $collectData = [];
            $collectCountData = [];
            if (isset($params['user_uid']) && !empty($params['user_uid']) && is_numeric($params['user_uid'])) {
                // 获取用户收藏的问题ID
                $collectIds = TenantExamQuestionCollection::where([
                    'user_uid' => $params['user_uid'], 
                    'library_uid' => $params['uid']
                ])->whereNull('delete_time')
                ->whereIn('question_uid', $questionIds)
                ->column('question_uid');
                
                $collectData = array_flip($collectIds);
            }
            
            
            
            // 处理点赞状态和点赞数量
            $likeData = [];
            $likeCountData = [];
            if (isset($params['user_uid']) && !empty($params['user_uid']) && is_numeric($params['user_uid'])) {
                // 获取用户点赞的问题ID
                $likeIds = TenantExamQuestionLike::where([
                    'user_uid' => $params['user_uid'], 
                    'library_uid' => $params['uid']
                ])->whereNull('delete_time')
                ->whereIn('question_uid', $questionIds)
                ->column('question_uid');
                
                $likeData = array_flip($likeIds);
            }
            
            // 处理数据格式，确保与前端匹配
            foreach ($items as &$item) {
                // 确保option是数组格式
                if (!empty($item['option']) && is_string($item['option'])) {
                    $item['option'] = json_decode($item['option'], true);
                } elseif (empty($item['option'])) {
                    $item['option'] = [];
                }
                
                // 确保knowledge是数组格式
                if (empty($item['knowledge'])) {
                    $item['knowledge'] = [];
                } elseif (!is_array($item['knowledge'])) {
                    $item['knowledge'] = [$item['knowledge']];
                }
                
                // 处理收藏状态
                $item['is_collection'] = isset($collectData[$item['uid']]);
                
                // 处理点赞状态
                $item['is_like'] = isset($likeData[$item['uid']]);
                
                // 处理章节信息，确保格式一致
                if (empty($item['chapter'])) {
                    $item['chapter'] = new \stdClass();
                }
                
                // 确保answer是字符串格式
                if (is_array($item['answer'])) {
                    $item['answer'] = json_encode($item['answer'], JSON_UNESCAPED_UNICODE);
                }
            }
            
            return [
                'lists'     => $items,
                'page_no'   => $pageNo,
                'page_size' => $pageSize,
                'count'     => $paginator->total(),
                'extend'    => []
            ];
        } catch (\PDOException $e) {
            // 记录数据库错误
            \think\facade\Log::error('搜索试题列表数据库错误: ' . $e->getMessage() . '\nStack trace: ' . $e->getTraceAsString());
            
            // 返回默认数据，避免暴露数据库错误信息
            return [
                'lists'     => [],
                'page_no'   => 1,
                'page_size' => 20,
                'count'     => 0,
                'extend'    => []
            ];
        } catch (\Exception $e) {
            // 记录其他错误
            \think\facade\Log::error('搜索试题列表失败: ' . $e->getMessage() . '\nStack trace: ' . $e->getTraceAsString());
            
            // 返回默认数据
            return [
                'lists'     => [],
                'page_no'   => 1,
                'page_size' => 20,
                'count'     => 0,
                'extend'    => []
            ];
        }
    }

    /**
     * 试题列表
     * @param array $params
     * @return array
     * @date 2025/5/3 23:27
     * @author 精解析答题
     */
    public static function orderOptionList(array $params): array
    {
        try {

            // 使用prepareQuestionQuery方法处理参数和构建查询条件
            $result = self::prepareQuestionQuery($params);
            $processedParams = $result['params'];
            $randomType = $processedParams['randomType'];
            
            $libraryUid = $processedParams['libraryUid'];
            $userId = $processedParams['userId'];
            $offset = $processedParams['offset'];
            $questionCount = $processedParams['questionCount'];
            
            // 检查是否需要按比例获取题目
            $items = [];
            $typeCounts = $result['typeCounts'];
            if (!empty($typeCounts)) {
                // 按比例获取题目
                foreach ($typeCounts as $type => $count) {
                    if ($count <= 0) continue;
                    
                    $typeParams = $params;
                    $typeParams['exam_type'] = [$type];
                    
                    // 使用prepareQuestionQuery方法处理每种题型的参数
                    $typeResult = self::prepareQuestionQuery($typeParams);
                    $typeQuery = $typeResult['query'];
                    
                    $typeItems = $typeQuery
                        ->field(['uid', 'library_uid', 'title', 'answer', 'option', 'analysis','commentaries' , 'exam_type', 'exam_level','integral', 'score', 'chapter_uid', 'knowledge_uid', 'label_uid', 'total_attempts', 'total_correct', 'accuracy_rate', 'easy_mistakes','like_count','collect_count'])
                        ->append(['exam_type_name', 'Knowledge', 'label_data', 'chapter', 'answer_count', 'correct_rate', 'wrong_option'])
                        ->when(
                            $randomType == 1,
                            function ($query) {
                                $query->orderRaw('RAND()');
                            },
                            function ($query) {
                                $query->order('sort', 'desc');
                            }
                        )
                        ->limit($count)
                        ->select()
                        ->toArray();
                    
                    $items = array_merge($items, $typeItems);
                }
                
                // 排序处理
                if ($randomType == 1) {
                    // 随机模式：使用固定的随机种子，确保相同参数下返回相同顺序
                    $seed = crc32(serialize($params));
                    srand($seed);
                    shuffle($items);
                    srand(); // 重置随机种子
                } else {
                    // 非随机模式：按题型排序，同题型内按sort降序排序，sort相同时按id降序排序
                    usort($items, function ($a, $b) {
                        // 先按题型排序
                        if ($a['exam_type'] != $b['exam_type']) {
                            return $a['exam_type'] <=> $b['exam_type'];
                        }
                        // 再按sort字段降序排序
                        if (($b['sort'] ?? 0) != ($a['sort'] ?? 0)) {
                            return ($b['sort'] ?? 0) <=> ($a['sort'] ?? 0);
                        }
                        // sort相同时按id字段降序排序
                        return ($b['id'] ?? 0) <=> ($a['id'] ?? 0);
                    });
                }
                
                // 应用offset和limit参数
                if ($offset > 0 || $questionCount > 0) {
                    $items = array_slice($items, $offset, $questionCount);
                }
            } else {
                // 使用prepareQuestionQuery方法获取查询条件
                $query = $result['query'];
                
                // 传统方式获取题目
                $items = $query
                    ->field(['uid', 'library_uid', 'title', 'answer', 'option', 'analysis','commentaries' , 'exam_type', 'exam_level','integral', 'score', 'chapter_uid', 'knowledge_uid', 'label_uid', 'total_attempts', 'total_correct', 'accuracy_rate', 'easy_mistakes','like_count','collect_count'])
                    ->append(['exam_type_name', 'Knowledge', 'label_data', 'chapter', 'answer_count', 'correct_rate', 'wrong_option'])
                    ->when(
                        $randomType == 1,
                        function ($query) {
                            // 随机模式：完全随机排序
                            $query->orderRaw('RAND()');
                        },
                        function ($query) {
                            // 非随机模式：严格按题型排序（exam_type 升序），同题型内按 sort 降序
                            $query->order('exam_type', 'asc')->order('sort', 'desc');
                        }
                    )
                    ->limit($offset, $questionCount)
                    ->select()
                    ->toArray();
                
            }
            foreach ($items as &$item) {
                $item['status'] = 'default';
                $item['is_selected'] = false;
                // 处理answer将["A", "B", "D"]，转换为为A、B、D
                if (isset($item['answer'])) {
                    if (!empty($item['answer']) && is_array($item['answer'])) {
                        $item['answer_str'] = implode('、', $item['answer']);
                    } else {
                        $item['answer_str'] = json_decode($item['answer'], true);
                        $item['answer_str'] = is_array($item['answer_str']) ? implode('、', $item['answer_str']) : $item['answer'];
                    }
                } else {
                    $item['answer_str'] = '';
                }

                // 检查option是否存在，避免Undefined array key 'option'错误
                if (isset($item['option'])) {
                    // 解码option字段，如果是JSON字符串
                    if (!is_array($item['option'])) {
                        $decodedOption = json_decode($item['option'], true);
                        if (is_array($decodedOption)) {
                            $item['option'] = $decodedOption;
                        } else {
                            $item['option'] = [];
                        }
                    }
                    // 为每个选项设置默认值
                    foreach ($item['option'] as &$option) {
                        $option['is_selected'] = false;
                        $option['status'] = 'default';
                    }
                } else {
                    $item['option'] = [];
                }
            }

            // 添加参数验证和异常处理
            try {
              
                // 检查题目列表是否有效
                if (is_array($items) && !empty($items)) {
                    $items = self::questionIsCollection($userId, $libraryUid, $items);
                    $items = self::questionIsLike($userId, $libraryUid, $items);
                }
            
            } catch (\Exception $e) {
                // 记录异常
                \think\facade\Log::error('试题列表失败: ' . $e->getMessage());
            }
            

            return $items;
        } catch (\Exception $e) {
            // 记录详细错误信息用于调试
            \think\facade\Log::error('orderOptionList方法执行失败: ' . $e->getMessage() . '\nStack trace: ' . $e->getTraceAsString());
            
            // 返回友好的错误信息给前端
            return [
                'code' => 0,
                'msg' => '服务器处理请求时发生错误，请稍后重试',
                'data' => []
            ];
        }
    }

    /**
     * 试题收藏列表
     * @param array $params
     * @return array
     * @author 精解析答题
     */
    public static function collectionOptionList(array $params): array
    {
        try {
            // 获取分页参数
            $pageNo = isset($params['page_no']) ? (int)$params['page_no'] : 1;
            $pageSize = isset($params['page_size']) ? (int)$params['page_size'] : 20;
            $offset = ($pageNo - 1) * $pageSize;
            
            // 参数验证
            if (empty($params['uid']) || empty($params['user_uid'])) {
                return [
                    'list' => [],
                    'total' => 0,
                    'page_no' => $pageNo,
                    'page_size' => $pageSize
                ];
            }

            //排序条件，包括收藏时间、题型、难度
            $orderField = 'update_time';
            $orderDirection = 'desc';
            if (isset($params['order_field']) && in_array($params['order_field'], ['update_time', 'exam_type', 'exam_level'])) {
                $orderField = $params['order_field'];
            }
            if (isset($params['order_direction']) && in_array($params['order_direction'], ['asc', 'desc'])) {
                $orderDirection = $params['order_direction'];
            }

            // 获取用户收藏的试题UID
            $items = TenantExamQuestionCollection::query()->where([
                    ['library_uid', '=', $params['uid']],
                    ['user_uid', '=', $params['user_uid']]
                ])
                ->whereNull('delete_time')
                ->order($orderField, $orderDirection)
                ->limit($offset, $pageSize)
                ->select()
                ->toArray();
            
            if (empty($items)) {
                return [
                    'list' => [],
                    'total' => 0,
                    'page_no' => $pageNo,
                    'page_size' => $pageSize
                ];
            }
            // 获取总数（根据数组长度）
            $total = is_array($items) ? count($items) : 0;
        
            // 返回标准格式的结果
            return [
                'list' => $items,
                'total' => $total,
                'page_no' => $pageNo,
                'page_size' => $pageSize
            ];
        } catch (\Exception $e) {
            \think\facade\Log::error('获取收藏试题列表失败: ' . $e->getMessage());
            return [
                'list' => [],
                'total' => 0,
                'page_no' => isset($params['page_no']) ? (int)$params['page_no'] : 1,
                'page_size' => isset($params['page_size']) ? (int)$params['page_size'] : 20
            ];
        }
    }

    /**
     * 历史错题记录
     * @param array $params
     * @return array
     * @author 精解析答题
     */
    public static function errorOptionList(array $params): array
    {
        try {
            // 获取分页参数
            $pageNo = isset($params['page_no']) ? (int)$params['page_no'] : 1;
            $pageSize = isset($params['page_size']) ? (int)$params['page_size'] : 20;
            
            // 参数验证
            if (empty($params['uid'])) {
                return [
                    'list' => [],
                    'total' => 0,
                    'page_no' => $pageNo,
                    'page_size' => $pageSize,
                    'total_page' => 0
                ];
            }
            
            // 控制器已经设置了user_uid，不需要检查是否为空
            // 控制器会从当前登录用户获取user_id并设置到params中
            
            // 构建查询条件
            $query = TenantExamQuestionError::query()
                ->where([
                    ['library_uid', '=', $params['uid']],
                    ['user_uid', '=', $params['user_uid']],
                ])
                ->whereNull('delete_time');
            
            // 添加筛选条件
            // 做题类型筛选
            if (!empty($params['questions_type'])) {
                $query->where('questions_type', '=', $params['questions_type']);
            }
            
            // 题型筛选
            if (!empty($params['exam_type'])) {
                $query->where('exam_type', '=', $params['exam_type']);
            }
            
            // 章节筛选
            if (!empty($params['chapter_uid'])) {
                $query->where('chapter_uid', '=', $params['chapter_uid']);
            }
            
            // 消灭状态筛选
            if (isset($params['eliminated_status'])) {
                $query->where('eliminated_status', '=', $params['eliminated_status']);
            }
            
            // 高频错题筛选
            if (isset($params['is_high_frequency']) && $params['is_high_frequency'] == 1) {
                $query->where('error_count', '>=', 2);
            }
            
            // 获取总记录数
            $total = $query->count();
            
            // 执行分页查询
            // 添加排序条件
            $orderField = isset($params['order_field']) ? $params['order_field'] : '';
            $orderDirection = isset($params['order_direction']) ? $params['order_direction'] : 'desc';
            
            // 允许的排序字段
            $allowedFields = ['error_count', 'update_time', 'exam_type', 'exam_level', 'create_time'];
            
            // 如果是高频错题请求且没有指定排序字段，默认按错误次数降序
            if (isset($params['is_high_frequency']) && $params['is_high_frequency'] == 1 && empty($orderField)) {
                $query->order('error_count', 'desc');
            } 
            // 如果指定了排序字段且字段有效，则按指定字段排序
            elseif (!empty($orderField) && in_array($orderField, $allowedFields)) {
                $query->order($orderField, $orderDirection);
            }
            // 默认排序
            $query->order(['create_time desc', 'id desc']);
            
            $errorList = $query
                ->field([
                    'id', 'question_uid', 'user_uid', 'library_uid',
                    'eliminated_status', 'eliminated_at', 'eliminated_by',
                    'questions_type', 'exam_type', 'chapter_uid',
                    'title', 'exam_level', 'score', 'user_answer', 'correct_answer',
                    'practice_mode', 'examination_uid', 'create_time', 'update_time',
                    'error_count', 'is_high_frequency'  // 添加错误次数和高频标记字段
                ])
                ->page($pageNo, $pageSize)
                ->select()
                ->toArray();
            
            // 处理返回数据，添加题型名称等额外信息
            foreach ($errorList as &$item) {
                // 转换时间戳为日期格式，确保参数是整数
                $item['create_time'] = is_numeric($item['create_time']) ? date('Y-m-d H:i:s', (int)$item['create_time']) : $item['create_time'];
                $item['update_time'] = is_numeric($item['update_time']) ? date('Y-m-d H:i:s', (int)$item['update_time']) : $item['update_time'];
                
                // eliminated_at 已经是日期格式，不需要转换
                if ($item['eliminated_at']) {
                    $item['eliminated_at'] = $item['eliminated_at'];
                }
                
                // 根据exam_type设置题型名称,从TenantDictData中获取
                $examTypeMap = [
                    1 => '单选题',
                    2 => '多选题',
                    3 => '判断题',
                    4 => '填空题',
                    5 => '问答题',
                    6 => '案例题'
                ];
                $item['exam_type_name'] = $examTypeMap[$item['exam_type']] ?? '未知题型';
                
                // 根据level设置难度名称
                $levelMap = [
                    1 => '简单',
                    2 => '中等',
                    3 => '困难'
                ];
                $item['exam_level_name'] = $levelMap[$item['exam_level']] ?? '未知难度';
            }
            
            // 构建返回结果
            return [
                'list' => $errorList,
                'total' => $total,
                'page_no' => $pageNo,
                'page_size' => $pageSize,
                'total_page' => ceil($total / $pageSize)
            ];
        } catch (\Throwable $e) {
            // 记录错误日志
            \think\facade\Log::error('获取错题列表失败: ' . $e->getMessage() . '\nStack trace: ' . $e->getTraceAsString());
            return [
                'list' => [],
                'total' => 0,
                'page_no' => isset($params['page_no']) ? (int)$params['page_no'] : 1,
                'page_size' => isset($params['page_size']) ? (int)$params['page_size'] : 20,
                'total_page' => 0
            ];
        }
    }

    /**
     * 章节练习试题列表
     * @param array $params
     * @return array
     * @date 2025/5/3 23:27
     * @author 精解析答题
     */
    public static function chapterOptionList(array $params): array
    {
        $items = TenantExamQuestion::query()->where([
            ['library_uid', '=', $params['uid']],
            ['chapter_uid', '=', $params['chapter_uid']],
            ['is_show', '=', 1]
        ])->field(['uid', 'library_uid', 'title', 'answer', 'option', 'analysis','commentaries' , 'exam_type', 'exam_level', 'total_attempts', 'total_correct', 'accuracy_rate', 'easy_mistakes'])
            ->append(['exam_type_name', 'answer_count', 'correct_rate', 'wrong_option'])
            ->order('sort desc, id desc')
            ->select()
            ->toArray();
        foreach ($items as &$item) {
            $item['status'] = 'default';
            $item['is_selected'] = false;
            foreach ($item['option'] as &$option) {
                $option['is_selected'] = false;
                $option['status'] = 'default';
            }
        }

        // 检查用户ID是否有效
        $userId = intval($params['user_uid']) ?? 0;
        $libraryUid = $params['uid'];
        // 检查题目列表是否有效
        if (is_array($items) && !empty($items)) {
            $items = self::questionIsCollection($userId, $libraryUid, $items);
            $items = self::questionIsLike($userId, $libraryUid, $items);
        }
    

        return is_array($items) ? $items : [];
    }

    /**
     * 题型练习试题列表
     * @param array $params
     * @return array
     * @date 2025/5/3 23:27
     * @author 精解析答题
     */
    public static function typeOptionList(array $params): array
    {
        $items = TenantExamQuestion::query()->where([
            ['library_uid', '=', $params['uid']],
            ['is_show', '=', 1]
        ])->where(function ($query) use ($params) {
            if (!empty($params['type'])) {
                $query->where('exam_type', '=', $params['type']);
            }
            if (!empty($params['exam_level'])) {
                $query->where('exam_level', '=', $params['exam_level']);
            }
        })->field(['uid', 'library_uid', 'title', 'answer', 'option', 'analysis','commentaries', 'exam_type', 'exam_level', 'total_attempts', 'total_correct', 'accuracy_rate', 'easy_mistakes'])
            ->append(['exam_type_name', 'answer_count', 'correct_rate', 'wrong_option'])
            ->order('sort desc, id desc')
            ->select()
            ->toArray();
        foreach ($items as &$item) {
            $item['status'] = 'default';
            $item['is_selected'] = false;
            foreach ($item['option'] as &$option) {
                $option['is_selected'] = false;
                $option['status'] = 'default';
            }
        }

        // 检查用户ID是否有效
        $userId = intval($params['user_uid']) ?? 0;
        $libraryUid = $params['uid'];
        // 检查题目列表是否有效
        if (is_array($items) && !empty($items)) {
            $items = self::questionIsCollection($userId, $libraryUid, $items);
            $items = self::questionIsLike($userId, $libraryUid, $items);
        }
    

        return is_array($items) ? $items : [];
    }


    /**
     * 题库类型列表
     * @param array $prams
     * @return array
     * @date 2025/5/10 02:13
     * @author 精解析答题
     */
    public static function questionTypeList(array $params): array
    {
        $examType = Db::name('tenant_dict_data')->where([
            ['type_value', '=', 'exam_type'],
            ['tenant_id', '=', request()->tenantId],
            ['status', '=', 1],
            ['delete_time', '=', NULL]
        ])->order('sort desc')->column(['name', 'value']);
        $examLevel = Db::name('tenant_dict_data')->where([
            ['type_value', '=', 'exam_level'],
            ['tenant_id', '=', request()->tenantId],
            ['status', '=', 1],
            ['delete_time', '=', NULL]
        ])->order('sort desc')->column(['name', 'value']);
        foreach ($examType as &$value) {
            $value['exam_count'] = TenantExamQuestion::query()->where([
                ['library_uid', '=', $params['uid']],
                ['is_show', '=', 1],
                ['exam_type', '=', $value['value']]
            ])->count();
        }
        foreach ($examLevel as &$value) {
            $value['exam_count'] = TenantExamQuestion::query()->where([
                ['library_uid', '=', $params['uid']],
                ['is_show', '=', 1],
                ['exam_level', '=', $value['value']]
            ])->count();
        }
        return [
            'exam_type_list'  => $examType,
            'exam_level_list' => $examLevel
        ];
    }

    /**
     * 试题章节
     * @param array $params
     * @return array
     * @date 2025/5/10 03:19
     * @author 精解析答题
     */
    public static function chapterList(array $params): array
    {
        // 定义递归关联查询函数
        $withRecursive = function ($query) use (&$withRecursive) {
            $query->with(['children' => $withRecursive]);
        };

        try {
          $items = TenantExamChapter::where([
                    ['library_uid', '=', $params['uid']],
                    ['parent_uid', '=', 0],
                    ['is_show', '=', 1]
                ])
                ->field(['uid', 'title', 'parent_uid'])
                ->with(["children" => $withRecursive])
                ->order(['sort' => 'desc', 'id' => 'asc'])
                ->select()
                ->toArray();
        } catch (\Exception $e) {
            \think\facade\Log::error('获取题库章节列表失败: ' . $e->getMessage());
            $items = [];
        }

        // 递归处理所有级别的子章节，添加exam_count和total_count
        $updateChapterCount = function (&$chapters) use ($params, &$updateChapterCount) {
            foreach ($chapters as &$chapter) {
                // 统计当前章节的题目数量
                $chapterCount = TenantExamQuestion::where([
                    ['chapter_uid', '=', $chapter['uid']],
                    ['library_uid', '=', $params['uid']],
                    ['is_show', '=', 1],
                    ['delete_time', '=', NULL]
                ])->count('id');
                
                $chapter['exam_count'] = $chapterCount;
                $chapter['total_count'] = $chapterCount;
                $chapter['expanded'] = false;
                
                // 递归处理子章节
                if (isset($chapter['children']) && !empty($chapter['children'])) {
                    $updateChapterCount($chapter['children']);
                    
                    // 累加所有子章节的题目数量到当前章节的total_count
                    foreach ($chapter['children'] as $child) {
                        $chapter['total_count'] += $child['total_count'];
                    }
                }
            }
        };
        
        // 处理所有章节
        $updateChapterCount($items);
        
        // 从新的统计表中获取用户章节数据（优化性能）
        if (isset($params['user_uid']) && !empty($params['user_uid'])) {
            $userUid = $params['user_uid'];
            $tenantId = $params['tenant_id'] ?? 0;
            
            // 从统计表获取数据
            $statsMap = self::getUserChapterStatistics([
                'uid' => $params['uid'],
                'user_uid' => $userUid,
                'tenant_id' => $tenantId
            ]);
            
            // 递归更新章节统计数据，并汇总子章节统计
            $updateChapterStats = function (&$chapters) use (&$updateChapterStats, $statsMap) {
                foreach ($chapters as &$chapter) {
                    // 先递归处理子章节（从下往上计算）
                    if (isset($chapter['children']) && !empty($chapter['children'])) {
                        $updateChapterStats($chapter['children']);
                    }
                    
                    // 如果统计表中有该章节数据，使用统计表数据
                    if (isset($statsMap[$chapter['uid']])) {
                        $chapter['done_count'] = $statsMap[$chapter['uid']]['done_count'];
                        $chapter['accuracy'] = $statsMap[$chapter['uid']]['accuracy'];
                    } else {
                        $chapter['done_count'] = 0;
                        $chapter['accuracy'] = 0;
                    }
                    
                    // 汇总子章节的统计数据
                    if (isset($chapter['children']) && !empty($chapter['children'])) {
                        $totalDoneCount = $chapter['done_count'];  // 当前章节的已答题数
                        $totalCorrectCount = 0;  // 累计正确题数
                        
                        // 如果当前章节有统计数据，计算正确题数
                        if ($chapter['done_count'] > 0 && $chapter['accuracy'] > 0) {
                            $totalCorrectCount = (int)round($chapter['done_count'] * $chapter['accuracy'] / 100);
                        }
                        
                        // 累加所有子章节的统计数据
                        foreach ($chapter['children'] as $child) {
                            $totalDoneCount += $child['done_count'];
                            
                            // 计算子章节的正确题数
                            if ($child['done_count'] > 0 && $child['accuracy'] > 0) {
                                $totalCorrectCount += (int)round($child['done_count'] * $child['accuracy'] / 100);
                            }
                        }
                        
                        // 更新父章节的统计数据
                        $chapter['done_count'] = $totalDoneCount;
                        
                        // 计算加权平均正确率
                        if ($totalDoneCount > 0) {
                            $chapter['accuracy'] = round(($totalCorrectCount / $totalDoneCount) * 100, 1);
                        } else {
                            $chapter['accuracy'] = 0;
                        }
                    }
                }
            };
            
            // 处理所有章节
            $updateChapterStats($items);
        }
        
        return $items;
    }

    /**
     * 题库试题收藏
     * @param array $params
     * @return bool
     * @author 精解析答题
     */
    public static function questionCollection(array $params): bool
    {
        $action = $params['action'];
        $where = [
            'library_uid'  => $params['library_uid'],
            'user_uid'     => $params['user_uid'],
            'question_uid' => $params['question_uid']
        ];
        $row = 0;
        try {
            switch ($action) {
                case 1: // 收藏
                    // 先检查是否存在任何记录（无论是否删除）
                    $existing = TenantExamQuestionCollection::withTrashed()->where($where)->find();
                    if ($existing) {
                        if ($existing->trashed()) {
                            // 存在已删除的记录，使用restore方法恢复
                            $row = $existing->restore();
                            // 恢复成功后，增加题目收藏数
                            if ($row) {
                                TenantExamQuestion::where('uid', $params['question_uid'])->inc('collect_count')->update();
                            }
                        } else {
                            // 已经收藏，直接返回成功
                            $row = 1;
                        }
                    } else {
                        // 不存在记录，创建新记录
                        $model = TenantExamQuestionCollection::create([
                            'library_uid'     => $params['library_uid'],
                            'user_uid'        => $params['user_uid'],
                            'question_uid'    => $params['question_uid'],
                            'exam_type'       => $params['exam_type'],
                            'exam_type_name'  => $params['exam_type_name'],
                            'chapter_uid'     => $params['chapter_uid'],
                            'title'           => $params['title'],
                            'exam_level'           => $params['exam_level'],
                            'score'           => $params['score'],
                            'correct_answer'  => $params['correct_answer'],
                        ]);
                        $row = $model ? $model->getKey() : 0;
                        // 创建成功后，增加题目收藏数
                        if ($row) {
                            TenantExamQuestion::where('uid', $params['question_uid'])->inc('collect_count')->update();
                        }
                    }
                    break;
                case 2: // 取消收藏
                    // 只取消未删除的收藏，使用模型实例进行操作
                    $existing = TenantExamQuestionCollection::where($where)->find();
                    if ($existing) {
                        // 使用delete方法进行软删除
                        $row = $existing->delete();
                        // 删除成功后，减少题目收藏数
                        if ($row) {
                            TenantExamQuestion::where('uid', $params['question_uid'])
                                ->where('collect_count', '>', 0)
                                ->dec('collect_count')
                                ->update();
                        }
                    } else {
                        // 没有找到收藏记录，直接返回成功
                        $row = 1;
                    }
                    break;
            }
        } catch (\Exception $exception) {
            self::setError($exception->getMessage());
            // 记录错误日志
            \think\facade\Log::error('收藏操作失败: ' . $exception->getMessage());
        }
        return $row > 0;
    }


   /**
     * 题库试题点赞
     * @param array $params
     * @return bool
     * @author 精解析答题
    */
    public static function questionLike(array $params): bool
    {
        // 检查必填参数
        if (!isset($params['library_uid']) || !isset($params['user_uid']) || !isset($params['question_uid']) || !isset($params['action'])) {
            self::setError('缺少必填参数');
            return false;
        }
        
        $action = $params['action'];
        $where = [
            ['library_uid', '=', $params['library_uid']],
            ['user_uid', '=', $params['user_uid']],
            ['question_uid', '=', $params['question_uid']]
            // 不再需要手动筛选delete_time，框架会自动处理
        ];
        $row = 0;
        try {
            switch ($action) {
                case 2: // 取消点赞
                    // 只取消未删除的点赞，使用模型实例进行软删除
                    $existing = TenantExamQuestionLike::where($where)->find();
                    if ($existing) {
                        $row = $existing->delete();
                        // 删除成功后，减少题目点赞数
                        if ($row) {
                            TenantExamQuestion::where('uid', $params['question_uid'])
                                ->where('like_count', '>', 0)
                                ->dec('like_count')
                                ->update();
                        }
                    } else {
                        // 没有找到点赞记录，直接返回成功
                        $row = 1;
                    }
                    break;
                case 1: // 点赞
                    // 先检查是否存在任何记录（无论是否删除）
                    $existing = TenantExamQuestionLike::withTrashed()->where($where)->find();
                    if ($existing) {
                        if ($existing->trashed()) {
                            // 存在已删除的记录，使用restore方法恢复
                            $row = $existing->restore();
                            // 恢复成功后，增加题目点赞数
                            if ($row) {
                                TenantExamQuestion::where('uid', $params['question_uid'])->inc('like_count')->update();
                            }
                        } else {
                            // 已经点赞，直接返回成功
                            $row = 1;
                        }
                    } else {
                        // 不存在记录，创建新记录
                        $model = TenantExamQuestionLike::create([
                            'library_uid'  => $params['library_uid'],
                            'user_uid'     => $params['user_uid'],
                            'question_uid' => $params['question_uid']
                            // 不再需要手动设置delete_time，框架会自动处理
                        ]);
                        $row = $model ? $model->getKey() : 0;
                        // 创建成功后，增加题目点赞数
                        if ($row) {
                            TenantExamQuestion::where('uid', $params['question_uid'])->inc('like_count')->update();
                        }
                    }
                    break;
                default:
                    self::setError('无效的操作类型');
                    return false;
            }
        } catch (\Exception $exception) {
            self::setError($exception->getMessage());
            return false;
        }
        return $row > 0;
    }

    /**
     * 移除模拟考试错题
     * @param array $params
     * @return bool
     * @author 精解析答题
     */
    public static function questionRemoveError(array $params): bool
    {
        $row = 0;
        try {
            // 查询题目信息，获取exam_type和chapter_uid
            $questionInfo = TenantExamQuestion::query()
                ->where('uid', $params['question_uid'])
                ->field(['uid', 'exam_type', 'chapter_uid', 'library_uid'])
                ->find()?->toArray() ?? [];
            
            // 记录日志
            \think\facade\Log::info('questionRemoveError - 题目信息: ' . json_encode([
                'question_uid' => $params['question_uid'],
                'exam_type' => $questionInfo['exam_type'] ?? null,
                'chapter_uid' => $questionInfo['chapter_uid'] ?? null
            ]));
            
            $model = TenantExamQuestionError::create([
                'library_uid'  => $params['library_uid'],
                'user_uid'     => $params['user_uid'],
                'question_uid' => $params['question_uid'],
                'exam_type'    => $questionInfo['exam_type'] ?? null ?: 0, // 修复：添加exam_type字段
                'chapter_uid'  => $questionInfo['chapter_uid'] ?? '', // 添加chapter_uid字段
            ]);
            if (!empty($model->getKey())) {
                $row = $model->getKey();
            }
        } catch (\Exception $exception) {
            self::setError($exception->getMessage());
        }
        return $row > 0;
    }

    /**
     * 试卷试题列表
     * @param array $params
     * @return array
     * @author 精解析答题
     */
    public static function examinationQuestionList(array $params): array
    {
        $paper = TenantExamExamination::query()->where([['uid', '=', $params['uid']], ['is_show', '=', 1]])->field('paper_uid,exam_time')->findOrEmpty();
        // 记录日志
        \think\facade\Log::info('examinationQuestionList - 试卷信息: ' . json_encode([
            'paper_uid' => $paper['paper_uid'] ?? null,
            'exam_time' => $paper['exam_time'] ?? null
        ]));
        if (empty($paper)) return [];
        $questionList = TenantExamPaper::query()->where([
            ['uid', '=', $paper['paper_uid']],
            ['is_show', '=', 1]
        ])->field(['uid','option_content'])->findOrEmpty();
        if ($questionList->isEmpty()) return [];
        $option = json_decode($questionList->toArray()['option_content'], true);
        foreach ($option as &$value) {
            $value['status'] = 'default';
            $value['exam_type_name'] = $value['exam_type_text'];
            $value['is_selected'] = false;
            unset($value['analysis'], $value['exam_type_text']);
            foreach ($value['option'] as &$v) {
                $v['is_selected'] = false;
                $v['status'] = 'default';
                $v['is_check'] = false;
            }
        }
        return [
            'list' => $option,
            'config'    => ['exam_time' => $paper['exam_time']],
        ];
    }

    /**
     * 题库菜单
     * @param array $params
     * @return array

     * @date 2025/5/3 01:49
     * @author 精解析答题
     */
    public static function questionMenu(array $params): array
    {
        // $questionLibrary = TenantExamLibrary::query()->where('category_uid', '=', $params['uid'],'is_show', '=', 1)->whereNull('delete_time')->findOrEmpty();
        // if ($questionLibrary->isEmpty()) return [];
        $questionLibrary['uid'] = $params['uid'];
        $questionCount = TenantExamQuestion::query()->where([['library_uid', '=', $questionLibrary['uid']], ['is_show', '=', 1]])->whereNull('delete_time')->count(); 
        $menuList = [
            [
                'title'     => '开始练习',
                'desc'      => '共计' . $questionCount . '题',
                'icon'      => 'edit-form',
                'iconColor' => '#4a90e2',
                // 'url'       => '/subpages/exam/questionOrder?uid=' . $params['uid'] . '&questions_type=8',
                'url'       => '/subpages/exam/questionSetting?uid='.$questionLibrary['uid'].'&questions_type=8&question_count='.$questionCount,
                'questions_type'      => 8
            ],
            [
                'title'     => '章节练习',
                'desc'      => '章节定项练习',
                'icon'      => 'level',
                'iconColor' => '#7ed321',
                'url'       => '/subpages/examChapter/questionChapter?uid=' . $questionLibrary['uid'] . '&questions_type=1',
                'questions_type'      => 1
            ],
            [
                'title'     => '模拟考试',
                'desc'      => '仿真模拟',
                'icon'      => 'platform',
                'iconColor' => '#e94e77',
                'url'       => '/subpages/examMn/questionMnSetting?uid=' . $questionLibrary['uid'] . '&questions_type=7',
                'questions_type'      => 7
            ],
            [
                'title'     => '试题搜索',
                'desc'      => '精准检索试题',
                'icon'      => 'search',
                'iconColor' => '#f5a623',
                'url'       => '/subpages/examOther/questionSearch?uid=' . $questionLibrary['uid'] ,
            ],
            [
                'title'     => '我的错题',
                'desc'      => '查看我的错题',
                'icon'      => 'warning',
                'iconColor' => '#E72F8C',
                'url'       => '/subpages/examError/questionError?uid=' . $questionLibrary['uid'] . '&questions_type=5',
                'type'      => 5
            ],
            [
                'title'     => '我的收藏',
                'desc'      => '查看我的收藏',
                'icon'      => 'star-fill',
                'iconColor' => '#E72F8C',
                'url'       => '/subpages/examCollection/questionCollection?uid=' . $questionLibrary['uid'] . '&questions_type=6',
                'type'      => 6
            ]
        ];
        return $menuList;
    }

    /**
     * 已选择的题数
     * @param array $params
     * @return int
     * @author 精解析答题
     */
    public static function selectedQuestionCount(array $params): int
    {
        // 快速返回：如果没有提供题库UID，直接返回0
        if (empty($params['uid'])) {
            \think\facade\Log::info('没有提供题库UID，直接返回0');
            return 0;
        }
        // 移除严格的user_uid检查，改为在各个分支中处理
        $hasUserUid = !empty($params['user_uid']);
        
        // 处理exam_type参数，将字符串转换为数组
        if (!empty($params['exam_type']) && is_string($params['exam_type'])) {
            $params['exam_type'] = array_filter(explode(',', $params['exam_type']), function($val) {
                return $val !== '';
            });
        }
        
        // 获取章节ID列表（包括指定章节及其所有子类章节）
        $chapterUids = [];
        if (!empty($params['chapter_uid']) && $params['chapter_uid'] !== 0) {
            // 定义递归函数获取所有子章节ID
            $getAllChapterUids = function ($parentUid) use (&$getAllChapterUids) {
                $uids = [$parentUid];
                $children = TenantExamChapter::query()->where([
                    ['parent_uid', '=', $parentUid],
                    ['is_show', '=', 1]
                ])->column('uid');
                foreach ($children as $childUid) {
                    $uids = array_merge($uids, $getAllChapterUids($childUid));
                }
                return $uids;
            };
            $chapterUids = $getAllChapterUids($params['chapter_uid']);
        }
        
        // 构建基础查询条件
        $where = [
            ['library_uid', '=', $params['uid']],
            ['is_show', '=', 1], // 只统计显示状态的题目
        ];
        
        // 类型，
        // 1：全部题目：直接从题库TenantExamQuestion中返回题目数量
        // 2：未做题目：在题库TenantExamQuestion中统计做题记录TenantExamExamination的字段"options"中没有的题目数量
        // 3：已做题目：在题库TenantExamQuestion中统计做题记录TenantExamExamination的字段"options"中有的题目数量
        // 4：错题题目：在题库TenantExamQuestion中统计做题记录TenantExamExamination的字段"submit_option"中有且“error_option_uid”字段中有的题目数量
        // 根据不同类型获取题目数量
        switch ($params['selecte_type']) {
            case 1:
                // 类型1：全部题目：直接从题库TenantExamQuestion中返回题目数量
                $query = TenantExamQuestion::query()->where($where)->where(function ($query) use ($params, $chapterUids) {
                    if (!empty($params['exam_type'])) {
                        $query->where('exam_type', 'IN', $params['exam_type']);
                    }
                    if (!empty($params['exam_level'])  && $params['exam_level'] !== 0) {
                        $query->where('exam_level', '=', $params['exam_level']);
                    }
                    if (!empty($chapterUids)) {
                        $query->where('chapter_uid', 'IN', $chapterUids);
                    }
                });
                $count = $query->count();
                
                break;
                
            case 2:
                // 类型2：未做题目
                // 如果没有用户UID，返回所有符合条件的题目数量
                if (!$hasUserUid) {
                    $query = TenantExamQuestion::query()->where($where)->where(function ($query) use ($params, $chapterUids) {
                        if (!empty($params['exam_type'])) {
                            $query->where('exam_type', 'IN', $params['exam_type']);
                        }
                        if (!empty($params['exam_level']) && $params['exam_level'] !== 0) {
                            $query->where('exam_level', '=', $params['exam_level']);
                        }
                        if (!empty($chapterUids)) {
                            $query->where('chapter_uid', 'IN', $chapterUids);
                        }
                    });
                    $count = $query->count();
                    break;
                }
                
                // 1. 获取已做题目ID列表
                $doneQuestionIds = [];
                try {
                    $historyRecords = TenantExamExaminationHistory::query()
                        ->where([
                            ['library_uid', '=', $params['uid']],
                            ['user_uid', '=', $params['user_uid']]
                        ])
                        ->field('options')
                        ->select()
                        ->toArray();
                    
                    // 遍历历史记录，解析options中的uid
                    foreach ($historyRecords as $record) {
                        $options = $record['options'] ?? '[]';
                        if (is_string($options)) {
                            $parsedOptions = json_decode($options, true);
                            if (is_array($parsedOptions)) {
                                foreach ($parsedOptions as $option) {
                                    if (isset($option['uid'])) {
                                        $doneQuestionIds[] = $option['uid'];
                                    }
                                }
                            }
                        }
                    }
                    
                    // 去重
                    $doneQuestionIds = array_unique($doneQuestionIds);
                } catch (\Exception $e) {
                    \think\facade\Log::error('获取已做题目ID列表失败: ' . $e->getMessage());
                    // 出错时返回空数组，避免影响后续查询
                    $doneQuestionIds = [];
                }
                
                // 2. 构建查询并应用过滤条件
                $query = TenantExamQuestion::query()->where($where)->where(function ($query) use ($params, $chapterUids) {
                    if (!empty($params['exam_type'])) {
                        $query->where('exam_type', 'IN', $params['exam_type']);
                    }
                    if (!empty($params['exam_level']) && $params['exam_level'] !== 0) {
                        $query->where('exam_level', '=', $params['exam_level']);
                    }
                    if (!empty($chapterUids)) {
                        $query->where('chapter_uid', 'IN', $chapterUids);
                    }
                });
                
                // 3. 如果有已做题目，则排除它们
                if (!empty($doneQuestionIds)) {
                    $query->whereNotIn('uid', $doneQuestionIds);
                }
                
                // 4. 统计数量
                $count = $query->count();

                break;
                
            case 3:
                // 类型3：已做题目
                // 如果没有用户UID，直接返回0
                if (!$hasUserUid) {
                    $count = 0;
                    break;
                }
                
                // 1. 获取已做题目ID列表
                $doneQuestionIds = [];
                try {
                    $historyRecords = TenantExamExaminationHistory::query()
                        ->where([
                            ['library_uid', '=', $params['uid']],
                            ['user_uid', '=', $params['user_uid']]
                        ])
                        ->field('options')
                        ->select()
                        ->toArray();
                    
                    // 遍历历史记录，解析options中的uid
                    foreach ($historyRecords as $record) {
                        $options = $record['options'] ?? '[]';
                        if (is_string($options)) {
                            $parsedOptions = json_decode($options, true);
                            if (is_array($parsedOptions)) {
                                foreach ($parsedOptions as $option) {
                                    if (isset($option['uid'])) {
                                        $doneQuestionIds[] = $option['uid'];
                                    }
                                }
                            }
                        }
                    }
                    
                    // 去重
                    $doneQuestionIds = array_unique($doneQuestionIds);
                } catch (\Exception $e) {
                    \think\facade\Log::error('获取已做题目ID列表失败: ' . $e->getMessage());
                    // 出错时返回空数组，避免影响后续查询
                    $doneQuestionIds = [];
                }
                
                // 2. 构建查询并应用过滤条件
                $query = TenantExamQuestion::query()->where($where)->where(function ($query) use ($params, $chapterUids) {
                    if (!empty($params['exam_type'])) {
                        $query->where('exam_type', 'IN', $params['exam_type']);
                    }
                    if (!empty($params['exam_level']) && $params['exam_level'] !== 0) {
                        $query->where('exam_level', '=', $params['exam_level']);
                    }
                    if (!empty($chapterUids)) {
                        $query->where('chapter_uid', 'IN', $chapterUids);
                    }
                });
                
                // 3. 如果有已做题目，则查询这些题目
                if (!empty($doneQuestionIds)) {
                    $query->whereIn('uid', $doneQuestionIds);
                } else {
                    // 如果没有已做题目，直接返回0
                    $count = 0;
                    break;
                }
                
                // 4. 统计数量
                $count = $query->count();

                break;
                
            case 4:
                // 类型4：错题题目
                // 如果没有用户UID，直接返回0
                if (!$hasUserUid) {
                    $count = 0;
                    break;
                }
                
                // 1. 获取未被消灭的错题ID列表
                try {
                    $optionUid = self::getErrorQuestionIds((int)$params['user_uid'], $params['uid']);
                } catch (\Exception $e) {
                    \think\facade\Log::error('获取错题ID列表失败: ' . $e->getMessage());
                }
                
                if (empty($optionUid)) {
                    $count = 0;
                    break;
                }
                // 2. 构建查询并应用过滤条件
                $query = TenantExamQuestion::query()->where($where)->where(function ($query) use ($params, $chapterUids) {
                    if (!empty($params['exam_type'])) {
                        $query->where('exam_type', 'IN', $params['exam_type']);
                    }
                    if (!empty($params['exam_level']) && $params['exam_level'] !== 0) {
                        $query->where('exam_level', '=', $params['exam_level']);
                    }
                    if (!empty($chapterUids)) {
                        $query->where('chapter_uid', 'IN', $chapterUids);
                    }
                });
                // 3. 如果有错题，则查询这些题目
                if (!empty($optionUid)) {
                    $query->whereIn('uid', $optionUid);
                }
                
                // 4. 统计数量
                $count = $query->count();
                break;
                
            default:
                // 默认返回0
                $count = 0;
                break;
        }
        
        return $count;
    }
    
    /**
     * 纠错添加
     * @param array $params
     * @return bool
     * @author 精解析答题
     */
    public static function addErrorCorrect(array $params): bool
    {
        $model = new TenantExamQuestionCorrections();
        //通过user_id查询用户信息
        $userInfo = \app\common\model\user\User::where('id', $params['user_id'])->field('id,nickname')->find();
        if (!$userInfo) {
            self::setError('用户不存在');
            return false;
        }
        //通过question_uid查询题目信息
        $questionInfo = \app\common\model\exam\TenantExamQuestion::where('uid', $params['question_uid'])->field('uid,title,exam_type')->find();
        if (!$questionInfo) {
            self::setError('题目不存在');
            return false;
        }
        $params['question_uid'] = $questionInfo['uid'];
        $params['question_name'] = $questionInfo['title'];
        $params['exam_type'] = $questionInfo['exam_type'];
        $params['user_id'] = $userInfo['id'];
        $params['user_nickname'] = $userInfo['nickname'];
        $model->data($params, true);
        if (!$model->save()) {
            self::setError($model->getError());
            return false;
        }
        return true;
    }

    /**
     * 查询是否收藏
     * @param int $userId 用户ID
     * @param array $items 题目列表
     * @return array 处理后的题目列表
     * @date 2025/5/10 02:13
     * @author 精解析答题
     */
    public static function questionIsCollection(int $userId, string $libraryUid, array $items): array
    {
        // 添加日志记录
        // 快速返回：如果题目列表为空，直接返回
        if (empty($items)) {
            \think\facade\Log::info('题目列表为空，直接返回');
            return $items;
        } 
        
        // 提取所有题目的uid，过滤掉没有uid的项
        $questionIds = [];
        foreach ($items as $item) {
            if (isset($item['uid'])) {
                $questionIds[] = $item['uid'];
            }
        }
        
        // 如果没有有效的题目ID，直接返回原列表
        if (empty($questionIds)) {
            return $items;
        }
        
        try {
            // 获取题库ID
            $libraryUid = $libraryUid;
            
            // 如果没有有效的题库ID，直接返回原列表
            if (empty($libraryUid)) {
                return $items;
            }
            
            // 查询用户收藏的题目ID
            $collectIds = TenantExamQuestionCollection::where([
                'user_uid' => $userId,
                'library_uid' => $libraryUid,
            ])
            ->whereIn('question_uid', $questionIds)
            ->whereNull('delete_time')
            ->column('question_uid');
            
            // 确保$collectIds是数组
            if (!is_array($collectIds)) {
                $collectIds = [];
            }

            // 为每个题目添加是否收藏的标记、收藏数量和收藏用户列表
            foreach ($items as &$item) {
                // 检查uid字段是否存在
                if (isset($item['uid'])) {
                    $item['is_collection'] = in_array($item['uid'], $collectIds);
                } else {
                    $item['is_collection'] = false;
                }
            }
        } catch (\Exception $e) {
            // 记录异常，但不影响主流程
            \think\facade\Log::error('查询题目收藏状态失败: ' . $e->getMessage());
            
            // 出错时确保每个题目都有默认值
            foreach ($items as &$item) {
                $item['is_collection'] = false;
            }
        }
        
        return $items;
    }
    
    /**
     * 查询是否点赞
     * @param int $userId 用户ID
     * @param array $items 题目列表
     * @return array 处理后的题目列表
     * @date 2025/5/10 02:13
     * @author 精解析答题
     */
    public static function questionIsLike(int $userId, string $libraryUid, array $items): array
    {
        // 添加日志记录
        // 快速返回：如果题目列表为空，直接返回
        if (empty($items)) {
            \think\facade\Log::info('题目列表为空，直接返回');
            return $items;
        } 
        
        // 提取所有题目的uid，过滤掉没有uid的项
        $questionIds = [];
        foreach ($items as $item) {
            if (isset($item['uid'])) {
                $questionIds[] = $item['uid'];
            }
        }
        
        // 如果没有有效的题目ID，直接返回原列表
        if (empty($questionIds)) {
            return $items;
        }
        
        try {
            // 获取题库ID
            $libraryUid = $libraryUid;
            
            // 如果没有有效的题库ID，直接返回原列表
            if (empty($libraryUid)) {
                return $items;
            }
            // 查询用户点赞的题目ID
            $likeIds = TenantExamQuestionLike::where([
                'user_uid' => $userId,
                'library_uid' => $libraryUid,
            ])
            ->whereIn('question_uid', $questionIds)
            ->whereNull('delete_time')
            ->column('question_uid');
            // 确保$likeIds是数组
            if (!is_array($likeIds)) {
                $likeIds = [];
            }
            


            // 为每个题目添加是否点赞的标记、点赞数量和点赞用户列表
            foreach ($items as &$item) {
                // 检查uid字段是否存在
                $item['is_like'] = isset($item['uid']) ? in_array($item['uid'], $likeIds) : false;
            }
        } catch (\Exception $e) {
            // 记录异常，但不影响主流程
            \think\facade\Log::error('查询题目点赞状态失败: ' . $e->getMessage());
            
            // 出错时确保每个题目都有默认值
            foreach ($items as &$item) {
                $item['is_like'] = false;
            }
        }
        
        return $items;
    }
    
    /**
     * 消灭错题
     * @param array $params 请求参数
     * @return array 处理后的题目列表
     * @date 2025/5/10 02:13
     * @author 精解析答题
     */
    public static function eliminateError(array $params): bool
    {
        // 添加日志记录
        \think\facade\Log::info('消灭错题请求参数: ' . json_encode($params));
        
        // 提取参数
        $uid = $params['uid'] ?? '';
        $questionUid = $params['question_uid'] ?? '';
        $userUid = $params['user_uid'] ?? '';
        
        // 验证参数是否为空
        if (empty($uid) || empty($questionUid) || empty($userUid)) {
            self::setError('参数不能为空');
            return false;
        }
        
        // 验证题目是否存在
        $question = TenantExamQuestion::where('uid', $questionUid)->find();
        if (!$question) {
            self::setError('题目不存在');
            return false;
        }
        
        // 查询用户当前的消灭状态
        $currentStatus = Db::name('tenant_exam_question_error_eliminate_log')
            ->where([
                'library_uid' => $uid,
                'user_uid' => $userUid,
                'question_uid' => $questionUid
            ])
            ->order('create_time', 'desc')
            ->value('new_status');
        
        // 如果已经是已消灭状态，直接返回成功
        if ($currentStatus === 1) {
            return true;
        }
        
        try {
            // 记录错题消灭日志
            Db::name('tenant_exam_question_error_eliminate_log')->insert([
                'library_uid' => $uid,
                'user_uid' => $userUid,
                'question_uid' => $questionUid,
                'old_status' => $currentStatus ?? 0,
                'new_status' => 1, // 1表示已消灭
                'create_time' => date('Y-m-d H:i:s'),
                'eliminated_by' => 'manual', // 手动操作
                'ip' => request()->ip()
            ]);
            
            return true;
        } catch (\Exception $e) {
            \think\facade\Log::error('消灭错题失败: ' . $e->getMessage());
            self::setError('操作失败: ' . $e->getMessage());
            return false;
        }
    }
    /**
     * 取消错题已消灭状态
     * @param array $params 请求参数
     * @return array 处理后的题目列表
     * @date 2025/5/10 02:13
     * @author 精解析答题
     */
    public static function restoreError(array $params): bool
    {
        // 添加日志记录
        \think\facade\Log::info('取消消灭错题请求参数: ' . json_encode($params));
        
        // 提取参数
        $uid = $params['uid'] ?? '';
        $questionUid = $params['question_uid'] ?? '';
        $userUid = $params['user_uid'] ?? '';
        
        // 验证参数是否为空
        if (empty($uid) || empty($questionUid) || empty($userUid)) {
            self::setError('参数不能为空');
            return false;
        }
        
        if (!$userUid) {
            self::setError('用户不存在');
            return false;
        }
        
        // 验证题目是否存在
        $question = TenantExamQuestion::where('uid', $questionUid)->find();
        if (!$question) {
            self::setError('题目不存在');
            return false;
        }
        
        // 查询用户当前的消灭状态
        $currentStatus = Db::name('tenant_exam_question_error_eliminate_log')
            ->where([
                'library_uid' => $uid,
                'user_uid' => $userUid,
                'question_uid' => $questionUid
            ])
            ->order('create_time', 'desc')
            ->value('new_status');
        
        // 如果已经是未消灭状态，直接返回成功
        if ($currentStatus === 0 || $currentStatus === null) {
            return true;
        }
        
        try {
            // 记录取消消灭日志
            Db::name('tenant_exam_question_error_eliminate_log')->insert([
                'library_uid' => $uid,
                'user_uid' => $userUid,
                'question_uid' => $questionUid,
                'old_status' => $currentStatus,
                'new_status' => 0, // 0表示未消灭
                'create_time' => date('Y-m-d H:i:s'),
                'eliminated_by' => 'manual', // 手动操作
                'ip' => request()->ip()
            ]);
            
            return true;
        } catch (\Exception $e) {
            \think\facade\Log::error('取消消灭错题失败: ' . $e->getMessage());
            self::setError('操作失败: ' . $e->getMessage());
            return false;
        }
    }

    //获取错题统计数据
    public static function errorStats(array $params): array
    {

        // 提取参数
        $uid = $params['uid'] ?? '';
        $userUid = $params['user_uid'] ?? '';
        
        // 验证参数是否为空
        if (empty($uid) || empty($userUid)) {
            self::setError('参数不能为空');
            return [
                'total_errors' => 0,
                'eliminated_errors' => 0,
            ];
        }
        
        try {
            // 构建基础查询条件
            $baseWhere1 = [
                ['library_uid', '=', $uid],
                ['user_uid', '=', $userUid],
                ['delete_time', '=', null],
            ];
            
            // 使用聚合函数一次查询获取所有统计数据，提高性能
            $stats = TenantExamQuestionError::query()
                ->where($baseWhere1)
                ->field('COUNT(*) as total_errors, SUM(CASE WHEN eliminated_status = 1 THEN 1 ELSE 0 END) as eliminated_errors')
                ->find();
            
            // 记录原始统计结果
            
            // 确保stats不为null且包含必要字段
            $stats = $stats ?? [];
            $total_errors = (int)($stats['total_errors'] ?? 0);
            $eliminated_errors = (int)($stats['eliminated_errors'] ?? 0);
            
            
            // 1. 优化：一次性查询所有题型统计数据，减少数据库查询次数
            $baseWhere = [
                ['library_uid', '=', $uid],
                ['user_uid', '=', $userUid],
                ['delete_time', '=', null],
                ['eliminated_status', '=', 0]
            ];
            $typeStatsData = TenantExamQuestionError::query()
                ->where($baseWhere)
                ->field('exam_type,GROUP_CONCAT(question_uid) as question_uid_list, COUNT(*) as type_total_errors, SUM(CASE WHEN eliminated_status = 1 THEN 1 ELSE 0 END) as type_eliminated_errors')
                ->group('exam_type')
                ->select()
                ->toArray();
            
            // 转换为以exam_type为键的关联数组，便于后续处理
            $typeStatsMap = [];
            foreach ($typeStatsData as $item) {
                $typeStatsMap[$item['exam_type']] = $item;
            }
            
            // 获取所有题型列表
            $questionTypeResult = self::questionTypeList(['uid' => $uid]);
            $questionTypes = $questionTypeResult['exam_type_list'] ?? [];
            
            // 构建最终的题型统计数据结构
            $typeStats = [];
            foreach ($questionTypes as $type) {
                // 确保type数组包含value和name字段
                if (!isset($type['value']) || !isset($type['name'])) {
                    continue;
                }
                
                $typeId = $type['value'];
                // 获取当前题型的统计数据，如果不存在则使用默认值
                $typeStatItem = $typeStatsMap[$typeId] ?? [];
                $typeTotalErrors = (int)($typeStatItem['type_total_errors'] ?? 0);
                $typeEliminatedErrors = (int)($typeStatItem['type_eliminated_errors'] ?? 0);
                
                $typeStats[] = [
                    'exam_type' => $typeId,
                    'question_uid' => $typeStatItem['question_uid_list'] ?? '',
                    'exam_type_name' => $type['name'],
                    'total_errors' => $typeTotalErrors,
                    'eliminated_errors' => $typeEliminatedErrors,
                ];
            }
            
            // 2. 优化：一次性查询所有章节统计数据，减少数据库查询次数
            $chapterStatsData = TenantExamQuestionError::query()
                ->where($baseWhere)
                ->field('chapter_uid,GROUP_CONCAT(question_uid) as question_uid_list, COUNT(*) as chapter_total_errors, SUM(CASE WHEN eliminated_status = 1 THEN 1 ELSE 0 END) as chapter_eliminated_errors')
                ->group('chapter_uid')
                ->select()
                ->toArray();
            
            // 转换为以chapter_uid为键的关联数组，便于后续处理
            $chapterStatsMap = [];
            foreach ($chapterStatsData as $item) {
                $chapterStatsMap[$item['chapter_uid']] = $item;
            }
            
            // 获取所有章节列表
            $chapters = self::chapterList(['uid' => $uid]) ?? [];
            
            // 构建最终的章节统计数据结构
            $chapterStats = [];
            
            // 递归遍历所有章节（包括子章节）
            $processChapter = function ($chapterList) use (&$processChapter, &$chapterStats, $chapterStatsMap) {
                foreach ($chapterList as $chapter) {
                    // 确保chapter数组包含uid和title字段
                    if (!isset($chapter['uid']) || !isset($chapter['title'])) {
                        continue;
                    }
                    
                    $chapterId = $chapter['uid'];
                    // 获取当前章节的统计数据，如果不存在则使用默认值
                    $chapterStatItem = $chapterStatsMap[$chapterId] ?? [];
                    $chapterTotalErrors = (int)($chapterStatItem['chapter_total_errors'] ?? 0);
                    $chapterEliminatedErrors = (int)($chapterStatItem['chapter_eliminated_errors'] ?? 0);
                    
                    $chapterStats[] = [
                    'chapter_uid' => $chapterId,
                    'question_uid' => $chapterStatItem['question_uid_list'] ?? '',
                    'chapter_parent_uid' => $chapter['parent_uid'],
                    'chapter_name' => $chapter['title'],
                    'total_errors' => $chapterTotalErrors,
                    'eliminated_errors' => $chapterEliminatedErrors,
                ];
                    
                    // 递归处理子章节
                    if (isset($chapter['children']) && !empty($chapter['children'])) {
                        $processChapter($chapter['children']);
                    }
                }
            };
            
            $processChapter($chapters);
            
            return [
                'total_errors' => $total_errors, // 总错题数
                'eliminated_errors' => $eliminated_errors, // 已消灭错题数
                'type_stats' => $typeStats, // 按题型的错题统计数据
                'chapter_stats' => $chapterStats, // 按章节的错题统计数据
            ];
        } catch (\Exception $e) {
            file_put_contents(runtime_path() . 'errorStats_debug.log', date('Y-m-d H:i:s') . ' - 异常: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
            \think\facade\Log::error('获取错题统计数据失败: ' . $e->getMessage());
            return [
                'total_errors' => 0,
                'eliminated_errors' => 0,
                'type_stats' => [],
                'chapter_stats' => []
            ];
        }
    }
   
    //获取收藏统计数据

    public static function collectStats(array $params): array
    {
        // 添加日志记录
        \think\facade\Log::info('获取收藏统计数据请求参数: ' . json_encode($params));
        
        // 提取参数
        $uid = $params['uid'] ?? '';
        $userUid = $params['user_uid'] ?? '';
        
        // 验证参数是否为空
        if (empty($uid) || empty($userUid)) {
            self::setError('参数不能为空');
            return [
                'total_collects' => 0,
                'recent_collects' => 0,
            ];
        }
        
        try {
            // 使用聚合函数一次查询获取所有统计数据，提高性能
            $stats = TenantExamQuestionCollection::query()
                ->where([
                    ['library_uid', '=', $uid],
                    ['user_uid', '=', $userUid]
                ])
                ->whereNull('delete_time')
                ->field('COUNT(*) as total_collects')
                ->find();
            
            // 确保stats不为null且包含必要字段
            $stats = $stats ?? [];
            $result['total_collects'] = (int)($stats['total_collects'] ?? 0);
            
            // 统计最近30天的收藏数
            $recentTime = time() - 30 * 24 * 3600; // 30天前的时间戳
            $recentStats = TenantExamQuestionCollection::query()
                ->where('library_uid', '=', $uid)
                ->where('user_uid', '=', $userUid)
                ->where('delete_time', '=', null)
                ->where('create_time', '>=', $recentTime)
                ->count();
            $result['recent_collects'] = (int)$recentStats;
            
            // 1. 优化：获取所有题型列表并统计每个题型的收藏数
            // 先获取所有题型列表
            $questionTypeResult = self::questionTypeList(['uid' => $uid]);
            $questionTypes = $questionTypeResult['exam_type_list'] ?? [];
            
            // 从收藏表获取题型统计数据
            $typeStatsData = TenantExamQuestionCollection::query()
                ->where('library_uid', '=', $uid)
                ->where('user_uid', '=', $userUid)
                ->whereNotNull('exam_type')
                ->whereNull('delete_time')
                ->field('exam_type, exam_type_name, GROUP_CONCAT(question_uid) as question_uid, COUNT(*) as total_collects')
                ->group('exam_type, exam_type_name')
                ->select()
                ->toArray();
            
            // 转换为以exam_type为键的关联数组，便于后续处理
            $typeStatsMap = [];
            foreach ($typeStatsData as $item) {
                $typeStatsMap[$item['exam_type']] = $item;
            }
            
            // 构建最终的题型统计数据结构，确保所有题型都存在
            $typeStats = [];
            foreach ($questionTypes as $type) {
                // 确保type数组包含value和name字段
                if (!isset($type['value']) || !isset($type['name'])) {
                    continue;
                }
                
                $typeId = $type['value'];
                // 获取当前题型的统计数据，如果不存在则使用默认值
                $typeStatItem = $typeStatsMap[$typeId] ?? [];
                
                $typeStats[] = [
                    'exam_type' => $typeId,
                    'question_uid' => $typeStatItem['question_uid'] ?? '',
                    'exam_type_name' => $type['name'],
                    'total_collects' => (int)($typeStatItem['total_collects'] ?? 0),
                ];
            }
            
            // 2. 优化：获取所有章节列表并统计每个章节的收藏数
            // 从收藏表获取章节统计数据
            $chapterStatsData = TenantExamQuestionCollection::query()
                ->where('library_uid', '=', $uid)
                ->where('user_uid', '=', $userUid)
                ->whereNotNull('chapter_uid')
                ->whereNull('delete_time')
                ->field('chapter_uid,GROUP_CONCAT(question_uid) as question_uid_list, COUNT(*) as chapter_total_collects')
                ->group('chapter_uid')
                ->select()
                ->toArray();
            
            // 转换为以chapter_uid为键的关联数组，便于后续处理
            $chapterStatsMap = [];
            foreach ($chapterStatsData as $item) {
                $chapterStatsMap[$item['chapter_uid']] = $item;
            }
            
            // 获取所有章节列表
            $chapters = self::chapterList(['uid' => $uid]) ?? [];
            
            // 构建最终的章节统计数据结构，确保所有章节都存在
            $chapterStats = [];
            
            // 递归遍历所有章节（包括子章节）
            $processChapter = function ($chapterList) use (&$processChapter, &$chapterStats, $chapterStatsMap) {
                foreach ($chapterList as $chapter) {
                    // 确保chapter数组包含uid和title字段
                    if (!isset($chapter['uid']) || !isset($chapter['title'])) {
                        continue;
                    }
                    
                    $chapterId = $chapter['uid'];
                    // 获取当前章节的统计数据，如果不存在则使用默认值
                    $chapterStatItem = $chapterStatsMap[$chapterId] ?? [];
                    $chapterTotalCollects = (int)($chapterStatItem['chapter_total_collects'] ?? 0);
                    
                    $chapterStats[] = [
                        'chapter_uid' => $chapterId,
                        'question_uid' => $chapterStatItem['question_uid_list'] ?? '',
                        'chapter_parent_uid' => $chapter['parent_uid'] ?? '',
                        'chapter_name' => $chapter['title'],
                        'total_collects' => $chapterTotalCollects,
                    ];
                    
                    // 递归处理子章节
                    if (isset($chapter['children']) && is_array($chapter['children']) && !empty($chapter['children'])) {
                        $processChapter($chapter['children']);
                    }
                }
            };
            
            $processChapter($chapters);
            
            // 确保chapter_stats不为空，如果没有章节数据，返回默认结构
            if (empty($chapterStats)) {
                // 如果没有章节数据，添加一个默认的章节统计项
                $chapterStats[] = [
                    'chapter_uid' => '0',
                    'question_uid' => '',
                    'chapter_parent_uid' => '',
                    'chapter_name' => '全部章节',
                    'total_collects' => 0,
                ];
            }
            
            // 设置最终的统计数据到返回数组中
            $result['type_stats'] = $typeStats;
            $result['chapter_stats'] = $chapterStats;
            
            return $result;
        } catch (\Exception $e) {
            \think\facade\Log::error('获取收藏统计数据失败: ' . $e->getMessage());
            // 返回初始化的默认数据，确保所有字段都存在
            return $result;
        }
    }
   
    /**
     * 获取用户章节统计数据（优化版本）
     * 从专用统计表中读取数据，提高性能
     * @param array $params
     * @return array
     * @author 精解析答题
     * @date 2025/12/24
     */
    public static function getUserChapterStatistics(array $params): array
    {
        try {
            $libraryUid = $params['uid'] ?? '';
            $userUid = (int)($params['user_uid'] ?? 0);  // user_uid现在是int类型
            
            if (empty($libraryUid)) {
                return [];
            }
            
            // 如果没有用户ID，返回空数据
            if (empty($userUid)) {
                return [];
            }
            
            // 获取租户ID
            $tenantId = (int)($params['tenant_id'] ?? 0);  // tenant_id也int类型
            
            // 从统计表中查询用户章节统计数据
            $statistics = TenantUserChapterStatistics::query()
                ->where([
                    ['library_uid', '=', $libraryUid],
                    ['user_uid', '=', $userUid],
                    ['tenant_id', '=', $tenantId]
                ])
                ->field(['chapter_uid', 'attempted_count', 'correct_count', 'accuracy_rate'])
                ->select()
                ->toArray();
            
            // 将数据转换为以chapter_uid为key的数组，方便前端使用
            $statsMap = [];
            foreach ($statistics as $stat) {
                $statsMap[$stat['chapter_uid']] = [
                    'done_count' => (int)$stat['attempted_count'],
                    'correct_count' => (int)$stat['correct_count'],
                    'accuracy' => round((float)$stat['accuracy_rate'], 1)
                ];
            }
            
            return $statsMap;
        } catch (\Exception $e) {
            \think\facade\Log::error('获取用户章节统计失败: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * 获取用户题库整体统计数据
     * 从章节统计表中汇总所有章节的数据
     * @param array $params
     * @return array
     * @author 精解析答题
     * @date 2025/12/25
     */
    public static function getLibraryOverallStatistics(array $params): array
    {
        try {
            $libraryUid = $params['uid'] ?? '';
            $userUid = (int)$params['user_uid'];
            
                        // 获取题库总题数
            $totalQuestions = TenantExamQuestion::query()
                ->where([
                    ['library_uid', '=', $libraryUid],
                    ['is_show', '=', 1]
                ])
                ->count();

            if(empty($userUid)){
                return [
                    'eliminated_errors' => 0,
                    'total_questions' => (int)$totalQuestions,
                    'total_errors' => 0,
                    'overall_accuracy' => 0,
                    'total_answers' => 0,
                ];
            }
            // ✅ 从答题历史记录中统计用户实际做过的不同题目数（避免重复计算）
            $historyStats = Db::name('tenant_exam_question_error_stats')
                ->where([
                    ['library_uid', '=', $libraryUid],
                    ['user_uid', '=', $userUid],
                ])
                ->field([
                    'total_errors',
                    'eliminated_errors',
                    'elimination_rate',
                    'total_answers'
                ])
                ->find();
            

            
            return [
                'eliminated_errors' =>  (int)$historyStats['eliminated_errors'],  // 已经消灭的错题
                'total_questions' => (int)$totalQuestions,  // 题库总题数
                'total_errors' => (int)$historyStats['total_errors'],  // 实际错误数
                'overall_accuracy' => round((float)$historyStats['elimination_rate'], 1) ,  // 整体准确率
                'total_answers' => (int)$historyStats['total_answers'],  // 实际回答数
            ];
        } catch (\Exception $e) {
            \think\facade\Log::error('获取题库整体统计失败: ' . $e->getMessage());
            return [
                'eliminated_errors' => 0,
                'total_questions' => 0,
                'total_errors' => 0,
                'overall_accuracy' => 0,
                'total_answers' => 0,
            ];
        }
    }
    
    
    /**
     * @notes 批量获取题目的点赞用户列表（每个题目只返回前5名）
     * @param array $questionIds 题目 ID数组
     * @param string $libraryUid 题库 UID
     * @return array 格式：[question_uid => [{user_id, nickname, avatar}, ...]]
     */
    private static function getLikeUsersForQuestions(array $questionIds, string $libraryUid): array
    {
        if (empty($questionIds)) {
            return [];
        }
        
        // 一次性查询所有题目的点赞记录，按点赞时间排序
        $likeRecords = TenantExamQuestionLike::query()
            ->whereIn('question_uid', $questionIds)
            ->where('library_uid', $libraryUid)
            ->whereNull('delete_time')
            ->with(['user' => function($query) {
                $query->field('id,uid,nickname,avatar');
            }])
            ->field('question_uid,user_uid,create_time')
            ->order('create_time', 'desc')
            ->select()
            ->toArray();
        
        // 按题目分组，每个题目只保留前5名
        $result = [];
        foreach ($likeRecords as $record) {
            $questionUid = $record['question_uid'];
            if (!isset($result[$questionUid])) {
                $result[$questionUid] = [];
            }
            
            // 每个题目最多保留5条记录
            if (count($result[$questionUid]) < 5) {
                $result[$questionUid][] = [
                    'user_id' => $record['user']['uid'] ?? '',
                    'nickname' => $record['user']['nickname'] ?? '',
                    'avatar' => $record['user']['avatar'] ?? ''
                ];
            }
        }
        
        return $result;
    }
    
    /**
     * 获取未被消灭的错题ID列表
     * @param int $userId 用户ID
     * @param string|null $libraryUid 题库ID（可选）
     * @return array 错题ID列表
     * @date 2026/01/23
     * @author 精解析答题
     */
    public static function getErrorQuestionIds(int $userId, ?string $libraryUid = null): array
    {
        try {
            // 1. 构建查询条件
            $where = [
                ['user_uid', '=', $userId],
                ['eliminated_status', '=', 0], // 0表示未被消灭
                ['eliminated_at', '=', null], // 未被消灭的记录
            ];
            
            // 如果提供了题库ID，添加到查询条件
            if (!empty($libraryUid)) {
                $where[] = ['library_uid', '=', $libraryUid];
            }
            
            // 2. 查询所有错题记录
            $errorQuestions = TenantExamQuestionError::where($where)
                ->column('id, question_uid');
            
            if (empty($errorQuestions)) {
                return [];
            } 
            // 提取所有 question_uid 并去重
            $questionUids = array_column($errorQuestions, 'question_uid');
            return array_values(array_unique($questionUids));
        } catch (\Exception $e) {
            \think\facade\Log::error('获取错题ID列表失败: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * @notes 批量获取题目的收藏用户列表（每个题目只返回前5名）
     * @param array $questionIds 题目 ID数组
     * @param string $libraryUid 题库 UID
     * @return array 格式：[question_uid => [{user_id, nickname, avatar}, ...]]
     */
    private static function getCollectUsersForQuestions(array $questionIds, string $libraryUid): array
    {
        if (empty($questionIds)) {
            return [];
        }
        
        // 一次性查询所有题目的收藏记录，按收藏时间排序
        $collectRecords = TenantExamQuestionCollection::query()
            ->whereIn('question_uid', $questionIds)
            ->where('library_uid', $libraryUid)
            ->whereNull('delete_time')
            ->with(['user' => function($query) {
                $query->field('id,uid,nickname,avatar');
            }])
            ->field('question_uid,user_uid,create_time')
            ->order('create_time', 'asc') // 按收藏时间正序，先收藏的在前
            ->select()
            ->toArray();
        
        // 按题目 ID分组，每组只取前5个
        $result = [];
        foreach ($collectRecords as $record) {
            $questionUid = $record['question_uid'];
            
            if (!isset($result[$questionUid])) {
                $result[$questionUid] = [];
            }
            
            // 每个题目最多返回5个收藏用户
            if (count($result[$questionUid]) < 5 && !empty($record['user'])) {
                $result[$questionUid][] = [
                    'user_id' => $record['user']['id'] ?? 0,
                    'user_uid' => $record['user_uid'],
                    'nickname' => $record['user']['nickname'] ?? '匿名用户',
                    'avatar' => $record['user']['avatar'] ?? ''
                ];
            }
        }
        
        return $result;
    }

    /**
     * 更新用户章节统计数据
     * 在用户答题提交后调用，使用原子操作更新统计
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/12/24
     */
    public static function updateUserChapterStatistics(array $params): bool
    {
        try {
            $userUid = $params['user_uid'] ?? '';
            $libraryUid = $params['library_uid'] ?? '';
            $chapterUid = $params['chapter_uid'] ?? '';
            $tenantId = $params['tenant_id'] ?? 0;
            $isCorrect = $params['is_correct'] ?? false;
            
            // 参数验证
            if (empty($userUid) || empty($libraryUid) || empty($chapterUid)) {
                \think\facade\Log::warning('更新章节统计参数不完整: ' . json_encode($params));
                return false;
            }
            
            // 生成唯一标识
            $uniqueKey = md5($userUid . $libraryUid . $chapterUid . $tenantId);
            $uid = substr($uniqueKey, 0, 36);
            
            // 查询章节题目总数
            $totalQuestions = TenantExamQuestion::query()
                ->where([
                    ['chapter_uid', '=', $chapterUid],
                    ['library_uid', '=', $libraryUid],
                    ['is_show', '=', 1]
                ])
                ->count();
            
            $now = time();
            
            // 查找是否存在记录
            $stat = TenantUserChapterStatistics::query()
                ->where([
                    ['user_uid', '=', $userUid],
                    ['chapter_uid', '=', $chapterUid],
                    ['library_uid', '=', $libraryUid],
                    ['tenant_id', '=', $tenantId]
                ])
                ->find();
            
            if ($stat) {
                // 记录存在，使用原子操作更新
                $updateData = [
                    'last_practice_time' => $now,
                    'update_time' => $now
                ];
                
                // 只有当已答题数小于总题数时，才增加已答题数
                if ($stat->attempted_count < $totalQuestions) {
                    $updateData['attempted_count'] = Db::raw('attempted_count + 1');
                    
                    // 如果答对了，同时增加正确数
                    if ($isCorrect) {
                        $updateData['correct_count'] = Db::raw('correct_count + 1');
                    }
                }
                
                // 更新总题目数（可能章节题目数有变化）
                $updateData['total_questions'] = $totalQuestions;
                
                // 确保correct_count不超过attempted_count
                if ($stat->correct_count > $stat->attempted_count) {
                    $updateData['correct_count'] = $stat->attempted_count;
                }
                
                $stat->save($updateData);
                
                // 重新计算正确率（触发器会自动计算，但为了保险手动计算）
                $updatedStat = TenantUserChapterStatistics::query()
                    ->where([
                        ['id', '=', $stat->id],
                        ['tenant_id', '=', $tenantId]  // 显式添加tenant_id条件
                    ])
                    ->find();
                
                if ($updatedStat && $updatedStat->attempted_count > 0) {
                    $accuracyRate = round(($updatedStat->correct_count / $updatedStat->attempted_count) * 100, 2);
                    $updatedStat->save(['accuracy_rate' => $accuracyRate]);
                }
            } else {
                // 记录不存在，创建新记录
                $correctCount = $isCorrect ? 1 : 0;
                TenantUserChapterStatistics::create([
                    'uid' => $uid,
                    'tenant_id' => $tenantId,
                    'user_uid' => $userUid,
                    'library_uid' => $libraryUid,
                    'chapter_uid' => $chapterUid,
                    'total_questions' => $totalQuestions,
                    'attempted_count' => 1,
                    'correct_count' => $correctCount,
                    'wrong_count' => 1 - $correctCount,
                    'accuracy_rate' => $correctCount * 100.00,
                    'first_practice_time' => $now,
                    'last_practice_time' => $now,
                    'create_time' => $now,
                    'update_time' => $now
                ]);
            }
            
            // 数据修复：确保现有所有记录的attempted_count不超过total_questions
            // 只在更新时执行一次，避免频繁修复
            TenantUserChapterStatistics::query()
                ->where([
                    ['user_uid', '=', $userUid],
                    ['library_uid', '=', $libraryUid],
                    ['tenant_id', '=', $tenantId]
                ])
                ->where('attempted_count', '>', Db::raw('total_questions'))
                ->update([
                    'attempted_count' => Db::raw('total_questions'),
                    'correct_count' => Db::raw('LEAST(correct_count, total_questions)'),
                    'update_time' => $now
                ]);
            
            return true;
        } catch (\Exception $e) {
            \think\facade\Log::error('更新章节统计失败: ' . $e->getMessage() . '\n' . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * 我的纠错列表
     * @param $params
     * @return array
     * @author 精解析题库
     * @date 2025/08/05 23:16
     */
    public static function myErrorCorrectList($params): array
    {
        $params['library_uid'] = $params['library_uid'] ?? '';
        $list = TenantExamQuestionCorrections::query()
            ->where([
                ['user_id', '=', $params['user_uid']],
                ['tenant_id', '=', $params['tenant_id']],
                ['library_uid', '=', $params['library_uid']]
            ])
            ->field('id,question_uid,question_name,correction_reason,platform_feedback,feedback_time,user_id,user_nickname,create_time')   
            ->order(['feedback_time desc', 'id desc'])
            ->select()
            ->toArray();
        return $list;
    }

    /**
     * 知识点列表
     * @param $params
     * @return array
     * @author 精解析题库
     * @date 2025/08/05 23:16
     */
    public static function knowledgeList($params): array
    {
        $params['library_uid'] = $params['library_uid'] ?? '';
        $params['chapter_uid'] = $params['chapter_uid'] ?? '';
        $params['tenant_id'] = $params['tenant_id'] ?? 0;
        
        $list = TenantExamKnowledge::query()
            ->where('tenant_id', '=', $params['tenant_id'])
            ->where('library_uid', '=', $params['library_uid'])
            ->where('chapter_uid', '=', $params['chapter_uid'])
            ->where('is_show', '=', 1)
            ->field('id,uid,title,chapter_uid,create_time')   
            ->order(['chapter_uid', 'id'])
            ->select()
            ->toArray();
        return $list;
    }

    /**
     * 标签列表
     * @param $params
     * @return array
     * @author 精解析题库
     * @date 2025/08/05 23:16
     */
    public static function labelList($params): array
    {
        $params['library_uid'] = $params['library_uid'] ?? '';
        $params['tenant_id'] = $params['tenant_id'] ?? 0;
        
        $list = TenantExamLabel::query()
            ->where('tenant_id', '=', $params['tenant_id'])
            ->where('is_show', '=', 1)
            ->where(function($query) use ($params) {
                $query->where('library_uid', '=', 0) // 全局公共标签
                      ->whereOr('library_uid', '=', $params['library_uid']); // 特定题库标签
            })
            ->field('id,uid,title,create_time')   
            ->order(['id'])
            ->select()
            ->toArray();
        return $list;
    }

    /**
     * 获取题目结构信息（用于答题卡显示）
     * @param array $params
     * @return array
     */
    public static function getQuestionStructure(array $params): array
    {
        try {
            
            // 使用prepareQuestionQuery方法处理参数和构建查询条件
            $result = self::prepareQuestionQuery($params);
            $processedParams = $result['params'];
            $typeCounts = $result['typeCounts'];
            $randomType = $processedParams['randomType'];
            $examType = $processedParams['examType'];
            
            // 获取offset和limit参数
            $offset = $processedParams['offset'];
            $limit = $processedParams['questionCount'];
            // 使用question_count参数作为总题数，用于计算typeCounts
            $totalQuestionCount = (int)($params['question_count'] ?? $params['limit'] ?? 50);
            
            // 检查是否需要按比例获取题目
            $items = [];
            
            // 特殊处理：当selecte_type=2（未做题目）时，直接获取所有未做题目，与selectedQuestionCount方法逻辑一致
            if ($processedParams['selecteType'] == 2) {
                $query = $result['query'];
                
                // 直接获取所有未做题目，与selectedQuestionCount方法逻辑一致
                $items = $query
                    ->field(['uid', 'exam_type', 'sort'])
                    ->when(
                        $randomType == 1,
                        function ($query) {
                            // 随机模式：完全随机排序
                            $query->orderRaw('RAND()');
                        },
                        function ($query) {
                            // 非随机模式：严格按题型排序（exam_type 升序），同题型内按 sort 降序
                            $query->order('exam_type', 'asc')->order('sort', 'desc');
                        }
                    )
                    ->select()
                    ->toArray();
                
                // 应用offset和limit参数
                if ($totalQuestionCount > 0) {
                    // 返回所有题目，不应用limit参数
                    $items = array_slice($items, $offset);
                } else if ($offset > 0 || $limit > 0) {
                    // 应用offset和limit参数
                    $items = array_slice($items, $offset, $limit);
                }
            // 特殊处理：当selecte_type=4（错题题目）时，直接获取所有错题
            } else if ($processedParams['selecteType'] == 4) {
                $query = $result['query'];
                
                // 直接获取所有错题
                $items = $query
                    ->field(['uid', 'exam_type', 'sort'])
                    ->when(
                        $randomType == 1,
                        function ($query) {
                            // 随机模式：完全随机排序
                            $query->orderRaw('RAND()');
                        },
                        function ($query) {
                            // 非随机模式：严格按题型排序（exam_type 升序），同题型内按 sort 降序
                            $query->order('exam_type', 'asc')->order('sort', 'desc');
                        }
                    )
                    ->select()
                    ->toArray();
                    
                // 应用offset和limit参数
                if ($totalQuestionCount > 0) {
                    // 返回所有题目，不应用limit参数
                    $items = array_slice($items, $offset);
                } else if ($offset > 0 || $limit > 0) {
                    // 应用offset和limit参数
                    $items = array_slice($items, $offset, $limit);
                }
            } else if (!empty($typeCounts)) {
                // 按比例获取题目，与orderOptionList方法完全一致
                foreach ($typeCounts as $type => $count) {
                    if ($count <= 0) continue;
                    
                    $typeParams = $params;
                    $typeParams['exam_type'] = [$type];
                    
                    // 使用prepareQuestionQuery方法处理每种题型的参数，与orderOptionList方法一致
                    $typeResult = self::prepareQuestionQuery($typeParams);
                    $typeQuery = $typeResult['query'];
                    
                    // 执行查询，与orderOptionList方法一致
                    $typeItems = $typeQuery
                        ->field(['uid', 'exam_type', 'sort'])
                        ->when(
                            $randomType == 1,
                            function ($query) {
                                $query->orderRaw('RAND()');
                            },
                            function ($query) {
                                $query->order('sort', 'desc');
                            }
                        )
                        ->limit($count)
                        ->select()
                        ->toArray();

                    $items = array_merge($items, $typeItems);
                }
                
                // 排序处理，与orderOptionList方法一致
                if ($randomType == 1) {
                    // 随机模式：使用固定的随机种子，确保相同参数下返回相同顺序
                    $seed = crc32(serialize($params));
                    srand($seed);
                    shuffle($items);
                    srand(); // 重置随机种子
                } else {
                    // 非随机模式：按题型排序，同题型内按sort降序排序，sort相同时按id降序排序
                    usort($items, function ($a, $b) {
                        // 先按题型排序
                        if ($a['exam_type'] != $b['exam_type']) {
                            return $a['exam_type'] <=> $b['exam_type'];
                        }
                        // 再按sort字段降序排序
                        if (($b['sort'] ?? 0) != ($a['sort'] ?? 0)) {
                            return ($b['sort'] ?? 0) <=> ($a['sort'] ?? 0);
                        }
                        // sort相同时按id字段降序排序
                        return ($b['id'] ?? 0) <=> ($a['id'] ?? 0);
                    });
                }
                
                // 应用offset和limit参数
                // 如果question_count参数存在且大于0，则返回所有题目，不应用limit参数
                if ($totalQuestionCount > 0) {
                    // 返回所有题目，不应用limit参数
                    $items = array_slice($items, $offset);
                } else if ($offset > 0 || $limit > 0) {
                    // 应用offset和limit参数，与orderOptionList方法一致
                    $items = array_slice($items, $offset, $limit);
                }
            } else {
                // 传统方式获取题目
                $query = $result['query'];
                $items = $query
                    ->field(['uid', 'exam_type', 'sort'])
                    ->when(
                        $randomType == 1,
                        function ($query) {
                            // 随机模式：完全随机排序
                            $query->orderRaw('RAND()');
                        },
                        function ($query) {
                            // 非随机模式：严格按题型排序（exam_type 升序），同题型内按 sort 降序
                            $query->order('exam_type', 'asc')->order('sort', 'desc');
                        }
                    )
                    ->limit($offset, $limit)
                    ->select()
                    ->toArray();
            }
            
            return $items;
        } catch (\Exception $e) {
            \think\facade\Log::error('获取题目结构信息失败: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * 按指定UID顺序获取题目列表
     * @param array $params
     * @return array
     * @author 精解析答题
     */
    public static function orderOptionListByUids(array $params): array
    {
        try {
            // 参数验证
            if (empty($params['question_uids']) || !is_array($params['question_uids'])) {
                return [];
            }
            
            $uids = $params['question_uids'];
            
            // 查询指定UID的题目
            $items = TenantExamQuestion::query()
                ->whereIn('uid', $uids)
                ->where(['is_show' => 1])
                ->field(['uid', 'library_uid', 'title', 'answer', 'option', 'analysis','commentaries' , 'exam_type', 'exam_level','integral', 'score', 'chapter_uid', 'knowledge_uid', 'label_uid', 'total_attempts', 'total_correct', 'accuracy_rate', 'easy_mistakes','like_count','collect_count'])
                ->append(['exam_type_name', 'Knowledge', 'label_data', 'chapter', 'answer_count', 'correct_rate', 'wrong_option'])
                ->select()
                ->toArray();
            
            // 按照输入的UID顺序重新排序
            $orderedItems = [];
            foreach ($uids as $uid) {
                foreach ($items as $item) {
                    if ($item['uid'] === $uid) {
                        // 添加初始状态
                        $item['status'] = 'default';
                        $item['is_selected'] = false;
                        
                        // 处理选项状态
                        if (isset($item['option']) && is_array($item['option'])) {
                            foreach ($item['option'] as &$option) {
                                $option['is_selected'] = false;
                                $option['status'] = 'default';
                            }
                        } else {
                            $item['option'] = [];
                        }
                        
                        $orderedItems[] = $item;
                        break;
                    }
                }
            }
            
            // 获取用户信息
            $userId = isset($params['user_uid']) ? intval($params['user_uid']) : 0;
            $libraryUid = $params['library_uid'] ?? '';
            
            // 添加参数验证和异常处理
            try {
                // 检查题目列表是否有效
                if (is_array($orderedItems) && !empty($orderedItems)) {
                    $orderedItems = self::questionIsCollection($userId, $libraryUid, $orderedItems);
                    $orderedItems = self::questionIsLike($userId, $libraryUid, $orderedItems);
                }
            } catch (\Exception $e) {
                // 记录异常
                \think\facade\Log::error('试题列表状态处理失败: ' . $e->getMessage());
            }
            
            return $orderedItems;
            
        } catch (\Exception $e) {
            \think\facade\Log::error('按UID顺序获取题目列表失败: ' . $e->getMessage() . '\nStack trace: ' . $e->getTraceAsString());
            return [];
        }
    }
}