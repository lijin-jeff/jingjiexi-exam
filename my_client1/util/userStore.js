/**
 * 用户信息统一存储管理工具
 * 提供统一的用户信息存储、获取和降级处理
 */

// Token配置常量（与后端保持一致）
const TOKEN_CONFIG = {
	EXPIRE_DURATION: 30 * 24 * 60 * 60 * 1000,  // Token有效期：30天（毫秒）
	BE_EXPIRE_DURATION: 7 * 24 * 60 * 60 * 1000,  // Token快过期前自动续期时间：7天（毫秒）
}

/**
 * 统一保存用户信息
 * @param {Object} userInfo - 用户信息对象
 * @returns {boolean} - 是否保存成功
 */
export function saveUserInfo(userInfo) {
	try {
		
		if (!userInfo || typeof userInfo !== 'object') {
			console.error('[saveUserInfo] 保存失败：无效的用户信息对象')
			return false
		}
		
		// 处理头像URL：自动补全协议
		if (userInfo.avatar && !userInfo.avatar.includes('://') && !userInfo.avatar.startsWith('data:')) {
			const oldAvatar = userInfo.avatar
			userInfo.avatar = 'https://' + userInfo.avatar
		}
		
		// 处理积分信息：优先使用直接返回的integral字段，其次从integral_info.total_integral获取
	let integral = userInfo.integral || 0;
	if (userInfo.integral_info && userInfo.integral_info.total_integral) {
		// 移除千位分隔符并转换为数字
		integral = parseFloat(userInfo.integral_info.total_integral.replace(/,/g, '')) || 0;
	}
		
		// 确保integral、vip_state、vip_endTime和is_admin字段存在，避免显示为0或undefined
		const userInfoWithDefaults = {
			...userInfo,
			integral: integral,
			vip_state: userInfo.vip_state || 0,
			vip_endTime: userInfo.vip_endTime || null,
			is_admin: userInfo.is_admin || 0
		};
		
		// 1. 保存到本地存储（完整对象）
		uni.setStorageSync('userInfo', userInfoWithDefaults)
		
		// 2. 保存到 globalData
		try {
			const app = getApp()
			if (app && app.globalData) {
				app.globalData.userInfo = userInfoWithDefaults
			}
		} catch (error) {
			console.warn('[saveUserInfo] 保存到 globalData 失败:', error)
		}
		
		// 3. 保存登录凭证（包含过期时间）
		if (userInfoWithDefaults.token) {
			const loginData = {
				token: userInfoWithDefaults.token,
				expireTime: Date.now() + TOKEN_CONFIG.EXPIRE_DURATION,
				createTime: Date.now()
			}
			uni.setStorageSync('login', loginData)
		}
		
		// 4. 验证保存结果
		const savedUserInfo = uni.getStorageSync('userInfo')
		const savedToken = uni.getStorageSync('login')
		
		return true
	} catch (error) {
		console.error('[saveUserInfo] 保存用户信息失败:', error)
		return false
	}
}

/**
 * 获取用户信息（多层降级策略）
 * @param {boolean} forceRefresh - 是否强制从 API 刷新
 * @returns {Promise<Object>} - 用户信息对象
 */
