import request from '@/utils/request'

// 题目纠错信息表列表
export function apiTenantExamQuestionCorrectionsLists(params: any) {
    //console.log('11111222')
    return request.get({ url: '/exam.tenant_exam_question_corrections/lists', params })
}

// 添加题目纠错信息表
export function apiTenantExamQuestionCorrectionsAdd(params: any) {
    return request.post({ url: '/exam.tenant_exam_question_corrections/add', params })
}

// 编辑题目纠错信息表
export function apiTenantExamQuestionCorrectionsEdit(params: any) {
    return request.post({ url: '/exam.tenant_exam_question_corrections/edit', params })
}

// 删除题目纠错信息表
export function apiTenantExamQuestionCorrectionsDelete(params: any) {
    return request.post({ url: '/exam.tenant_exam_question_corrections/delete', params })
}

// 题目纠错信息表详情
export function apiTenantExamQuestionCorrectionsDetail(params: any) {
    return request.get({ url: '/exam.tenant_exam_question_corrections/detail', params })
}

//编辑题目
export function apiTenantExamQuestionCorrectionsUpdate(params: any) {
    return request.post({ url: '/exam.tenant_exam_question_corrections/update', params })
}
