import request from '@/utils/request'

// 添加其他设置
export function apiTenantOtherSettingsAdd(params: any) {
    return request.post({ url: '/exam.tenant_other_settings/add', params })
}

// 编辑其他设置
export function apiTenantOtherSettingsEdit(params: any) {
    return request.post({ url: '/exam.tenant_other_settings/edit', params })
}

// 其他设置详情
export function apiTenantOtherSettingsDetail(params: any) {
    return request.get({ url: '/exam.tenant_other_settings/detail', params })
}
