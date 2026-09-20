<template>
  <!-- 积分明细 -->
  <view class="page tn-safe-area-inset-bottom">
    <view class="tn-navbg" :style="{height: vuex_custom_bar_height + 'px'}">
      <tn-nav-bar fixed alpha customBack>
        <template #back>
          <view class="tn-custom-nav-bar__back">
            <text
              class="icon tn-icon-left tn-color-white"
              @click="goBack"
            ></text>
            <text
              class="icon tn-icon-home-capsule-fill tn-color-white tn-margin-left-sm"
              @click="goHome"
            ></text>
          </view>
        </template>
        <view class="tn-custom-nav-bar__content">
          <text class="tn-text-bold tn-text-xl tn-color-white">积分明细</text>
        </view>
      </tn-nav-bar>
    </view>

    <view  :style="{paddingTop: vuex_custom_bar_height + 'px'}"> </view>
    <view class="integral-wrap top-background">
      <!-- 波浪背景 -->
      <view class="tnwave waveAnimation">
        <view class="waveWrapperInner bgTop">
          <view
            class="wave waveTop"
            style="background-image: url('https://datiqiniu.allpp.cn/static/wave-2.png')"
          ></view>
        </view>
        <view class="waveWrapperInner bgMiddle">
          <view
            class="wave waveMiddle"
            style="background-image: url('https://datiqiniu.allpp.cn/static/wave-2.png')"
          ></view>
        </view>
        <view class="waveWrapperInner bgBottom">
          <view
            class="wave waveBottom"
            style="background-image: url('https://datiqiniu.allpp.cn/static/wave-1.png')"
          ></view>
        </view>
      </view>
      
      <view
          :style="{background: `linear-gradient(to bottom, ${mainColor} 0%, ${darkMainColor} 100%)`, zIndex: 2}"
        >
        <!-- 积分汇总信息 -->
        <view class="integral-summary tn-padding tn-padding-top-lg">
          <view class="tn-flex tn-flex-col-center tn-text-center">
            <view class="tn-text-bold tn-text-xxl tn-color-white">当前总积分</view>
            <view class="tn-text-bold tn-text-4xl tn-color-white tn-padding-top-sm">{{ integral }}</view>
            <view class="tn-margin-top-xs">
              <text class="icon tn-icon-refresh tn-color-white" @click="refresh"></text>
            </view>
          </view>
        </view>
        <!-- 积分选项卡 -->
        <view class="tn-margin-horizontal-lg tn-flex tn-flex-row tn-items-center" style="height: 60rpx; position: relative; z-index: 10;">
          <tn-tabs
            :list="tabList"
            :active-color="'#fff'"
            :inactive-color="'rgba(255, 255, 255, 0.6)'"
            :current="currentTab"
            name="title"
            @change="tabChange"
            bg-color="transparent"
            line-width="30rpx"
            line-color="#fff"
            font-size="32rpx"
            height="80rpx"
            style="flex: 1;"
          />
          <view class="tn-margin-right">
            <text 
              class="tn-text-sm tn-color-white tn-opacity-80"
              @click="integralHelp"
              style="text-decoration: underline; cursor: pointer; z-index: 100;"
            >
              积分说明
            </text>
          </view>
        </view>
      </view>
    </view>
    <!-- 积分列表 -->
    <scroll-view 
      class="integral-list tn-padding-horizontal-lg" 
      style="margin-top: 0;"
      scroll-y="true"
      scroll-with-animation="true"
      refresher-enabled="true"
      :refresher-triggered="refresherTriggered"
      @refresherrefresh="onRefresherRefresh"
      @scrolltolower="onScrollToLower"
    >
      <!-- 空状态 -->
      <tn-empty 
        v-if="integralList.length === 0 && !loading" 
        mode="list"
        text="暂无积分记录"
        customStyle="{margin: 20rpx;}"
      />
      
      <!-- 列表内容 -->
      <view v-else>
        <view 
          v-for="(item, index) in integralList" 
          :key="index"
          class="tn-flex tn-flex-row-between tn-strip-bottom-min tn-padding"
        >
          <view class="tn-flex-1">
            <view class="tn-mb-2">
              <text class="tn-text-base tn-text-bold" user-select="true">
                {{ item.title }}
              </text>
            </view>
            <view>
              <text class="tn-text-sm tn-color-gray-600" user-select="true">
                {{ item.create_time }}
              </text>
            </view>
          </view>
          <view class="justify-content-item tn-text-lg tn-padding-top">
            <text 
              class="tn-text-bold tn-margin-right-sm" 
              :class="item.action === 1 ? 'tn-color-green' : 'tn-color-orangered'"
              user-select="true"
            >
              {{ item.action === 1 ? '+' : '-' }}{{ item.change_amount }}
            </text>
          </view>
        </view>
        
        <!-- 加载更多 -->
        <view class="load-more-container tn-margin-top">
          <tn-load-more 
            :status="loading ? 'loading' : (hasMore ? 'loadmore' : 'nomore')" 
            loadingIconType="flower"
            :loadText="{
              loadmore: '上拉加载更多',
              loading: '加载中...',
              nomore: '没有更多数据啦'
            }"
            fontColor="#666"
            :fontSize="28"
          />
        </view>
      </view>
    <view style="padding-bottom: 120rpx;"></view>
    </scroll-view>
        <!-- 消息&数据 -->
    <view class="tn-flex tn-flex-row-between tn-footerfixed" style="position: fixed; bottom: 20rpx; left: 0; right: 0; z-index: 999;">
        <view class="tn-flex-1 justify-content-item tn-margin-xs tn-text-center">
          <tn-button 
            class="tn-flex tn-flex-col-center tn-padding-sm tn-margin-sm bottom-button"
            backgroundColor="tn-cool-bg-color-12"
            fontColor="#fff"
            padding="40rpx 0"
            width="100%"
            shadow 
            font-bold
            @click="gotoRanking"
          >
            <text class="tn-icon-sword tn-padding-right-xs tn-color-white"></text>
            <text class="tn-color-white">积分排行</text>
          </tn-button>
        </view> 
          <!-- 周边兑换按钮 -->
        <view class="tn-flex-1 justify-content-item tn-margin-xs tn-text-center">
          <tn-button 
              class="tn-flex tn-flex-col-center tn-padding-sm tn-margin-sm bottom-button"
              backgroundColor="tn-cool-bg-color-13"
              fontColor="#fff"
              padding="40rpx 0"
              width="100%"
              shadow 
              font-bold
              @click="gotoRedeem"
            >
              <text class="tn-icon-gift tn-padding-right-xs tn-color-white"></text>
              <text class="tn-color-white">周边兑换</text>
            </tn-button>
          </view>
        </view>

    <!-- 积分说明弹窗 - 移到main容器内部 -->
    <tn-popup
      v-model="showIntegralHelp"
      mode="bottom"
      :safeAreaInsetBottom="true"
      :borderRadius="20"
      :zIndex="9999"
    >
      <view class="popup-header tn-padding tn-text-center">
        <text class="tn-text-lg tn-text-bold">积分说明</text>
      </view>
      <view class="popup-content tn-padding-horizontal tn-padding">
        <mp-html
          :selectable="true"
          :content="integralContent"
        />
      </view>
      <view class="tn-padding-horizontal tn-margin">
        <tn-button
          width="100%"
          height="70rpx"
          font-size="30rpx"
          backgroundColor="tn-cool-bg-color-9"
          fontColor="#fff"
          @click="closeIntegralHelpPopup"
          :style="{boxSizing: 'border-box'}"
        >
          关闭弹窗
        </tn-button>
      </view>
    </tn-popup>
  </view>
