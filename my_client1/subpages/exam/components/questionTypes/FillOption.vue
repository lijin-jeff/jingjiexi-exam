<template>
  <!-- 填空题组件-->
  <view class="fill-option-container">

    <!-- 答案输入区域 -->
    <view class="answer-input-area tn-bg-white tn-padding-left tn-padding-right tn-padding-bottom">
      <view class="tn-flex tn-flex-direction-row tn-flex-col-center">
        <view class="input-wrapper tn-flex-1 tn-margin-xs">
          <tn-input
            v-model="userAnswer"
            type="textarea"
            placeholder="请输入填空答案"
            :border="true"
            auto-height="true"
            @input="onAnswerInput"
            @blur="onAnswerBlur"
          />
        </view>
        <!-- 考试模式且未提交时显示提交按钮 -->
        <view
          v-if="currentMode === 'normal' && !question.is_submitted"
          class="submit-btn tn-margin-xs"
          style="display: block;"
        >
          <tn-button
            shape="round"
            fontColor="tn-color-white"
            fontSize="30"
            width="100%"
            size="lg"
            :shadow="true"
            background-color="tn-cool-bg-color-9"
            @click="submitAnswer"
          >
            <text class="tn-color-white">
              提交答案
            </text>
          </tn-button>
        </view>
      </view>
    </view>

    <!-- 用户答案显示区域 -->
    <view
      v-if="question.is_submitted"
      class="user-answer-section tn-bg-white tn-margin-top tn-padding-left tn-padding-right tn-padding-bottom"
    >
      <view class="tn-flex tn-flex-row-between">
        <view class="justify-content-item tn-text-bold tn-text-xl">
          我的作答
        </view>
      </view>
      <view class="tn-padding-xs">
        <mp-html
          v-if="question.user_answer"
          :content="question.user_answer"
        />
        <view v-else>
          <text>未作答</text>
        </view>
      </view>
    </view>

    <!-- 答案显示区域 -->
    <view
      v-if="question.is_submitted"
      class="answer-section tn-bg-white tn-margin-top tn-padding"
    >
      <view class="answer-status tn-margin-bottom">
        <text :class="question.is_correct ? 'tn-color-green--dark' : 'tn-color-red--dark'">
          {{ question.is_correct ? '回答正确' : '回答错误' }}
        </text>
      </view>
      
      <view class="correct-answer tn-margin-bottom">
        <text class="tn-text-bold">
          正确答案
        </text>
        <text class="tn-color-green--dark">
          {{ correctAnswer }}
        </text>
      </view>
      
      <view
        v-if="question.analysis"
        class="analysis-section tn-margin-top"
      >
        <text class="tn-text-bold">
          解析
        </text>
        <mp-html :content="question.analysis" />
      </view>
      
      <view
        v-if="question.comment"
        class="comment-section tn-margin-top"
      >
        <text class="tn-text-bold">
          名师点评
        </text>
        <mp-html :content="question.comment" />
      </view>
    </view>
  </view>
</template>

