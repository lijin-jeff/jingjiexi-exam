import request from '@/utils/request'

// 激活码批次表列表
export function apiTenantExamActivationCodeBatchLists(params: any) {
    return request.get({ url: '/exam.tenant_exam_activation_code_batch/lists', params })
}

// 添加激活码批次表
export function apiTenantExamActivationCodeBatchAdd(params: any) {
    return request.post({ url: '/exam.tenant_exam_activation_code_batch/add', params })
}

// 编辑激活码批次表
export function apiTenantExamActivationCodeBatchEdit(params: any) {
    return request.post({ url: '/exam.tenant_exam_activation_code_batch/edit', params })
}

// 删除激活码批次表
export function apiTenantExamActivationCodeBatchDelete(params: any) {
    return request.post({ url: '/exam.tenant_exam_activation_code_batch/delete', params })
}

// 激活码批次表详情
export function apiTenantExamActivationCodeBatchDetail(params: any) {
    return request.get({ url: '/exam.tenant_exam_activation_code_batch/detail', params })
}

// 激活码表列表
export function apiTenantExamActivationCodeLists(params: any) {
    return request.get({ url: '/exam.tenant_exam_activation_code/lists', params })
}

// 禁用\启用激活码
export function apiTenantExamActivationCodeDisableEnable(params: any) {
    return request.post({ url: '/exam.tenant_exam_activation_code/updateStatus', params })
}
