<template>
  <view class="my-note-list-page">
    <!-- 顶部导航栏 -->
    <view class="tn-navbg" :style="{height: vuex_custom_bar_height + 'px'}">
      <tn-nav-bar fixed alpha customBack>
        <view slot="back" class='tn-custom-nav-bar__back' @click="goBack">
          <text class='icon tn-icon-left'></text>
          <text class='icon tn-icon-home-capsule-fill'></text>
        </view>
        <view class="tn-flex tn-flex-col-center tn-flex-row-center ">
          <text class="tn-text-bold tn-text-xl tn-color-white">我的笔记</text>
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
        <!-- 顶部滑动tab组件 -->
        <view class="stats-section-top tn-bg-white tn-radius tn-shadow-sm tn-flex tn-flex-row-between tn-flex-col-center">
          <tn-tabs-swiper 
            ref="tabsSwiper"
            :list="innerTabs" 
            :current="innerCurrentTabIndex"
            :active-color="mainColor"
            :inactive-color="'#666666'"
            class="tn-flex-1 tn-text-lg"
            :swiper-config="{duration: 300, circular: false}"
            @change="onInnerTabChange"
          />
          <!-- 打印笔记按钮 -->
          <view class="print-btn-container tn-margin-left-md">
            <text class="tn-icon-vip tn-color-orangeyellow"></text>
            <tn-button
              type="primary"
              size="md"
              shape="round"
              padding="0 10rpx"
              fontSize="24"
              @click="onPrintNoteListClick"
            >
              打印笔记
            </tn-button>
          </view>
        </view>

      <!-- 加载中 -->
      <view v-if="loading && !refreshing" class="loading-container">
        <tn-loading mode="flower" />
        <text class="tn-margin-top tn-color-gray">加载中...</text>
      </view>

      <!-- 笔记列表 -->
      <view v-else-if="noteList.length > 0" class="note-list-container">
        <tn-list-view 
          v-model="refreshing" 
          :loading="loading" 
          :finished="!hasMore" 
          finished-text="没有更多笔记了" 
          @refresh="onRefresh" 
          @load="onLoadMore"
          class="note-list"
        >
          <view 
            v-for="(note, index) in noteList" 
            :key="note.id" 
            class="note-item"
          >
            <view class="note-card">
              <!-- 用户信息 -->
              <view class="tn-flex tn-flex-row-between">
                <view class="tn-flex items-center">
                  <tn-avatar 
                    :size="40" 
                    :src="note.user.avatar || 'https://datiqiniu.allpp.cn/static/userinfo_avatar.png'" 
                    class="tn-margin-right"
                  />
                  <view class="flex-1">
                    <view class="tn-padding-right tn-text-df tn-text-bold tn-color-black" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: inline-block;">
                      {{ note.user.nickname || '匿名用户' }}
                    </view>
                    <view class="tn-padding-right tn-text-xs tn-color-gray" style="padding-top: 5rpx;">
                      {{ formatTime(note.create_time) }}
                    </view>
                  </view>
                </view>
              </view>

              <!-- 笔记内容 -->
              <view class="note-content" @click="goToNoteDetail(note.id)">
                {{ note.content }}
              </view>

              <!-- 试题题目 -->
              <view v-if="note.type === 1" class="question-title-container tn-margin-bottom-sm" @click="goToQuestionDetail(note.qid || note.question_id || note.id)">
                <text class="question-prefix">题目：</text>
                <mp-html class="question-text" :content="note.question.title || '无标题'"/>
                <text class="question-arrow tn-icon-right tn-text-lg tn-color-blue"></text>
              </view>
              
              <!-- 文章标题 -->
              <view v-else-if="note.type === 2" class="question-title-container tn-margin-bottom-sm" @click="goToNoteDetail(note.id)">
                <text class="question-prefix">文章：</text>
                <mp-html class="question-text" :content="note.article.title || '无标题'"/>
                <text class="question-arrow tn-icon-right tn-text-lg tn-color-blue"></text>
              </view>
              
              <!-- 资源标题 -->
              <view v-else-if="note.type === 3" class="question-title-container tn-margin-bottom-sm" @click="goToNoteDetail(note.id)">
                <text class="question-prefix">资源：</text>
                <mp-html class="question-text" :content="note.resource.title || '无标题'"/>
                <text class="question-arrow tn-icon-right tn-text-lg tn-color-blue"></text>
              </view>
              <!-- 互动信息 -->
              <view class="interaction-info">
                <view class="tn-flex items-center">
                  <view class="tn-flex items-center tn-margin-right-lg">
                    <text :class="[note.user_like ? 'tn-icon-like-fill tn-color-red' : 'tn-icon-like-lack tn-color-gray']" />
                    <text class="tn-margin-left-xs tn-text-sm">
                      {{ note.likes || 0 }}
                    </text>
                  </view>
                  <view class="tn-flex items-center">
                    <text class="tn-icon-comment tn-color-gray" />
                    <text class="tn-margin-left-xs tn-text-sm">
                      {{ note.children_count || 0 }}
                    </text>
                  </view>
                
                </view>
                <view class="tn-flex items-center">
                  <view class="operation-buttons">
                    <text class="tn-icon-edit tn-margin-right-lg" @click.stop="editNote(note)"></text>
                    <text class="tn-icon-delete tn-color-red" @click.stop="deleteNote(note.id, index)"></text>
                  </view>
                </view>
              </view>
            </view>
          </view>
        </tn-list-view>
      </view>

      <!-- 空状态 -->
      <view v-else class="empty-state">
        <text class="tn-icon-notebook tn-text-xxl tn-color-gray" />
        <text class="tn-margin-top tn-text-df tn-color-gray">
          暂无笔记
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

    <!-- 编辑笔记弹窗 -->
    <tn-popup 
      v-model="editPopupshow" 
      width="90%"
      mode="center" 
      height="650rpx"
      :border-radius="30"
      :safe-area-inset-bottom="true"
    >
      <view class="popup-container">
        <view class="popup-header">
          <view class="tn-flex items-center">
            <tn-avatar 
              :size="45" 
              :src="userAvatar" 
              class="tn-margin-right"
            />
            <text class="tn-text-lg tn-text-bold">
              编辑笔记
            </text>
          </view>
          <view class="tn-text-df tn-color-gray">
            <text class="tn-margin-right-xs">{{ editContent.length }}/500字</text>
            <text class="tn-icon-keyboard" />
          </view>
        </view>
        <view class="textarea-wrapper">
          <textarea
            v-model="editContent"
            maxlength="500"
            placeholder="说点什么，万一火了呢"
            placeholder-style="color:#AAAAAA"
            class="comment-textarea"
          />
        </view>
        <view class="popup-footer">
          <tn-button 
            shape="round" 
            type="default"
            :plain="true"
            :shadow="true"
            @click="closeEditPopup"
          >
            关 闭
          </tn-button>
          <tn-button 
            fontColor="tn-color-white"
            shape="round" 
            :backgroundColor="mainColor"
            :shadow="true"
            :disabled="!editContent.trim() || submittingEdit" 
            @click="submitEdit"
          >
            {{ submittingEdit ? '提交中...' : '提 交' }}
          </tn-button>
        </view>
      </view>
    </tn-popup>

    <!-- 删除确认弹窗 -->
    <tn-popup 
      v-model="deletePopupshow" 
      width="80%"
      mode="center" 
      :border-radius="30"
    >
      <view class="delete-popup-container">
        <view class="delete-header">
          <text class="tn-text-xl tn-text-bold">确认删除</text>
        </view>
        <view class="delete-content">
          <text class="tn-text-df tn-color-gray">您确定要删除这条笔记吗？删除后不可恢复。</text>
        </view>
        <view class="delete-footer">
          <tn-button 
            shape="round" 
            type="default"
            :plain="true"
            @click="closeDeletePopup"
          >
            取消
          </tn-button>
          <tn-button 
            fontColor="tn-color-white"
            shape="round" 
            backgroundColor="#F56C6C" 
            :disabled="deletingNote" 
            @click="confirmDelete"
          >
            {{ deletingNote ? '删除中...' : '确认删除' }}
          </tn-button>
        </view>
      </view>
    </tn-popup>
  </view>
