<template>
  <view class="follow-list tn-safe-area-inset-bottom">
    <!-- 顶部导航栏 -->
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
      <view class="custom-nav tn-flex tn-flex-col-center tn-flex-row-center">
        <text class="tn-text-bold tn-text-xl">我订阅的倒计时</text>
      </view>
    </tn-nav-bar>
    </view>
    
    <!-- 页面内容 -->
    <view :style="{paddingTop: vuex_custom_bar_height + 'px'}"></view>
    
    <!-- 当前日期显示 -->
    <view class="current-date tn-margin-xs tn-text-center">
      <text class="tn-text-lg tn-color-gray--dark">{{ currentDate }}</text>
    </view>
    
    <!-- 订阅的倒计时列表 -->
    <view class="countdown-list tn-margin-xl">
      <tn-list-view title="我订阅的倒计时">
        <tn-list-cell 
          v-for="(item, index) in followedCountdowns" 
          :key="item.id"
          arrow
          hover
          @click="viewCountdownDetail(item.id)"
        >
          <view slot="default" class="list-item-content">
            <view class="list-item-header tn-flex tn-flex-row-between">
              <view class="list-item-title tn-text-bold">{{ item.title }}</view>
            </view>
            <view class="list-item-days tn-margin-top-xs tn-flex tn-flex-row-between tn-align-items-center">
              <text 
                :class="[
                  'tn-text-bold', 
                  item.statusType === 'expired' ? 'tn-color-gray' : 
                  item.statusType === 'upcoming' ? 'tn-color-red' : 'tn-color-blue'
                ]"
              >
                {{ item.daysText }}
              </text>
              <text 
                style="margin: 0 25rpx 0 0; display: flex; align-items: center; height: 100%;"
                :class="item.statusType === 'expired' ? 'tn-color-gray' : (item.statusType === 'upcoming' ? 'tn-color-red' : 'tn-color-green')"
              >
                {{ item.statusText }}
              </text>
            </view>
            <view class="list-item-info tn-flex tn-flex-row-between">
              <view class="list-item-date">目标日期：{{ item.target_date }}</view>
              <view class="follow-count tn-margin-left-xs"> {{ item.follow_count }} 人订阅</view>
            </view>
          </view>
          <view slot="extra" class="list-item-extra tn-flex tn-flex-row tn-align-items-center">
            <tn-color-icon
              name="heart"
              :color="item.is_followed === 1 ? 'tn-color-red' : 'tn-color-gray'"
              :size="28"
              @click.stop="toggleFollow(item.id, index)"
              class="action-icon"
            />
            <tn-badge 
              v-if="item.is_followed === 1" 
              :type="'tn-color-green'" 
              size="small" 
              text="已订阅"
              :style="{marginLeft: '8rpx'}"
            />
          </view>
        </tn-list-cell>
        <tn-empty v-if="followedCountdowns.length === 0 && !followedLoading" mode="list" text="暂无订阅的倒计时" />
        <tn-loading v-if="followedLoading" type="spinner" text="加载中..." class="tn-margin-xl" />
        <view v-if="!followedLoading && followedCountdowns.length > 0 && !followedHasMore" class="no-more tn-text-center tn-color-gray tn-padding-xl">
          没有更多数据了
        </view>
      </tn-list-view>
    </view>
    
    <!-- 我创建的倒计时列表 -->
    <view class="countdown-list tn-margin-xl">
      <tn-list-view title="我创建的倒计时">
        <tn-list-cell 
          v-for="(item, index) in createdCountdowns" 
          :key="item.id"
          arrow
          hover
          @click="viewCountdownDetail(item.id)"
        >
          <view slot="default" class="list-item-content">
            <view class="list-item-header tn-flex tn-flex-row-between">
              <view class="list-item-title tn-text-bold">{{ item.title }}</view>
            </view>
            <view class="list-item-days tn-margin-top-xs tn-flex tn-flex-row-between tn-align-items-center">
              <text 
                :class="[
                  'tn-text-bold', 
                  item.statusType === 'expired' ? 'tn-color-gray' : 
                  item.statusType === 'upcoming' ? 'tn-color-red' : 'tn-color-blue'
                ]"
              >
                {{ item.daysText }}
              </text>
              <text 
                style="margin: 0 25rpx 0 0; display: flex; align-items: center; height: 100%;"
                :class="item.statusType === 'expired' ? 'tn-color-gray' : (item.statusType === 'upcoming' ? 'tn-color-red' : 'tn-color-green')"
              >
                {{ item.statusText }}
              </text>
            </view>
            <view class="list-item-info tn-flex tn-flex-row-between">
              <view class="list-item-date">目标日期：{{ item.target_date }}</view>
              <view class="follow-count tn-margin-left-xs"> {{ item.follow_count }} 人订阅</view>
            </view>
          </view>
          <view slot="extra" class="list-item-extra">
            <tn-color-icon
              name="settings"
              color="tn-color-primary"
              @click.stop="openEditPopup(item)"
              class="action-icon"
            />
          </view>
        </tn-list-cell>
        <tn-empty v-if="createdCountdowns.length === 0 && !createdLoading" mode="list" text="暂无创建的倒计时" />
        <tn-loading v-if="createdLoading" type="spinner" text="加载中..." class="tn-margin-xl" />
        <view v-if="!createdLoading && createdCountdowns.length > 0 && !createdHasMore" class="no-more tn-text-center tn-color-gray tn-padding-xl">
          没有更多数据了
        </view>
      </tn-list-view>
       <view style="padding-bottom: 120rpx;"></view>
    </view>
    
    <!-- 底部按钮区域 -->
      <view class="tn-flex tn-flex-row-between tn-footerfixed" style="position: fixed; bottom: 20rpx; left: 0; right: 0; z-index: 999;">
      <view class="tn-flex-1 justify-content-item tn-margin-xs tn-text-center">
        
        <tn-button 
          class="bottom-button create-button" 
          backgroundColor="tn-cool-bg-color-6" 
          padding="40rpx 0"
          width="100%" 
          shadow 
          font-bold
          @click="createCountdown"
        >
          <text class="tn-icon-add tn-padding-right-xs tn-color-white"></text>
          <text class="tn-color-white">新建倒计时</text>
        </tn-button>
      </view>
      <view class="tn-flex-1 justify-content-item tn-margin-xs tn-text-center">
        <tn-button 
          class="bottom-button create-button" 
          backgroundColor="tn-cool-bg-color-9" 
          padding="40rpx 0"
          width="100%" 
          shadow 
          font-bold
          @click="allCountdown"
        >
          <text class="tn-icon-search-list tn-padding-right-xs tn-color-white"></text>
          <text class="tn-color-white">全部倒计时</text>
        </tn-button>
      </view>
    </view>

    <!-- 登录提示模态框 -->
    <tn-modal
      v-model="showLoginModal"
      :title="loginModalConfig.title"
      :content="loginModalConfig.content"
      :button="loginModalConfig.button"
      @click="handleLoginModalClick"
    ></tn-modal>

    <!-- 新建倒计时弹窗 -->
    <tn-popup v-model="showCreatePopup" mode="bottom" height="50%" :closeBtn="true">
      <view class="popup-content tn-padding-lg">
        <view class="popup-title tn-text-xxl tn-text-bold tn-text-center tn-margin-bottom-xl">
          新建倒计时
        </view>
        <tn-form :label-width="150">
          <tn-form-item label="标题" required>
            <tn-input
              v-model="newCountdown.title"
              placeholder="请输入倒计时名称"
              :maxlength="50"
              clearable
              :border="true"
              border-radius="lg"
            />
          </tn-form-item>
          <tn-form-item label="对自己说">
            <tn-input
              v-model="newCountdown.description"
              placeholder="请输入我对自己说的话（可选）"
              :maxlength="500"
              type="textarea"
              :rows="3"
              clearable
              :border="true"
              border-radius="lg"
            />
          </tn-form-item>
          <tn-form-item label="目标日期" required>
            <tn-button
              v-model="newCountdown.target_date"
              mode="date"
              type="default"
              shape="round"
              @click="showCalendar = true"
              :border="true"
              border-radius="lg"
            >
              {{ newCountdown.target_date || '请选择目标日期' }}
            </tn-button>
          </tn-form-item>
        </tn-form>
        <tn-button
          shape="round"
          size="lg"
          :shadow="true"
          background-color="#01BEFF"
          font-color="#FFFFFF"
          margin="20rpx 0"
          width="100%"
          @click="saveCountdown"
        >
          保存
        </tn-button>
      </view>
    </tn-popup>
  </view>
