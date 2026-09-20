import request from "@/util/request"

export default {

	apiHelpList() {
        // 帮助文档列表
        return request.get('api/exam.help/helpList').catch(error => {
            console.error('获取帮助文档列表失败:', error)
            // 可以返回一个默认值或重新抛出错误
            return { code: -1, msg: '获取帮助文档列表失败', data: [] }
        })
    },
    apiHelpContent(params) {
        // 帮助文档内容
        if (!params || !params.id) {
            return Promise.reject(new Error('缺少文档ID参数'))
        }
        return request.get('api/exam.help/helpContent', {
            params: params
        }).catch(error => {
            console.error('获取帮助文档内容失败:', error)
            // 可以返回一个默认值或重新抛出错误
            return { code: -1, msg: '获取帮助文档内容失败', data: {} }
        })
    }

}