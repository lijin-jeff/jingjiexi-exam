<template>
  <view class="countdown-detail tn-safe-area-inset-bottom">
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
        <text class="tn-text-lg tn-color-white">{{ countdownInfo.title || '' }}.倒计时</text>
      </view>
    </tn-nav-bar>
    </view>
    
    <!-- 登录提示模态框 -->
    <tn-modal
      v-model="showLoginModal"
      :title="loginModalConfig.title"
      :content="loginModalConfig.content"
      :button="loginModalConfig.button"
      @click="handleLoginModalClick"
    ></tn-modal>
    
    <!-- 页面内容 -->
    <view :style="{paddingTop: vuex_custom_bar_height + 'px'}"></view>
    <!-- 倒计时显示区域 -->
    <view class="countdown-section tn-bg-white" style="position: relative;">
      <!-- 我的订阅标签 -->
      <view v-if="isFollowed" class="followed-tag tn-padding-xs tn-bg-red tn-color-white tn-text-sm " style="position: absolute; top: 0; right: 0;">
        我的订阅
      </view>
      <!-- 编辑按钮 -->
      <view v-if="isOwner" name="edit" class="nav-icon-edit tn-padding-xs tn-bg-blue tn-color-white" @click="openEditPopup">
        <text class="tn-icon-edit tn-margin-right-xs"></text>编辑
      </view>
      <!-- 删除按钮 -->
      <view v-if="isOwner" name="delete" class="nav-icon-delete tn-padding-xs tn-bg-red tn-color-white" @click="openDeletePopup">
        <text class="tn-icon-delete tn-margin-right-xs"></text>删除
      </view>

      <!-- 我的创建标签 -->
       <view v-if="isOwner" class="followed-tag tn-padding-xs tn-bg-green tn-color-white tn-text-sm " style="position: absolute; top: 0; right: 0;">
        我的创建
      </view>
      <!-- 当前日期显示 -->     
      <view class="current-date-display tn-margin-bottom-md">
        <text class="tn-text-lg tn-color-gray--dark">今天是{{ currentDate }}</text>
      
      <!-- 实时倒计时显示 -->
      <view v-if="countdownInfo.is_end !== 1" class="countdown-time-container tn-margin">
      <!-- 目标日期 -->
      <view class="countdown-target tn-color-green--dark tn-margin-top-md tn-text-md">
        目标日期：{{ countdownInfo.target_date || '待定' }}
      </view>
         <view class="time-item horizontal tn-flex-row-center">
            <view class="time-number tn-font-bold tn-color-red tn-padding-sm tn-border-radius-lg">
              {{ countdownTime.days }}
            </view>
            <view class="time-label tn-color-red tn-text-xl-xxl">天</view>
          </view>
        <view class="countdown-detail-time tn-flex tn-flex-row-center tn-margin-top-md">
          <view class="time-item">
            <view class="time-number tn-font-bold tn-color-red tn-bg-red--light tn-padding-sm tn-border-radius-lg">{{ countdownTime.hours }}</view>
          </view>
          <view class="time-separator tn-color-red tn-margin-x-md">:</view>
          <view class="time-item">
            <view class="time-number tn-font-bold tn-color-red tn-bg-red--light tn-padding-sm tn-border-radius-lg">{{ countdownTime.minutes }}</view>
          </view>
          <view class="time-separator tn-color-red tn-margin-x-md">:</view>
          <view class="time-item">
            <view class="time-number tn-font-bold tn-color-red tn-bg-red--light tn-padding-sm tn-border-radius-lg">{{ countdownTime.seconds }}</view>
          </view>
        </view>
      </view>
      
      <!-- 已结束显示 -->
       <view v-else class="countdown-time-container tn-margin">
         <view class="time-item horizontal tn-flex-row-center">
            <view class="time-number tn-font-bold tn-color-red tn-padding-sm tn-border-radius-lg">
             0
            </view>
            <view class="time-label tn-color-red tn-text-xl-xxl">天</view>
          </view>
          <view class="countdown-ended tn-text-xxl tn-text-center tn-color-red tn-text-bold">
            倒计时已结束
          </view>
      </view>
      
      <!-- 对自己说信息 -->
      <view v-if="countdownInfo.description" class="countdown-description tn-text-center tn-color-gray--dark tn-text-md tn-line-height-xl">
        我对自己说：" {{ countdownInfo.description }} "
      </view>
    </view>
    </view>
    <!-- 名人名言展示区 -->
    <view class="quote-section tn-bg-white">
      <view class="quote-content tn-text-center tn-text-xxl tn-color-gray--darker tn-margin-bottom-md tn-line-height-xl">
        <text class="tn-font-medium">{{ randomQuote.content || '黑发不知勤学早，白首方悔读书迟。' }}</text>
      </view>
      <view class="quote-author tn-text-right tn-color-primary tn-text-md tn-font-bold">
        —— {{ randomQuote.author || '颜真卿' }}
      </view>
    </view>
    
    <!-- 所有倒计时列表 -->
    <view class="countdown-list tn-margin-xl">
      <tn-list-view customTitle="true">
        <view slot="title" class="custom-list-title tn-flex tn-flex-row-between tn-align-items-center">
          <text class="tn-text-bold tn-text-lg">热门倒计时</text>
          <text
            size="32"
            color="#01BEFF"
            @click="ToList"
            class="add-list-icon tn-icon-align-right"
          ></text>
        </view>
        <tn-list-cell 
          v-for="(item, index) in countdownList" 
          :key="item.id"
          arrow
          hover
          @click="viewCountdownDetail(item.id)"
        >
          <view slot="default" class="list-icon-text">
            <view class="list-item-title list__left">{{ item.title }}</view>
            <view class="list-item-days list__right tn-margin-right-lg">
              <text 
                :class="item.statusType === 'expired' ? 'tn-color-gray' : 
                item.statusType === 'upcoming' ? 'tn-color-red' : 'tn-color-blue'"
              >
                {{ item.daysText }}
              </text>
            </view>  
          </view>
        </tn-list-cell>
        <tn-empty v-if="countdownList.length === 0" mode="list" text="暂无倒计时数据" />
      </tn-list-view>
    </view>
    
    <!-- 我的订阅的倒计时列表 -->
    <view class="countdown-list tn-margin-xl">
      <tn-list-view customTitle="true">
        <view slot="title" class="custom-list-title tn-flex tn-flex-row-between tn-align-items-center">
          <text class="tn-text-bold tn-text-lg">订阅的倒计时</text>
          <text
            size="32"
            color="#01BEFF"
            @click="ToFollowList"
            class="add-list-icon tn-icon-align-right"
          ></text>
        </view>
        <tn-list-cell 
          v-for="(item, index) in followedCountdowns" 
          :key="item.id"
          arrow
          hover
          @click="viewCountdownDetail(item.id)"
        >
          <view slot="default" class="list-icon-text">
            <view class="list-item-title list__left">{{ item.title }}</view>
            <view class="list-item-days list__right tn-margin-right-lg">
              <text 
                :class="item.statusType === 'expired' ? 'tn-color-gray' : 
                item.statusType === 'upcoming' ? 'tn-color-red' : 'tn-color-blue'"
              >
                {{ item.daysText }}
              </text>
            </view>  
          </view>
        </tn-list-cell>
        <tn-empty v-if="followedCountdowns.length === 0" mode="list" text="暂无订阅的倒计时" />
      </tn-list-view>
      <view style="padding-bottom: 120rpx;"></view>
    </view>
    
    <!-- 底部按钮区域 -->
    <view class="tn-flex tn-flex-row-between tn-safe-area-inset-bottom" style="position: fixed; bottom: 20rpx; left: 0; right: 0; z-index: 999;">
      <view class="tn-flex-1 justify-content-item tn-margin-xs tn-text-center">
        <tn-button 
          class="bottom-button subscribe-button"
          :background-color="isFollowed ? 'tn-cool-bg-color-14' : 'tn-cool-bg-color-4'"
          padding="40rpx 0" 
          width="90%" 
          shadow 
          font-bold
          @click="toggleFollow"
        >
          <text class="tn-icon-like-fill tn-padding-right-xs tn-color-white"></text>
          <text class="tn-color-white">{{ isFollowed ? '已订阅' : '订阅' }}</text>
        </tn-button>
      </view>
      <view class="tn-flex-1 justify-content-item tn-margin-xs tn-text-center">
        <tn-button 
          class="bottom-button share-button"
          backgroundColor="tn-cool-bg-color-9" 
          padding="40rpx 0" 
          width="90%" 
          shadow 
          font-bold 
          open-type="share"
        >
          <text class="tn-icon-share tn-padding-right-xs tn-color-white"></text>
          <text class="tn-color-white">分享</text>
        </tn-button>
      </view>
      <view class="tn-flex-1 justify-content-item tn-margin-xs tn-text-center">
        <tn-button 
          class="bottom-button create-button"
          backgroundColor="tn-cool-bg-color-6" 
          padding="40rpx 0" 
          width="90%" 
          shadow 
          font-bold
          @click="createCountdown"
        >
          <text class="tn-icon-add tn-padding-right-xs tn-color-white"></text>
          <text class="tn-color-white">新建</text>
        </tn-button>
      </view>
    </view>
    
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
    
    <!-- 编辑倒计时弹窗 -->
    <tn-popup v-model="showEditPopup" mode="bottom" height="50%" :closeBtn="true">
      <view class="popup-content tn-padding-lg">
        <view class="popup-title tn-text-xxl tn-text-bold tn-text-center tn-margin-bottom-xl">
          编辑倒计时
        </view>
        <tn-form :label-width="150">
          <tn-form-item label="标题" required>
            <tn-input
              v-model="editCountdown.title"
              placeholder="请输入倒计时名称"
              :maxlength="50"
              clearable
              :border="true"
              border-radius="lg"
            />
          </tn-form-item>
          <tn-form-item label="对自己说">
            <tn-input
              v-model="editCountdown.description"
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
              @click="showEditCalendar = true"
              :border="true"
              border-radius="lg"
            >
              {{ editCountdown.target_date || '请选择目标日期' }}
            </tn-button>
          </tn-form-item>
        </tn-form>
           <view class="popup-buttons">
              <tn-button
                shape="round"
                @click="showEditPopup = false"
                size="lg"
                :shadow="true"
                background-color="#F5F7FA"
                font-color="#606266"
                border-radius="xl"
                margin="20rpx 0"
                width="50%"
              >
                取消
              </tn-button>
              <tn-button
                shape="round"
                @click="updateCountdown"
                size="lg"
                :shadow="true"
                background-color="#01BEFF"
                font-color="#FFFFFF"
                border-radius="xl"
                margin="20rpx 0"
                width="50%"
              >
                保存
              </tn-button>
            </view>
      </view>
    </tn-popup>
    
    <!-- 日历组件 - 新建 -->
    <tn-calendar
      v-if="showCalendar"
      v-model="showCalendar"
      :mode="calendarMode"
      :showLunar="showLunar"
      :activeBgColor="activeBgColor"
      :activeColor="activeColor"
      :btnColor="btnColor"
      :toolTips="toolTips"
      :changeYear="true"
      :changeMonth="true"
      :min-date="isoCurrentDate"
      :max-date="isoMaxDate"
      @change="onCalendarChange"
    ></tn-calendar>
    
    <!-- 日历组件 - 编辑 -->
    <tn-calendar
      v-if="showEditCalendar"
      v-model="showEditCalendar"
      :mode="calendarMode"
      :showLunar="showLunar"
      :activeBgColor="activeBgColor"
      :activeColor="activeColor"
      :btnColor="btnColor"
      :toolTips="toolTips"
      :changeYear="true"
      :changeMonth="true"
      :min-date="isoCurrentDate"
      :max-date="isoMaxDate"
      @change="onEditCalendarChange"
    ></tn-calendar>
    
    <!-- 删除确认模态框 -->
    <tn-modal
      v-model="showDeletePopup"
      :title="deleteModalConfig.title"
      :content="deleteModalConfig.content"
      :button="deleteModalConfig.button"
      @click="handleDeleteModalClick"
    ></tn-modal>
  </view>
