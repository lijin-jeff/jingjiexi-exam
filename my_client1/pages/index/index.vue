<template>

  <view class="template-screen tn-safe-area-inset-bottom">
    <!-- 骨架屏：使用图鸟UI官方组件 -->
    <view v-if="pageLoading" class="skeleton-container">
      <!-- 顶部导航占位 -->
      <view class="skeleton-nav-placeholder" :style="{ height: (vuex_custom_bar_height || 0) + 'px' }"></view>
      
      <!-- 顶部导航骨架 -->
      <view class="skeleton-nav">
        <view class="skeleton-nav-content">
          <tn-skeleton 
            shape="circle" 
            :width="80" 
            :height="80" 
            :animation="true"
            backgroundColor="#E8E8E8"
          />
          <tn-skeleton 
            shape="round" 
            width="100%"
            :height="70" 
            :animation="true"
            backgroundColor="#E8E8E8"
            style="flex: 1; margin-left: 20rpx;"
          />
        </view>
      </view>
      
      <!-- 轮播图骨架 -->
      <view class="skeleton-swiper">
        <tn-skeleton 
          shape="round" 
          width="100%"
          :height="350" 
          :animation="true"
          backgroundColor="#E8E8E8"
        />
      </view>
      
      <!-- 考试信息骨架 -->
      <view class="skeleton-exam-section">
        <view class="skeleton-exam-container">
          <view style="flex: 1;">
            <tn-skeleton 
              :rows="2" 
              :animation="true"
              :rowsHeight="[40, 30]"
              :rowsWidth="['200rpx', '300rpx']"
            />
          </view>
          <tn-skeleton 
            shape="round" 
            :width="120" 
            :height="80" 
            :animation="true"
            style="margin-left: 20rpx;"
          />
        </view>
      </view>
      
      <!-- 统计数据骨架 -->
      <view class="skeleton-stats">
        <view class="skeleton-stat-item">
          <tn-skeleton 
            shape="round" 
            width="100%"
            :height="180" 
            :animation="true"
          />
        </view>
        <view class="skeleton-stat-item">
          <tn-skeleton 
            shape="round" 
            width="100%"
            :height="180" 
            :animation="true"
          />
        </view>
        <view class="skeleton-stat-item">
          <tn-skeleton 
            shape="round" 
            width="100%"
            :height="180" 
            :animation="true"
          />
        </view>
      </view>
      
      <!-- 题库列表骨架 -->
      <view class="skeleton-menu-list">
        <view class="skeleton-menu-item">
          <tn-skeleton 
            shape="round" 
            width="100%"
            :height="180" 
            :animation="true"
          />
        </view>
        <view class="skeleton-menu-item">
          <tn-skeleton 
            shape="round" 
            width="100%"
            :height="180" 
            :animation="true"
          />
        </view>
        <view class="skeleton-menu-item">
          <tn-skeleton 
            shape="round" 
            width="100%"
            :height="180" 
            :animation="true"
          />
        </view>
        <view class="skeleton-menu-item">
          <tn-skeleton 
            shape="round" 
            width="100%"
            :height="180" 
            :animation="true"
          />
        </view>
        <view class="skeleton-menu-item">
          <tn-skeleton 
            shape="round" 
            width="100%"
            :height="180" 
            :animation="true"
          />
        </view>
        <view class="skeleton-menu-item">
          <tn-skeleton 
            shape="round" 
            width="100%"
            :height="180" 
            :animation="true"
          />
        </view>
      </view>
    </view>
    
    <!-- 实际内容：加载完成后显示 -->
    <view v-else>
      <page-b v-if="currentIndex === 1"  ref="pageB" :selectedCategoryUid="selectedCategoryUid"/>
      <page-e v-if="currentIndex === 4"  ref="pageE" :user="userInfoCache"/>
      <view class="bg-contaniner tn-bg-blue"></view>
      <view v-if="currentIndex === 0">
    <!-- 顶部自定义导航 -->
    <!-- <tn-nav-bar fixed alpha customBack>
      <view slot="back" class='tn-custom-nav-bar__back'
        @click="goBack">
        <text class='icon tn-icon-left'></text>
        <text class='icon tn-icon-home-capsule-fill'></text>
      </view>
    </tn-nav-bar> -->
 
    <!-- 顶部自定义导航 -->
    <tn-nav-bar
      fixed
      :is-back="false"
      :bottom-shadow="false"
      :alpha="true"
    >
      <view class="custom-nav tn-flex tn-flex-col-center tn-flex-row-left">
        <!-- 返回按钮 -->
        <view class="custom-nav__back">
          <view
            class="logo-pic tn-shadow-blur"
            :style="'background-image:url('+ (userInfo.avatar || 'https://datiqiniu.allpp.cn/static/userinfo_avatar.png') +')'"
            @click="tl('/subpages/user/set')"
          >
            <view class="logo-image" />
          </view> 
          <!-- <view class="tn-icon-left"></view> -->
        </view>
        <!-- 搜索框 -->
        <view class="custom-nav__search tn-flex tn-flex-col-center tn-flex-row-center"  @click="tl('/pages/index/search')">
          <view class="custom-nav__search__box tn-flex tn-flex-col-center tn-flex-row-left tn-color-gray--dark tn-bg-gray--light">
            <view class="custom-nav__search__icon tn-icon-search" />
            <view class="tn-padding-left-xs">
              好想搜点什么
            </view>
          </view>
        </view>
      </view>
    </tn-nav-bar>
    
   
    <view :style="{paddingTop: vuex_custom_bar_height + 'px'}"></view>

    <view v-if="swiperList.length > 0">
      <tn-swiper
        :list="swiperList"
        :height="350"
        :effect3d="true"
        :title="true"
        mode="round"
      />
    </view> 
    <!-- 考试选择和考试倒计时 -->
    <view class="exam-section tn-margin-top">
      <view class="exam-container tn-flex tn-flex-row-between tn-flex-col-center">
        <!-- 考试标题和选择 -->
        <view class="exam-title-section tn-flex tn-flex-row-center tn-flex-col-bottom">
          <view class="exam-title">
            {{ websiteShop_name }}
          </view>
          <view class="tn-flex tn-flex-row-center">
            <view 
              class="exam-select-text tn-flex tn-flex-row-center tn-flex-col-center" 
              @click="openBusinessPage"
            >
              <text>{{ (selectedCategory && selectedCategoryUid) ? selectedCategory : (selectedExam ? selectedExam.text : '请选择考试类型') }}</text>
              <text class="tn-icon-sequence tn-margin-left-xs" style="display: flex; align-items: center;" />
            </view>
          </view>
        </view>
        <!-- 考试倒计时 -->
        <view class="exam-countdown tn-flex tn-flex-row-center tn-flex-col-center" @click="navigateToCountdownDetail">
          <view class="countdown-label tn-bg-blue tn-color-white tn-text-xs tn-border-radius-sm">
            距离考试
          </view>
          <view class="countdown-days tn-text-xl tn-font-bold">
            {{ countdownDays }}天
          </view>
        </view>
      </view>
    </view>



    <!-- 数据信息 -->
    <view class="tn-margin-xs">
    <!-- 切换题库 -->
    <!-- 水平排列的tab和图标 -->
    <subject-tabs-manager
      :scroll-list="scrollList"
      :current="current"
      :visible="showSubjectPopup"
      :my-subjects="myQuestionLibList"
      :all-subjects="questionLibList"
      :loading="loadingQuestionLib"
      :title="'编辑科目'"
      :main-color="mainColor"
      :storage-key="'myQuestionLibList'+selectedCategoryUid"
      :auto-save="true"
      :show-toast="true"
      @tab-change="tabChange"
      @manager-click="showSubjectPopup = true"
      @close="showSubjectPopup = false"
      @save="handleSubjectSave"
      @add="handleSubjectAdd"
      @remove="handleSubjectRemove"
    />

    	<!-- 数据统计区域 -->
    <view class="stats-section">
      <!-- 奖杯图标：点击弹出压屏窗 -->
      <view 
        class="stats-prize-icon" 
        @click="showLandscape = true"
      >
        <text class="tn-icon-gift tn-margin-left-xs tn-cool-bg-color-10" style="font-size: 36rpx; display: flex; align-items: center; border-radius: 25%;"></text>
      </view>
      
      <view class="stats-grid">
        <!-- 我的作答题目 -->
        <view class="stat-item" hover-class="stat-item-hover">
          <view class="stat-value primary">
            {{ overallStats.total_answers }}/{{ overallStats.total_questions }}
          </view>
          <view class="stat-label">
            作答/总题
          </view>
        </view>

        <!-- 我的错题 -->
        <view class="stat-item" hover-class="stat-item-hover">
          <view class="stat-value error">
           {{ overallStats.eliminated_errors }}/{{ overallStats.total_errors }}
          </view>
          <view class="stat-label">
            消灭/错题
          </view>
        </view>

        <!-- 我的正确率 -->
        <view class="stat-item" hover-class="stat-item-hover">
          <view class="stat-value success">
           {{ overallStats.overall_accuracy }}%
          </view>
          <view class="stat-label">
            我的正确率
          </view>
        </view>
      </view>
    </view>

      <!-- 功能菜单列表 -->
      <view class="tn-flex tn-flex-wrap tn-flex-col-center tn-flex-row-between">
      <block
        v-for="(item, index) in menuTopList"
        :key="index"
      >
        <view class="tn-info__item tn-flex tn-flex-direction-row tn-flex-col-center tn-flex-row-between job-shadow"  style="background-color: #ffffff;"  
        @click.stop="tl(item.url + (item.url.includes('?') ? '&' : '?') + 'exam_category_uid=' + selectedCategoryUid)">
          <view class="tn-info__item__left tn-flex tn-flex-direction-row tn-flex-col-center tn-flex-row-left">
            <view
              class="tn-info__item__left--icon tn-flex tn-flex-col-center tn-flex-row-center tn-color-white"
              :style="{ backgroundColor: item.iconColor }"
            >
              <text :class="[`tn-icon-${item.icon}`]" />
            </view>
            <view class="tn-info__item__left__content">
              <view class="tn-info__item__left__content--title">
                {{ item.title }}
              </view>
              <view class="tn-info__item__left__content--data tn-padding-top-xs">
                {{ item.desc }}
              </view>
            </view>
          </view>
          <view class="tn-info__item__right">
            <view class="tn-info__item__right--icon">
              <view class="tn-icon-right" />
            </view>
          </view>
        </view>
      </block>
    </view>
    
    <!-- 方式16 start-->
    <view class="tn-flex tn-flex-wrap tn-margin-top-xs job-shadow"  style="background-color: #ffffff;">
      <block
        v-for="(item, index) in menuList"
        :key="index"
      >
        <view
          class=" "
          style="width: 25%;"  @click.stop="tl(item.url + (item.url.includes('?') ? '&' : '?') + 'exam_category_uid=' + selectedCategoryUid)"
        >
          <view class="tn-flex tn-flex-direction-column tn-flex-row-center tn-flex-col-center  tn-padding-xl">
            <view class="icon16__item--icon tn-flex tn-flex-row-center tn-flex-col-center">
              <view
                class="tn-cool-color-icon16"
                :class="[$tn.color.getRandomCoolBgClass(index) + ' tn-icon-' + item.icon]"
              />
            </view>  
            <view class="tn-color-black tn-text-md tn-text-center">
              <text class="tn-text-ellipsis">
                {{ item.title }}
              </text>
            </view>
          </view>
        </view>
      </block>
    </view>
    <!-- 方式16 end-->
  
  <!-- 🎨 分类弹窗开始 -->
  <tn-popup v-model="showCategoryList" mode="bottom" height="85%" :closeBtn="true" closeIconColor="#ffffff" :closeOnClickOverlay="hasSelectedExamType" :closeOnPressEscape="hasSelectedExamType">
      <!-- � 蓝色导航栏 -->
      <view class="custom-navbar" :style="{ backgroundColor: '#0E7DFF', height: '88rpx', display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '0 30rpx' }">
        <!-- ⬅️ 返回按钮 -->
        <view style="display: flex; align-items: center; width: 120rpx;" @click="closeCategoryList">
          <text class="tn-icon-left tn-color-white" style="font-size: 36rpx; margin-right: 10rpx;"></text>
          <text class="tn-color-white" style="font-size: 28rpx;">返回</text>
        </view>
        <!-- � 标题 -->
        <view style="flex: 1; text-align: center;">
          <text class="tn-text-bold tn-text-xl tn-color-white" style="font-size: 36rpx;">选择考试类型</text>
        </view>
        <!-- ❌ 关闭按钮 -->
        <view style="width: 120rpx; display: flex; align-items: center; justify-content: flex-end;">
          <text class="tn-icon-close tn-color-white" style="font-size: 32rpx;"></text>
        </view>
      </view>
      <!-- <view style="height: 120rpx;width: 100%;background-color: #FFFFFF;border-bottom: 1rpx solid #f5f5f5;"></view> -->
      <!-- 📱 内容区域 -->
      <view class="business-content">
      <!-- 📋 左侧分类列表 -->
      <view class="left-panel">
        <scroll-view class="category-scroll" scroll-y>
          <!-- ⏳ 加载中状态 -->
          <view v-if="loading" class="loading-container">
            <tn-loading type="cycle" color="#0E7DFF" />
          </view>
          
          <!-- 🏷️ 一级分类列表 -->
          <!-- 🎨 选中状态: 背景白色，文字蓝色 #0E7DFF，左侧蓝色边框 -->
          <!-- 🎨 未选中状态: 文字深灰色 #333333 -->
          <view
            v-for="(item, index) in categoryList"
            :key="index"
            :class="['category-item', activeCategory === index ? 'active' : ''] "
            hover-class="category-item-hover"
            @click="selectCategory(index)"
            :style="{
              backgroundColor: activeCategory === index ? '#ffffff' : '',
              color: activeCategory === index ? '#0E7DFF' : '#333333',
              borderLeft: activeCategory === index ? '4rpx solid #0E7DFF' : '4rpx solid transparent',
              fontWeight: activeCategory === index ? '1000' : 'normal'
            }"
          >
            {{ item.label }}
          </view>
          
          <!-- ❌ 加载失败状态 -->
          <view v-if="!loading && error && categoryList.length === 0" class="error-container">
            <view class="error-text">{{ error }}</view>
            <view class="retry-button tn-button tn-button--primary tn-button--small" @click="fetchCategory">
              重试
            </view>
          </view>
        </scroll-view>
      </view>

      <!-- 📊 右侧内容列表 -->
      <view class="right-panel">
        <scroll-view 
          id="right-scroll-view"
          class="content-scroll" 
          scroll-y
          scroll-with-animation
          :scroll-into-view="scrollIntoView"
        >
          <!-- ⏳ 加载中状态 -->
          <view v-if="loading" class="loading-container">
            <tn-loading type="cycle" color="#0E7DFF" />
            <view class="loading-text">加载中...</view>
          </view>
          
          <!-- ❌ 加载失败状态 -->
          <view v-if="!loading && error && categoryList.length === 0" class="empty-container">
            <tn-empty mode="network" text="加载失败" text-size="28" />
            <view class="retry-button tn-button tn-button--primary tn-button--small" @click="fetchCategory">
              重试
            </view>
          </view>
          
          <!-- 🔄 遍历所有分类的二级分类 -->
          <view 
            v-for="(category, catIndex) in categoryList" 
            :key="catIndex" 
            class="category-section"
            :id="'category' + catIndex"
          >
            <!-- 📌 二级分类标题 -->
            <view class="section-title">
              {{ category.label }}
            </view>

            <!-- 📋 二级分类列表 -->
            <!-- 🎨 选中状态: 文字蓝色 #0E7DFF，背景浅蓝色 #E8F4FF，右侧箭头蓝色，加粗 -->
            <!-- 🎨 未选中状态: 文字深灰色 #333333，背景白色，右侧箭头深灰色 -->
            <!-- 🎨 hover状态: 浅灰色背景 -->
            <view 
              v-for="(item, index) in category.children" 
              :key="index" 
              class="content-item" 
              hover-class="content-item-hover" 
              @click="selectCategoryItem(item)"
              :style="{
                color: item.value === selectedCategoryUid ? '#0E7DFF' : '#333333',
                backgroundColor: item.value === selectedCategoryUid ? '#E8F4FF' : '#ffffff',
                fontWeight: item.value === selectedCategoryUid ? 'bold' : 'normal'
              }"
            >
              <view class="item-text" :style="{
                color: item.value === selectedCategoryUid ? '#0E7DFF' : '#333333',
                fontWeight: item.value === selectedCategoryUid ? 'bold' : 'normal'
              }">{{ item.label || '未命名' }}</view>
              <view class="item-arrow" :style="{
                color: item.value === selectedCategoryUid ? '#0E7DFF' : '#999999'
              }">›</view>
            </view>
          </view>

          <!-- 📭 空状态（没有错误且没有数据） -->
        <view v-if="!loading && !error && categoryList.length === 0" class="empty-container">
          <tn-empty mode="list" text="暂无内容" text-size="28" />
        </view>
      </scroll-view>
    </view>
    </view>
  </tn-popup>
  <!-- 🎨 分类弹窗结束 -->
