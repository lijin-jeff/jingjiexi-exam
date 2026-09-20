<template>
  <view class="container">
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
		<view class="tn-flex tn-flex-col-center tn-flex-row-center">
			<text class="tn-text-bold tn-text-xl tn-color-white">
			题库主页
			</text>
		</view>
		</tn-nav-bar>
	</view>

    <!-- Banner -->
    <view
      v-if="bannerList && bannerList.length > 0"
      class="banner"
      :style="{paddingTop: vuex_custom_bar_height + 'px'}"
    >
      <tn-swiper
        :list="bannerList"
        :radius="0"
      />
    </view>

    <!-- 题库信息 -->
    <view class="info-section">
      <view class="user-info">
        <image
          class="avatar"
          src="https://datiqiniu.allpp.cn/static/userinfo_avatar.png"
          @error="handleImageError"
        />
        <view
          class="user-details"
          @click="tl('/subpages/common/serviceQrCode')"
        >
          <view class="user-id">
             {{ questionContent.title || '题库标题' }}
          </view>
          <view class="meta-info">
            <text>{{ questionContent.create_time || '未知时间' }}</text>
            <text class="separator">
              |
            </text>
            <text>{{ questionContent.question_count || 0 }}题</text>
            <text class="separator">
              |
            </text>
            <text>来源 {{ questionContent.author || '未知' }}</text>
          </view>
        </view>
      </view>
    </view>

    <!-- 题库练习 -->
    <view
      v-if="practiceItems.length > 0"
      class="practice-section"
    >
      <text class="section-title">
        功能总览
      </text>
      <view class="grid">
        <view
          v-for="(item, index) in practiceItems"
          :key="index"
          class="grid-item tn-margin-bottom-sm"
          @click="handleGridItemClick(item)"
        >
          <view>
            <view class="grid-title">
              {{ item.title || '功能' }}
            </view>
            <view class="grid-desc">
              {{ item.desc || '暂无描述' }}
            </view>
          </view>
          <view class="grid-icon-wrapper">
            <view
              :class="'tn-icon-' + (item.icon || 'help')"
              :style="{color: item.iconColor || '#666', fontSize: 56 + 'rpx'}"
            />
          </view>
        </view>
      </view>
    </view>
    <view
      v-else
      class="no-data"
    >
      <text>暂无功能</text>
    </view>
  </view>
</template>

