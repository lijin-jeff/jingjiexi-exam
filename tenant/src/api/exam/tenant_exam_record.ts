import request from '@/utils/request'

// 做题记录表列表
export function apiTenantExamRecordLists(params: any) {
    //console.log(params);
    return request.get({ url: '/exam.tenant_exam_record/lists', params })
}

// 添加做题记录表
export function apiTenantExamRecordAdd(params: any) {
    return request.post({ url: '/exam.tenant_exam_record/add', params })
}

// 编辑做题记录表
export function apiTenantExamRecordEdit(params: any) {
    return request.post({ url: '/exam.tenant_exam_record/edit', params })
}

// 删除做题记录表
export function apiTenantExamRecordDelete(params: any) {
    return request.post({ url: '/exam.tenant_exam_record/delete', params })
}

// 做题记录表详情
export function apiTenantExamRecordDetail(params: any) {
    return request.get({ url: '/exam.tenant_exam_record/detail', params })
}
