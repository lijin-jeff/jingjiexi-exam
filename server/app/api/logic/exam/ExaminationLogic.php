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
use app\common\model\exam\TenantExamExamination;
use app\common\model\exam\TenantExamExaminationHistory;
use app\common\model\exam\TenantExamPaper;
use app\common\model\exam\TenantExamQuestion;
use app\common\model\exam\TenantMockExamExamination;
use app\common\model\exam\TenantExamQuestionError;
use app\common\model\exam\TenantExamQuestionErrorLog;
use app\common\model\exam\TenantExamChapter;
use app\api\logic\IntegralLogic;
use app\common\model\exam\TenantUserIntegralLog;
use app\common\model\exam\TenantUserExamProgress;
use app\common\enum\IntegralEnum;
use app\common\model\user\UserSubscribe;
use app\common\model\user\User;


class ExaminationLogic extends BaseLogic
{
    // 题目类型常量定义
    const EXAM_TYPE_SINGLE = 1;      // 单选题
    const EXAM_TYPE_MULTIPLE = 2;    // 多选题
    const EXAM_TYPE_JUDGMENT = 3;    // 判断题
    const EXAM_TYPE_FILL = 4;        // 填空题
    const EXAM_TYPE_QUESTION = 5;    // 问答题
    const EXAM_TYPE_CASE = 6;        // 案例题

    /**
     * 保存模拟考试配置信息，并返回试题数据
     * @param array $params
     * @return string
     */
    public static function mockExaminationConfig(array $params): string
    {
        try {
            $uid = uid();
            $paperScore = $params['paper_score'] ?? 0;
            $model = TenantMockExamExamination::create([
                'tenant_id'          => request()->tenantId,
                'uid'                => $uid,
                'question_type'      => 7,
                'user_uid'           => $params['user_uid'],
                'score'              => $params['score'],
                'paper_score'        => $paperScore,
                'option_type_config' => $params['option_type_config'],
                'exam_time'          => minutesToHMS((int)$params['exam_time']),
                'checkbox_type'      => $params['checkbox_type'],
                'option_type'        => $params['option_type'],
                'library_uid'       => $params['library_uid'],
                'priority_wrong'     => $params['priority_wrong'] ?? 0,
                'priority_unattempted' => $params['priority_unattempted'] ?? 0,
            ]);

            if (!empty($model->getKey())) return $uid;

            return '';
        } catch (\Exception $exception) {
            self::setError($exception->getMessage());
            return "";
        }
    }

    /**
     * 模拟考试试题查询
     * @param array $params
     * @return array
     */
    public static function mockExamList(array $params): array
    {
        $configModel = TenantMockExamExamination::query()->where([
            ['uid', '=', $params['uid']],
            ['user_uid', '=', $params['user_uid']],
        ])->field(['option_type_config', 'exam_time', 'option_type', 'library_uid', 'priority_wrong', 'priority_unattempted'])->findOrEmpty();
        if ($configModel->isEmpty()) return [];
        
        $userId = $params['user_uid'];
        $libraryUid = $configModel['library_uid'];
        $priorityWrong = $configModel['priority_wrong'] ?? 0;
        $priorityUnattempted = $configModel['priority_unattempted'] ?? 0;
        
        // 获取错题ID列表（如果启用错题优先）
        $errorQuestionIds = [];
        if ($priorityWrong == 1) {
            $errorOptionUid = TenantExamExaminationHistory::query()->where([
                ['user_uid', '=', $userId]
            ])->field('error_option_uid')->select()->toArray();
            foreach ($errorOptionUid as $value) {
                $errorUids = $value['error_option_uid'] ?? [];
                if (is_string($errorUids)) {
                    $errorUids = json_decode($errorUids, true);
                }
                if (is_array($errorUids) && !empty($errorUids)) {
                    array_push($errorQuestionIds, ...$errorUids);
                }
            }
            $errorQuestionIds = array_unique($errorQuestionIds);
        }
        
        // 获取已做题ID列表（如果启用未做题优先）
        $doneQuestionIds = [];
        if ($priorityUnattempted == 1) {
            $optionUid = TenantExamExaminationHistory::query()
                ->where([['user_uid', '=', $userId]])
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
            $doneQuestionIds = $flattened;
        }
        // 安全解析 JSON，兼容空值
        $optionTypeConfig = json_decode($configModel['option_type_config'] ?? '[]', true);
        // 单选试题配置
        $optionConfig = ['count' => 0, 'score' => 1];
        // 多选试题配置
        $checkboxConfig = ['count' => 0, 'score' => 1];
        // 判断试题配置
        $judeConfig = ['count' => 0, 'score' => 1];
        // 填空题配置
        $essayConfig = ['count' => 0, 'score' => 1];
        // 问答题配置
        $questionConfig = ['count' => 0, 'score' => 1];
        // 案例题配置
        $caseConfig = ['count' => 0, 'score' => 1];

        foreach ($optionTypeConfig as $value) {
            if ((int)$value['value'] === 1) {
                $optionConfig['count'] = $value['count'];
                $optionConfig['score'] = $value['score'];
            } else if ((int)$value['value'] === 2) {
                $checkboxConfig['count'] = $value['count'];
                $checkboxConfig['score'] = $value['score'];
            } else if ((int)$value['value'] === 3) {
                $judeConfig['count'] = $value['count'];
                $judeConfig['score'] = $value['score'];
            } else if ((int)$value['value'] === 4) {
                $essayConfig['count'] = $value['count'];
                $essayConfig['score'] = $value['score'];
            } else if ((int)$value['value'] === 5) {
                $questionConfig['count'] = $value['count'];
                $questionConfig['score'] = $value['score'];
            } else if ((int)$value['value'] === 6) {
                $caseConfig['count'] = $value['count'];
                $caseConfig['score'] = $value['score'];
            }
        }
        $optionItems = [];

        if ($optionConfig['count'] > 0) {
            $query = TenantExamQuestion::query()->where([
                ['library_uid', '=', $configModel['library_uid']],
                ['is_show', '=', 1],
                ['exam_type', '=', 1]
            ]);
            
            // 错题优先：只查询错题
            if ($priorityWrong == 1 && !empty($errorQuestionIds)) {
                $query->whereIn('uid', $errorQuestionIds);
            }
            // 未做题优先：排除已做题
            if ($priorityUnattempted == 1 && !empty($doneQuestionIds)) {
                $query->whereNotIn('uid', $doneQuestionIds);
            }
            
            $optionItems = $query->field(['uid', 'uid', 'title', 'answer', 'option', 'exam_type', 'exam_level'])
                ->append(['exam_type_name'])
                ->limit(0, (int)$optionConfig['count'])
                ->orderRaw('RAND()')
                ->order('sort desc, id desc')
                ->select()
                ->toArray();
            foreach ($optionItems as &$item) {
                $item['status'] = 'default';
                $item['score'] = $optionConfig['score'];
                $item['is_selected'] = false;
                foreach ($item['option'] as &$option) {
                    $option['is_selected'] = false;
                    $option['status'] = 'default';
                }
            }
        }
        $checkboxItems = [];
        if ($checkboxConfig['count'] > 0) {
            $query = TenantExamQuestion::query()->where([
                ['library_uid', '=', $configModel['library_uid']],
                ['is_show', '=', 1],
                ['exam_type', '=', 2]
            ]);
            
            // 错题优先：只查询错题
            if ($priorityWrong == 1 && !empty($errorQuestionIds)) {
                $query->whereIn('uid', $errorQuestionIds);
            }
            // 未做题优先：排除已做题
            if ($priorityUnattempted == 1 && !empty($doneQuestionIds)) {
                $query->whereNotIn('uid', $doneQuestionIds);
            }
            
            $checkboxItems = $query->field(['uid', 'uid', 'title', 'answer', 'option', 'exam_type', 'exam_level'])
                ->append(['exam_type_name'])
                ->orderRaw('RAND()')
                ->limit(0, (int)$checkboxConfig['count'])
                ->order('sort desc, id desc')
                ->select()
                ->toArray();
            foreach ($checkboxItems as &$item) {
                $item['status'] = 'default';
                $item['score'] = $checkboxConfig['score'];
                $item['is_selected'] = false;
                foreach ($item['option'] as &$option) {
                    $option['is_selected'] = false;
                    $option['status'] = 'default';
                }
            }
        }
        $judeItems = [];
        if ($judeConfig['count'] > 0) {
            $query = TenantExamQuestion::query()->where([
                ['library_uid', '=', $configModel['library_uid']],
                ['is_show', '=', 1],
                ['exam_type', '=', 3]
            ]);
            
            // 错题优先：只查询错题
            if ($priorityWrong == 1 && !empty($errorQuestionIds)) {
                $query->whereIn('uid', $errorQuestionIds);
            }
            // 未做题优先：排除已做题
            if ($priorityUnattempted == 1 && !empty($doneQuestionIds)) {
                $query->whereNotIn('uid', $doneQuestionIds);
            }
            
            $judeItems = $query->field(['uid', 'uid', 'title', 'answer', 'option', 'exam_type', 'exam_level'])
                ->append(['exam_type_name'])
                ->orderRaw('RAND()')
                ->limit(0, (int)$judeConfig['count'])
                ->order('sort desc, id desc')
                ->select()
                ->toArray();
            foreach ($judeItems as &$item) {
                $item['status'] = 'default';
                $item['score'] = $judeConfig['score'];
                $item['is_selected'] = false;
                foreach ($item['option'] as &$option) {
                    $option['is_selected'] = false;
                    $option['status'] = 'default';
                }
            }
        }
        $essayItems = [];
        if ($essayConfig['count'] > 0) {
            $query = TenantExamQuestion::query()->where([
                ['library_uid', '=', $configModel['library_uid']],
                ['is_show', '=', 1],
                ['exam_type', '=', 4]
            ]);
            
            // 错题优先：只查询错题
            if ($priorityWrong == 1 && !empty($errorQuestionIds)) {
                $query->whereIn('uid', $errorQuestionIds);
            }
            // 未做题优先：排除已做题
            if ($priorityUnattempted == 1 && !empty($doneQuestionIds)) {
                $query->whereNotIn('uid', $doneQuestionIds);
            }
            
            $essayItems = $query->field(['uid', 'uid', 'title', 'answer', 'option', 'exam_type', 'exam_level'])
                ->append(['exam_type_name'])
                ->orderRaw('RAND()')
                ->limit(0, (int)$essayConfig['count'])
                ->order('sort desc, id desc')
                ->select()
                ->toArray();
            foreach ($essayItems as &$item) {
                $item['status'] = 'default';
                $item['score'] = $essayConfig['score'];
                $item['is_selected'] = false;
                foreach ($item['option'] as &$option) {
                    $option['is_selected'] = false;
                    $option['status'] = 'default';
                }
            }
        }
        // 问答题
        $questionItems = [];
        if ($questionConfig['count'] > 0) {
            $query = TenantExamQuestion::query()->where([
                ['library_uid', '=', $configModel['library_uid']],
                ['is_show', '=', 1],
                ['exam_type', '=', 5]
            ]);
            
            // 错题优先：只查询错题
            if ($priorityWrong == 1 && !empty($errorQuestionIds)) {
                $query->whereIn('uid', $errorQuestionIds);
            }
            // 未做题优先：排除已做题
            if ($priorityUnattempted == 1 && !empty($doneQuestionIds)) {
                $query->whereNotIn('uid', $doneQuestionIds);
            }
            
            $questionItems = $query->field(['uid', 'uid', 'title', 'answer', 'option', 'exam_type', 'exam_level'])
                ->append(['exam_type_name'])
                ->orderRaw('RAND()')
                ->limit(0, (int)$questionConfig['count'])
                ->order('sort desc, id desc')
                ->select()
                ->toArray();
            foreach ($questionItems as &$item) {
                $item['status'] = 'default';
                $item['score'] = $questionConfig['score'];
                $item['is_selected'] = false;
                // 问答题没有option数组，但保持结构一致性
                if (!isset($item['option'])) {
                    $item['option'] = [];
                }
            }
        }
        // 案例题
        $caseItems = [];
        if ($caseConfig['count'] > 0) {
            $query = TenantExamQuestion::query()->where([
                ['library_uid', '=', $configModel['library_uid']],
                ['is_show', '=', 1],
                ['exam_type', '=', 6]
            ]);
            
            // 错题优先：只查询错题
            if ($priorityWrong == 1 && !empty($errorQuestionIds)) {
                $query->whereIn('uid', $errorQuestionIds);
            }
            // 未做题优先：排除已做题
            if ($priorityUnattempted == 1 && !empty($doneQuestionIds)) {
                $query->whereNotIn('uid', $doneQuestionIds);
            }
            
            $caseItems = $query->field(['uid', 'uid', 'title', 'answer', 'option', 'exam_type', 'exam_level'])
                ->append(['exam_type_name'])
                ->orderRaw('RAND()')
                ->limit(0, (int)$caseConfig['count'])
                ->order('sort desc, id desc')
                ->select()
                ->toArray();
            foreach ($caseItems as &$item) {
                $item['status'] = 'default';
                $item['score'] = $caseConfig['score'];
                $item['is_selected'] = false;
                if (!isset($item['option'])) {
                    $item['option'] = [];
                }
            }
        }
        $fullItems = array_merge($optionItems, $checkboxItems, $judeItems, $essayItems, $questionItems, $caseItems);
        if ((int)$configModel['option_type'] === 1) {
            shuffle($fullItems);
        }
        return [
            'list' => $fullItems,
            'config'    => ['exam_time' => $configModel['exam_time'], 'library_uid' => $configModel['library_uid']],
        ];
    }

