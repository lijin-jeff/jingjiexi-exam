<template>
  <view class="template-login">
    <!-- 顶部自定义导航 -->
    <view class="tn-navbg" :style="{height: vuex_custom_bar_height + 'px'}">
		<tn-nav-bar fixed alpha customBack>
		<template #back>
			<view class="tn-custom-nav-bar__back">
			<text
				class="icon tn-icon-left"
				@click="goBack"
			/>
			<text
				class="icon tn-icon-home-capsule-fill"
				@click="goHome"
			/>
			</view>
		</template>
		<view class="tn-flex tn-flex-col-center tn-flex-row-center">
			<text class="tn-text-bold tn-text-xl tn-color-white">
			登录授权
			</text>
		</view>
		</tn-nav-bar>
	</view>

    <view class="login">
      <!-- 顶部背景图片-->
      <view class="login__bg login__bg--top">
        <image
          class="bg"
          src="https://datiqiniu.allpp.cn/static/login-top2.png"
          mode="widthFix"
        />
      </view>

      <view class="login__wrapper">
        <view
          class="tn-margin-left tn-margin-right tn-text-bold"
          style="font-size: 60rpx;"
        >
          欢迎使用
        </view>
        <view class="tn-margin tn-color-gray tn-text-lg">
          {{ shopName }}
        </view>

        <!-- 登录/注册切换 -->
        <view
          class="login-sussuspension login__mode tn-flex tn-flex-direction-row tn-flex-nowrap tn-flex-col-center tn-flex-row-center"
        >
          <view
            class="login__mode__item tn-flex-1"
            :class="[{'login__mode__item--active': currentModeIndex === 0}]"
            @tap.stop="modeSwitch(0)"
          >
            登录
          </view>
          <view
            class="login__mode__item tn-flex-1"
            :class="[{'login__mode__item--active': currentModeIndex === 1}]"
            @tap.stop="modeSwitch(1)"
          >
            注册
          </view>
          <view
            class="login__mode__slider tn-cool-bg-color-15--reverse"
            :style="[modeSliderStyle]"
          />
        </view>

        <!-- 输入框内容 -->
        <view class="login__info tn-flex tn-flex-direction-column tn-flex-col-center tn-flex-row-center">
          <!-- 登录 -->
          <block v-if="currentModeIndex === 0">
            <view
              class="login__info__item__input tn-flex tn-flex-direction-row tn-flex-nowrap tn-flex-col-center tn-flex-row-left"
            >
              <view class="login__info__item__input__left-icon">
                <view class="tn-icon-phone" />
              </view>
              <view class="login__info__item__input__content">
                <input
                  v-model="mobile"
                  maxlength="20"
                  placeholder-class="input-placeholder"
                  placeholder="请输入手机号"
                >
              </view>
            </view>

            <view
              class="login__info__item__input tn-flex tn-flex-direction-row tn-flex-nowrap tn-flex-col-center tn-flex-row-left"
            >
              <view class="login__info__item__input__left-icon">
                <view class="tn-icon-lock" />
              </view>
              <view class="login__info__item__input__content">
                <input
                  v-model="password"
                  :password="!showPassword"
                  placeholder-class="input-placeholder"
                  placeholder="请输入登录密码"
                >
              </view>
              <view
                class="login__info__item__input__right-icon"
                @click="showPassword = !showPassword"
              >
                <view :class="[showPassword ? 'tn-icon-eye' : 'tn-icon-eye-hide']" />
              </view>
            </view>

            <!-- <view
						class="login__info__item__input tn-flex tn-flex-direction-row tn-flex-nowrap tn-flex-col-center tn-flex-row-left">
						<view class="login__info__item__input__left-icon">
							<view class="tn-icon-safe"></view>
						</view>
						<view class="login__info__item__input__content login__info__item__input__content--verify-code">
							<input placeholder-class="input-placeholder" placeholder="请输入验证码" />
						</view>
						<view class="login__info__item__input__right-verify-code" @tap.stop="getCode">
							<tn-button backgroundColor="#01BEFF" fontColor="#FFFFFF" size="sm" padding="5rpx 10rpx" width="100%"
								shape="round">{{ tips }}</tn-button>
						</view>
					</view> -->
          </block>
          <!-- 注册 -->
          <block v-if="currentModeIndex === 1">
            <view
              class="login__info__item__input tn-flex tn-flex-direction-row tn-flex-nowrap tn-flex-col-center tn-flex-row-left"
            >
              <view class="login__info__item__input__left-icon">
                <view class="tn-icon-phone" />
              </view>
              <view class="login__info__item__input__content">
                <input
                  v-model="mobile"
                  maxlength="20"
                  placeholder-class="input-placeholder"
                  placeholder="请输入注册手机号"
                >
              </view>
            </view>

            <view
              class="login__info__item__input tn-flex tn-flex-direction-row tn-flex-nowrap tn-flex-col-center tn-flex-row-left"
            >
              <view class="login__info__item__input__left-icon">
                <view class="tn-icon-safe" />
              </view>
              <view class="login__info__item__input__content login__info__item__input__content--verify-code">
                <input
                  v-model="code"
                  placeholder-class="input-placeholder"
                  placeholder="请输入验证码"
                >
              </view>
              <view
                class="login__info__item__input__right-verify-code"
                @tap.stop="getCode"
              >
                <tn-button
                  size="sm"
                  padding="5rpx 10rpx"
                  width="100%"
                  shape="round"
                >
                  {{ tips }}
                </tn-button>
              </view>
            </view>

            <view
              class="login__info__item__input tn-flex tn-flex-direction-row tn-flex-nowrap tn-flex-col-center tn-flex-row-left"
            >
              <view class="login__info__item__input__left-icon">
                <view class="tn-icon-lock" />
              </view>
              <view class="login__info__item__input__content">
                <input
                  v-model="password"
                  :password="!showPassword"
                  placeholder-class="input-placeholder"
                  placeholder="请输入登录密码"
                >
              </view>
              <view
                class="login__info__item__input__right-icon"
                @click="showPassword = !showPassword"
              >
                <view :class="[showPassword ? 'tn-icon-eye' : 'tn-icon-eye-hide']" />
              </view>
            </view>
          </block>
          <!-- 重置密码 -->
          <block v-if="currentModeIndex === 3">
            <view
              class="login__info__item__input tn-flex tn-flex-direction-row tn-flex-nowrap tn-flex-col-center tn-flex-row-left"
            >
              <view class="login__info__item__input__left-icon">
                <view class="tn-icon-phone" />
              </view>
              <view class="login__info__item__input__content">
                <input
                  v-model="mobile"
                  maxlength="20"
                  placeholder-class="input-placeholder"
                  placeholder="请输入登录手机号"
                >
              </view>
            </view>

            <view
              class="login__info__item__input tn-flex tn-flex-direction-row tn-flex-nowrap tn-flex-col-center tn-flex-row-left"
            >
              <view class="login__info__item__input__left-icon">
                <view class="tn-icon-safe" />
              </view>
              <view class="login__info__item__input__content login__info__item__input__content--verify-code">
                <input
                  v-model="code"
                  placeholder-class="input-placeholder"
                  placeholder="请输入验证码"
                >
              </view>
              <view
                class="login__info__item__input__right-verify-code"
                @tap.stop="getCode"
              >
                <tn-button
                  size="sm"
                  padding="5rpx 10rpx"
                  width="100%"
                  shape="round"
                >
                  {{ tips }}
                </tn-button>
              </view>
            </view>

            <view
              class="login__info__item__input tn-flex tn-flex-direction-row tn-flex-nowrap tn-flex-col-center tn-flex-row-left"
            >
              <view class="login__info__item__input__left-icon">
                <view class="tn-icon-lock" />
              </view>
              <view class="login__info__item__input__content">
                <input
                  v-model="password"
                  :password="!showPassword"
                  placeholder-class="input-placeholder"
                  placeholder="请输入新密码"
                >
              </view>
              <view
                class="login__info__item__input__right-icon"
                @click="showPassword = !showPassword"
              >
                <view :class="[showPassword ? 'tn-icon-eye' : 'tn-icon-eye-hide']" />
              </view>
            </view>

            <view
              class="login__info__item__input tn-flex tn-flex-direction-row tn-flex-nowrap tn-flex-col-center tn-flex-row-left"
            >
              <view class="login__info__item__input__left-icon">
                <view class="tn-icon-lock" />
              </view>
              <view class="login__info__item__input__content">
                <input
                  v-model="password_confirm"
                  :password="!showPassword_confirm"
                  placeholder-class="input-placeholder"
                  placeholder="请确认新密码"
                >
              </view>
              <view
                class="login__info__item__input__right-icon"
                @click="showPassword_confirm = !showPassword_confirm"
              >
                <view :class="[showPassword_confirm ? 'tn-icon-eye' : 'tn-icon-eye-hide']" />
              </view>
            </view>
          </block>

          <view
            class="login__info__item__button tn-bg-blue tn-color-white"
            hover-class="tn-hover"
            :hover-stay-time="150"
            @click="submitForm"
          >
            {{ currentModeIndex === 0 ? '登录' : (currentModeIndex === 1 ? '注册' : '重置密码') }}
          </view>

          <view
            class="login__info__item__tips"
            :style="{margin: 0}"
            @click="readPolicy"
          >
            <view class="tn-flex tn-flex-row-between tn-flex-col-center">
              <view class="tn-icon-tip" />
              <view class="">
                请仔细阅读平台<text :style="{color: mainColor}">
                  用户隐私协议
                </text>
              </view>
            </view>
          </view>

          <view
            v-if="currentModeIndex === 1 || currentModeIndex === 3"
            :class="[{'login__info__item__tips': currentModeIndex === 0}]"
          >
            <view class="tn-flex tn-flex-row-between tn-padding">
              <view
                class=""
                @tap.stop="modeSwitch(0)"
              >
                前往登录
              </view>
            </view>
          </view>
          <view
            v-if="currentModeIndex === 0"
            :class="[{'login__info__item__tips': currentModeIndex === 1}]"
          >
            <view class="tn-flex tn-flex-row-between tn-padding">
              <view
                class="tn-padding-right"
                @tap.stop="modeSwitch(1)"
              >
                账号注册
              </view>
              <view
                class="tn-padding-left tn-color-gray"
                @tap.stop="modeSwitch(3)"
              >
                忘记密码
              </view>
            </view>
          </view>
        </view>

        <!-- 其他登录方式 -->
        <view class="login__way">
			<view class="login__way__title">— 快捷登录 —</view>
			<view class="login__way__icons">
				<view class="tn-padding-sm tn-margin-xs" @click="codeLogin">
					<view class="login__way__item--icon tn-flex tn-flex-row-center tn-flex-col-center tn-color-teal--dark">
						<view class="tn-icon-wechat-fill"></view>
					</view>
				</view>
			</view>
			<!-- <view class="tn-padding-sm tn-margin-xs" @tap.stop="modeSwitch(0)">
				<view class="login__way__item--icon tn-flex tn-flex-row-center tn-flex-col-center tn-color-red">
					<view class="tn-icon-iphone"></view>
				</view>
			</view> -->
		</view>
      </view>

      <!-- 底部背景图片-->
      <view class="login__bg login__bg--bottom">
        <image
          src="https://datiqiniu.allpp.cn/static/login-bottom2.png"
          mode="widthFix"
        />
      </view>
    </view>

    <!-- 验证码倒计时-->
    <tn-verification-code
      ref="code"
      unique-key="login-demo-4"
      :seconds="60"
      @change="codeChange"
    />

    <!-- 微信隐私鉴权保护弹窗开始-->
    <!-- #ifdef MP-WEIXIN -->
    <privacy-popup ref="privacyComponent" />
    <!-- #endif -->
    <!-- 微信隐私鉴权弹窗保护结束 -->

    <!-- 是否开启小程序授权开始-->
    <tn-modal
      v-model="showMpAuth"
      width="70%"
      :title="showMpAuthInfo.title"
      :content="showMpAuthInfo.content"
      :button="showMpAuthInfo.button"
      @click="authConfirm"
    />
    <!-- 是否开启小程序授权结束 -->
  </view>
