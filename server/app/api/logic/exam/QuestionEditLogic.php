<?php
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 精解析答题系统开发者版权所有，拥有最终解释权。

namespace app\api\logic\exam;

use app\common\model\dict\TenantDictData;
use think\facade\Db;
use think\facade\Log;
use app\common\model\exam\TenantExamQuestion;

/**
 * 试题逻辑层
 * Class QuestionEditLogic
 * @package app\api\logic\exam
 */
class QuestionEditLogic extends \app\common\logic\BaseLogic
{   
    /**
     * @notes 添加试题
     * @param array $params
     * @return bool
     * @author 精解析答题系统
     * @date 2026/01/25
     */
    public static function add(array $params): bool
    {
        Db::startTrans();
        try {
            //根据title判断是否存在（force=1时跳过重复检查）
            if (empty($params['force']) || $params['force'] != 1) {
                $question = TenantExamQuestion::where('tenant_id', $params['tenant_id'])
                    ->where('title', trim($params['title']))
                    ->find();
                if ($question) {
                    self::setError('相同标题的试题已存在');
                    return false;
                }
            }
            // 添加试题
            $question = new TenantExamQuestion();
            $question->tenant_id = $params['tenant_id'];
            $question->admin_id = $params['admin_id'] ?? 0;
            $question->uid = uid();
            $question->library_uid = $params['library_uid'];
            $question->title = trim($params['title']);
            $question->exam_type = $params['exam_type'];
            $question->exam_level = $params['exam_level'];
            $question->score = $params['score'];
            $question->integral = $params['integral'];
            $question->is_show = $params['is_show'] ?? 1;
            $question->sort = $params['sort'] ?? 0;
            $question->analysis = $params['analysis'] ?? '';
            $question->commentaries = $params['commentaries'] ?? '';//名师点评
            $question->chapter_uid = $params['chapter_uid'] ?? '';
            $question->knowledge_uid = $params['knowledge_uid'] ?? '';
            $question->label_uid = $params['label_uid'] ?? '';            
            // 将选项转换为JSON字符串存储
            if (!empty($params['option'])) {
                $question->option = json_encode($params['option'], JSON_UNESCAPED_UNICODE);
            }
            
            // 将答案转换为JSON字符串存储
            if (!empty($params['answer'])) {
                $question->answer = json_encode($params['answer'], JSON_UNESCAPED_UNICODE);
            }
            
            $question->save();
            
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            $errorMsg = '添加试题失败: ' . $e->getMessage();
            Log::error($errorMsg);
            self::setError($errorMsg);
            return false;
        }
    }
    
    /**
     * @notes 编辑试题
     * @param array $params
     * @return bool
     * @author 精解析答题系统
     * @date 2026/01/25
     */
    public static function edit(array $params): bool
    {
        Db::startTrans();
        try {
            $question = TenantExamQuestion::where([
                ['uid', '=', $params['uid']],
                ['tenant_id', '=', $params['tenant_id']],
            ])->findOrFail();
            
            // 检查标题是否重复（排除当前试题，force=1时跳过检查）
            if (empty($params['force']) || $params['force'] != 1) {
                $existingQuestion = TenantExamQuestion::where('tenant_id', $params['tenant_id'])
                    ->where('title', trim($params['title']))
                    ->where('uid', '<>', $params['uid'])
                    ->find();
                if ($existingQuestion) {
                    self::setError('相同标题的试题已存在');
                    return false;
                }
            }
            
            $question->library_uid = $params['library_uid'] ?? $question->library_uid;
            $question->title = trim($params['title']);
            $question->exam_type = $params['exam_type'] ?? $question->exam_type;
            $question->exam_level = $params['exam_level'] ?? $question->exam_level;
            $question->score = isset($params['score']) ? (float)$params['score'] : $question->score;
            $question->integral = isset($params['integral']) ? (int)$params['integral'] : $question->integral;
            $question->is_show = isset($params['is_show']) ? (int)$params['is_show'] : $question->is_show;
            $question->sort = isset($params['sort']) ? (int)$params['sort'] : $question->sort;
            $question->analysis = $params['analysis'] ?? $question->analysis;
            $question->commentaries = $params['commentaries'] ?? $question->commentaries;
            $question->chapter_uid = $params['chapter_uid'] ?? $question->chapter_uid;
            $question->knowledge_uid = $params['knowledge_uid'] ?? $question->knowledge_uid;
            $question->label_uid = $params['label_uid'] ?? $question->label_uid;
            $question->easy_mistakes = $params['easy_mistakes'] ?? $question->easy_mistakes;
            $question->answer_str = $params['answer_str'] ?? $question->answer_str;
            $question->case_content = $params['case_content'] ?? $question->case_content;
            $question->case_question = $params['case_question'] ?? $question->case_question;
            
            if (isset($params['option'])) {
                if (!empty($params['option'])) {
                    $question->option = json_encode($params['option'], JSON_UNESCAPED_UNICODE);
                } else {
                    $question->option = null;
                }
            }
            
            if (isset($params['answer'])) {
                if (!empty($params['answer'])) {
                    $question->answer = json_encode($params['answer'], JSON_UNESCAPED_UNICODE);
                } else {
                    $question->answer = null;
                }
            }
            
            $question->update_time = time();
            $question->save();
            
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            $errorMsg = '编辑试题失败: ' . $e->getMessage();
            Log::error($errorMsg);
            self::setError($errorMsg);
            return false;
        }
    }
    