</view>
    <view class="tn-padding-xl"></view>
    <view class="tn-padding-xl"></view>
</view>
    <!-- 底部tabbar start-->
    <view class="tabbar footerfixed">
      <view class="action"  @click="menuClick(1)">
        <view class="bar-icon">
          <!-- <view class="tn-icon-level">
          </view> -->
          <image
            class=""
            :src="currentIndex === 1 ? 'https://datiqiniu.allpp.cn/static/tabbar/information_tncur.png': 'https://datiqiniu.allpp.cn/static/tabbar/information_tn.png'"
          />
        </view>
        <view class="tn-color-gray">
          资源
        </view>
      </view>
      <view class="action"  @click="menuClick(0)">
        <view class="bar-circle tn-shadow-blur tn-cool-bg-color-9">
          <view :class="[currentIndex === 0 ? 'tn-icon-reload-home-fill' : 'tn-icon-reload-home', 'tn-color-white']" />
          <!-- <image class="" src='https://resource.tuniaokj.com/images/tabbar/information_tn.png'></image> -->
        </view>
        <view class="tn-color-gray">做题</view>
      </view>
      <view class="action" @click="menuClick(4)">
        <view class="bar-icon">
          <!-- <view class="tn-icon-signpost tn-color-gray--dark">
          </view> -->
          <image
            class=""
            :src="currentIndex === 4 ? 'https://datiqiniu.allpp.cn/static/tabbar/my_tncur.png' : 'https://datiqiniu.allpp.cn/static/tabbar/my_tn.png'"
          />
        </view>
        <view class="tn-color-gray">
          我的
        </view>
      </view>
    </view>
      <!-- 底部tabbar end-->
    </view>
    <!-- 实际内容结束 -->
    
    <!-- 厊屏窗 -->
    <tn-landscape 
      v-if="tipList.image"
      :show="showLandscape" 
      :close-btn="true"
      :close-position="tipList.close_position == 1 ? 'rightTop' : (tipList.close_position == 2 ? 'leftTop' : 'bottom')"
      @close="showLandscape = false"
    >
      <image 
        :src="tipList.image" 
        mode="widthFix" 
        style="width: 80vw; border-radius: 20rpx;"
        @click="tl('/' + tipList.url)"
      />
    </tn-landscape>
  </view>
