<template>
  <view class="countdown-list-page tn-safe-area-inset-bottom">
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
          <text class="tn-text-bold tn-text-xl">倒计时列表</text>
        </view>
      </tn-nav-bar>
    </view>
    
    <!-- 页面内容 -->
    <view :style="{paddingTop: vuex_custom_bar_height + 'px'}"></view>
    
    <!-- 当前日期显示 -->
    <view class="current-date tn-margin-xs tn-text-center">
      <text class="tn-text-lg tn-color-gray--dark">{{ currentDate }}</text>
    </view>
    
    <!-- 搜索和筛选区域 -->
    <view class="search-filter-section">
      <!-- 搜索框 -->
      <view class="search-input-wrapper tn-flex tn-flex-row tn-align-center">
        <tn-input
          v-model="searchKeyword"
          placeholder="搜索倒计时标题"
          @input="onSearchChange"
          @confirm="onSearch"
          clearable
          @clear="onSearchClear"
          class="search-input"
          :border="true"
          :border-color="'#E4E7ED'"
          :background-color="'#F5F7FA'"
          :border-radius="'lg'"
          :show-right-icon="true"
          :right-icon="'search'"
          @right-click="onSearch"
        />
      </view>
      
      <!-- 筛选和排序容器 -->
      <view class="filter-sort-container">
        <!-- 时间筛选 -->
        <view class="filter-item tn-flex tn-flex-row tn-align-center">
          <tn-tag
            :type="statusFilter ? 'primary' : 'default'"
            @click="showStatusSelect = true"
            backgroundColor="#F5F7FA"
            class="filter-value-tag"
            fontSize="24"
            shape="round"
            size="sm"
          >
            {{ currentStatusLabel }}
          </tn-tag>
        </view>
        
        <!-- 排序方式 -->
        <view class="filter-item tn-flex tn-flex-row tn-align-center">
          <tn-tag
            :type="sortBy !== 'default' ? 'primary' : 'default'"
            @click="showSortSelect = true"
            backgroundColor="#F5F7FA"
            class="filter-value-tag"
            fontSize="24"
            shape="round"
            size="sm"
          >
            {{ currentSortLabel }}
          </tn-tag>
        </view>
      </view>
      
      <!-- 状态选择器 -->
      <tn-select
        v-model="showStatusSelect"
        :searchShow="false"
        :list="statusFilterOptions"
        @confirm="onStatusSelectConfirm"
        @cancel="showStatusSelect = false"
        title="选择状态"
      />
      
      <!-- 排序选择器 -->
      <tn-select
        v-model="showSortSelect"
        :searchShow="false"
        :list="sortOptions"
        @confirm="onSortSelectConfirm"
        @cancel="showSortSelect = false"
        title="选择排序方式"
      />
    </view>
    
    <!-- 倒计时列表 -->
    <view class="countdown-list tn-margin-xs">
      <tn-list-view title="倒计时列表">
        <tn-list-cell 
          v-for="(item, index) in countdownList" 
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
            <view class="list-item-info tn-margin-top-xs tn-flex tn-flex-row-between">
              <view class="list-item-date">目标日期：{{ item.target_date }}</view>
              <view class="list-item-follow tn-flex tn-flex-row tn-align-items-center">
                <tn-tag 
                  :type="(item.is_followed || 0) === 1 ? 'tn-color-green' : 'tn-color-primary'" 
                  size="small"
                  @click="toggleFollow(item.id, index)"
                  class="follow-tag"
                >
                  {{ (item.is_followed || 0) === 1 ? '已订阅' : '订阅' }}
                </tn-tag>
                <text class="follow-count tn-margin-left-xs"> {{ item.follow_count || 0 }} 人订阅</text>
              </view>
            </view>
          </view>
        </tn-list-cell>
        <tn-empty v-if="countdownList.length === 0 && !loading" mode="list" text="暂无倒计时数据" />
        <tn-loading v-if="loading" type="spinner" text="加载中..." class="tn-margin-xl" />
        <view v-if="!loading && countdownList.length > 0 && !hasMore" class="no-more tn-text-center tn-color-gray tn-padding-xl">
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
          @click="myCountdown"
        >
          <text class="tn-icon-my tn-padding-right-xs tn-color-white"></text>
          <text class="tn-color-white">我的倒计时</text>
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
    name: 'CountdownList',
    mixins: [template_page_mixin],
    data() {
      return {
        // 当前日期
        currentDate: '',
        // 倒计时列表
        countdownList: [],
        // 加载状态
        loading: false,
        // 是否还有更多数据
        hasMore: true,
        // 分页参数
        page: 1,
        pageSize: 10,
        // 搜索关键词
        searchKeyword: '',
        // 状态筛选
        statusFilter: '',
        // 排序方式
        sortBy: 'default',
        // 筛选选项
        statusFilterOptions: [
          { label: '全部状态', value: '' },
          { label: '进行中', value: '1' },
          { label: '已结束', value: '0' }
        ],
        // 排序选项
        sortOptions: [
          { label: '默认排序', value: 'default' },
          { label: '订阅人数', value: 'follow_count' },
          { label: '距离目标天数', value: 'target_date' },
          { label: '天数排序', value: 'days' },
          { label: '最新创建', value: 'create_time' }
        ],
        // 新建倒计时弹窗显示状态
        showCreatePopup: false,
        // 新倒计时信息
        newCountdown: {
          title: '',
          description: '',
          targetDate: ''
        },
        // 搜索定时器
        searchTimer: null,
        // 显示状态选择面板
        showStatusSelect: false,
        // 显示排序选择面板
        showSortSelect: false,
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
      }
    },
    computed: {
      // 当前选中的状态标签
      currentStatusLabel() {
        const option = this.statusFilterOptions.find(item => item.value === this.statusFilter);
        return option ? option.label : '时间';
      },
      // 当前选中的排序标签
      currentSortLabel() {
        const option = this.sortOptions.find(item => item.value === this.sortBy);
        return option ? option.label : '排序方式';
      },
      // 从Vuex获取订阅模板ID
      subscribeTemplates() {
        return this.$store.state.vuex_subscribe_templates || [];
      }
    },
    onLoad() {
      // 初始化页面数据
      this.initData()
    },
    onShow() {
      // 刷新当前日期
      this.updateCurrentDate()
      // 刷新倒计时列表
      this.refreshCountdownList()
    },
    // 下拉刷新
    onPullDownRefresh() {
      this.refreshCountdownList()
    },
    // 上拉加载更多
    onReachBottom() {
      if (this.loading || !this.hasMore) {
        return
      }
      this.loadMoreCountdowns()
    },
    beforeDestroy() {
      // 清除定时器
      if (this.searchTimer) {
        clearTimeout(this.searchTimer)
      }
    },
    methods: {
      // 初始化页面数据
      initData() {
        // 更新当前日期
        this.updateCurrentDate()
        // 加载倒计时列表
        this.refreshCountdownList()
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
      
      // 刷新倒计时列表
      refreshCountdownList() {
        this.page = 1
        this.hasMore = true
        this.loading = true
        this.countdownList = []
        
        this.loadCountdownList().finally(() => {
          uni.stopPullDownRefresh()
          this.loading = false
        })
      },
      
      // 加载更多倒计时
      loadMoreCountdowns() {
        if (this.loading || !this.hasMore) {
          return
        }
        
        this.loading = true
        this.page++
        
        this.loadCountdownList().finally(() => {
          this.loading = false
        })
      },
      
      // 加载倒计时列表
      loadCountdownList() {
        return new Promise((resolve, reject) => {
          // 调用API获取倒计时列表
          const params = {
            page_no: this.page,
            page_size: this.pageSize,
            keyword: this.searchKeyword,
            status: this.statusFilter,
            order_by: this.sortBy
          }
          
          this.$api.apiGetCountdownLists(params).then(res => {
            if (res.code === 1) {
              let list = res.data.list || []
              
              // 计算每个倒计时的精确状态
              list = list.map(item => {
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
                let daysText = `距离目标还有 ${item.days} 天`
                
                if (isExpired) {
                  statusType = 'expired'
                  statusText = '已过期'
                  const expiredDays = Math.abs(diffDays)
                  daysText = `已过期 ${expiredDays} 天`
                } else if (isUpcoming) {
                  statusType = 'upcoming'
                  statusText = '即将到期'
                  if (diffDays === 1) {
                    daysText = '今天到期（还有 1 天）'
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
              })
              
              if (this.page === 1) {
                this.countdownList = list
              } else {
                this.countdownList = [...this.countdownList, ...list]
              }
              
              // 判断是否还有更多数据
              this.hasMore = list.length >= this.pageSize
            }
            resolve()
          }).catch(err => {
            console.error('获取倒计时列表失败', err)
            resolve()
          })
        })
      },
      
      // 搜索相关方法
      onSearch() {
        this.refreshCountdownList()
      },
      
      onSearchChange(value) {
        // 防抖处理，300ms后执行搜索
        if (this.searchTimer) {
          clearTimeout(this.searchTimer)
        }
        
        this.searchTimer = setTimeout(() => {
          this.refreshCountdownList()
        }, 300)
      },
      
      onSearchClear() {
        this.searchKeyword = ''
        this.refreshCountdownList()
      },
      
      // 状态筛选变化
      onStatusFilterChange(status) {
        this.statusFilter = status
        this.refreshCountdownList()
      },
      
      // 排序变化
      onSortChange(sortBy) {
        this.sortBy = sortBy
        this.refreshCountdownList()
      },
      
      // 状态选择确认
      onStatusSelectConfirm(values) {
        if (values && values.length > 0) {
          this.statusFilter = values[0].value
          this.refreshCountdownList()
        }
      },
      
      // 排序选择确认
      onSortSelectConfirm(values) {
        if (values && values.length > 0) {
          this.sortBy = values[0].value
          this.refreshCountdownList()
        }
      },
      
      // 查看倒计时详情
      viewCountdownDetail(id) {
        uni.navigateTo({ url: `/subpages/countdown/detail?id=${id}` })
      },
      
      // 订阅/取消订阅倒计时
      toggleFollow(id, index) {
        if (!isUserLoggedIn()) {
          // 未登录，跳转到登录页面
          uni.navigateTo({ url: '/subpages/user/login' })
          return
        }
        
        const countdownItem = this.countdownList[index]
        if (!countdownItem) return
        
        // 如果已经订阅，直接取消订阅
        if ((countdownItem.is_followed || 0) === 1) {
          // 调用后端API保存订阅状态
          this.$api.apiSubscribeCountdown({
            id: id,
            subscribe_status: 0
          }).then(res => {
            if (res.code === 1) {
              // 更新本地数据
              countdownItem.is_followed = 0
              // 更新订阅人数
              countdownItem.follow_count = Math.max(0, (countdownItem.follow_count || 0) - 1)
              
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
                    // 更新订阅人数
                    countdownItem.follow_count = (countdownItem.follow_count || 0) + 1;
                    
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
      // 跳转我的倒计时
      myCountdown() {
        uni.navigateTo({ url: '/subpages/countdown/followList' })
      },
      // 打开新建倒计时弹窗
      createCountdown() {
        if (!isUserLoggedIn()) {
          // 未登录，跳转到登录页面
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
  .countdown-list-page {
    background-color: #f5f7fa;
    min-height: 100vh;
  }
  
  .current-date {
    padding: 20rpx 0;
    background-color: #fff;
  }
  
  /* 搜索筛选区域样式 */
  .search-filter-section {
    background-color: #fff;
    margin: 20rpx;
    padding: 24rpx;
    border-radius: 16rpx;
    box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.08);
  }
  
  /* 搜索框样式 */
  .search-input-wrapper {
    margin-bottom: 24rpx;
  }
  
  .search-input {
    width: 100%;
  }
  
  /* 筛选和排序容器 */
  .filter-sort-container {
    display: flex;
    flex-direction: row;
    gap: 40rpx;
    align-items: center;
    justify-content: flex-start;
  }
  
  /* 筛选项样式 */
  .filter-item {
    display: flex;
    flex-direction: row;
    align-items: center;
  }
  
  /* 筛选标签样式 */
  .filter-label {
    font-size: 28rpx;
    color: #606266;
    white-space: nowrap;
  }
  
  /* 筛选值标签样式 */
  .filter-value-tag {
    margin: 0;
    padding: 12rpx 24rpx;
    font-size: 28rpx;
    font-weight: 500;
    cursor: pointer;
  }
  
  .filter-value-tag.hover-class {
    opacity: 0.8;
  }
  
  /* 倒计时列表样式 */
  .countdown-list {
    margin: 20rpx;
  }
  
  /* 列表项样式 */
  .list-item-content {
    display: flex;
    flex-direction: column;
    flex: 1;
    padding: 20rpx 0;
  }
  
  .list-item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8rpx;
  }
  
  .list-item-title {
    font-size: 36rpx;
    font-weight: bold;
    color: #333;
    line-height: 48rpx;
  }
  
  .list-item-days {
    font-size: 32rpx;
    margin-top: 8rpx;
    margin-bottom: 12rpx;
  }
  
  .list-item-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 26rpx;
    color: #666;
    margin-top: 8rpx;
  }
  
  .list-item-date {
    font-size: 26rpx;
    color: #666;
  }
  
  .list-item-follow {
    display: flex;
    align-items: center;
  }
  
  /* 订阅标签样式 */
  .follow-tag {
    cursor: pointer;
    user-select: none;
  }
  
  .follow-tag.hover-class {
    opacity: 0.8;
  }
  
  .follow-count {
    font-size: 24rpx;
    color: #999;
    margin-left: 10rpx;
  }
  
  /* 弹窗样式 */
  .popup-content {
    padding: 40rpx;
  }
  
  .popup-title {
    font-weight: bold;
  }
  
  /* 无更多数据样式 */
  .no-more {
    padding: 40rpx 0;
    text-align: center;
    color: #999;
    font-size: 24rpx;
  }
</style>