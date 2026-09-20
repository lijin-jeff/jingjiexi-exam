import request from '@/utils/request'

// 试卷管理列表
export function apiTenantExamPaperLists(params: any) {
    return request.get({ url: '/exam.tenant_exam_paper/lists', params })
}

// 添加试卷管理
export function apiTenantExamPaperAdd(params: any) {
    return request.post({ url: '/exam.tenant_exam_paper/add', params })
}

// 试卷组卷管理
export function apiTenantExamSavePaper(params: any) {
    return request.post({ url: '/exam.tenant_exam_paper/savePaper', params })
}

// 编辑试卷管理
export function apiTenantExamPaperEdit(params: any) {
    return request.post({ url: '/exam.tenant_exam_paper/edit', params })
}

// 删除试卷管理
export function apiTenantExamPaperDelete(params: any) {
    return request.post({ url: '/exam.tenant_exam_paper/delete', params })
}

// 试卷管理详情
export function apiTenantExamPaperDetail(params: any) {
    return request.get({ url: '/exam.tenant_exam_paper/detail', params })
}

// 试卷组卷详情
export function apiTenantPaperDetail(params: any) {
    return request.get({ url: '/exam.tenant_exam_paper/paperDetail', params })
}
