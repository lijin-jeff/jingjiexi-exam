import request from '@/utils/request'

// 积分记录列表
export function apiTenantUserIntegralLogLists(params: any) {
    return request.get({ url: '/exam.tenant_user_integral_log/lists', params })
}

// 添加积分记录
export function apiTenantUserIntegralLogAdd(params: any) {
    return request.post({ url: '/exam.tenant_user_integral_log/add', params })
}

// 编辑积分记录
export function apiTenantUserIntegralLogEdit(params: any) {
    return request.post({ url: '/exam.tenant_user_integral_log/edit', params })
}

// 删除积分记录
export function apiTenantUserIntegralLogDelete(params: any) {
    return request.post({ url: '/exam.tenant_user_integral_log/delete', params })
}

// 积分记录详情
export function apiTenantUserIntegralLogDetail(params: any) {
    return request.get({ url: '/exam.tenant_user_integral_log/detail', params })
}