</template>

<script>
  import template_page_mixin from '@/libs/mixin/template_page_mixin.js'
	import PageE from '@/pages/page/user.vue'
	import pageB from '@/pages/page/resource.vue'
	import SubjectTabsManager from '@/components/subject-tabs-manager.vue'
	import { getUserInfo, isUserLoggedIn } from '@/util/userStore.js'
	import ApiCache from '@/util/apiCache.js'

	export default {
		name: 'NavFooter',
		components: {
		PageE,
		pageB,
		SubjectTabsManager
	},
    mixins: [template_page_mixin],
    data() {
      return {
        // 厊屏窗显示状态
        showLandscape: false,
        // 厊屏窗配置数据（从后台获取）
        tipList: {},
        // 页面加载状态（首次加载）
        pageLoading: true,
        activeCategory: 0,
        websiteShop_name: getApp().globalData.sysConfig.website.shop_name || '答题平台',
        scrollIntoView: '', // 用于控制scroll-view滚动到指定位置
        categoryList: [],
        // 数据加载状态
        loading: false,
        // 题库列表加载状态
        loadingQuestionLib: false,
        // 加载错误信息
        error: '',
        selectedExam: null,
        // 选中的题库分类名称
        selectedCategory: null,
        // 选中的题库分类ID
        selectedCategoryUid: null,
        // 考试类型选择弹窗控制
        showCategoryList: false,
        // 是否已选择考试类型
        hasSelectedExamType: false,
        // 强制选择考试类型弹窗
        forceShowExamType: false,
        swiperList: [],
        // 考试选择和倒计时数据
        examOptions: [],
        selectedExam: { value: '1', text: '一级建造师' },
        countdownDays: 277,
        top: 0,
        currentIndex: 0,
        userInfoCache: null,
        menuTopList: [],
        menuList: [],
        current: 0,
        scrollList: [],
        // 科目管理弹窗
        showSubjectPopup: false,
        // 我的题库列表 - 默认选中questionLibList的前1个
        myQuestionLibList: [],
        // 全部题库列表 - 初始数据结构，API返回后会替换为真实数据
        questionLibList: [],
        // 题库列表的默认数据，当API调用失败时使用
        defaultQuestionLibList: [
          {uid: '1', name: '默认科目1' },
          {uid: '2', name: '默认科目1' },
          {uid: '3', name: '默认科目1' },
          {uid: '4', name: '默认科目1' },
        ],
        // 整体统计数据
			overallStats: {
				eliminated_errors: 0,
				total_questions: 0,
				total_errors: 0,
				overall_accuracy: 0,
				total_answers: 0,
			},
        // 关键数据加载状态（用于控制骨架屏）
        loadingCategory: true,
        loadingConfigs: true,
        loadingQuestionMenu: true
      }
    },
    computed: {
      // 使用计算属性实时获取globalData中的配置数据
      mainColor() {
        return getApp().globalData.mainColor || '#007AFF'
      },
      // 用户信息（优先从 userInfoCache 获取，自动响应变化）
      userInfo() {
        // 如果有缓存，优先使用
        if (this.userInfoCache && Object.keys(this.userInfoCache).length > 0) {
          return this.userInfoCache
        }
        
        // 最后降级到 globalData
        return getApp().globalData.userInfo || {}
      }
    },
    
    // 页面加载时执行
    async onLoad(options) {
      // 确保配置已经加载完成，如果没有则手动触发并等待完成
      if (!getApp().globalData.otherSettings || Object.keys(getApp().globalData.otherSettings).length === 0) {
        if (getApp().globalData.fetchOtherSettings) {
          await getApp().globalData.fetchOtherSettings()          
          // 强制更新视图，确保计算属性重新计算
          this.$forceUpdate()
        } else {
          console.error('fetchOtherSettings方法未找到')
        }
      }
      
      //首页重定向，如果otherSettings.homeRedirect等于1，则跳转到homeUrl
      // 等待配置加载
      const checkConfig = () => {
        const settings = getApp().globalData.otherSettings
        const homeUrl = getApp().globalData.homeUrl
        
        if (settings?.homeRedirect === '1') {

          // 获取当前页面路径
          const pages = getCurrentPages()
          const currentPage = pages[pages.length - 1]
          const currentRoute = '/' + currentPage.route

           uni.reLaunch({ url: homeUrl })
        }
      }
      if (getApp().globalData.otherSettings) {
        checkConfig()
      } else {
        setTimeout(checkConfig, 500) // 延迟检查
      }

      // 初始化用户信息（从本地存储加载）
      this.initUserInfo()
      
      // 处理分享参数：如果有 category_uid 参数，优先使用
      if (options && options.category_uid) {
        this.selectedCategoryUid = options.category_uid
        // 保存到本地存储
        uni.setStorageSync('selectedCategoryUid', options.category_uid)
      } else {
        // 从本地存储中恢复数据
        this.selectedCategoryUid = uni.getStorageSync('selectedCategoryUid')
      }
      
      const storedCategory = uni.getStorageSync('selectedCategory'+this.selectedCategoryUid)
      // 确保 selectedCategory 是字符串类型，防止显示 [object Object]
      this.selectedCategory = typeof storedCategory === 'string' ? storedCategory : null
      
      // 从本地存储中恢复用户选择的科目
      const savedQuestionLibList = uni.getStorageSync('myQuestionLibList'+this.selectedCategoryUid)
      if (savedQuestionLibList && Array.isArray(savedQuestionLibList)) {
        this.myQuestionLibList = savedQuestionLibList
        // 从本地存储中恢复选中的tab索引
        const savedTabIndex = uni.getStorageSync('selectedTabIndex'+this.selectedCategoryUid)
        if (savedTabIndex !== '' && savedTabIndex !== null && typeof savedTabIndex !== 'undefined') {
          this.current = parseInt(savedTabIndex)
        }
      } else {
        // 确保myQuestionLibList始终是数组
        this.myQuestionLibList = []
      }
      
      this.websiteShop_name = getApp().globalData.shopName || getApp().globalData.sysConfig?.website?.shop_name || '答题平台'
      
      // 初始化已选择考试类型状态
      this.hasSelectedExamType = !!(this.selectedCategoryUid && this.selectedCategory)
      
      if(this.hasSelectedExamType){
        // 关闭弹出层
        this.showCategoryList = false
      } else {
        // 强制显示考试类型选择弹窗
        this.showCategoryList = true
        this.forceShowExamType = true
      }
      
      this.fetchCategory()
      // 使用Promise.all()并行获取所有配置，提高效率
      this.fetchAllConfigs()
      // 在数据恢复后调用fetchQuestionMenu
      this.fetchQuestionMenu()
      // 调用获取题库列表的方法
      this.fetchQuestionList()
      // 注意：fetchLibraryStats 已移至 fetchQuestionList 的 finally 中调用
      
      // 性能优化：并行加载数据，加载完成后隐藏骨架屏
      this.loadPageData()
      
      // 添加定时器检查所有关键数据是否加载完成
      const checkAllDataLoaded = () => {
        if (!this.loadingCategory && !this.loadingConfigs && !this.loadingQuestionLib && !this.loadingQuestionMenu) {
          // 所有关键数据加载完成，延迟300ms隐藏骨架屏，让过渡更流畅
          setTimeout(() => {
            this.pageLoading = false
          }, 300)
        } else {
          // 继续检查
          setTimeout(checkAllDataLoaded, 100)
        }
      }
      checkAllDataLoaded()
    },
    // 页面显示时执行
    onShow() {
      // 根据配置决定是否弹出厊屏窗
      this.checkLandscapeDisplay();
      
      // 1. 更新用户信息，确保从登录页面返回时能刷新
      this.fetchUserBasicInfo();
      
      // 如果当前在用户Tab，刷新未读消息数量
      if (this.currentIndex === 4) {
        this.$nextTick(() => {
          if (this.$refs.pageE) {
            this.$refs.pageE.fetchUnreadMessageCount();
          }
        });
      }
      
      // 重新获取分类ID，确保获取最新的
      this.selectedCategoryUid = uni.getStorageSync('selectedCategoryUid')
      // 重新获取分类名称，并确保是字符串类型
      const storedCategory = uni.getStorageSync('selectedCategory'+this.selectedCategoryUid)
      this.selectedCategory = typeof storedCategory === 'string' ? storedCategory : null
      
      // 从本地存储中恢复最新的科目数据，确保在其他页面修改后能及时更新
      const savedQuestionLibList = uni.getStorageSync('myQuestionLibList'+this.selectedCategoryUid)
      if (savedQuestionLibList && Array.isArray(savedQuestionLibList)) {
        this.myQuestionLibList = savedQuestionLibList
        // 从本地存储中恢复选中的tab索引
        const savedTabIndex = uni.getStorageSync('selectedTabIndex'+this.selectedCategoryUid)
        if (savedTabIndex !== '' && savedTabIndex !== null && typeof savedTabIndex !== 'undefined') {
          this.current = parseInt(savedTabIndex)
        }
        // 更新顶部的scrollList
        this.updateScrollList()
        // 调用fetchQuestionMenu更新菜单数据，确保当前选中的科目菜单是最新的
        this.fetchQuestionMenu()
      } else {
        // 确保myQuestionLibList始终是数组
        this.myQuestionLibList = []
        // 更新顶部的scrollList
        this.updateScrollList()
      }
    },
    created() {
      // fetchQuestionMenu方法已移到onLoad中执行，确保数据恢复后再调用
    },
    methods: {
      // 页面刷新方法，用于navigateBackAndRefresh调用
      onRefresh() {
        // 刷新用户信息
        this.fetchUserBasicInfo();
      },
      
      // 页面触底事件处理
      onReachBottom() {
        try {
          // 只在对应页面显示时调用其onReachBottom方法
          if (this.currentIndex === 1 && this.$refs.pageB) {
            this.$refs.pageB.onReachBottom && this.$refs.pageB.onReachBottom();
          } else if (this.currentIndex === 4 && this.$refs.pageE) {
            this.$refs.pageE.onReachBottom && this.$refs.pageE.onReachBottom();
          }
        } catch (error) {
          console.error('页面触底事件处理失败:', error);
        }
      },
      
      // 检查是否应该显示厊屏窗
      checkLandscapeDisplay() {
        // 如果没有配置数据或没有图片，不显示
        if (!this.tipList || !this.tipList.image) {
          return;
        }
        
        const displayMode = parseInt(this.tipList.display_mode || '1');
        
        if (displayMode === 1) {
          // 1-不自动展示（点击才弹出）
          return;
        } else if (displayMode === 2) {
          // 2-每次进入都弹出
          this.showLandscape = true;
        } else if (displayMode === 3) {
          // 3-每天首次进入弹出
          const today = new Date().toLocaleDateString();
          const lastShowDate = uni.getStorageSync('landscape_last_show_date');
          
          if (lastShowDate !== today) {
            this.showLandscape = true;
            // 记录今天已显示
            uni.setStorageSync('landscape_last_show_date', today);
          }
        }
      },
      // 性能优化：页面数据加载
      async loadPageData() {
        try {
          // 移除重复的轮播图请求，使用fetchAllConfigs中的轮播图数据
          // 等待题库列表加载完成
          await new Promise(resolve => {
            const checkLoading = setInterval(() => {
              if (!this.loadingQuestionLib) {
                clearInterval(checkLoading)
                resolve()
              }
            }, 100)
          })
          
          // 不再手动设置pageLoading状态，由定时器统一管理
        } catch (error) {
          console.error('页面数据加载失败:', error)
          // 即使失败也会由定时器统一管理pageLoading状态
        }
      },
      
      // 初始化用户信息（同步加载）
      initUserInfo() {
        try {
          // 从本地存储加载用户信息
          const userInfo = uni.getStorageSync('userInfo')
          if (userInfo && Object.keys(userInfo).length > 0) {
            this.userInfoCache = userInfo
            // 同步到 globalData
            getApp().globalData.userInfo = userInfo
          } else {
            this.userInfoCache = {}
          }
        } catch (error) {
          console.error('初始化用户信息失败:', error)
          this.userInfoCache = {}
        }
      },
      
      closeCategoryList() {
       // 只有已选择考试类型才能关闭弹窗
       if (this.hasSelectedExamType) {
         this.showCategoryList = false
       }
      },
      // tab选项卡切换题库
      tabChange(index) {
        this.current = index
        // 将当前选中的tab索引存入本地缓存
        uni.setStorageSync('selectedTabIndex' + this.selectedCategoryUid, index)
        // 切换tab时执行fetchQuestionMenu，更新菜单数据
        this.fetchQuestionMenu()
      },
      fetchQuestionMenu() {
            // 设置加载状态为true
            this.loadingQuestionMenu = true
            // 支持两种场景：
            // 1. 分类选择场景：使用分类下的第一个tab的UID
            // 2. tab切换场景：使用选中tab的UID
            let selectedSubjectId = ''
            
            // 如果myQuestionLibList中有数据，优先使用选中的tab的UID或第一个tab的UID
            if (this.myQuestionLibList && this.myQuestionLibList.length > 0) {
                // 获取当前选中的tab或第一个tab
                const targetSubject = this.myQuestionLibList[this.current] || this.myQuestionLibList[0]
                selectedSubjectId = (targetSubject && targetSubject.id) || ''
            } 
            
            // 如果myQuestionLibList中没有数据或没有有效ID，使用selectedCategoryUid作为备选
            if (!selectedSubjectId && this.selectedCategoryUid) {
                selectedSubjectId = this.selectedCategoryUid
            }
            
            // 参数验证
            if (!selectedSubjectId) {
                console.warn('fetchQuestionMenu: 科目ID为空', 'current:', this.current, 'myQuestionLibList:', this.myQuestionLibList, 'selectedCategoryUid:', this.selectedCategoryUid)
                this.loadingQuestionMenu = false
                return
            }
            
            this.$api.apiQuestionMenu({
                uid: selectedSubjectId
            }).then(res => {
                if (res.code === 1 && res.data) {
                    this.menuTopList = Array.isArray(res.data) ? res.data : []
                } else {
                    console.warn('获取功能菜单失败:', res.msg)
                    this.menuTopList = []
                }
            }).catch(error => {
                console.error('获取功能菜单失败:', error)
                this.menuTopList = []
            }).finally(() => {
                // 无论成功还是失败，都设置加载状态为false
                this.fetchLibraryStats()
                this.loadingQuestionLib = false
                this.loadingQuestionMenu = false
            })
        },
      // 考试选择变化处理
      onExamChange(value) {
        // 根据选择的值找到对应的选项对象
        const selectedOption = this.examOptions.find(option => option.value === value)
        if (selectedOption) {
          this.selectedExam = selectedOption
        }
      },
      // 打开选择考试页面
      openBusinessPage() {
        // 不修改 selectedCategory，直接打开弹窗
        this.showCategoryList = true
      },
      fetchCategory() {
      // 设置加载状态为true
      this.loading = true
      this.loadingCategory = true
      // 清除之前的错误信息
      this.error = ''
      // 清空分类列表
      this.categoryList = []
      
      
      this.$api.apiQuestionCategoryTree().then((res) => {
        
        // 数据格式验证
        if (res && res.data && Array.isArray(res.data)) {
          // 数据格式正确，处理数据
          this.processCategoryData(res.data)
        } else {
          // 数据格式错误
          throw new Error('数据格式错误，预期为数组')
        }
      }).catch((error) => {
        console.error('获取分类数据失败', error)
        // 确保分类列表为空
        this.categoryList = []
      }).finally(() => {
        // 无论成功还是失败，都设置加载状态为false
        this.loading = false
        this.loadingCategory = false
      })
    },
    // 新增：使用Promise.all()并行获取所有配置，提高效率
    async fetchAllConfigs() {
      this.loadingConfigs = true
      try {
        const client = this.$func.currentPlatform()
        const [bannerRes, menuRes, tipRes] = await Promise.all([
          this.$api.apiImageConfig({ type: 'image_banner', position: 'home_top', client }),
          this.$api.apiImageConfig({ type: 'image_menu', position: 'home_menu', client }),
          this.$api.apiImageConfig({ type: 'image_banner', position: 'home_tip', client }),
        ])
        
        // 更新数据
        this.swiperList = bannerRes.data || []
        this.menuList = menuRes.data || []
        this.tipList = tipRes.data && tipRes.data[0] ? tipRes.data[0] : {}
      } catch (error) {
        console.error('获取配置失败:', error)
        // 失败时使用默认值，确保页面正常显示
        this.swiperList = []
        this.menuList = []
        this.tipList = {}
      } finally {
        this.loadingConfigs = false
      }
    },
    
    // 保留原有方法，确保兼容性
    fetchBanner() {
      // 使用 ApiCache 获取轮播图（缓存30分钟）
      return ApiCache.getBanner(
        () => this.$api.apiImageConfig({
          type: 'image_banner',
          position: 'home_top',
          client: this.$func.currentPlatform()
        }),
        {
          type: 'image_banner',
          position: 'home_top',
          client: this.$func.currentPlatform()
        }
      ).then(res => {
        this.swiperList = res.data || []
        return res
      })
    },
    fetchTopMenu() {
      return this.$api.apiImageConfig({
        type: 'image_menu',
        position: 'home_menu_top',
        client: this.$func.currentPlatform()
      }).then(res => {
        this.menuTopList = res.data || []
        return res
      })
    },
    fetchMenu() {
      return this.$api.apiImageConfig({
        type: 'image_menu',
        position: 'home_menu',
        client: this.$func.currentPlatform()
      }).then(res => {
        this.menuList = res.data || []
        return res
      })
    },
    // 处理分类数据，保留exam_time属性
    processCategoryData(data) {
      // 数据转换和验证
      const processedData = data.map(item => {
        // 处理子分类，确保每个子分类有label和value属性，并保留所有原始属性
        const processedChildren = Array.isArray(item.children) ? item.children.map(child => ({
          ...child, // 保留所有原始属性，包括exam_time
          label: child.label || child.title || '未命名分类', // 使用label或title作为显示文本
          value: child.value || child.uid || '' // 使用value或uid作为唯一标识
        })) : []
        
        // 返回处理后的分类数据，保留所有原始属性，除了重新处理的children
        return {
          ...item, // 保留所有原始属性，包括exam_time
          label: item.label || item.title || '未命名分类', // 使用label或title作为显示文本
          children: processedChildren
        }
      })
      
      // 更新分类列表
      this.categoryList = processedData
      
      // 🎨 根据已选中的二级分类，更新一级分类的选中状态
      if (this.selectedCategoryUid) {
        this.initActiveCategory()
        this.updateCountdownForSelectedCategory()
      }
    },
    
    // 🎨 初始化一级分类的选中状态，使其跟随二级分类
    initActiveCategory() {
      if (!this.selectedCategoryUid || !this.categoryList.length) return
      
      this.categoryList.forEach((category, index) => {
        if (category.children && category.children.some(child => child.value === this.selectedCategoryUid)) {
          this.activeCategory = index
        }
      })
    },
    
    // 计算倒计时天数
    calculateCountdown(examTime) {
      if (!examTime) {
        // 如果没有考试时间，设置为0
        this.countdownDays = 0
        return
      }
      
      // 转换为时间戳（如果是字符串）
      const timestamp = typeof examTime === 'string' ? parseInt(examTime) : examTime
      
      // 获取当前时间戳（秒）
      const now = Math.floor(Date.now() / 1000)
      
      // 计算时间差（秒）
      const diffSeconds = timestamp - now
      
      if (diffSeconds <= 0) {
        // 考试已结束
        this.countdownDays = 0
        return
      }
      
      // 计算天数差（向上取整）
      const days = Math.ceil(diffSeconds / (24 * 60 * 60))
      this.countdownDays = days
    },
    
    // 为已选中的分类更新倒计时
    updateCountdownForSelectedCategory() {
      // 遍历所有分类和子分类，找到对应的selectedCategoryUid
      let examTime = null
      
      for (const category of this.categoryList) {
        // 检查子分类
        for (const child of category.children) {
          if (child.value === this.selectedCategoryUid) {
            examTime = child.exam_time
            break
          }
        }
        if (examTime) break
      }
      
      // 计算倒计时
      this.calculateCountdown(examTime)
    },
    // 选中一级分类
    selectCategory(index) {
      // 更新当前选中的分类索引
      this.activeCategory = index
      // 设置滚动位置，自动滚动到对应的分类区域
      this.scrollIntoView = 'category' + index
    },
    
    // 选中分类项
    selectCategoryItem(item) {
      // 赋值选中的分类名称和ID
      this.selectedCategory = item.label || '未命名分类'
      this.selectedCategoryUid = item.value || ''
      
      // 🎨 更新一级分类的选中状态，使其跟随二级分类
      this.categoryList.forEach((category, index) => {
        if (category.children && category.children.some(child => child.value === item.value)) {
          this.activeCategory = index
        }
      })
      
      // 本地持久化存储
      uni.setStorageSync('selectedCategory'+this.selectedCategoryUid, this.selectedCategory)
      uni.setStorageSync('selectedCategoryUid', this.selectedCategoryUid)
      
      // 更新已选择考试类型状态
      this.hasSelectedExamType = true
      this.forceShowExamType = false
      
      // 计算并更新倒计时天数
      this.calculateCountdown(item.exam_time)
      
      // 从本地存储中恢复该考试类型对应的已保存科目
      const savedQuestionLibList = uni.getStorageSync('myQuestionLibList'+this.selectedCategoryUid)
      if (savedQuestionLibList && Array.isArray(savedQuestionLibList)) {
        this.myQuestionLibList = savedQuestionLibList
      } else {
        // 如果没有保存的科目或数据格式不正确，则清空当前列表
        this.myQuestionLibList = []
      }
      
      // 调用获取题库菜单的方法
      this.fetchQuestionMenu()
      // 调用获取题库列表的方法
      this.fetchQuestionList()
      // 关闭弹出层
      this.showCategoryList = false
    },
    tl(url) {
			if(url === '') {
				this.$func.showToast('暂未开放')
				return
			}
			this.$func.navigatorTo(url)
		},
    async fetchUserBasicInfo() {
	try {
		// 检查登录状态，未登录时不调用API
		if (!isUserLoggedIn()) {
			// 尝试从本地缓存获取用户信息
			const localUserInfo = uni.getStorageSync('userInfo') || {};
			this.userInfoCache = localUserInfo;
			return;
		}
		
		// 登录状态下，优先从本地存储获取用户信息，因为登录成功后userInfo已保存到本地存储
		const localUserInfo = uni.getStorageSync('userInfo') || {};
		if (localUserInfo && Object.keys(localUserInfo).length > 0) {
			this.userInfoCache = localUserInfo;
			return;
		}
		
		// 如果本地存储没有，尝试从API获取
		const userInfo = await getUserInfo(true); // 强制从 API 刷新
		if (userInfo && Object.keys(userInfo).length > 0) {
			this.userInfoCache = userInfo;
		} else {
			console.warn('无法获取用户信息');
			this.userInfoCache = {};
		}
	} catch (error) {
		console.error('获取用户信息失败:', error);
		// 发生错误时，尝试从本地存储获取
		const localUserInfo = uni.getStorageSync('userInfo') || {};
		this.userInfoCache = localUserInfo;
	}
	},
    menuClick(index) {
		// 如果是切换到"我的"页面（index === 4），先检查登录状态
		// if (index === 4) {
		// 	// 使用统一的登录检查方法
		// 	if (!isUserLoggedIn()) {
		// 		// 未登录，跳转到登录页面
		// 		uni.navigateTo({
		// 			url: "/subpages/user/login"
		// 		})
		// 		return
		// 	}
		// 	// 已登录，刷新用户信息
		// 	this.fetchUserBasicInfo()
		// }
		
		this.currentIndex = index

		if (this.currentIndex === 4) {
        // 使用nextTick确保组件渲染完成后再调用方法
        this.$nextTick(() => {
          // 调用PageE组件的方法
          if (this.$refs.pageE) {
            this.$refs.pageE.fetchWebsiteConfig();
            // 获取未读消息数量
            this.$refs.pageE.fetchUnreadMessageCount();
          }
        })
		} else if (this.currentIndex === 1) {
        // 使用nextTick确保组件渲染完成后再调用方法
        this.$nextTick(() => {
          // 调用pageB组件的方法，传入当前选中的分类ID
          if (this.$refs.pageB) {
            this.$refs.pageB.fetchResourceList(this.selectedCategoryUid)
          }
        })
      }
	},
    onReachBottom() {
        if (this.currentIndex === 3 && this.$refs.pageD) {
          this.$refs.pageD.fetchNewsList()
        } else if (this.currentIndex === 1 && this.$refs.pageB) {
          this.$refs.pageB.fetchResourceList(this.selectedCategoryUid)
        }
      },
      
      // 微信小程序分享功能 - 好友分享
      onShareAppMessage(res) {
        // 默认分享信息
        const shareInfo = {
          title: `${this.websiteShop_name} - ${this.selectedCategory || '答题平台'}`,
          path: '/pages/index/index',
          imageUrl: this.swiperList.length > 0 ? this.swiperList[0].image : ''
        }
        
        // 如果有选中的考试类型，添加到分享路径
        if (this.selectedCategoryUid) {
          shareInfo.path = `/pages/index/index?category_uid=${this.selectedCategoryUid}`
          shareInfo.title = `${this.websiteShop_name} - ${this.selectedCategory}`
        }
        
        return shareInfo
      },
      
      // 微信小程序分享功能 - 朋友圈分享
      onShareTimeline(res) {
        const shareInfo = {
          title: `${this.websiteShop_name} - ${this.selectedCategory || '答题平台'}`,
          query: '',
          imageUrl: this.swiperList.length > 0 ? this.swiperList[0].image : ''
        }
        
        // 如果有选中的考试类型，添加到分享查询参数
        if (this.selectedCategoryUid) {
          shareInfo.query = `category_uid=${this.selectedCategoryUid}`
        }
        
        return shareInfo
      },
      
      // 跳转到倒计时详情页
      navigateToCountdownDetail() {
        uni.navigateTo({ url: '/subpages/countdown/detail?category_uid='+this.selectedCategoryUid })
      },
      
      // 科目管理弹窗方法
      // 关闭科目弹窗
      closeSubjectPopup() {
        this.showSubjectPopup = false
      },
      
      // 处理保存科目事件
      handleSubjectSave(subjects) {
        // 保存用户选择的科目到本地存储，确保下次打开应用时能记住选择
        uni.setStorageSync('myQuestionLibList'+this.selectedCategoryUid, subjects)
        
        // 更新本地科目列表
        this.myQuestionLibList = [...subjects]
        
        // 保存成功后更新顶部的scrollList
        this.updateScrollList()
        
      },
      
      // 处理添加科目事件
      handleSubjectAdd(subject, subjects) {
        // 保存到本地存储
        uni.setStorageSync('myQuestionLibList'+this.selectedCategoryUid, subjects)
        // 更新本地科目列表
        this.myQuestionLibList = [...subjects]
        // 更新scrollList
        this.updateScrollList()
      },
      
      // 处理删除科目事件
      handleSubjectRemove(index, subjects) {
        // 保存到本地存储
        uni.setStorageSync('myQuestionLibList'+this.selectedCategoryUid, subjects)
        // 更新本地科目列表
        this.myQuestionLibList = [...subjects]
        // 更新scrollList
        this.updateScrollList()
      },
      fetchQuestionList() {
        // 添加加载状态
        this.loadingQuestionLib = true;
			this.$api.apiHotQuestionLib({
        category_uid: this.selectedCategoryUid,
        is_show: 1,
				page_no: 1,
				page_size: 20
			}).then(res => {
				// 检查API返回的数据结构是否正确，增加兼容性处理
				if (res && res.code === 1) {
              // 兼容不同的数据结构
              let lists = [];
              if (res.data && Array.isArray(res.data.lists)) {
                lists = res.data.lists;
              }
              // 处理数据，确保每个科目有id和name属性
              if (lists.length > 0) {
                this.questionLibList = lists.map(item => ({
                  id: item.id || item.uid || '',
                  name: item.name || item.title || '未命名科目',
                  ...item // 保留其他属性
                }));
              }else{
                this.questionLibList = this.defaultQuestionLibList
              }
              
              // 只有当myQuestionLibList为空时，才设置默认值
              // 如果已经有保存的科目列表，则保留不变
              if (this.myQuestionLibList.length === 0) {
                // 默认选中前1个题库到myQuestionLibList
                this.updateMyQuestionLibList();
              } else {
                // 已经有保存的科目列表，更新scrollList以确保UI正确显示
                this.updateScrollList();
              }
				}
			}).catch(error => {
				// API调用失败时，使用默认数据
				console.error('获取题库列表失败:', error)
				// 使用默认题库数据
				this.questionLibList = this.defaultQuestionLibList;
				// 只有当myQuestionLibList为空时，才设置默认值
              // 如果已经有保存的科目列表，则保留不变
              if (this.myQuestionLibList.length === 0) {
                // 默认选中前1个题库到myQuestionLibList
                this.updateMyQuestionLibList();
              } else {
                // 已经有保存的科目列表，更新scrollList以确保UI正确显示
                this.updateScrollList();
              }
			}).finally(() => {
              // 移除加载状态
              this.loadingQuestionLib = false;
              
              // 题库列表加载完成后，加载统计数据
              this.fetchLibraryStats();
            });
		},
			
			// 更新我的题库列表，默认选中前1个并保存到本地存储
			updateMyQuestionLibList() {
				// 清空当前我的题库列表
				this.myQuestionLibList = []
				
				// 只选中前1个题库
				const selectedCount = Math.min(1, this.questionLibList.length)
				for (let i = 0; i < selectedCount; i++) {
					// 添加题库到我的题库列表
					this.myQuestionLibList.push({
						id: this.questionLibList[i].id || this.questionLibList[i].uid,
						name: this.questionLibList[i].name || this.questionLibList[i].title
					})
				}
				
				// 保存默认科目到本地存储，确保其他页面能读取到
				if (this.myQuestionLibList.length > 0 && this.selectedCategoryUid) {
					uni.setStorageSync('myQuestionLibList'+this.selectedCategoryUid, this.myQuestionLibList)
				}
				
				// 根据选中的题库更新scrollList
				this.updateScrollList()
				// 调用fetchQuestionMenu加载默认题库菜单
				this.fetchQuestionMenu()
        this.fetchLibraryStats()
			},
			
			// 根据选中的题库更新scrollList
			updateScrollList() {
				// 确保myQuestionLibList是数组
				if (!Array.isArray(this.myQuestionLibList)) {
					this.scrollList = []
					return
				}
				
				// 根据myQuestionLibList生成scrollList
				this.scrollList = this.myQuestionLibList.map(subject => ({
					name: subject.name
				}))
				
				// 确保current索引在有效范围内
				if (this.current >= this.scrollList.length) {
					this.current = Math.max(0, this.scrollList.length - 1)
				}
			},
      // 获取题库整体统计数据
			async fetchLibraryStats() {
				try {
					// 初始化默认值
					let stats = {
						eliminated_errors: 0,
						total_questions: 0,
						total_errors: 0,
						overall_accuracy: 0,
						total_answers: 0,
					};
					
						// 获取当前选中的题库ID
						const currentLibrary = this.myQuestionLibList[this.current];
							
						// 兼容 id 和 uid 字段
						const uid = currentLibrary?.uid || currentLibrary?.id;
						if (currentLibrary && uid) {
							// 调用API获取统计数据
							try {
								const res = await this.$api.apiQuestionLibraryStats({uid: uid});
								if (res.code === 1 && res.data) {
									stats = {
										eliminated_errors: res.data.eliminated_errors || 0,
										total_questions: res.data.total_questions || 0,
										total_errors: res.data.total_errors || 0,
										overall_accuracy: res.data.overall_accuracy || 0,
										total_answers: res.data.total_answers || 0,
									};
								} else {
									console.warn('[首页] 统计数据加载失败:', res.msg);
								}
							} catch (apiErr) {
								console.error('[首页] 统计数据API调用失败:', apiErr);
								// 保持默认值
							}
						} else {
							console.warn('[首页] 没有选中的题库，使用默认统计数据');
						}
					// 更新统计数据
					this.overallStats = stats;
				} catch (err) {
					console.error('[首页] 统计数据加载失败:', err);
					// 统计数据加载失败，使用默认值
					this.overallStats = {
						eliminated_errors: 0,
						total_questions: 0,
						total_errors: 0,
						overall_accuracy: 0,
						total_answers: 0,
					};
				}
			},
    },

  }
