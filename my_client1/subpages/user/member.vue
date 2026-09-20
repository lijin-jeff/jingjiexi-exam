<template>
  <view>
    <!-- 支付方式选择操作菜单 -->
    <tn-action-sheet 
      v-model="showPaymentSheet" 
      :list="paymentSheetList" 
      @click="handlePaymentSelect"
    ></tn-action-sheet>
    
    <!-- 积分兑换确认模态框 -->
    <tn-modal 
      v-model="showIntegralModal" 
      :title="'积分兑换确认'" 
	  :showCloseBtn="true"
      :content="integralModalContent" 
      :button="[{ text: '取消', backgroundColor: '#F5F5F5', fontColor: '#666666' },
	  			{ text: '确定', backgroundColor: mainColor, fontColor: '#FFFFFF' }]" 
      @click="handleIntegralModalClick"
    ></tn-modal>
    
    <!-- 激活码兑换模态框 -->
    <tn-modal 
      v-model="showActivationModal" 
      :title="'激活码兑换'"
      :custom="true"
      :width="'84%'"
      :radius="12"
      :padding="'30rpx 26rpx'"
      :showCloseBtn="true"
    >
      <view class="custom-modal-content">
		<view class="modal-title">激活码兑换</view>
        <tn-form :labelWidth="100" :border="false">
          <tn-form-item :borderBottom="false">
            <tn-input 
              v-model="activationCode" 
              placeholder="请输入激活码"
              :border="true"
			  :borderColor="mainColor"
              :radius="8"
              :height="100"
              :fontSize="28"
			  style="width: 100%;"
              :backgroundColor="'#F5F7FA'"
              :placeholderColor="'#C0C4CC'"
            />
          </tn-form-item>
        </tn-form>
        <text class="custom-modal-hint">如果没有激活码，请《<text class="tn-color-blue" @click="handleContactCustomerService">联系客服</text>》获取</text>
        
        <!-- 按钮区 -->
        <view class="custom-modal-buttons">
          <tn-button 
            class="tn-button cancel-button" 
            @click="handleActivationModalCancel"
            :backgroundColor="'#F5F5F5'"
            :fontColor="'#666666'"
            :shape="'circle'"
            :height="90"
            :fontSize="38"
          >取消</tn-button>
          <tn-button 
            class="tn-button confirm-button" 
            @click="handleActivationModalConfirm"
            :backgroundColor="'#42B476'"
            :fontColor="'#FFFFFF'"
            :shape="'circle'"	
            :height="90"
            :fontSize="38"
          >兑换</tn-button>
        </view>
      </view>
    </tn-modal>
    
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
			激活会员
			</text>
		</view>
		</tn-nav-bar>
	</view>

    <view
      class="page"
      :style="{paddingTop: vuex_custom_bar_height + 10 + 'px'}"
    >
      <view class="user">
        <image :src="userInfo.avatar" />
        <view>
          <text style="font-size: 32rpx;">{{ userInfo.nickname }}</text>
          <text  v-if="userInfo.vip_state === 1">
            你还未激活会员
          </text>
          <text v-else-if="userInfo.vip_state === 2">
            会员已过期，请重新激活
          </text>
          <text v-else-if="userInfo.vip_state === 3">
			<!-- 会员未过期，显示到期时间，转换为时间格式 -->
           有效期至:{{userInfo.vip_endTime ? unixtimeToDate(userInfo.vip_endTime) : '未知'}}
          </text>
          <text v-if="userInfo.integral !== undefined" class="user-integral">
            当前积分：{{ userInfo.integral }}
          </text>
        </view>
      </view>

      <view class="option">
        <view class="option-title">
          会员套餐
        </view>
        <view class="option-list">
          <view
            v-for="(v, k) in options"
            :key="k"
            :class="'option-item ' + (k === current ? 'option-selected' : '')"
            @click="change(k)"
          >
            <text>{{ v.name }}</text>
            <text>￥{{ v.price }}</text>
			<text v-if="v.integral_required > 0" :style="{color: '#FF9500'}">
              积分:{{ v.integral_required }}
            </text>
            <text :style="{color: mainColor}">
              原价:￥{{ v.oldPrice }}
            </text>
            <view class="option-item-line" />
          </view>
        </view>
        <view
          class="option-botton"
          @click="pay()"
        >
          立即激活
        </view>
      </view>

      <view class="table">
        <view class="td th">
          <view>功能权限</view>
          <view>
            普通用户
          </view>
          <view>VIP用户</view>
        </view>
        <view
          v-for="(v, k) in advantages"
          :key="k"
          class="td"
        >
          <view>{{ v.name }}</view>
          <view>
            <image
              v-if="v.normal === 0"
              src="https://datiqiniu.allpp.cn/static/no.png"
              @error="$event.target.style.display='none'"
            />
            <image
              v-else-if="v.normal === 1"
              src="https://datiqiniu.allpp.cn/static/ok.png"
              @error="$event.target.style.display='none'"
            />
            <text v-else>
              {{ v.normal }}
            </text>
          </view>
          <view>
            <image
              v-if="v.vip === 1"
              src="https://datiqiniu.allpp.cn/static/ok.png"
              @error="$event.target.style.display='none'"
            />
            <text v-else>
              {{ v.vip }}
            </text>
          </view>
        </view>
      </view>
    </view>
  </view>
