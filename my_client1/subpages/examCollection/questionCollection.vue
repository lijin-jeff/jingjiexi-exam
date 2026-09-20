<template>
  <view>
    <view class="page tn-safe-area-inset-bottom">
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
          <text class="tn-text-bold tn-text-xl tn-color-white">
            收藏试题
          </text>
        </tn-nav-bar>
      </view>
      <view
        :style="{paddingTop: vuex_custom_bar_height + 'px'}"
      >
        <!-- 科目切换组件 -->
        <subject-tabs-manager
          :scroll-list="scrollList"
          :current="currentSubjectIndex"
          :visible="showSubjectPopup"
          :my-subjects="myQuestionLibList"
          :all-subjects="questionLibList"
          :loading="loadingQuestionLib"
          :title="'编辑科目'"
          :main-color="mainColor"
          :storage-key="'myQuestionLibList'+selectedCategoryUid"
          :auto-save="true"
          :show-toast="true"
          @tab-change="onSubjectTabChange"
          @manager-click="showSubjectManager"
          @close="showSubjectPopup = false"
          @save="(subjects) => { saveSubjectsToStorage(subjects); myQuestionLibList = [...subjects]; updateScrollList() }"
          @add="(subject, subjects) => { saveSubjectsToStorage(subjects); myQuestionLibList = [...subjects]; updateScrollList() }"
          @remove="(index, subjects) => { saveSubjectsToStorage(subjects); myQuestionLibList = [...subjects]; updateScrollList() }"
        />
        <!-- 内容区域 -->
        <view class="tab-content">
          <!-- 已近期收藏和全部收藏统计 -->
          <view class="stats-section-top tn-radius tn-shadow-sm tn-margin-bottom tn-flex tn-flex-row-center tn-flex-col-center">
              <view class="stats-item-success tn-flex-1 tn-padding tn-margin-right-md tn-radius tn-text-center">
                <text class="stats-label tn-text-center tn-text-md">
                  近期收藏：{{ collectStats.recent_collects }}
                </text>
              </view>
              <view class="stats-item-collect tn-flex-1 tn-padding tn-radius  tn-text-center">
                <text class="stats-label tn-text-center tn-text-md">
                  全部收藏：{{ collectStats.total_collects }}
                </text>
              </view>
          </view>
        
          <!-- 顶部滑动tab组件 -->
          <view class="tn-flex tn-flex-row-between tn-flex-col-center tn-margin-bottom-sm tn-bg-white">
            <tn-tabs-swiper 
              ref="tabsSwiper"
              :list="innerTabs" 
              :current="innerCurrentTabIndex"
              :active-color="mainColor"
              :inactive-color="'#666666'"
              class="tn-flex-1 tn-text-lg"
              :swiper-config="{duration: 300, circular: false}"
              @change="onInnerTabChange"
            >
              <template #item="{ item, index }">
                <view class="tn-flex tn-items-center">
                  <text>{{ item.name }}</text>
                  <text v-if="index === 0" class="tn-icon tn-icon-vip tn-margin-left-xs tn-color-orangeyellow"></text>
                </view>
              </template>
            </tn-tabs-swiper>
            <!-- 打印收藏按钮 -->
            <view class="print-btn-container tn-margin-right-sm">
              <text class="tn-icon-vip tn-color-orangeyellow"></text>
              <tn-button
                type="primary"
                size="md"
                padding="0 10rpx"
                shape="round"
                fontSize="24"
                @click="onPrintCollectClick"
              >
                收藏打印
              </tn-button>
              <!-- 排序图标，当innerCurrentTabIndex为0时显示 -->
              <text v-if="innerCurrentTabIndex === 0" class="tn-icon-sort tn-padding-sm" @click="showSortSelect = true"></text>
              <!-- 排序选择器 -->
              <tn-select
                v-model="showSortSelect"
                mode="single"
                :list="sortOptions"
                @confirm="onSortConfirm"
                :searchShow="false"
              />
            </view>
            
          </view>
        
          <!-- 滑动内容区域 -->
          <swiper
            ref="swiper"
            class="inner-swiper tn-bg-white tn-radius tn-shadow-sm"
            :current="innerCurrentTabIndex"
            :duration="300"
            :circular="false"
            :vertical="false"
            @change="onInnerSwiperChange"
            @animationfinish="onInnerSwiperAnimationFinish"
          >
            <!-- 全部收藏 -->
            <swiper-item>
              <view class="type-list-section">
                <!-- 收藏列表 -->
                <scroll-view
                  class="list-container"
                  scroll-y
                  @scrolltolower="onScrollToLower"
                  @refresherrefresh="onRefresherRefresh"
                  :refresher-enabled="true"
                  :refresher-threshold="80"
                  :refresher-triggered="refresherTriggered"
                  refresher-default-style="black"
                >
                  <!-- 加载中状态：覆盖整个列表区域 -->
                  <view v-if="loading['all']" class="loading-container tn-margin-top-lg tn-text-center">
                    <tn-loading
                      mode="spinner"
                      text="加载中..."
                    />
                  </view>
                  
                  <!-- 非加载中状态 -->
                  <view v-else>
                    <!-- 如果有数据，显示列表 -->
                    <view class="time-line__wrap">
                    <tn-time-line v-if="collectData['all'] && collectData['all'].length > 0" class="tn-padding-top">
                      <block v-for="(item, index) in collectData['all']" :key="item.question_uid">
                        <tn-time-line-item>
                          <template slot="node">
                            <view class="time-line-item__node">
                              <view class="time-line-item__node--icon tn-icon tn-icon-empty-favor"></view>
                            </view>
                          </template>
                          <template slot="content">
                            <view class="time-line-item__content tn-flex tn-flex-row">
                              <view class="time-line-item__content__left tn-flex-1">
                                <view class="time-line-item__content__title">{{ item.exam_type_name }}</view>
                                <view class="time-line-item__content__desc">
                                  <text class="tn-text-gray">难度：{{ ['', '简单', '中等', '困难'][item.exam_level || 0] }} | 分值：{{ item.score || 0 }}</text>
                                </view>
                                <view class="time-line-item__content__desc tn-margin-top-xs">
                                  <text class="tn-text-gray">{{ item.title.replace(/<[^>]*>/g, '') }}</text>
                                </view>
                                <view class="time-line-item__content__desc tn-margin-top-xs">
                                  <text class="tn-color-success">正确答案：{{ formatCorrectAnswer(item.correct_answer || '无') }}</text>
                                </view>
                                <view class="time-line-item__content__time tn-margin-top-xs">
                                  收藏时间：{{ item.update_time || item.create_time }}
                                </view>
                              </view>
                              <view class="tn-flex tn-flex-row-center tn-flex-col-center tn-margin-left-xs">
                                <tn-button
                                  :background-color="mainColor"
                                  size="sm"
                                  font-color="tn-color-white"
                                  @click="showActionSheetMenu('all', item.question_uid, index)"
                                >
                                  查看
                                </tn-button>
                              </view> 
                            </view>
                          </template>
                        </tn-time-line-item>
                      </block>
                    </tn-time-line>
                    </view>
                    <!-- 空状态组件，当没有数据时显示 -->
                    <tn-empty 
                      v-if="!collectData['all'] || collectData['all'].length === 0" 
                      mode="list" 
                      text="暂无收藏数据"
                    />
                    
                    <!-- 加载更多：只有当有数据时显示，根据状态显示不同内容 -->
                    <view v-else class="load-more-container tn-margin">
                      <tn-load-more 
                        :status="loading['all'] ? 'loading' : (hasMoreData['all'] ? 'loadmore' : 'nomore')" 
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
                </scroll-view>
              </view>
            </swiper-item>
            <!-- 按题型 -->
            <swiper-item>
              <view class="type-list-section">
                <!-- 题型列表 -->
                <view>
                  <view
                    v-for="(type, index) in typeTabs.slice(1)"
                    :key="index"
                    class="type-item tn-flex tn-flex-row-between tn-flex-col-center tn-radius tn-bg-white tn-shadow-sm"
                    @click="showActionSheetMenu('type', type.value, index)"
                  >
                    <text class="type-name tn-text-lg tn-text-bold">
                      {{ type.name }}
                    </text>
                    <view class="type-count tn-flex tn-flex-row-center tn-items-center">
                      <text class="count-number tn-text-lg tn-text-bold tn-color-warning">
                        {{ type.count || 0 }}
                      </text>
                      <text class="tn-icon-right tn-margin-left-xs tn-color-gray tn-text-xl" />
                    </view>
                  </view>
                </view>
              </view>
            </swiper-item>
        
            <!-- 按章节 -->
            <swiper-item>
              <view class="tab-content chapter-tab-content tn-bg-gray-light">
                <!-- 章节列表 -->
                <scroll-view
                  class="list-container tn-padding"
                  scroll-y
                >
                  <!-- 加载状态 -->
                  <view
                    v-if="loadingChapter && isFirstLoadChapter"
                    class="loading-container tn-margin-top-lg tn-text-center"
                  >
                    <tn-loading
                      mode="spinner"
                      text="加载中..."
                    />
                  </view>
              
                  <!-- 空状态 -->
                  <view
                    v-else-if="!loadingChapter && chapterList.length === 0"
                    class="empty-container tn-margin-top-lg"
                  >
                    <tn-empty
                      mode="list"
                      text="暂无章节数据"
                    />
                  </view>
              
                  <!-- 章节列表 -->
                  <view v-else>
                    <view
                      v-for="(group, groupIndex) in chapterList"
                      :key="groupIndex"
                      class="group-item tn-bg-white tn-radius tn-shadow-sm tn-margin-bottom"
                    >
                      <view
                        class="group-header"
                        :class="{ expanded: group.expanded }"
                      >
                        <view class="group-header-content">
                          <view
                            class="group-title-section"
                            @click="menuExpend(groupIndex)"
                          >
                            <text
                              class="arrow-icon"
                              :class="group.expanded ? 'tn-icon-reduce-circle-fill tn-text-xxl tn-color-blue' : 'tn-icon-add-fill tn-text-xxl tn-color-blue'"
                            />
                            <text class="group-title">
                              {{ group.title }}
                            </text>
                          </view>
                          <!-- 一级章节统计数据显示在标题下方 -->
                          <!-- 收藏统计数据 -->
                          <view class="group-stats-under-title">
                            <view class="stats-item">
                              <text class="stats-icon">⭐</text>
                              <text class="stats-text">
                                收藏数: {{ group.collect_count || 0 }}
                              </text>
                            </view>
                          </view>
                        </view>
                        <tn-button
                          :background-color="mainColor"
                          size="sm"
                          font-color="tn-color-white"
                          :class="group.has_progress ? 'continue-btn' : 'practice-btn'"
                          @click="showActionSheetMenu('chapter', group.uid, groupIndex)"
                        >
                          查看
                        </tn-button>
                      </view>
                  
                      <!-- 子章节列表 -->
                      <view
                        v-if="group.expanded && group.children && group.children.length > 0"
                        class="children-list tn-margin-left-lg tn-strip-top-min"
                      >
                        <view
                          v-for="(item, itemIndex) in group.children"
                          :key="item.uid"
                          class="list-item-vertical"
                        >
                          <div class="item-header">
                            <view class="item-header-content">
                              <view
                                class="item-title-section"
                                :class="{ 'has-children': item.children && item.children.length > 0 }"
                                @click="toggleChildChapter(item)"
                              >
                                <text
                                  v-if="item.children && item.children.length > 0"
                                  class="child-arrow"
                      :class="item.expanded ? 'tn-icon-reduce-circle tn-text-xl tn-color-blue' : 'tn-icon-add-circle tn-text-xl tn-color-blue'"
                                />
                                <text class="item-title">
                                  {{ item.title }}
                                </text>
                              </view>
                              <!-- 收藏统计数据 -->
                              <view class="group-stats-under-title item-stats-under-title">
                                <view class="stats-item">
                                  <text class="stats-text">
                                    收藏数: {{ item.collect_count || 0 }}
                                  </text>
                                </view>
                              </view>
                            </view>
                            <tn-button
                              :background-color="mainColor"
                              size="sm"
                              font-color="tn-color-white"
                              :class="item.has_progress ? 'continue-btn' : 'practice-btn'"
                              @click="showActionSheetMenu('chapter', item.uid, itemIndex)"
                            >
                              查看
                            </tn-button>
                          </div>
                      
                          <!-- 三级子章节列表 -->
                          <view
                            v-if="item.expanded && item.children && item.children.length > 0"
                            class="grandchildren-container"
                          >
                            <view
                              v-for="(subItem, subItemIndex) in item.children"
                              :key="subItemIndex"
                              class="grandchild-item-vertical"
                            >
                              <div class="item-header">
                                <view class="item-header-content">
                                  <view
                                    class="item-title-section"
                                    :class="{ 'has-children': subItem.children && subItem.children.length > 0 }"
                                    @click="toggleChildChapter(subItem)"
                                  >
                                    <text
                                      v-if="subItem.children && subItem.children.length > 0"
                      :class="subItem.expanded ? 'tn-icon-reduce-circle tn-text-xl tn-color-blue' : 'tn-icon-add-circle tn-text-xl tn-color-blue'"
                                    />
                                    <text class="item-title grandchild-title">
                                      {{ subItem.title }}
                                    </text>
                                  </view>
                                  <!-- 收藏统计数据 -->
                                  <view class="group-stats-under-title item-stats-under-title">
                                    <view class="stats-item">
                                      <text class="stats-text">
                                        收藏数: {{ subItem.collect_count || 0 }}
                                      </text>
                                    </view>
                                  </view>
                                </view>
                                <tn-button
                                  :background-color="mainColor"
                                  size="sm"
                                  font-color="tn-color-white"
                                  :class="subItem.has_progress ? 'continue-btn' : 'practice-btn'"
                                  @click="showActionSheetMenu('chapter', subItem.uid, subItemIndex)"
                                >
                                  查看
                                </tn-button>
                              </div>
                            </view>
                          </view>
                        </view>
                      </view>
                    </view>
                  </view>
                </scroll-view>
              </view>
            </swiper-item>
             <view class="bottom-padding" />
          </swiper>
         
          <!-- 操作菜单 -->
          <tn-action-sheet
            v-model="showActionSheet"
            :list="actionSheetList"
            :cancel-btn="true"
            :cancel-text="'取消'"
            @click="onActionSheetClick"
            @close="onActionSheetClose"
          />
        </view>
       
      </view>
    </view>
  </view>
