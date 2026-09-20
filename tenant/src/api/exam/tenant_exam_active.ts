import request from '@/utils/request'

// 题库激活列表
export function apiTenantExamActiveLists(params: any) {
    return request.get({ url: '/exam.tenant_exam_active/lists', params })
}

// 添加题库激活
export function apiTenantExamActiveAdd(params: any) {
    return request.post({ url: '/exam.tenant_exam_active/add', params })
}

// 编辑题库激活
export function apiTenantExamActiveEdit(params: any) {
    return request.post({ url: '/exam.tenant_exam_active/edit', params })
}

// 删除题库激活
export function apiTenantExamActiveDelete(params: any) {
    return request.post({ url: '/exam.tenant_exam_active/delete', params })
}

// 题库激活详情
export function apiTenantExamActiveDetail(params: any) {
    return request.get({ url: '/exam.tenant_exam_active/detail', params })
}
