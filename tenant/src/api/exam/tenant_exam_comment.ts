import request from '@/utils/request'

// 试题评论表列表
export function apiTenantExamCommentLists(params: any) {
    return request.get({ url: '/exam.tenant_exam_comment/lists', params })
}

// 添加试题评论表
export function apiTenantExamCommentAdd(params: any) {
    return request.post({ url: '/exam.tenant_exam_comment/add', params })
}

// 编辑试题评论表
export function apiTenantExamCommentEdit(params: any) {
    return request.post({ url: '/exam.tenant_exam_comment/edit', params })
}

// 删除试题评论表
export function apiTenantExamCommentDelete(params: any) {
    return request.post({ url: '/exam.tenant_exam_comment/delete', params })
}

// 试题评论表详情
export function apiTenantExamCommentDetail(params: any) {
    return request.get({ url: '/exam.tenant_exam_comment/detail', params })
}
