import request from '@/util/request'

export default {
	apiAccountRegister(params) { // 账号注册
		return request.post('api/login/register', params).then(res => {
			return res
		})
	},
	apiSmsSend(params) { // 短信验证码发送
		return request.post('api/sms/sendCode', params).then(res => {
			return res
		})
	},
	apiAccountLogin(params) { // 账号登录
		return request.post('api/login/account', params).then(res => {
			return res
		})
	},
	apiMnpLogin(params) {// 小程序一键授权登录
		return request.post('api/login/mnpLogin', params).then(res => {
			return res
		})
	},
	apiMnpSilentLogin(params) {// 小程序静默登录
		return request.post('api/login/silentLogin', params).then(res => {
			return res
		})
	},
	apiMnpAuthBind(params) { // 绑定微信小程序
		console.log('params', params);
		return request.post('api/login/mnpAuthBind', params).then(res => {
			return res
		})
	},
	apiUserInfo() { // 获取个人信息
		return request.get('api/user/info').then(res => {
			return res
		})
	},
	apiUpdateUserInfo(params) { // 更新用户信息
		return request.post('api/user/updateUser', params).then(res => {
			return res
		})
	},
	apiResetPassword(params) {// 重置密码
		return request.post('api/user/resetPassword', params).then(res => {
			return res
		})
	},
	//更新用户消灭做题次数
	apiUpdateUserEliminateThreshold(params) {
		return request.post('api/user/updateEliminateThreshold', params).then(res => {
			return res
		})
	},

}