</template>

<script>
	import template_page_mixin from '@/libs/mixin/template_page_mixin.js'
	import PrivacyPopup from './components/privacy-popup/privacy-popup.vue'
	import { saveUserInfo } from '@/util/userStore.js'
	
	export default {
		name: 'LoginPage',
		components: {
			PrivacyPopup
		},
		computed: {
			// 使用计算属性实时获取globalData中的配置数据
			mainColor() {
				return getApp().globalData.mainColor || '#007AFF'
			},
			shopName() {
				return getApp().globalData.shopName || getApp().globalData.sysConfig?.website?.shop_name || '答题平台'
			}
		},
		mixins: [template_page_mixin],
		data() {
			return {
				// 当前选中的模式
				currentModeIndex: 0,
				// 模式选中滑块
				modeSliderStyle: {
					left: 0
				},
				showMpAuth: false,
				showMpAuthInfo: {
					title: '授权提示',
					content: '当前正在使用微信小程序，是否授权微信小程序',
					button: [{
							text: '取消',
							backgroundColor: '#E83A30',
							fontColor: '#FFFFFF',
						},
						{ text: '确定', backgroundColor: '', fontColor: '#FFFFFF' }
					]
				},
				// 是否显示密码
				showPassword: false,
				showPassword_confirm: false,
				// 倒计时提示文本
			tips: '获取验证码',
				mobile: '',
				password: '',
				password_confirm: '',
				code: '',
			}
		},
		watch: {
			currentModeIndex(value) {
				const sliderWidth = uni.upx2px(476 / 2)
				this.modeSliderStyle.left = `${sliderWidth * value}px`
			}
		},
		onLoad(params) {
			// 动态设置按钮背景色
			this.showMpAuthInfo.button[1].backgroundColor = this.mainColor
				
			// #ifdef MP-WEIXIN
			this.$tn.mpShare = {
				share: false,
			}
			if (!this.$tn.mpShare.share) {
				uni.hideShareMenu()
			}
			// #endif
		},
		onShow() {
			// 动态设置按钮背景色
			this.showMpAuthInfo.button[1].backgroundColor = this.mainColor
		},
		methods: {
			submitForm() {
				if (this.currentModeIndex === 1) { // 注册
					
					// this.$func.showToast('注册已关闭，请使用演示账号')
					// return
					this.$api.apiAccountRegister({
						account: this.mobile,
						code: this.code,
						password: this.password,
						channel: 1
					}).then(res => {
						this.$func.showToast(res.msg)
						if (res.code === 1) {
							// 注册成功后自动登录
							uni.showLoading({ title: '自动登录中...' })
							
							this.$api.apiAccountLogin({
								terminal: 1,
								account: this.mobile,
								scene: 1,
								password: this.password
							}).then(loginRes => {
								uni.hideLoading()
								
								if (loginRes.code === 1) {
									// 使用统一的用户信息保存方法
							const success = saveUserInfo({
								...loginRes.data,
								token: loginRes.data.token,
								integral: loginRes.data.integral || 0,
								vip_state: loginRes.data.vip_state || 0,
								vip_endTime: loginRes.data.vip_endTime || null
							})
									
							if (!success) {
								console.error('用户信息保存失败')
							}
							
							this.$func.showToast('注册并登录成功')
							
									// 登录成功后的处理逻辑
							const handleLoginSuccess = () => {

								// 读取 storage 中的 redirectPath
								const redirectPath = uni.getStorageSync('redirectPath')
								if (redirectPath) {
									// 存在重定向路径，跳转到该路径
									uni.removeStorageSync('redirectPath')
									uni.reLaunch({
										url: redirectPath
									})
								} else {
									// 不存在重定向路径，返回上一页并刷新
									// 先等待100ms，确保用户信息已经保存到本地存储
									setTimeout(() => {
										this.$func.navigateBackAndRefresh()
									}, 100);
								}
							}
									
									// #ifdef MP-WEIXIN
									if (loginRes.data.need_mp === true) {
										this.showMpAuth = true
									} else {
										handleLoginSuccess()
									}
									// #endif
									// #ifndef MP-WEIXIN
									handleLoginSuccess()
									// #endif
								} else {
									this.$func.showToast(loginRes.msg || '自动登录失败，请手动登录')
									this.currentModeIndex = 0 // 切换到登录模式
								}
							}).catch(err => {
								uni.hideLoading()
								console.error('自动登录失败', err)
								this.$func.showToast('注册成功，但自动登录失败，请手动登录')
								this.currentModeIndex = 0 // 切换到登录模式
							})
						}
					})
				} else if (this.currentModeIndex === 0) { // 登录
					
					this.$api.apiAccountLogin({
						terminal: 1,
						account: this.mobile,
						scene: 1,
						password: this.password
					}).then(res => {
						this.$func.showToast(res.msg)
						if (res.code === 1) {
							// 使用统一的用户信息保存方法
									const success = saveUserInfo({
										...res.data,
										token: res.data.token,
										integral: res.data.integral || 0,
										vip_state: res.data.vip_state || 0,
										vip_endTime: res.data.vip_endTime || null
									})
							
							if (!success) {
								console.error('用户信息保存失败')
							}
							
							// 登录成功后的处理逻辑
						const handleLoginSuccess = () => {
							// 读取 storage 中的 redirectPath
							const redirectPath = uni.getStorageSync('redirectPath')
							if (redirectPath) {
								// 存在重定向路径，跳转到该路径
								uni.removeStorageSync('redirectPath')
								uni.reLaunch({
									url: redirectPath
								})
							} else {
								// 不存在重定向路径，返回上一页并刷新
								// 先等待100ms，确保用户信息已经保存到本地存储
								setTimeout(() => {
									this.$func.navigateBackAndRefresh()
								}, 100);
							}
						}
							
							// #ifdef MP-WEIXIN
							if (res.data.need_mp === true) {
								this.showMpAuth = true
							} else {
								handleLoginSuccess()
							}
							// #endif
							// #ifndef MP-WEIXIN
							handleLoginSuccess()
							// #endif
						}
					})
				} else if (this.currentModeIndex === 3) { // 重置密码
					this.$api.apiResetPassword({
						code: this.code,
						mobile: this.mobile,
						password: this.password,
						password_confirm: this.password_confirm
					}).then(res => {
						this.$func.showToast(res.msg)
						if(res.code === 1) {
							this.currentModeIndex = 0
						}
					})
				} else {
					this.$func.showToast('未知操作方式')
				}
			},
			bindMpCode() {
				let _that = this
				uni.getProvider({
					service: 'oauth',
					success(res) {
						if (~res.provider.indexOf('weixin')) {
							uni.login({
								provider: 'weixin',
								success(loginRes) {

									if (loginRes.errMsg === 'login:ok') {
										_that.$api.apiMnpAuthBind({
											code: loginRes.code,
											token: uni.getStorageSync('login')  // 添加token参数
										}).then(requestRes => {
											if (requestRes.code === 1) {
											_that.showMpAuth = false
										_that.$func.showToast(requestRes.msg)
										
										// 读取 storage 中的 redirectPath
										const redirectPath = uni.getStorageSync('redirectPath')
										if (redirectPath) {
											// 存在重定向路径，跳转到该路径
											uni.removeStorageSync('redirectPath')
											uni.reLaunch({
												url: redirectPath
											})
										} else {
											// 调用通用的返回上一页并刷新方法
											_that.$func.navigateBackAndRefresh()
										}
										return
										}
											_that.$func.showToast(requestRes.msg)
										})
										return
									}
									_that.$func.showToast(loginRes.errMsg)
								}
							})
						} else {
								_that.$func.showToast('暂不支持该授权类型')
							}
					},
					fail(res) {
						_that.$func.showToast(res.errMsg)
					}
				})
			},
			authConfirm(e) {
				if (e.index === 1) {
					this.bindMpCode()
					return
				}
				this.showMpAuth = false
				// 读取 storage 中的 redirectPath
				const redirectPath = uni.getStorageSync('redirectPath')
				if (redirectPath) {
					// 存在重定向路径，跳转到该路径
					uni.removeStorageSync('redirectPath')
					uni.reLaunch({
						url: redirectPath
					})
				} else {
					// 调用通用的返回上一页并刷新方法
					this.$func.navigateBackAndRefresh()
				}
			},
			readPolicy() {
				this.$func.navigatorTo('/subpages/common/policyContent?type=privacy')
			},
			// 微信小程序授权登录（静默授权+主动授权）
			codeLogin() {
				// #ifdef MP-WEIXIN
				uni.showLoading({ title: '登录中...' })
				
				// 先尝试静默登录
				uni.login({
					provider: 'weixin',
					success: (loginRes) => {
						if (loginRes.code) {
							// 调用后端小程序登录接口
							this.$api.apiMnpLogin({
								code: loginRes.code
							}).then(res => {
								uni.hideLoading()
								
								if (res.code === 1 && res.data) {
									// 使用统一的用户信息保存方法
								const success = saveUserInfo({
									...res.data,
									token: res.data.token,
									integral: res.data.integral || 0,
									vip_state: res.data.vip_state || 0,
									vip_endTime: res.data.vip_endTime || null
								})
									
									if (!success) {
										console.error('用户信息保存失败')
										this.$func.showToast('登录失败，请重试')
										return
									}
									
									this.$func.showToast('登录成功')
									// 登录成功后的处理逻辑
									const handleLoginSuccess = () => {
										// 读取 storage 中的 redirectPath
										const redirectPath = uni.getStorageSync('redirectPath')
										if (redirectPath) {
											// 存在重定向路径，跳转到该路径
											uni.removeStorageSync('redirectPath')
											uni.reLaunch({
												url: redirectPath
											})
										} else {
											// 不存在重定向路径，返回上一页并刷新
											// 先等待100ms，确保用户信息已经保存到本地存储
											setTimeout(() => {
												this.$func.navigateBackAndRefresh()
											}, 100);
										}
									}
									
									// 调用登录成功处理逻辑
									handleLoginSuccess();
								} else {
									this.$func.showToast(res.msg || '登录失败')
								}
							}).catch(err => {
								uni.hideLoading()
								console.error('微信登录失败', err)
								this.$func.showToast('登录失败，请重试')
							})
						} else {
							uni.hideLoading()
							this.$func.showToast('获取微信授权失败')
						}
					},
					fail: (err) => {
						uni.hideLoading()
						console.error('uni.login失败', err)
						this.$func.showToast('微信授权失败')
					}
				})
				// #endif
				
				// #ifndef MP-WEIXIN
				this.$func.showToast('仅支持微信小程序环境')
				// #endif
			},
			// 切换模式
			modeSwitch(index) {
				this.currentModeIndex = index
				this.showPassword = false
			},
			// 获取验证码
			getCode() {
				if (!this.$func.verifyPhone(this.mobile)) {
					this.$func.showToast('请输入正确的手机号码')
					return
				}
				if (this.$refs.code.canGetCode) {
					this.$func.showToast('正在获取验证码')
					this.$refs.code.start()
							
							// 根据当前模式选择验证码场景
							let scene = 'YZMDL' // 默认登录
							if (this.currentModeIndex === 1) {
								scene = 'ZCZH' // 注册
							} else if (this.currentModeIndex === 3) {
								scene = 'ZHDLMM' // 重置密码
							}
										
							this.$api.apiSmsSend({
								mobile: this.mobile,
								scene: scene
							}).then(res => {
								this.$func.showToast(res.msg)
							}).catch(err => {
								// 发送失败时停止倒计时
								this.$refs.code.reset()
								console.error('验证码发送失败', err)
							})
				} else {
					this.$func.showToast(this.$refs.code.secNum + '秒后再重试')
				}
			},
			// 获取验证码倒计时被修改
			codeChange(event) {
				this.tips = event
			}
		}
	}
