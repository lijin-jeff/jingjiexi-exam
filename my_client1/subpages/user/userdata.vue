<template>
  <view class="tn-safe-area-inset-bottom">
    <!-- 顶部自定义导航-->
    <view class="tn-navbg" :style="{height: vuex_custom_bar_height + 'px'}">
		<tn-nav-bar fixed alpha customBack>
		<template #back>
			<view
			class="tn-custom-nav-bar__back"
			>
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
			用户资料
			</text>
		</view>
		</tn-nav-bar>
	</view>

    <view
      class="tn-margin-top"
      :style="{paddingTop: vuex_custom_bar_height + 'px'}"
    >
      <view class="tn-flex tn-flex-row-between tn-strip-bottom tn-padding">
        <view class="justify-content-item">
          <view class="tn-text-bold tn-text-lg">
            {{ userInfo.nickname }}
          </view>
          <view class="tn-color-gray tn-padding-top-xs">
            {{ userInfo.remark }}
          </view>
        </view>
        <view class="justify-content-item tn-text-lg tn-color-grey">
          <view class="logo-pic tn-shadow">
            <view class="logo-image">
              <view
                class="tn-shadow-blur"
                :style="'background-image:url('+ userInfo.avatar +');width: 80rpx;height: 80rpx;background-size: cover;'"
              />
            </view>
          </view>
        </view>
      </view>
    </view>

    <!-- 修改个人备注开始-->
    <view class="tn-flex tn-flex-row-between tn-strip-bottom-min tn-padding">
      <view class="justify-content-item">
        <view class="tn-color-gray tn-padding-top-xs">
          {{ userInfo.remark }}
        </view>
      </view>
      <view class="justify-content-item tn-text-lg tn-color-grey">
        <view class="tn-icon-right tn-padding-top" />
      </view>
    </view>
    <!-- 修改个人备注结束 -->




    <!-- 性别修改开始-->
    <picker>
      <view class="tn-flex tn-flex-row-between tn-strip-bottom-min tn-padding">
        <view class="justify-content-item">
          <view class="tn-text-bold tn-text-lg">
            *性别
          </view>
          <view class="tn-color-gray tn-padding-top-xs">
            <view class="tn-color-gray">
              {{ userInfo.sex }}
            </view>
          </view>
        </view>
        <view class="justify-content-item tn-text-lg tn-color-grey">
          <view class="tn-icon-right tn-padding-top" />
        </view>
      </view>
    </picker>
    <!-- 性别修改结束 -->
  </view>
</template>

<script>
	import template_page_mixin from '@/libs/mixin/template_page_mixin.js'
	export default {
		name: 'TemplateSet',
		components: {
			
		},
		mixins: [template_page_mixin],
		data() {
			return {
				mainColor: getApp().globalData.mainColor,
				userInfo: {
					sex: 0,
					nickname: '',
					avatar: 'https://datiqiniu.allpp.cn/static/userinfo_avatar.png',
					remark: ''
				},
			}
		},
		onShow() {
			this.loading = true
			this.getUserInfo()
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
		},
		methods: {
			// 获取用户信息
			getUserInfo() {
				this.$api.apiUserInfo().then(res => {
					this.loading = false
					if (res.code === 1) {
						this.userInfo = res.data
						return
					}
					this.$func.showToast(res.msg)
				}).catch(error => {
					this.loading = false
					console.error('获取用户信息失败:', error)
					this.$func.showToast('获取用户信息失败')
				})
			},
		}
	}
</script>

<style lang="scss" scoped>
	@import '@/scss/custom_nav_bar.scss';
	/* 胶囊*/
	.tn-custom-nav-bar__back {
		width: 100%;
		height: 100%;
		position: relative;
		display: flex;
		justify-content: space-evenly;
		align-items: center;
		box-sizing: border-box;
		background-color: rgba(0, 0, 0, 0.15);
		border-radius: 1000rpx;
		border: 1rpx solid rgba(255, 255, 255, 0.5);
		color: #FFFFFF;
		font-size: 18px;

		.icon {
			display: block;
			flex: 1;
			margin: auto;
			text-align: center;
		}

		&:before {
			content: " ";
			width: 1rpx;
			height: 110%;
			position: absolute;
			top: 22.5%;
			left: 0;
			right: 0;
			margin: auto;
			transform: scale(0.5);
			transform-origin: 0 0;
			pointer-events: none;
			box-sizing: border-box;
			opacity: 0.7;
			background-color: #FFFFFF;
		}
	}

	/* 授权 */
	.login-page {
		width: 100vw;
		height: 100vh;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	/* 授权按钮 */
	.submit-btn {
		width: 100%;
		background-color: #05C160;
		color: #FFFFFF;
		margin-top: 60rpx;
		border-radius: 10rpx;
		padding: 25rpx;
		font-size: 32rpx;
		display: flex;
		align-items: center;
		justify-content: center;
		margin: 30rpx;
	}

	/* 间隔线 start*/
	.tn-strip-bottom-min {
		width: 100%;
		border-bottom: 1rpx solid #F8F9FB;
	}

	.tn-strip-bottom {
		width: 100%;
		border-bottom: 20rpx solid rgba(241, 241, 241, 0.8);
	}

	/* 间隔线 end*/


	/* 用户头像 start */
	.logo-image {
		width: 80rpx;
		height: 80rpx;
		position: relative;
	}

	.logo-pic {
		background-size: cover;
		background-repeat: no-repeat;
		// background-attachment:fixed;
		background-position: top;
		border: 2rpx solid rgba(255, 255, 255, 0.05);
		box-shadow: 0rpx 0rpx 80rpx 0rpx rgba(0, 0, 0, 0.15);
		border-radius: 50%;
		overflow: hidden;
		// background-color: #FFFFFF;
	}


	/* 底部悬浮按钮 start*/
	.tn-tabbar-height {
		min-height: 100rpx;
		height: calc(120rpx + env(safe-area-inset-bottom) / 2);
	}

	.tn-footerfixed {
		position: fixed;
		width: 100%;
		bottom: calc(30rpx + env(safe-area-inset-bottom));
		z-index: 1024;
		box-shadow: 0 1rpx 6rpx rgba(0, 0, 0, 0);

	}

	/* 底部悬浮按钮 end*/
</style>

