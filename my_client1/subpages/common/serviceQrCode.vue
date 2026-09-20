<template>
  <view class="components-time-line">
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
      <view class="tn-flex tn-flex-col-center tn-flex-row-center ">
        <text class="tn-text-bold tn-text-xl tn-color-white">
          在线客服
        </text>
      </view>
    </tn-nav-bar>
	</view>	

    <!-- 页面内容 -->
    <view :style="{paddingTop: vuex_custom_bar_height + 'px'}">
      <view class="customer-service-container tn-margin-lg tn-padding-lg">
        <view class="title">
          添加客服二维码
        </view>
        <view class="divider" />

        <view
          class="qrcode-container"
          @click="imagePreview"
        >
          <image
            class="qrcode-image"
            :src="serviceQrCode"
            mode="aspectFit"
          />
        </view>

        <view class="service-info">
          <text class="service-text">
            长按添加客服或拨打客服热线
          </text>
          <text
            class="service-phone"
            @tap="callService"
          >
            {{ mobile }}
          </text>
        </view>
		<view
          class="tn-text-center tn-padding-top"
          @click="copyWechat"
        >
          <text class="">
            客服微信：{{ customerWechat }}
          </text>
          <text class="tn-color-blue--disabled tn-padding-left-xs tn-text-df tn-icon-copy" />
        </view>

        <view class="service-time">
          <text>服务时间: 早上 9:30 到 19:00</text>
        </view>
      </view>
    </view>
  </view>
</template>

<script>
	import template_page_mixin from '@/libs/mixin/template_page_mixin.js'
	export default {
		name: 'ComponentsTimeline',
		mixins: [template_page_mixin],
		data() {
			return {
				mainColor: getApp().globalData.mainColor,
				mobile: getApp().globalData.mobile,
				serviceQrCode: getApp().globalData.serviceQrCode || 'https://datiqiniu.allpp.cn/static/qrCode.png',
				customerWechat: getApp().globalData.customerWechat || 'customerWechat'	
			}
		},
		onLoad() {
			// #ifdef MP-WEIXIN
			this.$tn.mpShare = {
				share: true,
			}
			if (!this.$tn.mpShare.share) {
				uni.hideShareMenu()
			} else {
				uni.showShareMenu({
					withShareTicket: true,
					menus: ['shareAppMessage', 'shareTimeline']
				})
			}
			// #endif
		},
		methods: {
			/**
			 * 返回上一页
			 */
			goBack() {
				uni.navigateBack()
			},
			
			// 复制微信
			copyWechat() {
				this.$func.setClipboardData(this.customerWechat)
			},
			/**
			 * 预览二维码图片
			 */
			imagePreview() {
				if (this.serviceQrCode) {
					// 使用框架提供的方法或原生方法
					if (typeof this.$func.showServiceImage === 'function') {
						this.$func.showServiceImage()
					} else {
						// 降级使用原生方法
						uni.previewImage({
							urls: [this.serviceQrCode]
						})
					}
				}
			},
			
			/**
			 * 拨打电话
			 */
			callService() {
				uni.makePhoneCall({
					phoneNumber: this.mobile
				})
			},
			// 分享给朋友
			onShareAppMessage() {
				return {
					title: '在线客服',
					path: '/subpages/common/serviceQrCode',
					imageUrl: this.serviceQrCode || '',
					desc: '在线客服联系方式'
				}
			},
			// 分享到朋友圈
			onShareTimeline() {
				return {
					title: '在线客服',
					query: '',
					imageUrl: this.serviceQrCode || '',
					success: function() {
						console.log('分享到朋友圈成功')
					}
				}
			}
		}
	}
</script>

<style lang="scss" scoped>
	@import "@/scss/custom_nav_bar.scss";

	.customer-service-container {
		background-color: #fff;
		display: flex;
		flex-direction: column;
		align-items: center;
		border-radius: 10rpx;
	}

	.title {
		font-size: 32rpx;
		font-weight: bold;
		text-align: center;
		margin-bottom: 20rpx;
	}

	.divider {
		height: 1rpx;
		background-color: #eee;
		width: 100%;
		margin-bottom: 40rpx;
	}

	.qrcode-container {
		margin: 30rpx 0;
		padding: 20rpx;
		display: flex;
		justify-content: center;
	}

	.qrcode-image {
		width: 400rpx;
		height: 400rpx;
	}

	.service-info {
		display: flex;
		flex-direction: column;
		align-items: center;
		margin-top: 20rpx;
	}

	.service-text {
		font-size: 28rpx;
		color: #333;
		margin-bottom: 10rpx;
	}

	.service-phone {
		font-size: 30rpx;
		color: #007AFF;
		margin-top: 10rpx;
	}

	.service-time {
		margin-top: 30rpx;
		font-size: 26rpx;
		color: #666;
	}
</style>

