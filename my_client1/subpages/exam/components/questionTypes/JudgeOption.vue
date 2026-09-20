<template>
  <!-- 判断题组件-->
  <view class="judge-option-container">

    <!-- 选项 -->
    <view class="options-list tn-bg-white tn-padding-left tn-padding-right tn-padding-bottom">
      
      <view
        v-for="(optionItem, optionIndex) in question.option"
        :key="optionIndex"
        class="option-item tn-flex tn-flex-direction-row tn-flex-col-center"
        :class="[
          optionItem.status === 'selected' ? 'selected' : '',
          question.is_submitted ? (optionItem.is_user_correct ? 'correct' : 
            optionItem.is_user_wrong ? 'wrong' : 
            optionItem.is_correct ? 'correct' : '') : ''
        ]"
        @click="handleOptionClick(optionIndex)"
      >
        
        <view class="tn-flex tn-flex-direction-row tn-flex-col-center tn-padding-xs">
          <view
            :class="[
              'option-label',
              optionItem.status === 'selected' ? 'label-selected' : '',
              question.is_submitted ? (optionItem.is_user_correct ? 'label-correct' : 
                optionItem.is_user_wrong ? 'label-wrong' : 
                optionItem.is_correct ? 'label-correct' : '') : ''
            ]"
          >
            {{ optionItem.check }}.
          </view>
          <view class="option-text">
            <mp-html :content="optionItem.title" />
          </view>
        </view>
      </view>
    </view>
  </view>
</template>

