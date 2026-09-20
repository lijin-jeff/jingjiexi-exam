<?php
declare(strict_types=1);
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。

// | 精解析答题系统开发者版权所有，拥有最终解释权。
namespace app\api\lists\exam;

use app\api\lists\BaseApiDataLists;
use app\common\model\exam\TenantExamLibrary;

class QuestionlibLists extends BaseApiDataLists
{

    public function setSearch(): array
    {
        return [
        ];
    }

    public function queryWhere(): array
    {
        $where[] = ['is_show', '=', 1];
        $params = request()->param();
        if (!empty($params['recommend_state'])) {
            $where[] = ['recommend_state', '=', $params['recommend_state']];
        }
        if (!empty($params['hot_state'])) {
            $where[] = ['hot_state', '=', $params['hot_state']];
        }
        if (!empty($params['cate_uid'])) {
            $where[] = ['category_uid', '=', $params['cate_uid']];
        }
        if (!empty($params['title'])) {
            $where[] = ['title', 'like', '%' . $params['title'] . '%'];
        }
        if (!empty($params['category_uid'])) {
            $where[] = ['category_uid', '=', $params['category_uid']];
        }
        return $where;
    }

    /**
     * @notes 题库列表
     * @return array
     * @date 2025/5/3 00:01
     * @author 精解析答题
     */
    public function lists(): array
    {
        $request = request();
        $lists = TenantExamLibrary::getBaseQuery($this->queryWhere(), $request)
            ->field(['uid', 'image', 'title', 'category_uid', 'create_time'])
            ->append(['cate_name', 'submit_count'])
            ->limit($this->limitOffset, $this->limitLength)
            ->order('sort desc,id desc')
            ->select()
            ->toArray();
        foreach ($lists as &$item) {
            $item['create_time'] = date('Y-m-d', strtotime($item['create_time']));
        }

        return $lists;
    }

    /**
     * @return int
     * @date 2025/5/3 00:01
     * @author 精解析答题
     */
    public function count(): int
    {
        $request = request();
        return TenantExamLibrary::getBaseQuery($this->queryWhere(), $request)->count();
    }
}