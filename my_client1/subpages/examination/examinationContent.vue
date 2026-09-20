<template>
  <view class="template-product tn-safe-area-inset-bottom">
    <!-- 顶部自定义导航-->
    <view class="tn-navbg" :style="{height: vuex_custom_bar_height + 'px'}">
		<tn-nav-bar fixed alpha customBack>
		<template #back>
			<view
			class="tn-custom-nav-bar__back"
			@click="goBack"
			>
			<text class="icon tn-icon-left" />
			<text class="icon tn-icon-home-capsule-fill" />
			</view>
		</template>
		<view class="tn-flex tn-flex-col-center tn-flex-row-center ">
			<text class="tn-text-bold tn-text-xl tn-color-white">
			考试说明
			</text>
		</view>
		</tn-nav-bar>
	</view>
    
    <!-- Tips 组件 -->
    <tn-tips ref="tips" position="top"></tn-tips>
    <view
      :style="{paddingTop: vuex_custom_bar_height + 5 + 'px'}"
    >
      <view
        class="tn-padding tn-margin-sm"
        style="height: auto;background-color: #fff;border-radius: 20rpx;"
      >
        <view class="collection-title tn-text-bold">
          考试详情
        </view>
        <view class="collection-title tn-padding-top tn-icon-trusty tn-text-left">
          考试名称: {{ examinationInfo.title }}
        </view>
        <view class="collection-title tn-padding-top tn-icon-time tn-text-left">
          考试时间: {{ examinationInfo.exam_time }}
        </view>
        <view class="collection-title tn-padding-top tn-icon-time tn-text-left">
          开始时间：{{ examinationInfo.start_time }}
        </view>
        <view class="collection-title tn-padding-top tn-icon-time tn-text-left">
          结束时间：{{ examinationInfo.end_time }}
        </view>
        <view class="collection-title tn-padding-top tn-icon-data tn-text-left">
          考试总分: {{ examinationInfo.paper.option_score || 0 }} - 及格分: {{ examinationInfo.score }}
        </view>
        <view class="collection-title tn-padding-top tn-icon-edit tn-text-left">
          考试题目数: {{ examinationInfo.paper.option_count || 0 }}
        </view>
		<view class="collection-title tn-padding-top tn-icon-edit tn-text-left">
          考试次数: {{ examinationInfo.submit_count_status.text }}
        </view>
		<view v-if ="examinationInfo.submit_count_status.remaining !==-1" class="collection-title tn-padding-top tn-icon-edit tn-text-left">
          您的剩余考试次数: {{ examinationInfo.submit_count_status.remaining }}
        </view>

      </view>	
      <!-- 底部悬浮按钮区域 -->
      <view class="tn-flex tn-flex-row tn-justify-content-between tn-safe-area-inset-bottom" style="position: fixed; bottom: 20rpx; left: 0; right: 0; z-index: 999; padding: 0 20rpx;">
		<!-- 订阅考试提醒按钮 -->
        <view class="tn-flex-1 tn-margin-right-xs">
          <tn-button
            class="bottom-button"
            :background-color="isFollowed ? 'tn-cool-bg-color-2' : 'tn-cool-bg-color-4'"
            padding="40rpx 0"
            width="100%"
            font-bold
            @click="toggleExamFollow"
          >
            <text class="tn-icon-like-fill tn-padding-right-xs tn-color-white"></text>
            <text class="tn-color-white">{{ isFollowed ? '已订阅' : '订阅提醒' }}</text>
          </tn-button>
        </view>
        <!-- 开始考试按钮 -->
        <view class="tn-flex-1 tn-margin-left-xs">
          <tn-button
            class="bottom-button"
            backgroundColor="tn-cool-bg-color-9"
            padding="40rpx 0"
            width="100%"
            font-bold
            :disabled="examinationInfo.submit_count_status.remaining ===0"
            @click="showQuestionCommonBtn()"
          >
            <text class="tn-icon-play tn-padding-right-xs tn-color-white"></text>
            <text class="tn-color-white">
              {{ examinationInfo.submit_count_status.remaining === 0 ? '已用完答题次数' : '开始考试' }}
            </text>
          </tn-button>
        </view>
      </view>
      <view
        v-if="examinationInfo.content !== ''"
        class="tn-padding tn-margin-sm"
        style="height: auto;background-color: #fff;border-radius: 20rpx;"
      >
        <view class="collection-title tn-text-bold">
          考试说明
        </view>
        <view class="collection-title tn-padding-top">
          <mp-html :content="examinationInfo.content" />
        </view>
      </view>
    </view>
    <view class="tn-tabbar-height" />
	<!-- VIP 功能提示弹窗 -->
      <tn-modal
        v-model="showVipModal"
        :width="'75%'"
        title="VIP功能"
        content="此功能为VIP专享功能，开通VIP会员即可使用"
        :button="[
          { text: '暂不开通', backgroundColor: '#F5F5F5', fontColor: '#333333' },
          { text: '立即开通', backgroundColor: mainColor, fontColor: '#FFFFFF' }
        ]"
        :maskCloseable="false"
		:showCloseBtn="true"
        @click="handleVipModalClick"
      />

  </view>
