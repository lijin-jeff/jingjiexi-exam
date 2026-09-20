import request from "@/util/request"

export default {
    apiCommonAddComment(params) {// 发布评论（type=1：试题评论，type=2：文章评论，type=3：资源评论）
        return request.post("api/exam.comment/addComment", params).then(res => {
            return res
        })
    },
    apiCommonEditComment(params) {// 编辑评论
        return request.post("api/exam.comment/editComment", params).then(res => {
            return res
        })
    },
    apiCommonDeleteComment(params) {// 删除评论
        return request.post("api/exam.comment/deleteComment", params).then(res => {
            return res
        })
    },
    apiCommonCommentList(params) {// 获取评论列表
        return request.get("api/exam.comment/commentList", {
            params: params
        }).then(res => {
            return res
        })
    },
    apiMyCommentList(params) {// 获取我的评论列表
        return request.get("api/exam.comment/myCommentList", {
            params: params
        }).then(res => {
            return res
        })
    },
    apiCommentDetail(params) {// 获取评论详情
        return request.get("api/exam.comment/commentDetail", {
            params: params
        }).then(res => {
            return res
        })
    },
    apiCommentLikeUsers(params) {// 获取评论点赞用户列表
        return request.get("api/exam.comment/commentLikeUsers", {
            params: params
        }).then(res => {
            return res
        })
    },
    apiCommonAddLike(params) { // 评论点赞
        return request.post("api/exam.comment/addCommentLike", params).then(res => {
            return res
        })
    },
    apiCommonCancelLike(params) { // 评论取消点赞
        console.log(params)
        return request.post("api/exam.comment/cancelCommentLike", params).then(res => {
            return res
        })
    }
}