</template>

<script>
  import template_page_mixin from '@/libs/mixin/template_page_mixin.js'
  import { getUserInfo, isUserLoggedIn } from '@/util/userStore.js'
  
  export default {
    name: 'FollowList',
    mixins: [template_page_mixin],
    data() {
      return {
        // 当前日期
        currentDate: '',
        // 订阅的倒计时列表
        followedCountdowns: [],
        // 我创建的倒计时列表
        createdCountdowns: [],
        // 订阅列表加载状态
        followedLoading: false,
        // 创建列表加载状态
        createdLoading: false,
        // 订阅列表是否还有更多数据
        followedHasMore: true,
        // 创建列表是否还有更多数据
        createdHasMore: true,
        // 分页参数 - 订阅列表
        followedPage: 1,
        // 分页参数 - 创建列表
        createdPage: 1,
        // 每页数量
        pageSize: 10,
        // 登录提示模态框
        showLoginModal: false,
        // 登录模态框配置
        loginModalConfig: {
          title: '提示',
          content: '',
          button: [
            { text: '取消', backgroundColor: '#F5F5F5', fontColor: '#666666' },
            { text: '去登录', backgroundColor: '#2DCB56', fontColor: '#FFFFFF' }
          ]
        },
        // 新建倒计时弹窗显示状态
        showCreatePopup: false,
        // 新倒计时信息
        newCountdown: {
          title: '',
          description: '',
          targetDate: ''
        },
      }
    },
    computed: {
      // 从Vuex获取订阅模板ID
      subscribeTemplates() {
        return this.$store.state.vuex_subscribe_templates || []
      }
    },
    onLoad() {
      // 初始化页面数据
      this.initData()
    },
    onShow() {
      // 刷新当前日期
      this.updateCurrentDate()
      // 刷新两个倒计时列表
      this.refreshAllCountdowns()
    },
    // 下拉刷新
    onPullDownRefresh() {
      this.refreshAllCountdowns()
    },
    // 上拉加载更多
    onReachBottom() {
      // 只在订阅列表还有更多数据时加载
      if (!this.followedLoading && this.followedHasMore) {
        this.loadMoreFollowedCountdowns()
      }
    },
    methods: {
      // 初始化页面数据
      initData() {
        // 更新当前日期
        this.updateCurrentDate()
        // 加载两个倒计时列表
        this.loadAllCountdowns()
        // 获取订阅模板ID
        this.getSubscribeTemplates()
      },
      
      // 更新当前日期
      updateCurrentDate() {
        const now = new Date()
        const year = now.getFullYear()
        const month = now.getMonth() + 1
        const day = now.getDate()
        const weekDays = ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六']
        const weekDay = weekDays[now.getDay()]
        this.currentDate = `${year}年${month}月${day}日 ${weekDay}`
      },
      
      // 加载所有倒计时列表
      loadAllCountdowns() {
        if (!isUserLoggedIn()) {
          // 未登录，跳转到登录页面
          uni.navigateTo({ url: '/subpages/user/login' })
          return
        }
        
        // 并行加载两个列表
        Promise.all([
          this.loadFollowedCountdowns(true),
          this.loadCreatedCountdowns(true)
        ])
      },
      // 跳转全部倒计时
      allCountdown() {
        uni.navigateTo({ url: '/subpages/countdown/list' })
      },
      // 刷新所有倒计时列表
      refreshAllCountdowns() {
        if (!isUserLoggedIn()) {
          // 未登录，跳转到登录页面
          uni.navigateTo({ url: '/subpages/user/login' })
          uni.stopPullDownRefresh()
          return
        }
        
        // 并行刷新两个列表
        Promise.all([
          this.loadFollowedCountdowns(true),
          this.loadCreatedCountdowns(true)
        ]).finally(() => {
          uni.stopPullDownRefresh()
        })
      },
      
      // 刷新订阅的倒计时列表
      refreshFollowedCountdowns() {
        if (!isUserLoggedIn()) {
          // 未登录，跳转到登录页面
          uni.navigateTo({ url: '/subpages/user/login' })
          uni.stopPullDownRefresh()
          return
        }
        
        this.loadFollowedCountdowns(true)
      },
      
      // 加载更多订阅的倒计时
      loadMoreFollowedCountdowns() {
        this.loadFollowedCountdowns(false)
      },
      
      // 加载订阅的倒计时列表
      loadFollowedCountdowns(refresh = false) {
        return new Promise((resolve, reject) => {
          // 如果正在加载，直接返回
          if (this.followedLoading) {
            resolve()
            return
          }
          
          // 刷新时重置分页参数
          if (refresh) {
            this.followedPage = 1
            this.followedHasMore = true
          }
          
          // 如果没有更多数据，直接返回
          if (!this.followedHasMore && !refresh) {
            resolve()
            return
          }
          
          this.followedLoading = true
          
          // 调用API获取订阅的倒计时列表
          const params = {
            page_no: this.followedPage,
            page_size: this.pageSize
          }
          
          this.$api.apiGetFollowedCountdowns(params).then(res => {
            if (res.code === 1) {
              let list = res.data.list || []
              
              // 计算每个倒计时的精确状态
              list = list.map(item => this.calculateCountdownStatus(item))
              
              if (refresh) {
                this.followedCountdowns = list
              } else {
                this.followedCountdowns = [...this.followedCountdowns, ...list]
              }
              
              // 判断是否还有更多数据
              this.followedHasMore = list.length >= this.pageSize
              
              // 只有在加载更多时才增加页码
              if (!refresh) {
                this.followedPage++
              }
            }
            resolve()
          }).catch(err => {
            console.error('获取订阅的倒计时列表失败', err)
            this.$func.showToast('获取订阅的倒计时列表失败')
            resolve()
          }).finally(() => {
            this.followedLoading = false
          })
        })
      },
      
      // 加载创建的倒计时列表
      loadCreatedCountdowns(refresh = true) {
        return new Promise((resolve, reject) => {
          // 如果正在加载，直接返回
          if (this.createdLoading) {
            resolve()
            return
          }
          
          // 刷新时重置分页参数
          if (refresh) {
            this.createdPage = 1
            this.createdHasMore = true
          }
          
          // 如果没有更多数据，直接返回
          if (!this.createdHasMore && !refresh) {
            resolve()
            return
          }
          
          this.createdLoading = true
          
          // 调用API获取创建的倒计时列表
          const params = {
            page_no: this.createdPage,
            page_size: this.pageSize
          }
          
          this.$api.apiGetCreatedCountdowns(params).then(res => {
            if (res.code === 1) {
              let list = res.data.list || []
              
              // 计算每个倒计时的精确状态
              list = list.map(item => this.calculateCountdownStatus(item))
              
              if (refresh) {
                this.createdCountdowns = list
              } else {
                this.createdCountdowns = [...this.createdCountdowns, ...list]
              }
              
              // 判断是否还有更多数据
              this.createdHasMore = list.length >= this.pageSize
              
              // 只有在加载更多时才增加页码
              if (!refresh) {
                this.createdPage++
              }
            }
            resolve()
          }).catch(err => {
            console.error('获取创建的倒计时列表失败', err)
            this.$func.showToast('获取创建的倒计时列表失败')
            resolve()
          }).finally(() => {
            this.createdLoading = false
          })
        })
      },
      
      // 计算倒计时状态
      calculateCountdownStatus(item) {
        // 计算天数差
        const now = new Date()
        const targetDate = new Date(item.target_date)
        const diffTime = targetDate - now
        const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24)) + 1
        
        // 判断状态
        const isExpired = diffDays <= 0
        const isUpcoming = !isExpired && diffDays <= 7
        
        // 确定状态类型
        let statusType = 'ongoing'
        let statusText = '进行中'
        let daysText = `还有 ${item.days} 天`
        
        if (isExpired) {
          statusType = 'expired'
          statusText = '已过期'
          const expiredDays = Math.abs(diffDays)
          daysText = `已过期 ${expiredDays} 天`
        } else if (isUpcoming) {
          statusType = 'upcoming'
          statusText = '即将到期'
          if (diffDays === 1) {
            daysText = '今天到期'
          } else {
            daysText = `仅剩 ${item.days} 天`
          }
        }
        
        return {
          ...item,
          isExpired,
          isUpcoming,
          statusType,
          statusText,
          daysText
        }
      },
      
          // 订阅/取消订阅微信小程序消息
      toggleFollow(id, index) {
        if (!isUserLoggedIn()) {
          // 未登录，跳转到登录页面
          uni.navigateTo({ url: '/subpages/user/login' })
          return
        }
        
        // 获取当前倒计时项
        const countdownItem = this.followedCountdowns[index]
        if (!countdownItem) return
        
        // 如果已经订阅，直接取消订阅
        if (countdownItem.is_followed === 1) {
          // 调用后端API保存订阅状态
          this.$api.apiSubscribeCountdown({ 
            id: id,
            subscribe_status: 0
          }).then(res => {
            if (res.code === 1) {
              // 更新本地数据
              countdownItem.is_followed = 0
              this.$func.showToast('🔔 已取消订阅\n\n您将不再收到该倒计时的提醒消息')
            } else {
              this.$func.showToast('取消订阅失败，请稍后重试')
            }
          }).catch(err => {
            console.error('取消订阅失败', err)
            this.$func.showToast('取消订阅失败，请稍后重试')
          })
          return
        }
        
        // 从Vuex获取订阅模板ID
        const templates = this.subscribeTemplates
        // 提取模板ID数组
        const tmplIds = templates.slice(0, 3).map(item => item.template_id);
        
        // 检查是否有模板ID
        if (tmplIds.length === 0) {
          this.$func.showToast('暂无可用的订阅模板，请联系管理员配置')
          return
        }
        
        // 订阅前的引导说明
        uni.showModal({
          title: '📢 订阅消息提醒',
          content: '🎉 订阅后，我们将为您提供贴心的倒计时提醒服务：\n\n✅ 每日倒计时进展更新\n✅ 重要节点提前提醒\n✅ 倒计时结束实时通知\n\n📱 请在接下来的弹窗中点击"允许"，开启消息提醒功能。\n\n🙏 感谢您的支持！',
          confirmText: '继续订阅',
          cancelText: '暂不订阅',
          confirmColor: '#01BEFF',
          cancelColor: '#909399',
          success: (modalRes) => {
            if (modalRes.confirm) {
              // 用户确认订阅，请求订阅消息
              uni.requestSubscribeMessage({
                tmplIds: tmplIds,
                success: (res) => {
                  // 记录订阅结果
                  const subscribeResults = {};
                  let acceptedCount = 0;
                  let rejectedCount = 0;
                  let bannedCount = 0;
                  
                  // 处理每个模板的订阅结果
                  for (let tmplId in res) {
                    if (tmplId !== 'errMsg') {
                      const status = res[tmplId];
                      subscribeResults[tmplId] = status;
                      
                      // 统计不同状态的数量
                      if (status === 'accept' || status === 'acceptWithAudio') {
                        acceptedCount++;
                      } else if (status === 'reject') {
                        rejectedCount++;
                      } else if (status === 'ban') {
                        bannedCount++;
                      }
                    }
                  }
                  
                  // 根据订阅结果更新状态
                  if (acceptedCount > 0) {
                    // 更新本地数据
                    countdownItem.is_followed = 1;
                    
                    // 根据订阅结果提供不同的反馈
                    if (acceptedCount === tmplIds.length) {
                      this.$func.showToast('🎉 订阅成功！\n\n我们将每日为您推送倒计时提醒');
                    } else {
                      this.$func.showToast(`🎊 成功订阅${acceptedCount}个模板\n\n部分模板订阅失败，您仍将收到已订阅模板的提醒`);
                    }
                    
                    // 调用后端API保存订阅状态和订阅结果
                    this.$api.apiSubscribeCountdown({ 
                      id: id,
                      subscribe_status: 1,
                      subscribe_results: JSON.stringify(subscribeResults)
                    });
                  } else {
                    // 更新本地数据
                    countdownItem.is_followed = 0;
                    this.$func.showToast('💔 订阅失败\n\n请在微信通知权限中允许消息通知，\n然后重新尝试订阅');
                    // 调用后端API保存订阅状态
                    this.$api.apiSubscribeCountdown({ 
                      id: id,
                      subscribe_status: 0,
                      subscribe_results: JSON.stringify(subscribeResults)
                    });
                  }
                  
                  // 记录订阅结果到控制台，便于调试
                  console.log('订阅结果:', subscribeResults);
                },
                fail: (err) => {
                  console.error('请求订阅消息失败', err);
                  
                  // 根据错误码提供不同的提示
                  let errorMsg = '订阅失败，请稍后重试';
                  if (err.errCode === 20004) {
                    errorMsg = '订阅请求已发送，请在微信通知中处理';
                  } else if (err.errCode === 20005) {
                    errorMsg = '当前设备不支持订阅消息';
                  } else if (err.errCode === 20006) {
                    errorMsg = '订阅消息功能已被禁用';
                  }
                  
                  this.$func.showToast(errorMsg);
                }
              });
            } else {
              // 用户取消订阅
              this.$func.showToast('已取消订阅');
            }
          },
          fail: (err) => {
            console.error('显示订阅引导失败:', err);
          }
        });
      },
      
      // 打开编辑弹窗
      openEditPopup(item) {
        // 跳转到详情页并传递编辑参数，直接进入编辑状态
        uni.navigateTo({ url: `/subpages/countdown/detail?id=${item.id}&edit=true` })
      },
      
      // 查看倒计时详情
      viewCountdownDetail(id) {
        uni.navigateTo({ url: `/subpages/countdown/detail?id=${id}` })
      },
      
      // 打开新建倒计时弹窗
      createCountdown() {
        if (!isUserLoggedIn()) {
          // 未登录，显示登录提示模态框
          this.loginModalConfig.content = '请先登录后再进行新建操作'
          this.showLoginModal = true
          return
        }
        this.showCreatePopup = true
      },
      // 登录模态框点击事件处理
      handleLoginModalClick(e) {
        if (e.index === 1) {
          // 用户点击"去登录"，跳转到登录页面
          uni.navigateTo({ url: '/subpages/user/login' })
        }
        // 关闭模态框
        this.showLoginModal = false
      },
      // 保存新倒计时
      saveCountdown() {
        // 表单验证
        if (!this.newCountdown.title) {
          this.$func.showToast('请输入倒计时名称')
          return
        }
        
        if (!this.newCountdown.target_date) {
          this.$func.showToast('请选择目标日期')
          return
        }
        
        // 调用API保存新倒计时
        this.$api.apiCreateCountdown(this.newCountdown).then(res => {
          if (res.code === 1) {
            this.$func.showToast(res.msg || '保存成功')
            this.showCreatePopup = false
            // 重置表单
            this.newCountdown = {
              title: '',
              description: '',
              target_date: ''
            }
            // 刷新订阅的倒计时列表
            this.loadFollowedCountdowns()
            // 刷新热门列表
            this.loadCountdownList()
          } else {
            this.$func.showToast(res.msg || '保存失败')
          }
        }).catch(err => {
          console.error('保存倒计时失败', err)
          this.$func.showToast('保存失败')
        })
      },
    }
  }
