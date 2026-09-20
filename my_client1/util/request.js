import Request from '@/tuniao-ui/libs/luch-request'
import { getToken } from './userStore'

const baseUrl = 'https://cx4vmw1d.allpp.cn/'
const http = new Request({
	baseURL: baseUrl,
	timeout: 300000,
})

http.interceptors.request.use((config) => {
	let sysInfo = {}
	uni.getSystemInfo({
		success(res) {
			sysInfo = res
		}
	})
	config.header = {
		Authorization: 'Bearer ' + getToken(),
		PlatformInfo: encodeURI(JSON.stringify(sysInfo)),
		'User-Code': 'Mg==',
		tenantId: 1,
	}
	return config
})
http.interceptors.response.use((response) => {
	return response.data
}, (response) => {
	if (response.statusCode === 200 && response.code === 101) {
		uni.showToast({
			title: response.data.msg || '请求失败',
			icon: "none",
			duration: 3000,
		})
	} else if (response.statusCode === 500) {
		uni.showToast({
			title: response.data.msg || '服务器错误',
			icon: "none",
			duration: 3000,
		})
	} else if (response.statusCode === 400) {
		uni.showToast({
			title: response.data.msg,
			icon: "none",
			duration: 3000,
		})
	} else if (response.statusCode === 404) {
		uni.showToast({
			title: response.data.msg,
			icon: "none",
			duration: 3000,
		})
	} else if (response.statusCode === 422) {
		uni.showToast({
			title: response.data.msg,
			icon: "none",
			duration: 3000,
		})
	} else if (response.statusCode === 401) {
		// 检查页面栈，避免重复跳转
		const pages = getCurrentPages();
		const loginPageIndex = pages.findIndex(page => page.route === 'subpages/user/login');
		if (loginPageIndex === -1) {
			uni.redirectTo({
				url: '/subpages/user/login'
			});
		}
	} else if (response.statusCode === 302) {
		uni.navigateTo({
			url: response.data.data.url,
			success() {
				uni.showToast({
					title: response.data.msg,
					icon: "none",
					duration: 5000,
					mask: true
				})
			}
		})
	} else if (response.statusCode === 403) {
		uni.showToast({
			title: response.data.msg,
			icon: "none",
			duration: 3000,
		})

	} else if (response.errMsg === "request:fail timeout") {
		uni.showToast({
			title: "网络请求超时",
			icon: "none",
			duration: 3000,
		})
	} else {
		uni.showToast({
			title: response.errMsg,
			icon: "none",
			duration: 3000,
		})
	}
	// 正确处理错误，返回rejected promise
	return Promise.reject(response)
})
export default http