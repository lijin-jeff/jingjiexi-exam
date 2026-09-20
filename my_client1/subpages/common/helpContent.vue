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
					id: '',
					title: '文章标题',
					content: '<p>文章内容</p>',
				},
				searchWhere: {},
				sence: "", // service_content:用户服务协议，privacy_content:用户隐私协议
			}
		},
		onLoad(params) {
			this.searchWhere.id = params.id || ""
			this.sence = params.sence || ""
			if (this.sence !== '') {
				this.getPrivacyContent()
			} else {
				this.getDocumentContent()
			}
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
			getDocumentContent() {
				this.$api.apiHelpContent(this.searchWhere).then(res => {
					if (res && res.data) {
						this.content = res.data
						return
					}
					this.$func.showToast(res.msg)
				})
			},
			getPrivacyContent() {
				this.$api.apiPrivacyContent({
					type: this.sence
				}).then(res => {
					if (res && res.data) {
						this.content = {
							content: res.data.content,
							title: this.sence == 'privacy_content' ? '用户隐私协议' : '用户服务协议'
						}
						return
					}
					this.$func.showToast(res.msg)
				})
			},
			getServiceContent() {
				getServiceContent().then(res => {
					if (res && res.data) {
						this.content = {
							content: res.data,
							title: "用户服务协议"
						}
						return
					}
					this.$func.showToast(res.msg)
				})
			},
		},
		onShareAppMessage() {
			return {
				title: '向你分享' + this.content.title + '快去阅读',
				desc: '向你分享' + this.content.title + '快去阅读',
				path: `/subpages/common/helpContent?id=${this.searchWhere.id}&sence=${this.sence}`,
			}
		},
		onShareTimeline() {
			return {
				title: '向你分享' + this.content.title + '快去阅读',
				desc: '向你分享' + this.content.title + '快去阅读',
				query: `id=${this.searchWhere.id}&sence=${this.sence}`
			}
		}
	}
</script>

<style lang="scss" scoped>
	@import "@/scss/custom_nav_bar.scss";
</style>