</template>

<script>
	import template_page_mixin from '@/libs/mixin/template_page_mixin.js'
	export default {
		mixins: [template_page_mixin],
		data() {
			return {
				mainColor: getApp().globalData.mainColor,
				options: [],
				advantages: [],
				memberSettings: {},
				activationTypes: [],
				current: 0, // 当前选中套餐
				userInfo: {
					id: 0,
					nickname: '匿名用户111',
					avatar: 'https://datiqiniu.allpp.cn/static/userinfo_avatar.png',
					sex: 0,
					sn: '12345678',
					vip_state: 1
				},
				orderSn: '',
				timeId: 0,
				
				// 支付方式选择操作菜单相关
				showPaymentSheet: false,
				paymentSheetList: [],
				
				// 积分兑换确认模态框相关
				showIntegralModal: false,
				integralModalContent: '',
				
				// 激活码兑换模态框相关
				showActivationModal: false,
				activationCode: '',
				
				// 存储当前选中的支付方式
				selectedPaymentType: ''
			}
		},
		onLoad() {
			// #ifdef MP-WEIXIN
			this.$tn.mpShare = {
				share: false,
			}
			if (!this.$tn.mpShare.share) {
				uni.hideShareMenu()
			}
			// #endif
			this.fetchUser()
			this.loadMemberSettings()
		},
		methods: {
			fetchUser() {
				this.$api.apiUserInfo().then(res => {
					if (res.code === 1) {
						this.userInfo = res.data
						return
					}
					this.$func.showToast(res.msg)
				})
			},
			loadMemberSettings() {
				// 从App.vue的globalData中获取otherSettings
				const globalData = getApp().globalData;
	
				// 从getApp().globalData.otherSettings.data安全获取会员设置
				const memberSettings = globalData.otherSettings?.member_settings;
				if (!memberSettings) {
					console.error('会员设置数据不存在');
					return;
				}
				
				// 尝试解析JSON字符串（如果是字符串的话）
				let parsedSettings;
				if (typeof memberSettings === 'string') {
					try {
						parsedSettings = JSON.parse(memberSettings);
					} catch (e) {
						console.error('解析会员设置失败:', e);
						return;
					}
				} else {
					parsedSettings = memberSettings;
				}
				
				this.memberSettings = parsedSettings;
				
				// 设置激活方式，确保是数组
				this.activationTypes = Array.isArray(parsedSettings.activation_type) 
					? parsedSettings.activation_type 
					: parsedSettings.activation_type ? [parsedSettings.activation_type] : [];
				
				// 处理会员套餐
				if (parsedSettings.packages && Array.isArray(parsedSettings.packages)) {
					
					this.options = parsedSettings.packages
						.filter(pkg => {
							if (!pkg) return false;
							// 兼容 boolean 和 string 类型的 is_enabled
							const isEnabled = pkg.is_enabled === true || 
							                 pkg.is_enabled === '1' || 
							                 pkg.is_enabled === 1 || 
							                 pkg.is_enabled === '';
							return isEnabled;
						})
						.map(pkg => {
							let duration = parseInt(pkg.duration) || 0;
							const durationUnit = pkg.duration_unit;
							
							// 将月和年转换为天
							if (durationUnit === 'month' || durationUnit === '月') {
								duration *= 30;
							} else if (durationUnit === 'year' || durationUnit === '年') {
								duration *= 365;
							}
							
							const result = {
								name: pkg.name,
								oldPrice: parseFloat(pkg.old_price) || 0,
								price: parseFloat(pkg.price) || 0,
								integral_required: parseInt(pkg.integral_required) || 0,
								duration: duration,
								duration_unit: pkg.duration_unit
							};
							
							return result;
						});
				
				} else {
					console.warn('[会员页面] 套餐数据不存在或格式错误');
					this.options = [];
				}
				
				// 处理会员权限
				if (parsedSettings.privileges && Array.isArray(parsedSettings.privileges)) {
					this.advantages = parsedSettings.privileges
						.map(privilege => ({
							name: privilege.name,
							normal: isNaN(parseInt(privilege.normal_value)) ? privilege.normal_value : parseInt(privilege.normal_value),
							vip: isNaN(parseInt(privilege.vip_value)) ? privilege.vip_value : parseInt(privilege.vip_value)
						}));
				} else {
					this.advantages = [];
				}
			},
			change(k) {
				this.current = k;
			},
			pay() {
				const selectedOption = this.options[this.current]
				if (!selectedOption) {
					this.$func.showToast('请选择会员套餐')
					return
				}
				
				// 构建支付方式选项
				const paymentOptions = []
				if (this.activationTypes.includes('1')) {
					paymentOptions.push({ value: '1', text: '积分兑换' })
				}
				if (this.activationTypes.includes('2')) {
					paymentOptions.push({ value: '2', text: '激活码兑换' })
				}
				if (this.activationTypes.includes('3')) {
					paymentOptions.push({ value: '3', text: '微信支付' })
				}
				
				// 如果只有一种支付方式，直接执行
				if (paymentOptions.length === 1) {
					this.executePayment(paymentOptions[0].value)
					return
				}
				
				// 多种支付方式，弹出图鸟UI操作菜单
				this.paymentSheetList = paymentOptions.map(option => ({ text: option.text }))
				this.showPaymentSheet = true
			},
			// 支付方式选择回调
			handlePaymentSelect(index) {
				const paymentOptions = []
				if (this.activationTypes.includes('1')) {
					paymentOptions.push({ value: '1', text: '积分兑换' })
				}
				if (this.activationTypes.includes('2')) {
					paymentOptions.push({ value: '2', text: '激活码兑换' })
				}
				if (this.activationTypes.includes('3')) {
					paymentOptions.push({ value: '3', text: '微信支付购买' })
				}
				
				const selectedType = paymentOptions[index].value
				this.executePayment(selectedType)
			},
			// 执行支付逻辑
			executePayment(type) {
				const selectedOption = this.options[this.current]
				if (!selectedOption) {
					this.$func.showToast('请选择会员套餐')
					return
				}
				
				if (type === '1') {
					// 积分兑换模式 - 先检查积分是否足够
					if (!this.userInfo.integral || this.userInfo.integral < selectedOption.integral_required) {
						this.$func.showToast('积分不足，无法兑换')
						return
					}
					// 显示积分兑换确认模态框
					this.integralModalContent = `确定使用${selectedOption.integral_required}积分兑换${selectedOption.name}会员吗？`
					this.selectedPaymentType = type
					this.showIntegralModal = true
				} else if (type === '2') {
					// 激活码兑换模式 - 显示激活码兑换模态框
					this.selectedPaymentType = type
					this.activationCode = ''
					this.showActivationModal = true
				} else if (type === '3') {
					// 支付金额开通模式
					uni.showLoading({
						title: '订单生成中',
						mask: true
					})
					this.$api.apiPayApply({
						vip_type: this.current,
						client_type: 1,
						order_type: 3
					}).then(res => {
						uni.hideLoading()
						if (res.code === 100) {
							this.orderSn = res.data.order_sn
							uni.requestPayment({
								provider:'wxpay',
								timeStamp: String(Date.now()),
								nonceStr: res.data.config.nonceStr,
								package: res.data.config.package,
								signType: res.data.config.signType,
								paySign: res.data.config.paySign
							})
							let _that = this
							_that.timeId = setInterval(function() {
								_that.fetchPayState()
							}, 1000)
							return
						}
						this.$func.showToast(res.msg)
					}).catch(error => {
						uni.hideLoading()
						this.$func.showToast('网络错误，请稍后重试')
					})
				}
			},
			// 积分兑换确认模态框点击回调
			handleIntegralModalClick(event) {
				if (event.index === 0) {
					// 点击取消按钮
					this.showIntegralModal = false
					return
				}

				if (event.index === 1) {
					// 点击确定按钮
					const selectedOption = this.options[this.current]
					uni.showLoading({
						title: '积分兑换中',
						mask: true
					})
					this.$api.apiVipIntegralPay({
					vip_type: 1,// 兑换类型，积分兑换
					package_name: selectedOption.name,// 会员套餐名称
					duration: selectedOption.duration,// 会员套餐时长
					integral_required: selectedOption.integral_required,// 积分兑换需要的积分
				}).then(res => {
					uni.hideLoading()
					if (res && res.code === 1) {
						this.showIntegralModal = false
						this.$func.showToast('积分兑换成功')
						this.fetchUser()
					} else {
						this.showIntegralModal = false
						this.$func.showToast(res && res.msg || '积分兑换失败')
					}
				}).catch(error => {
					uni.hideLoading()
					this.$func.showToast('网络错误，请稍后重试')
				})
				}
			},
			// 跳转联系客服页面
			handleContactCustomerService() {
				uni.navigateTo({ url: '/subpages/common/serviceQrCode' })
			},
			// 激活码兑换模态框点击回调
			handleActivationModalClick(event) {
				if (event.index === 0) {
					// 点击取消按钮
					this.handleActivationModalCancel()
				} else if (event.index === 1) {
					// 点击确认按钮
					this.handleActivationModalConfirm()
				}
			},
			// 激活码兑换模态框取消回调
			handleActivationModalCancel() {
				this.showActivationModal = false
				this.activationCode = ''
			},
			// 激活码兑换模态框确认回调
			handleActivationModalConfirm() {
				const selectedOption = this.options[this.current]
				const activationCode = this.activationCode.trim()
				if (!activationCode) {
					this.$func.showToast('请输入激活码')
					return
				}
				
				uni.showLoading({
					title: '激活码兑换中',
					mask: true
				})
				this.$api.apiVipActivationCode({
					vip_type: 2,// 兑换类型，激活码兑换
					package_name: selectedOption.name,// 会员套餐名称
					duration: selectedOption.duration,// 会员套餐时长
					activation_code: activationCode// 激活码
				}).then(res => {
					uni.hideLoading()
					if (res && res.code === 1) {
						this.$func.showToast('激活成功')
						this.fetchUser()
						this.showActivationModal = false
						this.activationCode = ''
					} else {
						this.$func.showToast(res && res.msg || '激活失败')
					}
				}).catch(error => {
					uni.hideLoading()
					this.$func.showToast('网络错误，请稍后重试')
				})
			},
			fetchPayState() {
				this.$api.apiPayStatus({order_sn: this.orderSn}).then(res => {
					if (res && res.code === 1) {
						// 支付成功
						this.$func.showToast('支付成功')
						this.fetchUser()
						// 清除定时器
						clearInterval(this.timeId)
						this.timeId = 0
					} else if (res && res.code === 2) {
						// 支付失败
						this.$func.showToast('支付失败')
						// 清除定时器
						clearInterval(this.timeId)
						this.timeId = 0
					}
				}).catch(error => {
					// 网络错误或其他异常
					// 清除定时器
					clearInterval(this.timeId)
					this.timeId = 0
				})
			},
			unixtimeToDate(unixtime) {
				const date = new Date(unixtime * 1000)
				const year = date.getFullYear()
				const month = date.getMonth() + 1
				const day = date.getDate()
				return `${year}-${month}-${day}`
			}
		}
	}
