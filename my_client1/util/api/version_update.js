import request from "@/util/request"

export default {

	apiVersionUpdateList() {
        // 版本更新列表
        return request.get('api/exam.version_update/versionUpdateList').catch(error => {
            console.error('获取版本更新列表失败:', error)
            // 可以返回一个默认值或重新抛出错误
            return { code: -1, msg: '获取版本更新列表失败', data: [] }
        })
    },
    apiVersionUpdateContent(params) {
        // 版本更新内容
        if (!params || !params.id) {
            return Promise.reject(new Error('缺少文档ID参数'))
        }
        return request.get('api/exam.version_update/versionUpdateContent', {
            params: params
        }).catch(error => {
            console.error('获取版本更新内容失败:', error)
            // 可以返回一个默认值或重新抛出错误
            return { code: -1, msg: '获取版本更新内容失败', data: {} }
        })
    },
    apiVersionUpdateSubscribeStatus() {
        return request.get('api/exam.version_update/versionUpdateSubscribeStatus').catch(error => {
            console.error('获取版本更新订阅状态失败:', error)
            // 可以返回一个默认值或重新抛出错误
            return { code: -1, msg: '获取版本更新订阅状态失败', data: 0 }
        })
    }
}