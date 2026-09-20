<template>
  <!-- 问答题选项 -->
  <view class="write-option-container">

    <!-- 答案输入区域 -->
    <view class="answer-input-container tn-bg-white tn-padding-left tn-padding-right tn-padding-bottom">
      <!-- <view class="tn-flex tn-flex-direction-row tn-flex-col-center">
        <view class="content__data tn-flex-1 tn-margin-xs">
          <tn-input 
            v-model="userAnswer" 
            type="textarea" 
            placeholder="请输入你的答案" 
            :border="true" 
            auto-height="true"
            @input="onAnswerInput"
            @blur="onAnswerBlur"
          />
        </view>
        <tn-button 
          v-if="!question.is_submitted"
          shape="round" 
          background-color="tn-cool-bg-color-9" 
          width="100%" 
          shadow 
          :disabled="currentMode === 'reviewOnly'"
          @click="submitAnswer"
        >
          <text
            class="tn-color-white"
            hover-class="tn-hover"
            :hover-stay-time="150"
          >
            提交
          </text>
        </tn-button>
      </view> -->

      <view class="popup-container">
        <view class="textarea-wrapper">
          <textarea
            v-model="userAnswer"
            maxlength="300"
            placeholder="请输入答案..."
            placeholder-style="color:#AAAAAA"
            class="comment-textarea"
            auto-height="true"
            @input="onAnswerInput"
            @blur="onAnswerBlur"
          />
        </view>
        <!-- 提交按钮  靠右显示-->
        <view class="popup-footer">
          <tn-button 
           v-if="!question.is_submitted"
            backgroundColor="tn-cool-bg-color-9"
            shape="round" 
            fontColor="tn-color-white"
            fontSize="30"
            width="100%"
            size="lg"
            :shadow="true"
            :disabled="currentMode === 'reviewOnly'"
            @click="submitAnswer"
          >
            {{ submittingAnswer ? '提交中...' : '提 交' }}
          </tn-button>
        </view>
      </view>
    </view>

    <!-- 用户答案显示区域 -->
    <view
      v-if="question.is_submitted"
      class="user-answer-section tn-bg-white"
    >
      <view class="section-header tn-flex">
        <text class="tn-icon-edit-write tn-color-blue"/>
        <view class="section-title tn-text-bold tn-text-xl">
          我的作答
        </view>
      </view>
      <view class="answer-content">
        <mp-html
          v-if="question.user_answer"
          :content="question.user_answer"
        />
        <view v-else class="no-answer">
          <text>未作答</text>
        </view>
      </view>
    </view>
    
    <!-- 图鸟UI确认提示框 -->
    <tn-modal
      v-model="showConfirmModal"
      :width="'70%'"
      :title="'确认提交'"
      :content="confirmContent"
      :button="[
        { text: '取消', backgroundColor: '#F5F5F5', fontColor: '#333333' },
        { text: '确定', backgroundColor: mainColor, fontColor: '#FFFFFF' }
      ]"
      :mask-closeable="true"
      @click="handleModalClick"
      @cancel="showConfirmModal = false"
    />
  </view>
</template>

