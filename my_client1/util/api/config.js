import request from "@/util/request"

export default {
	/**
	 * 获取首页导航菜单
	 * @param {Object} params
	 */
	getHomeMenuList(params) {
		return request.get("index/index/homeMenu", {
			params: params,
		}).then(res => {
			return res
		})
	},

	/**
	 * 获取主页航菜单
	 * @param {Object} params
	 */
	getMainMenuList(params) {
		return request.get("index/index/homeMainMenu", {
			params: params,
		}).then(res => {
			return res
		})
	},

	/**
	 * 获取主页商业航菜单
	 * @param {Object} params
	 */
	homeBusinessMenu(params) {
		return request.get("index/index/homeBusinessMenu", {
			params: params,
		}).then(res => {
			return res
		})
	},

	/**
	 * 获取主页项目案例展示
	 * @param {Object} params
	 */
	homeProjectMenu(params) {
		return request.get("index/index/homeProjectMenu", {
			params: params,
		}).then(res => {
			return res
		})
	},

	/**
	 * 首页历史数据
	 * @param {Object} params
	 */
	getMainData(params) {
		return request.get("index/index/homeMainData", {
			params: params,
		}).then(res => {
			return res
		})
	},

	/**
	 * 获取页面图片配置
	 */
	getImageConfig() {
		return request.get('index/index/imageConfig').then(res => {
			return res
		})
	},

	/**
	 * 获取用户专业配置列表
	 */
	getUserProfessionList() {
		return request.get("config/profession/getList", {}).then(res => {
			return res
		})
	},

	/**
	 * 获取首页公告列表
	 * @param {Object} params
	 */
	getNoticeList(params) {
		return request.get("getNoticeList", {
			params: params,
		}).then(res => {
			return res
		})
	},

	/**
	 * 获取开发状态配置
	 */
	getDevState() {
		return request.get("getDevState").then(res => {
			return res
		})
	},

	/**
	 * 获取首页顶部banner
	 * @param {Object} params
	 */
	getBannerList(params) {
		return request.get("index/index/banner", {
			params: params,
		}).then(res => {
			return res
		})
	},

	/**
	 * 获取配置信息
	 */
	getSysConfig() {
		return request.get('api/index/config').then(res => {
			return res
		})
	},

	/**
	 * 发送手机验证码
	 * @param {Object} params
	 */
	sendSmsCodeAuth(params) {
		return request.post('index/message/sendSmsCode', params).then(res => {
			return res
		})
	},

	/**
	 * @description 获取排行榜设置
	 * @return { Promise }
	 */
	getRankSettings() {
		return request.get('api/index/rankSettings').then(res => {
			return res
		})
	},

	/**
	 * @description 获取其他设置
	 * @return { Promise }
	 */
	getOtherSettings() {
		return request.get('api/index/otherSettings').then(res => {
			return res
		})
	},

	/**
	 * @description 获取积分设置
	 * @return { Promise }
	 */
	getIntegralSettings() {
		return request.get('api/index/integralSettings').then(res => {
			return res
		})
	},

	/**
	 * @description 获取网站设置
	 * @return { Promise }
	 */
	getWebsiteSettings() {
		return request.get('api/index/getWebsite').then(res => {
			return res
		})
	},

	/**
	 * @description 获取字典数据
	 * @return { Promise }
	 */
	getDictData(params) {
		return request.get('api/config/dictData', {
			params: params
		}).then(res => {
			return res
		})
	},
	
	/**
	 * 获取订阅模板ID列表
	 * @returns {Promise} 订阅模板ID列表Promise
	 */
	getSubscribeTemplates() {
		// 尝试从缓存获取
		const cacheKey = 'subscribe_templates';
		const cachedData = uni.getStorageSync(cacheKey);
		const cacheTime = uni.getStorageSync(cacheKey + '_time');
		const now = Date.now();
		
		// 缓存有效期5分钟
		if (cachedData && cacheTime && (now - cacheTime < 5 * 60 * 1000)) {
			// 返回缓存数据
			return Promise.resolve({
				code: 100,
				data: cachedData
			});
		}
		
		// 调用API获取新数据
		return request.get('api/index/subscribeTemplates').then(res => {
			// 缓存数据
			if (res.code === 100 && res.data) {
				uni.setStorageSync(cacheKey, res.data);
				uni.setStorageSync(cacheKey + '_time', now);
			}
			return res;
		}).catch(err => {
			console.error('获取订阅模板ID失败:', err);
			// 如果API请求失败，返回缓存数据（如果有）
			if (cachedData) {
				return {
					code: 100,
					data: cachedData,
					msg: '使用缓存数据'
				};
			}
			// 没有缓存数据，返回错误
			return Promise.reject(err);
		});
	},
	
	/**
	 * 模板消息订阅函数
	 * @param {Object} params - 请求参数
	 * @param {string} params.code - 模板代码
	 * @param {string} [params.type] - 订阅类型：'wechat_mini'（微信小程序）或 'wechat_mp'（微信公众号）
	 * @param {string} [params.openid] - 用户openid，某些场景下需要
	 * @returns {Promise} 订阅结果Promise
	 */
	templateSubscribe(params) {
		return new Promise((resolve, reject) => {
			// 参数验证
			if (!params || !params.code) {
				const error = new Error('参数错误：缺少必要的模板代码');
				console.error('templateSubscribe 失败:', error);
				reject(error);
				return;
			}

			// 请求配置
			const requestParams = {
				code: params.code,
				...params
			};

			// 发送请求
			request.post("api/index/templateSubscribe", requestParams)
				.then(res => {
					// 标准化响应处理
					if (res && res.code === 100) {
						resolve(res);
					} else {
						const errorMsg = res && res.msg ? res.msg : '订阅失败';
						const error = new Error(errorMsg);
						console.error('templateSubscribe API返回失败:', error, res);
						reject(error);
					}
				})
				.catch(error => {
					console.error('templateSubscribe 请求异常:', error);
					// 包装错误对象，添加更多上下文信息
					const wrappedError = new Error('网络请求失败，请检查网络连接');
					wrappedError.originalError = error;
					reject(wrappedError);
				});
		});
	}
}