<script>
export default {
  name: 'FillOption',
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
      userAnswer: ''
    }
  },
  computed: {
    correctAnswer() {
      if (!this.question || !Array.isArray(this.question.answer)) return ''
      return this.question.answer.join(',')
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
    // 监听question变化，更新userAnswer
    question: {
      handler(newVal) {
        if (newVal && newVal.user_answer) {
          this.userAnswer = newVal.user_answer
        }
        // 背题模式下，当题目切换时重新初始化
        if (this.currentMode === 'reviewOnly') {
          this.$nextTick(() => {
            this.initReviewMode();
          });
        }
      },
      immediate: true
    }
  },
  mounted() {
    // 背题模式下自动初始化
    if (this.currentMode === 'reviewOnly') {
      this.$nextTick(() => {
        this.initReviewMode();
      });
    }
  },
  methods: {
    // 答案输入事件处理
    onAnswerInput(e) {
      let val = ''
      try {
        if (e && e.detail && typeof e.detail.value === 'string') {
          val = e.detail.value
        } else if (typeof e === 'string') {
          val = e
        }
      } catch (error) {
        console.error('处理输入值时出错:', error)
        val = ''
      }
      
      this.userAnswer = val
      
      // 更新question中的user_answer
      if (this.question) {
        this.question.user_answer = val
      }
      
      // 触发父组件的输入事件
      this.$emit('answer-input', {
        questionIndex: this.questionIndex,
        answer: val,
        question: this.question
      })
    },
    
    // 答案失焦事件处理
    onAnswerBlur() {
      const trimmedAnswer = this.userAnswer ? this.userAnswer.trim() : ''
      
      // 更新question中的user_answer
      if (this.question) {
        this.question.user_answer = trimmedAnswer
      }
      
      // 触发父组件的失焦事件
      this.$emit('answer-blur', {
        questionIndex: this.questionIndex,
        answer: trimmedAnswer,
        question: this.question
      })
    },
    
    // 提交答案
    submitAnswer() {
      try {
        // 验证question对象
        if (!this.question || typeof this.question !== 'object') {
          console.warn('submitAnswer: question对象不存在或无效');
          this.$emit('show-toast', '题目数据异常')
          return
        }
        
        // 检查用户输入是否有效
        let userInputValue = this.userAnswer
        let isValidInput = false
        
        if (userInputValue) {
          if (Array.isArray(userInputValue)) {
            // 处理数组形式的输入，检查是否有非空项
            isValidInput = userInputValue.some(item => item && String(item).trim() !== '')
          } else if (typeof userInputValue === 'string') {
            // 处理字符串形式的输入，检查是否非空
            isValidInput = userInputValue.trim() !== ''
          } else {
            // 其他类型，转换为字符串检查是否非空
            isValidInput = String(userInputValue).trim() !== ''
          }
        }
        
        // 如果没有填写答案，提示用户输入
        if (!isValidInput) {
          this.$emit('show-toast', '请输入答案后再提交')
          return
        }
        
        // 记录提交前的状态，用于错误恢复
        const beforeSubmitState = {
          userAnswer: this.userAnswer,
          user_answer: this.question.user_answer,
          is_submitted: this.question.is_submitted,
          is_selected: this.question.is_selected,
          is_correct: this.question.is_correct
        }
        
        // 设置用户答案（确保是字符串格式）
        const trimmedAnswer = String(userInputValue).trim()
        this.$set(this.question, 'user_answer', trimmedAnswer)
        
        // 标记题目为已提交和已选择
        this.$set(this.question, 'is_submitted', true)
        this.$set(this.question, 'is_selected', true)
        this.$set(this.question, 'is_answered', true)
        this.$set(this.question, 'submitted_at', new Date().toISOString())
        
        // 判断答案是否正确
        try {
          if (this.question.answer && Array.isArray(this.question.answer)) {
            // 填空题答案比较逻辑
            const userAnswer = trimmedAnswer
            const correctAnswers = this.question.answer.map(ans => {
              if (ans === null || ans === undefined) return ''
              return String(ans).trim()
            }).filter(ans => ans !== '')
            
            // 检查用户答案是否在正确答案列表中（不区分大小写）
            this.question.is_correct = correctAnswers.some(correctAnswer => 
              userAnswer.toLowerCase() === correctAnswer.toLowerCase()
            )
          } else {
            console.warn('submitAnswer: 题目答案格式不正确或缺失');
            this.question.is_correct = false
          }
        } catch (compareError) {
          console.error('答案比较过程中发生错误:', compareError);
          // 恢复到提交前的状态
          Object.assign(this.question, {
            user_answer: beforeSubmitState.user_answer,
            is_submitted: beforeSubmitState.is_submitted,
            is_selected: beforeSubmitState.is_selected,
            is_correct: beforeSubmitState.is_correct
          })
          this.userAnswer = beforeSubmitState.userAnswer
          
          this.$emit('show-toast', '答案验证失败，请重试')
          return
        }
        
        // 触发父组件的提交事件
        this.$emit('answer-submitted', {
          questionIndex: this.questionIndex,
          question: this.question,
          result: {
            isCorrect: this.question.is_correct,
            userAnswer: trimmedAnswer
          }
        })
        
        // 根据当前模式处理后续逻辑
        if (this.currentMode === 'normal') {
          // 考试模式：提交后跳转下一题目
          this.$nextTick(() => {
            this.$emit('next-question')
          })
        } else if (this.currentMode === 'learnPractice') {
          // 学练结合：提交后显示答案、解析、名师点评
          this.showAnswerAfterSubmission()
        } else {
          // 背题模式：已自动展示，无需额外处理
          this.$emit('show-toast', '请查看标准答案和解析')
        }
      } catch (error) {
        console.error('提交填空题答案时发生错误:', error)
        // 显示错误提示
        this.$emit('show-toast', '提交答案失败，请重试')
      }
    },
    
    // 提交后显示答案、解析、名师点评
    showAnswerAfterSubmission() {
      // 答案显示已在submitAnswer中处理
      // 触发父组件的答案显示事件
      this.$emit('answer-shown', {
        questionIndex: this.questionIndex,
        question: this.question
      })
    },
    
    // 初始化背题模式状态
    initReviewMode() {
      try {
        if (this.currentMode === 'reviewOnly' && this.question) {
          // 背题模式：标记为已提交，但不阻止查看
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
              // 如果解析失败，直接清理并分割
              const cleanedAnswer = this.question.answer.replace(/[\[\]"']/g, '');
              correctAnswers = cleanedAnswer.split(/[,，、]/);
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
        console.error('初始化背题模式状态失败:', error);
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.question-content {
  .question-type-tag {
    font-weight: bold;
  }
}

.answer-input-area {
  .input-wrapper {
    flex: 1;
  }
  
  .submit-btn {
    width: 120rpx;
  }
}

.user-answer-section {
  .tn-text-bold {
    font-size: 32rpx;
  }
  
  .tn-padding-xs {
    font-size: 28rpx;
    line-height: 1.5;
  }
}

.answer-section {
  .answer-status {
    font-size: 32rpx;
    font-weight: bold;
  }
  
  .correct-answer {
    font-size: 28rpx;
    line-height: 1.5;
  }
  
  .analysis-section,
  .comment-section {
    font-size: 28rpx;
    line-height: 1.5;
  }
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