</template>

<script lang="javascript">
  import template_page_mixin from '@/libs/mixin/template_page_mixin.js'
  import { getUserInfo, isUserLoggedIn } from '@/util/userStore.js'
  
  export default {
    name: 'CountdownDetail',
    mixins: [template_page_mixin],
    data() {
      return {
        // 当前日期
        currentDate: '',
        // 是否为编辑模式
        isEditMode: false,
        // 倒计时信息
        countdownInfo: {
          id: 0,
          title: '',
          target_date: '',
          description: '',
          is_end: 0,
          user_id: 0,
          days: 0,
          hours: 0,
          minutes: 0,
          seconds: 0
        },
        // 实时倒计时
        countdownTime: {
          days: 0,
          hours: 0,
          minutes: 0,
          seconds: 0
        },
        // 随机名人名言
        randomQuote: {
          content: '',
          author: ''
        },
        // 订阅状态
        isFollowed: false,
        // 是否为创建者
        isOwner: false,
        // 热门倒计时列表
        countdownList: [],
        // 订阅的倒计时列表
        followedCountdowns: [],
        // 新建倒计时弹窗显示状态
        showCreatePopup: false,
        // 编辑倒计时弹窗显示状态
        showEditPopup: false,
        // 新倒计时信息
        newCountdown: {
          title: '',
          description: '',
          target_date: ''
        },
        // 编辑倒计时信息
        editCountdown: {
          id: 0,
          title: '',
          description: '',
          target_date: ''
        },
        // 日历相关
        showCalendar: false,
        showEditCalendar: false,
        calendarMode: 'date',
        showLunar: true,
        activeBgColor: '#01BEFF',
        activeColor: '#FFFFFF',
        btnColor: '#01BEFF',
        toolTips: '请选择目标日期',
        // 倒计时天数
        countdownDays: 0,
        // 定时器
        countdownTimer: null,
        // 分享图片路径
        shareImageUrl: '',
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
        // 删除确认弹窗
        showDeletePopup: false,
        // 删除确认弹窗配置
        deleteModalConfig: {
          title: '确认删除',
          content: '确定要删除这个倒计时吗？',
          button: [
            { text: '取消', backgroundColor: '#F5F5F5', fontColor: '#666666' },
            { text: '删除', backgroundColor: '#FF4D4F', fontColor: '#FFFFFF' }
          ]
        }
      };
    },
    computed: {
      // 使用计算属性实时获取globalData中的主色调
      mainColor() {
        return getApp().globalData.mainColor || '#007AFF'
      },
      
      // 从Vuex获取订阅模板ID
      subscribeTemplates() {
        return this.$store.state.vuex_subscribe_templates || []
      },
      
      // 当前日期的ISO格式，用于日历组件的min-date属性
      isoCurrentDate() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
      },
      
      // 最大日期的ISO格式，用于日历组件的max-date属性，设置为当前日期10年后
      isoMaxDate() {
        const now = new Date();
        const year = now.getFullYear() + 10;
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
      }
    },
    onLoad(options) {
      // 初始化页面数据
      this.initData()
      // 保存编辑参数
      this.isEditMode = options.edit === 'true'
      // 如果有倒计时ID或题库分类ID，加载详情
      if (options.id || options.category_uid) {
        this.loadCountdownDetail(options.id, options.category_uid)
      }
    },
    onShow() {
      // 刷新当前日期
      this.updateCurrentDate()
      // 刷新订阅状态
      this.checkFollowStatus()
      // 刷新热门倒计时列表
      this.loadCountdownList()
      // 刷新订阅的倒计时列表
      this.loadFollowedCountdowns()
      // 重新检查是否为创建者，确保用户信息加载完成后编辑按钮能正确显示
      if (this.countdownInfo.id && this.countdownInfo.user_id) {
        this.checkIfOwner(this.countdownInfo.user_id)
      }
      // 启动实时倒计时
      this.startCountdown()
    },
    onUnload() {
      // 清除实时倒计时
      this.stopCountdown()
    },
    methods: {
      // 初始化页面数据
      initData() {
        // 更新当前日期
        this.updateCurrentDate()
        // 获取随机名人名言
        this.getRandomQuote()
        // 加载热门倒计时列表
        this.loadCountdownList()
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
      
      // 获取随机名人名言
      getRandomQuote() {
        // 调用API获取随机名人名言
        this.$api.apiGetRandomQuote().then(res => {
          if (res.code === 1) {
            let data = res.data || {}
            let content = data.content || ''
            let author = data.author || ''
            
            // 检查content中是否包含作者信息，如果包含则分离
            if (content.includes('——')) {
              const parts = content.split('——')
              content = parts[0].trim()
              author = parts[1] ? parts[1].trim() : author
            }
            
            // 移除可能存在的引号
            content = content.replace(/^["'“‘](.+)['"”’]$/, '$1')
            
            this.randomQuote = {
              content: content,
              author: author
            }
          }
        }).catch(err => {
          console.error('获取随机名人名言失败', err)
        })
      },
      
      // 加载倒计时详情
      loadCountdownDetail(id, categoryUid) {
        // 调用API获取倒计时详情
        this.$api.apiGetCountdownDetail({ id: id, category_uid: categoryUid }).then(res => {
          if (res.code === 1) {
            this.countdownInfo = res.data
            // 初始化编辑信息
            this.editCountdown = {
              id: res.data.id,
              title: res.data.title,
              description: res.data.description,
              target_date: res.data.target_date
            }
            // 直接从返回数据中设置订阅状态
            this.isFollowed = res.data.is_followed === 1
            // 检查是否为创建者
            this.checkIfOwner(res.data.user_id)
            // 启动实时倒计时
            this.startCountdown()
            
            // 如果是编辑模式，自动打开编辑弹窗
            if (this.isEditMode) {
              this.showEditPopup = true
              // 重置编辑模式，避免下次打开页面时自动进入编辑状态
              this.isEditMode = false
            }
          }
        }).catch(err => {
          console.error('获取倒计时详情失败', err)
        })
      },
      
      // 检查是否为创建者
      checkIfOwner(creatorId) {
        // 使用同步方式获取用户信息，避免异步问题
        try {
          const userInfo = uni.getStorageSync('userInfo') || {};
          // 检查globalData
          try {
            const app = getApp();
            if (app && app.globalData && app.globalData.userInfo && Object.keys(app.globalData.userInfo).length > 0) {
              userInfo.id = app.globalData.userInfo.id;
            }
          } catch (e) {
            console.warn('从globalData获取用户ID失败:', e);
          }
          
          // 转换为字符串进行比较，避免类型不一致导致的比较失败
          const userIdStr = String(userInfo.id);
          const creatorIdStr = String(creatorId);
          
          // 只有当userInfo.id和creatorId都存在且相等时才为创建者
          this.isOwner = !!userIdStr && userIdStr !== 'undefined' && userIdStr !== 'null' && userIdStr === creatorIdStr;
          
        } catch (e) {
          console.error('检查是否为创建者失败:', e);
          this.isOwner = false;
        }
      },
      
      // 启动实时倒计时
      startCountdown() {
        // 清除之前的定时器
        this.stopCountdown()
        
        // 如果倒计时已结束，不需要启动定时器
        if (this.countdownInfo.is_end === 1) {
          return
        }
        
        // 立即更新一次倒计时
        this.updateCountdownDisplay()
        
        // 每秒更新一次倒计时
        this.countdownTimer = setInterval(() => {
          this.updateCountdownDisplay()
        }, 1000)
      },
      
      // 更新实时倒计时
      updateCountdownDisplay() {
        const now = new Date()
        const target_date = new Date(this.countdownInfo.target_date)
        
        // 检查目标日期是否有效
        if (isNaN(target_date.getTime())) {
          // 目标日期无效时，保持当前倒计时状态或重置为0
          return
        }
        
        const diff = target_date - now
        
        if (diff <= 0) {
          // 倒计时结束
          this.countdownInfo.is_end = 1
          this.countdownTime = {
            days: 0,
            hours: 0,
            minutes: 0,
            seconds: 0
          }
          this.stopCountdown()
          return
        }
        
        // 计算天、时、分、秒
        const days = Math.floor(diff / (1000 * 60 * 60 * 24))+1
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))
        const seconds = Math.floor((diff % (1000 * 60)) / 1000)
        
        this.countdownTime = {
          days,
          hours,
          minutes,
          seconds
        }
      },
      
      // 停止实时倒计时
      stopCountdown() {
        if (this.countdownTimer) {
          clearInterval(this.countdownTimer)
          this.countdownTimer = null
        }
      },
      
      // 检查订阅状态
      checkFollowStatus() {
        // 使用countdownInfo.id或editCountdown.id来检查订阅状态
        const countdownId = this.countdownInfo.id || this.editCountdown.id
        // 检查当前是否有倒计时ID
        if (!countdownId) {
          this.isFollowed = false
          return
        }
        
        // 如果未登录，默认未订阅
        if (!isUserLoggedIn()) {
          this.isFollowed = false
          return
        }
        
        // 调用API检查订阅状态
        this.$api.apiGetCountdownDetail({ id: countdownId }).then(res => {
          if (res.code === 1) { 
            this.isFollowed = res.data.is_followed === 1
          }
        }).catch(err => {
          console.error('检查订阅状态失败', err)
        })
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
      
      // 订阅/取消订阅微信小程序消息
      toggleFollow() {
        if (!isUserLoggedIn()) {
          // 未登录，显示登录提示模态框
          this.loginModalConfig.content = '请先登录后再进行订阅操作'
          this.showLoginModal = true
          return
        }
        
        // 检查倒计时信息是否已加载完成
        if (!this.countdownInfo || !this.countdownInfo.id) {
          this.$func.showToast('倒计时信息加载中，请稍后再试')
          return
        }
        
        // 如果已经订阅，直接取消订阅
        if (this.isFollowed) {
          // 调用后端统一订阅API保存订阅状态
          this.$api.apiSubscribe({ 
            type: 'countdown',
            related_id: this.countdownInfo.id,
            subscribe_status: 0,
          }).then(res => {
            if (res.code === 1) {
              this.isFollowed = false
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
        
        if (tmplIds.length === 0) {
          this.$func.showToast('暂无可用的订阅模板，请联系管理员')
          return
        }
        
        // 构建模板数据
        const templateData = {
          // 考试名称 (这里应该是倒计时名称)
          thing1: {
            value: this.countdownInfo.title || '倒计时提醒'
          },
          // 备注
          thing2: {
            value: this.countdownInfo.description || '倒计时提醒'
          },
          // 开始时间 (这里使用目标日期作为开始时间)
          time3: {
            value: this.countdownInfo.target_date || ''
          }
        };


        // 使用统一的订阅消息处理函数
        this.$func.handleSubscribeMessage({
          tmplIds: tmplIds,
          apiCall: this.$api.apiSubscribe,
          apiParams: {
            type: 'countdown',
            related_id: this.countdownInfo.id,
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
                type: 'countdown',
                related_id: this.countdownInfo.id,
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
            this.checkFollowStatus();
          }
        });
      },
      
      // 打开编辑弹窗
      openEditPopup() {
        this.editCountdown = {
          id: this.countdownInfo.id,
          title: this.countdownInfo.title,
          description: this.countdownInfo.description,
          target_date: this.countdownInfo.target_date
        }
        this.showEditPopup = true
      },
      
      // 打开删除确认弹窗
      openDeletePopup() {
        this.showDeletePopup = true
      },
      
      // 处理删除确认弹窗点击事件
      handleDeleteModalClick(e) {
        // 关闭弹窗
        this.showDeletePopup = false
        
        // 如果点击的是删除按钮
        if (e.index === 1) {
          // 执行删除操作
          this.deleteCountdown()
        }
      },
      
      // 删除倒计时
      deleteCountdown() {
        // 检查是否为创建者，只有创建者才能删除
        if (!this.isOwner) {
          this.$func.showToast('只有创建者才能删除此倒计时')
          return
        }
        
        // 调用API删除倒计时
        this.$api.apiDeleteCountdown({ id: this.countdownInfo.id }).then(res => {
          if (res.code === 1) {
            this.$func.showToast(res.msg || '删除成功')
            // 返回上一页
            uni.navigateBack()
          } else {
            this.$func.showToast(res.msg || '删除失败')
          }
        }).catch(err => {
          console.error('删除倒计时失败', err)
          this.$func.showToast('删除失败')
        })
      },
      // 更新倒计时
      updateCountdown() {
        // 检查是否为创建者，只有创建者才能更新
        if (!this.isOwner) {
          this.$func.showToast('只有创建者才能编辑此倒计时')
          this.showEditPopup = false
          return
        }
        
        // 表单验证
        if (!this.editCountdown.title) {
          this.$func.showToast('请输入倒计时名称')
          return
        }
        
        if (!this.editCountdown.target_date) {
          this.$func.showToast('请选择目标日期')
          return
        }
        
        // 调用API更新倒计时
        this.$api.apiUpdateCountdown(this.editCountdown).then(res => {
          if (res.code === 1) {
            this.$func.showToast(res.msg || '更新成功')
            this.showEditPopup = false
            // 重新加载详情
            this.loadCountdownDetail(this.countdownInfo.id, this.countdownInfo.category_uid)
            // 刷新列表
            this.loadCountdownList()
            this.loadFollowedCountdowns()
          } else {
            this.$func.showToast(res.msg || '更新失败')
          }
        }).catch(err => {
          console.error('更新倒计时失败', err)
          this.$func.showToast('更新失败')
        })
      },
      
      // 分享倒计时
      shareCountdown() {
        // 获取当前平台
        const platform = uni.getSystemInfoSync().platform
        
        // 微信小程序端分享处理
        if (platform === 'mp-weixin') {
          // 显示当前页面的转发按钮
          wx.showShareMenu({
            withShareTicket: true,
            menus: ['shareAppMessage', 'shareTimeline']
          })
          
          // 创建canvas用于截图
          this.createShareCanvas()
          
          // 提示用户点击右上角分享
          this.$func.showToast('请点击右上角分享按钮进行分享')
        } else if (platform === 'app-plus') {
          // App平台使用uni.share
          uni.share({
            provider: 'weixin',
            scene: 'WXSceneSession',
            type: 0,
            title: `${this.countdownInfo.title || '倒计时分享'} - 距离目标还有`,
            summary: this.countdownInfo.is_end === 1 ? 
              `${this.countdownInfo.title || '目标'}已结束` : 
              `距离${this.countdownInfo.title || '目标'}还有${this.countdownTime.days}天${this.countdownTime.hours}小时`,
            path: `/subpages/countdown/detail?id=${this.countdownInfo.id}`,
            imageUrl: this.shareImageUrl || '',
            success: function(res) {
              console.log('分享成功', res)
            },
            fail: function(err) {
              console.log('分享失败', err)
            }
          })
        } else if (platform === 'h5') {
          // H5平台处理
          if (window.wx && window.wx.ready) {
            // 微信H5环境，使用微信JS-SDK分享
            this.$func.showToast('请点击右上角分享按钮进行分享')
          } else {
            // 其他H5环境，提示用户使用浏览器分享
            this.$func.showToast('请使用浏览器自带的分享功能进行分享')
          }
        } else {
          // 其他平台提示
          this.$func.showToast('请使用平台自带的分享功能进行分享')
        }
      },
      
      // 创建分享截图canvas
      createShareCanvas() {
        // 获取系统信息
        const sysInfo = uni.getSystemInfoSync()
        const windowWidth = sysInfo.windowWidth
        const windowHeight = sysInfo.windowHeight
        
        // 创建canvas上下文
        const canvas = wx.createOffscreenCanvas({
          type: '2d',
          width: windowWidth,
          height: windowHeight
        })
        const ctx = canvas.getContext('2d')
        
        // 获取页面节点信息，用于截图
        const query = wx.createSelectorQuery()
        query.select('.countdown-detail').boundingClientRect()
        query.selectViewport().scrollOffset()
        query.exec((res) => {
          if (!res || !res[0]) return
          
          const rect = res[0]
          const scrollOffset = res[1]
          
          // 使用wx.canvasToTempFilePath API进行截图
          // 这里我们将使用页面的完整高度进行截图
          wx.canvasToTempFilePath({
            x: 0,
            y: 0,
            width: windowWidth,
            height: rect.height,
            destWidth: windowWidth * 2, // 提高截图质量
            destHeight: rect.height * 2,
            success: (res) => {
              // 保存截图路径，用于分享
              this.shareImageUrl = res.tempFilePath
              
              // 更新分享到朋友的数据，使用自定义截图
              wx.updateAppMessageShareData({
                title: `${this.countdownInfo.title || '倒计时分享'} - 距离目标还有`,
                path: `/subpages/countdown/detail?id=${this.countdownInfo.id}`,
                imageUrl: this.shareImageUrl,
                success: function() {
                  console.log('分享到朋友设置成功')
                }
              })
              
              // 更新分享到朋友圈的数据，使用自定义截图
              wx.updateTimelineShareData({
                title: `${this.countdownInfo.title || '倒计时分享'} - 距离目标还有${this.countdownTime.days}天`,
                query: `id=${this.countdownInfo.id}`,
                imageUrl: this.shareImageUrl,
                success: function() {
                  console.log('分享到朋友圈设置成功')
                }
              })
            },
            fail: (err) => {
              console.error('截图失败', err)
              // 截图失败时使用默认分享
              this.updateShareDataWithoutImage()
            }
          })
        })
      },
      
      // 更新分享数据（无图片时）
      updateShareDataWithoutImage() {
        // 更新分享到朋友的数据
        wx.updateAppMessageShareData({
          title: `${this.countdownInfo.title || '倒计时分享'} - 距离目标还有${this.countdownTime.days}天`,
          path: `/subpages/countdown/detail?id=${this.countdownInfo.id}`,
          imageUrl: '', // 可根据实际情况添加默认分享图片
          success: function() {
            console.log('分享到朋友设置成功')
          }
        })
        
        // 更新分享到朋友圈的数据
        wx.updateTimelineShareData({
          title: `${this.countdownInfo.title || '倒计时分享'} - 距离目标还有${this.countdownTime.days}天`,
          query: `id=${this.countdownInfo.id}`,
          imageUrl: '', // 可根据实际情况添加默认分享图片
          success: function() {
            console.log('分享到朋友圈设置成功')
          }
        })
      },
      
      // 监听用户点击右上角分享给朋友
      onShareAppMessage() {
        return {
          title: `${this.countdownInfo.title || '倒计时分享'} - 距离目标还有`,
          path: `/subpages/countdown/detail?id=${this.countdownInfo.id}`,
          imageUrl: this.shareImageUrl || '' // 使用自定义截图或默认图片
        }
      },
      
      // 监听用户点击右上角分享到朋友圈
      onShareTimeline() {
        return {
          title: `${this.countdownInfo.title || '倒计时分享'} - 距离目标还有${this.countdownTime.days}天`,
          query: `id=${this.countdownInfo.id}`,
          imageUrl: this.shareImageUrl || '' // 使用自定义截图或默认图片
        }
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
      
      // 加载订阅的倒计时列表
      loadFollowedCountdowns() {
        if (!isUserLoggedIn()) {
          return
        }
        
        // 调用API获取订阅的倒计时列表
        this.$api.apiGetFollowedCountdowns().then(res => {
          if (res.code === 1) {
            let list = res.data.list || []
            
            // 计算每个倒计时的精确状态
            list = list.map(item => this.calculateCountdownStatus(item))
            
            this.followedCountdowns = list
          }
        }).catch(err => {
          console.error('获取订阅的倒计时列表失败', err)
        })
      },
      
      // 加载热门倒计时列表
      loadCountdownList() {
        // 调用API获取热门倒计时列表
        this.$api.apiGetCountdownLists().then(res => {
          if (res.code === 1) {
            let list = res.data.list || []
            
            // 计算每个倒计时的精确状态
            list = list.map(item => this.calculateCountdownStatus(item))
            
            this.countdownList = list
          }
        }).catch(err => {
          console.error('获取热门倒计时列表失败', err)
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
      
      // 查看倒计时详情
      viewCountdownDetail(id) {
        uni.navigateTo({ url: `/subpages/countdown/detail?id=${id}` })
      },
      
      // 加入列表
      ToList() {
        // 实现跳转列表的逻辑
        uni.navigateTo({ url: '/subpages/countdown/list' })
      },
      ToFollowList() {
        // 实现跳转订阅列表的逻辑
        uni.navigateTo({ url: '/subpages/countdown/followList' })
      },
      
      // 日历日期有改变
      onCalendarChange(event) {
        if (event.date) {
          this.newCountdown.target_date = event.date
        }
      },
      
      // 编辑日历日期有改变
      onEditCalendarChange(event) {
        if (event.date) {
          this.editCountdown.target_date = event.date
        }
      }
    }
  }
</script>

<style lang="scss" scoped>
@import "@/scss/custom_nav_bar.scss";
  /* 页面样式 */
  .countdown-detail {
    background-color: #f5f7fa;
    min-height: 100vh;
  }
  
  .current-date {
    padding: 20rpx 0;
    background-color: #fff;
  }
  
  .nav-icon-edit {
    position: absolute;
    top: 66rpx;
    right: 0;
    font-size: 24rpx;
  }
  
  .nav-icon-delete {
    position: absolute;
    top: 130rpx;
    right: 0;
    font-size: 24rpx;
  }

  /* 倒计时区域样式 */
  .countdown-section {
    margin-top:20rpx;
    background-color: #fff;
    padding: 20rpx;
  }
  
  /* 实时倒计时样式 */
  .countdown-detail-time {
    display: flex;
    justify-content: center;
    align-items: center;
  }
  
  .time-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin: 0 10rpx;
  }
  
  /* 天数字段特殊样式 - 水平布局 */
  .time-item.horizontal {
    font-size: 180rpx;
    flex-direction: row;
    align-items: center;
  }
  
  .time-item.horizontal .time-label {
    margin-top: 0;
    margin-left: 10rpx;
  }
  
  .time-number {
    padding: 10rpx 20rpx;
    border-radius: 12rpx;
    min-width: 88rpx;
    text-align: center;
  }
  
  .time-label {
    margin-top: 8rpx;
    font-weight: 500;
  }
  
  .time-separator {
    margin: 0 10rpx;
  }
  
  /* 名人名言样式 */
  .quote-section {
    padding:0 30rpx 30rpx 30rpx;
  }
  
  .quote-content {
    text-align: center;
    font-size: 28rpx;
    color: #333;
    line-height: 1.8;
  }
  
  /* 按钮区域样式 */
  .button-section {
    margin:20rpx 0;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #fff;
    padding: 32rpx;
    border-radius: 12rpx;
    box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.1);
  }
  
  .button-container {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  /* 列表样式 */
  .countdown-list {
    margin:20rpx 0;
  }
  .custom-title {
    display: flex;
    align-items: flex-end;
    justify-content: flex-end;
    padding: 10rpx 20rpx;
  }
  /* 弹窗样式 */
  .popup-content {
    padding: 40rpx;
  }
  
  .popup-title {
    font-weight: bold;
    text-align: center;
    margin-bottom: 30rpx;
  }
  
  .popup-buttons {
    justify-content: space-between;
    margin-top: 40rpx;
  }
  
  /* 列表项样式 */
  .list {
    &__left {
      display: flex;
      align-items: center;
      justify-content: flex-start;
    }
    
    &__right {
      display: flex;
      align-items: center;
      justify-content: flex-end;
    }
  }
  
  .list-icon-text {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .list-item-title {
    font-size: 32rpx;
    color: #333;
    margin-bottom: 8rpx;
  }
  
  .list-item-days {
    font-size: 28rpx;
    color: #666;
  }
  
  .list-item-extra {
    display: flex;
    align-items: center;
  }
  
  .days-count {
    font-size: 28rpx;
    color: #1989fa;
    font-weight: bold;
  }
  
  /* 自定义列表标题样式 */
  .custom-list-title {
    padding: 30rpx;
    box-sizing: border-box;
    width: 100%;
  }
  
  /* 加入列表图标样式 */
  .add-list-icon {
    cursor: pointer;
  }
</style>