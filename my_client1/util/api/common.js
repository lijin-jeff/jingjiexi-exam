import request from "@/util/request"

export default {
	apiBannerList(params) { // 获取banner图
		return request.get('index/index/banner', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiMenuList(params) { // 获取菜单图
		return request.get('index/index/menuList', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiPolicyContent(params) { // 获取政策协议
		console.log(params)
		return request.get("api/index/policy", {
			params: params
		}).then(res => {
			return res
		})
	},
	apiUploadImage(fileUrl) { // 图片上传
		return request.upload('api/upload/image', {
			fileType: "image",
			name: "file",
			filePath: fileUrl,
		}).then(res => {
			return res
		})
	},
	apiImageConfig(params) { // 图片配置列表
		return request.get('api/index/imageConfig', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiDataSearch(params) { // 数据全局搜索
		return request.get('api/index/dataQuery', {
			params: params
		}).then(res => {
			return res
		})
	},
	

}