export async function getUserInfo(forceRefresh = false) {
	try {
		// 如果不强制刷新，先尝试从缓存获取
		if (!forceRefresh) {
			// 1. 优先从本地存储获取
			let userInfo = uni.getStorageSync('userInfo')
			if (userInfo && Object.keys(userInfo).length > 0) {
				// 处理头像URL
				if (userInfo.avatar && !userInfo.avatar.includes('://') && !userInfo.avatar.startsWith('data:')) {
					userInfo.avatar = 'https://' + userInfo.avatar
				}
				return userInfo
			}
			
			
			// 2. 尝试从 globalData 获取
			try {
				const app = getApp()
				if (app && app.globalData && app.globalData.userInfo && 
					Object.keys(app.globalData.userInfo).length > 0) {
					userInfo = app.globalData.userInfo
					// 处理头像URL
					if (userInfo.avatar && !userInfo.avatar.includes('://') && !userInfo.avatar.startsWith('data:')) {
						userInfo.avatar = 'https://' + userInfo.avatar
					}
					// 同步到本地存储
					uni.setStorageSync('userInfo', userInfo)
					return userInfo
				}
			} catch (error) {
				console.warn('从 globalData 获取用户信息失败:', error)
			}
		}
		
		// 3. 检查是否有登录 token，如果没有 token 则不调用 API
		const token = uni.getStorageSync('login')
		if (!token) {
			console.log('[用户信息] 未登录，返回空对象')
			return {}
		}
		
		// 4. 检查上次刷新时间，避免过于频繁的 API 请求（60秒内不重复刷新）
		// 注意：如果是强制刷新，忽略时间限制，直接从服务器获取
		if (!forceRefresh) {
			const lastRefreshTime = uni.getStorageSync('last_userinfo_refresh_time') || 0
			const currentTime = Date.now()
			const timeDiff = currentTime - lastRefreshTime
			
			// 如果距离上次刷新不足60秒，且本地有用户信息，则使用本地缓存
			if (timeDiff < 60 * 1000) {
				const localUserInfo = uni.getStorageSync('userInfo')
				if (localUserInfo && Object.keys(localUserInfo).length > 0) {
					return localUserInfo
				}
			}
		}
		
		// 5. 从 API 重新获取
    const { default: userApi } = await import('./api/user.js')
    const res = await userApi.apiUserInfo()
    
    if (res && res.data) {
      // 处理头像URL
      if (res.data.avatar && !res.data.avatar.includes('://') && !res.data.avatar.startsWith('data:')) {
        res.data.avatar = 'https://' + res.data.avatar
      }
      
      // 确保integral和vip_state字段存在，避免显示为0或undefined
      // 处理积分信息：优先使用服务器直接返回的integral字段，其次从integral_info.total_integral获取
      let integral = res.data.integral || 0;
      if (res.data.integral_info && res.data.integral_info.total_integral) {
        // 移除千位分隔符并转换为数字
        integral = parseFloat(res.data.integral_info.total_integral.replace(/,/g, '')) || 0;
      }
      
      const userInfoWithDefaults = {
        ...res.data,
        integral: integral,
        vip_state: res.data.vip_state || 0,
        vip_endTime: res.data.vip_endTime || null,
        is_admin: res.data.is_admin || 0
      };
      
      // 保存用户信息
      saveUserInfo(userInfoWithDefaults)
      // 记录刷新时间
      uni.setStorageSync('last_userinfo_refresh_time', Date.now())
      return userInfoWithDefaults
    } else {
      console.error('API 返回数据无效:', res)
      // 尝试从本地存储获取用户信息
      const localUserInfo = uni.getStorageSync('userInfo')
      if (localUserInfo && Object.keys(localUserInfo).length > 0) {
        return localUserInfo
      }
      return {}
    }
	} catch (error) {
		console.error('获取用户信息失败:', error)
		// 最后的降级：尝试从本地存储获取用户信息，如果没有则返回空对象
		const localUserInfo = uni.getStorageSync('userInfo')
		if (localUserInfo && Object.keys(localUserInfo).length > 0) {
			return localUserInfo
		}
		return {}
	}
}

/**
 * 检查用户是否已登录
 * @returns {boolean} - 是否已登录
 */
export function isUserLoggedIn() {
	try {
		const loginData = uni.getStorageSync('login')
		
		// 兼容旧版本：如果login是字符串，说明是旧版本存储，需要迁移
		if (typeof loginData === 'string') {
			// 旧版本Token，没有过期时间，视为有效（兼容处理）
			return !!(loginData && loginData.trim() !== '')
		}
		
		// 新版本：检查Token对象
		if (!loginData || !loginData.token) {
			return false
		}
		
		// 检查Token是否过期
		const currentTime = Date.now()
		if (loginData.expireTime && currentTime > loginData.expireTime) {
			// Token已过期，清除登录信息
			console.log('[isUserLoggedIn] Token已过期，清除登录信息')
			clearUserInfo()
			return false
		}
		
		return true
	} catch (error) {
		console.error('检查登录状态失败:', error)
		return false
	}
}

/**
 * 检查Token是否即将过期（需要刷新）
 * @returns {boolean} - 是否需要刷新Token
 */
export function isTokenNearExpiry() {
	try {
		const loginData = uni.getStorageSync('login')
		
		if (!loginData || !loginData.token || !loginData.expireTime) {
			return false
		}
		
		const currentTime = Date.now()
		const remainingTime = loginData.expireTime - currentTime
		
		// 如果剩余时间小于自动续期时间，则需要刷新
		return remainingTime < TOKEN_CONFIG.BE_EXPIRE_DURATION
	} catch (error) {
		console.error('检查Token过期状态失败:', error)
		return false
	}
}

