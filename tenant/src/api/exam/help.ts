import request from '@/utils/request'

// 帮助中心详情
export function helpDetail(params: any) {
    return request.get({ url: '/exam.help/detail', params })
}

// 帮助中心状态
export function helpStatus(params: any) {
    return request.post({ url: '/exam.help/updateStatus', params })
}

// 帮助中心列表
export function helpLists(params?: any) {
    return request.get({ url: '/exam.help/lists', params })
}
// 帮助中心列表
export function helpAll(params?: any) {
    return request.get({ url: '/exam.help/all', params })
}

// 添加帮助中心
export function helpAdd(params: any) {
    return request.post({ url: '/exam.help/add', params })
}

// 编辑帮助中心
export function helpEdit(params: any) {
    return request.post({ url: '/exam.help/edit', params })
}

// 删除帮助中心
export function helpDelete(params: any) {
    return request.post({ url: '/exam.help/delete', params })
}
