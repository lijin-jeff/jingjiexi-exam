<template>
  <!-- 多选题组件 -->
  <view
    :key="renderKey"
    class="checkbox-option-container"
  >
    <!-- 选项 -->
    <view class="options-list tn-bg-white tn-padding-left tn-padding-right tn-padding-bottom">
      <!-- 确保localQuestion.option存在且是数组 -->
      <view v-if="localQuestion && localQuestion.option && Array.isArray(localQuestion.option)">
        <view
          v-for="(optionItem, optionIndex) in localQuestion.option"
          :key="optionIndex"
          class="option-item"
          :class="{
            'selected': optionItem && optionItem.status === 'selected',
            // 答题模式下不显示正确/错误颜色，只在非答题模式下显示
            // 正确答案显示绿色（包括用户选对的和用户未选但是正确答案的）
            'correct': localQuestion.is_submitted && currentMode !== 'normal' && optionItem && (optionItem.is_user_correct || optionItem.is_correct),
            // 只有用户选错的才显示红色
            'wrong': localQuestion.is_submitted && currentMode !== 'normal' && optionItem && optionItem.is_user_wrong
          }"
          :style="(localQuestion.is_submitted && currentMode !== 'learnPractice') ? 'opacity: 0.9; pointer-events: none;' : ''"
          @click="handleOptionClick(optionIndex)"
        >
          <view class="tn-flex tn-flex-direction-row tn-flex-col-center tn-padding-xs">
            <view
              class="option-label"
              :class="{
                'label-selected': optionItem && optionItem.status === 'selected',
                // 答题模式下不显示正确/错误颜色，只在非答题模式下显示
                'label-correct': localQuestion.is_submitted && currentMode !== 'normal' && optionItem && (optionItem.is_user_correct || optionItem.is_correct),
                'label-wrong': localQuestion.is_submitted && currentMode !== 'normal' && optionItem && optionItem.is_user_wrong
              }"
            >
              {{ optionItem && optionItem.check ? optionItem.check + '.' : '' }}
            </view>
            <view class="option-text">
              <mp-html :content="optionItem.title" />
            </view>
          </view>
        </view>
      </view>
      <!-- 显示选项不存在时的提示 -->
      <view
        v-else
        class="no-options"
      >
        暂无选项
      </view>
    

    <!-- 多选题提交按钮 -->
    <view
      v-if="!localQuestion.is_submitted"
      class="submit-btn-container tn-margin-top tn-padding"
      style="display: block;"
    >
      <tn-button 
        backgroundColor="tn-cool-bg-color-9"
        fontColor="tn-color-white"
        fontSize="30"
        width="100%"
        size="lg"
        :shadow="true"
        :disabled="currentMode === 'reviewOnly'"
        @click="submitMultipleChoice"
      >
        提交答案
      </tn-button>
      </view>
    </view>
  </view>
</template>

