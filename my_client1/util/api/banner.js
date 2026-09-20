import request from "@/util/request"

export default {
	apiBannerList(params) {
		return request.get('index/index/menuList', {
			params: params
		}).then(res => {
			return res
		})
	}
}