</template>

<script>
import template_page_mixin from '@/libs/mixin/template_page_mixin.js'

export default {
  name: 'IntegralHistory',
  mixins: [template_page_mixin],
  // 启用下拉刷新
  onPullDownRefresh() {
    this.initData()
  },
  data() {
    return {
      showIntegralHelp: false,
      mainErrorColor: getApp().globalData.mainErrorColor,
      tabList: [
        { title: '增加' },
        { title: '减少' }
      ],
      currentTab: 0,
      integral: '0.00',
      integralList: [],
      loading: false,
      hasMore: true,
      searchWhere: {
        page_no: 1,
        page_size: 20,
        action: 0
      },
      integralContent: '积分说明',
      refresherTriggered: false

    }
  },
  computed: {
    mainColor() {
      return getApp().globalData.mainColor
    },
    darkMainColor() {
      return this.darkenColor(this.mainColor, 10)
    }
  },
  onLoad() {
    // #ifdef MP-WEIXIN
    this.$tn.mpShare = {
      share: false
    }
    if (!this.$tn.mpShare.share) {
      uni.hideShareMenu()
    }
    // #endif
  },
  onShow() {
    this.initData()
    // 从全局数据中获取积分规则
    const integralRes = getApp().globalData.integralRes
    // 尝试多种可能的数据结构获取积分规则
    this.integralContent = integralRes?.user_integral_rule
  },
  methods: {
      /**
       * 跳转到积分排行页面
       */
      gotoRanking() {
        uni.navigateTo({url: '/subpages/integral/ranking'})
      },
      
      /**
       * 跳转到周边兑换页面
       */
      gotoRedeem() {
        uni.navigateTo({url: '/subpages/integral/redeem'})
      },
      
      /**
       * 将颜色变暗
       * @param {string} color - 原始颜色值，如 #05CA8D
       * @param {number} percent - 变暗百分比，0-100
       * @returns {string} 变暗后的颜色值
       */
      darkenColor(color, percent) {
      // 移除 # 号
      color = color.replace('#', '');
      
      // 将 hex 转换为 RGB
      const r = parseInt(color.substring(0, 2), 16);
      const g = parseInt(color.substring(2, 4), 16);
      const b = parseInt(color.substring(4, 6), 16);
      
      // 计算变暗后的 RGB 值
      const factor = (100 - percent) / 100;
      const newR = Math.floor(r * factor);
      const newG = Math.floor(g * factor);
      const newB = Math.floor(b * factor);
      
      // 将 RGB 转换为 hex
      const newColor = `#${newR.toString(16).padStart(2, '0')}${newG.toString(16).padStart(2, '0')}${newB.toString(16).padStart(2, '0')}`;
      
      return newColor;
    },
    
    /**
     * 初始化数据
     */
    initData() {
      this.searchWhere.page_no = 1
      this.integralList = []
      this.hasMore = true
      this.fetchUserIntegral()
    },
    // 返回上一页
			goBack() {
				uni.navigateBack()
			},
    /**
     * 刷新数据
     */
    refresh() {
      this.initData()
    },
    
    /**
     * 积分说明
     */
    integralHelp() {
      this.showIntegralHelp = true
    },
    
    /**
     * 关闭积分说明弹窗
     */
    closeIntegralHelpPopup() {
      this.showIntegralHelp = false
    },
    
    /**
     * 切换标签
     */
    tabChange(e) {
      this.currentTab = e
      this.initData()
    },
    
    /**
     * 触底加载更多
     */
    onScrollToLower() {
      if (this.hasMore && !this.loading) {
        this.fetchUserIntegral()
      }
    },
    /**
     * 下拉刷新
     */
    onRefresherRefresh() {
      this.refresherTriggered = true
      this.initData()
    },
    
    /**
     * 页面跳转
     * @param {string} url - 跳转地址
     */
    tn(url) {
      if (url == '') {
        this.$func.showToast('暂未开放')
        return
      }
      this.$func.navigatorTo(url)
    },
    
    /**
     * 获取用户积分
     */
    async fetchUserIntegral() {
      if (this.loading) return
      
      this.loading = true
      // 修复action参数映射：0->1（增加），1->2（减少）
      this.searchWhere.action = this.currentTab + 1
      
      try {
        const res = await this.$api.apiUserIntegralList(this.searchWhere)
        if (res.code === 1) {
          if (this.searchWhere.page_no === 1) {
            this.integralList = res.data.lists
          } else {
            this.integralList.push(...res.data.lists)
          }
          
          // 更新总积分
          if (res.data.extend?.user_integral) {
            this.integral = res.data.extend.user_integral
          }
          
          // 判断是否还有更多数据
          this.hasMore = res.data.lists.length >= this.searchWhere.page_size
          if (this.hasMore) {
            this.searchWhere.page_no += 1
          }
        } else {
          this.$func.showToast(res.msg || '获取积分明细失败')
        }
      } catch (error) {
        this.$func.showToast('获取积分明细失败')
      } finally {
        this.loading = false
        // 停止下拉刷新动画
        this.refresherTriggered = false
        uni.stopPullDownRefresh()
      }
    }
  },
  
  /**
   * 触底加载更多
   */
  onReachBottom() {
    if (this.hasMore && !this.loading) {
      this.fetchUserIntegral()
    }
  },
}
</script>

