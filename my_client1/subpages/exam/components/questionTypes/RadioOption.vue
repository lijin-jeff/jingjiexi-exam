<template>
  <!-- 单选题组件 -->
  <view class="radio-option-container">

    <!-- 选项 -->
    <view class="options-list tn-bg-white">
      <!-- 选项列表 -->
      <view
        v-for="(optionItem, optionIndex) in question.option"
        :key="optionIndex"
        class="option-item tn-flex tn-flex-direction-row tn-flex-col-center"
        :class="[
          optionItem.status === 'selected' ? 'selected' : '',
          question.is_submitted ? (optionItem.is_user_correct ? 'correct' : 
            optionItem.is_user_wrong ? 'wrong' : 
            optionItem.is_correct ? 'correct' : '') : '',
          (question.is_submitted && currentMode !== 'learnPractice') ? 'disabled' : ''
        ]"
        @click="handleOptionClick(optionIndex)"
      >
        <view
          :class="[
            'option-label',
            optionItem.status === 'selected' ? 'label-selected' : '',
            question.is_submitted ? (optionItem.is_user_correct ? 'label-correct' : 
              optionItem.is_user_wrong ? 'label-wrong' : 
              optionItem.is_correct ? 'label-correct' : '') : ''
          ]"
        >
          {{ optionItem.check || String.fromCharCode(65 + optionIndex) }}.
        </view>
        <view class="option-text">
          <mp-html :content="optionItem.title" />
        </view>
      </view>
    </view>
  </view>
</template>

