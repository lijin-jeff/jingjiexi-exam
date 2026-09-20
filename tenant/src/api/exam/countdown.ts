import request from '@/utils/request'

/**
 * 倒计时模块API
 */
// 获取倒计时列表
export function countdownLists(params?: any) {
    return request.get({
        url: '/exam.countdown.countdown/lists',
        params
    })
}

// 获取倒计时详情
export function countdownDetail(params: any) {
    return request.get({
        url: '/exam.countdown.countdown/detail',
        params
    })
}

// 添加倒计时
export function countdownAdd(params: any) {
    return request.post({
        url: '/exam.countdown.countdown/add',
        data: params
    })
}

// 编辑倒计时
export function countdownEdit(params: any) {
    return request.post({
        url: '/exam.countdown.countdown/edit',
        data: params
    })
}

// 删除倒计时
export function countdownDelete(params: any) {
    return request.post({
        url: '/exam.countdown.countdown/delete',
        data: params
    })
}

// 更新倒计时状态
export function countdownUpdateStatus(params: any) {
    return request.post({
        url: '/exam.countdown.countdown/updateStatus',
        data: params
    })
}

/**
 * 名人名言模块API
 */
// 获取名人名言列表
export function celebrityQuoteLists(params?: any) {
    return request.get({
        url: '/exam.countdown.celebrity_quote/lists',
        params
    })
}

// 获取名人名言详情
export function celebrityQuoteDetail(params: any) {
    return request.get({
        url: '/exam.countdown.celebrity_quote/detail',
        params
    })
}

// 添加名人名言
export function celebrityQuoteAdd(params: any) {
    return request.post({
        url: '/exam.countdown.celebrity_quote/add',
        data: params
    })
}

// 编辑名人名言
export function celebrityQuoteEdit(params: any) {
    return request.post({
        url: '/exam.countdown.celebrity_quote/edit',
        data: params
    })
}

// 删除名人名言
export function celebrityQuoteDelete(params: any) {
    return request.post({
        url: '/exam.countdown.celebrity_quote/delete',
        data: params
    })
}

// 获取名人名言分类
export function celebrityQuoteCategories(params?: any) {
    return request.get({
        url: '/exam.countdown.celebrity_quote/categories',
        params
    })
}

// 获取随机名人名言
export function celebrityQuoteRandom(params?: any) {
    return request.get({
        url: '/exam.countdown.celebrity_quote/random',
        params
    })
}