<style lang="scss" scoped>
@import "@/scss/custom_nav_bar.scss";
$top-bg-height: 320rpx;

// 胶囊导航栏样式
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

// 内容容器样式
.tn-custom-nav-bar__content {
  position: absolute;
  text-align: center;
  left: 0;
  right: 0;
  bottom: 0;
  margin: auto;
  font-size: 32rpx;
  text-overflow: ellipsis;
  white-space: nowrap;
  overflow: hidden;
}

.integral-history {
  background-color: #f5f5f5;
  min-height: 100vh;
}

.top-background {
  position: relative;
  
  .integral-summary {
    display: flex;
    justify-content: center;
    align-items: center;
  }
}

.integral-list {
    background-color: #fff;
    border-radius: 15rpx;
    box-shadow: 0 0 10rpx rgba(0, 0, 0, 0.05);
    padding: 0;
    overflow: hidden;
    height: calc(100vh - 320rpx);
    -webkit-overflow-scrolling: touch;
    
    .integral-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        
        &__left {
            flex: 1;
        }
        
        &__right {
            flex-shrink: 0;
        }
    }
    
    .load-more-container {
        height: 80rpx;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20rpx 0;
    }
}

// 间隔线样式
.tn-strip-bottom-min {
  width: 100%;
  border-bottom: 1rpx solid #F8F9FB;
}