<script>
export default {
  name: 'RadioOption',
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
      // 数据保持简单，避免复杂的状态管理  - 直接参考判断题组件的实现
    }
  },
  
  computed: {
    correctAnswer() {
      if (!this.question.answer || !Array.isArray(this.question.answer)) return ''
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
    // 监听问题数据变化，重置组件状态
    'question': {
      handler(newVal) {
        this.resetComponentState()
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
    
    // 调试日志：打印题目状态
    
    // 修复：如果从缓存恢复且题目已提交，需要重新计算选项状态
    if (this.question.is_submitted && (this.currentMode === 'learnPractice' || this.currentMode === 'reviewOnly')) {
      this.recalculateOptionStates()
    }
    
    // 背题模式下自动显示正确答案
    if (this.currentMode === 'reviewOnly') {
      this.$nextTick(() => {
        this.initReviewMode();
      });
    }
  },
  methods: {
      // 初始化选项状态 - 直接参考判断题组件的实现
    initializeOptionStatus() {
      if (this.question && this.question.option && Array.isArray(this.question.option)) {
        this.question.option.forEach((opt, index) => {
          if (opt) {
            // 使用$set确保响应式更新 - 直接参考判断题组件的实现
            this.$set(opt, 'status', opt.status || '')
            this.$set(opt, 'is_user_correct', opt.is_user_correct || false)
            this.$set(opt, 'is_user_wrong', opt.is_user_wrong || false)
            this.$set(opt, 'is_correct', opt.is_correct || false)
          }
        })
      }
    },
    
    // 修复：重新计算选项状态（用于从缓存恢复时）
    recalculateOptionStates() {
      
      // 查找用户选择的选项
      const selectedOption = this.question.option.find(opt => opt && opt.status === 'selected')
      
      if (!selectedOption || !this.question.answer) {
        return
      }
      
      // 解析正确答案
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
      
      // 标准化正确答案
      const normalizedCorrectAnswers = correctAnswers.map(ans => String(ans).trim().toUpperCase())
      // 判断用户答案是否正确
      const normalizedUserAnswer = String(selectedOption.check).trim().toUpperCase()
      const isCorrect = normalizedCorrectAnswers.includes(normalizedUserAnswer)
      
      // 更新每个选项的状态
      this.question.option.forEach((opt, index) => {
        if (!opt) return
        
        // 保留原有的 status
        const currentStatus = opt.status
        
        // 重置状态字段
        this.$set(opt, 'is_user_correct', false)
        this.$set(opt, 'is_user_wrong', false)
        this.$set(opt, 'is_correct', false)
        
        // 恢复 status
        opt.status = currentStatus
        
        // 标记正确答案
        const optCheck = opt.check ? String(opt.check).trim().toUpperCase() : ''
        const isCorrectAnswer = normalizedCorrectAnswers.includes(optCheck)
        
        if (isCorrectAnswer) {
          this.$set(opt, 'is_correct', true)
        }
        
        // 标记用户选择的结果
        if (opt === selectedOption) {
          if (isCorrect) {
            this.$set(opt, 'is_user_correct', true)
          } else {
            this.$set(opt, 'is_user_wrong', true)
          }
        }
      })

      // 强制更新视图
      this.$forceUpdate()
    },
    
    // 重置组件状态 - 直接参考判断题组件的实现
    resetComponentState() {
      if (!this.question) return
      
      // 确保必要的属性存在 - 直接参考判断题组件的实现
      if (this.question.is_selected === undefined) {
        this.$set(this.question, 'is_selected', false)
      }
      if (this.question.is_submitted === undefined) {
        this.$set(this.question, 'is_submitted', false)
      }
      if (this.question.is_correct === undefined) {
        this.$set(this.question, 'is_correct', false)
      }
      
      // 确保options数组存在
      if (!Array.isArray(this.question.option)) {
        this.$set(this.question, 'option', [])
      }
      
      // 初始化选项状态 - 直接参考判断题组件的实现
      this.initializeOptionStatus();
    },
    // 处理选项点击事件
    handleOptionClick(optionIndex) {
      
      // 在学练模式下，允许重新选择选项，即使题目已提交
      if (this.question.is_submitted && this.currentMode !== 'learnPractice') {
        return;
      }
      
      this.selectOption(optionIndex);
    },
    // 选择选项 - 参考判断题组件实现
    selectOption(optionIndex) {
      try {
        if (!this.question || !this.question.option || !Array.isArray(this.question.option)) {
          return;
        }
        
        const selectedOption = this.question.option[optionIndex];
        if (!selectedOption) {
          console.log('选项数据无效');
          return;
        }
        
        // 单选题逻辑：清除所有选项的选中状态，只设置当前选项为选中
        // 准备更新后的题目对象，避免直接修改 Prop
        const updatedQuestion = {
          ...this.question,
          is_selected: true,
          is_answered: true,
          user_answer: selectedOption.check,
          option: this.question.option.map(opt => ({
            ...opt,
            status: opt === selectedOption ? 'selected' : ''
          }))
        };
        
        // 更新本地question对象，确保后续操作基于最新状态
        Object.assign(this.question, updatedQuestion);
        
        // 强制更新组件状态
        this.$forceUpdate()
        
        // 根据当前模式处理后续逻辑
        this.handleSelectionByMode()
        
        // 触发父组件的选项选择事件，传递更新后的对象
        this.$emit('option-selected', {
          questionIndex: this.questionIndex,
          optionIndex: optionIndex,
          question: this.question,
          selectedValue: selectedOption.check
        })
      } catch (error) {
        console.error('选择选项时出错:', error);
      }
    },
    
    // 根据模式处理选择后的逻辑
    handleSelectionByMode() {
      switch (this.currentMode) {
        case 'normal':
          // 考试模式：单选选中后自动跳转下一题目
          this.question.is_submitted = true
          this.question.is_selected = true
          this.question.is_answered = true
          
          // 设置 user_answer 字段，供答题卡识别已答状态
          const selectedOption = this.question.option.find(opt => opt && opt.status === 'selected')
          if (selectedOption && selectedOption.check) {
            this.question.user_answer = selectedOption.check
          }
          
          this.$nextTick(() => {
            this.$emit('next-question', {
              questionIndex: this.questionIndex
            })
          })
          break
        case 'learnPractice':
          // 学练结合：选中后立即显示答案、解析、名师点评
          this.showAnswerAfterSelection()
          break
        case 'reviewOnly':
          // 背题模式：点击选项后显示答案判断结果
          this.$nextTick(() => {
            this.showAnswerAfterSelection()
          })
          break
        default:
          break
      }
    },
    
    // 选中后显示答案、解析、名师点评
    showAnswerAfterSelection() {
      // 标记题目为已提交、已选择和已作答
      this.question.is_submitted = true
      this.question.is_selected = true
      this.$set(this.question, 'is_answered', true)
          
      // 确保is_correct有默认值false
      let isCorrect = false
          
      // 背题模式下，保持 initReviewMode 设置的 is_correct 状态，不重新计算
      if (this.currentMode !== 'reviewOnly' && this.question.answer) {
        // 查找用户选择的选项
        const selectedOption = this.question.option.find(opt => opt && opt.status === 'selected')
            
        if (selectedOption && selectedOption.check) {
          // 统一处理正确答案，转换为数组并标准化
          let correctAnswers = []
          if (Array.isArray(this.question.answer)) {
            correctAnswers = this.question.answer
          } else if (typeof this.question.answer === 'string') {
            // 处理字符串形式的答案，例如："["C"]" -> "C"、 "C" -> "C" 、"C,D" -> ["C", "D"]
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
          

          isCorrect = normalizedCorrectAnswers.includes(normalizedUserAnswer)

          // 使用$set确保响应式更新 - 直接参考判断题组件的实现
          this.$set(this.question, 'is_correct', isCorrect)

          // 直接调用，不使用$nextTick，确保状态更新及时
          this.updateOptionStates(isCorrect, selectedOption)
        }
      } else if (this.currentMode !== 'reviewOnly') {
        // 如果没有答案，默认设置为false
        this.$set(this.question, 'is_correct', false)
        // 更新选项状态
        const selectedOption = this.question.option.find(opt => opt && opt.status === 'selected')
        this.updateOptionStates(false, selectedOption)
      } else {
        // 背题模式下，使用 initReviewMode 设置的 is_correct 状态
        isCorrect = this.question.is_correct
      }
      
      // 触发父组件的答案显示事件
      this.$emit('answer-shown', {
        questionIndex: this.questionIndex,
        question: this.question,
        isCorrect: isCorrect // 直接使用本地变量，确保不为null
      })
    },
    
    // 更新选项状态 - 正确答案标记、用户错误标记
    updateOptionStates(isCorrect, selectedOption) {
      // 确保答案数组存在并且正确解析
      let correctAnswers = []
      if (this.question.answer) {
        if (Array.isArray(this.question.answer)) {
          correctAnswers = this.question.answer
        } else if (typeof this.question.answer === 'string') {
          // 处理字符串形式的答案，例如："["C"]" -> "C"、 "C" -> "C" 、"C,D" -> ["C", "D"]
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
      
      this.question.option.forEach((opt, index) => {
        if (!opt) return
        
        // 保存当前status值，确保选中状态不丢失
        const currentStatus = opt.status
        
        // 重置所有状态属性 - 使用$set确保响应式更新
        this.$set(opt, 'is_user_correct', false)
        this.$set(opt, 'is_user_wrong', false)
        this.$set(opt, 'is_correct', false)
        
        // 恢复保存的status值
        opt.status = currentStatus
        
        // 标记正确答案 - 使用标准化的比较
        const optCheck = opt.check ? String(opt.check).trim().toUpperCase() : ''
        const isCorrectAnswer = normalizedCorrectAnswers.includes(optCheck)
        
        if (isCorrectAnswer) {
          this.$set(opt, 'is_correct', true)
        }
        
        // 标记用户选择的结果 - 正确或错误
        if (opt === selectedOption) {
          if (isCorrect) {
            this.$set(opt, 'is_user_correct', true)
            // 确保选中且正确的选项同时有selected和correct状态
            this.$set(opt, 'status', 'selected')
          } else {
            this.$set(opt, 'is_user_wrong', true)
            // 确保选中且错误的选项同时有selected和wrong状态
            this.$set(opt, 'status', 'selected')
          }
        }
      })
      
      
      // 立即强制更新DOM，确保样式正确应用
      this.$forceUpdate()
    },
    
    // 初始化背题模式状态（参考多选题组件）
    initReviewMode() {
      try {
        if (this.currentMode === 'reviewOnly' && this.question) {
          // 背题模式：标记为已提交，但不阻止查看
          this.$set(this.question, 'is_submitted', true);
          this.$set(this.question, 'is_selected', true);
          this.$set(this.question, 'is_answered', true);

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
            this.$set(this.question, 'answer_str', correctAnswers.join(', '));
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
                  this.$set(opt, 'is_correct', true);
                  this.$set(opt, 'status', 'selected');
                  selectedCorrectOption = opt;
                }
              }
            });
          }
          
          // 背题模式：自动将正确答案设置为用户答案
          if (selectedCorrectOption) {
            this.$set(this.question, 'user_answer', selectedCorrectOption.check);
          } else if (normalizedCorrectAnswers.length > 0) {
            this.$set(this.question, 'user_answer', normalizedCorrectAnswers[0]);
          }
          
          // 背题模式下默认认为答案正确
          const isCorrect = true;
          this.$set(this.question, 'is_correct', isCorrect);
          
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
    }
  }
}
</script>

<style lang="scss" scoped>

.radio-option-container {
  background-color: #F5F5F5;
  padding: 20rpx;
}

.question-content {
    padding: 20rpx;
  .question-header {
    
    .question-number {
      font-weight: bold;
      font-size: 32rpx;
      margin-right: 10rpx;
    }
    
    .question-type-tag {
      font-weight: bold;
      font-size: 28rpx;
      margin-right: 10rpx;
    }
    
    .question-score {
      color: #FF6B6B;
      font-size: 26rpx;
    }
  }
  
  .question-text {
    line-height: 1.6;
    font-size: 28rpx;
  }
}

.options-list {
  border-radius: 12rpx;
  box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
  padding: 20rpx;
  min-height: 100rpx; /* 确保即使没有选项也有高度 */
  background-color: #fff;
  
  .debug-info {
    margin-bottom: 20rpx;
    padding: 15rpx;
    background-color: #fff3cd;
    border-radius: 8rpx;
    font-size: 24rpx;
    color: #856404;
  }
  
  .debug-text {
    display: block;
    margin-bottom: 8rpx;
    font-family: monospace;
  }
  
  .option-item {
    padding: 18rpx;
    margin-bottom: 20rpx;
    border-radius: 12rpx;
    border: 1rpx solid #E6E6E6;
    transition: all 0.3s ease;
    cursor: pointer;
    
    &:last-child {
      margin-bottom: 0;
    }
    
    /* 移除:active伪类，使用小程序内置的hover-*属性代替 */
    
    // 基础选中样式
    &.selected {
      background-color: #E6F7FF !important;
      border-color: #01BEFF !important;
      border-width: 2rpx !important;
      border-style: solid !important;
    }
    
    // 正确答案样式
    &.correct {
      background-color: #F6FFED !important;
      border-color: #52C41A !important;
      border-width: 2rpx !important;
      border-style: solid !important;
    }
    
    // 错误答案样式
    &.wrong {
      background-color: #FFF2F0 !important;
      border-color: #FF4D4F !important;
      border-width: 2rpx !important;
      border-style: solid !important;
    }
    
    // 覆盖样式：已提交状态下的正确答案样式
    &.selected.correct {
      background-color: #F6FFED !important;
      border-color: #52C41A !important;
    }
    
    // 覆盖样式：已提交状态下的错误答案样式
    &.selected.wrong {
      background-color: #FFF2F0 !important;
      border-color: #FF4D4F !important;
    }
    
    &.disabled {
      cursor: not-allowed;
      opacity: 0.9;
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
    }
    
    .label-selected {
      background-color: #01BEFF;
      color: white;
      border-color: #01BEFF;
    }
    
    .label-correct {
      background-color: #52C41A;
      color: white;
      border-color: #52C41A;
    }
    
    .label-wrong {
      background-color: #FF4D4F;
      color: white;
      border-color: #FF4D4F;
    }
    
    .option-text {
      flex: 1;
      line-height: 1.6;
      font-size: 28rpx;
    }
  }
  
  // 无选项时的样式
  .no-options {
    text-align: center;
    padding: 40rpx;
    color: #999;
    font-size: 28rpx;
    background-color: #fafafa;
    border-radius: 12rpx;
    border: 1px dashed #d9d9d9;
  }
}

.answer-section {
  border-radius: 12rpx;
  box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
  
  .answer-status {
    font-size: 32rpx;
    font-weight: bold;
    padding-bottom: 20rpx;
  }
  
  .correct-answer {
    font-size: 28rpx;
    padding-bottom: 20rpx;
  }
  
  .analysis-section, .comment-section {
    font-size: 28rpx;
    padding-top: 20rpx;
    border-top: 1rpx solid #E6E6E6;
    line-height: 1.6;
  }
}

// 响应式设计调整
@media screen and (min-width: 768px) {
  .radio-option-container {
    max-width: 800rpx;
    margin: 0 auto;
  }
  
  .question-content, .options-list, .answer-section {
    padding: 30rpx;
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


