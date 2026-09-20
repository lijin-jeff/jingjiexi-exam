<script>
import Vue from 'vue'
import store from '@/store/index.js'
import updateCustomBarInfo from '@/tuniao-ui/libs/function/updateCustomBarInfo.js'
import configApi from '@/util/api/config.js'
import ApiCache from '@/util/apiCache.js'

export default {
  globalData: {
    showAd: false,
    homeUrl: '/pages/home',// 首页默认地址，用于审核期间部分页面做重定向
    defaultAvatar: 'https://datiqiniu.allpp.cn/static/userinfo_avatar.png',
    mainColor: "#42B476",
    mainErrorColor: "#E83A30",
    mobile: '1234567890',//客服手机号，可以后台设置。会被接口数据覆盖
    serviceQrCode: 'https://datiqiniu.allpp.cn/static/qrCode.png', // 默认值，，可以后台设置。会被接口数据覆盖
    otherSettings: {},// 其他配置项，用于存储其他自定义配置
    integralRes: '',// 积分配置，用于显示用户积分规则
    sysConfig: { website: {} },// 系统配置项，用于存储系统配置
    customerWechat: '',//客服微信号，可以后台设置。会被接口数据覆盖
    customerName: '',//客服姓名，可以后台设置。会被接口数据覆盖
    customerCompany: '',//客服公司，可以后台设置。会被接口数据覆盖
    customerPosition: '',//客服职位，可以后台设置。会被接口数据覆盖
    adConfig: '',// 免广告配置，用于控制广告显示
    copyRight: '',// 底部版权信息
    doubaoApiKey: '08b716cc-3c5b-4d5b-9476-c9ff1d5d015f',// 豆包AI识别API Key
    // 用户信息
    userInfo: {},
    // 添加网站配置相关字段
    shopName: '',
    h5Favicon: '',
    shopLogo: '',
    domain: ''
  },
  data() {
    return {
      // 配置缓存管理
      configCache: {
        // 缓存有效期（30天）
        expiry: 30 * 24 * 60 * 60 * 1000,
        // 缓存数据
        data: {
          sysConfig: null,
          integralRes: null,
          otherSettings: null
        },
        // 缓存时间戳
        timestamps: {
          sysConfig: 0,
          integralRes: 0,
          otherSettings: 0
        }
      },
      // 请求状态标志，防止重复请求
      isRequesting: {
        sysConfig: false,
        integralRes: false,
        otherSettings: false
      }
    }
  },
  onLaunch: function() {
    // 性能优化：启动时的异步初始化
    this.initApp()
    
    // 初始化PC端特有能力
    this.initPCCapabilities()
    
    // 将需要外部调用的方法挂载到globalData中
    this.globalData.fetchOtherSettings = this.fetchOtherSettings
    this.globalData.fetchAllConfigs = this.fetchAllConfigs
  },
  
  onShow: function() {
    // console.log('App Show')
  },
  
  onHide: function() {
    // console.log('App Hide')
  },
  
  methods: {
    // 性能优化：应用初始化
    initApp() {
      // 1. 先初始化设备信息（同步）
      this.initDeviceInfo()
      
        this.fetchAllConfigs().catch(err => {
          console.error('配置初始化失败:', err)
        })
      
      // #ifdef MP-WEIXIN
        this.checkUpdate()
      // #endif
    },
    
    // 性能优化：初始化设备信息（使用新API）
    initDeviceInfo() {
      try {
        let deviceInfo
        let platform
        let system
        
        // #ifdef MP-WEIXIN
        // 微信小程序：使用新的API替代已废弃的 getSystemInfoSync
        deviceInfo = uni.getSystemInfoSync()
        const systemInfo = wx.getSystemSetting()
        // 优先使用systemInfo中的信息，确保能正确检测鸿蒙系统
        platform = (systemInfo.platform || deviceInfo.platform)?.toLowerCase() || ''
        system = (systemInfo.system || deviceInfo.system)?.toLowerCase() || ''
        // 额外检查是否为鸿蒙系统（微信小程序环境下的特殊处理）
        if (platform === 'android' && (system.includes('harmony') || system.includes('鸿蒙'))) {
          platform = 'harmony'
        }
        // #endif
        
        // #ifndef MP-WEIXIN
        // 非微信平台：使用传统 API
        deviceInfo = uni.getSystemInfoSync()
        platform = deviceInfo.platform?.toLowerCase() || ''
        system = deviceInfo.system?.toLowerCase() || ''
        // #endif
        
        // 判断是否为ios设备
        if (platform.indexOf('ios') != -1 && (system.indexOf('ios') != -1 || system.indexOf('macos') != -1)) {
          Vue.prototype.SystemPlatform = 'apple'
        } else if (platform.indexOf('android') != -1 && system.indexOf('android') != -1) {
          Vue.prototype.SystemPlatform = 'android'
        } else if (platform.indexOf('harmony') != -1 || system.indexOf('harmony') != -1) {
          Vue.prototype.SystemPlatform = 'harmony'
        } else {
          Vue.prototype.SystemPlatform = 'devtools'
        }
      } catch (e) {
        console.error('获取设备信息失败:', e)
      }
      
      // 获取状态栏信息
      updateCustomBarInfo().then((res) => {
        store.commit('$tStore', {
          name: 'vuex_status_bar_height',
          value: res.statusBarHeight
        })
        store.commit('$tStore', {
          name: 'vuex_custom_bar_height',
          value: res.customBarHeight
        })
      })
    },
    
    // 性能优化：检查更新
    checkUpdate() {
      if (!wx.canIUse('getUpdateManager')) {
        return
      }
      
      const updateManager = wx.getUpdateManager()
      if (!updateManager) return
      
      updateManager.onCheckForUpdate((res) => {
        if (!res.hasUpdate) return
        
        updateManager.onUpdateReady(() => {
          uni.showModal({
            title: '更新提示',
            content: '新版本已经准备就绪，是否需要重新启动应用？',
            success: (res) => {
              if (res.confirm) {
                uni.clearStorageSync()
                updateManager.applyUpdate()
              }
            }
          })
        })
        
        updateManager.onUpdateFailed(() => {
          uni.showModal({
            title: '已有新版本上线',
            content: '小程序自动更新失败，请删除该小程序后重新搜索打开哟~~~',
            showCancel: false
          })
        })
      })
    },

    // 检查缓存是否有效
    isCacheValid(cacheKey) {
      const now = Date.now()
      return this.configCache.data[cacheKey] && 
             (now - this.configCache.timestamps[cacheKey]) < this.configCache.expiry
    },
    
    // 统一配置获取方法
    async fetchConfig(apiFunc, cacheKey, dataMap = {}, specialMapping = null) {
      // 禁用本地缓存，确保每次都从API获取最新数据
      // 问题：缓存可能保存了无效数据，导致后续请求不发出
      
      // 防止重复请求
      if (this.isRequesting[cacheKey]) {
        console.log(`${cacheKey} 正在请求中，等待完成...`)
        // 等待当前请求完成
        return new Promise((resolve) => {
          const check = setInterval(() => {
            if (!this.isRequesting[cacheKey]) {
              clearInterval(check)
              resolve(this.configCache.data[cacheKey] || null)
            }
          }, 100)
        })
      }
      
      try {
        // 设置请求状态为true
        this.isRequesting[cacheKey] = true
        
        // 获取配置数据
        const result = await apiFunc()
        // 严格检查result格式
        if (!result || typeof result !== 'object') {
          console.error(`${cacheKey} API返回结果格式错误:`, result)
          return result
        }
        
        // 只保存有效的数据到缓存
        if (result.data && typeof result.data === 'object') {
          // 更新缓存
          this.configCache.data[cacheKey] = result
          this.configCache.timestamps[cacheKey] = Date.now()
          
          const updates = {}
          
          // 基础映射
          for (const [apiKey, globalKey] of Object.entries(dataMap)) {
            const value = result.data[apiKey]
            if (value !== undefined) {
              updates[globalKey] = value
            }
          }
          
          // 特殊映射处理
          if (specialMapping && typeof specialMapping === 'function') {
            specialMapping(result.data, updates)
          }
          
          // 一次性更新globalData，减少响应式更新次数
          if (Object.keys(updates).length > 0) {
            Object.assign(this.globalData, updates)
            // 打印更新后的globalData
          }
        } else {
          console.warn(`${cacheKey} API返回结果中缺少data字段或data不是对象:`, result)
          // 无效数据不保存到缓存
          this.configCache.data[cacheKey] = null
        }
        
        return result
      } catch (error) {
        console.error(`获取${cacheKey}失败:`, error)
        // 缓存无效时返回null，否则返回缓存数据
        return this.configCache.data[cacheKey] || null
      } finally {
        // 设置请求状态为false
        this.isRequesting[cacheKey] = false
      }
    },
    
    // 并行获取所有配置，提高首屏加载速度
    async fetchAllConfigs() {
        // 并行获取所有配置，减少等待时间
        await Promise.all([
          this.fetchIntegralConfig(),
          this.fetchOtherSettings(),
          this.fetchSystemConfig()
        ])
    },
    
    // 获取积分配置
    fetchIntegralConfig() {
      return this.fetchConfig(
        configApi.getIntegralSettings,
        'integralRes',
        {},
        (data) => {
          // 直接更新globalData，避免不必要的映射
          this.globalData.integralRes = data
        }
      )
    },
    
    // 获取其他设置
    fetchOtherSettings() {
      return this.fetchConfig(
        configApi.getOtherSettings,
        'otherSettings',
        {
          'customer_qrcode': 'serviceQrCode',
          'customer_wechat': 'customerWechat',
          'copyright': 'copyRight',
          'customer_name': 'customerName',
          'customer_company': 'customerCompany',
          'customer_position': 'customerPosition',
          'customer_mobile': 'mobile',
          'member_settings': 'member_settings',
          'adConfig': 'adConfig',
          'homeRedirect': 'homeRedirect'
        },
        (data, updates) => {
          // 将完整的otherSettings数据更新到globalData中
          this.globalData.otherSettings = data
        }
      )
    },
    
    // 获取系统配置
    fetchSystemConfig() {
      // 恢复使用ApiCache，确保缓存机制正常工作
      return this.fetchConfig(
        () => ApiCache.getSysConfig(configApi.getSysConfig),
        'sysConfig',
        {},
        (data) => {
          // 处理网站配置
          const websiteData = data.website || {}
          this.globalData.shopName = websiteData.shop_name || ''
          this.globalData.h5Favicon = websiteData.h5_favicon || ''
          this.globalData.shopLogo = websiteData.shop_logo || ''
          this.globalData.domain = data.domain || ''
          this.globalData.sysConfig.website = websiteData
          this.globalData.sysConfig = data
          
        }
      )
    },
    
    // 检查用户信息是否已缓存
    async fetchUserInfo() {
      try {
        // 导入 apiUserInfo 方法
        const { default: userApi } = await import('@/util/api/user.js')
        
        // 使用 ApiCache 获取用户信息（缓存5分钟）
        const res = await ApiCache.getUserInfo(userApi.apiUserInfo)
        
        if (res && res.code === 1 && res.data) {
          // 使用统一的存储方法
          const { saveUserInfo } = await import('@/util/userStore.js')
          const success = saveUserInfo(res.data)
          
          if (success) {
            console.log('用户信息获取并保存成功（使用缓存）')
          } else {
            console.error('用户信息保存失败')
          }
          
          return res.data
        } else {
          console.warn('API返回数据无效:', res)
          return null
        }
      } catch (error) {
        console.error('获取用户信息失败:', error)
        return null
      }
    },
    
    // 获取应用全局配置数据 - 兼容旧方法调用
    fetchGlobalConfig() {
      return Promise.all([
        this.fetchIntegralConfig(),
        this.fetchOtherSettings()
      ])
    },
    
    // 获取系统配置数据 - 兼容旧方法调用
    fetchSysConfig() {
      return this.fetchSystemConfig()
    },
    
    // 初始化PC端特有能力
    initPCCapabilities() {
      // #ifdef MP-WEIXIN
      // 监听窗口大小变化
      wx.onWindowResize((res) => {
        console.log('窗口大小变化:', res.size)
        // 可以在这里触发全局事件，通知其他页面窗口大小变化
        this.$emit('windowResize', res.size)
      })
      
      // 监听键盘事件
      wx.onKeyUp((res) => {
        console.log('键盘抬起:', res)
        // 可以在这里处理键盘事件，如ESC键关闭弹窗等
      })
      
      wx.onKeyDown((res) => {
        console.log('键盘按下:', res)
        // 可以在这里处理键盘按下事件
      })
      
      // 获取系统信息，判断是否为PC端
      try {
        const deviceInfo = wx.getSystemInfoSync()
        console.log('设备信息:', deviceInfo)
        // 存储设备信息到globalData
        this.globalData.deviceInfo = deviceInfo
        // 判断是否为PC端
        if (deviceInfo.platform === 'windows' || deviceInfo.platform === 'mac') {
          this.globalData.isPC = true
          console.log('当前为PC端')
        } else {
          this.globalData.isPC = false
        }
      } catch (error) {
        console.error('获取设备信息失败:', error)
      }
      // #endif
    }
  }
}
</script>

<style lang="scss">
  /* 注意要写在第一行，同时给style标签加入lang="scss"属性 */
  @import './tuniao-ui/index.scss';
  @import './tuniao-ui/iconfont.css';

  .tt-text-main-color {
		color: $view-theme;
	}

	/* 底部安全边距 start*/
	.tn-tabbar-height {
		min-height: 100rpx;
		height: calc(100rpx + env(safe-area-inset-bottom) / 2);
		height: calc(100rpx + constant(safe-area-inset-bottom));
	}

</style>