    /**
     * 保存模拟考试
     * @param array $params
     * @return array
     */
    public static function saveMockExamination(array $params): array
    {
        try {
            $mockConfig = TenantMockExamExamination::query()->where([
                ['uid', '=', $params['history_uid']]
            ])->field(['uid', 'library_uid', 'option_type_config', 'id'])->findOrEmpty();

            if (empty($mockConfig)) {
                self::setError('配置不存在');
                return [];
            }
            // 处理试题分数规则，安全解析 JSON
            $optionTypeConfig = json_decode($mockConfig['option_type_config'] ?? '[]', true);
            // 单选试题配置
            $optionConfig = ['count' => 0, 'score' => 1];
            // 多选试题配置
            $checkboxConfig = ['count' => 0, 'score' => 1];
            // 判断试题配置
            $judeConfig = ['count' => 0, 'score' => 1];
            // 填空题配置
            $fillConfig = ['count' => 0, 'score' => 1];
            // 问答题配置
            $writeConfig = ['count' => 0, 'score' => 1];
            // 案例题配置
            $caseConfig = ['count' => 0, 'score' => 1];
            
            foreach ($optionTypeConfig as $value) {
                if ((int)$value['value'] === 1) {
                    $optionConfig['count'] = $value['count'];
                    $optionConfig['score'] = $value['score'];
                } else if ((int)$value['value'] === 2) {
                    $checkboxConfig['count'] = $value['count'];
                    $checkboxConfig['score'] = $value['score'];
                } else if ($value['value'] === 3) {
                    $judeConfig['count'] = $value['count'];
                    $judeConfig['score'] = $value['score'];
                } else if ($value['value'] === 4) {
                    $fillConfig['count'] = $value['count'];
                    $fillConfig['score'] = $value['score'];
                } else if ($value['value'] === 5) {
                    $writeConfig['count'] = $value['count'];
                    $writeConfig['score'] = $value['score'];
                } else if ($value['value'] === 6) {
                    $caseConfig['count'] = $value['count'];
                    $caseConfig['score'] = $value['score'];
                }

            }
            // 处理试题，安全解析 JSON
            $option = json_decode($params['option'] ?? '[]', true);
            if (!is_array($option)) {
                $option = [];
            }
            $optionUid = array_column($option, 'uid');
            $questionList = TenantExamQuestion::query()->where([
                ['library_uid', '=', $mockConfig['library_uid']]
            ])->whereIn('uid', $optionUid)->field(['answer', 'exam_type', 'uid'])->select()->toArray();
            // 统计答题结果统计
             
            $errorOption = $correctOption = [];
            $correctCount = $errorCount = 0;
            $userScore = 0;
            
            // 初始化评分配置
            $scoreConfigs = [
                self::EXAM_TYPE_SINGLE => ['score' => 1],    // 单选题
                self::EXAM_TYPE_MULTIPLE => ['score' => 1],  // 多选题
                self::EXAM_TYPE_JUDGMENT => ['score' => 1],  // 判断题
                self::EXAM_TYPE_QUESTION => ['score' => 1],  // 问答题
                self::EXAM_TYPE_FILL => ['score' => 1],      // 填空题
                self::EXAM_TYPE_CASE => ['score' => 1]       // 案例题
            ];
            
            // 先把用户提交的答案按 uid 做成索引数组
            $optionIndex = array_column($option, null, 'uid');
            
            foreach ($questionList as $key => $value) {
                // 检查题目是否存在于用户答案中
                if (!isset($optionIndex[$value['uid']])) {
                    $errorCount++;
                    $errorOption[] = $value['uid'];
                    continue;
                }

                $v = $optionIndex[$value['uid']] ?? [];
                $examType = $value['exam_type'];
                $score = $scoreConfigs[$examType]['score'] ?? 0;

                // 解析JSON字符串为PHP数组
                $correctAnswer = json_decode($value['answer'], true) ?? [];
                $userAnswer = json_decode($v['user_answer'], true) ?? [];

                // 对数组进行排序以便比较
                sort($correctAnswer);
                sort($userAnswer);

                // 比较答案
                $isCorrect = ($correctAnswer === $userAnswer);

                if ($isCorrect) {
                    $correctOption[] = $value['uid'];
                    $correctCount++;
                    $userScore += $score;
                } else {
                    $errorCount++;
                    $errorOption[] = $value['uid'];
                }
                
                // 为 option 数组添加 is_correct 布尔字段（关键优化）
                $option[$key]['is_correct'] = $isCorrect;
                $option[$key]['is_half_correct'] = false; // 模拟考试暂不支持半对
            }

            $updateRow = TenantMockExamExamination::query()->where('id', '=', $mockConfig['id'])->update([
                'user_score'       => $userScore,
                'options'    => json_encode($option, JSON_UNESCAPED_UNICODE),
                'error_option'     => json_encode($errorOption, JSON_UNESCAPED_UNICODE),
                'correct_option'   => json_encode($correctOption, JSON_UNESCAPED_UNICODE),
                'correct_count'    => $correctCount,
                'error_count'      => $errorCount,
                'exam_submit_time' => secondsToHMS((int)$params['time'])
            ]);

            return [
                'history_uid'   => $mockConfig['uid'],
                'correct_count' => $correctCount,
                'error_count'   => $errorCount,
                'user_score'    => $userScore,
                'msg'           => '答对' . $correctCount . '题，答错' . $errorCount . '题，' . '最后得分' . number_format($userScore, 2),
            ];
        } catch (\Exception $exception) {
            self::setError($exception->getMessage() . $exception->getLine());
            return [];
        }
    }

    /**
     * 模拟考试历史记录
     * @param array $params
     * @return array
     */
    public static function mockExaminationList(array $params): array
    {
        $pageNo = $params['page_no'];
        $pageSize = $params['page_size'];
        $pageSize = min($pageSize, 20);  // 增大最大限制，允许一次性获取更多数据
        $items = TenantMockExamExamination::query()->where([
            ['user_uid', '=', $params['user_uid']],
            ['library_uid', '=', $params['uid']]
        ])->with(['question' => function ($query) {
            $query->field(['title', 'image', 'uid']);
        }])->field(['uid','library_uid', 'correct_count', 'error_count', 'user_score', 'paper_score', 'score', 'exam_submit_time'])
        ->paginate([
                'list_rows' => $pageSize,
                'page'      => $pageNo
            ]);
        return [
            'lists'     => $items->items(),
            'page_no'   => $items->currentPage(),
            'page_size' => $pageSize,
            'count'     => $items->total(),
            'extend'    => []
        ];
    }

    /**
     * 考试列表
     * @param array $params
     * @return array
     * @author 精解析答题
     */
    public static function examinationList(array $params): array
    {
        $pageNo = (int)(isset($params['page_no']) ? $params['page_no'] : 1);
        $pageSize = (int)(isset($params['page_size']) ? $params['page_size'] : 20);
        $pageSize = min($pageSize, 20);  // 增大最大限制，允许一次性获取更多数据
        $libraryCategoryUid = $params['library_category_uid'] ?? '';
        $where = [];
        if (!empty($libraryCategoryUid)) {
            $where[] = ['library_category_uid', '=', $libraryCategoryUid];
        }
        $libUid = $params['lib_uid'] ?? '';
        if (!empty($libUid)) {
            $where[] = ['library_uid', '=', $libUid];
        }
        $items = TenantExamExamination::query()->where($where)
        ->where(function ($query) use ($params) {
            $query->where('is_show', '=', 1);
            if (!empty($params['title'])) {
                $query->whereLike('title', '%' . $params['title'] . '%');
            }
        })->append(['status'])
            ->with(['paper' => function ($query) {
                $query->field(['uid', 'option_count', 'option_score']);
            }])
            ->field(['start_time', 'end_time', 'exam_time', 'title', 'score', 'exam_time', 'image', 'uid', 'privilege'])
            ->paginate([
                'list_rows' => $pageSize,
                'page'      => $pageNo
            ]);
        return [
            'lists'     => $items->items(),
            'page_no'   => $items->currentPage(),
            'page_size' => $pageSize,
            'count'     => $items->total(),
            'extend'    => []
        ];
    }

    /**
     * 考试详情
     * @param array $params
     * @return array
     * @author 精解析答题
     */
    public static function examinationContent(array $params): array
    {
        $examination = TenantExamExamination::query()->where([
            ['uid', '=', $params['uid']],
            ['is_show', '=', 1]
        ])->field(['id','start_time', 'end_time', 'exam_time', 'title', 'score', 'exam_time', 'image', 'content', 'uid', 'paper_uid', 'privilege', 'exam_submit_type', 'submit_count_value', 'login_style'])  
            ->with(['paper' => function ($query) {
                $query->field(['uid', 'option_count', 'option_score']);
            }])
            ->append(['status', 'submit_count_status'])
            ->findOrEmpty();
        
        if ($examination->isEmpty()) return [];
        
        // 添加订阅状态
        $userId = $params['user_uid'] ?? 0;
        $isFollowed = 0;
        if($userId > 0){
            $isFollowed = (UserSubscribe::where([
                'user_id' => $userId,
                'type' => 'exam',
                'related_id' => $examination->id,
                'subscribe_status' => 1,
                'is_pushed' => 0
            ])->find() ? 1 : 0);
        }
        
        // 正确设置模型属性
        $examination->is_followed = $isFollowed;
        
        $examination->hidden(['paper_uid']);
        return $examination->toArray();
    }

    /**
     * 考试历史
     * @param array $params
     * @return array
     * @author 精解析答题
     */
    public static function getExaminationHistory(array $params): array
    {
        $pageNo = (int)$params['page_no'];
        $pageSize = (int)$params['page_size'];
        $pageSize = min($pageSize, 20);  // 增大最大限制，允许一次性获取更多数据
        $query = TenantExamExaminationHistory::query()->where([
            ['user_uid', '=', $params['user_uid']],
        ]);
        
        // ✅ 优化：先创建基础查询构建器（不包含 questions_type 过滤）用于统计所有类型
        $baseQuery = TenantExamExaminationHistory::query()->where([
            ['user_uid', '=', $params['user_uid']],
        ]);
        
        // 只有当examination_uid不为空时才添加这个过滤条件（基础查询和分页查询都需要）
        if (!empty($params['examination_uid'])) {
            $query = $query->where('examination_uid', '=', $params['examination_uid']);
            $baseQuery = $baseQuery->where('examination_uid', '=', $params['examination_uid']);
        }
        if (!empty($params['uid'])) {
            $query = $query->where('library_uid', '=', $params['uid']);
            $baseQuery = $baseQuery->where('library_uid', '=', $params['uid']);
        }
        
        // 只有当questions_error不为空时才添加这个过滤条件，error_count不为0（基础查询和分页查询都需要）
        if (!empty($params['questions_error'])) {
            $query = $query->where('error_count', '<>', 0);
            $baseQuery = $baseQuery->where('error_count', '<>', 0);
        }
        
        // ✅ 使用基础查询统计所有类型的 count（不包含 questions_type 过滤）
        $questionsTypeCount = $baseQuery
            ->field('questions_type, count(*) as count')
            ->group('questions_type')
            ->select()
            ->toArray();
        
        // 只有当questions_type不为空时才添加这个过滤条件（只对分页查询）
        if (!empty($params['questions_type'])) {
            $query = $query->where('questions_type', '=', $params['questions_type']);
        }
        
        // 原查询用于分页数据
        $items = $query
            ->field(['uid','title' ,'examination_uid', 'error_option_uid','user_integral', 'paper_score', 'user_score', 'basic_score', 'paper_uid', 'submit_time', 'paper_time', 'error_count', 'correct_count', 'practice_duration', 'questions_type', 'create_time'])
            ->order('submit_time desc, id desc')
            ->paginate([
                'list_rows' => $pageSize,
                'page'      => $pageNo
            ]);
        
        
        // 处理数据，修复options字段的三重JSON编码问题
        $lists = $items->items();
        return [
            'lists'     => $lists,
            'page_no'   => $pageNo,
            'page_size' => $pageSize,
            'count'     => $items->total(),
            'extend'    => $questionsTypeCount
        ];
    }

