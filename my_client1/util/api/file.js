import request from "@/util/request"

/**
 * 上传单个图片资源
 * @param {Object} fileUrl
 */
export function uploadImage(fileUrl) {
	return request.upload('index/upload/imageUpload', {
		fileType: "image",
		name: "file",
		filePath: fileUrl,
	}).then(res => {
		return res
	})
}


//==========================================资源管理开始==========================================
/**
 * 获取文件资源全量分类
 */
export function getResourceCategoryList() {
	return request.get("index/resource.resource/resourceCategoryList").then(res => {
		return res
	})
}


/**
 * 首页资源列表
 * @param {Object} params
 */
export function homeResource(params) {
	return request.get('index/resource.resource/homeResource', {
		params: params
	}).then(res => {
		return res
	})
}

/**
 * 推荐和热门资源列表
 */
export function hotAndRecommendCategory() {
	return request.get('index/resource.resource/hotAndRecommendCategory').then(res => {
		return res
	})
}

/**
 * 获取首页推荐分类列表
 */
export function recommendCategory() {
	return request.get("index/resource.resource/homeRecommendCategory").then(res => {
		return res
	})
}

/**
 * 获取首页热门分类列表
 */
export function homeHotCategory() {
	return request.get("index/resource.resource/homeHotCategory").then(res => {
		return res
	})
}


/**
 * 获取文件资源列表
 * @param {Object} params
 */
export function getResourceList(params) {
	return request.get("index/resource.resource/resourceList", {
		params: params
	}).then(res => {
		return res
	})
}

/**
 * 获取文件资源查询条件
 * @param {Object} params
 */
export function getResourceSearchWhere(params) {
	return request.get("index/resource.resource/searchWhere", {
		params: params
	}).then(res => {
		return res
	})
}

/**
 * 获取资源详情内容
 * @param {Object} params
 */
export function getDownloadUrl(params) {
	return request.get("index/resource.resource/downloadSource", {
		params: params
	}).then(res => {
		return res
	})
}

/**
 * 获取资源附件链接地址
 * @param {Object} params
 */
export function getShareUrl(params) {
	return request.get('index/resource.resource/shareUrl', {
		params: params
	}).then(res => {
		return res
	})
}

/**
 * 提交资源或点赞
 * @param {Object} params
 */
export function submitSourceCollect(params) {
	return request.post("index/resource.resource/resourceClickOrCollect", params).then(res => {
		return res
	})
}

/**
 * 资源收藏或点赞列表
 * @param {Object} params
 */
export function collectOrClickList(params) {
	return request.get('index/resource.resource/collectOrClickList', {
		params: params
	}).then(res => {
		return res
	})
}
//==========================================资源管理结束==========================================