    // 隐藏试题
    public static function questionShow(array $params): bool
    {
        try {
            $model = TenantExamQuestion::where([
                ['uid', '=', $params['uid']],
                ['tenant_id', '=', $params['tenant_id']],
            ])->findOrEmpty();
            if (!$model) {
                self::setError('试题不存在');
                return false;
            }
            
            $model->is_show = $params['is_show'];
            $model->update_time = time();
            $model->save();
            
            return true;
        } catch (\Exception $e) {
            $errorMsg = '隐藏试题失败: ' . $e->getMessage();
            Log::error($errorMsg);
            self::setError($errorMsg);
            return false;
        }
    }

    /**
     * @notes 删除试题
     * @param array $params
     * @return bool
     * @author 精解析答题系统
     * @date 2026/01/25
     */
    public static function delete(array $params): bool
    {
        try {
            Db::startTrans();
            
            // 删除试题
            $model = TenantExamQuestion::where([
                ['uid', '=', $params['uid']],
                ['tenant_id', '=', $params['tenant_id']],
            ])->findOrEmpty();
            
            if (!$model) {
                self::setError('试题不存在');
                return false;
            }
            
            $model->delete_time = time();
            $model->update_time = time();
            $model->save();
            
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            $errorMsg = '删除试题失败: ' . $e->getMessage();
            Log::error($errorMsg);
            self::setError($errorMsg);
            return false;
        }
    }
    
    /**
     * @notes 批量删除试题
     * @param array $params
     * @return bool
     * @author 精解析答题系统
     * @date 2026/01/25
     */
    public static function batchDelete(array $params): bool
    {
        try {
            Db::startTrans();
            
            $ids = is_array($params['uids']) ? $params['uids'] : explode(',', $params['uids']);
            
            // 删除试题
            TenantExamQuestion::where([
                ['uid', 'in', $ids],
                ['tenant_id', '=', $params['tenant_id']],
            ])->update(['delete_time' => time()]);
            
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            $errorMsg = '批量删除试题失败: ' . $e->getMessage();
            Log::error($errorMsg);
            self::setError($errorMsg);
            return false;
        }
    }
    
    /**
     * @notes 文本批量导入试题
     * @param array $params
     * @return bool
     * @author 精解析答题系统
     * @date 2026/01/25
     */
    public static function textAdd(array $params): bool
    {
        try {
            Db::startTrans();
            
            $content = $params['content'];
            $lines = explode("\n", $content);
            $questions = [];
            $currentQuestion = [];
            $currentOptions = [];
            
            // 解析文本内容
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) {
                    // 保存当前试题
                    if (!empty($currentQuestion)) {
                        $currentQuestion['options'] = $currentOptions;
                        $questions[] = $currentQuestion;
                        $currentQuestion = [];
                        $currentOptions = [];
                    }
                    continue;
                }
                
                // 解析题型
                if (preg_match('/^【题型】(.*)$/', $line, $matches)) {
                    $currentQuestion['question_type'] = self::mapQuestionType($matches[1]);
                }
                // 解析难度
                elseif (preg_match('/^【难度】(.*)$/', $line, $matches)) {
                    $currentQuestion['difficulty'] = self::mapDifficulty($matches[1]);
                }
                // 解析分数
                elseif (preg_match('/^【分数】(.*)$/', $line, $matches)) {
                    $currentQuestion['score'] = (float)$matches[1];
                }
                // 解析题干
                elseif (preg_match('/^【题干】(.*)$/', $line, $matches)) {
                    $currentQuestion['title'] = $matches[1];
                }
                // 解析选项
                elseif (preg_match('/^【选项】(.*)$/', $line, $matches)) {
                    $options = explode('|', $matches[1]);
                    foreach ($options as $option) {
                        if (preg_match('/^(.)\.(.*)$/', $option, $optMatches)) {
                            $currentOptions[] = [
                                'option_content' => $optMatches[2],
                                'is_correct' => 0,
                                'sort' => count($currentOptions)
                            ];
                        }
                    }
                }
                // 解析答案
                elseif (preg_match('/^【答案】(.*)$/', $line, $matches)) {
                    $answer = $matches[1];
                    foreach ($currentOptions as &$option) {
                        if (strpos($answer, $option['option_content'][0]) !== false) {
                            $option['is_correct'] = 1;
                        }
                    }
                }
                // 解析解析
                elseif (preg_match('/^【解析】(.*)$/', $line, $matches)) {
                    $currentQuestion['analysis'] = $matches[1];
                }
            }
            
            // 保存最后一道试题
            if (!empty($currentQuestion)) {
                $currentQuestion['options'] = $currentOptions;
                $questions[] = $currentQuestion;
            }
            