<script>
export default {
  name: 'JudgeOption',
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
    }
  },
  computed: {
    correctAnswer() {
      if (!this.question || !this.question.answer || !Array.isArray(this.question.answer)) return '';
      return this.question.answer.join(',');
    },
    
    // 获取用户答案
    userAnswer() {
      if (!this.question || !this.question.option || !Array.isArray(this.question.option)) return '';
      const selectedOption = this.question.option.find(opt => opt && opt.status === 'selected');
      return selectedOption ? selectedOption.check : '';
    },
    
    // 显示星级数量
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
    // 监听问题数据变化，重置组件状态
    'question': {
      handler(newVal) {
        // 确保选项状态正确初始化
        this.initializeOptionStatus();
        // 背题模式下，当题目切换时重新初始化
        if (this.currentMode === 'reviewOnly') {
          this.$nextTick(() => {
            this.initReviewMode();
          });
        }
      },
      deep: true,
      immediate: true
    }
  },
  mounted() {
    // 确保选项状态正确初始化
    this.initializeOptionStatus();
    
    // 背题模式下自动显示正确答案
    if (this.currentMode === 'reviewOnly') {
      this.$nextTick(() => {
        this.initReviewMode();
      });
    }
  },
  methods: {
    // 初始化选项状态
    initializeOptionStatus() {
      if (this.question && this.question.option && Array.isArray(this.question.option)) {
        this.question.option.forEach((opt, index) => {
          if (opt) {
            // 使用$set确保响应式更新
            this.$set(opt, 'status', opt.status || '')
            this.$set(opt, 'is_user_correct', opt.is_user_correct || false)
            this.$set(opt, 'is_user_wrong', opt.is_user_wrong || false)
            this.$set(opt, 'is_correct', opt.is_correct || false)
          }
        })
      }
    },
    
    // 处理选项点击事件
    handleOptionClick(optionIndex) {
      
      // 在学练模式下，允许重新选择选项，即使题目已提交
      if (this.question.is_submitted && this.currentMode !== 'learnPractice') {
        return;
      }
          this.selectOption(optionIndex);
    },

    // 选择选项
    selectOption(optionIndex) {
      try {
        if (!this.question || !this.question.option || !Array.isArray(this.question.option)) {
          console.log('题目数据无效');
          return;
        }
        
        const selectedOption = this.question.option[optionIndex];
        if (!selectedOption) {
          console.log('选项数据无效');
          return;
        }
        
        // 判断题逻辑：清除所有选项的选中状态，只设置当前选项为选中
        this.question.option.forEach((opt, idx) => {
          if (opt) {
            // 使用$set确保响应式更新
            this.$set(opt, 'status', opt === selectedOption ? 'selected' : '')
          }
        });
        
        // 强制触发视图更新
        this.$forceUpdate();
        
        // 标记题目为已选择
        this.question.is_selected = true;
        this.$set(this.question, 'is_answered', true)
        // 关键修复：设置user_answer属性，确保AnswerDisplay组件能正确显示用户答案
        this.$set(this.question, 'user_answer', selectedOption.check)
        // 在学练模式下不立即标记为已提交，允许重新选择
        // 在其他模式下，标记为已提交
        if (this.currentMode !== 'learnPractice') {
          this.question.is_submitted = true;
        }
        
        // 强制更新组件状态
        this.$forceUpdate();
        
        // 根据当前模式处理后续逻辑
        this.handleSelectionByMode();
        
        // 触发父组件的选项选择事件
        this.$emit('option-selected', {
          questionIndex: this.questionIndex,
          optionIndex: optionIndex,
          question: this.question,
          selectedValue: selectedOption.check
        });
      } catch (error) {
        console.error('选择判断题选项失败:', error);
      }
    },
    
    // 根据模式处理选择后的逻辑
    handleSelectionByMode() {
      switch (this.currentMode) {
        case 'normal':
          // 答题模式：标记题目为已提交
          this.question.is_submitted = true
          this.question.is_selected = true
          this.question.is_answered = true
          
          // 设置 user_answer 字段，供答题卡识别已答状态
          const selectedOption = this.question.option.find(opt => opt && opt.status === 'selected')
          if (selectedOption && selectedOption.check) {
            this.question.user_answer = selectedOption.check
          }
          
          // 选中任一选项后自动跳转下一题目
          this.$nextTick(() => {
            // 答题模式下不显示正确/错误结果，直接跳转
            // 不调用 showAnswerAfterSelection()
            
            // 自动跳转下一题目
            setTimeout(() => {
              this.$emit('next-question', {
                questionIndex: this.questionIndex
              })
            }, 300)
          })
          break
        case 'learnPractice':
          // 学练结合模式：选中任一选项后，显示试题解析组件、作答显示组件、做题笔记组件
          // 在学练模式下不自动跳转，允许用户重新选择
          // 设置is_submitted为true以触发反馈组件显示
          this.question.is_submitted = true
          
          // 立即判断答案是否正确，显示解析、笔记等内容
          this.showAnswerAfterSelection()
          break
        case 'reviewOnly':
          // 背题模式：点击选项后显示答案判断结果
          this.question.is_submitted = true
          
          this.$nextTick(() => {
            // 判断答案是否正确
            this.showAnswerAfterSelection()
          })
          break
        default:
          break
      }
    },
    
    // 选中后显示答案判断结果
    showAnswerAfterSelection() {
      // 背题模式下，保持 initReviewMode 设置的 is_correct 状态，不重新计算
      if (this.currentMode !== 'reviewOnly' && this.question && this.question.answer) {
        // 查找用户选择的选项
        const selectedOption = this.question.option.find(opt => opt && opt.status === 'selected');
        if (selectedOption && selectedOption.check) {
          // 统一处理正确答案，转换为数组并标准化
          let correctAnswers = []
          if (Array.isArray(this.question.answer)) {
            correctAnswers = this.question.answer
          } else if (typeof this.question.answer === 'string') {
            // 处理字符串形式的答案
            try {
              // 尝试解析JSON格式的字符串
              const parsed = JSON.parse(this.question.answer)
              correctAnswers = Array.isArray(parsed) ? parsed : [this.question.answer.replace(/[\[\]\"']/g, '')]
            } catch (e) {
              // 如果解析失败，直接清理并分割
              correctAnswers = this.question.answer.replace(/[\[\]\"']/g, '').split(',')
            }
          }
          
          // 标准化用户答案和正确答案进行比较
          const normalizedUserAnswer = String(selectedOption.check).trim().toUpperCase()
          const normalizedCorrectAnswers = correctAnswers.map(ans => String(ans).trim().toUpperCase())
          
          const isCorrect = normalizedCorrectAnswers.includes(normalizedUserAnswer);
          this.$set(this.question, 'is_correct', isCorrect);
          
          // 更新选项状态
          this.updateOptionStates(isCorrect, selectedOption);
        }
      }
      
      // 触发父组件的答案显示事件
      this.$emit('answer-shown', {
        questionIndex: this.questionIndex,
        question: this.question,
        isCorrect: this.question.is_correct
      });
    },
    
    // 更新选项状态
    updateOptionStates(isCorrect, selectedOption) {
      // 统一处理正确答案，转换为数组并标准化
      let correctAnswers = []
      if (this.question.answer) {
        if (Array.isArray(this.question.answer)) {
          correctAnswers = this.question.answer
        } else if (typeof this.question.answer === 'string') {
          // 处理字符串形式的答案
          try {
            // 尝试解析JSON格式的字符串
            const parsed = JSON.parse(this.question.answer)
            correctAnswers = Array.isArray(parsed) ? parsed : [this.question.answer.replace(/[\[\]\"']/g, '')]
          } catch (e) {
            // 如果解析失败，直接清理并分割
            correctAnswers = this.question.answer.replace(/[\[\]\"']/g, '').split(',')
          }
        }
      }
      
      // 标准化正确答案数组，去除空格并转为大写
      const normalizedCorrectAnswers = correctAnswers.map(ans => String(ans).trim().toUpperCase())
      
      this.question.option.forEach(opt => {
        if (!opt) return;
        
        // 保存当前status值，确保选中状态不丢失
        const currentStatus = opt.status;
        
        // 重置状态，但保留status属性
        this.$set(opt, 'is_user_correct', false);
        this.$set(opt, 'is_user_wrong', false);
        this.$set(opt, 'is_correct', false);
        
        // 恢复保存的status值
        opt.status = currentStatus;
        
        // 标记正确答案 - 使用标准化的比较
        const optCheck = opt.check ? String(opt.check).trim().toUpperCase() : ''
        if (normalizedCorrectAnswers.includes(optCheck)) {
          this.$set(opt, 'is_correct', true);
        }
        
        // 标记用户选择的结果
        if (opt === selectedOption) {
          if (isCorrect) {
            this.$set(opt, 'is_user_correct', true);
          } else {
            this.$set(opt, 'is_user_wrong', true);
          }
        }
      });
      
      // 强制更新视图，确保样式正确应用
      this.$forceUpdate();
    },
    
    // 初始化背题模式状态（参考多选题组件）
    initReviewMode() {
      try {

        if (this.currentMode === 'reviewOnly' && this.question) {
          // 背题模式：标记为已提交，但不阻止查看
          // 注意：question 是 prop，直接修改可能被父组件重置
          // 但我们仍然尝试设置，并通过事件通知父组件
          this.question.is_submitted = true;
          this.question.is_selected = true;
          this.question.is_answered = true;
          
          // 生成 answer_str 字段（用于 AnswerDisplay 组件显示）
          if (this.question.answer && !this.question.answer_str) {
            // 处理正确答案：统一转换为数组格式
            let correctAnswers = [];
            if (Array.isArray(this.question.answer)) {
              correctAnswers = this.question.answer;
            } else if (typeof this.question.answer === 'string') {
              const answerStr = this.question.answer.trim();
              if (answerStr.startsWith('[') && answerStr.endsWith(']')) {
                try {
                  correctAnswers = JSON.parse(answerStr);
                } catch (e) {
                  correctAnswers = answerStr.replace(/[\[\]"']/g, '').split(',');
                }
              } else {
                correctAnswers = answerStr.split(',');
              }
            }
            this.question.answer_str = correctAnswers.join(', ');
          }
          
          // 处理正确答案：统一转换为数组格式，支持多种分隔符
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
                // 解析后是单个字符串，如 "D"，转换为数组
                correctAnswers = [parsedAnswer];
              } else {
                // 其他类型，直接转换为数组
                correctAnswers = [String(parsedAnswer)];
              }
            } catch (e) {
              // 如果解析失败，直接清理并分割，支持多种分隔符
              const cleanedAnswer = this.question.answer.replace(/[\[\]"']/g, '');
              // 支持逗号、顿号、中文逗号等多种分隔符
              correctAnswers = cleanedAnswer.split(/[,，、]/);
            }
          }
          
          // 标准化正确答案：去除空格、转换为大写、过滤空值
          const normalizedCorrectAnswers = correctAnswers
            .map(ans => {
              // 确保ans是字符串
              const strAns = String(ans);
              // 去除所有空格和引号
              const cleaned = strAns.replace(/[\s"']/g, '');
              // 转换为大写
              return cleaned.toUpperCase();
            })
            .filter(Boolean);
          
          // 标记正确答案并自动选择
          let selectedCorrectOption = null;
          if (this.question.option && Array.isArray(this.question.option)) {
            this.question.option.forEach((opt, index) => {
              if (opt && opt.check) {
                const normalizedOptionCheck = String(opt.check).trim().toUpperCase();
                const isCorrectOption = normalizedCorrectAnswers.some(ans => ans.includes(normalizedOptionCheck) || normalizedOptionCheck.includes(ans));
                
                if (isCorrectOption) {
                  opt.is_correct = true;
                  opt.status = 'selected';
                  selectedCorrectOption = opt;
                }
              }
            });
          }
          
          // 背题模式：自动将正确答案设置为用户答案
          if (selectedCorrectOption) {
            this.question.user_answer = selectedCorrectOption.check;
          } else if (normalizedCorrectAnswers.length > 0) {
            this.question.user_answer = normalizedCorrectAnswers[0];
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
              // 这样可以确保父组件的 swiperList 被正确更新
              this.$emit('answer-shown', {
                questionIndex: this.questionIndex,
                question: this.question,
                isCorrect: isCorrect
              });
              
              // 触发选项选择事件，确保父组件同步状态
              if (selectedCorrectOption && this.question.option) {
                const correctOptionIndex = this.question.option.findIndex(opt => opt === selectedCorrectOption);
                if (correctOptionIndex !== -1) {
                  this.$emit('option-selected', {
                    questionIndex: this.questionIndex,
                    optionIndex: correctOptionIndex,
                    question: this.question,
                    selectedValue: selectedCorrectOption.check
                  });
                }
              }
              
              // 如果 is_submitted 被重置，再次设置
              if (!this.question.is_submitted) {
                this.question.is_submitted = true;
                this.question.is_selected = true;
                this.question.is_answered = true;
                this.$forceUpdate();
              }
            });
          });
        } else {
          console.log('未满足条件，不执行 initReviewMode');
        }
      } catch (error) {
        console.error('初始化背题模式状态失败:', error);
      }
    },
    
    // 背题模式下自动显示正确答案（保留用于点击选项后的处理）
    autoShowCorrectAnswer() {
      
      if (!this.question || !this.question.answer || !this.question.option) {
        console.log('题目数据不完整，无法显示正确答案');
        return;
      }
      
      // 标记题目为已提交
      this.$set(this.question, 'is_submitted', true);
      
      // 统一处理正确答案，转换为数组并标准化
      let correctAnswers = []
      if (Array.isArray(this.question.answer)) {
        correctAnswers = this.question.answer
      } else if (typeof this.question.answer === 'string') {
        try {
          const parsed = JSON.parse(this.question.answer)
          correctAnswers = Array.isArray(parsed) ? parsed : [this.question.answer.replace(/[\[\]\"']/g, '')]
        } catch (e) {
          correctAnswers = this.question.answer.replace(/[\[\]\"']/g, '').split(',')
        }
      }
      
      // 标准化正确答案数组
      const normalizedCorrectAnswers = correctAnswers.map(ans => String(ans).trim().toUpperCase())
      
      // 更新所有选项的状态
      this.question.option.forEach(opt => {
        if (!opt) return;
        
        // 只在非背题模式下重置状态
        if (this.currentMode !== 'reviewOnly') {
          this.$set(opt, 'is_user_correct', false);
          this.$set(opt, 'is_user_wrong', false);
          this.$set(opt, 'is_correct', false);
          this.$set(opt, 'status', '');
        }
        
        // 标记正确答案
        const optCheck = opt.check ? String(opt.check).trim().toUpperCase() : ''
        if (normalizedCorrectAnswers.includes(optCheck)) {
          this.$set(opt, 'is_correct', true);
        }
      });
      
      // 强制触发视图更新
      this.$forceUpdate();
    },

  }
}
</script>

<style lang="scss" scoped>
.judge-option-container {
    background-color: #F5F5F5;
    padding: 20rpx;
}
.question-content {
    padding: 20rpx;
  .question-type-tag {
    font-weight: bold;
  }
}

.options-list {
    border-radius: 12rpx;
    box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
    padding: 20rpx;
    min-height: 100rpx; /* 确保即使没有选项也有高度 */
    background-color: #fff;
  .option-item {
    padding: 5rpx 20rpx;
    margin-bottom: 20rpx;
    border-radius: 12rpx;
    border: 1rpx solid #E6E6E6;
    transition: all 0.3s ease;
    cursor: pointer;
    
    &:last-child {
      margin-bottom: 0;
    }
    
    &.selected {
      background-color: #E6F7FF;
      border-color: #01BEFF;
    }
    
    &.correct {
      background-color: #F6FFED;
      border-color: #52C41A;
    }
    
    &.wrong {
      background-color: #FFF2F0;
      border-color: #FF4D4F;
    }
    
    &.disabled {
      cursor: not-allowed;
      opacity: 0.6;
    }
  
    .option-label {
       width: 50rpx;
      height: 50rpx;
      border-radius: 50%;
      border: 2rpx solid #D9D9D9;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 20rpx;
      font-weight: bold;
      font-size: 28rpx;
      transition: all 0.3s ease;
      
      &.label-selected {
        color: #01BEFF;
      }
      
      &.label-correct {
        color: #52C41A;
      }
      
      &.label-wrong {
        color: #FF4D4F;
      }
    }
    
    .option-text {
      flex: 1;
    }
  }
}
    .answer-section {
      border-radius: 12rpx;
      box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
      margin-top: 20rpx;
      
      .answer-status {
        font-size: 32rpx;
        font-weight: bold;
        padding-bottom: 20rpx;
      }
      
      .user-answer {
        font-size: 28rpx;
        padding-bottom: 20rpx;
      }
    }
    .answer-section {
      border-radius: 12rpx;
      box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
      margin-top: 20rpx;
      
      .answer-status {
        font-size: 32rpx;
        font-weight: bold;
        padding-bottom: 20rpx;
      }
      
      .user-answer {
        font-size: 28rpx;
        padding-bottom: 20rpx;
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

