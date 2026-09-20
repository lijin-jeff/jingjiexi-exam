<template>
  <view class="components-time-line tn-safe-area-inset-bottom">
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
          更新历史
        </text>
      </view>
    </tn-nav-bar>
	</view>	

    <!-- 页面内容 -->
    <view :style="{paddingTop: vuex_custom_bar_height + 'px'}">
      <view class="time-line__wrap">
        <tn-time-line>
          <block
            v-for="(item, index) in expressData"
            :key="index"
          >
            <tn-time-line-item
              v-if="item.status !== 0"
              :top="2"
            >
              <template #node>
                <view
                    v-if="item.status === 1"
                    class="time-line-item__node tn-icon-success tn-color-white tn-text-lg tn-bg-indigo tn-round tn-padding-xs"
                  >
                </view>
              </template>
              <template #content>
                <view>
                  <view class="time-line-item__content__title">
                    {{ item.version }}
                  </view>
                  <mp-html class="time-line-item__content__desc" :content="item.info" />
                  <view class="time-line-item__content__time tn-padding-top-sm">
                    {{ item.time }}
                  </view>
                </view>
              </template>
            </tn-time-line-item>
          </block>
        </tn-time-line>
		<view style="padding-bottom: 120rpx;"></view>
      </view>
			
      <!-- 底部悬浮按钮区域 -->
      <view class="tn-flex tn-flex-row-between tn-safe-area-inset-bottom" style="position: fixed; bottom: 20rpx; left: 0; right: 0; z-index: 999;">
        <view class="tn-flex-1 justify-content-item tn-margin-xs tn-text-center">
          <!-- 已订阅状态 -->
          <tn-button
            v-if="isSubscribed"
            class="bottom-button"
            backgroundColor="tn-cool-bg-color-4"
            padding="40rpx 0"
            width="90%"
            font-bold
            @click="unsubscribe"
            :loading="subscribeLoading"
          >
            <text class="tn-icon-check tn-padding-right-xs tn-color-white"></text>
            <text class="tn-color-white">{{ subscribeLoading ? '取消中...' : '已订阅' }}</text>
          </tn-button>
          <!-- 未订阅状态 -->
          <tn-button
            v-else
            class="bottom-button"
            background-color="tn-cool-bg-color-9"
            padding="40rpx 0"
            width="90%"
            font-bold
            @click="subscribe"
            :loading="subscribeLoading"
          >
            <text class="tn-icon-add tn-padding-right-xs tn-color-white"></text>
            <text class="tn-color-white">{{ subscribeLoading ? '订阅中...' : '订阅更新' }}</text>
          </tn-button>
        </view>
      </view>
    </view>
  </view>
</template>

<script>
	import template_page_mixin from '@/libs/mixin/template_page_mixin.js'