</script>

<style lang="scss" scoped>
	@import '@/scss/custom_nav_bar.scss';

	.login {
		position: relative;
		height: 100%;
		z-index: 1;

		/* 背景图片 start */
		&__bg {
			z-index: -1;
			position: fixed;

			&--top {
				top: 0;
				left: 0;
				right: 0;
				width: 100%;

				.bg {
					width: 750rpx;
					will-change: transform;
				}
			}

			&--bottom {
				bottom: -10rpx;
				left: 0;
				right: 0;
				width: 100%;
				// height: 144px;
				margin-bottom: env(safe-area-inset-bottom);

				image {
					width: 750rpx;
					will-change: transform;
				}
			}
		}

		/* 内容 start */
		&__wrapper {
			margin-top: 180rpx;
			width: 100%;
		}

		/* 切换 start */
		&__mode {
			position: relative;
			margin: 0 auto;
			width: 476rpx;
			height: 77rpx;
			margin-top: 50rpx;
			background-color: rgba(255, 255, 255, 0.6);
			box-shadow: 0rpx 10rpx 50rpx 0rpx rgba(0, 3, 72, 0.1);
			border-radius: 39rpx;

			&__item {
				height: 77rpx;
				width: 100%;
				line-height: 77rpx;
				text-align: center;
				font-size: 31rpx;
				color: #080808;
				letter-spacing: 1em;
				text-indent: 1em;
				z-index: 2;
				transition: all 0.4s;

				&--active {
					font-weight: bold;
					color: #FFFFFF;
				}
			}

			&__slider {
				position: absolute;
				height: inherit;
				width: calc(476rpx / 2);
				border-radius: inherit;
				box-shadow: 0rpx 18rpx 72rpx 18rpx rgba(0, 195, 255, 0.1);
				z-index: 1;
				transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
			}
		}

		/* 切换 end */

		/* 登录注册信息 start */
		&__info {
			margin: 0 30rpx 10rpx 30rpx;
			padding-bottom: 0;
			border-radius: 20rpx;

			&__item {

				&__input {
					margin-top: 59rpx;
					width: 100%;
					height: 77rpx;
					border: 1rpx solid #E6E6E6;
					border-radius: 39rpx;

					&__left-icon {
						width: 10%;
						font-size: 44rpx;
						margin-left: 20rpx;
						color: #838383;
					}

					&__content {
						width: 80%;
						padding-left: 10rpx;

						&--verify-code {
							width: 56%;
						}

						input {
							font-size: 24rpx;
							// letter-spacing: 0.1em;
						}
					}

					&__right-icon {
						width: 10%;
						font-size: 44rpx;
						margin-right: 20rpx;
						color: #838383;
					}

					&__right-verify-code {
						width: 34%;
						margin-right: 20rpx;
					}
				}

				&__button {
					margin-top: 75rpx;
					margin-bottom: 39rpx;
					width: 100%;
					height: 77rpx;
					text-align: center;
					font-size: 31rpx;
					font-weight: bold;
					line-height: 77rpx;
					letter-spacing: 1em;
					text-indent: 1em;
					border-radius: 39rpx;
					box-shadow: 1rpx 10rpx 24rpx 0rpx rgba(60, 129, 254, 0.35);
				}

				&__tips {
					margin: 30rpx 0;
					color: #AAAAAA;
				}
			}
		}

		/* 登录注册信息 end */

		/* 登录方式切换 start */
		&__way {
			margin: 0 auto;
			margin-top: 60rpx;

			&__title {
				width: 100%;
				font-size: 30rpx;
				color: #999999;
				margin-bottom: 30rpx;
				letter-spacing: 2rpx;
				font-weight: 500;
				text-align: center;
				margin-top: 0;
			}

			&__icons {
				display: flex;
				justify-content: center;
				align-items: center;
				width: 100%;
			}

			&__item {
				&--icon {
					width: 85rpx;
					height: 85rpx;
					font-size: 70rpx;
					// border-radius: 100rpx;
					margin-bottom: 18rpx;
					position: relative;
					z-index: 1;
				}
			}
		}
	}

	::v-deep.input-placeholder {
		font-size: 24rpx;
		color: #838383;
	}
</style>