import request from '@/utils/request'

// 版本更新列表
export function versionUpdateLists(params?: any) {
    return request.get({ url: '/exam.tenant_version_update/lists', params })
}

// 版本更新详情
export function versionUpdateDetail(params: any) {
    return request.get({ url: '/exam.tenant_version_update/detail', params })
}

// 添加版本更新
export function versionUpdateAdd(params: any) {
    return request.post({ url: '/exam.tenant_version_update/add', params })
}

// 编辑版本更新
export function versionUpdateEdit(params: any) {
    return request.post({ url: '/exam.tenant_version_update/edit', params })
}

// 删除版本更新
export function versionUpdateDelete(params: any) {
    return request.post({ url: '/exam.tenant_version_update/delete', params })
}

// 更新版本更新状态
export function versionUpdateStatus(params: any) {
    return request.post({ url: '/exam.tenant_version_update/updateStatus', params })
}