            // 批量插入试题
            foreach ($questions as $question) {
                // 添加试题
                $model = new TenantExamQuestion();
                $model->tenant_id = $params['tenant_id'];
                $model->title = $question['title'];
                $model->question_type = $question['question_type'];
                $model->difficulty = $question['difficulty'];
                $model->score = $question['score'];
                $model->analysis = $question['analysis'];
                
                // 将选项转换为JSON字符串存储
                if (!empty($question['options'])) {
                    $model->option = json_encode($question['options'], JSON_UNESCAPED_UNICODE);
                }
                
                $model->save();
            }
            
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            $errorMsg = '文本批量导入试题失败: ' . $e->getMessage();
            Log::error($errorMsg);
            self::setError($errorMsg);
            return false;
        }
    }
    
    /**
     * @notes 通过题库ID查询试题
     * @param array $params
     * @return array
     * @author 精解析答题系统
     * @date 2026/01/25
     */
    public static function questionListByLibrary(array $params): array
    {
        try {
            $page = (int)$params['page_no'] ?? 1;
            $limit = (int)$params['page_size'] ?? 10;
            $offset = ($page - 1) * $limit;
            
            $query = TenantExamQuestion::where([
                ['tenant_id', '=', $params['tenant_id']],
                ['delete_time', '=', null]
            ]);
            
            // 按题库ID筛选
            if (!empty($params['library_uid'])) {
                $query->where('library_uid', '=', $params['library_uid']);
            }
            
            // 搜索条件
            if (!empty($params['keyword'])) {
                $query->where('title', 'like', '%' . $params['keyword'] . '%');
            }
            // 标题搜索（兼容旧版参数）
            if (!empty($params['title'])) {
                $query->where('title', 'like', '%' . $params['title'] . '%');
            }
            
            // 题型筛选
            if (!empty($params['exam_type']) && $params['exam_type'] != 0) {
                $query->where('exam_type', '=', $params['exam_type']);
            }
            
            // 难度筛选
            if (!empty($params['exam_level']) && $params['exam_level'] != 0) {
                $query->where('exam_level', '=', $params['exam_level']);
            }
            
            // 显示状态筛选（只接受0或1）
            if (isset($params['is_show']) && in_array($params['is_show'], [0, 1])) {
                $query->where('is_show', '=', $params['is_show']);
            }
            
            $total = $query->count();
            $list = $query->field([
                'id', 'uid', 'library_uid', 'title', 'option', 'integral', 'score', 'answer', 
                'analysis', 'commentaries', 'exam_type', 'sort', 'is_show', 'exam_level', 
                'create_time', 'update_time', 'chapter_uid', 'knowledge_uid', 
                'total_attempts', 'total_correct', 'like_count', 'collect_count', 
                'easy_mistakes', 'label_uid', 'answer_str', 'correct_attempts', 'accuracy_rate', 'avg_time_spent'
            ])
            ->limit($offset, $limit)
            ->order('create_time', 'desc')
            ->select()
            ->toArray();
            
            // 处理JSON字段（检查是否已为数组，避免重复解码）
            foreach ($list as &$item) {
                if (!empty($item['option']) && is_string($item['option'])) {
                    $item['option'] = json_decode($item['option'], true);
                }
                if (!empty($item['answer']) && is_string($item['answer'])) {
                    $item['answer'] = json_decode($item['answer'], true);
                }
            }
            
            return [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'list' => $list
            ];
        } catch (\Exception $e) {
            Log::error('通过题库ID查询试题失败: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * @notes 题型映射
     * @param string $type
     * @return int
     */
    private static function mapQuestionType(string $type): int
    {
        $dictData = TenantDictData::where([
            ['tenant_id', '=', request()->tenantId],
            ['type_value', '=', 'exam_type'],
            ['delete_time', '=', null],
            ['is_show', '=', 1] 
        ])->column('value', 'name');
        return $dictData[$type] ?? 1;
    }
    
    /**
     * @notes 难度映射
     * @param string $difficulty
     * @return int
     */
    private static function mapDifficulty(string $difficulty): int
    {
        $dictData = TenantDictData::where([
            ['tenant_id', '=', request()->tenantId],
            ['type_value', '=', 'exam_level'],
            ['delete_time', '=', null],
            ['is_show', '=', 1]
        ])->column('value', 'name');
        return $dictData[$difficulty] ?? 3;
    }

    /**
     * @notes 试题详情
     * @param array $params
     * @return array|bool
     */
    public static function questionDetail(array $params): array|bool
    {
        try {
            $result = TenantExamQuestion::where([
                ['tenant_id', '=', $params['tenant_id']],
                ['uid', '=', $params['uid']],
                ['delete_time', '=', null]
            ])->find();
            
            if ($result) {
                $result = $result->toArray();
                $result['option'] = is_string($result['option']) ? json_decode($result['option'], true) : $result['option'];
                $result['answer'] = is_string($result['answer']) ? json_decode($result['answer'], true) : $result['answer'];
                return $result;
            }
            return false;
        } catch (\Exception $e) {
            Log::error('获取试题详情失败: ' . $e->getMessage());
            throw $e;
        }
    }

}