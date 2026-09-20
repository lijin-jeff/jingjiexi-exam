<template>
  <view class="page-template tn-safe-area-inset-bottom">
    <!-- 顶部自定义导航开始-->
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
            资源详情
          </text>
        </view>
      </tn-nav-bar>
    </view>

    <!-- 内容区域开始-->
    <view class="tn-margin-top-xs"  :style="{paddingTop: vuex_custom_bar_height + 'px'}">
      <view class="nav_title--wrap">
        <view class="nav_title tn-padding-sm">
          {{ resourceContent.title }}
        </view>
      </view>
      <view class="tn-flex tn-flex-wrap tn-margin-left-sm tn-margin-right-sm tn-color-grey tn-margin-top-sm">
        <view class="tn-padding-right-lg">
          <text class="tn-icon-eye" />
          <text class="tn-padding-left-xs">
            {{ resourceContent.view_count }}
          </text>
        </view>
        <view class="tn-padding-right-lg">
          <text class="tn-icon-my-simple" />
          <text class="tn-padding-left-xs">
            {{ resourceContent.author }}
          </text>
        </view>
        <view class="tn-padding-right-lg">
          <text class="tn-icon-calendar" />
          <text class="tn-padding-left-xs">
            {{ resourceContent.year + `年` }}
          </text>
        </view>
        <view class="tn-padding-right-lg" v-if="resourceContent.purchase_state">
          <text class="tn-icon-success tn-color-green" />
          <text class="tn-padding-left-xs tn-color-green">
            已购买
          </text>
        </view>
        <view class="tn-padding-right-lg" v-else-if="resourceContent.free_state === '2'">
          <text class="tn-icon-price-tag tn-color-orange" />
          <text class="tn-padding-left-xs tn-color-orange">
            {{ resourceContent.money }}积分
          </text>
        </view>
        <view class="tn-padding-right-lg" v-else>
          <text class="tn-icon-free tn-color-green" />
          <text class="tn-padding-left-xs tn-color-green">
            免费
          </text>
        </view>
      </view>

      <view
        class="tn-padding-right-sm tn-padding-left-sm tn-margin-top-sm"
        style="padding-bottom: 110rpx;"
      >
        <mp-html :content="resourceContent.remark" />
      </view>
    </view>
    <!-- 内容区域结束 -->

    <!-- 资源预览区域 -->
    <view v-if="resourceContent.preview_url || resourceContent.cover" class="tn-padding-sm">
      <view class="tn-text-center tn-text-lg tn-margin-bottom-sm">资源预览</view>
      <view class="preview-container tn-border tn-border-grey tn-radius-lg tn-overflow-hidden">
        <image 
          v-if="resourceContent.cover" 
          :src="resourceContent.cover" 
          mode="aspectFit" 
          class="preview-image"
          @tap="previewResource"
        />
        <view v-else class="no-preview tn-flex tn-flex-col-center tn-justify-center tn-align-center tn-height-lg">
          <text class="tn-icon-file-text tn-text-grey tn-text-2xl"></text>
          <text class="tn-text-grey tn-margin-top-sm">暂无预览图</text>
        </view>
      </view>
      <view v-if="resourceContent.preview_url" class="tn-text-center tn-margin-top-sm">
        <tn-button @click="previewResource" size="small" type="primary" plain>
          <text class="tn-icon-eye tn-margin-right-xs"></text>
          查看完整预览
        </tn-button>
      </view>
    </view>

    <!-- 底部菜单开始-->
    <view
      class="tn-flex"
      style="height: 100rpx; width: 100%; line-height: 100rpx; position: fixed; bottom: 0rpx;background-color: #fff;"
    >
      <view
        class="tn-flex tn-flex-col-center"
        style="width: 50%;font-size: 30rpx;"
        @click="showModal"
      >
        <view class="tn-flex tn-flex-direction-row tn-padding-left-lg">
          <!-- <image src="/static/customer.png" style="width: 60rpx; height:60rpx;"></image> -->
          <text class="tn-icon-my-add tn-text-bold" />
        </view>
        <view class="tn-padding-left-sm">
          <text>联系客服</text>
        </view>
      </view>
      <view
        class="tn-flex tn-flex-col-center tn-flex-row-right"
        style="width: 45%; line-height: 40rpx; text-align: center;"
      >
        <!-- <view class="bottom-menu" @click="submitCollection(1)">
					<view class="bottom-menu--icon">
						<text v-if="resourceContent.is_click" class="tn-icon-praise-fill tt-text-main-color"></text>
						<text v-else class="tn-icon-praise"></text>
					</view>
					<view class="bottom-menu--text">
						<text>{{resourceContent.click_count}}</text>
					</view>
				</view> -->
        <!-- <view
          class="bottom-menu"
          @click="subscribleMessage"
        >
          <view class="bottom-menu--icon">
            <text class="tn-icon-notice" />
          </view>
          <view class="bottom-menu--text">
            <text>订阅</text>
          </view>
        </view> -->
        <view
          class="bottom-menu"
          @click="submitCollection()"
        >
          <view class="bottom-menu--icon">
            <text
              v-if="resourceContent.collection_state"
              class="tn-icon-star-fill" :style="{color: mainColor}"
            />
            <text
              v-else
              class="tn-icon-star"
            />
          </view>
          <view class="bottom-menu--text">
            <text>收藏</text>
          </view>
        </view>
        <view
          class="bottom-menu"
          @click="downloadClick"
        >
          <view class="bottom-menu--icon">
            <text class="tn-icon-download" />
          </view>
          <view class="bottom-menu--text">
            <text>下载</text>
          </view>
        </view>
      </view>
    </view>
    <!-- 底部菜单结束 -->
    <!-- 联系客服模态框 -->
    <tn-modal
      v-model="show1"
      :custom="true"
    >
      <view class="custom-modal-content">
        <image
          :src="serviceQrCode"
          mode="aspectFill"
          style="width: 100%;"
          @tap="previewQRCodeImage"
        />
        <view
          class="tn-text-center tn-padding-top"
          @click="copyWechat"
        >
          <text class="">
            客服微信：{{ customerWechat }}
          </text>
          <text class="tn-color-blue--disabled tn-padding-left-xs tn-text-df tn-icon-copy" />
        </view>
        <view class="tn-text-center tn-padding-top tn-text-lg">
          点击上图，可识别微信二维码
        </view>
      </view>
    </tn-modal>
    
    <!-- 操作菜单组件 -->
    <tn-action-sheet
      v-model="showActionSheet"
      :list="actionSheetOptions"
      @click="handleActionSheetClick"
      :tips="{ text: '下载方式选择' }"
      :cancelBtn="true"
    />
    
    <!-- 积分下载模态框 -->
    <tn-modal
      v-model="showPointsModal"
      :title="pointsModalContent.title"
      :content="pointsModalContent.content"
      :button="pointsModalContent.button"
      @click="handlePointsModalClick"
    />
    
    <!-- 广告下载模态框 -->
    <tn-modal
      v-model="showAdModal"
      :title="adModalContent.title"
      :content="adModalContent.content"
      :button="adModalContent.button"
      @click="handleAdModalClick"
    />
    
    <!-- 激励视频广告-->
    <!-- 微信小程序广告无需预加载，仅在需要时显示 -->
    <RewardedVideoAd
      v-if="showAd"
      :ad-unit-id="rewardedAdUnitId"
      button-text="观看广告获取奖励"
      @ad-loaded="handleAdLoaded"
      @ad-error="handleAdError"
      @ad-close="handleAdClose"
      @ad-complete="handleAdComplete"
    />
    <!-- 激励视频广告-->
  </view>