</script>

/* 全局样式 - 动画定义 */
<style lang="scss">
  @-webkit-keyframes bg {
    0% {
      background-position: 0 0;
    }
    100% {
      background-position: 0 -300rpx;
    }
  }
  
  @keyframes bg {
    0% {
      background-position: 0 0;
    }
    100% {
      background-position: 0 -300rpx;
    }
  }
</style>

<style lang="scss" scoped>
  /* 考试选择和倒计时样式 */
  .exam-section {
    margin:0 20rpx;
    padding: 10rpx;
    color  : #f0f9ff;
    border-radius: 12rpx;
    min-height: 120rpx;
    display: flex;
    align-items: stretch;
  }
  
  .exam-container {
    width: 100%;
    flex: 1;
    align-items: center;
  }
  
  .exam-title-section {
    display: flex;
    height: 100%;
    gap: 10rpx;
    align-items: flex-end;
  }
  
  .exam-title {
    font-size: 40rpx;
    font-weight: bold;
  }
  
  .exam-select {
    font-size: 24rpx;
  }
  
  .exam-select-text {
    font-size: 28rpx;
  }
  
  .exam-countdown {
    display: flex;
    justify-content: center;
    //竖向排列
    width: 113rpx;
    flex-direction: column;
    gap: 5rpx;
    border: 1rpx solid #f6f6f6;
    border-radius: 8rpx;
  }
  .libtablist {
    width: 100%;
    align-items: center;
    background-color: #ffffff;
    box-shadow: 0rpx 0rpx 80rpx 0rpx rgba(0, 0, 0, 0.07);
    border-radius: 20rpx;
    margin-bottom: 10rpx;
  }
  
  .countdown-label {
    font-size: 22rpx;
    color: #fff;
    background-color: #3b82f6;
    padding: 4rpx 10rpx;
    border-radius: 8rpx;
  }
  
  .countdown-days {
    min-width: 113rpx;
    font-size: 26rpx;
    font-weight: bold;
    color: #3b82f6;
    background-color: #fff;
    padding: 8rpx 20rpx;
    text-align: center;
    border-radius: 8rpx;
  }
  /* 自定义导航栏内容 start */
    .custom-nav {
      height: 100%;
      
      &__back {
        margin: auto 5rpx;
        font-size: 40rpx;
        margin-right: 10rpx;
        margin-left: 30rpx;
        flex-basis: 5%;
      }
      
      &__search {
        flex-basis: 60%;
        width: 100%;
        height: 100%;
        
        &__box {
          width: 100%;
          height: 70%;
          padding: 10rpx 0;
          margin: 0 30rpx;
          border-radius: 60rpx 60rpx 0 60rpx;
          font-size: 24rpx;
          background-color: rgba(255,255,255,0.2);
        }
        
        &__icon {
          padding-right: 10rpx;
          margin-left: 20rpx;
          font-size: 30rpx;
        }
        
        &__text {
          color: #FFFFFF;
          margin-left: 20rpx;
        }
        }
      }
    /* 自定义导航栏内容 end */
    
    /*logo start */
    .logo-image{
      width: 65rpx;
      height: 65rpx;
      position: relative;
    }
    .logo-pic{
      background-size: cover;
      background-repeat:no-repeat;
      // background-attachment:fixed;
      background-position:top;
      border-radius: 50%;
    }
    

  /* 信息展示 start */
  .tn-info {
    
    &__container {
      margin-top: 40rpx;
    }
    
    &__item {
      width: 48%;
      margin: 10rpx 0rpx;
      padding: 30rpx 20rpx;
      border-radius: 10rpx;
      
      &__left {
        
        &--icon {
          width: 60rpx;
          height: 60rpx;
          border-radius: 50%;
          font-size: 40rpx;
          margin-right: 15rpx;
          position: relative;
          z-index: 1;
          
          &::after {
            content: " ";
            position: absolute;
            z-index: -1;
            width: 100%;
            height: 100%;
            left: 0;
            bottom: 0;
            border-radius: inherit;
            opacity: 1;
            transform: scale(1, 1);
            background-size: 100% 100%;
            background-image: url(https://datiqiniu.allpp.cn/uploads/images/20260101/20260101202804094280706.png);
          }
        }
        
        &__content {
          font-size: 28rpx;
          
          &--data {
            margin-top: 5rpx;
            font-weight: bold;
          }
        }
      }
      
      &__right {
        &--icon {
          font-size: 30rpx;
          opacity: 0.5;
        }
      }
    }
  }
  .job-shadow{
      box-shadow: 0rpx 0rpx 80rpx 0rpx rgba(0, 0, 0, 0.07);
      border-radius: 20rpx;
  }
  /* 信息展示 end */
  
  /* 图标容器16 start */
  .tn-cool-color-icon16{
    // background-image: -webkit-linear-gradient(135deg, #ED1C24, #FECE12);   16
    // background-image: linear-gradient(135deg, #ED1C24, #FECE12);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    -webkit-text-fill-color: transparent;
  }
    .icon16 {
      &__item {
        // width: 30%;
        background-color: #FFFFFF;
        border-radius: 10rpx;
        padding: 0rpx;
        margin: 0rpx;
        transform: scale(1);
        transition: transform 0.3s linear;
        transform-origin: center center;
        
        &--icon {
          width: 80rpx;
          height: 80rpx;
          font-size: 70rpx;
          border-radius: 50%;
          margin-bottom: 18rpx;
          position: relative;
          z-index: 1;
        }
      }
    }
  /* 图标容器16 end */
  
  /* 底部tabbar start*/
  .footerfixed{
   position: fixed;
   width: 100%;
   bottom: 0;
   z-index: 999;
   background-color: #FFFFFF;
   box-shadow: 0rpx 0rpx 30rpx 0rpx rgba(0, 0, 0, 0.07);
  }
  
  .tabbar {
    display: flex;
    align-items: center;
    min-height: 110rpx;
    justify-content: space-between;
    padding: 0;
    height: calc(110rpx + env(safe-area-inset-bottom) / 2);
    padding-bottom: calc(env(safe-area-inset-bottom) / 2);
  }
  
  .tabbar .action {
    font-size: 22rpx;
    position: relative;
    flex: 1;
    text-align: center;
    padding: 0;
    display: block;
    height: auto;
    line-height: 1;
    margin: 0;
    overflow: initial;
  }
  
  .tabbar .action .bar-icon {
    width: 100rpx;
    position: relative;
    display: block;
    height: auto;
    margin: 0 auto 10rpx;
    text-align: center;
    font-size: 42rpx;
    // line-height: 50rpx;
  }
  
  .tabbar .action .bar-icon image {
    width: 50rpx;
    height: 50rpx;
    display: inline-block;
  }
  
  .tabbar .action .bar-circle {
    position: relative;
    display: block;
    margin: -30rpx auto 10rpx;
    text-align: center;
    font-size: 52rpx;
    line-height: 90rpx;
    width: 90rpx !important;
    height: 90rpx !important;
    overflow: hidden;
    border-radius: 50%;
    box-shadow: 0rpx 0rpx 20rpx 0rpx rgba(231, 47, 140, 0.5);
  }
  
  .tabbar .action .bar-circle image {
    width: 60rpx;
    height: 60rpx;
    display: inline-block;
    margin: 15rpx auto 15rpx;
  }
  .business-content {
  display: flex;
  flex: 1;
  overflow: hidden;

  .left-panel {
    width: 160rpx;
    background-color: #f6f6f6;
    border-right: 1rpx solid #f6f6f6;

    .category-scroll {
      height: 100%;
      -webkit-overflow-scrolling: touch;
    }

    .category-item {
      padding: 30rpx 0;
      font-size: 28rpx;
      color: #333333;
      border-left: 4rpx solid transparent;
      transition: all 0.3s ease;
      text-align: center;

      &.active {
        background-color: #ffffff;
        color: #0E7DFF;
        border-left-color: #0E7DFF;
        font-weight: 1000;
      }

      &.category-item-hover {
        background-color: #ffffff;
      }
    }
  }

  .right-panel {
    flex: 1;
    display: flex;
    flex-direction: column;
    background-color: #ffffff;

    .content-scroll {
      height: calc(100% - 0rpx);
      padding: 20rpx 24rpx;
      box-sizing: border-box;
      -webkit-overflow-scrolling: touch;
    }

    .category-section {
      margin-bottom: 30rpx;
    }

    .section-title {
      font-size: 30rpx;
      font-weight: bold;
      color: #333333;
      margin-bottom: 20rpx;
      padding: 16rpx 0;
      border-bottom: 1rpx solid #f6f6f6;
    }

    .content-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 20rpx 16rpx;
      background-color: #f6f6f6;
      border-radius: 8rpx;
      margin-bottom: 12rpx;
      transition: all 0.2s ease;

      &.content-item-hover {
        background-color: #f6f6f6;
        transform: scale(0.98);
      }

      .item-text {
        font-size: 28rpx;
        color: #333333;
        flex: 1;
      }

      .item-arrow {
        font-size: 40rpx;
        color: #a5a5a5;
        margin-left: 12rpx;
      }
    }

    .empty-state {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 400rpx;
      font-size: 28rpx;
      color: #999999;
    }
    
    .empty-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 100rpx 0;
      width: 100%;
    }
    
    /* 加载状态样式 */
    .loading-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 200rpx;
    }

  /* 自定义导航栏样式 - 简单直接的布局 */
  .custom-navbar {
    position: relative;
    width: 100%;
    height: 88rpx;
    z-index: 999;
    background-color: #0E7DFF;
  }
  
  .navbar-content {
    position: relative;
    width: 100%;
    height: 100%;
    padding: 0 30rpx;
  }
  
  /* 使用grid布局确保水平排列 */
  .navbar-content {
    display: grid !important;
    grid-template-columns: 120rpx 1fr 120rpx !important;
    align-items: center !important;
  }
  
  /* 直接设置每个元素的位置 */
  .navbar-left {
    grid-column: 1;
    display: flex;
    align-items: center;
  }
  
  .navbar-center {
    grid-column: 2;
    text-align: center;
  }
  
  .navbar-right {
    grid-column: 3;
    text-align: right;
  }
  
  .navbar-back-icon {
    font-size: 36rpx;
    margin-right: 10rpx;
  }
  
  .navbar-back-text {
    font-size: 28rpx;
  }
  
  .navbar-title {
    font-size: 36rpx;
    font-weight: bold;
  }
  
  .navbar-action-text {
    font-size: 32rpx;
    font-weight: 500;
  }
    
    .loading-text {
      margin-top: 20rpx;
      font-size: 28rpx;
      color: #999999;
    }
    
    /* 错误状态样式 */
    .error-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 100rpx 0;
      width: 100%;
    }
    
    .error-text {
      font-size: 28rpx;
      color: #ff4d4f;
      margin-bottom: 20rpx;
      text-align: center;
      padding: 0 40rpx;
    }
    
    /* 重试按钮样式 */
    .retry-button {
      margin-top: 20rpx;
      padding: 20rpx 40rpx;
    }
  }
}

