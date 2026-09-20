import request from '@/utils/request'

// 资源分类列表
export function apiTenantResourceCategoryLists(params: any) {
    return request.get({ url: '/exam.resource.tenant_resource_category/lists', params })
}

// 添加资源分类
export function apiTenantResourceCategoryAdd(params: any) {
    return request.post({ url: '/exam.resource.tenant_resource_category/add', params })
}

// 编辑资源分类
export function apiTenantResourceCategoryEdit(params: any) {
    return request.post({ url: '/exam.resource.tenant_resource_category/edit', params })
}

// 删除资源分类
export function apiTenantResourceCategoryDelete(params: any) {
    return request.post({ url: '/exam.resource.tenant_resource_category/delete', params })
}

// 资源分类详情
export function apiTenantResourceCategoryDetail(params: any) {
    return request.get({ url: '/exam.resource.tenant_resource_category/detail', params })
}

// 资源分类父级列表
export function apiTenantResourceCategoryParent(params?: any) {
    return request.get({ url: '/exam.resource.tenant_resource_category/parentList', params })
}

// 资源分类树
export function apiTenantResourceCategoryTree() {
    return request.get({ url: '/exam.resource.tenant_resource_category/categoryTree' })
}
