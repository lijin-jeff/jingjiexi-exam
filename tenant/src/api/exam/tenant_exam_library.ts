import request from '@/utils/request'

// 题库管理列表
export function apiTenantExamLibraryLists(params: any) {
    return request.get({ url: '/exam.tenant_exam_library/lists', params })
}

// 添加题库管理
export function apiTenantExamLibraryAdd(params: any) {
    return request.post({ url: '/exam.tenant_exam_library/add', params })
}

// 编辑题库管理
export function apiTenantExamLibraryEdit(params: any) {
    return request.post({ url: '/exam.tenant_exam_library/edit', params })
}

// 删除题库管理
export function apiTenantExamLibraryDelete(params: any) {
    return request.post({ url: '/exam.tenant_exam_library/delete', params })
}

// 题库管理详情
export function apiTenantExamLibraryDetail(params: any) {
    return request.get({ url: '/exam.tenant_exam_library/detail', params })
}

// 导出题库数据
export function apiTenantExamLibraryExport(params: any) {
    return request.get({ url: '/exam.tenant_exam_library/export', params })
}
