<template>
  <view class="template-ranking tn-safe-area-inset-bottom">
    <!-- 顶部自定义导航 -->
    <tn-nav-bar
      fixed
      alpha
      custom-back
    >
      <template #back>
        <view
          class="tn-custom-nav-bar__back"
          @click="goBack"
        >
          <text class="icon tn-icon-left" />
          <text class="icon tn-icon-home-capsule-fill" />
        </view>
      </template>
    </tn-nav-bar>
    
    <!-- 流星-->
    <view class="tn-satr">
      <view class="sky" />
      <view class="stars">
        <view class="falling-stars">
          <view class="star-fall" />
          <view class="star-fall" />
          <view class="star-fall" />
          <view class="star-fall" />
        </view>
        <view class="small-stars">
          <view class="star" />
          <view class="star" />
          <view class="star" />
          <view class="star" />
          <view class="star" />
          <view class="star" />
          <view class="star" />
          <view class="star" />
          <view class="star" />
          <view class="star" />
          <view class="star" />
          <view class="star" />
        </view>
        <view class="medium-stars">
          <view class="star" />
          <view class="star" />
          <view class="star" />
          <view class="star" />
          <view class="star" />
          <view class="star" />
          <view class="star" />
          <view class="star" />
          <view class="star" />
          <view class="star" />
          <view class="star" />
          <view class="star" />
        </view>
      </view>
    </view>
    
    
    <view class="top-backgroup">
      <image
        src="https://datiqiniu.allpp.cn/static/honor.png"
        mode="widthFix"
        class="backgroud-image"
      />
    </view>
    
    <!-- 加载状态 -->
    <view
      v-if="loading"
      class="loading-container"
    >
      <tn-loading
        type="flower"
        color="#fff"
        size="large"
      />
      <text class="loading-text">
        加载中...
      </text>
    </view>
    
    <!-- 错误提示 -->
    <view
      v-else-if="error"
      class="error-container"
    >
      <text class="error-text">
        {{ error }}
      </text>
      <tn-button
        type="primary"
        size="small"
        @click="fetchRankingData"
      >
        重试
      </tn-button>
    </view>
    
    <!-- 数据展示 -->
    <view v-else>
      <!-- 头像用户信息 -->
      <view class="tn-flex tn-flex-row-around">
        <view
          v-for="(item, index) in topThree"
          :key="index"
          class="user-info__container justify-content-item"
        >
          <view
            :class="['user-info__avatar-' + ['two', 'one', 'three'][index]]"
            class="tn-flex-col-center tn-flex-row-center"
          >
            <view
              class="tn-shadow-blur"
              :style="'background-image:url(' + item.userAvatar + ');width: ' + ['140rpx', '180rpx', '120rpx'][index] + ';height: ' + ['140rpx', '180rpx', '120rpx'][index] + ';background-size: cover;'"
            />
          </view>
          <view
            :class="['user-info__nick-name-' + ['two', 'one', 'three'][index]]"
            class="clamp-text-1"
          >
            {{ item.userName }}
          </view>
          <view
            :class="['user-info__nick-number-' + ['two', 'one', 'three'][index]]"
            class="clamp-text-1"
            style="margin-left: -8rpx;"
          >
            <text class="tn-icon-sword tn-padding-right-xs" /> {{ item.collectionCount }}
          </view>
        </view>
      </view>

      <!-- 组件对应可选项容器 -->
      <view
        class=""
        style="background-color: rgba(255,255,255,1);position: relative;color: #3A4F72;border-radius: 50rpx 50rpx 0 0;margin-top: 21vh;padding: 20rpx 10rpx 130rpx 10rpx;"
      >
        <view
          class=""
          style="padding-top: 20rpx;"
        >
          <view class="nav_title--wrap">
            <view class="nav_title tn-cool-bg-color-15">
              <text class="tn-icon-sword tn-padding-right-sm tn-text-xxl" />
              <text class="tn-text-xl">
              积分榜单 · 全球排行
              </text>
              <text class="tn-icon-sword tn-padding-left-sm tn-text-xxl" />
            </view>
          </view>
        </view>
        <block
          v-for="(item,index) in content"
          :key="index"
        >
          <view class="tn-flex tn-flex-row-between tn-flex-col-center tn-margin">
            <view class="justify-content-item tn-margin-top">
              <view class="tn-flex tn-flex-row-center tn-flex-col-center">
                <view class="tn-flex tn-flex-row-center tn-padding-right">
                  <text
                    class="tn-text-bold tn-text-xxl"
                    style="color: #B0B7C6;"
                  >
                    {{ item.userNumber }}
                  </text>
                </view>
                <view class="tn-flex tn-flex-row-center tn-flex-col-center">
                  <view class="avatar-all">
                    <view
                      class="tn-shadow-blur"
                      :style="'background-image:url('+ item.userAvatar + ');width: 80rpx;height: 80rpx;background-size: cover;'"
                    />
                  </view>
                  <view class="tn-padding-right tn-text-ellipsis">
                    <view class="tn-padding-right tn-padding-left-sm tn-text-bold tn-text-lg clamp-text-1" style="width: 280rpx;">
                      {{ item.userName }}
                    </view>
                    <view
                      class="tn-padding-right tn-padding-left-sm"
                      :class="[`tn-color-${item.color}`]"
                    >
                      <text>{{ item.desc }}</text>
                    </view>
                  </view>
                </view>
              </view>
            </view>
            <view class="justify-content-item tn-flex-row-center tn-margin-top">
              <text class="tn-text-xl tn-padding-right">
                {{ item.collectionCount }}
              </text>
            </view>
          </view>
        </block>
      </view>  
      
          
      <!-- 当前用户排名 -->
      <view
        v-if="currentUser"
        class="tabbar footerfixed dd-glass"
        @click="tn('/subpages/integral/integralHistory')"
      >
        <view class="tn-flex tn-flex-row-between tn-flex-col-center">
          <view class="justify-content-item tn-margin-top">
            <view class="tn-flex tn-flex-row-center tn-flex-col-center">
              <view class="tn-flex tn-flex-row-center tn-padding-right tn-padding-left">
                <text
                  class="tn-text-bold tn-text-xxl"
                  style="color: #B0B7C6;"
                >
                  {{ currentUser.rank }}
                </text>
              </view>
              <view class="tn-flex tn-flex-row-center tn-flex-col-center">
                <view class="avatar-all">
                  <view
                    class="tn-shadow-blur"
                    :style="'background-image:url(' + currentUser.avatar + ');width: 80rpx;height: 80rpx;background-size: cover;'"
                  />
                </view>
                <view class="tn-padding-right tn-text-ellipsis">
                  <view class="tn-padding-right tn-padding-left-sm tn-text-bold tn-text-lg clamp-text-1" style="width: 280rpx;">
                    {{ currentUser.nickname }}
                  </view>
                  <view class="tn-padding-right tn-padding-left-sm tn-color-grey">
                    <text>{{ currentUser.duanweiName }}</text>
                  </view>
                </view>
              </view>
            </view>
          </view>
          <view class="justify-content-item tn-flex-row-center tn-margin-top tn-padding-right">
            <text class="tn-text-xl tn-padding-right">
              {{ currentUser.integral }}
            </text>
          </view>
        </view>
      </view>
      
      <!-- 我的排名 -->
      <view
        v-else
        class="tabbar footerfixed dd-glass"
        @click="tn('/minePages/integral')"
      >
        <view class="tn-flex tn-flex-row-between tn-flex-col-center">
          <view class="justify-content-item tn-margin-top">
            <view class="tn-flex tn-flex-row-center tn-flex-col-center">
              <view class="tn-flex tn-flex-row-center tn-padding-right tn-padding-left">
                <text
                  class="tn-text-bold tn-text-xxl"
                  style="color: #B0B7C6;"
                >
                  100+
                </text>
              </view>
              <view class="tn-flex tn-flex-row-center tn-flex-col-center">
                <view class="avatar-all">
                  <view
                    class="tn-shadow-blur"
                    :style="'background-image:url(' + (userInfo.avatar || 'https://datiqiniu.allpp.cn/static/userinfo_avatar.png') + '); width: 80rpx; height: 80rpx; background-size: cover;'"
                  />
                </view>
                <view class="tn-padding-right tn-text-ellipsis">
                  <view class="tn-padding-right tn-padding-left-sm tn-text-bold tn-text-lg clamp-text-1" style="width: 240rpx;">
                    {{ userInfo.nickname || '匿名用户' }}
                  </view>
                  <view class="tn-padding-right tn-padding-left-sm" :class="['tn-color-' + (duanweiColorMapping[userInfo.duanwei_id || 0] || 'grey')]">
                    <text>{{ duanwei[userInfo.duanwei_id || 0] || '暂无段位' }}</text>
                  </view>
                </view>
              </view>
            </view>
          </view>
          <view class="justify-content-item tn-flex-row-center tn-margin-top tn-padding-right">
            <text class="tn-text-xl tn-padding-right">
              {{ userInfo.integral || 0 }}
            </text>
          </view>
        </view>
      </view>
    </view>
  </view>
