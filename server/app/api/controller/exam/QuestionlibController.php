<?php
declare(strict_types=1);
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 精解析答题系统开发者版权所有，拥有最终解释权。
namespace app\api\controller\exam;

use app\api\controller\BaseApiController;
use app\api\lists\exam\QuestionlibLists;
use app\api\logic\exam\QuestionlibLogic;
use app\api\validate\CommonValidate;
use app\api\validate\exam\QuestionValidate;

class QuestionlibController extends BaseApiController
{
    public array $notNeedLogin = [
        'recommendList', 
        'questionList', 
        'hotList',
        'questionMenu',
        'questionDetail',
        'searchOptionList',
        'questionTypeList',
        'libraryStats',
        'selectedQuestionCount',
        'chapterList',
        'myErrorCorrectList',
    ];



    /**
     * 推荐题库
     * @return \think\response\Json
     * @link 精解析
     * @email 精解析
     * @date 2025/5/3 00:02
     * @author 精解析答题 <精解析>
     */
    public function recommendList(): \think\response\Json
    {
       // print_r($this->request->param());exit;

        (new CommonValidate())->get()->goCheck('page');
        return $this->dataLists(new QuestionlibLists());
    }

    /**
     * 热门题库
     * @return \think\response\Json
     * @link 精解析
     * @email 精解析
     * @date 2025/5/3 00:02
     * @author 精解析答题 <精解析>
     */
    public function hotList(): \think\response\Json
    {
        (new CommonValidate())->get()->goCheck('page');
        return $this->dataLists(new QuestionlibLists());
    }

    /**
     * 题库列表
     * @return \think\response\Json
     * @link 精解析
     * @email 精解析
     * @date 2025/5/3 00:57
     * @author 精解析答题 <精解析>
     */
    public function questionList(): \think\response\Json
    {
        return $this->dataLists(new QuestionlibLists());
    }

    /**
     * 题库详情
     * @return \think\response\Json
     * @link 精解析
     * @email 精解析
     * @date 2025/5/3 01:28
     * @author 精解析答题 <精解析>
     */
    public function questionLibDetail(): \think\response\Json
    {
        (new QuestionValidate())->get()->goCheck('detail');
        return $this->success('题库详情获取成功', QuestionlibLogic::questionLibDetail($this->request->param()));
    }

    /**
     * 题库菜单配置
     * @return \think\response\Json
     * @link 精解析
     * @email 精解析
     * @date 2025/5/3 01:50
     * @author 精解析答题 <精解析>
     */
    public function questionMenu(): \think\response\Json
    {
        (new QuestionValidate())->get()->goCheck('detail');
        return $this->success('题库菜单获取成功', QuestionlibLogic::questionMenu($this->request->param()));
    }

    /**
     * 随机练习试题列表
     * @return \think\response\Json
     * @link 精解析
     * @email 精解析
     * @date 2025/5/3 23:26
     * @author 精解析答题 <精解析>
     */
    public function randOptionList(): \think\response\Json
    {
        (new QuestionValidate())->get()->goCheck('rand');
        $params = $this->request->param();
        $params['user_uid'] = $this->userId;
        return $this->success('试题获取成功', QuestionlibLogic::randOptionList($params));
    }

    /**
     * 顺序练习试题列表
     * @return \think\response\Json
     * @link 精解析
     * @email 精解析
     * @date 2025/5/3 23:26
     * @author 精解析答题 <精解析>
     */
    public function orderOptionList(): \think\response\Json
    {
        $params = $this->request->param();
        $params['user_uid'] = $this->userId;
        return $this->success('试题获取成功', QuestionlibLogic::orderOptionList($params));
    }

    public function orderOptionListByUids(): \think\response\Json
    {
        $params = $this->request->param();
        $params['user_uid'] = $this->userId;
        return $this->success('试题获取成功', QuestionlibLogic::orderOptionListByUids($params));
    }
    /**
     * 试题搜索列表
     * @return \think\response\Json
     * @link 精解析
     * @email 精解析
     * @date 2025/5/3 23:26
     * @author 精解析答题 <精解析>
     */
    public function searchOptionList(): \think\response\Json
    {
        (new QuestionValidate())->get()->goCheck('detail');
        $params = $this->request->param();
        // 只有登录用户才添加user_uid参数
        if ($this->userId) {
            $params['user_uid'] = $this->userId;
        }
        return $this->success('试题获取成功', QuestionlibLogic::searchOptionList($params));
    }

    /**
     * 章节练习试题列表
     * @return \think\response\Json
     * @link 精解析
     * @email 精解析
     * @date 2025/5/3 23:26
     * @author 精解析答题 <精解析>
     */
    public function chapterOptionList(): \think\response\Json
    {
        (new QuestionValidate())->get()->goCheck('chapter');
        $params = $this->request->param();
        $params['user_uid'] = $this->userId;
        return $this->success('试题获取成功', QuestionlibLogic::chapterOptionList($params));
    }