<script>
export default {
  name: 'WriteOption',
  props: {
    question: {
      type: Object,
      required: true
    },
    questionIndex: {
      type: Number,
      required: true
    },
    currentMode: {
      type: String,
      default: 'normal' // normal, learnPractice, reviewOnly
    },
    showTitle: {
      type: Boolean,
      default: true
    }
  },
  data() {
    return {
      mainColor: getApp().globalData.mainColor || '#1E88E5',
      userAnswer: '',
      showConfirmModal: false,
      confirmContent: '',
      submittingAnswer: false
    }
  },
  computed: {
    correctAnswer() {
      if (!this.question) return '';
      if (Array.isArray(this.question.answer)) {
        return this.question.answer.join(', ');
      }
      return this.question.answer || '';
    },
    showStarLevel() {
      // 根据 exam_level 值返回对应的星级：1→1星，2→2.5星，3→5星
      const examLevel = Number(this.question.exam_level);
      // 确保返回值始终是数字
      if (isNaN(examLevel)) {
        return 0;
      }
      switch (examLevel) {
        case 1:
          return 1
        case 2:
          return 2.5
        case 3:
          return 5
        default:
          return 0
      }
    }
  },
  watch: {
    'question.user_answer'(newVal) {
      // 监听question.user_answer的变化，同步到本地userAnswer
      if (newVal !== this.userAnswer) {
        this.userAnswer = newVal || ''
      }
    }
  },
  created() {
    // 初始化用户答案
    if (this.question && this.question.user_answer) {
      this.userAnswer = this.question.user_answer;
    }
    
    // 初始化背题模式状态
    this.initReviewMode();
  },
  methods: {
    onAnswerInput(e) {
      try {
        if (!this.question) return;
        
        // 处理输入事件
        const value = (e && e.detail && typeof e.detail.value === 'string') ? e.detail.value : (typeof e === 'string' ? e : '');
        this.userAnswer = value;
        
        // 更新到question对象
        this.question.user_answer = value;
        
        // 触发父组件的答案输入事件
        this.$emit('answer-input', {
          questionIndex: this.questionIndex,
          answer: value,
          question: this.question
        });
      } catch (error) {
        console.error('简答题输入处理失败:', error);
      }
    },
    
    onAnswerBlur() {
      try {
        if (!this.question) return;
        
        // 处理失去焦点事件
        this.userAnswer = (this.userAnswer || '').trim();
        this.question.user_answer = this.userAnswer;
        
        // 触发父组件的答案失去焦点事件
        this.$emit('answer-blur', {
          questionIndex: this.questionIndex,
          answer: this.userAnswer,
          question: this.question
        });
      } catch (error) {
        console.error('简答题失去焦点处理失败:', error);
      }
    },
    
    submitAnswer() {
      try {
        if (!this.question) {
          this.$emit('show-toast', '题目数据加载失败');
          return;
        }
        
        // 检测用户是否已填写答案
        const trimmedAnswer = (this.userAnswer || '').trim();
        const hasAnswer = trimmedAnswer.length > 0;
        
        // 确定弹窗内容
        this.confirmContent = hasAnswer ? '提交后不可修改！' : '未作答，确定提交吗？';
        
        // 显示图鸟UI确认提示框
        this.showConfirmModal = true;
      } catch (error) {
        console.error('提交简答题答案失败:', error);
        this.$emit('show-toast', '提交失败，请重试');
      }
    },
    
    // 处理图鸟UI模态框按钮点击事件
    handleModalClick({ index }) {
      // 关闭模态框
      this.showConfirmModal = false;
      
      // 用户点击确定按钮（索引为1）
      if (index === 1) {
        // 设置提交中状态
        this.submittingAnswer = true;
        
        // 检测用户是否已填写答案
        const trimmedAnswer = (this.userAnswer || '').trim();
        
        try {
          // 执行提交操作
          // 标记题目为已提交和已选择
          this.$set(this.question, 'is_submitted', true);
          this.$set(this.question, 'is_selected', true);
          this.$set(this.question, 'is_answered', true);
          this.$set(this.question, 'user_answer', trimmedAnswer);
          
          // 显示答案
          this.showAnswerAfterSubmission();
          
          // 触发父组件的答案提交事件
          this.$emit('answer-submitted', {
            questionIndex: this.questionIndex,
            question: this.question
          });
          
          // 根据当前模式处理后续逻辑
          this.handleSubmissionByMode();
        } finally {
          // 无论成功失败，都关闭提交中状态
          this.submittingAnswer = false;
        }
      } else {
        // 用户点击取消按钮（索引为0）
        console.log('用户取消提交');
      }
    },
    
    // 根据模式处理提交后的逻辑
    handleSubmissionByMode() {
      switch (this.currentMode) {
        case 'normal':
          // 答题模式：点击提交按钮后跳转下一题目
          this.$nextTick(() => {
            setTimeout(() => {
              this.$emit('next-question', {
                questionIndex: this.questionIndex
              });
            }, 300);
          });
          break;
        case 'learnPractice':
          // 学练结合模式：点击提交按钮后，显示试题解析组件和做题笔记组件（不显示作答显示区域）
          // 触发父组件显示解析和笔记组件
          this.$emit('answer-shown', {
            questionIndex: this.questionIndex,
            question: this.question,
          });
          break;
        case 'reviewOnly':
          // 背题模式：进入题目后直接显示相关组件
          break;
        default:
          break;
      }
    },
    
    showAnswerAfterSubmission() {
      try {
        if (!this.question) return;
        
        // 标记题目为已提交和已选择
        this.question.is_submitted = true;
        this.question.is_selected = true;
        this.question.is_answered = true;
        
        // 判断答案是否正确
        if (this.question.answer) {
          // 更智能的答案比较逻辑
          const userAnswer = (this.userAnswer || '').trim().toLowerCase();
          const correctAnswer = (this.question.answer || '').trim().toLowerCase();
          
          // 完全匹配
          let isCorrect = userAnswer === correctAnswer;
          
          // 对于问答题，我们不自动判断对错，只显示参考答案
          // 用户需要自己判断答案是否正确
          // isCorrect = true; // 可选：默认认为填写了就正确
          
        }
        
        // 触发父组件的答案显示事件
        this.$emit('answer-shown', {
          questionIndex: this.questionIndex,
          question: this.question,
        });
      } catch (error) {
        console.error('显示简答题答案失败:', error);
      }
    },
    
    // 初始化背题模式状态
    initReviewMode() {
      try {
        if (this.currentMode === 'reviewOnly' && this.question) {
          // 背题模式：进入题目后直接显示相关组件
          this.question.is_submitted = true;
          this.question.is_selected = true;
          this.question.is_answered = true;
          
          // 处理正确答案：统一转换为数组格式
          let correctAnswers = [];
          if (Array.isArray(this.question.answer)) {
            correctAnswers = this.question.answer;
          } else if (typeof this.question.answer === 'string') {
            try {
              // 尝试解析JSON格式的字符串
              let parsedAnswer = this.question.answer;
              let parseAttempts = 0;
              const maxParseAttempts = 2;
              
              // 尝试解析最多2次，处理双字符串化的情况
              while (typeof parsedAnswer === 'string' && parseAttempts < maxParseAttempts) {
                try {
                  parsedAnswer = JSON.parse(parsedAnswer);
                  parseAttempts++;
                } catch (e) {
                  break;
                }
              }
              
              // 处理解析结果
              if (Array.isArray(parsedAnswer)) {
                correctAnswers = parsedAnswer;
              } else if (typeof parsedAnswer === 'string') {
                // 解析后是单个字符串，转换为数组
                correctAnswers = [parsedAnswer];
              } else {
                // 其他类型，直接转换为数组
                correctAnswers = [String(parsedAnswer)];
              }
            } catch (e) {
              // 如果解析失败，直接使用原字符串
              correctAnswers = [this.question.answer];
            }
          }
          
          // 标准化正确答案：去除空格、过滤空值
          const normalizedCorrectAnswers = correctAnswers
            .map(ans => {
              // 确保ans是字符串
              const strAns = String(ans);
              // 去除所有空格和引号
              return strAns.replace(/[\s"']/g, '');
            })
            .filter(Boolean);
          
          // 背题模式：自动将正确答案设置为用户答案
          if (normalizedCorrectAnswers.length > 0) {
            const correctAnswer = normalizedCorrectAnswers[0];
            this.question.user_answer = correctAnswer;
            this.userAnswer = correctAnswer;
          }
          
          // 背题模式下默认认为答案正确
          const isCorrect = true;
          this.question.is_correct = isCorrect;
          
          // 强制触发视图更新
          this.$forceUpdate();

          // 通过多层 $nextTick 确保DOM完全更新后再触发事件
          this.$nextTick(() => {
            // 再次强制更新，确保所有状态变化都被应用
            this.$forceUpdate();
            
            this.$nextTick(() => {
              // 触发 answer-shown 事件，让父组件更新题目状态
              this.$emit('answer-shown', {
                questionIndex: this.questionIndex,
                question: this.question,
                isCorrect: isCorrect
              });
              
              // 触发输入事件，确保父组件同步状态
              if (this.question.user_answer) {
                this.$emit('answer-input', {
                  questionIndex: this.questionIndex,
                  answer: this.question.user_answer,
                  question: this.question
                });
              }
            });
          });
        }
      } catch (error) {
        console.error('初始化背题模式失败:', error);
      }
    },
  }
}
</script>

<style lang="scss" scoped>
.question-content {
  padding: 20rpx 30rpx;
  margin: 20rpx 20rpx 0 20rpx;
  .question-type-tag {
    font-weight: bold;
  }
}

.answer-input-container {
   margin: 0 20rpx;
  .content__data {
    flex: 1;
  }
  
  .tn-button {
    margin-top: 20rpx;
  }
}

.user-answer-section {
  margin: 0 20rpx;
  
  .tn-text-xl {
    font-weight: bold;
  }
  
  .answer-status {
    font-size: 28rpx;
    font-weight: bold;
    padding: 4rpx 16rpx;
    border-radius: 16rpx;
  }
  
  .correct-status {
    background-color: #F6FFED;
    color: #52C41A; /* 正确状态文字为绿色 */
    border: 1px solid #52C41A; /* 答对的边框设为绿色 */
  }
  
  .wrong-status {
    background-color: #FFF2F0;
    color: #FF4D4F; /* 错误状态文字为红色 */
    border: 1px solid #FF4D4F; /* 答错的边框设为红色 */
  }
}

.answer-section {
  margin-top: 20rpx;
  
  .answer-status {
    font-size: 32rpx;
    font-weight: bold;
  }
  
  .correct-answer {
    margin-top: 20rpx;
    
    .tn-text-bold {
      margin-right: 10rpx;
    }
  }
  
  .analysis-section, .comment-section {
    margin-top: 30rpx;
    
    .tn-text-bold {
      display: block;
      margin-bottom: 10rpx;
    }
  }
}

/* 答案输入样式 start*/
.popup-container {
  display: flex;
  flex-direction: column;

  height: 100%;
}

.textarea-wrapper {
  flex: 1;
  background-color: #F8F9FA;
  border-radius: 12rpx;
  padding: 20rpx;
  margin-bottom: 30rpx;
  
  .comment-textarea {
    width: 100%;
    height: 150rpx;
    resize: none;
    overflow: hidden;
    font-size: 28rpx;
    line-height: 1.5;
  }
}

.popup-footer {
  display: block;
  gap: 20rpx;
}
/* 答案输入样式 end*/

  // 区域标题样式
  .section-header {
    display: flex;
    align-items: center;
    gap: 15rpx;
    padding:20rpx 30rpx;
    background: linear-gradient(135deg, #F8F9FA 0%, #FFFFFF 100%);
    border-bottom: 2rpx solid #F0F0F0;
  }
  
  .section-title {
    font-size: 30rpx;
    font-weight: bold;
    color: #333333;
  }
  
  // 作答内容样式
  .answer-content, .correct-answer-content {
    padding: 15rpx;
    transition: all 0.3s ease;
    border-left: 6rpx solid #52C41A;
  }
  
  .no-answer {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #999999;
    font-size: 28rpx;
  }
  .blogger {
    &__desc {
      line-height: 45rpx;
      
      &__label {
        padding: 0 20rpx;
        &--prefix {
          padding-right: 10rpx;
        }
      }
    }
  }
</style>

