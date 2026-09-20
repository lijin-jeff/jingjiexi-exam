import request from "@/util/request"

export default {
	apiDocumentGroupList(params) {
		return request.get("index/article/documentGroup", {
			params: params
		}).then(res => {
			return res
		})
	},
	apiDocumentContent(params) {
		return request.get("index/article/documentContent", {
			params: params
		}).then(res => {
			return res
		})
	}
}