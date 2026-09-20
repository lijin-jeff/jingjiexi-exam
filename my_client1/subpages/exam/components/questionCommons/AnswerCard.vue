<template>
  <!-- 答题卡组件 -->
  <tn-popup 
    v-model="isShow" 
    mode="bottom" 
    height="70%" 
    :safe-area-inset-bottom="true"
    :border-radius="30" 
    :close-btn="true"
    close-btn-icon="close-circle"
    close-btn-position="top-right"
    close-icon-color="#AAAAAA"
    :mask-closeable="true"
    :margin-top="0"
    @close="handleClose"
  >
    <view class="answer-card">
      <!-- 答题卡标题栏 -->
      <view class="card-header">
        <view class="header-title">
          <text class="tn-icon-edit" :style="{color: mainColor}" />
          <text class="title-text">
            答题卡
          </text>
        </view>
        <view class="header-stats">
          <view class="stat-item">
            <text class="stat-value">
              {{ answeredCount }}
            </text>
            <text class="stat-label">
              已答
            </text>
          </view>
          <view class="stat-item">
            <text class="stat-value">
              {{ unansweredCount }}
            </text>
            <text class="stat-label">
              未答
            </text>
          </view>
          <view
            v-if="showResult"
            class="stat-item"
          >
            <text class="stat-value">
              {{ accuracyRate }}%
            </text>
            <text class="stat-label">
              正确率
            </text>
          </view>
        </view>
      </view>
      
      <!-- 图例说明 -->
      <view
        v-if="answeredCount > 0 || showResult"
        class="card-legend"
      >
        <view class="legend-item">
          <view class="legend-dot current-dot" />
          <text class="legend-text">
            当前题
          </text>
        </view>
        <view class="legend-item">
          <view class="legend-dot answered-dot" />
          <text class="legend-text">
            已答
          </text>
        </view>
        <view class="legend-item">
          <view class="legend-dot unanswered-dot" />
          <text class="legend-text">
            未答
          </text>
        </view>
        <view
          v-if="showResult"
          class="legend-item"
        >
          <view class="legend-dot correct-dot" />
          <text class="legend-text">
            正确
          </text>
        </view>
        <view
          v-if="showResult"
          class="legend-item"
        >
          <view class="legend-dot wrong-dot" />
          <text class="legend-text">
            错误
          </text>
        </view>
      </view>
      
      <!-- 题目列表 -->
      <scroll-view
        scroll-y
        class="card-content"
      >
        <block
          v-for="(questions, typeKey) in questionsByType"
          :key="typeKey"
        >
          <tn-list-view
            :card="true"
            unlined="all"
            class="question-type-section"
          >
            <tn-list-cell
              :arrow="false"
              :hover="false"
              :padding="0"
            >
              <view class="question-type-header">
                <text class="tn-icon-document-text" :style="{color: mainColor}" />
                <text class="type-label">
                  {{ getQuestionTypeName(typeKey) }}
                </text>
                <text :style="{color: mainColor}">
                  （{{ questions.length }}题）
                </text>
              </view>
            </tn-list-cell>
            
            <tn-list-cell
              :arrow="false"
              :hover="false"
              :unlined="true"
              :padding="0"
            >
              <view class="question-grid">
                <block
                  v-for="item in questions"
                  :key="item.uniqueKey"
                >
                  <view 
                    :class="[
                      'question-item',
                      item.index === currentIndex ? 'current-question' : '',
                      (item.question.is_answered || (item.question.user_answer && (Array.isArray(item.question.user_answer) ? item.question.user_answer.length > 0 : true))) ? 'answered' : 'unanswered',
                      showResult && (item.question.is_answered || (item.question.user_answer && (Array.isArray(item.question.user_answer) ? item.question.user_answer.length > 0 : true))) && item.question.is_correct !== undefined ? (item.question.is_correct ? 'correct' : 'wrong') : ''
                    ]"
                    @click="handleQuestionClick(item.index)"
                  >
                    {{ item.index + 1 }}
                  </view>
                </block>
              </view>
            </tn-list-cell>
          </tn-list-view>
        </block>
      </scroll-view>
      
      <!-- 底部按钮 -->
      <view class="card-footer">
        <tn-button 
          background-color="tn-cool-bg-color-14" 
          font-color="#FFFFFF"
          shape="round" 
          :shadow="true"
          class="footer-btn"
          height="68rpx"
          font-size="28rpx"
          :disabled="unansweredCount === 0"
          @click="goToUnansweredQuestion"
        >
          去未答题
        </tn-button>
        <tn-button 
          background-color="tn-cool-bg-color-6" 
          font-color="#FFFFFF"
          shape="round" 
          :shadow="true"
          class="footer-btn"
          height="68rpx"
          font-size="28rpx"
          @click="handleSubmitExam"
        >
          提交答题
        </tn-button>
        <tn-button 
          backgroundColor="tn-cool-bg-color-9" 
          font-color="#FFFFFF"
          shape="round" 
          :shadow="true"
          class="footer-btn"
          height="68rpx"
          font-size="28rpx"
          @click="handleSaveProgress"
        >
          保存退出
        </tn-button>
      </view>
    </view>
  </tn-popup>