</template>

<script>
	import template_page_mixin from '@/libs/mixin/template_page_mixin.js'
	import { checkVipStatusSync } from '@/util/userStore.js'
	import { isUserLoggedIn } from '@/util/userStore.js'

	export default {
		name: 'examinationContent',
		mixins: [template_page_mixin],
		data() {
			return {
				mainColor: getApp().globalData.mainColor,
				uid: "",// 考试uid
				examinationInfo: {}, // 考试详情
				// VIP 提示相关
			    showVipModal: false, // 是否显示 VIP 提示弹窗
			    // 订阅状态
			    isFollowed: false,
			}
		},
		onLoad(params) {
			this.uid = params.uid || ''
			this.collectionType = params.type || 2
			// #ifdef MP-WEIXIN
			this.$tn.mpShare = {
				share: true
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
			this.fetchExamination()
		},
		computed: {
			// 从Vuex获取订阅模板ID
			subscribeTemplates() {
				return this.$store.state.vuex_subscribe_templates || []
			}
		},
		methods: {
			// 显示提示信息
			showTips(msg, backgroundColor = '#E83A30', fontColor = '#FFFFFF', duration = 1500) {
				this.$refs.tips.show({
					msg: msg,
					backgroundColor: backgroundColor,
					fontColor: fontColor,
					duration: duration
				})
			},
			
			// 点击跳转做题页面
			showQuestionCommonBtn() {
				if (this.examinationInfo.submit_count_status.remaining === 0) {
					// 答题次数已用完，显示提示
					this.showTips('您的答题次数已用完，无法继续考试');
					return;
				}
				
				if (!this.checkVipStatus()) {
						// 非VIP用户，显示VIP提示
						this.showVipModal = true;
						return;
					}
				// 准备传递给questionSetting.vue和commonQuestion.vue的参数
				const examSettings = {
					uid: this.uid,
					questions_type: 2, // 考试类型，对应commonQuestion.vue中的case 'examination'
					mode: 'normal', // 答题模式
					practice_mode: 2, // 2表示在线考试答题模式
					// 从examinationInfo中获取考试信息
					exam_time: this.examinationInfo.exam_time,
					start_time: this.examinationInfo.start_time,
					end_time: this.examinationInfo.end_time,
					paper_score: this.examinationInfo.paper?.option_score || 0,
					question_count: this.examinationInfo.paper?.option_count || 0,
					// 传递考试详情，方便commonQuestion.vue使用
					questionType: 'examination', // 考试类型，对应commonQuestion.vue中的case 'examination'
					// 标记为考试类型，确保questionSetting.vue能正确处理
					isExamination: true
				}
				
				// 验证是否有有效的题目
				if (examSettings.question_count <= 0) {
					this.showTips('暂无题目', '#E83A30', '#FFFFFF', 1500);
					return
				}
				
				// 保存到全局状态，方便questionSetting.vue和commonQuestion.vue获取
				// 直接跳转到答题页面，不需要经过设置步骤
				uni.navigateTo({
					url: '/subpages/exam/components/commonQuestion?examSettings=' + encodeURIComponent(JSON.stringify(examSettings))
				})
			},
			/**
			 * 检查用户VIP状态
			 * 使用统一的用户管理工具，带有自动降级策略
			 * @returns {boolean} - true表示有VIP权限，false表示无VIP权限
			 */
			checkVipStatus() {
				return checkVipStatusSync()
			},
			// 处理 VIP 提示弹窗的点击事件
			handleVipModalClick(event) {
				if (event.index === 0) {
					// 用户点击“暂不开通”
					this.showVipModal = false;
					return;
				} else if (event.index === 1) {
					// 跳转到 VIP 开通页面
					uni.navigateTo({
						url: '/subpages/user/member'
					});
				}
			},
			fetchExamination() {
			uni.showLoading({
				title: '努力加载考试详情...',
				mask: true
			})
			this.$api.apiExaminationContent({
				uid: this.uid,
			}).then(res => {
				uni.hideLoading()
				if (res.code === 1) {
					this.examinationInfo = res.data
					// 设置初始订阅状态
					this.isFollowed = res.data.is_followed === 1
					return
				}
				this.showTips(res.msg)
			})
		},
			// 分享给朋友
			onShareAppMessage() {
				return {
					title: this.examinationInfo.title || '考试说明',
					path: `/subpages/examination/examinationContent?uid=${this.uid}`,
					imageUrl: this.examinationInfo.image || '',
					desc: '在线考试分享'
				}
			},
			// 分享到朋友圈
			onShareTimeline() {
				return {
					title: this.examinationInfo.title || '考试说明',
					query: `uid=${this.uid}`,
					imageUrl: this.examinationInfo.image || '',
					success: function() {
						console.log('分享到朋友圈成功')
					}
				}
			},
			// 订阅/取消订阅考试提醒
			toggleExamFollow() {
				if (!isUserLoggedIn()) {
					// 未登录，提示用户登录
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
				
				// 检查考试信息是否已加载完成
				if (!this.examinationInfo || !this.examinationInfo.id) {
					this.showTips('考试信息加载中，请稍后再试')
					return
				}
				
				// 如果已经订阅，直接取消订阅
				if (this.isFollowed) {
					// 调用后端统一订阅API保存订阅状态
					this.$api.apiSubscribe({ 
						type: 'exam',
						related_id: this.examinationInfo.id,
						subscribe_status: 0,
					}).then(res => {
						if (res.code == 1) {
							this.isFollowed = false
							this.showTips('🔔 已取消订阅考试提醒')
						} else {
							this.showTips('取消订阅失败，请稍后重试')
						}
					}).catch(err => {
						console.error('取消订阅失败', err)
						this.showTips('取消订阅失败，请稍后重试')
					})
					return
				}
				
				// 从Vuex获取订阅模板ID
				const templates = this.subscribeTemplates
				// 提取模板ID数组
				const tmplIds = templates.slice(0, 3).map(item => item.template_id);
				
				if (tmplIds.length === 0) {
					this.showTips('暂无可用的订阅模板，请联系管理员')
					return
				}
				// 构建模板数据
				const templateData = {
					// 考试名称
					thing1: {
						value: this.examinationInfo.title || '考试提醒'
					},
					// 备注
					thing2: {
						value: this.examinationInfo.remarks || this.examinationInfo.description || '考试提醒'
					},
					// 开始时间
					time3: {
						value: this.examinationInfo.start_time || ''
					},
					// 结束时间
					time4: {
						value: this.examinationInfo.end_time || ''
					}
				};
				
				// 使用统一的订阅消息处理函数
				this.$func.handleSubscribeMessage({
					tmplIds: tmplIds,
					apiCall: this.$api.apiSubscribe,
					apiParams: {
						type: 'exam',
						related_id: this.examinationInfo.id,
						subscribe_status: 1,
					},
					templateData: templateData,
					isSubscribed: this.isFollowed,
					successCallback: (result) => {
						this.isFollowed = true;
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
								type: 'exam',
								related_id: this.examinationInfo.id,
							}).catch(err => {
								console.error('记录订阅状态失败:', err);
							});
						}
					},
					failCallback: (err) => {
						console.error('订阅失败:', err);
					},
					completeCallback: () => {
						// 无论成功失败，都刷新订阅状态
						this.fetchExamination();
					}
				});
			},
		}
	}
