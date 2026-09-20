<template>
  <view class="template-help tn-safe-area-inset-bottom">
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
          在线帮助
        </text>
      </view>
    </tn-nav-bar>
	</view>	

    <view
      class="tn-margin-bottom-xl"
      :style="{paddingTop: vuex_custom_bar_height + 'px'}"
    >
      <block
        v-for="(item, index) in helpList"
        :key="index"
      >
        <tn-list-cell
          :arrow="true"
          :arrow-right="true"
          @click="tn('/subpages/common/helpContent?id=' + item.id)"
        >
          {{index+1}}. {{ item.title }}
        </tn-list-cell>
      </block>
    </view>

    <view
      class="tn-footerfixed tn-flex tn-flex-row-between tn-flex-col-center tn-padding tn-safe-area-inset-bottom tn-bg-white"
      @click="showModal"
    >
      <view class="justify-content-item tn-padding-bottom">
        <view class="tn-flex tn-flex-col-center tn-flex-row-left">
          <view class="user-pic">
            <view class="user-image">
              <view
                class="tn-shadow-blur" 
                :style="{backgroundImage: `url(${defaultAvatar})`, width: '100rpx', height: '100rpx', backgroundSize: 'cover'}"
              />
            </view>
          </view>
          <view class="tn-padding-right tn-color-black">
            <view class="tn-padding-right tn-padding-left-sm">
              <text class="tn-text-lg tn-text-bold">
                {{ customerName }}
              </text>
              <text class="tn-padding-left-sm">
                {{ customerPosition }}
              </text>
            </view>
            <view class="tn-padding-right tn-padding-top-xs tn-padding-left-sm tn-text-ellipsis">
              <text class="tn-color-black tn-text-bold">
                {{ customerCompany }}
              </text>
            </view>
          </view>
        </view>
      </view>
      <view class="justify-content-item tn-flex-col-center tn-flex-row-center tn-text-center tn-padding-bottom">
        <view class="">
          <text
            class="tn-icon-wechat-fill tn-color-green--dark"
            style="font-size: 50rpx;"
          />
        </view>
        <view class="">
          <text class="tn-text-sm">
            联系客服
          </text>
        </view>
      </view>
    </view>

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

    <view class="tn-tabbar-height" />
  </view>
</template>

<script>
	import template_page_mixin from '@/libs/mixin/template_page_mixin.js'
	
	export default {
		name: 'TemplateHelp',
		components: {
		},
		mixins: [template_page_mixin],
		data() {
			return {
				mainColor: getApp().globalData.mainColor,
				serviceQrCode: getApp().globalData.serviceQrCode,
				customerWechat: getApp().globalData.customerWechat,
				customerName: getApp().globalData.customerName,
				customerCompany: getApp().globalData.customerCompany,
				customerPosition: getApp().globalData.customerPosition,
				copyright: getApp().globalData.copyRight,
				defaultAvatar: getApp().globalData.defaultAvatar,
				show1: false,
				helpList: [{
					title: '分类名称',
					id: '',
					children: [{
						id: '',
						title: '文章标题'
					}],
				}]
			}
		},
		onLoad() {
			this.fetchHelpList()
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
			// 获取帮助文档列表
			fetchHelpList() {
				uni.showLoading({
					title: '努力加载中'
				})
				this.$api.apiHelpList().then(res => {
					this.helpList = res.data
					uni.hideLoading()
				})
			},
			// 跳转
			tn(e) {
				uni.navigateTo({
					url: e,
				});
			},
			// 复制微信
			copyWechat() {
				this.$func.setClipboardData(this.customerWechat)
			},
			// 预览作者图片
			previewQRCodeImage() {
				this.$func.previewQRCodeImage()
			},

			// 弹出模态框
			showModal(event) {
				this.openModal()
			},
			// 打开模态框
			openModal() {
				this.show1 = true
			},
			// 分享给朋友
			onShareAppMessage() {
				return {
					title: '在线帮助',
					path: '/subpages/common/helpList',
					imageUrl: '',
					desc: '在线帮助文档分享'
				}
			},
			// 分享到朋友圈
			onShareTimeline() {
				return {
					title: '在线帮助',
					query: '',
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

	/* 间隔线 start*/
	.tn-strip-bottom-min {
		width: 100%;
		// border-bottom: 1rpx solid #F8F9FB;
	}

	.tn-strip-top {
		width: 100%;
		border-top: 20rpx solid rgba(241, 241, 241, 0.8);
	}

	/* 间隔线 end*/

	/* 毛玻璃/
	.dd-glass {
		width: 100%;
		backdrop-filter: blur(20rpx);
		-webkit-backdrop-filter: blur(20rpx);
	}


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

	/* 底部悬浮按钮 start*/
	.tn-tabbar-height {
		min-height: 120rpx;
		height: calc(140rpx + env(safe-area-inset-bottom) / 2);
		height: calc(140rpx + constant(safe-area-inset-bottom));
	}

	.tn-footerfixed {
		position: fixed;
		background-color: rgba(255, 255, 255, 0.5);
		box-shadow: 0rpx 0rpx 30rpx 0rpx rgba(0, 0, 0, 0.07);
		bottom: 0;
		width: 100%;
		transition: all 0.25s ease-out;
		z-index: 100;
	}
</style>

