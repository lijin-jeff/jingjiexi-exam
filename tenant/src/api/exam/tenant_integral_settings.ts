import request from '@/utils/request'

// 积分设置列表
export function apiTenantIntegralSettingsLists(params: any) {
    return request.get({ url: '/exam.tenant_integral_settings/lists', params })
}

// 添加积分设置
export function apiTenantIntegralSettingsAdd(params: any) {
    return request.post({ url: '/exam.tenant_integral_settings/add', params })
}

// 编辑积分设置
export function apiTenantIntegralSettingsEdit(params: any) {
    return request.post({ url: '/exam.tenant_integral_settings/edit', params })
}

// 删除积分设置
export function apiTenantIntegralSettingsDelete(params: any) {
    return request.post({ url: '/exam.tenant_integral_settings/delete', params })
}

// 积分设置详情
export function apiTenantIntegralSettingsDetail(params: any) {
    return request.get({ url: '/exam.tenant_integral_settings/detail', params })
}

// 清空积分
export function apiTenantIntegralSettingsClear(params: any) {
    return request.post({ url: '/exam.tenant_integral_settings/clear', params })
}