<script>
	import template_page_mixin from '@/libs/mixin/template_page_mixin.js'
	export default {
		name: 'QuestionLib',
		mixins: [template_page_mixin],
		data() {
			return {
				mainColor: getApp().globalData.mainColor,
				customerWechat: getApp().globalData.customerWechat,
				bannerList: [],
				questionUid: '',
				questionCount: 0, // 题库总题数				
				questionContent: {},
				practiceItems: [],
				loading: false,
				error: null
			}
		},
		onLoad(option) {
		// 参数验证
		if (!option || !option.uid) {
			this.$func.showToast('题库信息错误')
			setTimeout(() => {
				uni.navigateBack()
			}, 1500)
			return
		}
		
		this.questionUid = option.uid
		
		// 并行处理 API 请求，提高加载速度
		Promise.all([
			this.fetchQuestionDetail(),
			this.fetchQuestionMenu(),
			this.fetchBanner()
		]).finally(() => {
			// 所有请求完成后，可以在这里添加额外的处理逻辑
		})
		
		// 页面加载时开启分享功能（必须调用，否则非按钮组件无法触发分享）		
		// // 添加环境判断，确保只在微信小程序环境中执行		
		// // #ifdef MP-WEIXIN
		try {
			wx.showShareMenu({
				withShareTicket: true,
				menus: ['shareAppMessage', 'shareTimeline'] // 支持好友和朋友圈
			})
		} catch (error) {
			console.warn('开启分享功能失败', error)
		}
		// #endif
	},
		methods: {
			fetchQuestionDetail() {
				// 显示加载状态				
				uni.showLoading({
					title: '加载中..',
					mask: true
				})
				
				return this.$api.apiQuestionDetail({
					uid: this.questionUid
				}).then(res => {
					uni.hideLoading()
					if (res.code === 1 && res.data) {
						this.questionContent = res.data
						this.questionCount = res.data.question_count || 0
					} else {
						this.$func.showToast(res.msg || '获取题库信息失败')
					}
				}).catch(error => {
					uni.hideLoading()
					console.error('获取题库信息失败:', error)
					this.$func.showToast('网络错误，请检查网络连接后重试')
				})
			},
			fetchQuestionMenu() {
				return this.$api.apiQuestionMenu({
					uid: this.questionUid
				}).then(res => {
					if (res.code === 1 && res.data) {
						this.practiceItems = Array.isArray(res.data) ? res.data : []
					} else {
						console.warn('获取功能菜单失败:', res.msg)
						this.practiceItems = []
					}
				}).catch(error => {
					console.error('获取功能菜单失败:', error)
					this.practiceItems = []
				})
			},
			fetchBanner() {
				return this.$api.apiImageConfig({
					type: 'image_banner',
					position: 'exam_menu_top',
					client: this.$func.currentPlatform()
				}).then(res => {
					if (res.code === 1 && res.data) {
						this.bannerList = (Array.isArray(res.data) ? res.data : []).map(item => ({
							...item,
							// 确保image字段是字符串而不是对象								
							image: typeof item.image === 'string' ? item.image : (item.image && item.image.url ? item.image.url : ''),
							// 添加默认标题，防止显示空值								
							title: item.title || '题库推荐'
						}))
					} else {
						this.bannerList = []
					}
				}).catch(error => {
					console.error('获取Banner失败:', error)
					this.bannerList = []
				})
			},
			tl(url) {
				this.$func.navigatorTo(url)
			},
			goBack() {
				uni.navigateBack();
			},
			
			// 处理图片加载错误
			handleImageError(e) {
				console.warn('图片加载失败:', e)
				// 可以在这里设置默认图片			
				},
			
			// 显示错误信息
			showError(message) {
				this.error = message
				this.$func.showToast(message)
				setTimeout(() => {
					this.error = null
				}, 3000)
			},
			handleGridItemClick(row) {
				// 参数验证
				if (!row || typeof row !== 'object') {
					this.$func.showToast('功能异常，请重试')
					return
				}
				
				// 检查功能是否可用				
				if (!row.type && !row.url) {
					this.$func.showToast('暂未开放')
					return
				}
				
				// 使用 try-catch 确保跳转逻辑的稳定性
				try {
					if (row.questions_type === 8) {
						// 开始练习						
						const url = `/subpages/exam/questionSetting?uid=${this.questionUid}&questions_type=${row.questions_type}&question_count=${this.questionCount}`
						this.$func.navigatorTo(url)
					} else if (row.url) {
						// 其他功能
						this.$func.navigatorTo(row.url)
					} else {
						this.$func.showToast('暂未开放')	
					}
				} catch (error) {
					console.error('跳转失败:', error)
					this.$func.showToast('跳转失败，请重试')
				}
			},  
		triggerShare() {
			// 主动调用分享功能（需先通过 wx.showShareMenu 开启）
			console.log("触发分享2");
		},
		// 添加onShareAppMessage生命周期方法，这是微信小程序分享的标准方法		
		onShareAppMessage() {
			// 数据验证
			const title = this.questionContent && this.questionContent.title 
				? `分享题库 - ${this.questionContent.title}` 
				: '分享题库';
				
			const imageUrl = this.questionContent && this.questionContent.image 
				? this.questionContent.image 
				: 'https://datiqiniu.allpp.cn/static/userinfo_avatar.png';
				
			return {
				title,
				imageUrl,
				path: '/subpages/exam/questionContent?uid=' + this.questionUid,
			}
		},
		// 分享到朋友圈的自定义内容（可选）
		onShareTimeline() {
			// 数据验证
			const title = this.questionContent && this.questionContent.title 
				? `分享题库 - ${this.questionContent.title}` 
				: '分享题库';
				
			const imageUrl = this.questionContent && this.questionContent.image 
				? this.questionContent.image 
				: 'https://datiqiniu.allpp.cn/static/userinfo_avatar.png';
				
			return {
				title,
				imageUrl,
				path: '/subpages/exam/questionContent?uid=' + this.questionUid,
			};
		},
	},
}
</script>