</script>

<style lang="scss" scoped>
	@import "@/scss/custom_nav_bar.scss";
	.template-product {
		height: 100vh;
		background-color: #D5FAF2;
	}

	.tn-tabbar-height {
		min-height: 20rpx;
		height: calc(40rpx + env(safe-area-inset-bottom) / 2);
	}

	/* 用户头像 start */
	.logo-image {
		width: 110rpx;
		height: 110rpx;
		position: relative;
	}

	.logo-pic {
		background-size: cover;
		background-repeat: no-repeat;
		// background-attachment:fixed;
		background-position: top;
		box-shadow: 0rpx 0rpx 80rpx 0rpx rgba(0, 0, 0, 0.15);
		border-radius: 10rpx;
		overflow: hidden;
		// background-color: #FFFFFF;
	}

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

	/* 轮播视觉样式start */
	.card-swiper {
		height: 750rpx !important;
	}

	.card-swiper swiper-item {
		width: 750rpx !important;
		left: 0rpx;
		box-sizing: border-box;
		// padding: 0rpx 30rpx 90rpx 30rpx;
		overflow: initial;
	}

	.card-swiper swiper-item .swiper-item {
		width: 100%;
		display: block;
		height: 100%;
		transform: scale(1);
		transition: all 0.2s ease-in 0s;
		overflow: hidden;
	}

	.card-swiper swiper-item.cur .swiper-item {
		transform: none;
		transition: all 0.2s ease-in 0s;
	}

	.image-banner {
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.image-banner image {
		width: 100%;
		height: 100%;
	}

	/* 轮播指示器start*/
	.indication {
		z-index: 9999;
		width: 100%;
		height: 36rpx;
		position: absolute;
		display: flex;
		flex-direction: row;
		align-items: center;
		justify-content: center;
	}

	.spot {
		background-color: #FFFFFF;
		opacity: 0.6;
		width: 10rpx;
		height: 10rpx;
		border-radius: 20rpx;
		top: -60rpx;
		margin: 0 8rpx !important;
		position: relative;
	}

	.spot.active {
		opacity: 1;
		width: 30rpx;
		background-color: #FFFFFF;
	}

	/* 间隔样式start*/
	.tn-strip-bottom-min {
		width: 100%;
		border-bottom: 1rpx solid #F8F9FB;
	}

	/* 间隔样式start*/
	.tn-strip-bottom {
		width: 100%;
		border-bottom: 20rpx solid rgba(241, 241, 241, 0.8);
	}

	/* 间隔样式end*/
	/* 标题 start */
	.nav_title {
		-webkit-background-clip: text;
		background-clip: text;
		color: transparent;

		&--wrap {
			position: relative;
			display: flex;
			height: 120rpx;
			font-size: 46rpx;
			align-items: center;
			justify-content: center;
			font-weight: bold;
			background-image: url(https://tnuiimage.tnkjapp.com/title_bg/title44.png);
			background-size: cover;
		}
	}

	/* 标题 end */

	/* 用户头像 start */
	.user-image {
		width: 90rpx;
		height: 90rpx;
		position: relative;
	}

	.user-pic {
		background-size: cover;
		background-repeat: no-repeat;
		// background-attachment:fixed;
		background-position: top;
		border-radius: 50%;
		overflow: hidden;
		background-color: #FFFFFF;
	}

	/* 底部*/
	.tn-footerfixed {
		position: fixed;
		background-color: rgba(255, 255, 255, 0.5);
		box-shadow: 0rpx 0rpx 30rpx 0rpx rgba(0, 0, 0, 0.07);
		bottom: 0;
		width: 100%;
		transition: all 0.25s ease-out;
		will-change: transform;
		z-index: 100;
	}

	/* 底部 start*/
	.footerfixed {
		position: fixed;
		width: 100%;
		bottom: 0;
		z-index: 999;
		box-shadow: 0rpx 0rpx 30rpx 0rpx rgba(0, 0, 0, 0.07);
	}

	/* 标签内容 start*/
	.tn-tag-content {
		&__item {
			display: inline-block;
			line-height: 45rpx;
			padding: 10rpx 30rpx;
			margin: 20rpx 20rpx 5rpx 0rpx;

			&--prefix {
				padding-right: 10rpx;
			}
		}
	}

	/* 标签内容 end*/

	/* 内容样式start */
	.content-backgroup {
		z-index: -1;

		.backgroud-image {
			width: 100%;
		}
	}

	/* 内容样式end */

	/* 商家商品 start*/
	.tn-blogger-content {
		&__wrap {
			box-shadow: 0rpx 0rpx 50rpx 0rpx rgba(0, 0, 0, 0.07);
			border-radius: 20rpx;
			margin: 15rpx;
		}

		&__info {
			&__btn {
				margin-right: -12rpx;
				opacity: 0.5;
			}
		}

		&__label {
			&__item {
				line-height: 45rpx;
				padding: 0 10rpx;
				margin: 5rpx 18rpx 0 0;

				&--prefix {
					color: #E83A30;
					padding-right: 10rpx;
				}
			}

			&__desc {
				line-height: 35rpx;
			}
		}

		&__main-image {
			border-radius: 16rpx 16rpx 0 0;

			&--1 {
				max-width: 690rpx;
				min-width: 690rpx;
				max-height: 400rpx;
				min-height: 400rpx;
			}

			&--2 {
				max-width: 260rpx;
				max-height: 260rpx;
			}

			&--3 {
				height: 212rpx;
				width: 100%;
			}
		}

		&__count-icon {
			font-size: 24rpx;
			padding-right: 5rpx;
		}
	}

	.image-book {
		padding: 150rpx 0rpx;
		font-size: 16rpx;
		font-weight: 300;
		position: relative;
	}

	.image-picbook {
		background-size: cover;
		background-repeat: no-repeat;
		// background-attachment:fixed;
		background-position: top;
		border-radius: 15rpx 15rpx 0 0;
	}

	/* 按钮 */
	.button-1 {
		background-color: rgba(0, 0, 0, 0.15);
		position: absolute;
		/* bottom:200rpx;
      right: 20rpx; */
		top: 700rpx;
		right: 30rpx;
		z-index: 1001;
		border-radius: 100px;
	}

	.see {
		display: flex;
		justify-content: space-between;
		padding-top: 10rpx;
		border-radius: 6rpx;
		color: #666;
		line-height: 1.6;
	}

	.vip-tag {
		color: #FF9900;
		font-size: 28rpx;
		padding: 2rpx 8rpx;
		border-radius: 4rpx;
		margin-left: 10rpx;
		display: inline-block;
		vertical-align: middle;
		margin-bottom: 5rpx;
		font-weight: bold;
	}

	.label-group .label+.vip-tag {
		margin-left: 10rpx;
	}

</style>

