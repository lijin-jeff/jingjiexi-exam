import request from '@/utils/request'

// 资源管理列表
export function apiTenantResourceLists(params: any) {
    return request.get({ url: '/exam.resource.tenant_resource/lists', params })
}

// 添加资源管理
export function apiTenantResourceAdd(params: any) {
    return request.post({ url: '/exam.resource.tenant_resource/add', params })
}

// 编辑资源管理
export function apiTenantResourceEdit(params: any) {
    return request.post({ url: '/exam.resource.tenant_resource/edit', params })
}

// 删除资源管理
export function apiTenantResourceDelete(params: any) {
    return request.post({ url: '/exam.resource.tenant_resource/delete', params })
}

// 资源管理详情
export function apiTenantResourceDetail(params: any) {
    return request.get({ url: '/exam.resource.tenant_resource/detail', params })
}

// 资源管理分类列表
export function apiTenantResourceCategoryLists() {
    return request.get({ url: '/exam.resource.tenant_resource_category/lists' })
}

// 复制资源管理
export function apiTenantResourceCopy(params: any) {
    return request.post({ url: '/exam.resource.tenant_resource/copy', params })
}