</script>

<style lang="scss" scoped>
	@import '@/scss/custom_nav_bar.scss';

	.page {
		display: flex;
		flex-direction: column;
		align-items: center;
		min-height: 100vh;
		background: linear-gradient(180deg, #42B476, #f4f4f4);

		.user {
			background: #fff;
			padding: 30rpx;
			border-radius: 12rpx;
			width: 700rpx;
			margin-bottom: 20rpx;
			display: flex;
			box-sizing: border-box;

			image {
				width: 90rpx;
				height: 90rpx;
				border-radius: 50%;
				margin-right: 30rpx;
			}

			view {
				display: flex;
				flex-direction: column;
				justify-content: space-between;

				text {
					&:nth-child(2) {
						font-size: 28rpx;
						color: #555;
					}

					&.user-integral {
						font-size: 28rpx;
						color: #42B476;
					}
				}
			}
		}

		.option {
			display: flex;
			align-items: center;
			flex-direction: column;
			background: #fff;
			width: 700rpx;
			border-radius: 12rpx;
			margin-bottom: 20rpx;

			.option-title {
				font-weight: bold;
				padding: 30rpx 0;
			}

			.option-list {
				display: flex;
				justify-content: center;

				.option-item {
					width: 200rpx;
					height: 200rpx;
					padding: 20rpx 0;
					margin: 0 8rpx;
					border: 1px solid #42B476;
					border-radius: 12rpx;
					display: flex;
					flex-direction: column;
					align-items: center;
					justify-content: space-between;
					position: relative;
					box-sizing: border-box;

					&.hover-class {
						background: #1b4f30;
					}

					text {
						color: #42B476;

						&:nth-child(2) {
							font-weight: bold;
						}

						&:nth-child(3) {
							font-size: 14px;
							color: #ccc;
						}
					}

					.option-item-line {
						width: 180rpx;
						height: 1px;
						background: #ccc;
						position: absolute;
						bottom: calc(20rpx + 8px);
						left: 10rpx;
						opacity: .8;
					}
				}

				.option-selected {
					background: #1b4f30;
				}
			}

			.option-botton {
				margin: 30rpx 0;
				width: 200rpx;
				height: 90rpx;
				line-height: 90rpx;
				border-radius: 12rpx;
				text-align: center;
				background: #42B476;
				color: #fff;

				&.hover-class {
					background: #2a754c;
				}
			}
		}

		.table {
			width: 700rpx;
			background: #fff;
			border-radius: 12rpx;
			margin-bottom: 20rpx;

			.td {
				display: flex;
				justify-content: space-between;
				min-height: 80rpx;
				line-height: 36rpx;
				padding: 10rpx 0;

				view { 
					color: #444;
					width: 233rpx;
					text-align: center;
					display: flex;
					align-items: center;
					justify-content: center;
					flex-wrap: wrap;
					word-break: break-all;
					max-height: 80rpx;
					overflow: hidden;
				}

				image {
					width: 40rpx;
					height: 40rpx;
					vertical-align: middle;
				}

				text {
					font-size: 28rpx;
					color: #42B476;
					font-weight: 500;
					vertical-align: middle;
					line-height: 36rpx;
					display: inline;
					max-height: 80rpx;
					overflow: hidden;
				}
			}

			.th {
				view {
					font-size: 16px;
					color: #000;
					font-weight: bold;
					line-height: 60rpx;
				}
			}
		}
	}

	/* 激活码模态框样式 */
	.custom-modal-content {
		width: 100%;
		display: flex;
		flex-direction: column;
		align-items: center;
	}
	
		/* 标题样式 */
	.modal-title {
		font-size: 36rpx;
		font-weight: bold;
		color: #303133;
		margin-bottom: 40rpx;
		text-align: center;
		width: 100%;
	}
	/* 表单样式 */
	.tn-form {
		width: 100%;
		margin-bottom: 20rpx;
	}
	
	.tn-form-item {
		padding: 0;
		margin-bottom: 0;
	}
	
	.tn-form-item__label {
		font-size: 28rpx;
		color: #333333;
		font-weight: bold;
	}
	
	/* 输入框样式 */
	.tn-input {
		padding: 0 20rpx;
		width: 100%;
	}
	
	/* 提示文字样式 */
	.custom-modal-hint {
		font-size: 28rpx;
		color: #FF9500;
		margin-top: 20rpx;
		text-align: center;
		line-height: 34rpx;
		width: 100%;
	}
	
	/* 按钮区样式 */
	.custom-modal-buttons {
		display: flex;
		justify-content: center;
		gap: 20rpx;
		width: 100%;
		margin-top: 40rpx;
	}
	
	/* 按钮样式 */
	.tn-button {
		width: 280rpx;
		font-size: 28rpx;
		height: 80rpx;
		border-radius: 8rpx;
		text-align: center;
		line-height: 80rpx;
	}
	
	/* 取消按钮 */
	.cancel-button {
		background-color: #F5F5F5;
		color: #666666;
	}
	
	/* 确认按钮 */
	.confirm-button {
		background-color: #42B476;
		color: #FFFFFF;
	}
</style>