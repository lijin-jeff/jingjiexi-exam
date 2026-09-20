import request from "@/util/request"

export default {
	apiCreateRechargeOrder(params) {// 充值订单创建
		return request.post('api/recharge/recharge', params).then(res => {
			return res
		})
	},
	apiPayApply(params){// 订单支付申请
		return request.post('api/pay/prepay', params).then(res => {
			return res
		})
	},
	apiPayStatus(params) {// 查询订单状态
		return request.get('api/pay/payStatus', {params: params}).then(res => {
			return res
		})
	},
	apiOrderList(params) {// 订单列表
		return request.get('api/recharge/lists', {params: params}).then(res => {
			return res
		})
	}
}