// 导航栏右侧图标样式
.nav-right {
  display: flex;
  align-items: center;
  gap: 12rpx;
  margin-right: 16rpx;

  .nav-icon {
    width: 40rpx;
    height: 40rpx;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24rpx;
    color: #ffffff;
    background-color: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    transition: all 0.2s ease;

    &.hover-class {
      background-color: rgba(255, 255, 255, 0.3);
    }
  }
}

/* 科目管理弹窗样式优化 */
.subject-section {
  margin: 20rpx;
  padding: 30rpx;
  background-color: #ffffff;
  border-radius: 16rpx;
  box-shadow: 0 2rpx 12rpx rgba(0, 0, 0, 0.08);
}

.section-title-text {
  font-size: 32rpx;
  font-weight: bold;
  color: #333333;
  margin-bottom: 12rpx;
  text-align: center;
}

.section-desc {
  font-size: 24rpx;
  color: #999999;
  margin-bottom: 30rpx;
  text-align: center;
}

/* 我的科目样式 */
.my-subjects {
  display: flex;
  flex-wrap: wrap;
  gap: 16rpx;
  padding: 0;
}

.subject-item {
  display: inline-flex;
  align-items: center;
  background-color: #f5f5f5;
  border-radius: 20rpx;
  padding: 16rpx 24rpx;
  font-size: 28rpx;
  color: #333333;
  position: relative;
}

