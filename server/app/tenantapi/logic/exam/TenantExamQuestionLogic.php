<?php
// +----------------------------------------------------------------------
// | 答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 答题系统开发者版权所有，拥有最终解释权。

namespace app\tenantapi\logic\exam;


use app\common\logic\BaseLogic;
use app\common\model\exam\TenantExamLibrary;
use app\common\model\exam\TenantExamQuestion;
use think\facade\Cache;
use think\facade\Db;
use app\common\model\exam\TenantExamLabel;


/**
 * 试题管理逻辑
 * Class TenantExamQuestionLogic
 * @package app\platform\logic\exam
 */
class TenantExamQuestionLogic extends BaseLogic

{
    /**
     * @notes 添加试题管理
     * @param array $params
     * @return bool
     * @date 2025/04/10 19:22
     */
    public static function add(array $params): bool
    {
        try {
            $model = TenantExamQuestion::create([
                'uid'                     => uid(),
                'library_uid' => $params['library_uid'],
                'title'                   => trim($params['title']),
                'option'                  => json_encode($params['option'], JSON_UNESCAPED_UNICODE),
                'integral'                => $params['integral'],
                'score'                   => $params['score'],
                'answer'                  => isset($params['answer']) && !empty($params['answer']) ? json_encode($params['answer'], JSON_UNESCAPED_UNICODE) : null,
                'analysis'                => $params['analysis'],
                'commentaries'            => $params['commentaries'],
                'exam_type'               => $params['exam_type'],
                'sort'                    => $params['sort'],
                'is_show'                 => $params['is_show'],
                'exam_level'                   => $params['exam_level'],
                'admin_id'                => $params['admin_id'],
                'tenant_id'               => $params['tenant_id'],
                'chapter_uid'             => $params['chapter_uid'],
                'knowledge_uid'             => $params['knowledge_uid'],
                'label_uid'             => $params['label_uid'],
            ]);
            if (!empty($model->getKey())) {
                return true;
            }
            return false;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }


    /**
     * @notes 编辑试题管理
     * @param array $params
     * @return bool
     * @author 答题
     * @date 2025/04/10 19:22
     */
    public static function edit(array $params): bool
    {
        Db::startTrans();
        try {
            TenantExamQuestion::where([
                ['id', '=', $params['id']],
                ['tenant_id', '=', $params['tenant_id']]
            ])->update([
                'library_uid' => $params['library_uid'],
                'title'                   => trim($params['title']),
                'option'                  => json_encode($params['option'], JSON_UNESCAPED_UNICODE),
                'integral'                => $params['integral'],
                'score'                   => $params['score'],
                'answer'                  => isset($params['answer']) && !empty($params['answer']) ? json_encode($params['answer'], JSON_UNESCAPED_UNICODE) : null,
                'analysis'                => $params['analysis'],
                'commentaries'            => $params['commentaries'],
                'exam_type'               => $params['exam_type'],
                'sort'                    => $params['sort'],
                'is_show'                 => $params['is_show'],
                'exam_level'                   => $params['exam_level'],
                'admin_id'                => $params['admin_id'],
                'chapter_uid'             => $params['chapter_uid'],
                'knowledge_uid'             => $params['knowledge_uid'],
                'label_uid'             => $params['label_uid'],
            ]);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }


    /**
     * @notes 删除试题管理
     * @param array $params
     * @return bool
     * @date 2025/04/10 19:22
     */
    public static function delete(array $params): bool
    {
        $buildModel = TenantExamQuestion::where([
            ["tenant_id", "=", $params["tenant_id"]]
        ]);
        if (is_array($params["id"])) {
            $buildModel->whereIn("id", $params["id"]);
        } else {
            $buildModel->where("id", $params["id"]);
        }
        return $buildModel->update(['delete_time' => time()]) > 0;
    }


    /**
     * @notes 获取试题管理详情
     * @param $params
     * @return array
     * @date 2025/04/10 19:22
     */
    public static function detail($params): array
    {
        
        $question = TenantExamQuestion::where([
            ["tenant_id", "=", $params["tenant_id"]],
            ["id", "=", $params["id"]],
        ])->findOrEmpty()->toArray();
       
        if(isset($question["label_uid"]) && !empty($question["label_uid"])){
            //$labelUids = $question["label_uid"] ? explode(',', $question["label_uid"]) : []; // 转换为数组
            $labelList = TenantExamLabel::query()->where(1)
            ->field(['uid', 'title'])
            ->select()->toArray();

            $question["labels"] = $labelList;
        }else{
            $question["labels"] = [];
        }


         
        if(isset($question["knowledge_uid"]) && !empty($question["knowledge_uid"])){

// 先添加对应的 use 语句，不过当前只能修改选中部分，假设模型类路径为 app\common\model\exam\TenantExamKnowledge
            $question["knowledge"] = \app\common\model\exam\TenantExamKnowledge::query()
                ->where(1)
                ->field(['uid', 'title'])
                ->select()->toArray();
        }else{
            $question["knowledge"] = [];
        }
    
      
        if (!empty($question["option"]) && in_array($question["exam_type"], [1, 2, 3]) ) {
            //print_r($question["option"]);exit;
            foreach ($question["option"] as $value) {
                // 统一转换 is_check 为布尔类型

                $value["is_check"] = (bool)$value["is_check"] ?? false;

                $checkValue = $value["check"] ?? null;
                // 修复 JSON 解析并添加错误处理
                $answers = json_decode($question["answer"], true) ?: [];
                // 修复 JSON 解析错误
                if (json_last_error() !== JSON_ERROR_NONE) {
                    \think\facade\Log::error('JSON 解析错误: ' . json_last_error_msg());
                    continue;
                }
                
                // 添加日志记录，方便调试
                if (in_array($checkValue, $answers)) {
                    $value["is_check"] = true;
                } else {
                    $value["is_check"] = false;
                }
            }
            unset($value);
        }

        // 确保数值类型正确，避免字符串类型的数值
        // integral和score是decimal(6,2)类型，应转换为float保留小数精度
        $question['integral'] = isset($question['integral']) ? (float)$question['integral'] : 0;
        $question['score'] = isset($question['score']) ? (float)$question['score'] : 0;
        // 其他整数类型字段转换为int
        $question['sort'] = isset($question['sort']) ? (int)$question['sort'] : 0;
        $question['exam_level'] = isset($question['exam_level']) ? (int)$question['exam_level'] : 0;
        $question['is_show'] = isset($question['is_show']) ? (int)$question['is_show'] : 1;
        $question['exam_type'] = isset($question['exam_type']) ? (int)$question['exam_type'] : 1;
        
        //$question->label_uid = explode(',', $question->label_uid ?? '');
        \think\facade\Log::debug('Question detail: ' . json_encode($question));
        return $question;
    }

    /**
     * @notes 文本类型试题添加
     * @param array $params
     * @return bool
     * @date 2025/4/13 04:08
     */
    public static function textAdd(array $params): bool
    {
        $key = "text_input" . $params["tenant_id"] . $params["admin_id"];
        if (Cache::get($key)) {
            self::setError("请求处理中，请稍后再试。");
            return false;
        }
        Cache::set($key, 1, 20);
        try {
            $content = self::examContentHandle($params["content"]);
            $examType = array_column($content, "exam_type");
            if (count($examType) === 0) {
                self::setError("试题类型项存在错误，请确认。");
                return false;
            }
            $examType = array_unique($examType);
            if (in_array(0, $examType)) {
                self::setError("试题中存在不支持的类型，请确认。");
                return false;
            }
            $examList = [];
            Db::startTrans();
            foreach ($content as $item) {
                $examList[] = [
                    'uid'                     => uid(),
                    'library_uid' => $params['library_uid'],
                    'title'                   => "<p>" . $item["title"] . "</p>",
                    'option'                  => json_encode($item["option"], JSON_UNESCAPED_UNICODE),
                    'integral'                => 1,
                    'score'                   => 2,
                    'answer'                  => json_encode($item["answer"], JSON_UNESCAPED_UNICODE),
                    'exam_type'               => $item['exam_type'],
                    'analysis'                => "<p>" . $item['analysis'] . "</p>",
                    'commentaries'            => "<p>" . $item['commentaries'] . "</p>",
                    'sort'                    => 1,
                    'is_show'                 => 1,
                    'exam_level'                   => 1,
                    'admin_id'                => $params['admin_id'],
                    'chapter_uid'             => $params['chapter_uid'],
                    'knowledge_uid'             => $params['knowledge_uid'],
                    'label_uid'             => $params['label_uid'],
                    'tenant_id'               => $params['tenant_id'],
                    'create_time'             => time(),
                    'update_time'             => time(),
                ];
            }
            $row = Db::name(TenantExamQuestion::query()->getName())->insertAll($examList);
            if ($row) {
                Db::commit();
                Cache::delete($key);
                return true;
            }
            Db::rollback();
            Cache::delete($key);
            return false;
        } catch (\Exception $exception) {
            self::setError($exception->getMessage());
            Cache::delete($key);
            return false;
        }
    }

    private static function examContentHandle(string $content): array
    {
        $blocks = preg_split('/\n\s*\n/', $content, -1, PREG_SPLIT_NO_EMPTY);
        $result = [];
        foreach ($blocks as $block) {
            $lines = explode("\n", trim($block));
            $data = [
                'title'     => '',
                'option'    => [],
                'answer'    => [],
                'analysis'  => '',
                'commentaries'  => '',
                'exam_type' => 0,
            ];
            foreach ($lines as $line) {
                $line = trim($line);
                if (strpos($line, '题目：') === 0) {
                    $data['title'] = trim(substr($line, 6), " ：");
                } elseif (preg_match('/^[A-D]\.\s*(.+)/', $line, $matches)) {
                    $check = $matches[0][0];
                    $data['option'][] = [
                        'check'    => $check,
                        'title'    => substr($line, 3),
                        'is_check' => 0
                    ];
                } elseif (strpos($line, '答案：') === 0) {
                    $answer = explode("、", trim(substr($line, 6), " ：",));
                    $data['answer'] = $answer;
                } elseif (strpos($line, '题型：') === 0) {
                    $type = trim(substr($line, 6), " ：");
                    $data['exam_type'] = match ($type) {
                        '单选题' => 1,
                        '多选题' => 2,
                        '判断题' => 3,
                        '填空题' => 4,
                        '问答题' => 5,
                        '案例题' => 6,
                        default => 0,
                    };
                } elseif (strpos($line, '解释：') === 0) {
                    $data['analysis'] = trim(substr($line, 6), " ：");
                }
            }

            // 标记正确选项
            foreach ($data["option"] as &$v) {
                if (in_array($v['check'], $data['answer'])) {
                    $v['is_check'] = true;
                } else {
                    $v['is_check'] = false;
                }
            }
            $result[] = $data;
        }
        return $result;
    }

    /**
     * 根据题库 id 查询试题
     * @param array $params
     * @return array
     */
    public static function questionListByLibrary(array $params): array
    {
        $id = $params['id'];
        $tenantId = $params['tenant_id'];
        $uid = TenantExamLibrary::query()->whereIn('id', $id)->column(['uid']);
        $questionItems = TenantExamQuestion::query()->whereIn('library_uid', $uid)->where([
            ['tenant_id', '=', $tenantId],
            ['is_show', '=', 1]
        ])->field(['title', 'option', 'integral', 'score', 'analysis','commentaries', 'uid', 'exam_type'])->select()->toArray();
        
        // 确保数值类型正确，避免字符串类型的数值
        foreach ($questionItems as &$item) {
            // integral和score是decimal(6,2)类型，应转换为float保留小数精度
            $item['integral'] = (float)$item['integral'];
            $item['score'] = (float)$item['score'];
        }
        unset($item);

        $data = [
            [
                'total_score'  => 0,
                'total_number' => 0,
                'score'        => 2.0,
                'value'        => 1,
                'exam_type'    => '单选题'
            ],
            [
                'total_score'  => 0,
                'total_number' => 0,
                'score'        => 2.0,
                'value'        => 2,
                'exam_type'    => '多选题'
            ],
            [
                'total_score'  => 0,
                'total_number' => 0,
                'score'        => 2.0,
                'value'        => 3,
                'exam_type'    => '判断题'
            ],
            [
                'total_score'  => 0,
                'total_number' => 0,
                'score'        => 2.0,
                'value'        => 5,
                'exam_type'    => '问答题'
            ],
            [
                'total_score'  => 0,
                'total_number' => 0,
                'score'        => 2.0,
                'value'        => 4,
                'exam_type'    => '填空题'
            ],
            [
                'total_score'  => 0,
                'total_number' => 0,
                'score'        => 2.0,
                'value'        => 6,
                'exam_type'    => '案例题'
            ],
        ];
        $total = [
            'exam_count' => 0,
            'exam_score' => 0,
        ];
        foreach ($questionItems as $key => $value) {
            $total['exam_count'] += 1;
            $total['exam_score'] += $value['score'];
            $examType = (int)$value['exam_type'];
            
            // 确保数值类型正确，避免字符串类型的数值
            // integral和score是decimal(6,2)类型，应转换为float保留小数精度
            $value['integral'] = (float)$value['integral'];
            $value['score'] = (float)$value['score'];
            $value['exam_type'] = $examType;
            
            // 题型名称映射
            $typeNames = [
                1 => '单选题',
                2 => '多选题',
                3 => '判断题',
                4 => '填空题',
                5 => '问答题',
                6 => '案例题'
            ];
            
            $value['exam_type_text'] = $typeNames[$examType] ?? '未知题型';
            $questionItems[$key] = $value;
            
            // 统计各题型数据（exam_type值对应data数组索引 = exam_type - 1）
            if ($examType >= 1 && $examType <= 6) {
                $dataIndex = $examType - 1;
                $data[$dataIndex]['total_number'] += 1;
                $data[$dataIndex]['total_score'] += $value['score'];
            }
        }
        return [
            'total'     => $total,
            'data'      => $data,
            'exam_list' => $questionItems
        ];
    }
}