</template>

<script>
import template_page_mixin from '@/libs/mixin/template_page_mixin.js'
import SubjectTabsManager from '@/components/subject-tabs-manager.vue'
import ExamDataAdapter from '@/util/examDataAdapter.js'
export default {
  name: 'QuestionCollection',
  components: {SubjectTabsManager},
  mixins: [template_page_mixin],
  data() {
    return {
      mainColor: getApp().globalData.mainColor,
      // 收藏统计数据
      collectStats: {
        total_collects: 0,
        recent_collects: 0,
      },
      // 科目管理
      showSubjectPopup: false, // 控制科目管理弹窗显示/隐藏
      loadingQuestionLib: false, // 加载状态
      questionLibList: [], // 全部科目列表
      myQuestionLibList: [], // 我的科目列表
      scrollList: [], // 科目切换标签列表
      currentSubjectIndex: 0, // 当前选中的科目索引,
      selectedCategoryUid: uni.getStorageSync('selectedCategoryUid'), // 从本地存储中恢复分类ID
      // 题型统计数据
      typeStats: [],
      // 章节统计数据
      chapterStats: [],
      // 题型映射表，用于快速查找题型名称
      examTypeMap: {},
      currentTabIndex: 0,
      // 分类相关数据
      totalDataCount: {}, // 存储每个tab的总数据量
      tabs: [], // 做题类型选项卡
      // 章节相关数据
      chapterTabs: [
        { name: '全部章节', value: 'all' }
      ],
      activeChapterIndex: 0,
      activeChapter: 'all',
      // 章节列表数据
      chapterList: [],
      loadingChapter: false,
      isFirstLoadChapter: true,
      // 题型分类数据
      typeTabs: [],
      activeTypeIndex: 0,
      activeType: 'all',
      // 内部滑动tab数据
      innerTabs: [
        { name: '全部收藏', value: 'all' },
        { name: '按题型', value: 'type' },
        { name: '按章节', value: 'chapter' }
      ],
      innerCurrentTabIndex: 0,
      // 收藏数据：按做题类型存储
      collectData: {}, // 收藏列表，按做题类型存储
      collectionList: [], // 全部收藏列表
      questionCllectType: '', // 收藏类型
      hasMoreData: {}, // 是否还有更多数据，按做题类型存储
      loading: {}, // 加载状态，按做题类型存储
      pageNo: {}, // 当前页码，按做题类型存储
      refresherTriggered: false, // 下拉刷新触发状态
      queryParams: {
        page_no: 1,
        page_size: 20, // 分页大小
        uid: '', // 题库ID
        exam_type: '', // 题型
        questions_type: '', // 做题类型
        chapter_uid: '', // 章节ID
      },
      // 收藏信息
      collectMsg: '',
      // actionSheet相关数据
      showActionSheet: false,
      selectedCollectItem: null,
      actionSheetList: [
        { text: '查看（背题模式）', color: '#1890ff', fontSize: 28 },
        { text: '练习（学练结合）', color: '#ff4d4f', fontSize: 28 },
        { text: '取消收藏', color: '#ff4d4f', fontSize: 28 },
      ],
      // 排序相关数据
      showSortSelect: false,
      sortOptions: [
        { value: 'update_time', label: '收藏时间' },
        { value: 'exam_type', label: '题型' },
        { value: 'exam_level', label: '难度' }
      ],
      selectedSort: null,
      sortDirection: 'desc', // 默认排序方向
      currentSortField: 'update_time', // 当前排序字段
      ex_questions_type:0 ,
    }
  },
  computed: {
  },
  onLoad(option) {
    // 检查登录状态，未登录时跳转登录页（只检查一次）
    const { isUserLoggedIn } = require('@/util/userStore.js')
    if (!isUserLoggedIn()) {
      console.log('[收藏页面] 未登录，跳转登录页面')
      uni.showToast({
        title: '请先登录',
        icon: 'none'
      })
      setTimeout(() => {
        uni.navigateTo({
          url: '/subpages/user/login'
        })
      }, 500)
      return
    }
    
    // 获取题库ID
    this.queryParams.uid = option.uid || '';
    // 从本地存储中恢复用户选择的科目
    this.restoreUserSubjects();
    // 初始化加载收藏统计数据
    this.fetchCollectStats();
    // 初始化加载收藏数据
    this.fetchCollectData();
    
  },
  onShow() {
    // 每次显示页面时，从本地存储中恢复用户选择的科目
    this.restoreUserSubjects();
  },
  methods: {
    // 返回上一页
    goBack() {
      uni.navigateBack();
    },
    // 选项卡切换事件
		onTabChange(index) {
			this.currentTabIndex = index;
			const type = this.tabs[index].value;
			// 如果该类型还没有数据，则加载
		},
    // 监听swiper切换
    onSwiperChange(e) {
      const index = e.detail.current;
      this.currentTabIndex = index;
    },
    // 内部选项卡切换事件
    onInnerTabChange(index) {
      this.innerCurrentTabIndex = index;
      // 切换标签时重置所有查询参数，避免参数交叉影响
      this.resetQueryParams();
      
      // 通知swiper组件切换到对应的索引
      if (this.$refs.swiper) {
        this.$refs.swiper.setCurrent(index, 300);
      }
      
      // 如果切换到全部收藏标签，加载全部收藏数据
      if (index === 0) {
        this.fetchCollectData('all', false);
      }
    },
    // 监听内部swiper切换
    onInnerSwiperChange(e) {
      const index = e.detail.current;
      this.innerCurrentTabIndex = index;
      // 切换标签时重置所有查询参数，避免参数交叉影响
      this.resetQueryParams();
    },
    // 内部swiper动画结束事件
    onInnerSwiperAnimationFinish(e) {
      const index = e.detail.current;
      // 根据Tuniao UI文档要求，必须将组件的current参数设置为animationfinish中的返回值
      this.innerCurrentTabIndex = index;
      // 切换标签时重置所有查询参数，避免参数交叉影响
      this.resetQueryParams();
      
      // 如果切换到全部收藏标签，加载全部收藏数据
      if (index === 0) {
        this.fetchCollectData('all', false);
      }
    },
    // 重置查询参数
    resetQueryParams() {
      // 重置所有查询条件
      this.queryParams.chapter_uid = '';
      this.queryParams.exam_type = '';
      this.queryParams.questions_type = '';
    },
    
    /**
     * scroll-view滚动到底部事件
     */
    onScrollToLower() {
      // 处理全部收藏列表的下拉加载更多
      this.fetchCollectData('all', true);
    },
    // 下拉刷新
    onRefresherRefresh() {
      // 设置刷新触发状态
      this.refresherTriggered = true;
      // 重置页码和状态
      this.pageNo['all'] = 1;
      this.hasMoreData['all'] = true;
      // 重新加载数据
      this.fetchCollectData('all', false);
    },
    // 根据选项卡类型获取收藏列表
    getTabCollectList(type) {
      // 如果是"全部"类型，直接返回专门为"all"类型存储的数据
      // 不再合并其他标签的数据，以确保正确显示API返回的所有数据
      return this.collectData[type] || [];
    },

    // 显示操作菜单
    showActionSheetMenu(active, uid, itemIndex) {
      // 重置选中项
      this.selectedCollectItem = null;
      // 重置操作菜单禁用状态
      this.actionSheetList.forEach(item => item.disabled = false);
      let questionUids = '';
      this.questionCllectType = active;
      if (active === 'chapter') {
        // 根据章节ID获取对应的收藏ID
        // 从collectStats中获取直接统计数据
        const chapterStat = this.collectStats.chapter_stats?.find(item => item.chapter_uid === uid);
        questionUids = chapterStat?.question_uid || '';
        
        // 从chapterList中获取章节及其所有子章节的收藏ID
        const chapterFromList = this.findChapterInList(this.chapterList, uid);
        if (chapterFromList) {
          // 获取该章节及其所有子章节的收藏ID
          const allQuestionUids = this.getAllQuestionUidsFromChapter(chapterFromList);
          if (allQuestionUids.length > 0) {
            questionUids = allQuestionUids.join(',');
          }
        }
        this.actionSheetList[2].disabled = true;
      } else if (active === 'type'){
        // 根据题型ID获取对应的收藏ID
        const typeStat = this.collectStats.type_stats?.find(item => item.exam_type === uid);
        questionUids = typeStat?.question_uid || '';
        this.actionSheetList[2].disabled = true;
      }else{
        questionUids =  uid ;
        // 禁用操作菜单第二项（练习（学练结合））
        this.actionSheetList[1].disabled = true;
      }
      
      // 检查是否有收藏数据
      if (!questionUids) {
        uni.showToast({
          title: '暂无收藏数据',
          icon: 'none'
        });
        return;
      }
      
      this.selectedCollectItem = questionUids;

      // 显示操作菜单
      this.showActionSheet = true;
    },
    
    // 格式化收藏ID，处理各种复杂格式
    formatCollectUids(uid) {
      if (!uid) return '';
      // 如果是数组，处理数组元素
      if (Array.isArray(uid)) {
        // 处理数组中每个元素，去除URL编码、引号和特殊字符
        const processedUids = uid.map(item => {
          // 转换为字符串
          let strItem = String(item);
          // 解码URL编码
          strItem = decodeURIComponent(strItem);
          // 去除引号、+号和空白字符
          strItem = strItem.replace(/["'+\s]/g, '');
          // 去除前后的空白字符
          return strItem.trim();
        }).filter(item => item.length > 0);
        // 去重并转换为逗号分隔字符串
        return [...new Set(processedUids)].join(',');
      } else if (typeof uid === 'string') {
        // 如果是字符串，处理类似数组的字符串格式
        if (uid.startsWith('[') && uid.endsWith(']')) {
          // 解码URL编码
          let decoded = decodeURIComponent(uid);
          // 去除前后的方括号
          decoded = decoded.slice(1, -1);
          // 分割为数组
          const uidArray = decoded.split(',');
          // 处理每个元素
          const processedUids = uidArray.map(item => {
            // 去除引号、+号和空白字符
            return item.replace(/["'+\s]/g, '').trim();
          }).filter(item => item.length > 0);
          // 去重并转换为逗号分隔字符串
          return [...new Set(processedUids)].join(',');
        } else {
          // 普通字符串，直接返回
          return uid;
        }
      } else {
        // 其他类型，转换为字符串
        return String(uid);
      }
    },
    
    // 在章节列表中查找指定ID的章节
    findChapterInList(chapters, chapterId) {
      for (let i = 0; i < chapters.length; i++) {
        const chapter = chapters[i];
        if (chapter.uid === chapterId) {
          return chapter;
        }
        if (chapter.children && chapter.children.length > 0) {
          const found = this.findChapterInList(chapter.children, chapterId);
          if (found) {
            return found;
          }
        }
      }
      return null;
    },
    
    // 获取章节及其所有子章节的收藏ID
    getAllQuestionUidsFromChapter(chapter) {
      const questionUids = [];
      
      // 从chapterStats中获取当前章节的直接收藏ID
      const chapterStat = this.chapterStats.find(item => item.chapter_uid === chapter.uid);
      if (chapterStat?.question_uid) {
        const directUids = this.formatCollectUids(chapterStat.question_uid).split(',');
        questionUids.push(...directUids.filter(uid => uid.trim() !== ''));
      }
      
      // 递归处理子章节
      if (chapter.children && chapter.children.length > 0) {
        for (let i = 0; i < chapter.children.length; i++) {
          const childUids = this.getAllQuestionUidsFromChapter(chapter.children[i]);
          questionUids.push(...childUids);
        }
      }
      
      // 去重
      return [...new Set(questionUids)];
    },
    // 章节变化事件
    onChapterChange(param) {
      let chapterValue = 'all';
      if (typeof param === 'object' && param !== null) {
        chapterValue = param.value;
      }
      this.activeChapter = chapterValue;
      // 设置章节ID，重置其他查询条件
      this.queryParams.chapter_uid = chapterValue === 'all' ? '' : chapterValue;
      // 重置其他查询条件
      this.queryParams.exam_type = '';
      this.queryParams.questions_type = '';
      // 重置分页和数据：为每个做题类型重置
      Object.keys(this.collectData).forEach(type => {
        this.pageNo[type] = 1;
        this.collectData[type] = [];
        this.hasMoreData[type] = true;
      });
      // 更新收藏统计数据
      this.fetchCollectStats();
      // 获取当前做题类型的收藏数据
    },

    // 展开/折叠章节
    menuExpend(groupIndex) {
      // 获取当前点击的章节
      const currentChapter = this.chapterList[groupIndex];
      // 遍历所有一级章节
      for (let i = 0; i < this.chapterList.length; i++) {
        if (i === groupIndex) {
          // 如果是当前点击的章节，切换其展开状态
          this.chapterList[i].expanded = !currentChapter.expanded;
        } else {
          // 如果是其他一级章节，将它们的展开状态设置为false
          this.chapterList[i].expanded = false;
        }
      }
    },
    // 切换子章节展开/折叠状态
    toggleChildChapter(item) {
      if (item.children && item.children.length > 0) {
        item.expanded = !item.expanded;
      }
    },


    // 获取收藏统计数据
    fetchCollectStats() {
      try {
        
        this.$api.apiCollectStats({
          uid: this.queryParams.uid,
        }).then(res => {
          if (res.code === 1) {
            this.collectStats = res.data || {
              total_collects: 0,
              recent_collects: 0,
              type_stats: [],
              chapter_stats: []
            };
            this.typeStats = res.data.type_stats || [];
            this.chapterStats = res.data.chapter_stats || [];
            
            // 更新题型选项卡的收藏数量
            this.updateTypeTabsCount();
            // 更新章节列表的收藏统计
            this.updateChapterStats();
            
            // 构建全部收藏列表
            this.buildCollectionList();
          } else {
            console.error('获取收藏统计数据失败:', res.msg || '未知错误');
          }
        }).catch(err => {
          console.error('获取收藏统计数据请求失败:', err);
        });
      } catch (err) {
        console.error('获取收藏统计数据异常:', err);
      }
    },
    // 构建全部收藏列表
    buildCollectionList() {
      // 全部收藏列表包含按题型和按章节的收藏
      const allCollections = [];
      
      // 添加按题型的收藏
      if (this.typeStats && this.typeStats.length > 0) {
        this.typeStats.forEach(typeStat => {
          allCollections.push({
            id: `type_${typeStat.exam_type}`,
            name: typeStat.exam_type_name,
            count: typeStat.total_collects,
            type: 'type',
            value: typeStat.exam_type,
            question_uid: typeStat.question_uid
          });
        });
      }
      
      // 添加按章节的收藏（只添加一级章节）
      if (this.chapterList && this.chapterList.length > 0) {
        this.chapterList.forEach(chapter => {
          allCollections.push({
            id: `chapter_${chapter.uid}`,
            name: chapter.title,
            count: chapter.collect_count,
            type: 'chapter',
            value: chapter.uid,
            question_uid: this.getAllQuestionUidsFromChapter(chapter).join(',')
          });
        });
      }
      
      this.collectionList = allCollections;
    },
    // 获取收藏数据
    fetchCollectData(type = 'all', isLoadMore = false) {
      // 确保type存在
      type = type || 'all';
      

      
      // 初始化当前类型的数据结构（如果不存在）
      if (!this.collectData[type]) {
        this.$set(this.collectData, type, []);
        this.$set(this.hasMoreData, type, true);
        this.$set(this.loading, type, false);
        this.$set(this.pageNo, type, 1);
      }
      
      // 避免重复请求
      if (this.loading[type] || !this.hasMoreData[type]) return;

      // 设置加载状态
      this.loading[type] = true;
      
      // 重置页码
      const currentPageNo = isLoadMore ? this.pageNo[type] : 1;

      // 显示加载提示
      if (!isLoadMore) {
        uni.showLoading({
          title: '努力加载中...',
          icon: 'none'
        });
      }

      try {
        // 构建API请求参数，仅包含必要参数
        const apiParams = {
          uid: this.queryParams.uid,
          page_no: currentPageNo,
          page_size: this.queryParams.page_size,
          mode: 'learnPractice',
          question_count: 1000,
          selecte_type: 1,
          exam_type: 0,
          practice_mode: 1,
          random_type: 2,
          // 添加排序参数
          order_field: this.currentSortField,
          order_direction: this.sortDirection
        };

        // 根据查询条件动态添加参数
        // 做题类型参数：只有当不是'all'时才添加
        if (type !== 'all') {
          apiParams.questions_type = type;
        }

        
        // 题型参数：只有当有值时才添加
        if (this.queryParams.exam_type && this.queryParams.exam_type !== '') {
          apiParams.exam_type = this.queryParams.exam_type;
        }

        // 章节参数：只有当不是'all'时才添加
        if (this.queryParams.chapter_uid && this.queryParams.chapter_uid !== 'all') {
          apiParams.chapter_uid = this.queryParams.chapter_uid;
        }

        // 调用API获取收藏数据
        this.$api.apiQuestionCollectionList(apiParams).then(res => {
          if (res.code === 1) {
            const result = res.data || {};
            const resultData = result.list || [];
            // 处理返回数据
            const processedData = resultData.map(item => ({
              ...item,
              eliminated_status: item.eliminated_status || 0 // 使用返回的收藏状态，默认0
            }));
            
            // 更新收藏列表
      if (isLoadMore) {
        // 加载更多，追加数据
        if (!this.collectData[type]) {
          this.$set(this.collectData, type, []);
        }
        this.collectData[type].push(...processedData);
      } else {
        // 重新加载，替换数据
        this.$set(this.collectData, type, processedData);
      }
            
            // 判断是否还有更多数据
            this.hasMoreData[type] = resultData.length >= this.queryParams.page_size;
            // 更新页码，用于下次加载更多
            if (this.hasMoreData[type]) {
              this.pageNo[type] += 1;
            }
            
          } else {
            // API返回错误
            uni.showToast({
              title: res.msg || '获取收藏数据失败',
              icon: 'none'
            });
            console.error('获取收藏数据失败:', res.msg || '未知错误');
          }
        }).catch(err => {
          // 请求失败
          uni.showToast({
            title: '网络错误，请重试',
            icon: 'none'
          });
          console.error('获取收藏数据请求失败:', err);
        }).finally(() => {
          // 无论成功失败，都关闭加载状态
          this.loading[type] = false;
          uni.hideLoading();
          // 结束下拉刷新动画
          this.refresherTriggered = false;
        });
      } catch (err) {
        // 代码执行异常
        uni.showToast({
          title: '获取收藏数据异常',
          icon: 'none'
        });
        console.error('获取收藏数据异常:', err);
        // 确保关闭加载状态
        this.loading[type] = false;
        uni.hideLoading();
        // 结束下拉刷新动画
        this.refresherTriggered = false;
      }
    },
    // 操作菜单点击事件
    onActionSheetClick(index) {
      console.log('[questionCollection] index:', index)
      if(index === 0 || index === 1){
        // 收藏（背题模式）
        this.showActionSheet = false
          this.ex_questions_type =  0 
          console.log('[questionCollection] this.questionCllectType:', this.questionCllectType)
          if (this.questionCllectType !== 'all') {
            this.ex_questions_type = 6;
          }
        // 准备传递给commonQuestion.vue的参数
        const examSettings = {
          uid: this.queryParams.uid,
          analysis_quid: this.selectedCollectItem,
          question_count: this.selectedCollectItem.split(',').length,
          questions_collect: 1,
          questionCllectType: this.questionCllectType || '',
          questions_type: this.ex_questions_type, // 6表示收藏练习（API需要此参数）

        }
        if (index === 0) {
          // 收藏（背题）
          examSettings['mode'] = 'reviewOnly'
          examSettings['practice_mode'] = 3//背题模式
        }else if(index===1){
          examSettings['mode'] = 'learnPractice'
          examSettings['practice_mode'] = 1//学练结合
      }
        // 重置选中的收藏项
        this.selectedCollectItem = null
        
        // 直接跳转到答题页面，不需要经过设置步骤
        uni.navigateTo({
          url: '/subpages/exam/components/commonQuestion?examSettings=' + encodeURIComponent(JSON.stringify(examSettings))
        })
      }else if(index === 2){
        // 取消收藏
        console.log('[questionCollection] this.selectedCollectItem:', this.selectedCollectItem)
        this.$api.apiQuestionCollection({
          library_uid: this.queryParams.uid,
          question_uid: this.selectedCollectItem,
          action: 2
        }).then(res => {
          if (res.code === 1) {
            uni.showToast({
              title: '取消收藏成功',
              icon: 'none'
            });
            
            // 动态从列表中移除对应的数据，而不是重新拉取
            // 1. 解析被取消收藏的question_uid
            const questionUids = this.selectedCollectItem.split(',');
            
            // 2. 遍历所有collectData类型，从每个类型的列表中移除对应的数据
            Object.keys(this.collectData).forEach(type => {
              if (this.collectData[type] && this.collectData[type].length > 0) {
                // 过滤掉被取消收藏的数据
                this.collectData[type] = this.collectData[type].filter(item => 
                  !questionUids.includes(item.question_uid)
                );
              }
            });
            
            // 3. 更新收藏统计数据
            this.fetchCollectStats();
            
            // 4. 重置hasMoreData，确保后续可以继续加载更多数据
            Object.keys(this.hasMoreData).forEach(type => {
              this.hasMoreData[type] = true;
            });
          } else {
            uni.showToast({
              title: res.msg || '取消收藏失败',
              icon: 'none'
            });
          }
        }).catch(err => {
          uni.showToast({
            title: '取消收藏请求失败',
            icon: 'none'
          });
          console.error('取消收藏请求失败:', err);
        });
        return
      }
    },
    // 操作菜单关闭事件
    onActionSheetClose() {
      // 重置选中的收藏项 
      this.selectedCollectItem = null;
    },  
    // 排序确认事件
    onSortConfirm(e) {
      // 获取选中的排序字段
      this.currentSortField = e[0].value;
      // 保存当前选中的排序条件
      this.selectedSort = e[0];
      // 关闭选择器
      this.showSortSelect = false;
      // 重置分页参数
      this.pageNo['all'] = 1;
      this.hasMoreData['all'] = true;
      // 重新加载数据
      this.fetchCollectData('all', false);
      console.log('选中的排序条件：', { field: this.currentSortField, direction: this.sortDirection });
    },  
    // 更新题型选项卡的收藏数量
    updateTypeTabsCount() {
      // 如果API返回了题型统计数据，直接使用这些数据构建题型列表
      if (this.typeStats && this.typeStats.length > 0) {
        // 创建新的题型列表
        const newTypeTabs = [{ name: '全部题型', value: 'all', count: this.collectStats.total_collects }];
        
        // 添加各个题型到列表
        this.typeStats.forEach(stat => {
          newTypeTabs.push({
            name: stat.exam_type_name,
            value: stat.exam_type,
            count: stat.total_collects
          });
          
          // 为题型创建映射，便于后续使用
          this.examTypeMap[stat.exam_type] = stat.exam_type_name;
        });
        
        // 更新题型列表
        this.typeTabs = newTypeTabs;
        return;
      }
      
      // 如果typeTabs为空，等待题型数据加载完成后再更新
      if (!this.typeTabs || this.typeTabs.length === 0) return;
      
      // 创建题型统计数据映射，便于快速查找
      const typeStatsMap = {};
      this.typeStats.forEach(stat => {
        typeStatsMap[stat.exam_type] = stat;
      });
      
      // 更新题型选项卡的收藏数量
      this.typeTabs.forEach((tab, index) => {
        if (tab.value === 'all') {
          // 全部题型的数量为总收藏数
          this.$set(this.typeTabs[index], 'count', this.collectStats.total_collects);
        } else {
          // 其他题型的数量为对应题型的收藏数
          const stat = typeStatsMap[tab.value] || { total_collects: 0 };
          this.$set(this.typeTabs[index], 'count', stat.total_collects);
        }
      });
    },
    
    // 更新章节列表的收藏统计
    updateChapterStats() {
      // 如果API返回了章节统计数据，直接使用这些数据构建章节树
      if (this.chapterStats && this.chapterStats.length > 0) {
        this.buildChapterTree();
        // 构建章节树后，计算上级章节汇总数据
        this.calculateParentChapterStats(this.chapterList);
        return;
      }
      
      // 如果没有章节统计数据，重置章节列表
      this.chapterList = [];
      
      // 如果chapterList为空，等待章节数据加载完成后再更新
      if (!this.chapterList || this.chapterList.length === 0) return;
      
      // 创建章节统计数据映射，便于快速查找
      const chapterStatsMap = {};
      this.chapterStats.forEach(stat => {
        chapterStatsMap[stat.chapter_uid] = stat;
      });
      
      // 递归更新章节统计数据
      const updateChapter = (chapters) => {
        if (!chapters || chapters.length === 0) return;
        
        chapters.forEach((chapter, index) => {
          // 更新当前章节的收藏统计
          const stat = chapterStatsMap[chapter.uid] || { total_collects: 0, recent_collects: 0 };
          this.$set(chapters[index], 'collect_count', stat.total_collects);
          this.$set(chapters[index], 'recent_count', stat.recent_collects);
          
          // 递归更新子章节
          if (chapter.children && chapter.children.length > 0) {
            updateChapter(chapter.children);
          }
        });
      };
      
      // 更新章节列表
      updateChapter(this.chapterList);
      // 更新完成后，计算上级章节汇总数据
      this.calculateParentChapterStats(this.chapterList);
    },
    
    // 递归计算上级章节的汇总数据
    calculateParentChapterStats(chapters) {
      if (!chapters || chapters.length === 0) return;
      
      // 从最底层子章节开始计算，逐级向上汇总
      for (let i = 0; i < chapters.length; i++) {
        const chapter = chapters[i];
        
        // 递归处理子章节
        if (chapter.children && chapter.children.length > 0) {
          this.calculateParentChapterStats(chapter.children);
          
          // 计算当前章节的汇总数据
          let totalCollects = 0;
          let totalEliminated = 0;
          
          // 遍历所有直接子章节，汇总收藏数
          for (let j = 0; j < chapter.children.length; j++) {
            const child = chapter.children[j];
            totalCollects += parseInt(child.collect_count || 0);
            totalEliminated += parseInt(child.eliminated_count || 0);
          }
          
          // 更新当前章节的汇总数据
          this.$set(chapter, 'collect_count', totalCollects);
          this.$set(chapter, 'eliminated_count', totalEliminated);
          
        }
      }
    },
    
    // 根据API返回的chapter_stats构建章节树
    buildChapterTree() {
      // 如果没有章节统计数据，直接返回
      if (!this.chapterStats || this.chapterStats.length === 0) return;
      
      // 创建章节映射，便于快速查找
      const chapterMap = {};
      // 创建根章节数组
      const rootChapters = [];
      
      // 首先将所有章节添加到映射中
      this.chapterStats.forEach(chapter => {
        chapterMap[chapter.chapter_uid] = {
          uid: chapter.chapter_uid,
          parent_uid: chapter.chapter_parent_uid,
          title: chapter.chapter_name,
          children: [],
          expanded: false,
          collect_count: chapter.total_collects || 0,
          recent_count: chapter.recent_collects || 0,
        };
      });
      
      // 确保所有章节都有必要的统计属性，处理边界情况
      for (const key in chapterMap) {
        const chapter = chapterMap[key];
        // 初始化可能缺失的属性
        chapter.collect_count = chapter.collect_count || 0;
        chapter.eliminated_count = chapter.eliminated_count || 0;
        chapter.children = chapter.children || [];
      }
      
      // 然后构建章节树
      this.chapterStats.forEach(chapter => {
        const currentChapter = chapterMap[chapter.chapter_uid];
        if (chapter.chapter_parent_uid === '0' || !chapterMap[chapter.chapter_parent_uid]) {
          // 如果是根章节，直接添加到根章节数组
          rootChapters.push(currentChapter);
        } else {
          // 否则添加到父章节的children数组中
          chapterMap[chapter.chapter_parent_uid].children.push(currentChapter);
        }
      });
      
      // 更新章节列表
      this.chapterList = rootChapters;
    },
    
    // 题型项点击事件
    onTypeItemClick(type) {
      // 设置当前题型
      this.activeType = type.value;
      this.queryParams.exam_type = type.value === 'all' ? '' : type.value;
      
    },
    
    // 章节项点击事件
    onChapterItemClick(chapter) {
      // 设置当前章节
      this.activeChapter = chapter.uid;
      this.queryParams.chapter_uid = chapter.uid;

    },

    // 格式化正确答案显示（使用数据适配器统一处理）
    formatCorrectAnswer(answer) {
      if (!answer) return '无';
      try {
        // 使用适配器统一解析答案
        const answerArray = ExamDataAdapter.normalizeAnswer(answer);
        // 如果是数组，转换为逗号分隔的字符串
        return answerArray.length > 0 ? answerArray.join(', ') : '无';
      } catch (e) {
        console.error('formatCorrectAnswer error:', e, answer);
        // 如果解析失败，返回原始值
        return String(answer);
      }
    },
    
    
    // 打印收藏按钮点击事件
    onPrintCollectClick() {
      uni.showToast({ title: '正在开发中', icon: 'none' });
    },
    
    // 科目管理相关方法
    // 显示科目管理弹窗
    showSubjectManager() {
      // 加载科目列表数据
      this.loadQuestionLibList();
      // 显示弹窗
      this.showSubjectPopup = true;
    },
    
    // 科目切换事件处理
    onSubjectTabChange(index) {
      this.currentSubjectIndex = index;
      // 切换科目时，根据新选中的科目更新题库ID
      if (this.myQuestionLibList.length > 0 && index < this.myQuestionLibList.length) {
        this.queryParams.uid = this.myQuestionLibList[index].id || this.myQuestionLibList[index].uid;
      }
      // 重置收藏数据状态，确保切换科目后能重新加载数据
      // 优先重置'all'类型，因为fetchCollectData默认使用'all'类型
      this.collectData['all'] = [];
      this.hasMoreData['all'] = true;
      this.loading['all'] = false;
      this.pageNo['all'] = 1;
      
      // 如果typeTabs已初始化，重置所有类型的数据
      if (this.typeTabs && this.typeTabs.length > 0) {
        this.typeTabs.forEach(tab => {
          const type = tab.value;
          if (type !== 'all') {
            this.collectData[type] = [];
            this.hasMoreData[type] = true;
            this.loading[type] = false;
            this.pageNo[type] = 1;
          }
        });
      }
      // 初始化加载收藏数据
      this.fetchCollectData();
      // 更新收藏统计数据
      this.fetchCollectStats();
    },
    
    // 从本地存储中恢复用户选择的科目
    restoreUserSubjects() {
      // 获取当前选中的分类ID
      const selectedCategoryUid = uni.getStorageSync('selectedCategoryUid');
      if (selectedCategoryUid) {
        // 从本地存储中获取用户选择的科目
        const savedQuestionLibList = uni.getStorageSync('myQuestionLibList' + selectedCategoryUid);
        if (savedQuestionLibList && Array.isArray(savedQuestionLibList) && savedQuestionLibList.length > 0) {
          // 更新我的科目列表
          this.myQuestionLibList = savedQuestionLibList;
          // 更新科目切换标签列表
          this.updateScrollList();
          // 如果有科目，更新题库ID
          if (this.myQuestionLibList[this.currentSubjectIndex]) {
            this.queryParams.uid = this.myQuestionLibList[this.currentSubjectIndex].id || this.myQuestionLibList[this.currentSubjectIndex].uid;
            // 恢复科目后，重新获取收藏统计和收藏数据
            this.fetchCollectStats();
            this.fetchCollectData();
          }
        } else {
          // 如果本地没有保存的科目，加载默认科目列表
          this.loadQuestionLibList();
        }
      } else {
        // 如果没有选中的分类ID，加载默认科目列表
        this.loadQuestionLibList();
      }
    },
    
    // 保存用户选择的科目到本地存储
    saveSubjectsToStorage(subjects) {
      // 获取当前选中的分类ID
      let selectedCategoryUid = uni.getStorageSync('selectedCategoryUid');
      // 如果没有分类ID，使用默认值或从其他地方获取
      if (!selectedCategoryUid) {
        // 尝试从当前选中的科目ID获取
        selectedCategoryUid = this.myQuestionLibList[0]?.id || this.myQuestionLibList[0]?.uid || 'default';
        // 保存分类ID到本地存储
        uni.setStorageSync('selectedCategoryUid', selectedCategoryUid);
      }
      // 保存用户选择的科目到本地存储
      uni.setStorageSync('myQuestionLibList' + selectedCategoryUid, subjects);
    },
    
    // 加载科目列表
    loadQuestionLibList() {
      // 避免重复请求
      if (this.loadingQuestionLib) {
        return;
      }
      // 加载数据
      this.loadingQuestionLib = true;
      
      // 调用API获取科目列表
      this.$api.apiQuestionLib({
        category_uid: this.selectedCategoryUid,
        is_show: 1,
        page_no: 1,
        page_size: 20
      }).then(res => {
        if (res.code === 1) {
          this.questionLibList = res.data.lists || [];
          // 只有当myQuestionLibList为空时才设置默认科目，否则保留用户选择的科目
          if (this.myQuestionLibList.length === 0) {
            // 设置默认科目为前2个
            this.myQuestionLibList = res.data.lists.slice(0, 2) || [];
            // 更新科目切换标签列表
            this.updateScrollList();
          }
        } else {
          uni.showToast({
            title: res.msg || '获取科目列表失败',
            icon: 'none'
          });
        }
      }).catch(err => {
        console.error('获取科目列表失败:', err);
        uni.showToast({
          title: '网络错误，请重试',
          icon: 'none'
        });
      }).finally(() => {
        this.loadingQuestionLib = false;
      });
    },
    
    // 更新科目切换标签列表
    updateScrollList() {
      // 根据myQuestionLibList生成scrollList
      this.scrollList = this.myQuestionLibList.map(subject => ({
        name: subject.name
      }));
      
      // 如果scrollList为空，添加一个默认项
      if (this.scrollList.length === 0) {
        this.scrollList = [{ name: '无考试科目' }];
      }
      
      // 确保currentSubjectIndex索引在有效范围内
      if (this.currentSubjectIndex >= this.scrollList.length) {
        this.currentSubjectIndex = Math.max(0, this.scrollList.length - 1);
        // 如果有科目，更新题库ID
        if (this.myQuestionLibList[this.currentSubjectIndex]) {
          this.queryParams.uid = this.myQuestionLibList[this.currentSubjectIndex].id || this.myQuestionLibList[this.currentSubjectIndex].uid;
        }
      }
    }
  }
}
</script>

<style scoped lang="scss">
	@import "@/scss/custom_nav_bar.scss";
/* 基本样式 */
page {
  height: 100%;
  background-color: #f5f7fa;
}

/* 页面容器样式 */
.page {
  height: 100vh;
  display: flex;
  flex-direction: column;
  background-color: #f5f7fa;
}

/* 内容容器样式 */
.page > view:nth-child(2) {
  flex: 1;
  display: flex;
  flex-direction: column;
}

/* 选项卡内容区域 */
.tab-content {
  flex: 1;
  display: flex;
  flex-direction: column;
}

::v-deep .exam-swiper {
  min-height: calc(100vh - 260rpx);
  display: flex;
  overflow: hidden;
}

/* 滑动内容区域样式 */
.inner-swiper {
  flex: 1;
}

/* 题型列表区域样式 */
.type-list-section {
  height: 100%;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}


/* 底部内边距，防止内容被遮挡 */
.bottom-padding {
  height: 100rpx;
}

/* 滚动容器样式 */
.list-container {
  flex: 1;
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
}

/* 分类选择器样式 */
.category-selector {
  background-color: #fff;
  border-radius: 16rpx;
  margin-left: -16rpx;
  box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.08);
}

/* 收藏列表样式 */
.exam-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  padding-bottom: 20rpx;
  min-height: 300rpx;
}

	.exam-ul {
		width: 100%;
		max-height: 100%;
		overflow-y: auto;
		padding: 0 20rpx;
		box-sizing: border-box;

		.exam-li {
			background: #fff;
			border-radius: 16rpx;
			padding: 24rpx;
			margin-bottom: 20rpx;
			box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.08);
			position: relative;
			transition: all 0.3s ease;

			&:hover {
				box-shadow: 0 6rpx 24rpx rgba(0, 0, 0, 0.12);
				transform: translateY(-2rpx);
			}

			.txt {
				justify-content: space-between;
				align-items: flex-start;
			}

			/* 题型标签样式 */
			.exam-type-tag {
				background-color: #f0f9eb;
				color: #67c23a;
				padding: 6rpx 12rpx;
				border-radius: 8rpx;
				font-size: 24rpx;
				font-weight: 500;
				display: inline-block;
				margin-bottom: 12rpx;
			}

			/* 题目内容样式 */
			.exam-content {
				font-size: 28rpx;
				color: #333;
				line-height: 42rpx;
				margin-bottom: 16rpx;
				word-break: break-word;
			}

			/* 元信息样式 */
			.exam-meta {
				font-size: 24rpx;
				color: #909399;
				margin-bottom: 16rpx;
				display: flex;
				gap: 20rpx;
			}

			/* 按钮容器样式 */
			.btn-container {
				display: flex;
				gap: 12rpx;
				justify-content: flex-end;
				align-items: center;
				margin-top: 16rpx;
			}

			/* 单个按钮样式 */
			.btn-item {
				text-align: center;
			}
		}

	}

	.fs-16 {
		font-size: 30rpx;
	}

	/* 移除不再使用的样式 */
	.exam-list-btn {
		display: none;
	}

/* 空状态样式优化 */
.empty-container {
  margin-top: 40rpx !important;
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}


/* 章节列表样式 */
.chapter-tab-content {
  background-color: #f5f7fa;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.list-container {
  box-sizing: border-box;
  flex: 1;
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
}

.group-item {
  margin-bottom: 20rpx;
  border-radius: 12rpx;
  overflow: hidden;
  background: #ffffff;
  box-shadow: 0 2rpx 12rpx rgba(0, 0, 0, 0.06);
}

.group-header {
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
  gap: 20rpx;
  background-color: #ffffff;
  padding: 20rpx;
  border-radius: 12rpx;
  cursor: pointer;
}

/* 分组头部内容区 - 包含标题和统计 */
.group-header-content {
  display: flex;
  flex-direction: column;
  flex: 1;
  gap: 8rpx;
  min-width: 0; /* 确保文本省略能正常工作 */
  overflow: hidden; /* 防止内容溢出 */
}

/* 分组标题区域 */
.group-title-section {
  display: flex;
  align-items: center;
  flex: 1;
  min-width: 0; /* 确保文本省略能正常工作 */
  overflow: hidden; /* 防止内容溢出 */
}

/* 箭头图标 */
.arrow-icon {
  color: #909399;
  margin:0 20rpx 0 0;
  width: 44rpx;
  height: 44rpx;
  text-align: center;
  transition: all 0.3s ease;
  font-size: 44rpx;
}

.arrow-icon.tn-icon-up-triangle {
  transform: rotate(180deg);
}

.group-header.expanded .arrow-icon {
  transform: rotate(0deg);
}

/* 分组标题 */
.group-title {
  color: #303133;
  font-size: 30rpx;
  line-height: 42rpx;
  flex: 1;
}

/* 一级章节标题下方的统计数据 */
.group-stats-under-title {
  display: flex;
  align-items: center;
  gap: 24rpx;
  width: 100%;
  flex-wrap: wrap;
}

/* 统计项样式 */
.stats-item {
  display: flex;
  align-items: center;
  gap: 6rpx;
  padding: 0 16rpx;
  background: linear-gradient(135deg, #f5f7fa 0%, #fafbfc 100%);
  border-radius: 20rpx;
  transition: all 0.3s ease;
  flex: 1;
  text-align: center;
}

.stats-item.hover-class {
  transform: scale(0.98);
  opacity: 0.9;
}

/* 统计图标 */
.stats-icon {
  font-size: 24rpx;
  line-height: 1;
}

/* 统计文本 */
.stats-text {
  color: #606266;
  font-size: 24rpx;
  line-height: 1.4;
  font-weight: 500;
}

/* 子章节列表 */
.children-list {
  background-color: #fafafa;
  border-top: 1rpx solid #f0f0f0;
  border-bottom-left-radius: 12rpx;
  border-bottom-right-radius: 12rpx;
  overflow: hidden;
}

/* 垂直布局列表项 - 二级章节 */
.list-item-vertical {
  display: flex;
  flex-direction: column;
  background-color: #ffffff;
  padding: 10rpx 20rpx;
  transition: all 0.3s ease;
}

.list-item-vertical.hover-class {
  background-color: #f8f9fa;
}

.list-item-vertical:not(:last-child) {
  border-bottom: 1rpx solid #ededed;
}

/* 垂直布局列表项 - 三级章节 */
.grandchild-item-vertical {
  display: flex;
  flex-direction: column;
  background-color: #ffffff;
  padding: 10rpx 0;
  transition: all 0.3s ease;
  border-bottom: 1rpx solid #ededed;
}

.grandchild-item-vertical.hover-class {
  background-color: #f8f9fa;
}

.grandchild-item-vertical:last-child {
  border-bottom: none;
}

/* 子章节头部容器（二级、三级通用） */
.item-header {
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
  gap: 20rpx;
  background-color: #ffffff;
  padding: 10rpx 0;
}

/* 子章节内容区 - 包含标题和统计 */
.item-header-content {
  display: flex;
  flex-direction: column;
  flex: 1;
  gap: 8rpx;
  min-width: 0; /* 确保文本省略能正常工作 */
  overflow: hidden; /* 防止内容溢出 */
}

/* 项标题区域 */
.item-title-section {
  display: flex;
  align-items: center;
  margin-bottom: 0;
  min-width: 0; /* 确保文本省略能正常工作 */
  overflow: hidden; /* 防止内容溢出 */
}

/* 子项箭头 */
.child-arrow {
  color: #c0c4cc;
  margin:0 20rpx 0 0;
  width: 44rpx;
  height: 44rpx;
  text-align: center;
  font-size: 44rpx;
  transition: all 0.3s ease;
}

/* 子项标题 */
.item-title {
  color: #606266;
  font-size: 28rpx;
  line-height: 40rpx;
  flex: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* 孙子章节标题 */
.grandchild-title {
  font-size: 28rpx;
  color: #909399;
  line-height: 40rpx;
}

/* 二级、三级章节的统计数据 */
.item-stats-under-title {
  display: flex;
  align-items: center;
  gap: 20rpx;
  width: 100%;
  left: 30rpx;
}

/* 孙子章节容器 */
.grandchildren-container {
  background-color: #fafafa;
  margin-left: 40rpx;
  overflow: hidden;
  margin-bottom: 0;
}

/* 练习按钮 */
::v-deep .practice-btn {
  min-width: 100rpx;
  height: 48rpx;
  line-height: 48rpx;
  font-size: 24rpx;
  padding: 0 24rpx;
  border-radius: 24rpx;
  transition: all 0.3s ease;
  align-self: center;
  flex-shrink: 0;
}

/* 继续按钮 */
::v-deep.continue-btn {
  min-width: 100rpx;
  height: 48rpx;
  line-height: 48rpx;
  font-size: 24rpx;
  padding: 0 24rpx;
  border-radius: 24rpx;
  transition: all 0.3s ease;
  align-self: center;
  flex-shrink: 0;
}

.group-header-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  margin-bottom: 12rpx;
}

/* 统计数据样式 */
.stats-section-top {
  padding: 20rpx;
  background-color: #ffffff;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20rpx;
  border-radius: 12rpx;
  box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.06);
}

.stats-item-success {
  background-color: #f0f9eb;
  color: #67c23a;
  margin-right: 10rpx;
}

.stats-item-collect {
  background-color: #fef0f0;
  color: #f56c6c;
  margin-left: 10rpx;
}

/* 收藏统计样式 */
.group-collect-stats {
  font-size: 26rpx;
  margin-bottom: 10rpx;
}

.collect-count-text {
  font-weight: 500;
}

.elimination-rate-text {
  font-weight: 500;
}

/* 消除率颜色 */
.tn-color-green {
  color: #67c23a;
}

/* 时间轴样式 */
.time-line__wrap {
  padding:0 30rpx;
}

.time-line-item__node {
  width: 44rpx;
  height: 44rpx;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #ffffff;
  border: 2rpx solid #67c23a;
  border-radius: 50%;
}

.time-line-item__node--icon {
  font-size: 24rpx;
  color: #67c23a;
}

.time-line-item__content {
  background-color: #ffffff;
  padding: 20rpx;
  border-radius: 12rpx;
}

.time-line-item__content__title {
  font-size: 28rpx;
  font-weight: 600;
  color: #303133;
  margin-bottom: 8rpx;
}

.time-line-item__content__desc {
  font-size: 26rpx;
  color: #606266;
  line-height: 38rpx;
}

.time-line-item__content__time {
  font-size: 24rpx;
  color: #909399;
  margin-top: 8rpx;
}

/* 题型项样式增强 */
.type-item {
  cursor: pointer;
  transition: all 0.3s ease;
  margin-bottom: 16rpx;
  padding: 24rpx 20rpx;
  box-shadow: 0 2rpx 12rpx rgba(0, 0, 0, 0.08);
}

.type-item.hover-class {
  opacity: 0.8;
  transform: scale(0.98);
}

/* 类型名称样式 */
.type-name {
  flex: 1;
  text-align: left;
  margin-right: 16rpx;
}

/* 类型计数样式 */
.type-count {
  gap: 8rpx;
}

.collection-desc {
  display: block;
  text-align: left;
}

/* 收藏计数样式 */
.collection-count {
  gap: 8rpx;
  align-items: flex-start;
  margin-top: 8rpx;
}

/* 内容区域底部padding，防止被底部导航栏遮挡 */
.tab-content {
  padding-bottom: 20rpx;
}

</style>
