import request from "@/util/request"

export default {
	/**
	 * 用户积分明细
	 * @param {Object} params - 查询参数
	 * @returns {Promise} - 请求结果
	 */
	 apiUserIntegralList(params) {
			return  request.get('api/integral/integralList', {  params: params }).catch(error => {
			console.error('获取积分明细失败:', error)
			return { code: 0, msg: '获取积分明细失败', data: null }
		})
	
	},
	
	/**
	 * 用户积分信息
	 * @returns {Promise} - 请求结果
	 */
	 apiUserIntegral() {
			return  request.get('api/integral/userIntegral').catch(error => {	
			console.error('获取用户积分失败:', error)
			return { code: 0, msg: '获取用户积分失败', data: null }
		})	
	},
		
	/**
	 * 用户积分排行榜
	 * @returns {Promise} - 请求结果
	 */
	 apiUserIntegralRanking() {
			return  request.get('api/integral/userIntegralRanking').catch(error => {	
			console.error('获取用户积分排行榜失败:', error)
			return { code: 0, msg: '获取用户积分排行榜失败', data: null }
		})	
	}
	
}