<style scoped lang="scss">
	@import "@/scss/custom_nav_bar.scss";

	.container {
		display: flex;
		flex-direction: column;
		background-color: #f4f4f4;
	}

	.banner image {
		width: 100%;
		display: block;
	}

	.info-section {
		background-color: #fff;
		padding: 15px;
		margin-bottom: 16rpx;

		.title-row {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 10rpx;
		}

		.main-title {
			font-size: 18px;
			font-weight: bold;
		}

		.user-info {
			display: flex;
			align-items: center;
			margin-bottom: 15px;
		}

		.avatar {
			width: 40px;
			height: 40px;
			border-radius: 50%;
			margin-right: 10px;
			background-color: #eee;
			/* 占位符背景*/
		}

		.user-details {
			display: flex;
			flex-direction: column;
		}

		.user-id {
			font-size: 14px;
			color: #333;
			margin-bottom: 4px;
		}

		.meta-info {
			font-size: 12px;
			color: #999;
			display: flex;
			align-items: center;

			.separator {
				margin: 0 5px;
			}
		}

		.action-buttons {
			display: flex;
			justify-content: space-around;
			align-items: center;
			padding-top: 10px;
			border-top: 1px solid #f0f0f0;
		}

		.action-item {
			display: flex;
			flex-direction: column;
			align-items: center;
			font-size: 12px;
			color: #666;
			padding: 10px 0;
			cursor: pointer;
			transition: all 0.3s ease;

			&.action-item-hover {
				opacity: 0.7;
			}

			view {
				margin-bottom: 4px;
			}
		}
	}

	.practice-section {
		background-color: #fff;
		padding: 15px;

		.section-title {
			font-size: 16px;
			font-weight: bold;
			margin-bottom: 15px;
			display: block;
		}

		.grid {
			display: flex;
			flex-wrap: wrap;
			// justify-content: space-between; // 如果每行不足2个，会导致不对齐
		}

		.grid-item {
			width: 49%; // 每行显示两个
			display: flex;
			margin-right: 1%;
			flex-direction: row;
			justify-content: space-around;
			padding: 30rpx 10rpx;
			box-sizing: border-box;
			position: relative; // 为了徽标定位
			background-color: #F4F4F4;

			.grid-icon-wrapper {
				position: relative;
				margin-bottom: 8px;
				width: 40px; // 给徽标定位提供参考				height: 30px;
				display: flex;
				justify-content: center;
				align-items: center;
			}

			.badge {
				position: absolute;
				top: -5px;
				right: -10px;
				padding: 1px 4px;
				border-radius: 6px;
				font-size: 9px;
				color: #fff;

				&.new {
					background-color: #ff4d4f;
				}

				&.vip {
					background-color: #fadb14;
					color: #6f4f00;
				}
			}

			.grid-title {
				font-size: 32rpx;
				color: #333;
				margin-bottom: 10rpx;
			}

			.grid-desc {
			font-size: 26rpx;
			color: #999;
		}
	}
	
}
	/* 加载状态*/
	.loading-container {
		display: flex;
		justify-content: center;
		align-items: center;
		height: 200px;
	}
	
	/* 错误状态*/
	.error-container {
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
		height: 200px;
		color: #ff4d4f;
	}
	
	/* 无数据状态*/
	.no-data {
		display: flex;
		justify-content: center;
		align-items: center;
		height: 100px;
		color: #999;
	}
</style>


