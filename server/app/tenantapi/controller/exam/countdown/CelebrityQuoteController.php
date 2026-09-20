<?php
// +----------------------------------------------------------------------
// | likeadmin快速开发前后端分离管理后台（PHP版）
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// +----------------------------------------------------------------------
// | 开源版本可自由商用，可去除界面版权logo
// +----------------------------------------------------------------------
// | gitee下载：https://gitee.com/likeshop_gitee/likeadmin
// | github下载：https://github.com/likeshop-github/likeadmin
// | 访问官网：https://www.likeadmin.cn
// | likeadmin团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeadminTeam
// +----------------------------------------------------------------------

namespace app\tenantapi\controller\exam\countdown;

use app\tenantapi\controller\BaseAdminController;
use app\tenantapi\lists\exam\countdown\CelebrityLists;
use app\tenantapi\logic\exam\countdown\CelebrityLogic;
use app\tenantapi\validate\exam\countdown\CelebrityQuoteValidate;

/**
 * 名人名言控制器
 * Class CelebrityQuoteController
 * @package app\tenantapi\controller\exam\countdown
 */
class CelebrityQuoteController extends BaseAdminController
{

    /**
     * @notes  查看名人名言列表
     * @return \think\response\Json
     * @author likeadmin
     * @date 2024/01/08
     */
    public function lists()
    {
        return $this->dataLists(new CelebrityLists());
    }

    /**
     * @notes  添加名人名言
     * @return \think\response\Json
     * @author likeadmin
     * @date 2024/01/08
     */
    public function add()
    {
        $params = (new CelebrityQuoteValidate())->post()->goCheck('add');
        $params['tenant_id'] = $this->tenantId;
        try {
            CelebrityLogic::add($params);        
            return $this->success('添加成功', [], 1, 1);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * @notes  编辑名人名言
     * @return \think\response\Json
     * @author likeadmin
     * @date 2024/01/08
     */
    public function edit()
    {
        $params = (new CelebrityQuoteValidate())->post()->goCheck('edit');
        try {
            CelebrityLogic::edit($params);
            return $this->success('编辑成功', [], 1, 1);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * @notes  删除名人名言
     * @return \think\response\Json
     * @author likeadmin
     * @date 2024/01/08
     */
    public function delete()
    {
        $params = (new CelebrityQuoteValidate())->post()->goCheck('delete');
        try {
            CelebrityLogic::delete($params['id']);
            return $this->success('删除成功', [], 1, 1);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * @notes  名人名言详情
     * @return \think\response\Json
     * @author likeadmin
     * @date 2024/01/08
     */
    public function detail()
    {
        $params = (new CelebrityQuoteValidate())->goCheck('detail');
        try {
            $result = CelebrityLogic::detail($params['id']);
            return $this->data($result);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * @notes  获取名人名言分类
     * @return \think\response\Json
     * @author likeadmin
     * @date 2024/01/08
     */
    public function categories()
    {
        $result = CelebrityLogic::getCategories();
        return $this->data($result);
    }

    /**
     * @notes  获取随机名人名言
     * @return \think\response\Json
     * @author likeadmin
     * @date 2024/01/08
     */
    public function random()
    {
        $result = CelebrityLogic::random();
        return $this->data($result);
    }
}
