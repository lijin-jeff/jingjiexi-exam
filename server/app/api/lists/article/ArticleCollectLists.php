<?php
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 精解析答题系统开发者版权所有，拥有最终解释权。

namespace app\api\lists\article;

use app\api\lists\BaseApiDataLists;
use app\common\enum\YesNoEnum;
use app\common\model\article\Article;

/**
 * 文章收藏列表
 * Class ArticleCollectLists
 * @package app\api\lists\article
 */
class ArticleCollectLists extends BaseApiDataLists
{

    /**
     * @notes 获取收藏列表
     * @return array
     * @author 精解析答题
     * @date 2022/9/20 16:29
     */
    public function lists(): array
    {
        $field = "c.id,c.article_id,a.title,a.image,a.desc,a.is_show,
        a.click_virtual, a.click_actual,a.create_time, c.create_time as collect_time";

        $lists = (new Article())->alias('a')
            ->join('article_collect c', 'c.article_id = a.id')
            ->field($field)
            ->where([
                'c.user_id' => $this->userId,
                'c.status' => YesNoEnum::YES,
                'a.is_show' => YesNoEnum::YES,
            ])
            ->order(['sort' => 'desc', 'c.id' => 'desc'])
            ->limit($this->limitOffset, $this->limitLength)
            ->append(['click'])
            ->hidden(['click_virtual', 'click_actual'])
            ->select()->toArray();

        foreach ($lists as &$item) {
            $item['collect_time'] = date('Y-m-d H:i', $item['collect_time']);
        }

        return $lists;
    }


    /**
     * @notes 获取收藏数量
     * @return int
     * @author 精解析答题
     * @date 2022/9/20 16:29
     */
    public function count(): int
    {
        return (new Article())->alias('a')
            ->join('article_collect c', 'c.article_id = a.id')
            ->where([
                'c.user_id' => $this->userId,
                'c.status' => YesNoEnum::YES,
                'a.is_show' => YesNoEnum::YES,
            ])
            ->count();
    }
}