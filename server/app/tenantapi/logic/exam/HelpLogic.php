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

use app\common\logic\BaseLogic;
use app\common\model\exam\Help;
use app\common\service\FileService;
use Exception;
use think\facade\Db;

/**
 * 帮助管理逻辑
 * Class HelpLogic
 * @package app\tenantapi\logic\exam
 */
class HelpLogic extends BaseLogic
{

    /**
     * @notes  添加帮助
     * @param array $params
     * @author heshihu
     * @date 2022/2/22 9:57
     */
    public static function add(array $params)
    {
        try {
            Help::create([
                'title'         => $params['title'],
                'tenant_id'     => $params['tenant_id'],
                'author'        => $params['author'] ?? '', //作者
                'sort'          => $params['sort'] ?? 0, // 排序
                'click_virtual' => $params['click_virtual'] ?? 0,
                'image'         => $params['image'] ? FileService::setFileUrl($params['image']) : '',
                'is_show'       => $params['is_show'],
                'content'       => $params['content'] ?? '',
            ]);
            return true;
        } catch (Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }


    /**
     * @notes  编辑帮助
     * @param array $params
     * @return bool
     * @author heshihu
     * @date 2022/2/22 10:12
     */
    public static function edit(array $params): bool
    {
        try {
            Help::update([
                'title'         => $params['title'],
                'author'        => $params['author'] ?? '', //作者
                'sort'          => $params['sort'] ?? 0, // 排序
                'click_virtual' => $params['click_virtual'] ?? 0,
                'image'         => $params['image'] ? FileService::setFileUrl($params['image']) : '',
                'is_show'       => $params['is_show'],
                'content'       => $params['content'] ?? '',
            ], ['id' => $params['id']]);
            return true;
        } catch (Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }


    /**
     * @notes  删除帮助
     * @param array $params
     * @author heshihu
     * @date 2022/2/22 10:17
     */
    public static function delete(array $params)
    {
        Help::destroy($params['id']);
    }

    /**
     * @notes  查看帮助详情
     * @param $params
     * @return array
     * @author heshihu
     * @date 2022/2/22 10:15
     */
    public static function detail($params): array
    {
        return Help::findOrEmpty($params['id'])->toArray();
    }

    /**
     * @notes  更改帮助状态
     * @param array $params
     * @return false|void
     * @author heshihu
     * @date 2022/2/22 10:18
     */
    public static function updateStatus(array $params)
    {
        try {
            Help::update([
                'is_show' => $params['is_show']
            ], ['id' => $params['id']]);
        } catch (Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 初始化租户帮助列表
     * @param mixed $tenant_id
     * @return void
     * @throws Exception
     * @author yfdong
     * @date 2024/09/10 20:59
     */
    public static function initialization(mixed $tenant_id): void
    {
        Db::startTrans();
        try {
            $helpField = 'tenant_id,title,image,author,content,click_virtual,click_actual,is_show,sort';

            $templateHelp = Help::where('tenant_id', 0)->field($helpField)->select()->toArray();

            foreach ($templateHelp as $item) {  
                $item['tenant_id'] = $tenant_id;
                Help::create($item);
            }
            Db::commit();
        } catch (Exception) {
            Db::rollback();
            throw new Exception('帮助初始化失败');
        }
    }
}