</template>

<script>
  import template_page_mixin from '@/libs/mixin/template_page_mixin.js'
  export default {
    name: 'TemplateRanking',
    mixins: [template_page_mixin],
    data(){
      return {
        content: [], // 排行榜数据
        loading: true, // 加载状态
        error: '', // 错误信息
        topThree: [], // 前三名数据
        currentUser: null, // 当前用户排名信息
        userInfo: {}, // 当前用户信息
        duanwei: {
          1: '最强王者',
          2: '傲视宗师',
          3: '超凡大师',
          4: '璀璨钻石',
          5: '华贵铂金',
          6: '荣耀黄金',
          7: '不屈白银',
          8: '英勇黄铜',
          9: '坚韧黑铁',
          0: '暂无段位'
        },
        duanweiColorMapping: {
          1: 'orangeyellow',
          2: 'indigo',
          3: 'brown',
          4: 'grey',
          5: 'grey',
          6: 'grey',
          7: 'grey',
          8: 'grey',
          9: 'grey',
          0: 'grey'
        }
      }
    },
    onLoad() {
      // 从本地存储获取当前用户信息
      this.userInfo = uni.getStorageSync('userInfo') || {}
      // 计算用户段位
      this.userInfo.duanwei_id = this.calculateDuanweiByIntegral(this.userInfo.integral)
      this.fetchRankingData()
    },
    methods: {
      // 返回上一页
      goBack() {
        uni.navigateBack()
      },
      
      // 获取排行榜数据
      async fetchRankingData() {
        this.loading = true
        this.error = ''
        try {
          const res = await this.$api.apiUserIntegralRanking()
          if (res.code === 1) {
            // 处理API响应数据
            this.handleRankingData(res.data)
          } else {
            this.error = res.msg || '获取排行榜数据失败'
          }
        } catch (err) {
          console.error('获取排行榜数据异常:', err)
          this.error = '网络错误，请稍后重试'
        } finally {
          this.loading = false
        }
      },
      
      // 根据积分计算段位ID
      calculateDuanweiByIntegral(integral) {
        const integralNum = parseFloat(integral) || 0;
        if (integralNum >= 10000) {
          return 1; // 最强王者
        } else if (integralNum >= 8000) {
          return 2; // 傲视宗师
        } else if (integralNum >= 6000) {
          return 3; // 超凡大师
        } else if (integralNum >= 4000) {
          return 4; // 璀璨钻石
        } else if (integralNum >= 2500) {
          return 5; // 华贵铂金
        } else if (integralNum >= 1500) {
          return 6; // 荣耀黄金
        } else if (integralNum >= 800) {
          return 7; // 不屈白银
        } else if (integralNum >= 300) {
          return 8; // 英勇黄铜
        } else if (integralNum > 0) {
          return 9; // 坚韧黑铁
        } else {
          return 0; // 暂无段位
        }
      },
      
      // 处理排行榜数据
      handleRankingData(data) {
        // API返回的数据格式：data 是对象，直接包含排行榜数据和当前用户信息
        const { me, ...rest } = data || {}
        
        // 处理排行榜数据：将对象转换为数组
        let rankingList = Object.keys(rest)
          .map(key => rest[key])
          .filter(item => typeof item === 'object' && item.integral) // 过滤有效数据
          .sort((a, b) => parseFloat(b.integral) - parseFloat(a.integral)) // 按积分降序排序

        // 分离前三名数据
        const originalTopThree = rankingList.slice(0, 3).map((item, index) => {
          const duanweiId = this.calculateDuanweiByIntegral(item.integral);
          return {
            userAvatar: item.avatar || 'https://datiqiniu.allpp.cn/static/userinfo_avatar.png',
            userName: item.nickname || '匿名用户',
            collectionCount: item.integral || 0,
            duanwei_id: duanweiId,
            duanweiName: this.duanwei[duanweiId] || '暂无段位'
          }
        })
        // 调整前三名顺序：第二名、第一名、第三名，确保第一名在中间
        if (originalTopThree.length === 3) {
          this.topThree = [
            originalTopThree[1], // 第二名
            originalTopThree[0], // 第一名
            originalTopThree[2]  // 第三名
          ]
        } else {
          this.topThree = originalTopThree
        }
        
        // 处理剩余排名数据
        this.content = rankingList.map((item, index) => {
          const rank = index + 1
          const duanweiId = this.calculateDuanweiByIntegral(item.integral);
          
          return {
            userAvatar: item.avatar || 'https://datiqiniu.allpp.cn/static/userinfo_avatar.png',
            userNumber: rank < 10 ? `0${rank}` : `${rank}`,
            userName: item.nickname || '匿名用户',
            date: new Date().getFullYear() + '年' + (new Date().getMonth() + 1) + '月' + new Date().getDate() + '日',
            desc: this.duanwei[duanweiId] || '暂无段位',
            color: this.duanweiColorMapping[duanweiId] || 'grey',
            mainImage: [],
            viewUser: {
              latestUserAvatar: [{src: 'https://datiqiniu.allpp.cn/static/userinfo_avatar.png'},],
              viewUserCount: Math.floor(Math.random() * 100) + 10
            },
            collectionCount: item.integral || 0,
            commentCount: Math.floor(Math.random() * 50) + 10,
            likeCount: Math.floor(Math.random() * 100) + 10
          }
        })
        
        // 使用API返回的"me"字段作为当前用户信息
        if (me) {
          const userInfo = uni.getStorageSync('userInfo') || {}
          const currentUserRank = rankingList.findIndex(item => item.id == me.id) + 1
          const duanweiId = this.calculateDuanweiByIntegral(me.integral);
          this.currentUser = {
            rank: currentUserRank > 0 ? currentUserRank : '100+',
            duanweiName: this.duanwei[duanweiId] || '暂无段位',
            avatar: me.avatar || userInfo.avatar || 'https://datiqiniu.allpp.cn/static/userinfo_avatar.png',
            nickname: me.nickname || userInfo.nickname || '匿名用户',
            integral: me.integral || userInfo.integral || 0
          }
        } else if (this.userInfo) {
          // 如果没有me字段，使用本地用户信息
          const duanweiId = this.calculateDuanweiByIntegral(this.userInfo.integral);
          this.currentUser = {
            rank: '100+',
            duanweiName: this.duanwei[duanweiId] || '暂无段位',
            avatar: this.userInfo.avatar || 'https://datiqiniu.allpp.cn/static/userinfo_avatar.png',
            nickname: this.userInfo.nickname || '匿名用户',
            integral: this.userInfo.integral || 0
          }
        }
      },
      
      // 跳转
      tn(e) {
        uni.navigateTo({
          url: e,
        });
      },
    }
  }
