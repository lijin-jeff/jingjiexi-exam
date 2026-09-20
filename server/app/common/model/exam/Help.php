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

namespace app\common\model\exam;

use app\common\enum\YesNoEnum;
use app\common\model\BaseModel;
use think\model\concern\SoftDelete;

/**
 * 帮助管理模型
 * Class Help
 * @package app\common\model\exam;
 */
class Help extends BaseModel
{

    protected $name = 'tenant_exam_help';
    use SoftDelete;

    protected $deleteTime = 'delete_time';


    /**
     * @notes 浏览量
     * @param $value
     * @param $data
     * @return mixed
     * @author 段誉
     * @date 2022/9/15 11:33
     */
    public function getClickAttr($data)
    {
        return $data['click_actual'] + $data['click_virtual'];
    }


    /**
     * @notes 设置图片域名
     * @param $value
     * @param $data
     * @return array|string|string[]|null
     * @author 段誉
     * @date 2022/9/28 10:17
     */
    public function getContentAttr($value)
    {
        return get_file_domain($value);
    }


    /**
     * @notes 清除图片域名
     * @param $value
     * @param $data
     * @return array|string|string[]
     * @author 段誉
     * @date 2022/9/28 10:17
     */
    public function setContentAttr($value, $data)
    {
        return clear_file_domain($value);
    }


    /**
     * @notes 获取帮助中心详情
     * @param $id
     * @return array
     * @author 段誉
     * @date 2022/10/20 15:23
     */
    public static function getHelpDetailArr(int $id)
    {
        $help = Help::where(['id' => $id, 'is_show' => YesNoEnum::YES])
            ->findOrEmpty();

        if ($help->isEmpty()) {
            return [];
        }

        // 增加点击量
        $help->click_actual += 1;
        $help->save();

        return $help->append(['click'])
            ->hidden(['click_virtual', 'click_actual'])
            ->toArray();
    }


    /**
     * @notes 分表情况下软删除重写方法
     * @param $data
     * @param bool $force
     * @return bool
     * @throws \think\db\exception\DbException
     * @author yfdong
     * @date 2025/02/26 23:24
     */
    public static function destroy($data, bool $force = false): bool
    {
        return Help::query()->where('id', $data)->update(['delete_time' => time()]) > 0;
    }


}