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

namespace app\tenantapi\logic\exam;


use app\common\model\exam\TenantExamActivationCodeBatch;
use app\common\logic\BaseLogic;
use think\facade\Db;


/**
 * codeBatch逻辑
 * Class TenantExamActivationCodeBatchLogic
 * @package app\tenantapi\logic\exam
 */
class TenantExamActivationCodeBatchLogic extends BaseLogic
{


    /**
     * @notes 添加codeBatch
     * @param array $params
     * @return bool
     * @author likeadmin
     * @date 2025/08/06 08:55
     */
    public static function add(array $params): bool
    {
        Db::startTrans();
        try {
           // print_r($params);exit;
           $codeBatch = TenantExamActivationCodeBatch::create([
                'tenant_id' => $params['tenant_id'],
                'duration_days' => $params['duration_days'],
                'total_count' => $params['total_count'],
                'remark' => $params['remark'],
                'status' => $params['status'],
            ]);
            // 添加codeBatch成功后生成激活码
            if($codeBatch){
                $params['id'] = $codeBatch->id;
                 $activationCodes = self::generateActivationCodes($params['total_count'], $params['id'], $params['tenant_id']);
                // 保存激活码批次到la_tenant_exam_activation_code表
                if($activationCodes){
                    foreach ($activationCodes as $activationCode) {
                        Db::name('tenant_exam_activation_code')->insert($activationCode);
                    }
                }
            }else{
                self::setError('添加失败');
                return false;
            }


            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }


    /**
     * @notes 编辑codeBatch
     * @param array $params
     * @return bool
     * @author likeadmin
     * @date 2025/08/06 08:55
     */
    public static function edit(array $params): bool
    {
        Db::startTrans();
        try {
            TenantExamActivationCodeBatch::where('id', $params['id'])->update([
                'tenant_id' => $params['tenant_id'],
                'duration_days' => $params['duration_days'],
                'remark' => $params['remark'],
                'status' => $params['status'],
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
     * @notes 删除codeBatch
     * @param array $params
     * @return bool
     * @author likeadmin
     * @date 2025/08/06 08:55
     */
    public static function delete(array $params): bool
    {
        return TenantExamActivationCodeBatch::destroy($params['id']);
    }


    /**
     * @notes 获取codeBatch详情
     * @param $params
     * @return array
     * @author likeadmin
     * @date 2025/08/06 08:55
     */
    public static function detail($params): array
    {
        return TenantExamActivationCodeBatch::findOrEmpty($params['id'])->toArray();
    }

    /**
     * @notes 生成激活码批次
     * @param $totalCount
     * @param $durationDays
     * @param $tenantId
     * @return array    
     * @author likeadmin
     * @date 2025/08/06 08:55
     */
    public static function generateActivationCodes($totalCount, $batchId, $tenantId): array
    {
        // 生成由大写、小写字母和数字组成的18位的激活码
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
        $activationCodes = [];
        for ($i = 0; $i < $totalCount; $i++) {
            $activationCodes[] = [
                'tenant_id' => $tenantId,
                'batch_id' => $batchId,
                'code' => self::generateCode($chars),
                'status' => 0,//默认未使用
                'activation_time' => null,//激活时间，默认为空
            ];
        }
        return $activationCodes;
    }

    /**
     * @notes 生成激活码
     * @param $chars
     * @return string
     * @author likeadmin
     * @date 2025/08/06 08:55
     */
    public static function generateCode($chars): string 
    {
        $code = '';
        $charsLength = strlen($chars);
        for ($i = 0; $i < 18; $i++) {
            $code .= $chars[rand(0, $charsLength - 1)];
        }
        return $code;
    }

}  