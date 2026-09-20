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

namespace app\api\controller\exam;

use app\api\controller\BaseApiController;
use app\api\lists\exam\CommentLists;
use app\api\logic\exam\CommentLogic;
use think\response\Json;

/**
 * 评论/笔记相关接口
 * Class CommentController
 * @package app\api\controller\exam
 */
class CommentController extends BaseApiController
{


    // public array $notNeedLogin = ['commentList', 'commentDetail', 'commentLikeUsers', 'commentCancelLike'];

    // 添加评论
    public function addComment()
    {
      $params = $this->request->param();
      $params['user_id'] = $this->userId;
      $result = CommentLogic::addComment($params);
        if (false === $result) {
            return $this->fail(CommentLogic::getError());
        }
      return $this->success('添加成功');
    }
    
    // 编辑评论
    public function editComment()
    {
      $params = $this->request->param();
      $params['user_id'] = $this->userId;
      $result = CommentLogic::editComment($params);
        if (false === $result) {
            return $this->fail(CommentLogic::getError());
        }
      return $this->success('编辑成功');
    }
    
    // 删除评论
    public function deleteComment()
    {
      $params = $this->request->param();
      $params['user_id'] = $this->userId;
      $result = CommentLogic::deleteComment($params);
        if (false === $result) {
            return $this->fail(CommentLogic::getError());
        }
      return $this->success('删除成功');
    }



    //评论列表
    public function commentList()
    {
         return $this->dataLists(new CommentLists());
    }

    // 我的评论列表
    public function myCommentList()
    {
         $params = $this->request->param();
         $params['user_id'] = $this->userId;
         $result = (new CommentLists())->myCommentList($params);
        if (false === $result) {
            return $this->fail(CommentLogic::getError());
        }
        
        return $this->data($result);
    }
    /**
     * 评论详情（笔记详情）
     * @return Json
     */
    public function commentDetail()
    {
        $comment_id = $this->request->get('comment_id/d', 0);
        
        if (!$comment_id) {
            return $this->fail('评论 ID 不能为空');
        }
        
        $result = CommentLogic::getCommentDetail($comment_id, $this->userId);
        
        if (false === $result) {
            return $this->fail(CommentLogic::getError());
        }
        
        return $this->data($result);
    }

    /**
     * 获取评论点赞用户列表
     * @return Json
     */
    public function commentLikeUsers()
    {
        $comment_id = $this->request->get('comment_id/d', 0);
        $limit = $this->request->get('limit/d', 5);
        
        if (!$comment_id) {
            return $this->fail('评论 ID 不能为空');
        }
        
        $result = CommentLogic::getCommentLikeUsers($comment_id, $limit);
        
        if (false === $result) {
            return $this->fail(CommentLogic::getError());
        }
        
        return $this->data($result);
    }

    /**
     * 添加评论点赞
     * @return Json
     */
    public function addCommentLike()
    {
        // 这里可根据实际需求添加具体逻辑
        $params = $this->request->param();
        $params['user_id'] = $this->userId;
        $result = CommentLogic::addCommentLike($params);
        if (false === $result) {
            return $this->fail(CommentLogic::getError());
        }
        return $this->success('操作成功');
    }

    /**
     * 取消评论点赞
     * @return Json
     */
    public function cancelCommentLike()
    {
        // 这里可根据实际需求添加具体逻辑
        $params = $this->request->param();
        $params['user_id'] = $this->userId;
        $result = CommentLogic::cancelCommentLike($params);
        if (false === $result) {
            return $this->fail(CommentLogic::getError());
        }
        return $this->success('操作成功');
    }
}