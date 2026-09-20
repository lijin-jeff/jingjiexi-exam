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

namespace app\tenantapi\validate\exam\countdown;

use app\common\validate\BaseValidate;
use app\common\model\exam\countdown\TenantExamCelebrityQuote;

/**
 * 名人名言验证器
 * Class CelebrityQuoteValidate
 * @package app\tenantapi\validate\exam\countdown
 */
class CelebrityQuoteValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|checkCelebrityQuote',
        'content' => 'require|length:1,500',
        'category' => 'length:0,50',
    ];

    protected $message = [
        'id.require' => 'id不能为空',
        'content.require' => '内容不能为空',
        'content.length' => '内容长度须在1-500位字符',
        'category.length' => '分类长度不能超过50位字符',
    ];

    /**
     * @notes  添加场景
     * @return CelebrityQuoteValidate
     * @author likeadmin
     * @date 2024/01/08
     */
    public function sceneAdd()
    {
        return $this->only(['content', 'category']);
    }

    /**
     * @notes  详情场景
     * @return CelebrityQuoteValidate
     * @author likeadmin
     * @date 2024/01/08
     */
    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    /**
     * @notes  编辑场景
     * @return CelebrityQuoteValidate
     * @author likeadmin
     * @date 2024/01/08
     */
    public function sceneEdit()
    {
        return $this->only(['id', 'content', 'category']);
    }

    /**
     * @notes  删除场景
     * @return CelebrityQuoteValidate
     * @author likeadmin
     * @date 2024/01/08
     */
    public function sceneDelete()
    {
        return $this->only(['id']);
    }

    /**
     * @notes  检查指定名人名言是否存在
     * @param $value
     * @return bool|string
     * @author likeadmin
     * @date 2024/01/08
     */
    public function checkCelebrityQuote($value)
    {
        $quote = TenantExamCelebrityQuote::findOrEmpty($value);
        if ($quote->isEmpty()) {
            return '名人名言不存在';
        }
        return true;
    }
}