import { isUserLoggedIn } from '@/util/userStore.js'
	export default {
		name: 'ComponentsTimeline',
		mixins: [template_page_mixin],
		data() {
			return {
				mainColor: getApp().globalData.mainColor,
				expressData: [],
				subscribeLoading: false, // 加载状态
				isSubscribed: false // 订阅状态
			}
		},
		computed: {
			// 从Vuex获取订阅模板ID
			subscribeTemplates() {
				return this.$store.state.vuex_subscribe_templates || []
			}
		},
		onLoad() {
			this.$tn.mpShare = {
				share: false,
			}
			if (!this.$tn.mpShare.share) {
				uni.hideShareMenu()
			}
			// 获取版本更新列表
			this.getVersionUpdateList()
			this.checkSubscribeStatus()
		},
		methods: {
			// 获取版本更新列表
			getVersionUpdateList() {
				this.$api.apiVersionUpdateList().then(res => {
					if (res.code ==1 && res.data) {
						this.expressData = res.data.map(item => {
							return {
								id: item.id,
								version: item.version,
								info: item.info.replace(/\n/g, '<br>'),
								status: item.status,
								time: this.formatTime(item.release_time)
							}
						})
					}
				})
			},
			// 检查订阅状态
			checkSubscribeStatus() {
				this.$api.apiVersionUpdateSubscribeStatus().then(res => {
					if (res.code == 1 && res.data) {
						// 正确处理API响应，status字段表示订阅状态
						this.isSubscribed = res.data.status === 1
					}
				})
			},
			// 格式化时间
			formatTime(timestamp) {
				if (!timestamp) return ''
				const date = new Date(timestamp * 1000)
				const year = date.getFullYear()
				const month = String(date.getMonth() + 1).padStart(2, '0')
				const day = String(date.getDate()).padStart(2, '0')
				const hours = String(date.getHours()).padStart(2, '0')
				const minutes = String(date.getMinutes()).padStart(2, '0')
				const seconds = String(date.getSeconds()).padStart(2, '0')
				return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`
			},
			// 微信订阅消息
		subscribe() {
			// 防止重复点击
			if (this.subscribeLoading) {
				return
			}
			
			// 检查登录状态
			if (!isUserLoggedIn()) {
				uni.showModal({
					title: '提示',
					content: '请先登录后再进行订阅操作',
					confirmText: '去登录',
					cancelText: '取消',
					success: (res) => {
						if (res.confirm) {
							uni.navigateTo({ url: '/subpages/user/login' })
						}
					}
				})
				return
			}
			
			// 从Vuex获取订阅模板ID
			const templates = this.subscribeTemplates
			// 提取模板ID数组
			const tmplIds = templates.slice(0, 3).map(item => item.template_id);
			
			if (tmplIds.length === 0) {
				this.$func.showToast('暂无可用的订阅模板，请联系管理员')
				return
			}
			
			// 获取最新版本信息作为模板数据
			const latestVersion = this.expressData[0] || {}
			
			// 检查版本信息是否已加载
			if (!latestVersion || !latestVersion.id) {
				this.$func.showToast('版本信息加载中，请稍后再试')
				return
			}
			
			// 设置loading状态
			this.subscribeLoading = true
			
			// 构建模板数据
			const templateData = {
				// 更新内容
				thing1: {
					value: latestVersion.version + '\n' + latestVersion.info.replace(/<br>/g, '\n')
				},
				// 更新时间
				time2: {
					value: latestVersion.time
				},
				// 温馨提示
				thing3: {
					value: '感谢您使用我们的应用，欢迎体验新功能并提出宝贵意见！'
				}
			}
			
			// 使用统一的订阅消息处理函数
			this.$func.handleSubscribeMessage({
				tmplIds: tmplIds,
				apiCall: this.$api.apiSubscribe,
				apiParams: {
					type: 'version_update',
					subscribe_status: 1,
				},
				templateData: templateData,
				isSubscribed: false,
				successCallback: (result) => {
					// 记录订阅授权状态
					const now = Date.now();
					
					// 检查是否有模板被接受
					const hasAccepted = Object.values(result.subscribeResults).some(status => 
						status === 'accept' || status === 'acceptWithAudio' || status === 'acceptWithAlert'
					);
					
					if (hasAccepted) {
						// 只调用一次apiRecordSubscribe，避免重复插入导致唯一键冲突
						// 使用第一个接受的模板ID作为记录
						let acceptedTemplateId = '';
						const acceptedEntry = Object.entries(result.subscribeResults).find(([_, status]) => 
							status === 'accept' || status === 'acceptWithAudio' || status === 'acceptWithAlert'
						);
						if (acceptedEntry) {
							acceptedTemplateId = acceptedEntry[0];
						}
						
						// 调用后端API记录订阅状态
						this.$api.apiRecordSubscribe({
							template_id: acceptedTemplateId,
							subscribe_time: now,
							type: 'version_update',
						}).catch(err => {
							console.error('记录订阅状态失败:', err);
						});
						
						// 订阅成功后更新状态
						this.isSubscribed = true
					}
				},
				failCallback: (err) => {
					console.error('订阅失败:', err);
				},
				completeCallback: () => {
					// 重置loading状态
					this.subscribeLoading = false
				}
			});
		},
		// 取消订阅
		unsubscribe() {
			// 防止重复点击
			if (this.subscribeLoading) {
				return
			}
			
			// 检查登录状态
			if (!isUserLoggedIn()) {
				uni.showModal({
					title: '提示',
					content: '请先登录后再进行取消订阅操作',
					confirmText: '去登录',
					cancelText: '取消',
					success: (res) => {
						if (res.confirm) {
							uni.navigateTo({ url: '/subpages/user/login' })
						}
					}
				})
				return
			}
			
			// 设置loading状态
			this.subscribeLoading = true
			
			// 调用取消订阅API
			this.$api.apiSubscribe({
				type: 'version_update',
				subscribe_status: 0,
			}).then(res => {
				if (res.code == 1) {
					// 更新订阅状态
					this.isSubscribed = false
					this.$func.showToast('取消订阅成功')
				} else {
					this.$func.showToast(res.msg || '取消订阅失败')
				}
			}).catch(err => {
				console.error('取消订阅失败:', err)
				this.$func.showToast('取消订阅失败')
			}).finally(() => {
				// 重置loading状态
				this.subscribeLoading = false
			})
		}
		}

	}
</script>

<style lang="scss" scoped>
	@import "@/scss/custom_nav_bar.scss";
	.time-line {
		&__wrap {
			padding: 60rpx 30rpx 30rpx 60rpx;
		}
		&-item {
			&__node {
				width: 44rpx;
				height: 44rpx;
				border-radius: 100rpx;
				display: flex;
				align-items: center;
				justify-content: center;
				background-color: #AAAAAA;

				&--active {
					background-color: '#05CA8D';
				}
				&--icon {
					color: #FFFFFF;
					font-size: 24rpx;
				}
			}
			&__content {
				&__title {
					font-weight: bold;
					font-size: 32rpx;
				}

				&__desc {
					color: $tn-font-sub-color;
					font-size: 28rpx;
					margin-bottom: 6rpx;
				}
				&__time {
					color: $tn-font-holder-color;
					font-size: 26rpx;
				}
			}
		}
	}
</style>

