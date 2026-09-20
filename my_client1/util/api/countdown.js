import request from "@/util/request"

export default {
    // 获取倒计时列表
    /**
     * @description 获取倒计时列表
     * @return { Promise }
     */
    apiGetCountdownLists(params) {
        return request.get('api/exam.countdown.countdown/lists', {
            params: params
        }).then(res => {
            return res
        })
    },
    
    // 获取倒计时详情
    /**
     * @description 获取倒计时详情
     * @return { Promise }
     */
    apiGetCountdownDetail(params) {
        return request.get('api/exam.countdown.countdown/detail', {
            params: params
        }).then(res => {
            return res
        })
    },
    
    // 创建倒计时
    /**
     * @description 创建倒计时
     * @return { Promise }
     */
    apiCreateCountdown(params) {
        return request.post('api/exam.countdown.countdown/create', params).then(res => {
            return res
        })
    },
    
    // 更新倒计时
    /**
     * @description 更新倒计时
     * @return { Promise }
     */
    apiUpdateCountdown(params) {
        return request.post('api/exam.countdown.countdown/update', params).then(res => {
            return res
        })
    },
    
    // 删除倒计时
    /**
     * @description 删除倒计时
     * @return { Promise }
     */
    apiDeleteCountdown(params) {
        return request.post('api/exam.countdown.countdown/delete', params).then(res => {
            return res
        })
    },
    
    // 关注/取消关注倒计时（同时处理微信小程序订阅消息）
    /**
     * @description 关注/取消关注倒计时，并处理微信小程序订阅消息
     * @param {Object} params - 请求参数
     * @param {number} params.id - 倒计时ID
     * @param {number} params.subscribe_status - 订阅状态：1-订阅，0-取消订阅
     * @return { Promise }
     */
    apiFollowCountdown(params) {
        return request.post('api/exam.countdown.countdown/follow', params).then(res => {
            return res
        })
    },
    
    // 订阅/取消订阅微信小程序消息（与关注功能合并处理）
    /**
     * @description 订阅/取消订阅微信小程序消息，与关注功能合并处理
     * @param {Object} params - 请求参数
     * @param {number} params.id - 倒计时ID
     * @param {number} params.subscribe_status - 订阅状态：1-订阅，0-取消订阅
     * @return { Promise }
     */
    apiSubscribeCountdown(params) {
        return request.post('api/exam.countdown.countdown/follow', params).then(res => {
            return res
        })
    },
    
    // 获取用户关注的倒计时列表
    /**
     * @description 获取用户关注的倒计时列表
     * @return { Promise }
     */
    apiGetFollowedCountdowns(params) {
        return request.get('api/exam.countdown.countdown/followedList', {
            params: params
        }).then(res => {
            return res
        })
    },
    
    // 获取用户创建的倒计时列表
    /**
     * @description 获取用户创建的倒计时列表
     * @return { Promise }
     */
    apiGetCreatedCountdowns(params) {
        return request.get('api/exam.countdown.countdown/createdList', {
            params: params
        }).then(res => {
            return res
        })
    },
    
    // 获取随机名人名言
    /**
     * @description 获取随机名人名言
     * @return { Promise }
     */
    apiGetRandomQuote(params) {
        return request.get('api/exam.countdown.celebrity_quote/random', {
            params: params
        }).then(res => {
            return res
        })
    },
    
    // 获取名人名言列表
    /**
     * @description 获取名人名言列表
     * @return { Promise }
     */
    apiGetQuoteList(params) {
        return request.get('api/exam.countdown.celebrity_quote/lists', {
            params: params
        }).then(res => {
            return res
        })
    },
}
