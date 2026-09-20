<?php
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 访问官网：
// | 官方邮箱：
// | 精解析答题系统开发者版权所有，拥有最终解释权。

namespace app\tenantapi\logic\exam;


use app\common\logic\BaseLogic;
use app\common\model\exam\TenantExamExamination;
use app\common\model\exam\TenantExamPaper;
use think\facade\Db;


/**
 * 考试管理逻辑
 * Class TenantExamExaminationLogic
 * @package app\platform\logic\exam
 */
class TenantExamExaminationLogic extends BaseLogic
{


    /**
     * @notes 添加考试管理
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/04/13 21:43
     */
    public static function add(array $params): bool
    {
        try {
            $model = TenantExamExamination::create([
                'uid'                => uid(),
                'title'              => $params['title'],
                'library_uid'        => $params['library_uid'],
                'library_category_uid' => $params['library_category_uid'],
                'score'              => $params['score'],
                'content'            => $params['content'],
                'sort'               => $params['sort'],
                'is_show'            => $params['is_show'],
                'admin_id'           => $params['admin_id'],
                'tenant_id'          => $params['tenant_id'],
                'start_time'         => $params['start_time'],
                'end_time'           => $params['end_time'],
                'privilege'          => $params['privilege'],
                'exam_time'          => $params['exam_time'],
                'image'              => $params['image'],
                'exam_submit_type'   => $params['exam_submit_type'],
                'submit_count_value' => $params['submit_count_value'],
                'login_style'        => $params['login_style']
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
     * @notes 编辑考试管理
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/04/13 21:43
     */
    public static function edit(array $params): bool
    {
        try {
            return TenantExamExamination::where([
                    ['id', '=', $params['id']],
                    ['tenant_id', '=', $params['tenant_id']],
                ])->update([
                    'title'              => $params['title'],
                    'library_uid'        => $params['library_uid'],
                    'library_category_uid' => $params['library_category_uid'],
                    'score'              => $params['score'],
                    'content'            => $params['content'],
                    'sort'               => $params['sort'],
                    'is_show'            => $params['is_show'],
                    'start_time'         => $params['start_time'],
                    'end_time'           => $params['end_time'],
                    'image'              => $params['image'],
                    'privilege'          => $params['privilege'],
                    'exam_time'          => $params['exam_time'],
                    'exam_submit_type'   => $params['exam_submit_type'],
                    'submit_count_value' => $params['submit_count_value'],
                    'login_style'        => $params['login_style']
                ]) > 0;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }


    /**
     * @notes 删除考试管理
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/04/13 21:43
     */
    public static function delete(array $params): bool
    {
        if (is_array($params["id"])) {
            return TenantExamExamination::whereIn("id", $params["id"])->where([
                    ['tenant_id', '=', $params['tenant_id']]
                ])->update(['delete_time' => time()]) > 0;
        }
        return TenantExamExamination::where([
                ['id', '=', $params['id']],
                ['tenant_id', '=', $params['tenant_id']]
            ])->update(['delete_time' => time()]) > 0;
    }


    /**
     * @notes 获取考试管理详情
     * @param $params
     * @return array
     * @author 精解析答题
     * @date 2025/04/13 21:43
     */
    public static function detail($params): array
    {
        return TenantExamExamination::where([
            ['id', '=', $params['id']],
            ['tenant_id', '=', $params['tenant_id']]
        ])->find()->toArray();
    }

    /**
     * 绑定试卷
     * @param array $params
     * @return bool
     */
    public static function savePaper(array $params): bool
    {
        try {
            $paperUid = TenantExamPaper::query()->where([
                ['id', '=', $params['paper_id']],
                ['tenant_id', '=', $params['tenant_id']]
            ])->value('uid');
            return TenantExamExamination::query()->where([
                    ['id', '=', $params['id']],
                    ['tenant_id', '=', $params['tenant_id']]
                ])->update(['paper_uid' => $paperUid]) > 0;
        } catch (\Exception $exception) {
            self::setError($exception->getMessage());
            return false;
        }
    }
}