/**
 * 获取Token字符串
 * @returns {string|null} - Token字符串
 */
export function getToken() {
	try {
		const loginData = uni.getStorageSync('login')
		if (typeof loginData === 'string') {
			return loginData
		}
		return loginData?.token || null
	} catch (error) {
		console.error('获取Token失败:', error)
		return null
	}
}

/**
 * 检查用户 VIP 状态
 * @returns {Promise<boolean>} - 是否为有效 VIP
 */
export async function checkVipStatus() {
	try {
		// 获取用户信息（自动降级）
		const userInfo = await getUserInfo()
		
		if (!userInfo || Object.keys(userInfo).length === 0) {
			console.warn('用户信息不存在，可能未登录')
			return false
		}
		
		// vip_state === 3 表示会员有效（未过期）
		return userInfo.vip_state === 3
	} catch (error) {
		console.error('检查 VIP 状态失败:', error)
		return false
	}
}

/**
 * 同步检查 VIP 状态（不使用 async）
 * @returns {boolean} - 是否为有效 VIP
 */
export function checkVipStatusSync() {
	try {
		// 1. 优先从本地存储获取
		let userInfo = uni.getStorageSync('userInfo') || {}
		
		// 2. 如果本地存储为空，尝试从 globalData 获取
		if (!userInfo || Object.keys(userInfo).length === 0) {
			try {
				const app = getApp()
				userInfo = app?.globalData?.userInfo || {}
			} catch (error) {
				console.warn('从 globalData 获取用户信息失败:', error)
			}
		}
		
		// 3. 如果还是空，返回 false
		if (!userInfo || Object.keys(userInfo).length === 0) {
			console.warn('用户信息不存在，可能未登录')
			return false
		}
		// vip_state === 3 表示会员有效（未过期）
		return userInfo.vip_state === 3
	} catch (error) {
		console.error('检查 VIP 状态失败:', error)
		return false
	}
}

/**
 * 清除用户信息（登出时调用）
 * @returns {boolean} - 是否清除成功
 */
export function clearUserInfo() {
	try {
		// 1. 清除本地存储
		uni.removeStorageSync('userInfo')
		uni.removeStorageSync('login')
		
		// 2. 清除 globalData
		try {
			const app = getApp()
			if (app && app.globalData) {
				app.globalData.userInfo = {}
			}
		} catch (error) {
			console.warn('清除 globalData 失败:', error)
		}
		return true
	} catch (error) {
		console.error('清除用户信息失败:', error)
		return false
	}
}

/**
 * 跳转到登录页面
 * @param {string} redirectUrl - 登录成功后的跳转地址
 */
export function navigateToLogin(redirectUrl = '') {
	try {
		let url = '/subpages/user/login'
		if (redirectUrl) {
			url += `?redirect=${encodeURIComponent(redirectUrl)}`
		}
		// 检查页面栈，避免重复跳转
		const pages = getCurrentPages();
		const loginPageIndex = pages.findIndex(page => page.route === 'subpages/user/login');
		if (loginPageIndex === -1) {
			uni.redirectTo({ url });
		}
	} catch (error) {
		console.error('跳转登录页失败:', error)
	}
}

/**
 * 确保用户已登录（中间件函数）
 * @param {Function} callback - 登录后执行的回调函数
 * @param {string} redirectUrl - 登录成功后的跳转地址
 * @returns {Promise<boolean>} - 是否已登录
 */
export async function ensureUserLoggedIn(callback, redirectUrl = '') {
	try {
		// 检查是否已登录
		if (!isUserLoggedIn()) {
			console.warn('用户未登录，跳转到登录页')
			navigateToLogin(redirectUrl)
			return false
		}
		
		// 尝试获取用户信息
		const userInfo = await getUserInfo()
		if (!userInfo || Object.keys(userInfo).length === 0) {
			console.warn('获取用户信息失败，跳转到登录页')
			navigateToLogin(redirectUrl)
			return false
		}
		
		// 执行回调
		if (typeof callback === 'function') {
			await callback(userInfo)
		}
		
		return true
	} catch (error) {
		console.error('确保用户登录失败:', error)
		return false
	}
}

export default {
	saveUserInfo,
	getUserInfo,
	isUserLoggedIn,
	isTokenNearExpiry,
	getToken,
	checkVipStatus,
	checkVipStatusSync,
	clearUserInfo,
	navigateToLogin,
	ensureUserLoggedIn,
	TOKEN_CONFIG
}
