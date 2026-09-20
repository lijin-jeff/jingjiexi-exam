import request from '@/utils/request'

// 题库试题列表
export function apiTenantExamQuestionLists(params: any) {
    return request.get({ url: '/exam.tenant_exam_question/lists', params })
}

// 添加题库试题
export function apiTenantExamQuestionAdd(params: any) {
    return request.post({ url: '/exam.tenant_exam_question/add', params })
}

// 文本批量导入
export function apiTenantExamQuestionTextAdd(params: any) {
    return request.post({ url: '/exam.tenant_exam_question/textAdd', params })
}

// 编辑题库试题
export function apiTenantExamQuestionEdit(params: any) {
    return request.post({ url: '/exam.tenant_exam_question/edit', params })
}

// 删除题库试题
export function apiTenantExamQuestionDelete(params: any) {
    return request.post({ url: '/exam.tenant_exam_question/delete', params })
}

// 题库试题详情
export function apiTenantExamQuestionDetail(params: any) {
    console.log(params)
    return request.get({ url: '/exam.tenant_exam_question/detail', params })
}

// 查询题库试题(通过题库 id)
export function apiTenantExamQuestionList(params: any) {
    return request.get({ url: '/exam.tenant_exam_question/questionListByLibrary', params })
}