</template>

<script>
	import template_page_mixin from '@/libs/mixin/template_page_mixin.js'
	import RewardedVideoAd from "./ad/RewardedVideoAd.vue"
	import { checkVipStatusSync } from '@/util/userStore.js'
	export default {
		components: {
			RewardedVideoAd
		},
		mixins: [template_page_mixin],
		data() {
			return {
				mainColor: getApp().globalData.mainColor,
				serviceQrCode: getApp().globalData.serviceQrCode,
				customerWechat: getApp().globalData.customerWechat,
				uid: '',
				showAd: false,
				show1: false,
				resourceContent: {},
				// 用户积分
				userPoints: 0,
				// 广告配置：1-会员免广告，2-全部用户免广告
				adConfig: getApp().globalData.otherSettings.adConfig || 2,
				// 操作菜单状态
				showActionSheet: false,
				// 操作菜单选项
				actionSheetOptions: [],
				// 积分下载模态框相关
				showPointsModal: false,
				pointsModalContent: {
					title: '💎 积分下载',
					content: '使用 0 积分下载该资源，一次支付，终身免费下载！\n\n如果积分不足，你还可以选择观看广告免费下载。',
					button: [
						{
							text: '📺 看广告免费下载',
							backgroundColor: '#E6E6E6',
							fontColor: '#333333'
						},
						{
							text: '✅ 确认支付',
							backgroundColor: '#42B476',
							fontColor: '#FFFFFF'
						}
					]
				},
				// 广告下载模态框相关
				showAdModal: false,
				adModalContent: {
					title: '广告下载',
					content: '',
					button: [
						{
							text: '开通会员',
							backgroundColor: '#E6E6E6',
							fontColor: '#333333'
						},
						{
							text: '确认下载',
							backgroundColor: '#42B476',
							fontColor: '#FFFFFF'
						}
					]
				},
				// 激励视频广告单元ID，从全局配置获取或使用默认值
				rewardedAdUnitId: getApp().globalData.otherSettings.rewardedAdUnitId || 'adunit-f42d23471e903ed9'
			}
		},
		onLoad(params) {
			this.uid = params.uid
			// #ifdef MP-WEIXIN
			this.$tn.mpShare = {
				share: true,
			}
			if (!this.$tn.mpShare.share) {
				uni.hideShareMenu()
			}
			// #endif
			this.fetchResourceContent()
		},
		methods: {
			// 广告加载成功
			handleAdLoaded() {
				uni.hideLoading()
				console.log("激励视频广告加载成功");
			},
			// 广告加载失败
			handleAdError(error) {
				this.$func.showToast('广告加载失败，请稍后重试或选择其他下载方式');
				console.error("激励视频广告加载失败", error);
				this.showAd = false;
				uni.hideLoading();
			},
			// 广告关闭（用户未完整观看）
			handleAdClose() {
				this.$func.showToast('需要观看完整广告才能获取下载权限哦~')
				console.log("用户未完整观看激励视频广告");
				this.showAd = false;
			},
			// 广告播放完成（用户完整观看）
			handleAdComplete() {
				console.log("用户完整观看了激励视频广告，发放奖励");
				this.fetchResourceUrl()
				this.showAd = false;
				// 这里可以添加发放奖励的逻辑
			},
			// 复制微信
			copyWechat() {
				this.$func.setClipboardData(this.customerWechat)
			},
			// 预览作者图片
			previewQRCodeImage() {
				this.$func.showServiceImage()
			},
			
			// 弹出模态框
			showModal(event) {
				this.openModal()
			},
			// 打开模态框
			openModal() {
				this.show1 = true
			},
			subscribleMessage() {
				this.$func.templateSubscribe('resource_update')
			},
			submitCollection(type) {
				if (this.resourceContent.collection_state) {
					this.$func.showToast("你已收藏")
					return
				}
				this.$api.apiResourceCollection({
					uid: this.uid,
				}).then(res => {
					this.$func.showToast(res.msg)
					if (res.code === 1) {
						this.resourceContent.collection_state = true
					}
				})
			},
			downloadClick() {
				// 检查是否已购买该资源，如果已购买直接下载
				if (this.resourceContent.purchase_state) {
					this.$func.showToast('该资源已购买，正在为您准备下载链接')
					this.fetchResourceUrl()
					return
				}
				
				// 检查是否为付费下载
				if (this.resourceContent.free_state === '2') {
					// 设置操作菜单选项，添加更明确的描述
					this.actionSheetOptions = [
						{
							text: `💎 使用积分下载 (${this.resourceContent.money}积分)`,
							value: 'points',
						
desc: '一次支付，终身免费下载'
						},
						{
							text: `📺 观看广告免费下载`,
							value: 'ad',
						
desc: '无需积分，观看广告即可下载'
						}
					]
					// 显示操作菜单
					this.showActionSheet = true
					return
				}
				
				// 检查广告配置
				const isVip = checkVipStatusSync()
				
				if (this.adConfig == 2 || (this.adConfig == 1 && isVip)) {
					// 全部用户免广告或会员免广告，直接下载
					this.$func.showToast('正在为您准备下载链接')
					this.fetchResourceUrl()
					return
				}
				
				// 非会员用户或未开启免广告，需要观看广告
				this.adModalContent.content = '仅会员免广告下载，其他用户需观看广告'
				this.showAdModal = true
			},
			fetchResourceUrl() {
				let _that = this
				uni.showLoading({
					title: '正在生成下载链接',
					mask: true
				})
				this.$api.apiResourceDownload({
					uid: this.uid
				}).then(result => {
					uni.hideLoading()
					if (result.code == 1) {
						uni.showModal({
							title: '✅ 下载链接已生成',
							content: '链接复制成功后，建议使用浏览器打开下载',
							confirmText: '复制链接',
							cancelText: '暂不复制',
							confirmColor: '#42B476',
							cancelColor: '#999999',
							success(res) {
								if (res.confirm) {
									_that.$func.setClipboardData(result.data.file_url, '✅ 链接复制成功')
								}
							}
						})
					} else {
						this.$func.showToast(result.msg || '生成下载链接失败，请稍后重试')
					}
				}).catch(err => {
					uni.hideLoading()
					this.$func.showToast('网络请求失败，请检查网络连接后重试')
					console.error('获取下载链接失败:', err)
				})
			},
			// 操作菜单点击事件处理
			handleActionSheetClick(index) {
				// 关闭操作菜单
				this.showActionSheet = false
				
				const option = this.actionSheetOptions[index]
				if (option.value === 'points') {
					// 确保resourceContent已加载
					if (!this.resourceContent || !this.resourceContent.money) {
						this.$func.showToast('资源信息加载中，请稍后重试')
						return
					}
					// 检查积分是否足够
			if (parseFloat(this.userPoints) < parseFloat(this.resourceContent.money)) {
				this.$func.showToast(`积分不足，您当前有 ${this.userPoints} 积分，需要 ${this.resourceContent.money} 积分`)
				// 自动切换到广告下载选项
				this.adModalContent.content = '您的积分不足，观看广告即可免费下载'
				this.showAdModal = true
				return
			}
					// 更新积分下载模态框内容，显示用户当前积分
					this.pointsModalContent.content = `您当前有 ${this.userPoints} 积分，使用 ${this.resourceContent.money} 积分下载该资源，一次支付，终身免费下载！\n\n如果取消，你还可以选择观看广告免费下载。`
					// 显示积分下载模态框
					this.showPointsModal = true
				} else if (option.value === 'ad') {
					// 设置广告下载模态框内容
					this.adModalContent.content = this.adConfig == 2 ? '此资源，全部用户免广告可直接下载' : '仅会员免广告下载，其他用户需观看广告'
					// 显示广告下载模态框
					this.showAdModal = true
				}
			},
			// 积分下载模态框点击事件处理
			handlePointsModalClick(event) {
				// 关闭模态框
				this.showPointsModal = false
				
				if (event.index === 0) {
					// 看广告下载
					uni.showLoading({
						title: '广告加载中',
						mask: true
					})
					this.showAd = true
				} else if (event.index === 1) {
					// 确认支付，使用积分下载
					this.downloadByPoints()
				}
			},
			// 广告下载模态框点击事件处理
			handleAdModalClick(event) {
				// 关闭模态框
				this.showAdModal = false
				
				if (event.index === 0) {
					// 开通会员
					this.$func.navigateTo('/subpages/user/member')
				} else if (event.index === 1) {
					// 确认下载，观看广告
					uni.showLoading({
						title: '广告加载中',
						mask: true
					})
					this.showAd = true
				}
			},
			// 积分下载方法
		downloadByPoints() {
			// 确保积分支付后不会弹出广告
			this.showAd = false
			uni.showLoading({
				title: '💎 积分扣除中',
				mask: true
			})
			
			this.$api.apiResourceDownloadPoints({
				uid: this.uid
			}).then(result => {
				uni.hideLoading()
				// 确保积分支付后不会弹出广告
				this.showAd = false
				if (result.code == 1) {
					uni.showModal({
						title: '✅ 下载链接已生成',
						content: '链接复制成功后，建议使用浏览器打开下载',
						confirmText: '复制链接',
						cancelText: '暂不复制',
						confirmColor: '#42B476',
						cancelColor: '#999999',
						success(res) {
							if (res.confirm) {
								this.$func.setClipboardData(result.data.file_url, '✅ 链接复制成功')
							}
						}
					})
					// 更新用户积分
					this.userPoints = parseFloat(this.userPoints) - parseFloat(this.resourceContent.money)
				} else {
					this.$func.showToast(result.msg || '积分下载失败，请稍后重试')
				}
			}).catch(err => {
				uni.hideLoading()
				this.$func.showToast('网络请求失败，请检查网络连接后重试')
				console.error('积分下载失败:', err)
			})
		},
			fetchResourceContent() {
				uni.showLoading({
					title: '努力加载中',
					mask: true
				})
				this.$api.apiResourceContent({
					uid: this.uid
				}).then(res => {
					uni.hideLoading()
					this.resourceContent = res.data
					// 获取用户积分信息
					this.fetchUserPoints()
				})
			},
			// 获取用户积分
			fetchUserPoints() {
				this.$api.apiUserInfo().then(res => {
					if (res.code === 1) {
						this.userPoints = res.data.integral || 0
					}
				})
			},
			// 预览资源
			previewResource() {
				if (this.resourceContent.preview_url) {
					// 如果有预览链接，打开预览页面
					uni.navigateTo({
						url: `/subpages/resource/preview?url=${encodeURIComponent(this.resourceContent.preview_url)}&title=${encodeURIComponent(this.resourceContent.title)}`
					})
				} else if (this.resourceContent.cover) {
					// 如果只有封面图，预览图片
					uni.previewImage({
						urls: [this.resourceContent.cover],
						success: function(res) {
							console.log('预览图片成功');
						}
					})
				} else {
					this.$func.showToast('暂无预览资源');
				}
			}
		},
		onShareAppMessage() {
			return {
				title: this.resourceContent.title,
				imageUrl: this.resourceContent.cover,
				desc: this.resourceContent.title,
				path: '/subpages/resource/content?uid=' + this.resourceContent.uid,
				bgImgUrl: this.cover,
			}
		},
		onShareTimeline() {
			return {
				title: this.resourceContent.title,
				imageUrl: this.resourceContent.cover,
				desc: this.resourceContent.title,
				path: '/subpages/resource/content?uid=' + this.resourceContent.uid,
				bgImgUrl: this.resourceContent.cover,
			}
		}
	}
</script>

<style lang="scss" scoped>
	@import "@/scss/custom_nav_bar.scss";

	// 底部操作按钮开始
	.bottom-menu {
		width: 25%;

		&--icon {
			font-size: 34rpx;
		}

		&--text {
			font-size: 20rpx;
		}
	}
	.nav_title {
		-webkit-background-clip: text;
		background-clip: text;

		&--wrap {
			position: relative;
			display: flex;
			// height: 120rpx;
			font-size: 30rpx;
			align-items: center;
			// justify-content: center;
			// font-weight: bold;
			background-image: url('https://datiqiniu.allpp.cn/static/title00.png');
			background-size: cover;
		}
	}
	
	// 资源预览样式
	.preview-container {
		width: 100%;
		min-height: 300rpx;
		background-color: #f5f5f5;
		
		.preview-image {
			width: 100%;
			height: 400rpx;
			background-color: #fff;
		}
		
		.no-preview {
			padding: 50rpx;
		}
	}
	
	.page-template {
		min-height: 100vh;
		// background-color: #E6FBFB;
	}
</style>