    // 获取考试历史详情
    public static function getExaminationHistoryDetail(array $params): array
    {
        $query = TenantExamExaminationHistory::query();
        
        // 构建查询条件 - 优化：使用 isset 而不是 empty，避免空字符串被忽略
        if (isset($params['id']) && $params['id'] !== 0 && $params['id'] !== ''&& $params['id'] !== '0') {
            $query->where('id', '=', $params['id']);
        }
        if (isset($params['uid']) && $params['uid'] !== '') {
            $query->where('uid', '=', $params['uid']);
        }
        
        
        $history = $query->where('user_uid', '=', $params['user_uid'])
            ->findOrEmpty();
            
        if ($history->isEmpty()) return [];
        
        // 获取历史记录数据
        $historyData = $history->toArray();
        
        // 处理 options 字段，添加 is_correct 布尔值
        if (isset($historyData['options']) && !empty($historyData['options'])) {
            // 解析 JSON 字符串
            $options = is_string($historyData['options']) 
                ? json_decode($historyData['options'], true) 
                : $historyData['options'];
            
            if (is_array($options)) {
                // 获取正确和错误题目 UID 列表
                $correctUids = [];
                $errorUids = [];
                
                if (isset($historyData['correct_option_uid'])) {
                    $correctUids = is_string($historyData['correct_option_uid']) 
                        ? json_decode($historyData['correct_option_uid'], true) 
                        : $historyData['correct_option_uid'];
                    $correctUids = is_array($correctUids) ? $correctUids : [];
                }
                
                if (isset($historyData['error_option_uid'])) {
                    $errorUids = is_string($historyData['error_option_uid']) 
                        ? json_decode($historyData['error_option_uid'], true) 
                        : $historyData['error_option_uid'];
                    $errorUids = is_array($errorUids) ? $errorUids : [];
                }
                
                // 为每道题添加 is_correct 布尔字段
                foreach ($options as &$option) {
                    $questionUid = $option['uid'] ?? '';
                    
                    // 判断是否正确：在 correctUids 中为 true，否则为 false
                    if (in_array($questionUid, $correctUids)) {
                        $option['is_correct'] = true;
                        $option['is_half_correct'] = false;
                    } elseif (in_array($questionUid, $errorUids)) {
                        $option['is_correct'] = false;
                        
                        // 判断是否半对（多选题）
                        $option['is_half_correct'] = self::checkHalfCorrect($option);
                    } else {
                        // ✅ 新逻辑：既不在正确列表也不在错误列表
                        // 这种情况可能是：
                        // 1. 问答题/填空题/案例题（默认正确）
                        // 2. 案例题的子题（继承案例题的正确性）
                        // 3. 未作答的题目
                        
                        $examType = (int)($option['exam_type'] ?? 0);
                        $hasUserAnswer = !empty($option['user_answer']) && $option['user_answer'] !== '-';
                        
                        if ($hasUserAnswer) {
                            // ✅ 有答案的题目默认正确（兼容问答题/填空题/案例题及其子题）
                            $option['is_correct'] = true;
                            $option['is_half_correct'] = false;
                        } else {
                            // 未作答
                            $option['is_correct'] = false;
                            $option['is_half_correct'] = false;
                        }
                    }
                    
                    // 确保 user_answer 为数组格式，并同时提供字符串格式
                    if (isset($option['user_answer'])) {
                        $userAnswerArray = [];
                        $userAnswerString = '';
                        
                        if (is_string($option['user_answer'])) {
                            $decoded = json_decode($option['user_answer'], true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                $userAnswerArray = $decoded;
                            } else {
                                // 如果不是 JSON，可能是逗号分隔的字符串
                                $userAnswerArray = array_filter(explode(',', $option['user_answer']));
                            }
                        } elseif (is_array($option['user_answer'])) {
                            $userAnswerArray = $option['user_answer'];
                        }
                        
                        // ✅ 生成字符串格式：需要处理嵌套数组（案例题）
                        if (!empty($userAnswerArray)) {
                            // 检查是否是嵌套数组（案例题格式）
                            $firstElement = reset($userAnswerArray);
                            if (is_array($firstElement) && isset($firstElement['answer'])) {
                                // 案例题格式：[{"answer":["A"]}, {"answer":["B"]}]
                                $answers = array_map(function($item) {
                                    if (isset($item['answer']) && is_array($item['answer'])) {
                                        return implode(',', $item['answer']);
                                    }
                                    return '';
                                }, $userAnswerArray);
                                $userAnswerString = implode('; ', array_filter($answers));
                            } else {
                                // 普通题型格式：["A", "B"]
                                $userAnswerString = implode(',', $userAnswerArray);
                            }
                        }
                        
                        // 同时提供两种格式
                        $option['user_answer'] = $userAnswerArray;          // 数组格式（主要）
                        $option['user_answer_str'] = $userAnswerString;     // 字符串格式（备用）
                    }
                    
                    // 统一正确答案格式
                    if (isset($option['answer'])) {
                        $correctAnswerArray = [];
                        $correctAnswerString = '';
                        
                        if (is_string($option['answer'])) {
                            $decoded = json_decode($option['answer'], true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                $correctAnswerArray = $decoded;
                            } else {
                                $correctAnswerArray = array_filter(explode(',', $option['answer']));
                            }
                        } elseif (is_array($option['answer'])) {
                            $correctAnswerArray = $option['answer'];
                        }
                        
                        $correctAnswerString = implode(',', $correctAnswerArray);
                        
                        $option['answer'] = $correctAnswerArray;            // 数组格式（主要）
                        $option['answer_str'] = $correctAnswerString;       // 字符串格式（备用）
                    }
                }
                unset($option); // 解除引用
                
                // ✅ 更新 options 字段：直接赋值数组，ThinkPHP返回时会自动json_encode
                $historyData['options'] = $options;
            }
        }
        
        // 添加统计字段（便于前端直接使用）
        $historyData['total_count'] = ($historyData['correct_count'] ?? 0) + ($historyData['error_count'] ?? 0);
        $historyData['answered_count'] = $historyData['total_count'];
        $historyData['accuracy_rate'] = $historyData['total_count'] > 0 
            ? round(($historyData['correct_count'] ?? 0) / $historyData['total_count'] * 100, 2) 
            : 0;
        
        // 统一时间字段为 13 位时间戳（毫秒）
        if (isset($historyData['submit_time'])) {
            $historyData['submit_time'] = self::normalizeTimestamp($historyData['submit_time']);
        }
        if (isset($historyData['create_time'])) {
            $historyData['create_time'] = self::normalizeTimestamp($historyData['create_time']);
        }
        
        // ✅ practice_duration 已经是秒数，无需转换
        // paper_time 是字符串格式（如 "00:08:22"），不应覆盖 practice_duration
        
        return $historyData;
    }

    /**
     * 格式化提交时间（返回13位时间戳）
     * @param mixed $time 输入的时间值（可能是秒数、时间戳或其他格式）
     * @return int 13位时间戳（毫秒）
     */
    private static function formatSubmitTime($time): int
    {
        // 如果是数字且长度合理，可能是秒数（如考试用时）
        if (is_numeric($time)) {
            $intTime = (int)$time;
            
            // 判断是否为合理的秒数（假设考试时间不超过10小时=36000秒）
            if ($intTime > 0 && $intTime < 86400) {
                // 如果是秒数，返回当前时间的13位时间戳
                return time() * 1000;
            }
            
            // 可能是10位时间戳（秒）
            if ($intTime > 1000000000 && $intTime < 9999999999) {
                return $intTime * 1000; // 转换为13位
            }
            
            // 可能已经是13位时间戳
            if ($intTime > 1000000000000) {
                return $intTime;
            }
        }
        
        // 检查是否已经是有效的日期时间字符串
        $date = date_create($time);
        if ($date !== false) {
            return (int)date_format($date, 'U') * 1000; // 转换为13位时间戳
        }
        
        // 默认为当前时间的13位时间戳
        return time() * 1000;
    }
    
    /**
     * 处理自动消灭错题
     * @param array $correctOptionUid 答对的题目UID列表
     * @param int|string $userUid 用户UID
     * @param array $optionItems 答题数据
     */
    private static function handleAutoEliminateError(array $correctOptionUid, int|string $userUid, array $optionItems): void
    {
        // 如果没有答对题目，直接返回
        if (empty($correctOptionUid)) {
            return;
        }
        
        // 获取用户设置的自动消灭阈值
        $user = User::where('id', $userUid)->find();
        $autoEliminateThreshold = $user ? ($user->auto_eliminate_threshold ?? 2) : 2;
        
        // 将optionItems转换为以uid为键的数组，便于快速查找
        $optionItemsMap = array_column($optionItems, null, 'uid');
        
        // 批量获取错题记录，减少数据库查询次数
        $errorRecords = TenantExamQuestionError::whereIn('question_uid', $correctOptionUid)
            ->where('user_uid', $userUid)
            ->select()
            ->toArray();
        
        // 转换为以question_uid为键的数组，便于快速查找
        $errorRecordsMap = array_column($errorRecords, null, 'question_uid');
        
        // 遍历答对的题目
        foreach ($correctOptionUid as $questionUid) {
            // 检查是否是错题（从数组中查找，避免数据库查询）
            if (isset($errorRecordsMap[$questionUid])) {
                $errorRecord = $errorRecordsMap[$questionUid];
                
                // 获取用户答案
                $userAnswer = $optionItemsMap[$questionUid]['user_answer'] ?? '';
                
                // 更新正确次数
                $newCorrectCount = ($errorRecord['correct_count'] ?? 0) + 1;
                
                // 准备更新数据
                $updateData = [
                    'correct_count' => $newCorrectCount,
                    'update_time' => time(),
                    'user_answer' => $userAnswer
                ];
                
                // 检查是否需要自动消灭
                if ($newCorrectCount >= $autoEliminateThreshold) {
                    $updateData['eliminated_status'] = 1;
                    $updateData['eliminated_at'] = date('Y-m-d H:i:s');
                    $updateData['eliminated_by'] = 'auto';
                }
                
                // 更新错题记录
                TenantExamQuestionError::where('id', $errorRecord['id'])->update($updateData);
            }
        }
    }
    
