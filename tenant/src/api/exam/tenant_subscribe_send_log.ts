import request from '@/utils/request'

// 订阅消息发送记录列表
export function subscribeSendLogLists(params?: any) {
    return request.post({ url: '/subscribe_send_log/lists', params })
}

// 订阅消息发送记录详情
export function subscribeSendLogDetail(params: any) {
    return request.get({ url: `/subscribe_send_log/detail/${params.id}` })
}

// 创建订阅消息发送记录
export function subscribeSendLogAdd(params: any) {
    return request.post({ url: '/subscribe_send_log/create', params })
}

// 编辑订阅消息发送记录
export function subscribeSendLogEdit(params: any) {
    return request.post({ url: `/subscribe_send_log/update/${params.id}`, params })
}

// 删除订阅消息发送记录
export function subscribeSendLogDelete(params: any) {
    return request.post({ url: `/subscribe_send_log/delete/${params.id}` })
}

// 批量删除订阅消息发送记录
export function subscribeSendLogBatchDelete(params: any) {
    return request.post({ url: '/subscribe_send_log/batch_delete', params })
}

// 订阅消息发送记录统计
export function subscribeSendLogStats(params?: any) {
    return request.get({ url: '/subscribe_send_log/stats', params })
}