    /**
     * 题型练习试题列表
     * @return \think\response\Json
     * @link 精解析
     * @email 精解析
     * @date 2025/5/3 23:26
     * @author 精解析答题 <精解析>
     */
    public function typeOptionList(): \think\response\Json
    {
        (new QuestionValidate())->get()->goCheck('detail');
        $params = $this->request->param();
        $params['user_uid'] = $this->userId;
        return $this->success('试题获取成功', QuestionlibLogic::typeOptionList($params));
    }

    /**
     * 题库类型
     * @return \think\response\Json
     * @link 精解析
     * @email 精解析
     * @date 2025/5/10 02:13
     * @author 精解析答题 <精解析>
     */
    public function questionTypeList(): \think\response\Json
    {
        (new QuestionValidate())->get()->goCheck('detail');
        return $this->success('题库类型查询成功', QuestionlibLogic::questionTypeList($this->request->param()));
    }

    /**
     * 试题章节
     * @return \think\response\Json
     * @link 精解析
     * @email 精解析
     * @date 2025/5/10 03:17
     * @author 精解析答题 <精解析>
     */
    public function chapterList(): \think\response\Json
    {
        (new QuestionValidate())->get()->goCheck('detail');
        $params = $this->request->param();
        $params['user_uid'] = $this->userId;
        $params['tenant_id'] = request()->tenantId;  // 添加租户ID
        
        $result = QuestionlibLogic::chapterList($params);
        
        return $this->success('章节查询成功', $result);
    }
    
    /**
     * 获取题库整体统计数据
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/12/25
     */
    public function libraryStats(): \think\response\Json
    {
        (new QuestionValidate())->get()->goCheck('detail');
        $params = $this->request->param();
        $params['user_uid'] = $this->userId;
        $params['tenant_id'] = request()->tenantId;
        
        $result = QuestionlibLogic::getLibraryOverallStatistics($params);
        
        return $this->success('统计数据获取成功', $result);
    }

    /**
     * 题库试题收藏
     * @return \think\response\Json
     * @link 精解析
     * @email 精解析
     * @author 精解析答题
     */
    public function questionCollection(): \think\response\Json
    {
        (new QuestionValidate())->post()->goCheck('collection');
        $params = $this->request->param();
        $params['user_uid'] = $this->userId;
        $result = QuestionlibLogic::questionCollection($params);
        if ($result) {
           if ($params['action'] == 1) {
                return $this->success('收藏成功');
            }elseif (QuestionlibLogic::questionCollection($params)) {
                return $this->success('取消收藏成功');
            }
        }
        return $this->fail(QuestionlibLogic::getError());
    }

    /**
     * 收藏统计
     * @return \think\response\Json
     * @link 精解析
     * @email 精解析
     * @author 精解析答题
     */

    public function collectStats(): \think\response\Json
    {
        (new QuestionValidate())->get()->goCheck('detail');
        $params = $this->request->param();
        $params['user_uid'] = $this->userId;
        return $this->success('收藏统计查询成功', QuestionlibLogic::collectStats($params));
    }

    /**
     * 题库试题点赞
     * @return \think\response\Json
     * @link 精解析
     * @email 精解析
     * @author 精解析答题
     */
    public function questionLike(): \think\response\Json
    {
        $params = $this->request->param();
        $params['user_uid'] = $this->userId;
        if (QuestionlibLogic::questionLike($params)) {
            return $this->success('操作成功');
        }
        return $this->fail(QuestionlibLogic::getError());
    }

    

    /**
     * 收藏试题列表
     * @return \think\response\Json
     * @link 精解析
     * @email 精解析
     * @author 精解析答题
     */
    public function collectionOptionList(): \think\response\Json
    {
        (new QuestionValidate())->get()->goCheck('detail');
        $params = $this->request->param();
        $params['user_uid'] = $this->userId;
        return $this->success('收藏试题查询成功', QuestionlibLogic::collectionOptionList($params));
    }

    /**
     * 错题历史记录
     * @return \think\response\Json
     * @link 精解析
     * @email 精解析
     * @author 精解析答题
     */
    public function errorOptionList(): \think\response\Json
    {
        (new QuestionValidate())->get()->goCheck('detail');
        $params = $this->request->param();
        $params['user_uid'] = $this->userId;
        return $this->success('试题查询成功', QuestionlibLogic::errorOptionList($params));
    }

