import request from '@/utils/request'

// 激活码激活记录表列表
export function apiTenantExamActivationRecordLists(params: any) {
    return request.get({ url: '/exam.tenant_exam_activation_record/lists', params })
}

// 添加激活码激活记录表
export function apiTenantExamActivationRecordAdd(params: any) {
    return request.post({ url: '/exam.tenant_exam_activation_record/add', params })
}

// 编辑激活码激活记录表
export function apiTenantExamActivationRecordEdit(params: any) {
    return request.post({ url: '/exam.tenant_exam_activation_record/edit', params })
}

// 删除激活码激活记录表
export function apiTenantExamActivationRecordDelete(params: any) {
    return request.post({ url: '/exam.tenant_exam_activation_record/delete', params })
}

// 激活码激活记录表详情
export function apiTenantExamActivationRecordDetail(params: any) {
    return request.get({ url: '/exam.tenant_exam_activation_record/detail', params })
}