.subject-item-text {
  margin-right: 20rpx;
}

.subject-item-delete {
  font-size: 24rpx;
  color: #999999;
  cursor: pointer;
  padding: 4rpx;
}

.empty-subjects {
  width: 100%;
  text-align: center;
  color: #999999;
  font-size: 26rpx;
  padding: 40rpx 0;
}

/* 全部科目样式 */
.all-subjects {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20rpx;
  padding: 0;
}

.subject-item-add {
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
  background-color: #ffffff;
  border: 2rpx solid #e5e5e5;
  border-radius: 12rpx;
  padding: 24rpx;
  font-size: 28rpx;
  color: #333333;
  cursor: pointer;
  transition: all 0.2s ease;
  
  &.hover-class {
    background-color: #f5f5f5;
  }
}

.subject-item-added {
  border-color: #0E7DFF;
  color: #0E7DFF;
}

.subject-item-add-icon {
  font-size: 24rpx;
  color: #999999;
}

.subject-item-added .subject-item-add-icon {
  color: #0E7DFF;
}

/* 文本居中辅助类 */
.text-center {
  text-align: center;
}

/* 加载状态样式 */
.loading-question-lib {
  grid-column: 1 / -1;
  padding: 60rpx 0;
}

/* 空状态样式 */
.empty-question-lib {
  grid-column: 1 / -1;
  padding: 60rpx 0;
}  
  /* 科目管理弹窗样式 */
  /* 自定义导航栏右侧内容 */
  .custom-nav-right {
    padding-right: 20rpx;
  }
  
  /* 科目滚动容器 */
  .subject-scroll {
    height: 100%;
    padding-bottom: 20rpx;
  }
  
  /* 科目区域 */
  .subject-section {
    padding: 20rpx;
  }
  
  /* 区域标题 */
  .section-title-text {
    font-size: 32rpx;
    font-weight: bold;
    margin-bottom: 10rpx;
    color: #333;
  }
  
  /* 区域描述 */
  .section-desc {
    font-size: 24rpx;
    color: #999;
    margin-bottom: 20rpx;
  }
  
  /* 我的科目列表 */
  .my-subjects {
    padding: 10rpx 0;
  }
  
  /* 全部科目列表 */
  .all-subjects {
    padding: 10rpx 0;
  }
  
  /* 科目项 */
  .subject-item {
    position: relative;
    display: flex;
    align-items: center;
    padding: 15rpx 40rpx 15rpx 20rpx;
    background-color: #f0f0f0;
    border-radius: 30rpx;
    font-size: 28rpx;
    color: #333;
  }
  
  /* 科目项文本 */
  .subject-item-text {
    white-space: nowrap;
  }
  
  /* 科目项删除按钮 */
  .subject-item-delete {
    position: absolute;
    right: 10rpx;
    top: 50%;
    transform: translateY(-50%);
    width: 30rpx;
    height: 30rpx;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(0, 0, 0, 0.1);
    border-radius: 50%;
    color: #999;
  }
  
  /* 添加科目项 */
  .subject-item-add {
    background-color: #fff;
    border: 1rpx solid #e0e0e0;
    padding: 15rpx 40rpx 15rpx 20rpx;
    transition: all 0.3s ease;
  }
  
  /* 添加科目项悬停效果 */
  .subject-item-add.hover-class {
    background-color: #f5f5f5;
    transform: scale(0.98);
  }
  
  /* 已添加的科目项 */
  .subject-item-added {
    background-color: #e8f5e9;
    border-color: #4caf50;
    color: #4caf50;
  }
  
  /* 科目项添加图标 */
  .subject-item-add-icon {
    position: absolute;
    right: 10rpx;
    top: 50%;
    transform: translateY(-50%);
    width: 30rpx;
    height: 30rpx;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(0, 0, 0, 0.05);
    border-radius: 50%;
    color: #999;
  }
  
  /* 已添加科目项的图标 */
  .subject-item-added .subject-item-add-icon {
    background-color: #4caf50;
    color: #fff;
  }
  
  /* 空科目状态 */
  .empty-subjects {
    padding: 40rpx;
    font-size: 24rpx;
    color: #999;
    text-align: center;
    width: 100%;
  }
  // 数据统计区域 start
  .stats-section {
    margin: 20rpx 0 10rpx 0;
    position: relative; // 为奖杯图标提供定位基准
    
    // 奖杯图标样式（数据统计区域右上角）
    .stats-prize-icon {
      position: absolute;
      top: 10rpx;
      right: 10rpx;
      width: 44rpx;
      height: 44rpx;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      z-index: 10;
    }
    
    .stats-grid {
      display: flex;
      justify-content: space-between;
	    background-color: #ffffff;
      border-radius: 20rpx;
      padding: 15rpx;
      
      gap: 10rpx;
      
      .stat-item {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 16rpx;
        padding: 10rpx;
        border-radius: 12rpx;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        
        &.stat-item-hover {
          transform: translateY(-4rpx);
          box-shadow: 0 8rpx 24rpx rgba(0, 0, 0, 0.1);
        }
        
        .stat-value {
          font-size: 40rpx;
          letter-spacing: 2rpx;
          
          &.primary {
            color: #5B7FE8;
          }
          
          &.success {
            color: #52C988;
          }
          
          &.error {
            color: #FF6B81;
          }
        }
        
        .stat-label {
          font-size: 28rpx;
          letter-spacing: 1rpx;
        }
      }
    }
}
	/* 数据统计区域 end */