// 文本换行样式
.tn-word-wrap {
  word-wrap: break-word;
  white-space: normal;
  overflow: hidden;
}

.popup-header {
  border-bottom: 1px solid #F8F9FB;
}

.popup-content {
  max-height: 50vh;
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
}

  /* 翘边阴影*/
  .shadow-warp {
  	position: relative;
  	box-shadow: 0 10rpx 10rpx rgba(0, 0, 0, 0.01);
  }
  
  .shadow-warp:before,
  .shadow-warp:after {
  	position: absolute;
  	content: "";
  	top: 20rpx;
  	bottom: 30rpx;
  	left: 20rpx;
  	width: 50%;
  	box-shadow: 0 30rpx 20rpx rgba(0, 0, 0, 0.2);
  	transform: rotate(-3deg);
  	z-index: -1;
  }
  
  .shadow-warp:after {
  	right: 20rpx;
  	left: auto;
  	transform: rotate(3deg);
  }
  	/* 动态背景波浪 - 简化版本 */
	@keyframes move_wave {
		0% {
			transform: translateX(0) scaleY(1);
		}
		50% {
			transform: translateX(-25%) scaleY(1);
		}
		100% {
			transform: translateX(-50%) scaleY(1);
		}
	}

	.tnwave {
		overflow: hidden;
		position: absolute;
		left: 0;
		right: 0;
		top: 0;
		height: 100%;
		z-index: 0;
	}

	.waveWrapperInner {
		position: absolute;
		width: 100%;
		overflow: hidden;
		height: 100%;
	}

	.wave {
		position: absolute;
		left: 0;
		width: 200%;
		height: 100%;
		background-repeat: repeat no-repeat;
		background-position: 0 bottom;
		transform-origin: center bottom;
	}
  	.bgTop {
		opacity: 0.1;
	}

	.waveTop {
		background-size: 50% 65px;
	}

	.waveAnimation .waveTop {
		animation: move_wave 4s linear infinite;
	}

	.bgMiddle {
		opacity: 0.2;
	}

	.waveMiddle {
		background-size: 50% 60px;
	}

	.waveAnimation .waveMiddle {
		animation: move_wave 3.5s linear infinite;
	}

	.bgBottom {
		opacity: 0.3;
	}

	.waveBottom {
		background-size: 50% 45px;
	}

	.waveAnimation .waveBottom {
		animation: move_wave 2s linear infinite;
	}

</style>