</template>

<script>

import { getQuestionTypeName } from '@/util/questionTypeManager.js'

export default {
  name: 'AnswerCard',
  components: {
  },
  props: {
    // 题目列表
    questionList: {
      type: Array,
      default: () => []
    },
    // 当前题目索引
    currentIndex: {
      type: Number,
      default: 0
    },
    // 已答题数量
    answeredCount: {
      type: Number,
      default: 0
    },
    // 总题目数
    totalCount: {
      type: Number,
      default: 0
    },
    // 是否显示结果
    showResult: {
      type: Boolean,
      default: false
    },
    // 是否显示（v-model支持）
    value: {
      type: Boolean,
      default: false
    }
  },
  emits: ['input', 'examChange', 'saveProgress', 'submitExam'],
  data() {
    return {
      mainColor: getApp().globalData.mainColor || '#1E88E5',
      showIcon: false,
      isShow: this.value,
      // 屏幕宽度和高度
      screenWidth: 0,
      screenHeight: 0,
      // 是否显示按钮区域
      showActions: true,
    };
  },
  computed: {
    // 按题型分类的题目
    questionsByType() {
      const result = {};
      
      // 初始化题型分类
      if (this.questionList && this.questionList.length > 0) {
        this.questionList.forEach((question, index) => {
          const typeKey = question.exam_type || 1;
          if (!result[typeKey]) {
            result[typeKey] = [];
          }
          result[typeKey].push({
            question: question,
            index: index,
            // 🔑 修复：为每个题目生成唯一的 key，优先使用 uid
            uniqueKey: question.uid || `question_${index}`
          });
        });
      }
      
      return result;
    },
    
    // 已答题数（计算值，优先使用外部传入的数据）
    computedAnsweredCount() {
      return this.$props.answeredCount || (this.questionList ? this.questionList.filter(question => 
        question && (question.is_answered || (question.user_answer && (Array.isArray(question.user_answer) ? question.user_answer.length > 0 : true)))
      ).length : 0);
    },
    
    // 未答题数
    unansweredCount() {
      const totalCount = this.questionList ? this.questionList.length : 0;
      return totalCount - this.computedAnsweredCount;
    },
    
    // 正确率（计算值，优先使用外部传入的数据）
    accuracyRate() {
      if (!this.questionList || !this.showResult) return 0;
      
      const answeredQuestions = this.questionList.filter(question => 
        question && (question.is_answered || (question.user_answer && (Array.isArray(question.user_answer) ? question.user_answer.length > 0 : true)))
      );
      
      if (answeredQuestions.length === 0) return 0;
      
      const correctQuestions = answeredQuestions.filter(question => 
        question && question.is_correct
      );
      
      return Math.round((correctQuestions.length / answeredQuestions.length) * 100);
    },
    
    // 计算错题数（计算值，优先使用外部传入的数据）
    errorCount() {
      return this.questionList && Array.isArray(this.questionList) ? 
        this.questionList.filter((item) => item && item.is_wrong).length : 0;
    },
    
    // 计算答题进度百分比（计算值，优先使用外部传入的数据）
    progress() {
      const totalCount = this.questionList ? this.questionList.length : 0;
      if (!totalCount) return 0;
      return ((this.computedAnsweredCount / totalCount) * 100).toFixed(0);
    },
    
    // 计算按钮尺寸
    buttonSize() {
      // 根据屏幕宽度调整按钮尺寸
      if (this.screenWidth < 375) {
        return 'small';
      } else if (this.screenWidth < 428) {
        return 'medium';
      }
      return 'large';
    },
    
    // 计算网格样式
    gridStyle() {
      // 根据屏幕宽度调整网格布局
      const cols = this.screenWidth < 375 ? 5 : 
                  this.screenWidth < 428 ? 6 : 8;
      
      return {
        display: 'grid',
        gridTemplateColumns: `repeat(${cols}, 1fr)`,
        gap: '16rpx',
      };
    }
  },
  watch: {
    value: {
      handler(newVal) {
        // 直接设置isShow，确保响应式更新
        this.isShow = newVal;
      },
      immediate: true,
    },
    isShow(newVal) {
      // 无论isShow如何变化，都通知父组件更新
      this.$emit('input', newVal);
    },
    // 监听屏幕尺寸变化
    ['$mp.statusBarHeight']() {
      this.getScreenSize();
    },
  },
  mounted() {
    // 获取屏幕尺寸
    this.getScreenSize();
  },
  beforeUnmount() {
    // 小程序环境中不需要移除事件监听器
  },
  methods: {
    // 获取屏幕尺寸
    getScreenSize() {
      // 使用推荐的API替代弃用的wx.getSystemInfoSync()
      const { windowWidth, windowHeight } = uni.getWindowInfo();
      this.screenWidth = windowWidth;
      this.screenHeight = windowHeight;
    },
    
    // 获取题目类型名称
    getQuestionTypeName(type) {
      // 使用统一的题型管理工具
      return getQuestionTypeName(type)
    },
    
    // 获取题目状态类名
    getQuestionClass(question, index) {
      if (!question) return 'question-item';
      
      const classes = ['question-item'];
      
      // 当前选中题目
      if (index === this.currentIndex) {
        classes.push('current-question');
      }
      
      // 已答/未答状态
      if (question.is_answered || (question.user_answer && (Array.isArray(question.user_answer) ? question.user_answer.length > 0 : true))) {
        classes.push('answered');
        
        // 如果显示结果，添加正确/错误状态
        if (this.showResult && question.is_correct !== undefined) {
          if (question.is_correct) {
            classes.push('correct');
          } else {
            classes.push('wrong');
          }
        }
      } else {
        classes.push('unanswered');
      }
      
      return classes.join(' ');
    },
    
    // 处理题目点击
    handleQuestionClick(index) {
      // 🔑 调试日志：检查传入的索引

      // 验证索引有效性
      if (index >= 0 && index < (this.questionList ? this.questionList.length : 0)) {
        // 使用examChange事件名，与父组件监听的事件名一致
        this.$emit('examChange', index);
        // 关闭答题弹窗 
        this.isShow = false;
      } else {
        console.error('索引无效:', index);
      }
    },
    
    // 保存进度
    handleSaveProgress() {
      this.$emit('save-progress');
      this.isShow = false;
    },
    
    // 提交考试
    handleSubmitExam() {
      this.$emit('submit-exam');
      this.isShow = false;
    },
    
    // 跳转到下一题目
    goToNextQuestion() {
      if (this.currentIndex < (this.questionList ? this.questionList.length : 0) - 1) {
        this.handleQuestionClick(this.currentIndex + 1);
      } else {
        this.showToast('已经是最后一题了');
      }
    },
    
    // 跳转到上一题目
    goToPreQuestion() {
      if (this.currentIndex > 0) {
        this.handleQuestionClick(this.currentIndex - 1);
      } else {
        this.showToast('已经是第一题了');
      }
    },
    
    // 跳转到未答题
    goToUnansweredQuestion() {
      const index = this.questionList && Array.isArray(this.questionList) ? 
        this.questionList.findIndex((item) => item && !item.is_answered && !(item.user_answer && (Array.isArray(item.user_answer) ? item.user_answer.length > 0 : true))) : -1;
      if (index !== -1) {
        this.handleQuestionClick(index);
      } else {
        this.showToast('所有题目都已作答');
      }
    },
    
    // 显示提示信息
    showToast(message) {
      uni.showToast({
        title: message,
        icon: 'none',
        duration: 2000
      });
    },
    
    // 更新答题状态
    updateAnswerStatus(index, answer) {
      if (this.questionList && this.questionList[index]) {
        // 使用$set确保响应式更新
        this.$set(this.questionList[index], 'user_answer', answer);
        this.$set(this.questionList[index], 'is_answered', !!answer);
      }
    },
    
    // 打开答题弹窗
    open() {
      this.isShow = true;
    },
    
    // 关闭答题弹窗 
    close() {
      this.isShow = false
    },
    
    // 处理弹出层关闭事件
    handleClose() {
      this.isShow = false
    }
  }
}
</script>

