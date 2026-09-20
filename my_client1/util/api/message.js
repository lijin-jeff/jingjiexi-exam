import request from "@/util/request"

export default {
	/**
	 * 统一订阅消息函数
	 * @param {Object} params - 请求参数
	 * @param {string} params.type - 订阅类型：'countdown'（倒计时）, 'version_update'（版本更新）
	 * @param {number} params.related_id - 关联ID，如倒计时ID
	 * @param {number} [params.subscribe_status] - 订阅状态：1-订阅 0-取消订阅
	 * @param {Object} [params.subscribe_results] - 订阅结果，包含模板ID和订阅状态
	 * @param {Object} [params.template_data] - 模板数据
	 * @returns {Promise} 订阅结果Promise
	 */
	apiSubscribe(params) {
		return new Promise((resolve, reject) => {
			try {
				// 参数验证
				if (!params || typeof params !== 'object') {
					throw new Error('参数错误：params必须是对象');
				}
				
				if (!params.type) {
					throw new Error('参数错误：缺少必要的订阅类型');
				}
				
				// 直接调用后端统一订阅API接口
			request.post("api/subscribe", params)
				.then(res => {
					// 标准化响应处理
					if (res && res.code === 1) {
						resolve(res);
					} else {
						const errorMsg = res && res.msg ? res.msg : '订阅失败';
						console.error('apiSubscribe API返回失败:', errorMsg, res);
						reject({
							code: 0,
							msg: errorMsg,
							data: res
						});
					}
				})
					.catch(error => {
						console.error('apiSubscribe 请求异常:', error);
						reject({
							code: 0,
							msg: '网络请求失败，请检查网络连接',
							error: error
						});
					});
			} catch (error) {
				console.error('apiSubscribe 执行失败:', error);
				reject({
					code: 0,
					msg: '订阅功能执行失败',
					error: error
				});
			}
		});
	},
	
	/**
	 * 获取订阅状态
	 * @param {Object} params - 请求参数
	 * @param {string} params.type - 订阅类型：'countdown'（倒计时）, 'version_update'（版本更新）
	 * @param {number} params.related_id - 关联ID，如倒计时ID
	 * @returns {Promise} 订阅状态Promise
	 */
	apiGetSubscribeStatus(params) {
		return new Promise((resolve, reject) => {
			try {
				// 参数验证
				if (!params || typeof params !== 'object') {
					throw new Error('参数错误：params必须是对象');
				}
				
				if (!params.type) {
					throw new Error('参数错误：缺少必要的订阅类型');
				}
				
				if (!params.related_id) {
					throw new Error('参数错误：缺少必要的关联ID');
				}
				
				// 直接调用后端获取订阅状态API接口
				request.get("api/subscribe/status", params)
					.then(res => {
						// 标准化响应处理
						if (res && res.code === 1) {
							resolve(res);
						} else {
							const errorMsg = res && res.msg ? res.msg : '获取订阅状态失败';
							console.error('apiGetSubscribeStatus API返回失败:', errorMsg, res);
							reject({
								code: 0,
								msg: errorMsg,
								data: res
							});
						}
					})
					.catch(error => {
						console.error('apiGetSubscribeStatus 请求异常:', error);
						reject({
							code: 0,
							msg: '网络请求失败，请检查网络连接',
							error: error
						});
					});
			} catch (error) {
				console.error('apiGetSubscribeStatus 执行失败:', error);
				reject({
					code: 0,
					msg: '获取订阅状态失败',
					error: error
				});
			}
		});
	},
	
	/**
	 * 记录订阅授权状态
	 * @param {Object} params - 请求参数
	 * @param {string} params.template_id - 模板ID
	 * @param {number} params.subscribe_time - 订阅时间戳
	 * @param {string} params.type - 订阅类型：'countdown'（倒计时）, 'version_update'（版本更新）, 'exam'（考试）
	 * @param {number} params.related_id - 关联ID
	 * @returns {Promise} 记录结果Promise
	 */
	apiRecordSubscribe(params) {
		return new Promise((resolve, reject) => {
			try {
				// 参数验证
				if (!params || typeof params !== 'object') {
					throw new Error('参数错误：params必须是对象');
				}
				
				if (!params.template_id) {
					throw new Error('参数错误：缺少必要的模板ID');
				}
				
				if (!params.subscribe_time) {
					throw new Error('参数错误：缺少必要的订阅时间');
				}
				
				if (!params.type) {
					throw new Error('参数错误：缺少必要的订阅类型');
				}
				
				if (!params.related_id) {
					throw new Error('参数错误：缺少必要的关联ID');
				}
				
				// 移除openid依赖，统一从后端获取
				delete params.openid;
				
				// 直接调用后端记录订阅授权状态API接口
				request.post("api/userSubscribe/recordSubscribe", params)
					.then(res => {
						// 标准化响应处理
						if (res && res.code === 1) {
							resolve(res);
						} else {
							const errorMsg = res && res.msg ? res.msg : '记录订阅授权状态失败';
							console.error('apiRecordSubscribe API返回失败:', errorMsg, res);
							// 这里只记录错误，不影响主流程，所以仍然resolve
							resolve(res);
						}
					})
					.catch(error => {
						console.error('apiRecordSubscribe 请求异常:', error);
						// 这里只记录错误，不影响主流程，所以仍然resolve
						resolve({code: 0, msg: '记录订阅授权状态失败', error: error});
					});
			} catch (error) {
				console.error('apiRecordSubscribe 执行失败:', error);
				// 这里只记录错误，不影响主流程，所以仍然resolve
				resolve({code: 0, msg: '记录订阅授权状态失败', error: error});
			}
		});
	},
	
	/**
	 * 模板消息订阅函数
	 * @param {Object} params - 请求参数
	 * @param {string} params.code - 模板代码
	 * @param {string} [params.type] - 订阅类型：'wechat_mini'（微信小程序）
	 * @param {string} [params.openid] - 用户openid，某些场景下需要
	 * @returns {Promise} 订阅结果Promise
	 */
	apiTemplateSubscribe(params) {
		return new Promise((resolve, reject) => {
			try {
				// 参数验证
				if (!params || typeof params !== 'object') {
					throw new Error('参数错误：params必须是对象');
				}
				
				if (!params.code) {
					throw new Error('参数错误：缺少必要的模板代码');
				}
				
				// 准备请求参数
				const requestParams = {
					code: params.code,
					...params
				};
				
				// 直接调用后端API接口，而不是尝试使用云函数模块
				request.post("api/index/templateSubscribe", requestParams)
					.then(res => {
						// 标准化响应处理
						if (res && res.code === 1) {
							resolve(res);
						} else {
							const errorMsg = res && res.msg ? res.msg : '订阅失败';
							console.error('apiTemplateSubscribe API返回失败:', errorMsg, res);
							reject({
								code: 0,
								msg: errorMsg,
								data: res
							});
						}
					})
					.catch(error => {
						console.error('apiTemplateSubscribe 请求异常:', error);
						reject({
							code: 0,
							msg: '网络请求失败，请检查网络连接',
							error: error
						});
					});
			} catch (error) {
				console.error('apiTemplateSubscribe 执行失败:', error);
				reject({
					code: 0,
					msg: '订阅功能执行失败',
					error: error
				});
			}
		});
	},

	/**
	 * 获取消息列表
	 * @param {Object} params - 请求参数
	 * @param {number} [params.type] - 消息类型: 1-评论回复 2-评论点赞 3-评论赞赏 4-系统通知
	 * @param {number} [params.page_no=1] - 页码
	 * @param {number} [params.page_size=20] - 每页数量
	 * @returns {Promise}
	 */
	apiMessageList(params = {}) {
		return  request.get('api/message/lists', {  params: params }).catch(error => {
			console.error('获取消息列表失败:', error)
			return { code: 0, msg: '获取积消息列表失败', data: null }
		})
	
	},

	/**
	 * 获取未读消息数量
	 * @returns {Promise}
	 */
	apiMessageUnreadCount() {
		return request.get('api/message/unreadCount')
	},

	/**
	 * 标记消息为已读
	 * @param {Object} params - 请求参数
	 * @param {Array|null} params.message_ids - 消息ID数组，null表示全部已读
	 * @returns {Promise}
	 */
	apiMessageMarkRead(params = {}) {
		return request.post('api/message/markRead', params)
	},

	/**
	 * 删除消息
	 * @param {Object} params - 请求参数
	 * @param {Array} params.message_ids - 消息ID数组
	 * @returns {Promise}
	 */
	apiMessageDelete(params) {
		return request.post('api/message/delete', params)
	},

	/**
	 * 评论赞赏
	 * @param {Object} params - 请求参数
	 * @param {number} params.comment_id - 评论ID
	 * @param {number} params.integral - 赞赏积分数量
	 * @param {string} [params.remark] - 赞赏留言
	 * @returns {Promise}
	 */
	apiCommentReward(params) {
		return request.post('api/message/rewardComment', params)
	}
}