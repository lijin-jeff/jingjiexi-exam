import request from "@/util/request"

export default {
	apiArticleList(params) {// 图文资讯列表
		return request.get('api/article/lists', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiArticleContent(params) {// 文章详情
		return request.get("api/article/detail", {
			params: params
		}).then(res => {
			return res
		})
	},
	apiArticleCateList(params) {// 图文资讯分类列表
		return request.get("api/article/cate", {
			params: params
		}).then(res => {
			return res
		})
	},	
	apiArticleAddCollect(params) {// 文章添加收藏
		return request.post("api/article/addCollect", params).then(res => {
			return res
		})
	},
	apiArticleCancelCollect(params) {// 文章取消收藏
		return request.post("api/article/cancelCollect", params).then(res => {
			return res
		})
	}

}