/* ==================== 骨架屏样式 ==================== */
.skeleton-container {
  min-height: 100vh;
  background-color: #F8F8F8;
}

.skeleton-nav {
  background-color: #05CA8D;
  padding: 20rpx 30rpx;
}

.skeleton-nav-placeholder {
  width: 100%;
  background-color: #05CA8D;
}

.skeleton-nav-content {
  display: flex;
  align-items: center;
  gap: 20rpx;
}

.skeleton-swiper {
  margin-top: 120rpx;
  padding: 0 30rpx;
}

.skeleton-exam-section {
  padding: 24rpx 30rpx;
}

.skeleton-exam-container {
  display: flex;
  align-items: center;
  gap: 20rpx;
}

.skeleton-stats {
  display: flex;
  gap: 20rpx;
  padding: 0 30rpx;
  margin-bottom: 24rpx;
}

.skeleton-stat-item {
  flex: 1;
  min-width: 0; /* 防止flex子项溢出 */
}

/* 让骨架屏组件填充容器 */
.skeleton-stat-item ::v-deep .tn-skeleton,
.skeleton-menu-item ::v-deep .tn-skeleton {
  width: 100% !important;
}

.skeleton-menu-list {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20rpx;
  padding: 0 30rpx 30rpx;
}

/* PC端骨架屏适配 */
@media (min-width: 1024px) {
  .skeleton-container {
    max-width: 1200px;
    margin: 0 auto;
  }
  
  .skeleton-menu-list {
    grid-template-columns: repeat(auto-fill, minmax(200rpx, 1fr));
  }
}