    /**
     * 答题提交
     * @param array $params 请求参数
     * @return array 提交结果
     * @author 精解析答题
     */
    public static function submitExamination(array $params): array
    {
        // 参数验证
        if (!isset($params['options'], $params['uid'], $params['user_uid'], $params['submit_time'])) {
            self::setError('缺少必要的参数');
            return [];
        }

        $optionItems = $params['options'];
        $uid = uid();
        
        // ✅ 处理前端传来的options：如果是JSON字符串，先解码
        // 前端会JSON.stringify，后端需要解码后再存储，避免双重编码
        if (is_string($optionItems)) {
            $decoded = json_decode($optionItems, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $optionItems = $decoded;
            } else {
                self::setError('答题数据格式错误：无法解析JSON');
                return [];
            }
        }
        
        // 获取当前时间戳（区分 10 位和 13 位）
        $now = time();  // 10位时间戳（秒）
        $nowMillis = $now * 1000;  // 13位时间戳（毫秒）
        
        // 获取练习时长（秒）
        $practiceSeconds = 0;
        if (isset($params['submit_time']) && is_numeric($params['submit_time'])) {
            $practiceSeconds = (int)$params['submit_time'];
        }
        
        // 初始化历史记录数组
        $examinationHistory = [
            'uid'                => $uid,
            'questions_type'     => (int)$params['questions_type'], // 将questions_type转换为整数
            'user_score'         => 0,
            'user_integral'      => 0,
            'tenant_id'          => request()->tenantId,
            'paper_uid'          => '',
            'examination_uid'    => '',
            'library_uid'        => $params['uid'],
            'user_uid'           => $params['user_uid'],
            'paper_score'        => 0,
            'submit_time'        => self::formatSubmitTime($params['submit_time']),
            'paper_time'         => '',
            'options'            => json_encode($optionItems, JSON_UNESCAPED_UNICODE),
            'error_count'        => 0,
            'correct_count'      => 0,
            'error_option_uid'   => json_encode([], JSON_UNESCAPED_UNICODE),
            'correct_option_uid' => json_encode([], JSON_UNESCAPED_UNICODE),
            'basic_score'        => 0,
            'start_time'         => '',
            'end_time'           => '',
            'is_rand'            => $params['is_rand'] ?? 2,
            'options_count'      => 0, // 添加题目数量字段，默认为0
            'practice_duration'  => $practiceSeconds, // ✅ 练习时长（秒）
            'total_count'        => 0, // 总题数
            'answered_count'     => 0, // 已答题数
            'accuracy_rate'      => 0, // 正确率（%）
            'create_time'        => $nowMillis, // 创建时间（13位时间戳）
            'update_time'        => $now, // 更新时间（10位时间戳）
            'delete_time'        => null, // 删除时间（10位时间戳）
            'title'              => date('Y-m-d H:i:s', $now), // 默认标题，确保所有情况都有值
        ];

        try {
            // ✅ 已在前面解码过，这里直接检查是否为数组
            // 安全检查：确保$optionItems是有效的数组且不为空
            if (!is_array($optionItems) || empty($optionItems)) {
                self::setError('无效的答题数据');
                return [];
            }
            
            // 提取题目UID并去重，减少数据库查询量
            $optionUidArray = array_column($optionItems, 'uid');
            if (empty($optionUidArray)) {
                self::setError('答题数据缺少题目标识');
                return [];
            }
            
            $optionUidArray = array_unique($optionUidArray);
            $optionUidArray = array_filter($optionUidArray, function($uid) {
                return !empty($uid);
            });
            
            if (empty($optionUidArray)) {
                self::setError('无效的题目标识');
                return [];
            }
            
            // 获取题目正确答案
            try {
                            
                $questionItems = TenantExamQuestion::query()
                    ->whereIn('uid', $optionUidArray)
                    ->field(['answer', 'uid', 'exam_type', 'integral', 'score', 'chapter_uid', 'exam_level', 'library_uid', 'title'])
                    ->select()
                    ->toArray();
                            
            } catch (\Exception $e) {
                \think\facade\Log::error('获取题目信息失败', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
                //使用中文提示用户获取题目信息失败
                self::setError('获取题目信息失败: ' . $e->getMessage());
                return [];
            }
            
            if (empty($questionItems)) {
                self::setError('未找到对应的题目信息');
                return [];
            }
            // 获取题目正确答案已完成
            // 只构建单选、多选、判断题的题目答案配置数组，填空题和案例题默认全对
            $questionAnswerConfig = [];
            foreach ($questionItems as $value) {
                $questionExamType = $value['exam_type'] ?? 0;
                
                // 对于单选、多选、判断题，构建答案配置数组
                if ($questionExamType === self::EXAM_TYPE_SINGLE || 
                    $questionExamType === self::EXAM_TYPE_MULTIPLE || 
                    $questionExamType === self::EXAM_TYPE_JUDGMENT) {
                    // 确保answer字段存在且不为空
                    if (!empty($value['answer'])) {
                        // 解析answer字段，可能是JSON字符串或数组
                        $answer = $value['answer'];
                        if (is_string($answer)) {
                            // 处理格式不正确的JSON字符串，例如："["C"]"
                            $cleanAnswer = trim($answer, '"');
                            
                            // 尝试解析JSON字符串
                            $parsedAnswer = json_decode($cleanAnswer, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($parsedAnswer)) {
                                $answer = $parsedAnswer;
                            } else {
                                // 如果解析失败，尝试处理转义字符
                                $cleanAnswer = stripslashes($cleanAnswer);
                                $parsedAnswer = json_decode($cleanAnswer, true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($parsedAnswer)) {
                                    $answer = $parsedAnswer;
                                } else {
                                    // 如果还是解析失败，尝试直接提取答案
                                    preg_match_all('/["\']([A-Za-z0-9])["\']/', $cleanAnswer, $matches);
                                    if (!empty($matches[1])) {
                                        $answer = $matches[1];
                                    } else {
                                        // 如果还是失败，将其作为单个答案处理
                                        $answer = [$cleanAnswer];
                                    }
                                }
                            }
                        }
                        
                        if (is_array($answer) && !empty($answer)) {
                            $sortedAnswer = $answer;
                            sort($sortedAnswer); // 排序确保答案格式一致性
                            $questionAnswerConfig[$value['uid']] = implode(',', $sortedAnswer);
                        }
                    }
                }
                // 填空题、问答题和案例题不需要构建答案配置，将在后续处理中默认标记为正确
            }

            // 将questions_type转换为整数，避免类型比较问题
            $questionsType = (int)$params['questions_type'];  
            
            $optionTypeConfig = null;
            
            //根据questionParams.mode判断是否是考试模式
            $practice_mode = '学练结合'; // 默认值
            if(!empty($params['questionParams']['mode'])){
               if($params['questionParams']['mode'] === 'normal'){
                $practice_mode='考试模式';
            }elseif($params['questionParams']['mode'] === 'reviewOnly'){
                $practice_mode='背题模式';
            }else{
                $practice_mode='学练结合';
             }
            }
            
            // 考试时间处理，使用当前时间作为结束时间，开始时间设为结束时间减去练习时长
            $examinationHistory['end_time'] = date('Y-m-d H:i:s');
            $examinationHistory['start_time'] = date('Y-m-d H:i:s', strtotime($examinationHistory['end_time']) - $practiceSeconds);
            

            // 初始化isExamination为false
            $isExamination = false;
            $ExaminationConfig = null;
            $MockExaminationConfig = null;
            
            // 如果是试卷考试（questions_type为2），检查考试是否存在
            if ($questionsType === 2) {
                $ExaminationConfig = TenantExamExamination::query()
                    ->where('uid', '=', $params['uid'])
                    ->field(['uid', 'title', 'exam_time', 'paper_uid', 'score'])
                    ->findOrEmpty();
                $isExamination = !$ExaminationConfig->isEmpty();
            } 
            // 如果是模拟考试（questions_type为7），检查考试是否存在
            elseif ($questionsType === 7) {
                $MockExaminationConfig = TenantMockExamExamination::query()
                    ->where('uid', '=', $params['uid'])
                    ->field(["uid", "paper_score", "score", "exam_time", "options","create_time"])
                    ->findOrEmpty();
                $isExamination = !$MockExaminationConfig->isEmpty();
            }
            
            // 考试模式处理
            if ($isExamination) {             
                if (!empty($MockExaminationConfig)) {
                    // 模拟考试没有试卷配置
                   $examinationHistory['paper_uid'] = $MockExaminationConfig->uid;
                   $examinationHistory['examination_uid'] = $MockExaminationConfig->uid;
                   $examinationHistory['basic_score'] = $MockExaminationConfig->score ?? 0;
                   $examinationHistory['is_rand'] = 2;
                   $examinationHistory['paper_time'] = $MockExaminationConfig->exam_time ?? 0;
                   $examinationHistory['paper_score'] = $MockExaminationConfig->paper_score ?? 0;
                   // 将 create_time 转换为时间戳再格式化
                   $createTime = is_numeric($MockExaminationConfig->create_time) 
                       ? (int)$MockExaminationConfig->create_time 
                       : strtotime( $MockExaminationConfig->create_time);
                   $examinationHistory['title'] = date('Y-m-d H:i:s', $createTime) . '创建的模拟考试';
                   // 模拟考试使用 option_type_config 字段
                   $optionTypeConfig = json_decode($MockExaminationConfig->options ?? '[]', true);
                }else{
                    $paperConfig = TenantExamPaper::query()
                    ->where('uid', '=', $ExaminationConfig->paper_uid)
                    ->field(['uid', 'is_rand', 'option_score', 'option_config'])
                    ->findOrEmpty();
                    // 更新历史记录中的考试信息
                    $examinationHistory['is_rand'] = $paperConfig->is_rand ?? 2;
                    $examinationHistory['basic_score'] = $ExaminationConfig->score ?? 0;
                    $examinationHistory['paper_score'] = $paperConfig->option_score ?? 0;
                    $examinationHistory['paper_uid'] = $paperConfig->uid;
                    $examinationHistory['examination_uid'] = $ExaminationConfig->uid;
                    $examinationHistory['title'] = $ExaminationConfig->title ?? '';
                    
                    // 解析题目类型配置，兼容空值
                    $optionTypeConfig = json_decode($paperConfig->option_config ?? '[]', true);
                }
            }else {
                // 对于非考试模式的所有questionsType，生成标题
                $prefix = '[' . $practice_mode . '] ';

                // 生成考试历史标题
                if($questionsType === 1){
                    // ✅ 优先从 questionParams.chapter_uid 获取，其次从 paper_uid 获取
                    $chapterUid = $params['questionParams']['chapter_uid'] ?? $params['paper_uid'] ?? '';
                    $examinationHistory['paper_uid'] = $chapterUid;

                    // 1. 章节练习标题生成
                    if (!empty($chapterUid)) {
                        try {
                            $chapter = TenantExamChapter::query()
                                ->where('uid', '=', $chapterUid)
                                ->field(['uid', 'title', 'parent_uid']) // 使用数组形式指定字段，更清晰
                                ->with(['parent' => function ($query) {
                                    $query->where('is_show', 1)->field(['uid', 'title']);
                                }])
                                ->find();
                            
                            $chapterTitle = $chapter ? (
                                $chapter->parent ? "{$chapter->parent->title}-{$chapter->title}" : $chapter->title
                            ) : '未知章节';
                            $examinationHistory['title'] = $prefix . $chapterTitle;
                        } catch (\Exception $e) {
                            \think\facade\Log::error('获取章节名称失败: ' . $e->getMessage(), [
                                'chapter_uid' => $chapterUid,
                                'file' => $e->getFile(),
                                'line' => $e->getLine()
                            ]);
                            $examinationHistory['title'] = $prefix . '未知章节';
                        }
                    } 
                    // 2. 其他类型练习标题生成
                    else {
                        // 从数据字典中查询练习类型名称
                        $dictData = \app\common\model\dict\TenantDictData::query()->where([
                            ['tenant_id', '=', request()->tenantId],
                            ['type_value', '=', 'questions_type'],
                            ['status', '=', 1]
                        ])->whereNull('delete_time')
                        ->column(['value', 'name']);
                        
                        // 将字典数据转换为关联数组，方便查询
                        $dictDataGroup = [];
                        foreach ($dictData as $item) {
                            $dictDataGroup[$item['value']] = $item['name'];
                        }
                        
                        // 获取对应的练习类型名称
                        $exerciseTypeName = $dictDataGroup[$questionsType] ?? '';
                        
                        if (!empty($exerciseTypeName)) {
                            $examinationHistory['title'] = $prefix . $exerciseTypeName;
                        } else {
                            // 默认标题，确保所有情况都有有效值
                            $examinationHistory['title'] = $prefix . date('Y-m-d H:i:s');
                        }
                    }
                } else {
                    // 处理其他questionsType值，包括questionsType=8
                    // 从数据字典中查询练习类型名称
                    $dictData = \app\common\model\dict\TenantDictData::query()->where([
                        ['tenant_id', '=', request()->tenantId],
                        ['type_value', '=', 'questions_type'],
                        ['status', '=', 1]
                    ])->whereNull('delete_time')
                    ->column(['value', 'name']);
                    
                    // 将字典数据转换为关联数组，方便查询
                    $dictDataGroup = [];
                    foreach ($dictData as $item) {
                        $dictDataGroup[$item['value']] = $item['name'];
                    }
                    
                    // 获取对应的练习类型名称
                    $exerciseTypeName = $dictDataGroup[$questionsType] ?? '';
                    
                    if (!empty($exerciseTypeName)) {
                        $examinationHistory['title'] = $prefix . $exerciseTypeName;
                    } else {
                        // 默认标题，确保所有情况都有有效值
                        $examinationHistory['title'] = $prefix . date('Y-m-d H:i:s');
                    }
                }
                
                $optionTypeConfig = $questionItems;
            }
        
           
            // 初始化评分配置
            $scoreConfigs = self::initializeScoreConfigs($optionTypeConfig, $isExamination);
           
            // 处理用户答题结果
            $result = self::processUserAnswers($optionItems, $questionAnswerConfig, $scoreConfigs, $examinationHistory);
            
            // 保存答题结果到临时变量，防止被意外修改
            $correctOptionUid = $result['correctOptionUid'];
            $errorOptionUid = $result['errorOptionUid'];
            
            // 更新历史记录中的答题结果
            $examinationHistory['error_option_uid'] = json_encode($errorOptionUid, JSON_UNESCAPED_UNICODE);
            $examinationHistory['correct_option_uid'] = json_encode($correctOptionUid, JSON_UNESCAPED_UNICODE);
            
            // 设置题目数量和统计字段
            if (isset($params['questionParams']['options']) && is_array($params['questionParams']['options'])) {
                $examinationHistory['options_count'] = count($params['questionParams']['options']);
            } else {
                $examinationHistory['options_count'] = count($optionItems);
            }
            
            // 计算统计字段（基于最新数据）
            $examinationHistory['total_count'] = $examinationHistory['options_count'];
            $examinationHistory['answered_count'] = $examinationHistory['correct_count'] + $examinationHistory['error_count'];
            $examinationHistory['accuracy_rate'] = $examinationHistory['total_count'] > 0 
                ? round(($examinationHistory['correct_count'] / $examinationHistory['total_count']) * 100, 2) 
                : 0;
            
            // 处理用户答题积分
            self::handleUserIntegral($examinationHistory);
            
            //将答错题目存入错题表
            if (!empty($errorOptionUid)) {
                $tenantId = request()->tenantId;
                $userUid = $examinationHistory['user_uid'];
                $now = time();
                
                // 将optionItems转换为以question_uid为键的数组，便于快速查找
                $optionItemsMap = array_column($optionItems, null, 'uid');
                
                // 批量获取已存在的错题记录
                $existingErrors = TenantExamQuestionError::query()
                    ->whereIn('question_uid', $errorOptionUid)
                    ->where('user_uid', $userUid)
                    ->select()
                    ->toArray();
                
                // 转换为以question_uid为键的数组，便于快速查找
                $existingErrorsMap = array_column($existingErrors, null, 'question_uid');
                
                // 将questionItems转换为以uid为键的数组，便于快速查找题目信息
                $questionItemsMap = array_column($questionItems, null, 'uid');
                
                // 遍历答错的题目，插入错题表
                foreach ($errorOptionUid as $questionUid) {
                    // 检查错题是否已存在
                    $exists = $existingErrorsMap[$questionUid] ?? null;
                    
                    // 获取用户答案
                    $userAnswer = $optionItemsMap[$questionUid]['user_answer'] ?? '';
                    
                    // 使用已获取的题目信息，避免重复查询数据库
                    $questionInfo = $questionItemsMap[$questionUid] ?? [];
                 
                    // 准备插入错题数据
                    $errorData = [
                        'tenant_id'      => $tenantId,
                        'library_uid'    => $questionInfo['library_uid'] ?? '',
                        'question_uid'   => $questionUid,
                        'user_uid'       => $userUid,
                        'exam_type'      => $questionInfo['exam_type'] ?? null ?: 0, // 使用?:确俛null也被转为0
                        'exam_level'          => $questionInfo['exam_level'] ?? 0,
                        'score'          => $questionInfo['score'] ?? 0,
                        'title'          => $questionInfo['title'] ?? '',
                        'chapter_uid'    => $questionInfo['chapter_uid'] ?? '',
                        'user_answer'    => $userAnswer,
                        'correct_answer' => $questionInfo['answer'] ?? '',
                        'questions_type' => $params['questions_type'] ?? 1, // 使用传入的做题类型
                        'practice_mode'  => $params['practice_mode'] ?? 0,
                        'examination_uid' => $params['history_uid'] ?? '',
                        'update_time'    => $now,
                    ];
                    
                    // 如果不存在，则插入
                    if (!$exists) {
                        $errorData['create_time'] = $now;
                        $errorData['error_count'] = 1;
                        $errorData['is_high_frequency'] = 0;

                        $result = TenantExamQuestionError::create($errorData);

                    } else {
                        // 更新错误次数
                        $errorData['error_count'] = ($exists['error_count'] ?? 0) + 1;
                        // 判断是否为高频错题
                        $errorData['is_high_frequency'] = $errorData['error_count'] >= 2 ? 1 : 0;
                    
                    $result = TenantExamQuestionError::query()
                        ->where('id', $exists['id'])
                        ->update($errorData);

                    }
                }
            }
            
            // 新增：处理自动消灭错题
            self::handleAutoEliminateError($correctOptionUid, $examinationHistory['user_uid'], $optionItems);

            // 处理错题消灭提交
            // 注意：questions_error可能是字符串"1"或整数1，需要处理类型转换
            $questionsError = $params['questionParams']['questions_error'] ?? 0;
            $isErrorEliminate = (int)$questionsError === 1;
            
            if ($isErrorEliminate) {
                $tenantId = request()->tenantId;
                $userUid = $examinationHistory['user_uid'];
                $now = time();
                
                // 获取要消灭的错题ID，优先从analysis_quid获取
                $analysisQuid = $params['questionParams']['analysis_quid'] ?? '';
                $questionUids = [];
                                
                // 处理analysis_quid，支持单个ID或逗号分隔的多个ID
                if (!empty($analysisQuid)) {
                    if (is_array($analysisQuid)) {
                        $questionUids = $analysisQuid;
                    } else {
                        $questionUids = explode(',', $analysisQuid);
                    }
                } else {
                    // 如果没有analysis_quid，使用correctOptionUid
                    $questionUids = $correctOptionUid;
                }
                
                // 去重并过滤空值
                $questionUids = array_filter(array_unique($questionUids));
                
                // 遍历要消灭的题目，更新错题表为已消灭状态
                foreach ($questionUids as $questionUid) {
                    
                    // 查找所有状态的错题记录
                    $errorRecord = TenantExamQuestionError::query()
                        ->where('question_uid', $questionUid)
                        ->where('user_uid', $userUid)
                        ->find();
                    
                    // 存在该错题记录
                    if ($errorRecord) {
                        $oldStatus = $errorRecord->eliminated_status;                        
                        try {
                            if ($oldStatus == 0) {
                                // 更新错题表状态为已消灭
                                try {
                                    TenantExamQuestionError::query()
                                        ->where('id', $errorRecord->id)
                                        ->update([
                                            'eliminated_status' => 1,
                                            'eliminated_at' => date('Y-m-d H:i:s'),
                                        'eliminated_by' => 'auto', // 自动消灭
                                        'update_time' => $now
                                    ]);
                                } catch (\Exception $e) {
                                    self::setError('更新错题表状态失败: ' . $e->getMessage());
                                }
                                    
                                
                                // 记录消灭日志
                                $insertId = TenantExamQuestionErrorLog::create([
                                    'tenant_id' => $tenantId,
                                    'question_error_uid' => $errorRecord->id,
                                    'user_uid' => $userUid,
                                    'old_status' => 0,
                                    'new_status' => 1,
                                    'create_time' => date('Y-m-d H:i:s'),
                                    'eliminated_by' => 'auto',
                                    'ip' => request()->ip()
                                ]);
                                
                            } 
                            
                            // 修正统计数据
                            if (!in_array($questionUid, $correctOptionUid)) {
                                $examinationHistory['correct_count'] += 1;
                                $examinationHistory['error_count'] = max(0, $examinationHistory['error_count'] - 1);
                            }
                        } catch (\Exception $e) {
                            self::setError('自动消灭错题失败: ' . $e->getMessage());
                            \think\facade\Log::error('自动消灭错题失败', [
                                'questionUid' => $questionUid,
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                        }
                    }
                }
                
                // 错题消灭提交不需要保存历史记录，但需要更新统计数据
                // 修复：检查questionParams中是否有options，否则使用count(correctOptionUid + errorOptionUid)
                $examinationHistory['options_count'] = count($correctOptionUid) + count($errorOptionUid);
                
                // 更新题目统计数据
                self::updateQuestionStatistics($optionItems, $correctOptionUid, $errorOptionUid);
                
                // 更新用户章节统计数据（基于 chapter_uid 判断）
                // 只要题目中包含 chapter_uid 字段，就自动更新章节统计
                self::updateChapterStatistics(
                    $optionItems, 
                    $correctOptionUid, 
                    $errorOptionUid, 
                    (string)$params['user_uid'],  // 确保转换为string类型
                    (int)request()->tenantId      // 确保转换为int类型
                );
                
                // 生成个性化答题提示
                $msg = self::generateExamMessage($questionsType, $examinationHistory, $correctOptionUid, $errorOptionUid);
                
                return [
                    'msg'                    => $msg,
                    'correct_count'          => $examinationHistory['correct_count'],
                    'error_count'            => $examinationHistory['error_count'],
                    'score'                  => $examinationHistory['user_score'],
                    'integral'               => $examinationHistory['user_integral'],
                    'daily_integral_limit'   => $examinationHistory['daily_integral_limit'] ?? 0,
                    'today_integral'         => $examinationHistory['today_integral'] ?? 0,
                    'remaining_integral'     => $examinationHistory['remaining_integral'] ?? 0
                ];
            }

            // 保存考试历史记录
            try {
                // 使用数据库连接直接插入，绕过模型的钩子方法
                $db = \think\facade\Db::name('tenant_exam_examination_history');
                
                // 从examinationHistory中移除数据库表不存在的字段
                $insertData = $examinationHistory;
                unset($insertData['daily_integral_limit'], $insertData['today_integral'], $insertData['remaining_integral']);
                
                $insertId = $db->insertGetId($insertData);
                
                
                if (!empty($insertId)) {
                    // 保存成功后，异步更新题目统计数据
                    self::updateQuestionStatistics($optionItems, $correctOptionUid, $errorOptionUid);
                    
                    // 更新用户章节统计数据（基于 chapter_uid 判断）
                    // 只要题目中包含 chapter_uid 字段，就自动更新章节统计
                    self::updateChapterStatistics(
                        $optionItems, 
                        $correctOptionUid, 
                        $errorOptionUid, 
                        (string)$params['user_uid'],  // 确保转换为string类型
                        (int)request()->tenantId      // 确保转换为int类型
                    );
                    
                    // 生成个性化答题提示
                    $msg = self::generateExamMessage($questionsType, $examinationHistory, $correctOptionUid, $errorOptionUid);
                    
                    return [
                        'history_id'             => $insertId,
                        'msg'                    => $msg,
                        'correct_count'          => $examinationHistory['correct_count'],
                        'error_count'            => $examinationHistory['error_count'],
                        'score'                  => $examinationHistory['user_score'],
                        'integral'               => $examinationHistory['user_integral'],
                        'daily_integral_limit'   => $examinationHistory['daily_integral_limit'] ?? 0,
                        'today_integral'         => $examinationHistory['today_integral'] ?? 0,
                        'remaining_integral'     => $examinationHistory['remaining_integral'] ?? 0
                    ];
                }
                
                self::setError('保存考试历史记录失败');
            } catch (\Exception $e) {
                self::setError('保存考试历史记录失败: ' . $e->getMessage());
            }
        } catch (\Exception $exception) {
            // 记录异常信息到日志
            \think\facade\Log::error('提交考试数据时发生异常', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ]);
            self::setError('提交考试数据时发生异常: ' . $exception->getMessage());
        }
        
        return [];
    }
    
    /**
     * 生成个性化答题提示
     * @param int $questionsType 答题类型
     * @param array $examinationHistory 考试历史记录
     * @param array $correctOptionUid 正确题目UID数组
     * @param array $errorOptionUid 错误题目UID数组
     * @return string 个性化提示信息
     */
    private static function generateExamMessage(int $questionsType, array $examinationHistory, array $correctOptionUid, array $errorOptionUid): string
    {
        // 获取答题统计信息
        $correctCount = $examinationHistory['correct_count'];
        $errorCount = $examinationHistory['error_count'];
        $totalCount = $correctCount + $errorCount;
        $score = $examinationHistory['user_score'];
        $integral = $examinationHistory['user_integral'];
        
        // 获取积分相关信息
        $dailyIntegralLimit = $examinationHistory['daily_integral_limit'] ?? 0;
        $todayIntegral = $examinationHistory['today_integral'] ?? 0;
        $remainingIntegral = $examinationHistory['remaining_integral'] ?? $dailyIntegralLimit;
        
        // 根据答题类型生成个性化提示
        switch ($questionsType) {
            case 1: // 章节练习
                $typeName = '章节练习';
                break;
            case 2: // 试卷考试
                $typeName = '试卷考试';
                break;
            case 3: // 每日一练
                $typeName = '每日一练';
                break;
            case 4: // 举一反三
                $typeName = '举一反三';
                break;
            case 5: // 错题练习
                $typeName = '错题练习';
                break;
            case 6: // 收藏练习
                $typeName = '收藏练习';
                break;
            case 7: // 模拟考试
                $typeName = '模拟考试';
                break;
            case 8: // 顺序练习
                $typeName = '顺序练习';
                break;
            default:
                $typeName = '答题';
        }
        
        // 生成基础提示
        $msg = $typeName . '完成！';
        
        // 根据答题结果生成不同的提示
        if ($totalCount > 0) {
            $accuracyRate = round(($correctCount / $totalCount) * 100, 1);
            
            if ($errorCount === 0) {
                $msg .= "\n恭喜你全部答对！";
            } elseif ($correctCount === 0) {
                $msg .= "\n别灰心，继续努力！";
            } else {
                $msg .= "\n答对" . $correctCount . "题，答错" . $errorCount . "题，正确率" . $accuracyRate . "%。";
            }
            
            // 添加得分和积分信息
            $msg .= "\n得分" . $score . "，积分+" . $integral . "。";
        }
        
        // 添加积分上限和剩余积分信息
        if ($dailyIntegralLimit > 0) {
            $msg .= "\n答题积分上限" . $dailyIntegralLimit .  "/天，已获得（" . $todayIntegral . "/" . $dailyIntegralLimit . "）。";
        }
        
        return $msg;
    }
    
    /**
     * 初始化评分配置
     * @param array $optionTypeConfig 题目类型配置
     * @param bool $isExamination 是否为考试模式
     * @return array 评分配置
     */
    private static function initializeScoreConfigs(?array $optionTypeConfig, bool $isExamination): array
    {
        // 默认为普通答题模式的评分配置，添加integral字段默认值
        $configs = [
            'option' => ['score' => 1, 'integral' => 1],    // 单选题
            'checkbox' => ['score' => 1, 'integral' => 1],  // 多选题
            'jude' => ['score' => 1, 'integral' => 1],      // 判断题
            'write' => ['score' => 1, 'integral' => 1],     // 问答题
            'fill' => ['score' => 1, 'integral' => 1],      // 填空题
            'case' => ['score' => 1, 'integral' => 1]       // 案例题
        ];
        
        // 确保$optionTypeConfig是有效的数组
        if (!is_array($optionTypeConfig)) {
            return $configs;
        }
        
        // 初始化题目特定的评分配置
        $configs['questions'] = [];
        
        // 考试模式下，使用试卷配置的评分规则
        if ($isExamination && !empty($optionTypeConfig)) {
            foreach ($optionTypeConfig as $value) {
                // 确保$value是数组
                if (!is_array($value)) {
                    continue;
                }
                $typeValue = (int)($value['exam_type'] ?? 0);
                $integral = (int)($value['integral'] ?: 1);
                $score = (int)($value['score'] ?? 1);
                
                switch ($typeValue) {
                    case self::EXAM_TYPE_SINGLE: // 单选题
                        $configs['option']['score'] = $score;
                        $configs['option']['integral'] = $integral;
                        break;
                    case self::EXAM_TYPE_MULTIPLE: // 多选题
                        $configs['checkbox']['score'] = $score;
                        $configs['checkbox']['integral'] = $integral;
                        break;
                    case self::EXAM_TYPE_JUDGMENT: // 判断题
                        $configs['jude']['score'] = $score;
                        $configs['jude']['integral'] = $integral;
                        break;
                    case self::EXAM_TYPE_FILL: // 填空题
                        $configs['fill']['score'] = $score;
                        $configs['fill']['integral'] = $integral;
                        break;
                    case self::EXAM_TYPE_CASE: // 案例题
                        $configs['case']['score'] = $score;
                        $configs['case']['integral'] = $integral;
                        break;
                    case self::EXAM_TYPE_QUESTION: // 问答题
                        $configs['write']['score'] = $score;
                        $configs['write']['integral'] = $integral;
                        break;
                }
            }
        } else {
            // 非考试模式下，使用题目本身的积分和分数配置
            foreach ($optionTypeConfig as $question) {
                // 确保$question是数组且有uid字段
                if (!is_array($question) || empty($question['uid'])) {
                    continue;
                }
                
                $questionUid = $question['uid'];
                $questionExamType = (int)($question['exam_type'] ?? 0);
                $integral = (float)($question['integral'] ?: 1);
                $score = (float)($question['score'] ?? 1);
                
                // 为每个题目设置特定的评分配置
                $configs['questions'][$questionUid] = [
                    'score' => $score,
                    'integral' => $integral
                ];
                
                // 同时更新对应题型的默认配置，确保兼容性
                switch ($questionExamType) {
                    case self::EXAM_TYPE_SINGLE: // 单选题
                        $configs['option']['score'] = $score;
                        $configs['option']['integral'] = $integral;
                        break;
                    case self::EXAM_TYPE_MULTIPLE: // 多选题
                        $configs['checkbox']['score'] = $score;
                        $configs['checkbox']['integral'] = $integral;
                        break;
                    case self::EXAM_TYPE_JUDGMENT: // 判断题
                        $configs['jude']['score'] = $score;
                        $configs['jude']['integral'] = $integral;
                        break;
                    case self::EXAM_TYPE_FILL: // 填空题
                        $configs['fill']['score'] = $score;
                        $configs['fill']['integral'] = $integral;
                        break;
                    case self::EXAM_TYPE_QUESTION: // 问答题
                        $configs['write']['score'] = $score;
                        $configs['write']['integral'] = $integral;
                        break;
                    case self::EXAM_TYPE_CASE: // 案例题
                        $configs['case']['score'] = $score;
                        $configs['case']['integral'] = $integral;
                        break;
                }
            }
        }
        
        return $configs;
    }
    
    /**
     * 计算用户积分
     * @param int $questionExamType 题目类型
     * @param string $questionUid 题目UID
     * @param array $scoreConfigs 分数配置
     * @param array &$examinationHistory 考试历史记录（引用传递）
     */
    private static function calculateUserIntegral(int $questionExamType, string $questionUid, array $scoreConfigs, array &$examinationHistory): void
    {
        // 获取题目特定的评分配置，如果不存在则使用默认配置
        $questionScoreConfig = $scoreConfigs['questions'][$questionUid] ?? [];
        
        // 根据题目类型和特定配置计算积分
        switch ($questionExamType) {
            case self::EXAM_TYPE_SINGLE: // 单选题
                $integral = $questionScoreConfig['integral'] ?? $scoreConfigs['option']['integral'] ?? 0;
                $examinationHistory['user_integral'] += $integral > 0 ? $integral : 1;
                break;
            case self::EXAM_TYPE_MULTIPLE: // 多选题
                $integral = $questionScoreConfig['integral'] ?? $scoreConfigs['checkbox']['integral'] ?? 0;
                $examinationHistory['user_integral'] += $integral > 0 ? $integral : 1;
                break;
            case self::EXAM_TYPE_JUDGMENT: // 判断题
                $integral = $questionScoreConfig['integral'] ?? $scoreConfigs['jude']['integral'] ?? 0;
                $examinationHistory['user_integral'] += $integral > 0 ? $integral : 1;
                break;
            case self::EXAM_TYPE_FILL:// 填空题
                $integral = $questionScoreConfig['integral'] ?? $scoreConfigs['fill']['integral'] ?? 0;
                $examinationHistory['user_integral'] += $integral > 0 ? $integral : 1;
                break;
            case self::EXAM_TYPE_QUESTION:// 问答题
                $integral = $questionScoreConfig['integral'] ?? $scoreConfigs['write']['integral'] ?? 0;
                $examinationHistory['user_integral'] += $integral > 0 ? $integral : 1;
                break;
            case self::EXAM_TYPE_CASE:// 案例题
                $integral = $questionScoreConfig['integral'] ?? $scoreConfigs['case']['integral'] ?? 0;
                $examinationHistory['user_integral'] += $integral > 0 ? $integral : 1;
                break;
        }
    }
    
    /**
     * 处理用户答案
     * @param array $optionItems 用户答题数据
     * @param array $questionAnswerConfig 题目答案配置数组
     * @param array $scoreConfigs 分数配置
     * @param array $examinationHistory 考试历史记录（引用传递）
     * @return array 包含正确和错误题目UID的数组
     */
    private static function processUserAnswers(array $optionItems, array $questionAnswerConfig, array $scoreConfigs, array &$examinationHistory): array
    {
        $errorOptionUid = [];
        $correctOptionUid = [];
        
        foreach ($optionItems as $value) {
            // 跳过没有uid的题目
            if (empty($value['uid'])) {
                continue;
            }
            
            $questionExamType = (int)($value['exam_type'] ?? 0);
            $questionUid = $value['uid'];
            
            // 预定义题目类型映射，提高判断效率
            $scoreTypeMap = [
                self::EXAM_TYPE_SINGLE => 'option',
                self::EXAM_TYPE_MULTIPLE => 'checkbox',
                self::EXAM_TYPE_JUDGMENT => 'jude',
                self::EXAM_TYPE_FILL => 'fill',
                self::EXAM_TYPE_QUESTION => 'write',
                self::EXAM_TYPE_CASE => 'case'
            ];
            
            // 检查是否为填空题、问答题或案例题。默认全部正确
            $isFillType = $questionExamType === self::EXAM_TYPE_FILL || $questionExamType === self::EXAM_TYPE_QUESTION || $questionExamType === self::EXAM_TYPE_CASE;
            
            if ($isFillType) {
                $examinationHistory['correct_count'] += 1;
                $correctOptionUid[] = $questionUid;
                
                // 根据题目类型加分
                $scoreType = $scoreTypeMap[$questionExamType] ?? '';
                if (!empty($scoreType) && isset($scoreConfigs[$scoreType])) {
                    $examinationHistory['user_score'] += $scoreConfigs[$scoreType]['score'] ?? 0;
                }
                
                // 使用calculateUserIntegral方法计算积分
                self::calculateUserIntegral($questionExamType, $questionUid, $scoreConfigs, $examinationHistory);
                continue; // 跳过后续逻辑处理
            } else {
                // 优先使用提交数据中的is_correct字段来判断答案的正确性
                if (isset($value['is_correct']) && $value['is_correct'] !== null) {
                    // 将is_correct字段转换为布尔值，处理各种类型的输入
                    $isCorrectValue = $value['is_correct'];
                    $isCorrect = false;
                    
                    if (is_bool($isCorrectValue)) {
                        $isCorrect = $isCorrectValue;
                    } elseif (is_string($isCorrectValue)) {
                        $isCorrect = strtolower($isCorrectValue) === 'true';
                    } elseif (is_numeric($isCorrectValue)) {
                        $isCorrect = (int)$isCorrectValue === 1;
                    }
                    
                    if ($isCorrect) {
                        // 答案正确
                        $examinationHistory['correct_count'] += 1;
                        $correctOptionUid[] = $questionUid;
                        
                        // 获取题目特定的评分配置和分数类型
                        $questionScoreConfig = $scoreConfigs['questions'][$questionUid] ?? [];
                        $scoreType = $scoreTypeMap[$questionExamType] ?? '';
                        
                        // 根据题目类型和特定配置加分
                        if (!empty($scoreType)) {
                            $examinationHistory['user_score'] += $questionScoreConfig['score'] ?? $scoreConfigs[$scoreType]['score'] ?? 0;
                        }
                        
                        // 使用calculateUserIntegral方法计算积分
                        self::calculateUserIntegral($questionExamType, $questionUid, $scoreConfigs, $examinationHistory);
                    } else {
                        // 答案错误
                        $examinationHistory['error_count'] += 1;
                        $errorOptionUid[] = $questionUid;
                    }
                    
                    // 直接返回，不再执行传统的答案比较方法
                    continue;
                } else {
                    // 没有is_correct字段或is_correct字段的值为null，使用传统的答案比较方法
                    if (isset($questionAnswerConfig[$questionUid])) {
                        // 收集用户选中的答案
                        $userSelectedAnswer = [];
                        
                        // 优先检查user_answer字段，这是前端直接提交的答案，更可靠
                        if (isset($value['user_answer'])) {
                            $userAnswer = $value['user_answer'];
                            if (is_string($userAnswer)) {
                                // 尝试解析JSON字符串
                                $parsedUserAnswer = json_decode($userAnswer, true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($parsedUserAnswer)) {
                                    $userSelectedAnswer = $parsedUserAnswer;
                                } else {
                                    // 如果解析失败，将其作为单个答案处理
                                    $userSelectedAnswer = [$userAnswer];
                                }
                            } elseif (is_array($userAnswer)) {
                                $userSelectedAnswer = $userAnswer;
                            }
                        }
                        
                        // 如果user_answer中没有答案，再检查option中的选中状态
                        if (empty($userSelectedAnswer) && !empty($value['option']) && is_array($value['option'])) {
                            foreach ($value['option'] as $v) {
                                // 检查is_selected字段或status字段
                                if (($v['is_selected'] ?? false) || ($v['status'] ?? '') === 'selected') {
                                    $userSelectedAnswer[] = $v['check'] ?? '';
                                }
                            }
                        }
                        
                        // 过滤掉空值
                        $userSelectedAnswer = array_filter($userSelectedAnswer, function($answer) {
                            return !empty($answer);
                        });
                        
                        // 处理用户答案（排序并拼接）
                        sort($userSelectedAnswer);
                        $userSelectedAnswerStr = implode(',', $userSelectedAnswer);
                        
                        // 判断答案是否正确
                        if ($userSelectedAnswerStr == $questionAnswerConfig[$questionUid]) {
                            // 答案正确
                            $examinationHistory['correct_count'] += 1;
                            $correctOptionUid[] = $questionUid;
                            
                            // 获取题目特定的评分配置，如果不存在则使用默认配置
                            $questionScoreConfig = $scoreConfigs['questions'][$questionUid] ?? [];
                            
                            // 根据题目类型和特定配置加分
                            // 添加分数
                            switch ($questionExamType) {
                                case self::EXAM_TYPE_SINGLE: // 单选题
                                    $examinationHistory['user_score'] += $questionScoreConfig['score'] ?? $scoreConfigs['option']['score'] ?? 0;
                                    break;
                                case self::EXAM_TYPE_MULTIPLE: // 多选题
                                    $examinationHistory['user_score'] += $questionScoreConfig['score'] ?? $scoreConfigs['checkbox']['score'] ?? 0;
                                    break;
                                case self::EXAM_TYPE_JUDGMENT: // 判断题
                                    $examinationHistory['user_score'] += $questionScoreConfig['score'] ?? $scoreConfigs['jude']['score'] ?? 0;
                                    break;
                            }
                            // 使用calculateUserIntegral方法计算积分
                            self::calculateUserIntegral($questionExamType, $questionUid, $scoreConfigs, $examinationHistory);
                        } else {
                            // 答案错误
                            $examinationHistory['error_count'] += 1;
                            $errorOptionUid[] = $questionUid;
                        }
                    } else {
                        // 没有找到答案配置，可能是题目数据异常，默认标记为错误
                        $examinationHistory['error_count'] += 1;
                        $errorOptionUid[] = $questionUid;
                    }
                }
            }
            
           
        }
        
        return [
            'errorOptionUid' => $errorOptionUid,
            'correctOptionUid' => $correctOptionUid
        ];
    }
    
    /**
     * 处理用户答题积分
     * @param array $examinationHistory 考试历史记录（引用传递）
     */
    private static function handleUserIntegral(array &$examinationHistory): void
    {
        try {
            // 获取每日积分上限
            $dailyIntegralLimit = IntegralEnum::IntegralAmount(IntegralEnum::User_EXAMINATION);
            
            // 初始化积分相关信息
            $examinationHistory['daily_integral_limit'] = $dailyIntegralLimit;// 每日积分上限
            $examinationHistory['today_integral'] = 0;// 当日已获得的积分
            $examinationHistory['remaining_integral'] = $dailyIntegralLimit;// 当日剩余可获得的积分
            
            // 检查是否有积分可加
            if ($examinationHistory['user_integral'] <= 0) {
                // 即使没有积分可加，也要获取用户当日已获得的积分
                if ($dailyIntegralLimit > 0) {
                    // 获取当天的开始时间
                    $todayStart = strtotime(date('Y-m-d'));
                    
                    // 查询用户当日已获得的积分总和
                    $todayIntegral = TenantUserIntegralLog::query()
                        ->where('tenant_id', '=', request()->tenantId)
                        ->where('user_id', '=', $examinationHistory['user_uid'])
                        ->where('action', '=', 1) // 只统计增加的积分
                        ->where('create_time', '>=', $todayStart) // 当天开始时间
                        ->where('change_type', '=', IntegralEnum::User_EXAMINATION) // 仅统计考试相关积分
                        ->sum('change_amount');
                    
                    // 更新积分相关信息
                    $examinationHistory['today_integral'] = $todayIntegral;
                    $examinationHistory['remaining_integral'] = max(0, $dailyIntegralLimit - $todayIntegral);
                }
                return;
            }
            
            // 如果设置了积分上限，检查用户当日已获得的积分
            if ($dailyIntegralLimit > 0) {
                // 获取当天的开始时间
                $todayStart = strtotime(date('Y-m-d'));
                
                // 查询用户当日已获得的积分总和
                $todayIntegral = TenantUserIntegralLog::query()
                    ->where('tenant_id', '=', request()->tenantId)
                    ->where('user_id', '=', $examinationHistory['user_uid'])
                    ->where('action', '=', 1) // 只统计增加的积分
                    ->where('create_time', '>=', $todayStart) // 当天开始时间
                    ->where('change_type', '=', IntegralEnum::User_EXAMINATION) // 仅统计考试相关积分
                    ->sum('change_amount');
                
                // 计算剩余可获得的积分
                $remainingIntegral = $dailyIntegralLimit - $todayIntegral;
                
                // 更新积分相关信息
                $examinationHistory['today_integral'] = $todayIntegral;
                $examinationHistory['remaining_integral'] = max(0, $remainingIntegral);
                
                // 更新实际可获得的积分
                if ($remainingIntegral <= 0) {
                    $examinationHistory['user_integral'] = 0;
                    return;
                } elseif ($examinationHistory['user_integral'] > $remainingIntegral) {
                    $examinationHistory['user_integral'] = $remainingIntegral;
                }
            }
            
            // 插入答题赠送积分
            if ($examinationHistory['user_integral'] > 0) {
                IntegralLogic::addIntegral(
                    $examinationHistory['user_uid'],       // $userId
                    1,                               // $action: 1-增加积分
                    IntegralEnum::User_EXAMINATION,           // $integralType
                    (float)$examinationHistory['user_integral'], // $integral
                    IntegralEnum::IntegralTitle(IntegralEnum::User_EXAMINATION) // $remark
                );
            }

        } catch (\Exception $e) {
            // 可以考虑添加日志记录
            return;
        }
    }
    
    /**
     * 更新题目统计数据
     * @param array $optionItems 答题项数据
     * @param array $correctOptionUid 答对的题目UID数组
     * @param array $errorOptionUid 答错的题目UID数组
     * @return void
     */
    private static function updateQuestionStatistics(array $optionItems, array $correctOptionUid, array $errorOptionUid): void
    {
        try {
            // 构建题目UID到用户答案的映射
            $answerMap = [];
            $timeMap = [];  // 答题时长映射
            
            foreach ($optionItems as $item) {
                if (isset($item['uid'])) {
                    if (isset($item['user_answer'])) {
                        $answerMap[$item['uid']] = $item['user_answer'];
                    }
                    // 提取答题时长（秒）
                    if (isset($item['time_spent'])) {
                        $timeMap[$item['uid']] = (int)$item['time_spent'];
                    }
                }
            }
            
            // 获取所有题目的UID
            $allQuestionUids = array_unique(array_merge($correctOptionUid, $errorOptionUid));
            
            // 批量更新统计数据
            foreach ($allQuestionUids as $questionUid) {
                if (empty($questionUid)) continue;
                
                $isCorrect = in_array($questionUid, $correctOptionUid);
                $userAnswer = $answerMap[$questionUid] ?? '';
                $timeSpent = $timeMap[$questionUid] ?? 0;
                
                // 1. 使用原子操作增加总做题人次（避免并发问题）
                TenantExamQuestion::where('uid', $questionUid)->inc('total_attempts', 1)->update();
                
                // 2. 如果答对，增加正确次数
                if ($isCorrect) {
                    TenantExamQuestion::where('uid', $questionUid)->inc('correct_attempts', 1)->update();
                }
                
                // 3. 更新易错项统计（只统计错误答案）
                if (!$isCorrect) {
                    self::updateEasyMistakes($questionUid, $userAnswer);
                }
                
                // 4. 更新平均答题时间（只当有时长数据时）
                if ($timeSpent > 0) {
                    // 获取当前统计数据
                    $question = TenantExamQuestion::where('uid', $questionUid)
                        ->field(['total_attempts', 'avg_time_spent'])
                        ->find();
                    
                    if ($question) {
                        $totalAttempts = (int)$question['total_attempts'];
                        $oldAvg = (int)($question['avg_time_spent'] ?? 0);
                        
                        // 计算新的平均时长：(旧平均 * (总次数-1) + 本次时长) / 总次数
                        $newAvg = $totalAttempts > 0 
                            ? (int)(($oldAvg * ($totalAttempts - 1) + $timeSpent) / $totalAttempts)
                            : $timeSpent;
                        
                        TenantExamQuestion::where('uid', $questionUid)
                            ->update(['avg_time_spent' => $newAvg]);
                    }
                }
                
                // 5. 重新计算正确率
                $question = TenantExamQuestion::where('uid', $questionUid)
                    ->field(['total_attempts', 'correct_attempts'])
                    ->find();
                
                if ($question) {
                    $totalAttempts = (int)$question['total_attempts'];
                    $correctAttempts = (int)($question['correct_attempts'] ?? 0);
                    
                    $accuracyRate = $totalAttempts > 0 
                        ? round(($correctAttempts / $totalAttempts) * 100, 2)
                        : 0;
                    
                    TenantExamQuestion::where('uid', $questionUid)
                        ->update(['accuracy_rate' => $accuracyRate]);
                }
            }
        } catch (\Exception $e) {
            // 记录日志但不影响主流程
            \think\facade\Log::error('更新题目统计失败: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    }
    
    /**
     * 更新易错项统计
     * @param string $questionUid 题目UID
     * @param mixed $userAnswer 用户的错误答案（可能是字符串或数组）
     * @return void
     */
    private static function updateEasyMistakes(string $questionUid, mixed $userAnswer): void
    {
        try {
            // 获取当前题目的完整数据，包括正确答案
            $question = TenantExamQuestion::where('uid', $questionUid)
                ->field(['easy_mistakes', 'answer'])
                ->find();
            
            if (!$question) return;
            
            // 解析当前的易错项数据（JSON格式存储）
            // 格式：{"A": 10, "B": 5, "C": 15, "D": 2}
            $easyMistakes = [];
            if (!empty($question['easy_mistakes'])) {
                $decoded = json_decode($question['easy_mistakes'], true);
                if (is_array($decoded)) {
                    $easyMistakes = $decoded;
                }
            }
            
            // 解析用户答案（可能是数组、单选"A"、多选"A,B"或JSON格式"[\"A\",\"B\"]"）
            $answers = [];
            
            if (empty($userAnswer)) {
                return; // 空答案直接返回
            }
            
            // 处理不同类型的用户答案
            if (is_array($userAnswer)) {
                // 已经是数组，直接使用
                $answers = array_filter($userAnswer, function($item) {
                    return !empty($item);
                });
            } elseif (is_string($userAnswer)) {
                // 字符串类型，尝试解析JSON
                $parsed = json_decode($userAnswer, true);
                if (is_array($parsed)) {
                    $answers = $parsed;
                } else {
                    // 按逗号或中文顿号分割
                    $userAnswer = str_replace(['、', ' '], ',', $userAnswer);
                    $answers = array_map('trim', explode(',', $userAnswer));
                }
            } else {
                // 其他类型，转换为字符串后处理
                $strAnswer = (string)$userAnswer;
                $answers = [$strAnswer];
            }
            
            // 解析正确答案（处理多种格式）
            $correctAnswers = [];
            $answer = $question['answer'];
            
            if (is_string($answer)) {
                $parsed = json_decode($answer, true);
                if (is_array($parsed)) {
                    $correctAnswers = $parsed;
                } else {
                    $answer = str_replace(['、', ' '], ',', $answer);
                    $correctAnswers = array_map('trim', explode(',', $answer));
                }
            } elseif (is_array($answer)) {
                $correctAnswers = $answer;
            }
            
            // 清理正确答案，去除特殊字符
            $correctAnswers = array_map(function($item) {
                $item = preg_replace('/[\[\]\"\'\'\'"]/', '', $item);
                return trim($item);
            }, $correctAnswers);
            // 过滤空值
            $correctAnswers = array_filter($correctAnswers);
            
            // 统计每个错误选项，排除正确答案
            $updated = false;
            foreach ($answers as $answer) {
                if (empty($answer)) continue;
                
                // 清理答案（去除特殊字符）
                $cleanAnswer = preg_replace('/[\[\]\"\'\'\'"]/', '', $answer);
                $cleanAnswer = trim($cleanAnswer);
                
                if (empty($cleanAnswer)) continue;
                
                // 只统计不在正确答案列表中的选项
                if (!in_array($cleanAnswer, $correctAnswers)) {
                    // 增加该选项的错误次数
                    if (!isset($easyMistakes[$cleanAnswer])) {
                        $easyMistakes[$cleanAnswer] = 0;
                    }
                    $easyMistakes[$cleanAnswer]++;
                    $updated = true;
                }
            }
            
            // 如果有更新，保存数据
            if ($updated) {
                TenantExamQuestion::where('uid', $questionUid)
                    ->update(['easy_mistakes' => json_encode($easyMistakes, JSON_UNESCAPED_UNICODE)]);
            }
        } catch (\Exception $e) {
            // 记录日志但不影响主流程
            \think\facade\Log::error('更新易错项失败: ' . $e->getMessage(), [
                'questionUid' => $questionUid,
                'userAnswer' => $userAnswer
            ]);
        }
    }
    
    /**
     * 更新用户章节统计数据
     * @param array $optionItems 答题项数据
     * @param array $correctOptionUid 答对的题目UID数组
     * @param array $errorOptionUid 答错的题目UID数组
     * @param string $userUid 用户UID
     * @param int $tenantId 租户ID
     * @return void
     */
    private static function updateChapterStatistics(
        array $optionItems, 
        array $correctOptionUid, 
        array $errorOptionUid,
        string $userUid,
        int $tenantId
    ): void
    {
        try {
            // 获取所有题目的UID
            $allQuestionUids = array_unique(array_merge($correctOptionUid, $errorOptionUid));
            
            if (empty($allQuestionUids)) {
                return;
            }
            
            // 批量查询题目信息，获取章节和题库信息
            $questions = TenantExamQuestion::query()
                ->whereIn('uid', $allQuestionUids)
                ->field(['uid', 'chapter_uid', 'library_uid'])
                ->select()
                ->toArray();
            
            // 按题目分组统计，每个题目单独调用更新方法
            foreach ($questions as $question) {
                $chapterUid = $question['chapter_uid'] ?? '';
                $libraryUid = $question['library_uid'] ?? '';
                $questionUid = $question['uid'];
                
                // 跳过没有章节信息的题目（关键改进：基于 chapter_uid 判断）
                if (empty($chapterUid) || empty($libraryUid)) {
                    continue;
                }
                
                // 判断该题目是否答对
                $isCorrect = in_array($questionUid, $correctOptionUid);
                
                // 调用更新方法，每个题目单独更新
                \app\api\logic\exam\QuestionlibLogic::updateUserChapterStatistics([
                    'user_uid' => $userUid,
                    'library_uid' => $libraryUid,
                    'chapter_uid' => $chapterUid,
                    'tenant_id' => $tenantId,
                    'is_correct' => $isCorrect
                ]);
            }
        } catch (\Exception $e) {
            // 记录日志但不影响主流程
            \think\facade\Log::error('更新章节统计失败: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    }
    
    /**
     * 判断多选题是否半对（部分正确）
     * @param array $option 题目数据
     * @return bool 是否半对
     */
    private static function checkHalfCorrect(array $option): bool
    {
        // 只有多选题才有半对状态
        if ((int)($option['exam_type'] ?? 0) !== self::EXAM_TYPE_MULTIPLE) {
            return false;
        }
        
        // 获取用户答案和正确答案
        $userAnswer = $option['user_answer'] ?? '';
        $correctAnswer = $option['answer'] ?? '';
        
        if (empty($userAnswer) || empty($correctAnswer)) {
            return false;
        }
        
        // 解析答案为数组
        $userAnswerArr = [];
        $correctAnswerArr = [];
        
        // 处理用户答案
        if (is_array($userAnswer)) {
            $userAnswerArr = $userAnswer;
        } elseif (is_string($userAnswer)) {
            $decoded = json_decode($userAnswer, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $userAnswerArr = $decoded;
            } else {
                $userAnswerArr = array_filter(explode(',', $userAnswer));
            }
        }
        
        // 处理正确答案
        if (is_array($correctAnswer)) {
            $correctAnswerArr = $correctAnswer;
        } elseif (is_string($correctAnswer)) {
            $decoded = json_decode($correctAnswer, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $correctAnswerArr = $decoded;
            } else {
                $correctAnswerArr = array_filter(explode(',', $correctAnswer));
            }
        }
        
        // 移除空值
        $userAnswerArr = array_filter($userAnswerArr, function($v) { return !empty($v); });
        $correctAnswerArr = array_filter($correctAnswerArr, function($v) { return !empty($v); });
        
        if (empty($userAnswerArr) || empty($correctAnswerArr)) {
            return false;
        }
        
        // 计算正确选择和错误选择
        $correctSelections = array_intersect($userAnswerArr, $correctAnswerArr);
        $wrongSelections = array_diff($userAnswerArr, $correctAnswerArr);
        
        // 半对条件：有正确选择，但不是全对（有遗漏或有错选）
        if (!empty($correctSelections) && (!empty($wrongSelections) || count($correctSelections) < count($correctAnswerArr))) {
            return true;
        }
        
        return false;
    }
    
    /**
     * 统一时间戳为 13 位（毫秒）
     * @param mixed $timestamp 时间戳（可能为 10 位或 13 位）
     * @return int 13 位时间戳
     */
    private static function normalizeTimestamp($timestamp): int
    {
        if (empty($timestamp)) {
            return time() * 1000; // 返回当前时间的毫秒
        }
        
        // 如果是字符串，尝试转换为时间戳
        if (is_string($timestamp)) {
            // 尝试作为日期字符串解析
            $parsedTime = strtotime($timestamp);
            if ($parsedTime !== false) {
                return $parsedTime * 1000;
            }
            
            // 尝试作为数字字符串解析
            if (is_numeric($timestamp)) {
                $timestamp = (int)$timestamp;
            } else {
                return time() * 1000;
            }
        }
        
        $timestamp = (int)$timestamp;
        
        // 10 位时间戳（秒），转换为 13 位（毫秒）
        if ($timestamp < 10000000000) {
            return $timestamp * 1000;
        }
        
        // 已经是 13 位，直接返回
        return $timestamp;
    }
    
    /**
     * 统一答案格式为 JSON 数组
     * @param mixed $answer 答案（可能为字符串或数组）
     * @return array 返回包含数组和字符串两种格式
     */
    private static function normalizeAnswer($answer): array
    {
        $answerArray = [];
        $answerString = '';
        
        if (empty($answer)) {
            return [
                'array' => [],
                'string' => '',
                'json' => '[]'
            ];
        }
        
        // 处理不同类型的答案
        if (is_array($answer)) {
            $answerArray = array_values(array_filter($answer, function($v) {
                return $v !== null && $v !== '';
            }));
        } elseif (is_string($answer)) {
            // 尝试解析 JSON
            $decoded = json_decode($answer, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $answerArray = array_values(array_filter($decoded, function($v) {
                    return $v !== null && $v !== '';
                }));
            } else {
                // 处理逗号分隔的字符串
                $answerArray = array_values(array_filter(
                    array_map('trim', explode(',', $answer)),
                    function($v) { return $v !== ''; }
                ));
            }
        }
        
        // 生成字符串格式
        $answerString = implode(',', $answerArray);
        
        return [
            'array' => $answerArray,              // 数组格式
            'string' => $answerString,            // 逗号分隔字符串
            'json' => json_encode($answerArray, JSON_UNESCAPED_UNICODE)  // JSON 字符串
        ];
    }
    
    /**
     * 保存答题进度
     * @param array $params
     * @return bool
     */
    public static function saveProgress(array $params): bool
    {
        try {
            $userUid = $params['user_uid'] ?? '';
            $libraryUid = $params['library_uid'] ?? '';
            $questionsType = (int)($params['questions_type'] ?? 1);
            $chapterUid = $params['chapter_uid'] ?? null;
            
            if (empty($userUid) || empty($libraryUid)) {
                self::setError('用户ID或题库UID不能为空');
                return false;
            }
            
            // 构建进度数据
            $progressData = [
                'questions' => $params['questions'] ?? [],        // 题目列表
                'current_index' => $params['current_index'] ?? 0, // 当前题目索引
                'mode' => $params['mode'] ?? 'sequence',           // 做题模式
                'answers' => $params['answers'] ?? [],             // 已答题数据
                'start_time' => $params['start_time'] ?? time() * 1000, // 开始时间
            ];
            
            $now = time() * 1000; // 13位时间戳
            
            // 查询是否已存在进度记录
            $existingProgress = TenantUserExamProgress::query()
                ->where('user_uid', $userUid)
                ->where('library_uid', $libraryUid)
                ->where('questions_type', $questionsType)
                ->when($chapterUid, function($query) use ($chapterUid) {
                    $query->where('chapter_uid', $chapterUid);
                })
                ->find();
            
            $answeredCount = count($progressData['answers']);
            $elapsedTime = isset($params['elapsed_time']) 
                ? (int)$params['elapsed_time'] 
                : 0;
            
            if ($existingProgress) {
                // 更新已有进度
                $existingProgress->progress_data = $progressData;
                $existingProgress->current_index = $progressData['current_index'];
                $existingProgress->answered_count = $answeredCount;
                $existingProgress->elapsed_time = $elapsedTime;
                $existingProgress->updated_at = $now;
                $existingProgress->save();
            } else {
                // 创建新进度
                TenantUserExamProgress::create([
                    'user_uid' => $userUid,
                    'library_uid' => $libraryUid,
                    'questions_type' => $questionsType,
                    'chapter_uid' => $chapterUid,
                    'progress_data' => $progressData,
                    'current_index' => $progressData['current_index'],
                    'answered_count' => $answeredCount,
                    'elapsed_time' => $elapsedTime,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]);
            }
            
            return true;
        } catch (\Exception $e) {
            self::setError('保存进度失败: ' . $e->getMessage());
            \think\facade\Log::error('保存答题进度失败', [
                'error' => $e->getMessage(),
                'params' => $params
            ]);
            return false;
        }
    }
    
    /**
     * 加载答题进度
     * @param array $params
     * @return array
     */
    public static function loadProgress(array $params): array
    {
        try {
            $userUid = $params['user_uid'] ?? '';
            $libraryUid = $params['library_uid'] ?? '';
            $questionsType = (int)($params['questions_type'] ?? 1);
            $chapterUid = $params['chapter_uid'] ?? null;
            
            if (empty($userUid) || empty($libraryUid)) {
                return [];
            }
            
            // 查询进度记录
            $progress = TenantUserExamProgress::query()
                ->where('user_uid', $userUid)
                ->where('library_uid', $libraryUid)
                ->where('questions_type', $questionsType)
                ->when($chapterUid, function($query) use ($chapterUid) {
                    $query->where('chapter_uid', $chapterUid);
                })
                ->order('updated_at', 'desc')
                ->find();
            
            if (!$progress) {
                return [];
            }
            
            // 返回进度数据
            return [
                'progress_data' => $progress->progress_data,
                'current_index' => $progress->current_index ?? 0,
                'answered_count' => $progress->answered_count ?? 0,
                'elapsed_time' => $progress->elapsed_time ?? 0,
                'updated_at' => $progress->updated_at,
            ];
        } catch (\Exception $e) {
            \think\facade\Log::error('加载答题进度失败', [
                'error' => $e->getMessage(),
                'params' => $params
            ]);
            return [];
        }
    }
    
    /**
     * 清除答题进度
     * @param array $params
     * @return bool
     */
    public static function clearProgress(array $params): bool
    {
        try {
            $userUid = $params['user_uid'] ?? '';
            $libraryUid = $params['library_uid'] ?? '';
            $questionsType = (int)($params['questions_type'] ?? 1);
            $chapterUid = $params['chapter_uid'] ?? null;
            
            if (empty($userUid) || empty($libraryUid)) {
                return false;
            }
            
            // 删除进度记录
            TenantUserExamProgress::query()
                ->where('user_uid', $userUid)
                ->where('library_uid', $libraryUid)
                ->where('questions_type', $questionsType)
                ->when($chapterUid, function($query) use ($chapterUid) {
                    $query->where('chapter_uid', $chapterUid);
                })
                ->delete();
            
            return true;
        } catch (\Exception $e) {
            \think\facade\Log::error('清除答题进度失败', [
                'error' => $e->getMessage(),
                'params' => $params
            ]);
            return false;
        }
    }
}