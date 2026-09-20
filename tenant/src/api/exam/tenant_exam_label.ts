import request from '@/utils/request'

// 试题标签列表
export function apiTenantExamLabelLists(params: any) {
    //console.log(params)
    return request.get({ url: '/exam.tenant_exam_label/lists', params })
}

// 添加试题标签
export function apiTenantExamLabelAdd(params: any) {
    return request.post({ url: '/exam.tenant_exam_label/add', params })
}

// 编辑试题标签
export function apiTenantExamLabelEdit(params: any) {
    return request.post({ url: '/exam.tenant_exam_label/edit', params })
}

// 删除试题标签
export function apiTenantExamLabelDelete(params: any) {
    return request.post({ url: '/exam.tenant_exam_label/delete', params })
}

// 试题标签详情
export function apiTenantExamLabelDetail(params: any) {
    return request.get({ url: '/exam.tenant_exam_label/detail', params })
}