<script>
export default {
  name: 'CheckBoxOption',
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
      renderKey: 0, // 用于强制重新渲染
      localQuestion: { ...this.question } // 创建question的本地副本，避免直接修改prop
    }
  },
  computed: {
    correctAnswer() {
      if (!this.localQuestion || !Array.isArray(this.localQuestion.answer)) return ''
      return this.localQuestion.answer.join(',')
    },
    userAnswer() {
      if (!this.localQuestion || !Array.isArray(this.localQuestion.option)) return ''
      const selectedOptions = this.localQuestion.option.filter(opt => opt.status === 'selected')
      return selectedOptions.map(opt => opt.check).join(',')
    },
    showStarLevel() {
      // 根据 exam_level 值返回对应的星级：1→1星，2→2.5星，3→5星
      const examLevel = Number(this.localQuestion.exam_level);
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
    // 监听prop变化，更新本地副本
    question: {
      handler(newVal) {
        this.localQuestion = { ...newVal }
        // 重新初始化选项状态
        this.initializeOptionStatus()
        // 背题模式下，当题目切换时重新初始化
        if (this.currentMode === 'reviewOnly') {
          this.$nextTick(() => {
            this.initReviewMode();
          });
        }
      },
      deep: true
    }
  },
  created() {
    // 初始化localQuestion
    if (this.question) {
      this.localQuestion = { ...this.question }
      // 初始化选项状态
      this.initializeOptionStatus()
    }
    // 初始化背题模式状态
    this.initReviewMode();
  },
  mounted() {
    // 确保选项状态正确初始化
    this.initializeOptionStatus();
  },
  methods: {
    // 初始化选项状态
    initializeOptionStatus() {
      if (this.localQuestion && this.localQuestion.option && Array.isArray(this.localQuestion.option)) {
        this.localQuestion.option.forEach((opt, index) => {
          if (opt) {
            // 使用$set确保响应式更新选项状态
            this.$set(opt, 'status', opt.status || '')
            this.$set(opt, 'is_user_correct', opt.is_user_correct || false)
            this.$set(opt, 'is_user_wrong', opt.is_user_wrong || false)
            this.$set(opt, 'is_correct', opt.is_correct || false)
          }
        })
      }
    },
    
    // 初始化背题模式状态
    initReviewMode() {
      try {
        if (this.currentMode === 'reviewOnly' && this.localQuestion) {
          // 背题模式：标记为已提交，但不阻止查看
          this.localQuestion.is_submitted = true;
          this.localQuestion.is_selected = true;
          this.localQuestion.is_answered = true;
          
          // 同时更新父组件的 question 对象
          if (this.question) {
            this.question.is_submitted = true;
            this.question.is_selected = true;
            this.question.is_answered = true;
          }
          
          // 处理正确答案：统一转换为数组格式，支持多种分隔符
            let correctAnswers = [];
            if (Array.isArray(this.localQuestion.answer)) {
              correctAnswers = this.localQuestion.answer;
            } else if (typeof this.localQuestion.answer === 'string') {
              try {
                // 尝试解析JSON格式的字符串
                let parsedAnswer = this.localQuestion.answer;
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
                const cleanedAnswer = this.localQuestion.answer.replace(/[\[\]"']/g, '');
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
            
            // 生成 answer_str 字段（用于 AnswerDisplay 组件显示）
            if (this.localQuestion.answer && !this.localQuestion.answer_str) {
              // 使用与 normalizedCorrectAnswers 相同的逻辑生成 answer_str
              // 这样可以确保 AnswerDisplay 组件显示的正确答案格式与用户答案格式一致
              this.$set(this.localQuestion, 'answer_str', normalizedCorrectAnswers.join(', '));
            }
            
            // 标记正确答案并自动选择
            const selectedCorrectOptions = [];
            if (this.localQuestion.option && Array.isArray(this.localQuestion.option)) {
              this.localQuestion.option.forEach(opt => {
                if (opt && opt.check) {
                  const normalizedOptionCheck = String(opt.check).trim().toUpperCase();
                  const isCorrectOption = normalizedCorrectAnswers.some(ans => ans.includes(normalizedOptionCheck) || normalizedOptionCheck.includes(ans));
                  
                  if (isCorrectOption) {
                    opt.is_correct = true;
                    opt.status = 'selected';
                    selectedCorrectOptions.push(opt);
                  }
                }
              });
            }
            
            // 背题模式：自动将所有正确答案设置为用户答案（数组格式）
            const correctAnswerValues = selectedCorrectOptions.map(opt => opt.check);
            if (correctAnswerValues.length > 0) {
              this.localQuestion.user_answer = correctAnswerValues;
              // 同时更新父组件的 question 对象
              if (this.question) {
                this.question.user_answer = correctAnswerValues;
              }
            } else if (normalizedCorrectAnswers.length > 0) {
              this.localQuestion.user_answer = normalizedCorrectAnswers;
              // 同时更新父组件的 question 对象
              if (this.question) {
                this.question.user_answer = normalizedCorrectAnswers;
              }
            }
            
            // 背题模式下默认认为答案正确
            const isCorrect = true;
            this.$set(this.localQuestion, 'is_correct', isCorrect);
            // 同时更新父组件的 question 对象
            if (this.question) {
              this.$set(this.question, 'is_correct', isCorrect);
            }
          
          // 强制更新视图
          this.$forceUpdate();
          
          // 通过多层 $nextTick 确保DOM完全更新后再触发事件
          this.$nextTick(() => {
            // 再次强制更新，确保所有状态变化都被应用
            this.$forceUpdate();
            
            this.$nextTick(() => {
              // 触发 answer-shown 事件，让父组件更新题目状态
              this.$emit('answer-shown', {
                questionIndex: this.questionIndex,
                question: this.localQuestion,
                isCorrect: isCorrect
              });
              
              // 触发选项选择事件，确保父组件同步状态
              if (selectedCorrectOptions.length > 0 && this.localQuestion.option) {
                selectedCorrectOptions.forEach(selectedOption => {
                  const correctOptionIndex = this.localQuestion.option.findIndex(opt => opt === selectedOption);
                  if (correctOptionIndex !== -1) {
                    this.$emit('option-selected', {
                      questionIndex: this.questionIndex,
                      optionIndex: correctOptionIndex,
                      question: this.localQuestion
                    });
                  }
                });
              }
            });
          });
        }
      } catch (error) {
        console.error('初始化背题模式状态失败:', error);
      }
    },
    
    // 处理选项点击事件
    handleOptionClick(optionIndex) {
      
      // 检查题目是否可以被选择
      if (this.localQuestion.is_submitted && this.currentMode !== 'learnPractice') {
        return
      }
      
      this.selectOption(optionIndex)
    },

    selectOption(optionIndex) {

      // 确保题目和选项数据有效
      if (!this.localQuestion || !Array.isArray(this.localQuestion.option)) {
        console.error('题目数据无效');
        return
      }
      
      // 确保选项存在
      if (!this.localQuestion.option[optionIndex]) {
        console.error('选项不存在:', optionIndex);
        return
      }
      
      // 直接修改本地状态，确保响应式更新
      this.$set(this.localQuestion, 'is_selected', true);
      
      // 切换选项的选中状态
      const option = this.localQuestion.option[optionIndex];
      const newStatus = option.status === 'selected' ? '' : 'selected';
      this.$set(option, 'status', newStatus);
        
      // 强制组件重新渲染
      this.renderKey++
        
      // 在DOM更新后触发父组件事件
      this.$nextTick(() => {
        // 触发父组件的选项选择事件
        this.$emit('option-selected', {
          questionIndex: this.questionIndex,
          optionIndex: optionIndex,
          question: this.localQuestion
        })
        
        // 根据当前模式处理后续逻辑
        if (this.currentMode === 'learnPractice') {
          this.$emit('option-selected-learn', {
            questionIndex: this.questionIndex,
            optionIndex: optionIndex,
            question: this.localQuestion
          })
        }
        
        // 强制更新组件状态
        this.$forceUpdate()
      })
    },
    
    submitMultipleChoice() {
      try {
        // 验证question对象
        if (!this.localQuestion || typeof this.localQuestion !== 'object') {
          uni.showToast({
            title: '题目数据异常',
            icon: 'none',
            duration: 2000
          })
          return
        }
        
        // 验证用户答案
        const hasSelectedOptions = this.localQuestion.option && Array.isArray(this.localQuestion.option) && this.localQuestion.option.some(opt => opt && opt.status === 'selected')
        
        if (!hasSelectedOptions) {
          uni.showToast({
            title: '请至少选择一个选项',
            icon: 'none',
            duration: 2000
          })
          return
        }
        
        // 立即标记题目为已提交，防止重复点击
        this.$set(this.localQuestion, 'is_submitted', true)
        
        // 强制更新组件状态，确保按钮立即隐藏
        this.$forceUpdate()
        
        // 标记其他状态
        this.$set(this.localQuestion, 'is_selected', true)
        this.$set(this.localQuestion, 'is_answered', true)
        this.$set(this.localQuestion, 'submitted_at', new Date().toISOString())
        
        // 设置 user_answer 字段，供答题卡识别已答状态
        const selectedOptions = this.localQuestion.option.filter(opt => opt && opt.status === 'selected')
        if (selectedOptions.length > 0) {
          this.$set(this.localQuestion, 'user_answer', selectedOptions.map(opt => opt.check))
        }
        
        // 计算答案正确性
        this.showAnswerAfterSelection()
        
        // 触发父组件的提交事件
        this.$emit('submit', {
          questionIndex: this.questionIndex,
          question: this.localQuestion
        })
        
        // 触发父组件的选项选择事件，确保状态更新
        this.$emit('option-selected', {
          questionIndex: this.questionIndex,
          question: this.localQuestion
        })
        
        // 根据当前模式处理后续逻辑
        this.handleSubmissionByMode();
      } catch (error) {
        console.error('提交多选题答案时发生错误:', error) 
        // 显示错误提示
        uni.showToast({
          title: '提交答案失败，请重试',
          icon: 'none',
          duration: 2000
        })
        // 出错时恢复提交状态，允许用户重新提交
        this.$set(this.localQuestion, 'is_submitted', false)
        this.$forceUpdate()
      }
    },
    
    // 根据模式处理提交后的逻辑
    handleSubmissionByMode() {
      try {
        switch (this.currentMode) {
          case 'normal':
            // 答题模式：提交后跳转下一题目
            this.$nextTick(() => {
              setTimeout(() => {
                this.$emit('next-question', {
                  questionIndex: this.questionIndex
                });
              }, 300);
            });
            break;
          case 'learnPractice':
            // 学练结合模式：提交后显示答案解析，不跳转
            // 答案显示事件已在showAnswerAfterSelection方法中触发，无需重复触发
            break;
          case 'reviewOnly':
            // 背题模式：已自动展示，无需额外处理
            break;
          default:
            break;
        }
      } catch (error) {
        console.error('处理提交后逻辑时发生错误:', error);
      }
    },
    
    // 提交后显示答案解析
    showAnswerAfterSelection() {
      try {
        // 验证question对象
        if (!this.localQuestion || typeof this.localQuestion !== 'object') {
          console.warn('showAnswerAfterSelection: question对象不存在或无效');
          return;
        }
        
        // 验证选项数据
        if (!Array.isArray(this.localQuestion.option)) {
          console.warn('showAnswerAfterSelection: 选项数据无效');
          return;
        }
        
        // 背题模式下，保持 initReviewMode 设置的 is_correct 状态，不重新计算
        if (this.currentMode !== 'reviewOnly') {
          // 使用nextTick确保DOM更新后再执行状态更新
          this.$nextTick(() => {
            try {
              // 获取用户选择的答案
              const rawCheckboxUserAnswers = this.localQuestion.option
                .filter(opt => opt && opt.status === 'selected')
                .map(opt => opt.check)
              
              // 安全处理正确答案，与initQuestionList方法保持一致
              let correctAnswers = []
              if (Array.isArray(this.localQuestion.answer)) {
                correctAnswers = this.localQuestion.answer
              } else if (typeof this.localQuestion.answer === 'string') {
                try {
                  // 尝试解析JSON格式的字符串
                  let parsedAnswer = this.localQuestion.answer
                  let parseAttempts = 0
                  const maxParseAttempts = 2
                  
                  // 尝试解析最多2次，处理双字符串化的情况
                  while (typeof parsedAnswer === 'string' && parseAttempts < maxParseAttempts) {
                    try {
                      parsedAnswer = JSON.parse(parsedAnswer)
                      parseAttempts++
                    } catch (e) {
                      break
                    }
                  }
                  
                  // 处理解析结果
                  if (Array.isArray(parsedAnswer)) {
                    correctAnswers = parsedAnswer
                  } else if (typeof parsedAnswer === 'string') {
                    // 解析后是单个字符串，如 "D"，转换为数组
                    correctAnswers = [parsedAnswer]
                  } else {
                    // 其他类型，直接转换为数组
                    correctAnswers = [String(parsedAnswer)]
                  }
                } catch (e) {
                  // 如果解析失败，直接清理并分割，支持多种分隔符
                  const cleanedAnswer = this.localQuestion.answer.replace(/[\[\]"']/g, '')
                  // 支持逗号、顿号、中文逗号等多种分隔符
                  correctAnswers = cleanedAnswer.split(/[,，、]/)
                }
              }
              
              // 最后的处理：如果数组中包含字符串且包含分隔符，尝试分割
              let finalCorrectAnswers = []
              correctAnswers.forEach(ans => {
                if (typeof ans === 'string') {
                  // 支持多种分隔符
                  const splitAnswers = ans.split(/[,，、]/)
                    .map(a => a.trim())
                    .filter(a => a !== '')
                  finalCorrectAnswers = finalCorrectAnswers.concat(splitAnswers)
                } else {
                  finalCorrectAnswers.push(String(ans))
                }
              })
              
              correctAnswers = finalCorrectAnswers
              
              // 标准化正确答案：去除空格、转换为大写、过滤空值
              const normalizedCorrectAnswers = correctAnswers
                .map(ans => {
                  // 确保ans是字符串
                  const strAns = String(ans)
                  // 去除所有空格和引号
                  const cleaned = strAns.replace(/[\s"']/g, '')
                  // 转换为大写
                  return cleaned.toUpperCase()
                })
                .filter(Boolean) // 过滤空字符串
              
              // 标准化用户答案：与正确答案使用相同的标准化逻辑
              const normalizedCheckboxUserAnswers = rawCheckboxUserAnswers
                .map(ans => {
                  // 确保ans是字符串
                  const strAns = String(ans)
                  // 去除所有空格和引号
                  const cleaned = strAns.replace(/[\s"']/g, '')
                  // 转换为大写
                  return cleaned.toUpperCase()
                })
                .filter(Boolean) // 过滤空字符串
              
              // 规范化答案格式以便比较 - 排序后比较，确保顺序不影响结果
              const sortedCheckboxUserAnswers = normalizedCheckboxUserAnswers.sort().join('')
              const sortedCheckboxCorrectAnswers = normalizedCorrectAnswers.sort().join('')
              
              // 判断用户答案是否正确
              const isCorrect = sortedCheckboxUserAnswers === sortedCheckboxCorrectAnswers
              
              // 更新每个选项的状态
              this.localQuestion.option.forEach(optionItem => {
                if (!optionItem) return
                
                // 使用$set确保响应式更新
                this.$set(optionItem, 'is_correct', false)
                this.$set(optionItem, 'is_user_correct', false)
                this.$set(optionItem, 'is_user_wrong', false)
                
                const normalizedOptionCheck = String(optionItem.check).trim().toUpperCase()
                
                // 标记正确选项
                if (normalizedCorrectAnswers.includes(normalizedOptionCheck)) {
                  this.$set(optionItem, 'is_correct', true)
                }
                
                // 标记用户选择的正确错误选项
                if (optionItem.status === 'selected') {
                  if (normalizedCorrectAnswers.includes(normalizedOptionCheck)) {
                    // 用户选择正确
                    this.$set(optionItem, 'is_user_correct', true)
                  } else {
                    // 用户选择错误
                    this.$set(optionItem, 'is_user_wrong', true)
                  }
                }
              })
              
              // 更新题目是否正确的状态
              this.$set(this.localQuestion, 'is_correct', isCorrect)
              
              // 只有当用户选择了选项时，才设置 user_answer 字段
              const userAnswerArray = this.localQuestion.option
                .filter(opt => opt && opt.status === 'selected')
                .map(opt => opt.check)
              if (userAnswerArray.length > 0) {
                this.$set(this.localQuestion, 'user_answer', userAnswerArray)
              } else {
                this.$set(this.localQuestion, 'user_answer', undefined)
              }
              
              // 强制重新渲染以确保视觉反馈正确显示
              this.renderKey++
              
              // 强制更新组件状态
              this.$forceUpdate()
              
              // 再次使用nextTick确保状态完全更新后再触发事件
              this.$nextTick(() => {
                // 触发父组件的答案显示事件
                this.$emit('answer-shown', {
                  questionIndex: this.questionIndex,
                  question: this.localQuestion,
                  isCorrect: isCorrect
                })
              })
            } catch (error) {
              console.error('处理多选题答案时发生错误:', error)
            }
          })
        } else {
          // 背题模式下，保持 initReviewMode 设置的状态
          const isCorrect = this.localQuestion.is_correct
          
          // 强制更新组件状态
          this.$forceUpdate()
          
          // 触发父组件的答案显示事件
          this.$emit('answer-shown', {
            questionIndex: this.questionIndex,
            question: this.localQuestion,
            isCorrect: isCorrect
          })
        }
      } catch (error) {
        console.error('显示多选题答案时发生错误:', error)
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.checkbox-option-container {
    background-color: #F5F5F5;
    padding: 20rpx;
}
.question-content {
   padding: 20rpx;
   .question-number {
      font-weight: bold;
      font-size: 32rpx;
      margin-right: 10rpx;
    }

  .question-type-tag {
    font-weight: bold;
  }
  .question-score {
      color: #FF6B6B;
      font-size: 26rpx;
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
      
      /* 移除:active伪类，使用小程序内置的hover-*属性代替 */
      
      &.selected {
        background-color: #E6F7FF;
        border-color: #01BEFF;
      }
      
      &.correct {
        background-color: #F6FFED;
        border-color: #52C41A; /* 答对的选项边框设为绿色 */
      }
      
      &.wrong {
        background-color: #FFF2F0;
        border-color: #FF4D4F; /* 答错的选项边框设为红色 */
      }
      
      &.disabled {
        cursor: not-allowed;
        opacity: 0.9;
        pointer-events: none;
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

.submit-btn-container {
  .submit-btn {
    width: 100%;
    height: 88rpx;
    line-height: 88rpx;
    background: linear-gradient(135deg, #1677ff 0%, #0055ff 100%);
    color: #ffffff;
    font-size: 32rpx;
    font-weight: bold;
    border-radius: 16rpx;
    border: none;
    transition: all 0.3s ease;
    box-shadow: 0 8rpx 20rpx rgba(22, 119, 255, 0.3);
    text-align: center;
    
    &:disabled {
      background: #d9d9d9;
      color: #ffffff;
      cursor: not-allowed;
      box-shadow: none;
      opacity: 0.6;
    }
    
    /* 移除:active伪类，使用小程序内置的hover-*属性代替 */
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

  .question-text, .inline-html {
    display: inline-block;
    vertical-align: top;
    min-width: 0;
    word-wrap: break-word;
    word-break: break-all;
  }

  .tn-flex-row {
    flex-wrap: wrap;
  }

  .tn-align-items-start {
    align-items: flex-start;
  }
</style>