</view>
</template>

<script>
import template_page_mixin from '@/libs/mixin/template_page_mixin.js'
import SubjectTabsManager from '@/components/subject-tabs-manager.vue'
export default {
  name: 'MyNoteList',
  components: {
    SubjectTabsManager,
  },
  mixins: [template_page_mixin],
  data() {
    return {
      mainColor: getApp().globalData.mainColor || '#007aff',
      noteList: [],
      loading: false,
      refreshing: false,
      hasMore: true,
      page_no: 1,
      page_size: 10,
      userAvatar: '',
      // 编辑笔记相关
      editPopupshow: false,
      editContent: '',
      editingNoteId: null,
      submittingEdit: false,
      // 删除笔记相关
      deletePopupshow: false,
      deletingNoteId: null,
      deletingNoteIndex: null,
      deletingNote: false,
      innerTabs: [
        { name: '题目笔记', value: 'question' },
        { name: '文章评论', value: 'article' },
        { name: '资源评论', value: 'resource' },
      ],
      innerCurrentTabIndex: 0,
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
    }
  },
  computed: {
    // 根据当前标签页动态生成空状态按钮文本
    emptyStateButtonText() {
      const tab = this.innerTabs[this.innerCurrentTabIndex]?.value;
      switch (tab) {
        case 'article':
          return '去阅读';
        case 'resource':
          return '去查看';
        default:
          return '去答题';
      }
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
    
    this.initUserInfo();
    this.restoreUserSubjects();
    this.loadNoteList();
  },
  methods: {
    // 初始化用户信息
    initUserInfo() {
      try {
        const userInfo = getApp().globalData.userInfo;
        if (userInfo && userInfo.avatar) {
          this.userAvatar = userInfo.avatar;
        } else {
          this.userAvatar = 'https://datiqiniu.allpp.cn/static/userinfo_avatar.png';
        }
      } catch (error) {
        console.error('[MyNoteList] 获取用户信息失败:', error);
        this.userAvatar = 'https://datiqiniu.allpp.cn/static/userinfo_avatar.png';
      }
    },
    // 内部选项卡切换事件
    onInnerTabChange(index) {
      this.innerCurrentTabIndex = index;
      // 切换标签时重置所有查询参数，避免参数交叉影响
      this.resetQueryParams();
      
      // 切换标签时刷新笔记列表
      this.loadNoteList();
    },
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
        this.loadNoteList();
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
      this.noteList = [];
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
    onPrintNoteListClick() {
      uni.showToast({ title: '正在开发中', icon: 'none' });
    },
    // 加载笔记列表
    loadNoteList() {
      if (this.loading || !this.hasMore) return;

      this.loading = true;
      
      // 调用API获取用户笔记列表
      const tabType = this.innerTabs[this.innerCurrentTabIndex].value;
      let apiType = 1; // 默认试题评论
      if (tabType === 'article') {
        apiType = 2; // 文章评论
      } else if (tabType === 'resource') {
        apiType = 3; // 资源评论
      }
      
      this.$api.apiMyCommentList({
        page_no: this.page_no,
        page_size: this.page_size,
        type: apiType, // 1: 试题评论, 2: 文章评论, 3: 资源评论
        library_uid: this.queryParams.uid || '', // 题库(科目)uid
        category_uid: this.selectedCategoryUid || '' // 题库分类uid
      }).then(res => {
        if (res && res.code === 1) {
          const newNotes = res.data || [];
          
          if (this.page_no === 1) {
            this.noteList = newNotes;
          } else {
            this.noteList = [...this.noteList, ...newNotes];
          }
          
          this.hasMore = newNotes.length === this.page_size;
          this.page_no++;
        } else {
          this.$func.showToast(res && res.msg || '获取笔记列表失败');
        }
      }).catch(error => {
        console.error('[MyNoteList] 加载笔记列表失败:', error);
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
      this.loadNoteList();
    },

    // 加载更多
    onLoadMore() {
      this.loadNoteList();
    },

    // 前往笔记详情页
    goToNoteDetail(commentId) {
      uni.navigateTo({
        url: `/subpages/examOther/noteDetail?comment_id=${commentId}&listType=1`
      });
    },

    // 编辑笔记
    editNote(note) {
      this.editingNoteId = note.id;
      this.editContent = note.content;
      this.editPopupshow = true;
    },

    // 提交编辑
    submitEdit() {
      if (!this.editContent.trim()) {
        this.$func.showToast('请输入笔记内容');
        return;
      }

      this.submittingEdit = true;

      // 获取用户ID
      const userId = getApp().globalData.userInfo?.id || '';

      // 调用API更新笔记
      this.$api.apiCommonEditComment({
        id: this.editingNoteId, // 笔记ID
        content: this.editContent, // 编辑后的内容
        user_id: userId // 添加用户ID
      }).then(res => {
        if (res && res.code === 1) {
          this.$func.showToast('笔记更新成功');
          this.closeEditPopup();
          this.onRefresh(); // 刷新列表
        } else {
          this.$func.showToast(res && res.msg || '笔记更新失败');
        }
      }).catch(error => {
        console.error('[MyNoteList] 更新笔记失败:', error);
        this.$func.showToast('网络请求失败');
      }).finally(() => {
        this.submittingEdit = false;
      });
    },

    // 关闭编辑弹窗
    closeEditPopup() {
      this.editPopupshow = false;
      this.editContent = '';
      this.editingNoteId = null;
    },

    // 删除笔记
    deleteNote(commentId, index) {
      this.deletingNoteId = commentId;
      this.deletingNoteIndex = index;
      this.deletePopupshow = true;
    },

    // 确认删除
    confirmDelete() {
      this.deletingNote = true;
      
      // 获取用户ID
      const userId = getApp().globalData.userInfo?.id || '';
      
      // 调用真实的API删除笔记
      this.$api.apiCommonDeleteComment({
        id: this.deletingNoteId, // 笔记ID
        user_id: userId // 添加用户ID
      }).then(res => {
        if (res && res.code === 1) {
          this.$func.showToast('笔记删除成功');
          this.noteList.splice(this.deletingNoteIndex, 1);
          this.closeDeletePopup();
        } else {
          this.$func.showToast(res && res.msg || '笔记删除失败');
        }
      }).catch(error => {
        console.error('[MyNoteList] 删除笔记失败:', error);
        this.$func.showToast('网络请求失败');
      }).finally(() => {
        this.deletingNote = false;
      });
    },

    // 关闭删除弹窗
    closeDeletePopup() {
      this.deletePopupshow = false;
      this.deletingNoteId = null;
      this.deletingNoteIndex = null;
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
      const tab = this.innerTabs[this.innerCurrentTabIndex]?.value;
      switch (tab) {
        case 'article':
          // 跳转到文章列表页
          uni.navigateTo({
            url: '/subpages/news/articleList?exam_category_uid=' + this.selectedCategoryUid
          });
          break;
        case 'resource':
          // 跳转到资源列表页
          uni.navigateTo({
            url: '/subpages/resource/resourceList?exam_category_uid=' + this.selectedCategoryUid
          });
          break;
        default:
          // 跳转到答题设置页
          uni.navigateTo({
            url: '/subpages/exam/questionSetting?uid=' + this.queryParams.uid
          });
          break;
      }
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
      title: '我的笔记',
      path: '/subpages/examOther/myNoteList',
      imageUrl: ''
    };
  },
  
  onShareTimeline() {
    return {
      title: '我的笔记',
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

.my-note-list-page {
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

.note-list-container {
  padding: 20rpx;
}

.note-list {
  padding-bottom: 20rpx;
}

.note-item {
  margin-bottom: 20rpx;
}

.note-card {
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

.note-content {
  margin: 10rpx;
  line-height: 1.8;
  font-size: 30rpx;
  color: #333333;
  word-break: break-word;
  padding: 10rpx 0;
}

/* 试题题目容器样式 */
.question-title-container {
  display: flex;
  align-items: center;
  background-color: #E3F2FD;
  border-radius: 15rpx;
  padding: 10rpx 25rpx;
  cursor: pointer;
  transition: all 0.3s ease;
}

/* 题目前缀样式 */
.question-prefix {
  font-size: 28rpx;
  color: #1976D2;
  font-weight: bold;
}

/* 题目文本样式 */
.question-text {
  flex: 1;
  font-size: 26rpx;
  color: #1976D2;
  line-height: 1.6;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* 题目箭头样式 */
.question-arrow {
  margin-left: 10rpx;
  color: #1976D2;
  font-size: 32rpx;
}

.interaction-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 10rpx;
  border-top: 1rpx solid #F0F0F0;
  color: #606266;
}

.operation-buttons {
  display: flex;
  align-items: center;
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

/* 弹窗样式 */
.popup-container {
  display: flex;
  flex-direction: column;
  height: 100%;
  padding: 30rpx;
}

.popup-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 30rpx;
  border-bottom: 2rpx solid #F0F0F0;
  margin-bottom: 30rpx;
}

.textarea-wrapper {
  flex: 1;
  background-color: #F8F9FA;
  border-radius: 12rpx;
  padding: 20rpx;
  margin-bottom: 30rpx;
  
  .comment-textarea {
    width: 100%;
    font-size: 28rpx;
    line-height: 1.6;
  }
}

.popup-footer {
  display: flex;
  justify-content: space-between;
  gap: 20rpx;
}

/* 删除弹窗样式 */
.delete-popup-container {
  padding: 40rpx;
}

.delete-header {
  text-align: center;
  margin-bottom: 30rpx;
}

.delete-content {
  text-align: center;
  margin-bottom: 40rpx;
}

.delete-footer {
  display: flex;
  justify-content: space-between;
  gap: 20rpx;
}

/* 响应式设计 */
@media screen and (max-width: 375px) {
  .note-card {
    padding: 15rpx;
  }
  
  .note-content {
    font-size: 28rpx;
    margin: 15rpx 0;
  }
}

/* 滑动内容区域样式 */
.inner-swiper {
  height: calc(100vh - 380rpx);
  overflow: hidden;
}

.tab-content {
  min-height: 100%;
  display: flex;
  flex-direction: column;
}

/* 分类选择器样式 */
.category-selector {
  background-color: #fff;
  border-radius: 16rpx;
  margin-left: -16rpx;
  box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.08);
}
</style>