<?php
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 精解析答题系统开发者版权所有，拥有最终解释权。

namespace app\common\model\article;

use app\common\enum\YesNoEnum;
use app\common\model\BaseModel;
use think\model\concern\SoftDelete;

/**
 * 资讯收藏
 * Class ArticleCollect
 * @package app\common\model\article
 */
class ArticleCollect extends BaseModel
{
    use SoftDelete;

    protected $deleteTime = 'delete_time';
    protected $name = 'article_collect';


    /**
     * @notes 是否已收藏文章
     * @param $userId
     * @param $articleId
     * @return bool (true=已收藏, false=未收藏)
     * @author 精解析答题
     * @date 2022/10/20 15:13
     */
    public static function isCollectArticle($userId, $articleId)
    {
        $collect = ArticleCollect::where([
            'user_id' => $userId,
            'article_id' => $articleId,
            'status' => YesNoEnum::YES
        ])->findOrEmpty();

        return !$collect->isEmpty();
    }

}