/* ==================== PC 端适配样式 ==================== */

/* 基础容器样式 - 移动端 */
.template-screen {
  height: 100vh;
  overflow: auto;
  position: relative;
}

/* PC 端容器 */
@media (min-width: 1024px) {
  .template-screen {
    max-width: 1200px;
    margin: 0 auto;
  }
  
  /* PC 端导航栏居中 */
  .custom-nav {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 40rpx;
  }
  
  /* PC 端轮播图优化 */
  .tn-swiper {
    border-radius: 16rpx;
    overflow: hidden;
    margin: 0 40rpx;
  }
  
  /* PC 端考试选择区域 */
  .exam-section {
    padding: 0 40rpx;
  }
  
  /* PC 端数据统计网格布局 */
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24rpx;
    padding: 32rpx;
  }
  
  /* PC 端题库列表 */
  .menu-container {
    padding: 0 40rpx;
  }
  
  .menu-row {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200rpx, 1fr));
    gap: 20rpx;
  }
  
  /* PC 端卡片悬停效果 */
  .menu-box:hover {
    transform: translateY(-4rpx);
    box-shadow: 0 8rpx 24rpx rgba(0, 0, 0, 0.12);
    transition: all 0.3s ease;
  }
  
  /* PC 端统计项悬停 */
  .stat-item:hover {
    transform: translateY(-4rpx);
    box-shadow: 0 8rpx 24rpx rgba(0, 0, 0, 0.1);
  }
  
  /* PC 端科目管理弹窗 */
  .subject-popup-content {
    max-width: 800px;
    margin: 0 auto;
  }
  
  /* PC 端科目列表网格 */
  .subject-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250rpx, 1fr));
    gap: 16rpx;
  }
}

/* 横屏模式优化 */
@media (orientation: landscape) and (max-width: 1024px) {
  .exam-container {
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
  }
  
  .stats-grid {
    display: flex;
    flex-direction: row;
  }
}

</style>