</script>

<style lang="scss" scoped>
  @import "@/scss/custom_nav_bar.scss";
  /* 页面样式 */
  .follow-list {
    background-color: #f5f7fa;
    min-height: 100vh;
  }
  
  .current-date {
    padding: 20rpx 0;
    background-color: #fff;
  }
  
  /* 倒计时列表样式 */
  .countdown-list {
    margin: 10rpx 20rpx;
  }
  
  /* 列表项样式 */
  .list-item-content {
    display: flex;
    flex-direction: column;
    flex: 1;
    padding: 10rpx 0;
  }
  
  .list-item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  .list-item-title {
    font-size: 32rpx;
    font-weight: bold;
    color: #333;
  }
  
  .list-item-days {
    font-size: 32rpx;
    margin-top: 8rpx;
    margin-bottom: 12rpx;
    color: #1989fa;
  }
  
  .list-item-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 24rpx;
    color: #666;
  }
  
  .list-item-date {
    font-size: 24rpx;
    color: #666;
  }
  
  .follow-count {
    font-size: 24rpx;
    color: #999;
  }
  
  /* 列表项操作区 */
  .list-item-extra {
    display: flex;
    align-items: center;
  }
  
  .action-icon {
    font-size: 32rpx;
    margin-left: 20rpx;
  }
  
  /* 无更多数据样式 */
  .no-more {
    padding: 40rpx 0;
    text-align: center;
    color: #999;
    font-size: 24rpx;
  }

/* 底部悬浮按钮 start*/
  .tn-footerfixed {
    position: fixed;
    width: 100%;
    bottom: calc(30rpx + env(safe-area-inset-bottom));
    z-index: 1024;
    box-shadow: 0 1rpx 6rpx rgba(0, 0, 0, 0);
    
  }
  /* 底部悬浮按钮 end*/
</style>