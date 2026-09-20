import request from "@/util/request"

export default {
	apiShopPrizeList(params) {
		return request.get('index/shop.prize/getList', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiShopPrizeContent(params) {
		return request.get('index/shop.prize/getDetail', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiShopPrizeExchange(params) {
		return request.post('index/shop.prize/prizeExchange', params).then(res => {
			return res
		})
	},
	apiShopPrizeExchangeList(params) {
		return request.get('index/shop.prize/prizeExchangeList', {
			params: params
		}).then(res => {
			return res
		})
	},
}