    /**
     * 题库错题移除
     * @return \think\response\Json
     * @link 精解析
     * @email 精解析
     * @author 精解析答题
     */
    public function questionErrorRemove(): \think\response\Json
    {
        (new QuestionValidate())->post()->goCheck('error');
        $params = $this->request->param();
        $params['user_uid'] = $this->userId;
        if (QuestionlibLogic::questionRemoveError($params)) {
            return $this->success('操作成功');
        }
        return $this->fail(QuestionlibLogic::getError());
    }

    /**
     * 标记错题为已消灭
     * @return \think\response\Json
     * @link 精解析
     * @email 精解析
     * @author 精解析答题
     */
    public function eliminateError(): \think\response\Json
    {
        (new QuestionValidate())->post()->goCheck('error');
        $params = $this->request->param();
        $params['user_uid'] = $this->userId;
        if (QuestionlibLogic::eliminateError($params)) {
            return $this->success('操作成功');
        }
        return $this->fail(QuestionlibLogic::getError());
    }

    /**
     * 取消错题已消灭状态
     * @return \think\response\Json
     * @link 精解析
     * @email 精解析
     * @author 精解析答题
     */
    public function restoreError(): \think\response\Json
    {
        (new QuestionValidate())->post()->goCheck('error');
        $params = $this->request->param();
        $params['user_uid'] = $this->userId;
        if (QuestionlibLogic::restoreError($params)) {
            return $this->success('操作成功');
        }
        return $this->fail(QuestionlibLogic::getError());
    }

    /**
     * 获取错题统计数据
     * @return \think\response\Json
     * @link 精解析
     * @email 精解析
     * @author 精解析答题
     */
    public function errorStats(): \think\response\Json
    {
        (new QuestionValidate())->get()->goCheck('detail');
        $params = $this->request->param();
        $params['user_uid'] = $this->userId;
        return $this->success('统计数据查询成功', QuestionlibLogic::errorStats($params));
    }

    /**
     * 试卷试题列表
     * @return \think\response\Json
     * @link 精解析
     * @email 精解析
     * @author 精解析答题
     */
    public function examinationQuestionList(): \think\response\Json
    {
        (new QuestionValidate())->get()->goCheck('detail');
        return $this->success('试题列表查询成功', QuestionlibLogic::examinationQuestionList($this->request->param()));
    }

    // 已选择的题数
     public function selectedQuestionCount(): \think\response\Json
    {
        (new QuestionValidate())->get()->goCheck('detail');
        $params = $this->request->param();
        $params['user_uid'] = $this->userId;
        return $this->success('已选择的题数查询成功', ['count' => QuestionlibLogic::selectedQuestionCount($params)]);
    }

    /**
     * 纠错
     * @return \think\response\Json
     * @link 精解析
     * @email 精解析
     * @author 精解析答题
     */
    public function errorCorrect(): \think\response\Json
    {
        $params = $this->request->param();
        $params['user_id'] = $this->userId;
        if (QuestionlibLogic::addErrorCorrect($params)) {
            return $this->success('操作成功');
        }
        return $this->fail(QuestionlibLogic::getError());
    }

    /**
     * 我的纠错列表
     * @return \think\response\Json
     * @link 精解析
     * @email 精解析
     * @author 精解析答题
     */
    public function myErrorCorrectList(): \think\response\Json
    {
        $params = (new QuestionValidate())->get()->goCheck('MyErrorCorrectList');
        $params['user_uid'] = $this->userId;
        $params['tenant_id'] = request()->tenantId;
        return $this->success('我的纠错列表查询成功', QuestionlibLogic::myErrorCorrectList($params));
    }
    /**
     * 获取知识点列表
     * @return \think\response\Json
     * @link 精解析
     * @email 精解析
     * @author 精解析答题
     */
    public function knowledgeList(): \think\response\Json
    {
        $params = $this->request->param();
        $params['tenant_id'] = request()->tenantId;
        return $this->success('知识点列表查询成功', QuestionlibLogic::knowledgeList($params));
    }

    /**
     * 获取标签列表
     * @return \think\response\Json
     * @link 精解析
     * @email 精解析
     * @author 精解析答题
     */
    public function labelList(): \think\response\Json
    {
        $params = $this->request->param();
        $params['tenant_id'] = request()->tenantId;
        return $this->success('标签列表查询成功', QuestionlibLogic::labelList($params));
    }

    /**
     * 获取题目结构信息
     * @return \think\response\Json
     * @link 精解析
     * @email 精解析
     * @author 精解析答题
     */
    public function getQuestionStructure(): \think\response\Json
    {
        $params = $this->request->param();
        $params['tenant_id'] = request()->tenantId;
        $params['user_uid'] = $this->userId;
        return $this->success('题目结构信息查询成功', QuestionlibLogic::getQuestionStructure($params));
    }
}