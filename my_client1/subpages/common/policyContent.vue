<template>
  <view class="template-content">
    <!-- 顶部自定义导航-->
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
          {{ content.title }}
        </text>
      </view>
    </tn-nav-bar>
	</view>	

    <view
      class="tn-margin"
      :style="{paddingTop: vuex_custom_bar_height + 'px'}"
    >
      <mp-html :content="content.content" />
    </view>
  </view>
</template>

<script>
	import template_page_mixin from '@/libs/mixin/template_page_mixin.js'
	export default {
		name: 'TemplateContent',
		mixins: [template_page_mixin],
		data() {
			return {
				mainColor: getApp().globalData.mainColor,
				content: {
					title: '',
					content: '',
				},
				type: 'privacy',
			}
		},
		onLoad(params) {
			this.type = params.type || 'privacy'
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
			this.fetchPrivacyContent()
		},
		methods: {
			fetchPrivacyContent() {
				this.$api.apiPolicyContent({type: this.type}).then(res => {
					if (res.code === 1) {
						this.content = res.data
						return
					}
					this.$func.showToast(res.msg)
				})
			},
			// 分享给朋友
			onShareAppMessage() {
				return {
					title: this.content.title || '政策内容',
					path: `/subpages/common/policyContent?type=${this.type}`,
					imageUrl: '',
					desc: this.content.title || '政策内容分享'
				}
			},
			// 分享到朋友圈
			onShareTimeline() {
				return {
					title: this.content.title || '政策内容',
					query: `type=${this.type}`,
					imageUrl: '',
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
</style>

