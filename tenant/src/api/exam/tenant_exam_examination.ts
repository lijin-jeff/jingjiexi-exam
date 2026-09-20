import request from '@/utils/request'

// 考试管理列表
export function apiTenantExamExaminationLists(params: any) {
    return request.get({ url: '/exam.tenant_exam_examination/lists', params })
}

// 添加考试管理
export function apiTenantExamExaminationAdd(params: any) {
    return request.post({ url: '/exam.tenant_exam_examination/add', params })
}

// 编辑考试管理
export function apiTenantExamExaminationEdit(params: any) {
    return request.post({ url: '/exam.tenant_exam_examination/edit', params })
}

// 删除考试管理
export function apiTenantExamExaminationDelete(params: any) {
    return request.post({ url: '/exam.tenant_exam_examination/delete', params })
}

// 考试管理详情
export function apiTenantExamExaminationDetail(params: any) {
    return request.get({ url: '/exam.tenant_exam_examination/detail', params })
}

// 考试绑定试卷
export function apiTenantExamExaminationSavePaper(params: any) {
    return request.post({ url: '/exam.tenant_exam_examination/savePaper', params })
}
