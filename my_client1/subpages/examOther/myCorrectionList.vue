<template>
  <view class="my-correction-list-page">
    <!-- 顶部导航栏 -->
    <view class="tn-navbg" :style="{height: vuex_custom_bar_height + 'px'}">
      <tn-nav-bar fixed alpha customBack>
        <view slot="back" class='tn-custom-nav-bar__back' @click="goBack">
          <text class='icon tn-icon-left'></text>
          <text class='icon tn-icon-home-capsule-fill'></text>
        </view>
        <view class="tn-flex tn-flex-col-center tn-flex-row-center ">
          <text class="tn-text-bold tn-text-xl tn-color-white">我的纠错</text>
        </view>
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
        @save="handleSubjectsSave"
        @add="handleSubjectsAdd"
        @remove="handleSubjectsRemove"
      />
      <!-- 内容区域 -->
      <view class="tab-content">

        <!-- 加载中 -->
        <view v-if="loading && !refreshing" class="loading-container">
          <tn-loading mode="flower" />
          <text class="tn-margin-top tn-color-gray">加载中...</text>
        </view>

        <!-- 纠错列表 -->
        <view v-else-if="correctionList.length > 0" class="correction-list-container">
          <tn-list-view 
            v-model="refreshing" 
            :loading="loading" 
            :finished="!hasMore" 
            finished-text="没有更多纠错了" 
            @refresh="onRefresh" 
            @load="onLoadMore"
            class="correction-list"
          >
            <view 
              v-for="(correction, index) in correctionList" 
              :key="correction.id" 
              class="correction-item"
            >
              <view class="correction-card tn-card tn-radius tn-shadow-sm">
                <!-- 题目信息 -->
                <view class="question-info tn-flex tn-flex-row-between tn-flex-col-center" @click="goToQuestionDetail(correction.question_uid)">
                  <view class="question-title tn-text-df tn-color-black tn-text-bold">
                    {{ correction.question_name.replace(/<[^>]*>/g, '') }}
                  </view>
                  <text class="question-arrow tn-icon-right tn-text-lg tn-color-blue"></text>
                </view>

                <!-- 纠错原因 -->
                <view class="correction-reason tn-margin-top">
                  <text class="reason-label tn-text-sm tn-color-gray">纠错原因：</text>
                  <text class="reason-content tn-text-df tn-color-333">{{ correction.correction_reason || '无' }}</text>
                </view>

                <!-- 反馈状态 -->
                <view class="feedback-status tn-margin-top">
                  <text class="status-label tn-text-sm tn-color-gray">反馈状态：</text>
                  <text 
                    :class="correction.platform_feedback ? 'tn-color-green' : 'tn-color-red'"
                    size="small"
                    class="tn-margin-left-xs"
                  >
                    {{ correction.platform_feedback ? '已反馈' : '未反馈' }}
                  </text>
                  <text v-if="correction.platform_feedback" class="feedback-time tn-margin-left-xs tn-text-xs tn-color-gray"  @click="showFeedbackModal(correction)">
                    （{{ formatTime(correction.feedback_time) }}）
                    <text class="tn-icon-eye tn-color-blue tn-margin-left-xs">详情</text>
                  </text>
                </view>

                <!-- 提交时间 -->
                <view class="submit-time tn-margin-top">
                  <text class="time-label tn-text-sm tn-color-gray">提交时间：</text>
                  <text class="time-content tn-text-xs tn-color-gray">{{ correction.create_time }}</text>
                </view>
              </view>
            </view>
          </tn-list-view>
        </view>

        <!-- 空状态 -->
        <view v-else class="empty-state">
          <text class="tn-icon-document-error tn-text-xxl tn-color-gray" />
          <text class="tn-margin-top tn-text-df tn-color-gray">
            暂无纠错记录
          </text>
          <tn-button 
            class="tn-margin-top-lg"
            :backgroundColor="mainColor"
            fontColor="tn-color-white"
            @click="handleEmptyStateButtonClick"
          >
            {{ emptyStateButtonText }}
          </tn-button>
        </view>
      </view>
    </view>
    <!-- 反馈详情模态框 -->
    <tn-modal
      v-model="showFeedbackModalVisible"
      title="反馈内容"
      :custom="true"
      :showCloseBtn="true"
      :maskCloseable="true"
      :width="'90%'"
      @cancel="closeFeedbackModal"
    >
      <view class="feedback-modal-content" v-if="currentFeedback">
        <view class="modal-item">
          <text class="modal-label tn-text-sm tn-color-gray">题目名称：</text>
          <view class="modal-content tn-text-df tn-color-black" v-html="currentFeedback.question_name"></view>
        </view>
        <view class="modal-item tn-margin-top">
          <text class="modal-label tn-text-sm tn-color-gray">纠错原因：</text>
          <view class="modal-content tn-text-df tn-color-black" v-html="currentFeedback.correction_reason"></view>
        </view>
        <view class="modal-item tn-margin-top">
          <text class="modal-label tn-text-sm tn-color-gray">平台反馈：</text>
          <view class="modal-content tn-text-df tn-color-black" v-html="currentFeedback.platform_feedback"></view>
        </view>
        <view class="modal-item tn-margin-top">
          <text class="modal-label tn-text-sm tn-color-gray">反馈时间：</text>
          <text class="modal-content tn-text-df tn-color-gray">{{ currentFeedback.feedback_time }}</text>
        </view>
      </view>
    </tn-modal>
  </view>