<style lang="scss" scoped>
.answer-card {
  display: flex;
  flex-direction: column;
  height: 100%;
  padding: 20rpx;
  background-color: var(--tn-bg-gray--light, #F8F9FA);
  
  .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 30rpx 20rpx 30rpx 90rpx;
    margin-bottom: 20rpx;
    background-color: #FFFFFF;
    border-radius: var(--tn-border-radius-lg, 20rpx);
    box-shadow: var(--tn-box-shadow-sm, 0 4rpx 20rpx rgba(0, 0, 0, 0.05));
    
    .header-title {
      display: flex;
      align-items: center;
      
      /* 图标样式（颜色通过 :style 动态绑定） */
      .tn-icon-edit {
        font-size: var(--tn-font-size-lg, 36rpx);
        margin-right: 15rpx;
      }
      
      .title-text {
        font-size: var(--tn-font-size-lg, 36rpx);
        font-weight: var(--tn-font-weight-bold, bold);
        color: var(--tn-text-color-1, #333333);
      }
    }
    
    .header-stats {
      display: flex;
      margin-right: 80rpx;
      .stat-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-left: 30rpx;
        
        .stat-value {
          font-size: var(--tn-font-size-base, 32rpx);
          font-weight: var(--tn-font-weight-bold, bold);
          color: #42B476; /* 主题色 */
        }
        
        .stat-label {
          font-size: var(--tn-font-size-sm, 24rpx);
          color: var(--tn-text-color-3, #999999);
          margin-top: 5rpx;
        }
      }
    }
  }
  
  .card-legend {
    display: flex;
    justify-content: center;
    padding: 20rpx;
    margin-bottom: 20rpx;
    background-color: #FFFFFF;
    border-radius: var(--tn-border-radius-lg, 20rpx);
    box-shadow: var(--tn-box-shadow-sm, 0 4rpx 20rpx rgba(0, 0, 0, 0.05));
    flex-wrap: wrap;
    
    .legend-item {
      display: flex;
      align-items: center;
      margin: 0 15rpx;
      margin-bottom: 10rpx;
      
      .legend-dot {
        width: 24rpx;
        height: 24rpx;
        border-radius: 50%;
        margin-right: 10rpx;
        
        &.answered-dot {
          background-color: #42B476; /* 主题色 */
        }
        
        &.unanswered-dot {
          background-color: var(--tn-bg-gray--light, #EEEEEE);
        }
        
        &.correct-dot {
          background-color: var(--tn-color-green, #52C41A);
        }
        
        &.wrong-dot {
          background-color: var(--tn-color-red, #FF4D4F);
        }
      }
      
      .legend-text {
        font-size: var(--tn-font-size-sm, 26rpx);
        color: var(--tn-text-color-2, #666666);
      }
    }
  }
  
  .card-content {
    flex: 1;
    padding: 0 10rpx;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    
    .question-type-section {
      margin-bottom: 20rpx;
      background-color: #FFFFFF;
      border-radius: var(--tn-border-radius-lg, 20rpx);
      overflow: hidden;
      box-shadow: var(--tn-box-shadow-sm, 0 4rpx 20rpx rgba(0, 0, 0, 0.05));
      
      .question-type-header {
        display: flex;
        align-items: center;
        padding: 25rpx 30rpx;
        background-color: var(--tn-bg-gray--light, #F8F9FA);
        border-bottom: 1rpx solid var(--tn-border-color, #F0F0F0);
        
        .tn-icon-subjects {
          color: #42B476; /* 主题色 */
          font-size: var(--tn-font-size-base, 32rpx);
          margin-right: 15rpx;
        }
        
        .type-label {
          font-size: var(--tn-font-size-base, 32rpx);
          font-weight: var(--tn-font-weight-bold, bold);
          color: var(--tn-text-color-1, #333333);
          margin-right: 15rpx;
        }
      }
      
      .question-grid {
        display: flex;
        flex-wrap: wrap;
        padding: 20rpx;
        justify-content: flex-start;
        
        .question-item {
          width: 80rpx;
          height: 80rpx;
          display: flex;
          justify-content: center;
          align-items: center;
          margin: 10rpx;
          border-radius: var(--tn-border-radius-base, 16rpx);
          font-size: var(--tn-font-size-base, 28rpx);
          font-weight: var(--tn-font-weight-medium, 500);
          transition: all var(--tn-transition-duration, 0.3s) ease;
          user-select: none;
          position: relative;
          
          &.current-question {
            border: 3rpx solid #42B476; /* 主题色 */
            transform: scale(1.1);
            background-color: rgba(66, 180, 118, 0.1);
          }
          
          &.answered {
            background-color: #42B476; /* 主题色 */
            color: #FFFFFF;
          }
          
          &.unanswered {
            background-color: var(--tn-bg-gray--light, #F0F0F0);
            color: var(--tn-text-color-3, #999999);
          }
          
          &.correct {
            background-color: var(--tn-color-green, #52C41A);
            color: #FFFFFF;
          }
          
          &.wrong {
            background-color: var(--tn-color-red, #FF4D4F);
            color: #FFFFFF;
          }
          
          /* 移除:active伪类，使用小程序内置的hover-*属性代替 */
        }
      }
    }
  }
  
  .card-footer {
    display: flex;
    justify-content: space-between;
    padding: 30rpx 20rpx;
    background-color: #FFFFFF;
    border-radius: var(--tn-border-radius-lg, 20rpx);
    box-shadow: var(--tn-box-shadow-sm, 0 -4rpx 20rpx rgba(0, 0, 0, 0.05));
    
    .footer-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all var(--tn-transition-duration, 0.3s) ease;
      
      /* 移除:active伪类，使用小程序内置的hover-*属性代替 */
    }
  }
}

// 响应式样式- 小屏
@media screen and (max-width: 320px) {
  .answer-card {
    padding: 16rpx;
    
    .card-header {
      padding: 20rpx 15rpx;
      margin-bottom: 15rpx;
      
      .header-title {
        /* 这里的 .tn-color-blue 已经被移除，颜色通过 :style 动态绑定 */
        .tn-icon-edit {
          font-size: var(--tn-font-size-base, 30rpx);
          margin-right: 10rpx;
        }
        
        .title-text {
          font-size: var(--tn-font-size-base, 30rpx);
        }
      }
      
      .header-stats {
        .stat-item {
          margin-left: 20rpx;
          
          .stat-value {
            font-size: var(--tn-font-size-sm, 28rpx);
          }
          
          .stat-label {
            font-size: 20rpx;
          }
        }
      }
    }
    
    .card-legend {
      padding: 15rpx;
      margin-bottom: 15rpx;
      
      .legend-item {
        margin: 0 10rpx;
        margin-bottom: 8rpx;
        
        .legend-dot {
          width: 20rpx;
          height: 20rpx;
          margin-right: 8rpx;
        }
        
        .legend-text {
          font-size: 22rpx;
        }
      }
    }
    
    .card-content {
      padding: 0 5rpx;
      
      .question-type-section {
        margin-bottom: 15rpx;
        
        .question-type-header {
          padding: 20rpx 25rpx;
          
          /* 图标颜色通过 :style 动态绑定 */
          .tn-icon-document-text {
            font-size: var(--tn-font-size-sm, 28rpx);
            margin-right: 10rpx;
          }
          
          .type-label {
            font-size: var(--tn-font-size-sm, 28rpx);
          }
        }
        
        .question-grid {
          padding: 15rpx;
          
          .question-item {
            width: 70rpx;
            height: 70rpx;
            margin: 8rpx;
            border-radius: var(--tn-border-radius-sm, 14rpx);
            font-size: 24rpx;
          }
        }
      }
    }
    
    .card-footer {
      padding: 20rpx 15rpx;
      
      .footer-btn {
        height: 70rpx !important;
        font-size: 24rpx !important;
      }
    }
  }
}

// 响应式样式- 大屏
@media screen and (min-width: 428px) {
  .answer-card {
    padding: 24rpx;
    
    .card-header {
      padding: 35rpx 30rpx;
      margin-bottom: 25rpx;
      
      .header-title {
        /* 颜色通过 :style 动态绑定 */
        .tn-icon-edit {
          font-size: var(--tn-font-size-xl, 40rpx);
          margin-right: 20rpx;
        }
        
        .title-text {
          font-size: var(--tn-font-size-xl, 40rpx);
        }
      }
      
      .header-stats {
        .stat-item {
          margin-left: 40rpx;
          
          .stat-value {
            font-size: var(--tn-font-size-lg, 36rpx);
          }
          
          .stat-label {
            font-size: var(--tn-font-size-sm, 26rpx);
          }
        }
      }
    }
    
    .card-legend {
      padding: 25rpx;
      margin-bottom: 25rpx;
      
      .legend-item {
        margin: 0 25rpx;
        margin-bottom: 12rpx;
        
        .legend-dot {
          width: 28rpx;
          height: 28rpx;
          margin-right: 12rpx;
        }
        
        .legend-text {
          font-size: var(--tn-font-size-base, 30rpx);
        }
      }
    }
    
    .card-content {
      padding: 0 15rpx;
      
      .question-type-section {
        margin-bottom: 25rpx;
        
        .question-type-header {
          padding: 30rpx 35rpx;
          
          /* 图标颜色通过 :style 动态绑定 */
          .tn-icon-document-text {
            font-size: var(--tn-font-size-lg, 36rpx);
            margin-right: 20rpx;
          }
          
          .type-label {
            font-size: var(--tn-font-size-lg, 36rpx);
          }
        }
        
        .question-grid {
          padding: 25rpx;
          
          .question-item {
            width: 90rpx;
            height: 90rpx;
            margin: 12rpx;
            border-radius: var(--tn-border-radius-lg, 20rpx);
            font-size: var(--tn-font-size-lg, 32rpx);
          }
        }
      }
    }
    
    .card-footer {
      padding: 35rpx 30rpx;
      
      .footer-btn {
        height: 90rpx !important;
        font-size: var(--tn-font-size-lg, 32rpx) !important;
      }
    }
  }
}

// 横屏适配
@media screen and (orientation: landscape) {
  .answer-card {
    
    .card-header {
      padding: 20rpx 25rpx;
    }
    
    .card-content {
      .question-grid {
        .question-item {
          width: 75rpx;
          height: 75rpx;
          margin: 10rpx;
        }
      }
    }
    
    .card-footer {
      padding: 20rpx 25rpx;
      
      .footer-btn {
        height: 80rpx !important;
      }
    }
  }
}

// 超高屏幕适配
@media screen and (min-height: 812px) {
  .answer-card {
    
    .card-content {
      flex: 1;
    }
    
    .question-type-section {
      
      .question-grid {
        max-height: none;
      }
    }
  }
}

// 深色模式支持
@media (prefers-color-scheme: dark) {
  .answer-card {
    background-color: var(--tn-bg-gray-dark, #1A1A1A);
    
    .card-header,
    .card-legend,
    .card-footer {
      background-color: var(--tn-bg-gray-darker, #2A2A2A);
      box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.2);
    }
    
    .title-text,
    .stat-value,
    .type-label {
      color: var(--tn-text-color-white, #FFFFFF);
    }
    
    .stat-label,
    .legend-text {
      color: var(--tn-text-color-gray, #AAAAAA);
    }
    
    .question-type-section {
      background-color: var(--tn-bg-gray-darker, #2A2A2A);
      
      .question-type-header {
        background-color: var(--tn-bg-gray-dark, #1A1A1A);
        border-bottom-color: var(--tn-border-color-dark, #3A3A3A);
      }
      
      .question-grid {
        background-color: var(--tn-bg-gray-darker, #2A2A2A);
        
        .question-item {
          &.unanswered {
            background-color: var(--tn-bg-gray-dark, #1A1A1A);
            color: var(--tn-text-color-gray, #AAAAAA);
          }
          
          &.current-question {
            background-color: rgba(14, 125, 255, 0.2);
          }
        }
      }
    }
  }
}
</style>