</script>

<style lang="scss" scoped>
  /* 背景*/
  .template-ranking {
    margin: 0;
    width: 100%;
    height: 100%;
    /* background: linear-gradient(-120deg, #5969f6, #0976ea, #01BEFF, #00F5D4); */
    background: linear-gradient(-120deg, #F15BB5, #9A5CE5, #01BEFF, #00F5D4);
    /* background: linear-gradient(-120deg,  #9A5CE5, #01BEFF, #00F5D4, #43e97b); */
    /* background: linear-gradient(-120deg,#c471f5, #ec008c, #ff4e50,#f9d423); */
    /* background: linear-gradient(-120deg, #0976ea, #c471f5, #f956b6, #ea7e0a); */
    background-size: 500% 500%;
    animation: gradientBG 15s ease infinite;
  }
  
  @keyframes gradientBG {
    0% {
      background-position: 0% 50%;
    }
  
    50% {
      background-position: 100% 50%;
    }
  
    100% {
      background-position: 0% 50%;
    }
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
  
  /* 标题 start */
  .nav_title {
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    
    &--wrap {
      position: relative;
      display: flex;
      height: 120rpx;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      background-image: url('https://datiqiniu.allpp.cn/static/title44.png');
      background-size: cover;
    }
  }
  /* 标题 end */
  
  /* 图标 start */
  
  
  /* 用户信息 start */
    .user-info {
      &__container {
        margin-top: -10vh;
      }
      
      &__avatar-one {
        margin-top: -90rpx;
        width: 180rpx;
        height: 180rpx;
        border: 8rpx solid rgba(255,255,255,0.05);
        border-radius: 50%;
        overflow: hidden;
        box-shadow: 0rpx 0rpx 80rpx 0rpx rgba(0, 0, 0, 0.15);
      }
      &__avatar-two {
        width: 140rpx;
        height: 140rpx;
        border: 8rpx solid rgba(255,255,255,0.05);
        border-radius: 50%;
        overflow: hidden;
        box-shadow: 0rpx 0rpx 80rpx 0rpx rgba(0, 0, 0, 0.15);
      }
      &__avatar-three {
        margin-top: 60rpx;
        width: 120rpx;
        height: 120rpx;
        border: 8rpx solid rgba(255,255,255,0.05);
        border-radius: 50%;
        overflow: hidden;
        box-shadow: 0rpx 0rpx 80rpx 0rpx rgba(0, 0, 0, 0.15);
      }
      
      &__nick-name-one {
        width: 180rpx;
        color: #FFFFFF;
        margin-top: 26rpx;
        font-size: 26rpx;
        font-weight: 600;
        text-align: center;
      }
      &__nick-name-two {
        width: 140rpx;
        color: #FFFFFF;
        margin-top: 26rpx;
        font-size: 24rpx;
        font-weight: 600;
        text-align: center;
      }
      &__nick-name-three {
        width: 120rpx;
        color: #FFFFFF;
        margin-top: 26rpx;
        font-size: 24rpx;
        font-weight: 600;
        text-align: center;
      }
      &__nick-number-one {
        width: 180rpx;
        color: #FFFFFF;
        margin-top: 13rpx;
        font-size: 26rpx;
        font-weight: 600;
        text-align: center;
      }
      &__nick-number-two {
        width: 140rpx;
        color: #FFFFFF;
        margin-top: 13rpx;
        font-size: 24rpx;
        font-weight: 600;
        text-align: center;
      }
      &__nick-number-three {
        width: 120rpx;
        color: #FFFFFF;
        margin-top: 13rpx;
        font-size: 24rpx;
        font-weight: 600;
        text-align: center;
      }
    }
    .avatar-all {
      width: 80rpx;
      height: 80rpx;
      border: 4rpx solid rgba(255,255,255,0.05);
      border-radius: 50%;
      overflow: hidden;
      box-shadow: 0rpx 0rpx 80rpx 0rpx rgba(0, 0, 0, 0.15);
    }
  /* 用户信息 end */
  
  
  /* 文字截取*/
  .clamp-text-1 {
    -webkit-line-clamp: 1;
    line-clamp: 1;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    text-overflow: ellipsis;
    overflow: hidden;
  }

  .clamp-text-2 {
    -webkit-line-clamp: 2;
    line-clamp: 2;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    text-overflow: ellipsis;
    overflow: hidden;
  }
  
  /* 顶部背景图 start */
  .top-backgroup {
    opacity: 0.8;
    height: 350rpx;
    z-index: -1;
    padding-top: 27vh;
    
    .backgroud-image {
      width: 100%;
      height: 350rpx;
      z-index: -1;
    }
  }
  /* 顶部背景图 end */
  
  /* 流星*/
  .tn-satr {
    position: fixed;
    width: 100%;
    height: 600px;
    overflow: hidden;
    flex-shrink: 0;
    z-index: 999;
  }
  
  .stars {
    position: absolute;
    z-index: 1;
    width: 100%;
    height: 400px;
  }
  
  .star {
    border-radius: 50%;
    background: #ffffff;
    box-shadow: 0px 0px 6px 0px rgba(255, 255, 255, 0.8);
  }
  
  .small-stars .star {
    position: absolute;
    width: 3px;
    height: 3px;
  }
  .small-stars .star:nth-child(2n) {
    opacity: 0;
    -webkit-animation: star-blink 1.2s linear infinite alternate;
            animation: star-blink 1.2s linear infinite alternate;
  }
  .small-stars .star:nth-child(1) {
    left: 40px;
    bottom: 50px;
  }
  .small-stars .star:nth-child(2) {
    left: 200px;
    bottom: 40px;
  }
  .small-stars .star:nth-child(3) {
    left: 60px;
    bottom: 120px;
  }
  .small-stars .star:nth-child(4) {
    left: 140px;
    bottom: 250px;
  }
  .small-stars .star:nth-child(5) {
    left: 400px;
    bottom: 300px;
  }
  .small-stars .star:nth-child(6) {
    left: 170px;
    bottom: 80px;
  }
  .small-stars .star:nth-child(7) {
    left: 200px;
    bottom: 360px;
    -webkit-animation-delay: .2s;
            animation-delay: .2s;
  }
  .small-stars .star:nth-child(8) {
    left: 250px;
    bottom: 320px;
  }
  .small-stars .star:nth-child(9) {
    left: 300px;
    bottom: 340px;
  }
  .small-stars .star:nth-child(10) {
    left: 130px;
    bottom: 320px;
    -webkit-animation-delay: .5s;
            animation-delay: .5s;
  }
  .small-stars .star:nth-child(11) {
    left: 230px;
    bottom: 330px;
    -webkit-animation-delay: 7s;
            animation-delay: 7s;
  }
  .small-stars .star:nth-child(12) {
    left: 300px;
    bottom: 360px;
    -webkit-animation-delay: .3s;
            animation-delay: .3s;
  }
  @-webkit-keyframes star-blink {
    50% {
      width: 3px;
      height: 3px;
      opacity: 1;
    }
  }
  @keyframes star-blink {
    50% {
      width: 3px;
      height: 3px;
      opacity: 1;
    }
  }
  .medium-stars .star {
    position: absolute;
    width: 3px;
    height: 3px;
    opacity: 0;
    -webkit-animation: star-blink 1.2s ease-in infinite alternate;
            animation: star-blink 1.2s ease-in infinite alternate;
  }
  .medium-stars .star:nth-child(1) {
    left: 300px;
    bottom: 50px;
  }
  .medium-stars .star:nth-child(2) {
    left: 400px;
    bottom: 40px;
    -webkit-animation-delay: .4s;
            animation-delay: .4s;
  }
  .medium-stars .star:nth-child(3) {
    left: 330px;
    bottom: 300px;
    -webkit-animation-delay: .2s;
            animation-delay: .2s;
  }
  .medium-stars .star:nth-child(4) {
    left: 460px;
    bottom: 300px;
    -webkit-animation-delay: .9s;
            animation-delay: .9s;
  }
  .medium-stars .star:nth-child(5) {
    left: 300px;
    bottom: 150px;
    -webkit-animation-delay: 1.2s;
            animation-delay: 1.2s;
  }
  .medium-stars .star:nth-child(6) {
    left: 440px;
    bottom: 120px;
    -webkit-animation-delay: 1s;
            animation-delay: 1s;
  }
  .medium-stars .star:nth-child(7) {
    left: 200px;
    bottom: 140px;
    -webkit-animation-delay: .8s;
            animation-delay: .8s;
  }
  .medium-stars .star:nth-child(8) {
    left: 30px;
    bottom: 480px;
    -webkit-animation-delay: .3s;
            animation-delay: .3s;
  }
  .medium-stars .star:nth-child(9) {
    left: 460px;
    bottom: 400px;
    -webkit-animation-delay: 1.2s;
            animation-delay: 1.2s;
  }
  .medium-stars .star:nth-child(10) {
    left: 150px;
    bottom: 10px;
    -webkit-animation-delay: 1s;
            animation-delay: 1s;
  }
  .medium-stars .star:nth-child(11) {
    left: 420px;
    bottom: 450px;
    -webkit-animation-delay: 1.2s;
            animation-delay: 1.2s;
  }
  .medium-stars .star:nth-child(12) {
    left: 340px;
    bottom: 180px;
    -webkit-animation-delay: 1.1s;
            animation-delay: 1.1s;
  }
  
  .star-fall {
    position: relative;
    border-radius: 2px;
    width: 80px;
    height: 2px;
    overflow: hidden;
    -webkit-transform: rotate(-20deg);
            transform: rotate(-20deg);
  }
  .star-fall:after {
    content: "";
    position: absolute;
    width: 50px;
    height: 2px;
    background: -webkit-gradient(linear, right top, left top, from(rgba(0, 0, 0, 0)), to(rgba(255, 255, 255, 0.4)));
    background: linear-gradient(to left, rgba(0, 0, 0, 0) 0%, rgba(255, 255, 255, 0.4) 100%);
    left: 100%;
    -webkit-animation: star-fall 3.6s linear infinite;
            animation: star-fall 3.6s linear infinite;
  }
  
  .star-fall:nth-child(1) {
    left: 80px;
    bottom: -100px;
  }
  .star-fall:nth-child(1):after {
    -webkit-animation-delay: 2.4s;
            animation-delay: 2.4s;
  }
  
  .star-fall:nth-child(2) {
    left: 200px;
    bottom: -200px;
  }
  .star-fall:nth-child(2):after {
    -webkit-animation-delay: 2s;
            animation-delay: 2s;
  }
  
  .star-fall:nth-child(3) {
    left: 430px;
    bottom: -50px;
  }
  .star-fall:nth-child(3):after {
    -webkit-animation-delay: 3.6s;
            animation-delay: 3.6s;
  }
  
  .star-fall:nth-child(4) {
    left: 400px;
    bottom: 100px;
  }
  .star-fall:nth-child(4):after {
    -webkit-animation-delay: .2s;
            animation-delay: .2s;
  }
  
  @-webkit-keyframes star-fall {
    20% {
      left: -100%;
    }
    100% {
      left: -100%;
    }
  }
  
  @keyframes star-fall {
    20% {
      left: -100%;
    }
    100% {
      left: -100%;
    }
  }


  /* 底部 start*/
  .footerfixed{
   position: fixed;
   width: 100%;
   bottom: 0;
   z-index: 999;
   background-color: rgba(255,255,255,0.5);
   box-shadow: 0rpx 0rpx 30rpx 0rpx rgba(0, 0, 0, 0.07);
  }
  
  .tabbar {
    align-items: center;
    min-height: 130rpx;
    padding: 0;
    height: calc(130rpx + env(safe-area-inset-bottom) / 2);
    padding-bottom: calc(30rpx + env(safe-area-inset-bottom) / 2);
    padding-left: 10rpx;
    padding-right: 10rpx;
  }
  
    /* 毛玻璃*/
  .dd-glass {
     width: 100%;
     backdrop-filter: blur(20rpx);
    -webkit-backdrop-filter: blur(20rpx);
  }
  
  /* 加载状态 */
  .loading-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 60vh;
    color: #fff;
  }
  
  .loading-text {
    margin-top: 20rpx;
    font-size: 28rpx;
    color: #fff;
  }
  
  /* 错误提示 */
  .error-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 60vh;
    color: #fff;
  }
  
  .error-text {
    margin-bottom: 30rpx;
    font-size: 32rpx;
    color: #fff;
  }
  
</style>