</template>

<script>
import template_page_mixin from '@/libs/mixin/template_page_mixin.js'
import SubjectTabsManager from '@/components/subject-tabs-manager.vue'
export default {
  name: 'MyCorrectionList',
  components: {
    SubjectTabsManager,
  },
  mixins: [template_page_mixin],
  data() {
    return {
      mainColor: getApp().globalData.mainColor || '#007aff',
      correctionList: [],
      loading: false,
      refreshing: false,
      hasMore: true,
      page_no: 1,
      // 查询参数
      queryParams: {
        uid: '', // 题库ID
      },
      // 主题标签相关数据
      scrollList: [],
      currentSubjectIndex: 0,
      showSubjectPopup: false,
      myQuestionLibList: [],
      questionLibList: [],
      loadingQuestionLib: false,
      selectedCategory: '',
      selectedCategoryUid: '',
      // 反馈详情模态框相关
      showFeedbackModalVisible: false,
      currentFeedback: null
    }
  },
  computed: {
    // 空状态按钮文本
    emptyStateButtonText() {
      return '去答题';
    }
  },
  onLoad() {
    // 检查登录状态，未登录时跳转登录页
    // 获取当前选中的分类ID
    this.selectedCategoryUid = uni.getStorageSync('selectedCategoryUid');
    const { isUserLoggedIn } = require('@/util/userStore.js')
    if (!isUserLoggedIn()) {
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
    
    this.restoreUserSubjects();
    this.loadCorrectionList();
  },
  methods: {
    // 主题标签切换
    onSubjectTabChange(index) {
      // 切换主题时，根据新选中的主题更新查询参数
      this.resetQueryParams();
      this.currentSubjectIndex = index;
      // 切换主题时，根据新选中的主题更新题库ID
      if (this.myQuestionLibList.length > 0 && index < this.myQuestionLibList.length) {
        // 保存当前选中的题库ID到查询参数，用于API调用
        this.queryParams = this.queryParams || {};
        this.queryParams.uid = this.myQuestionLibList[index].id || this.myQuestionLibList[index].uid;
        this.selectedCategory = this.myQuestionLibList[index].name;
        
        // 切换主题时刷新笔记列表
        this.loadCorrectionList();
      }
    },
    // 显示主题管理弹窗
    showSubjectManager() {
      // 加载科目列表数据
      this.loadQuestionLibList();
      // 显示弹窗
      this.showSubjectPopup = true;
    },
    // 保存主题设置
    handleSubjectsSave(subjects) {
      this.saveSubjectsToStorage(subjects)
      this.myQuestionLibList = [...subjects]
      this.updateScrollList()
      this.showSubjectPopup = false
    },
    // 添加主题
    handleSubjectsAdd(subject, subjects) {
      this.saveSubjectsToStorage(subjects)
      this.myQuestionLibList = [...subjects]
      this.updateScrollList()
    },
    // 移除主题
    handleSubjectsRemove(index, subjects) {
      this.saveSubjectsToStorage(subjects)
      this.myQuestionLibList = [...subjects]
      this.updateScrollList()
    },
    // 重置基本查询参数，不含题库uid
    resetQueryParams() {
      this.page_no = 1;
      this.hasMore = true;
      this.correctionList = [];
    },
    // 从本地存储中恢复用户选择的科目
    restoreUserSubjects() {
      if (this.selectedCategoryUid) {
        // 从本地存储中获取用户选择的科目
        const savedQuestionLibList = uni.getStorageSync('myQuestionLibList' + this.selectedCategoryUid);
        if (savedQuestionLibList && Array.isArray(savedQuestionLibList) && savedQuestionLibList.length > 0) {
          // 更新我的科目列表
          this.myQuestionLibList = savedQuestionLibList;
          // 更新科目切换标签列表
          this.updateScrollList();
          // 如果有科目，更新题库ID
          if (this.myQuestionLibList[this.currentSubjectIndex]) {
            // 保存当前选中的题库ID到查询参数，用于API调用
            this.queryParams = this.queryParams || {};
            this.queryParams.uid = this.myQuestionLibList[this.currentSubjectIndex].id || this.myQuestionLibList[this.currentSubjectIndex].uid;
            this.selectedCategory = this.myQuestionLibList[this.currentSubjectIndex].name;
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
    // 显示反馈详情模态框
    showFeedbackModal(correction) {
      this.currentFeedback = correction;
      this.showFeedbackModalVisible = true;
    },
    // 关闭反馈详情模态框
    closeFeedbackModal() {
      this.showFeedbackModalVisible = false;
      this.currentFeedback = null;
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
        category_uid: this.selectedCategoryUid || '',
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
      
      // 确保currentSubjectIndex索引在有效范围内
      if (this.currentSubjectIndex >= this.scrollList.length) {
        this.currentSubjectIndex = Math.max(0, this.scrollList.length - 1);
        // 如果有科目，更新题库ID
        if (this.myQuestionLibList[this.currentSubjectIndex]) {
          // 保存当前选中的题库ID到查询参数，用于API调用
          this.queryParams = this.queryParams || {};
          this.queryParams.uid = this.myQuestionLibList[this.currentSubjectIndex].id || this.myQuestionLibList[this.currentSubjectIndex].uid;
          this.selectedCategory = this.myQuestionLibList[this.currentSubjectIndex].name;
        }
      }
    },
    // 保存科目到本地存储
    saveSubjectsToStorage(subjects) {
      // 如果没有分类ID，使用默认值或从其他地方获取
      if (!this.selectedCategoryUid) {
        // 尝试从index.vue使用的默认值获取，或者使用当前选中的科目ID
        this.selectedCategoryUid = (this.myQuestionLibList[0] && this.myQuestionLibList[0].id) || (this.myQuestionLibList[0] && this.myQuestionLibList[0].uid) || 'default';
        // 保存分类ID到本地存储
        uni.setStorageSync('selectedCategoryUid', this.selectedCategoryUid);
      }
      // 保存用户选择的科目到本地存储
      uni.setStorageSync('myQuestionLibList' + this.selectedCategoryUid, subjects);
    },
    // 加载纠错列表
    loadCorrectionList() {
      if (this.loading || !this.hasMore) return;

      this.loading = true;
      
      // 调用API获取用户纠错列表
      this.$api.myErrorCorrectList({
        page_no: this.page_no,
        page_size: this.page_size,
        library_uid: this.queryParams.uid || '', // 题库(科目)uid
        category_uid: this.selectedCategoryUid || '' // 题库分类uid
      }).then(res => {
        if (res && res.code === 1) {
          const newCorrections = res.data || [];
          
          if (this.page_no === 1) {
            this.correctionList = newCorrections;
          } else {
            this.correctionList = [...this.correctionList, ...newCorrections];
          }
          
          this.hasMore = newCorrections.length === this.page_size;
          this.page_no++;
        } else {
          this.$func.showToast(res && res.msg || '获取纠错列表失败');
        }
      }).catch(error => {
        console.error('[MyCorrectionList] 加载纠错列表失败:', error);
        this.$func.showToast('网络请求失败');
      }).finally(() => {
        this.loading = false;
        this.refreshing = false;
      });
    },

    // 下拉刷新
    onRefresh() {
      this.page_no = 1;
      this.hasMore = true;
      this.loadCorrectionList();
    },

    // 加载更多
    onLoadMore() {
      this.loadCorrectionList();
    },

    // 格式化时间
    formatTime(timeStr) {
      if (!timeStr) return '';
      
      const formattedTimeStr = timeStr.replace(/-/g, '/');
      const now = new Date();
      const time = new Date(formattedTimeStr);
      
      if (isNaN(time.getTime())) {
        return timeStr;
      }
      
      const diffInSeconds = Math.floor((now - time) / 1000);
      
      if (diffInSeconds < 60) return '刚刚';
      
      const diffInMinutes = Math.floor(diffInSeconds / 60);
      if (diffInMinutes < 60) return `${diffInMinutes}分钟前`;
      
      const diffInHours = Math.floor(diffInMinutes / 60);
      if (diffInHours < 24) return `${diffInHours}小时前`;
      
      const diffInDays = Math.floor(diffInHours / 24);
      if (diffInDays < 7) return `${diffInDays}天前`;
      
      return `${time.getFullYear()}-${(time.getMonth() + 1).toString().padStart(2, '0')}-${time.getDate().toString().padStart(2, '0')}`;
    },

    // 返回上一页
    goBack() {
      uni.navigateBack();
    },

    // 去首页
    goToHome() {
      uni.switchTab({
        url: '/pages/index/index'
      });
    },
    
    // 处理空状态按钮点击事件
    handleEmptyStateButtonClick() {
      // 跳转到答题设置页
      uni.navigateTo({
        url: '/subpages/exam/questionSetting?uid=' + this.queryParams.uid
      });
    },
    
    // 跳转到试题详细信息页面
    goToQuestionDetail(questionUid) {
      // 准备传递给commonQuestion.vue的参数
      const examSettings = {
        uid: this.queryParams.uid,
        analysis_quid: questionUid,
        question_count: 1,
        questions_note: 1,
        questions_type: 0, // 0表示笔记练习
        mode: 'reviewOnly',
        practice_mode: 3
      };
      
     // 重置选中的项
      this.selectedErrorItem = null
      
      // 直接跳转到答题页面，不需要经过设置步骤
      uni.navigateTo({
        url: '/subpages/exam/components/commonQuestion?examSettings=' + encodeURIComponent(JSON.stringify(examSettings))
      })
    }
  },

  // 分享功能
  onShareAppMessage() {
    return {
      title: '我的纠错',
      path: '/subpages/examOther/myCorrectionList',
      imageUrl: ''
    };
  },
  
  onShareTimeline() {
    return {
      title: '我的纠错',
      query: '',
      imageUrl: ''
    };
  },
  
  onShow() {
    // 每次显示页面时，从本地存储中恢复用户选择的科目
    this.restoreUserSubjects();
  }
};
</script>

<style lang="scss" scoped>
@import "@/scss/custom_nav_bar.scss";

.my-correction-list-page {
  min-height: 100vh;
  background-color: #F8F9FA;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 100rpx 0;
}

.correction-list-container {
  padding: 20rpx;
}

.correction-list {
  padding-bottom: 20rpx;
}

.correction-item {
  margin-bottom: 20rpx;
}

.correction-card {
  background-color: #FFFFFF;
  border-radius: 16rpx;
  padding: 20rpx;
  box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
  
  &.hover-class {
    transform: scale(0.98);
    box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
  }
}

.question-info {
  cursor: pointer;
  padding: 10rpx 0;
}

.question-title {
  flex: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.question-arrow {
  margin-left: 10rpx;
  color: #409EFF;
  font-size: 32rpx;
}

.correction-reason,
.feedback-status,
.submit-time {
  margin-top: 10rpx;
}

.reason-label,
.status-label,
.time-label {
  font-size: 28rpx;
  color: #909399;
  font-weight: normal;
}

.reason-content {
  font-size: 30rpx;
  color: #303133;
  line-height: 1.6;
}

.feedback-time,
.time-content {
  font-size: 26rpx;
  color: #909399;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 200rpx 40rpx;
  color: #999999;
  text-align: center;
}

.tab-content {
  min-height: 100%;
  display: flex;
  flex-direction: column;
}

/* 响应式设计 */
@media screen and (max-width: 375px) {
  .correction-card {
    padding: 15rpx;
  }
  
  .reason-content {
    font-size: 28rpx;
    margin: 15rpx 0;
  }
}
</style>