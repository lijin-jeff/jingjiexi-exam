import request from '@/utils/request'

// 图片配置管理列表
export function apiTenantBannerLists(params: any) {
    return request.get({ url: '/exam.tenant_setting_banner/lists', params })
}

// 添加图片配置管理
export function apiTenantBannerAdd(params: any) {
    return request.post({ url: '/exam.tenant_setting_banner/add', params })
}

// 编辑图片配置管理
export function apiTenantBannerEdit(params: any) {
    return request.post({ url: '/exam.tenant_setting_banner/edit', params })
}

// 删除图片配置管理
export function apiTenantBannerDelete(params: any) {
    return request.post({ url: '/exam.tenant_setting_banner/delete', params })
}

// 图片配置管理详情
export function apiTenantBannerDetail(params: any) {
    return request.get({ url: '/exam.tenant_setting_banner/detail', params })
}
