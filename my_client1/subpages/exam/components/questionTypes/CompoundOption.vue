<template>
  <!-- 案例题组选项组件 -->
  <view class="compound-question-container">
    
    <!-- 案例题子题区 --> 
    <view class="case-subquestions-area">
      <!-- 子题Tabs切换 -->
      <tn-tabs 
        :list="subquestionTabs" 
        :current="currentTabIndex" 
        :isScroll="true"
        :activeColor="mainColor"
        count="badge"
        @change="handleTabChange"
      />
      
      <!-- 子题内容区域 - 响应式布局 -->
      <view class="subquestion-content responsive-layout">
        <view 
          v-for="(subItem, subIndex) in question.option" 
          v-show="subIndex === currentTabIndex"
          :key="subIndex"
          class="subquestion-item"
        >
          <!-- 子题标题 -->
          <view class="subquestion-title">
            <view style="display: flex; align-items: center;">
              <text class="subquestion-number">
                {{ subIndex + 1 }}.
              </text>
              <text class="subquestion-type-tag" :style="{ color: mainColor }">
                [{{ getQuestionTypeLabel(subItem.exam_type) }}]
              </text>
              <view  v-if="subItem.childrenScore" class="question-score">
                ({{ subItem.childrenScore }}分)
              </view>
            </view>
            <view>
            <mp-html :content="subItem.title" />
            </view> 
          </view>
          
          <!-- 子题选项区域（单选题、多选题、判断题） -->
          <view 
            v-if="subItem.exam_type == 1 || subItem.exam_type == 2 || subItem.exam_type == 3"
            class="subquestion-options"
          >
            <view 
              v-for="(option, childIndex) in getSubItemOptions(subItem)"
              :key="childIndex"
              class="option-item"
              :class="{
                'selected': option.status === 'selected' && !question.is_submitted,
                'correct': question.is_submitted && option.is_correct,
                'user-correct': question.is_submitted && option.is_user_correct,
                'user-wrong': question.is_submitted && option.is_user_wrong
              }"
              :disabled="question.is_submitted && currentMode !== 'learnPractice'"
              @click="selectOption(subIndex, childIndex, subItem.exam_type)"
            >
              <view class="option-prefix">
                {{ getOptionPrefix(childIndex) }}
              </view>
              <view class="option-content">
                <mp-html :content="option.title" />
              </view>
            </view>
          </view>
          
          <!-- 子题问答题区 --> 
          <view 
            v-if="subItem.exam_type == 4 || subItem.exam_type == 5"
            class="subquestion-essay-area"
          >
            <view class="essay-input-container" :class="{ 'essay-input-container-disabled': question.is_submitted }">
              
              <!-- <tn-input
                v-model="subItem.user_answer"
                type="textarea"
                placeholder="请输入答案..."
                :disabled="question.is_submitted && currentMode !== 'learnPractice'"
                :border="true"
                auto-height="true"
                @blur="handleEssayBlur(subIndex)"
              /> -->
              <textarea
                v-model="subItem.user_answer"
                maxlength="300"
                placeholder="请输入答案..."
                placeholder-style="color:#AAAAAA"
                class="comment-textarea"
                auto-height="true"
                :disabled="question.is_submitted"
                @input="handleEssayInput(subIndex, $event)"
                @blur="handleEssayBlur(subIndex)"
                />
            </view>
          </view>
        </view>
      </view>
      
      <!-- 提交按钮 - 在背题模式下隐藏 -->
      <view 
        v-if="currentMode !== 'reviewOnly'"
        class="tn-padding-left tn-padding-right tn-padding-bottom"
        style="display: block;"
      >
        <!-- 已提交状态显示 -->
        <view v-if="question.is_submitted">
          <tn-button 
            :background-color="mainColor + '--disabled'"
            fontSize="30"
            width="100%"
            size="lg"
            disabled
          >
            已提交答案
          </tn-button>
        </view>
        <!-- 未提交状态显示 -->
        <tn-button 
          v-else
          :background-color="mainColor"
          font-color="tn-color-white"
          fontSize="30"
          width="100%"
          size="lg"
          :disabled="!canSubmit"
          @click="handleSubmitClick"
        >
          {{ currentMode === 'learnPractice' ? '提交答案并查看解析' : currentMode === 'normal' ? '提交答案' : '' }}
        </tn-button>
      </view>
      
      <!-- 案例题结果统计区域（提交后显示, 答题模式下不显示） -->
      <view
        v-if="question.is_submitted && currentMode !== 'normal'"
      >
        <view class="case-result-section">
          <view class="result-title">
            参考答案：
          </view>
          <view class="result-content">
            <mp-html 
              class="result-text"
              :content="currentCorrectAnswer"
            />
          </view>
        </view>
      </view>
      
      <!-- 图鸟UI确认弹窗 -->
      <tn-modal
        v-model="showConfirmModal"
        :width="'70%'"
        :title="confirmModalTitle"
        :content="confirmModalContent"
        :button="[
          { text: '取消', backgroundColor: '#F5F5F5', fontColor: '#333333' },
          { text: '确定', backgroundColor: mainColor, fontColor: '#FFFFFF' }
        ]"
        :mask-closeable="true"
        @click="handleModalClick"
        @cancel="showConfirmModal = false"
      />
      
      <!-- 图鸟UI提示弹窗 -->
      <tn-toast ref="toast" />
    </view>
  </view>
</template>

<script>
import { getQuestionTypeName } from '@/util/questionTypeManager.js'

export default {
  name: 'CompoundOption',
  components: {
  },
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
      default: 'normal', // normal, learnPractice, reviewOnly
    },
    showTitle: {
      type: Boolean,
      default: true
    },
    // 是否是最后一题，用于控制考试模式下的提交
    isLastQuestion: {
      type: Boolean,
      default: false
    },
    // 总题目数量，用于控制考试模式下的提交
    totalQuestions: {
      type: Number,
      default: 0
    }
  },
  emits: ['option-selected', 'answer-shown', 'next-question', 'answer-submitted', 'submit'],
  data() {
    return {
      mainColor: getApp().globalData.mainColor || '#1E88E5',
      currentTabIndex: 0,
      // 图鸟UI弹窗相关数据
      showConfirmModal: false,
      confirmModalTitle: '',
      confirmModalContent: ''
    }
  },
  computed: {
    // 子题Tabs列表
    subquestionTabs() {
      if (!this.question.option || !Array.isArray(this.question.option)) return []
      
      return this.question.option.map((item, index) => {
        // 判断子题是否已答
        const isAnswered = this.isSubQuestionAnswered(item)
        
        return {
          name: `试题${index + 1}`,
          badge: isAnswered ? '' : '', 
          badgeColor: isAnswered ? '#52c41a' : '#ff4d4f'  // 已答绿色，未答红色
        }
      })
    },
    
    

    
    // 格式化的正确答案
    correctAnswer() {
      if (!this.question || !this.question.option || !Array.isArray(this.question.option)) return ''
      
      // 处理案例题的正确答案格式，优先使用子试题的answerContent
      return this.question.option
        .map((subItem, index) => {
          if (subItem.answerContent) {
            // 为每个子题添加标题，使子题之间区分更明显
            return `<div class="sub-answer-section">
              <h4 class="sub-answer-title">试题 ${index + 1} 参考答案</h4>
              <div class="sub-answer-content">${subItem.answerContent}</div>
            </div>`
          } else if (subItem.check) {
            // 处理选择题答案
            return `<div class="sub-answer-section">
              <h4 class="sub-answer-title">试题 ${index + 1} 参考答案</h4>
              <div class="sub-answer-content">${subItem.check
                .replace(/^ExamOptionCheck\./g, '')
                .split(',')
                .map(item => item.trim())
                .join(', ')}
              </div>
            </div>`
          }
          return ''
        })
        .join('')
    },
    
    // 当前选中子题的正确答案
    currentCorrectAnswer() {
      if (!this.question || !this.question.option || !Array.isArray(this.question.option) || this.question.option.length <= this.currentTabIndex) return ''
      
      const subItem = this.question.option[this.currentTabIndex]
      if (subItem.answerContent) {
        return subItem.answerContent
      } else if (subItem.check) {
        return subItem.check
          .replace(/^ExamOptionCheck\./g, '')
          .split(',')
          .map(item => item.trim())
          .join(', ')
      }
      return ''
    },
    
    // 当前选中子题的用户答案
    currentUserAnswer() {
      if (!this.question || !this.question.option || !Array.isArray(this.question.option) || this.question.option.length <= this.currentTabIndex) return ''
      
      const subItem = this.question.option[this.currentTabIndex]
      if (subItem.user_answer) {
        if (Array.isArray(subItem.user_answer)) {
          // 多选题答案（数组格式）
          return subItem.user_answer.map(item => {
            if (typeof item === 'string') {
              return item.replace(/^ExamOptionCheck\./g, '')
            }
            return item
          }).join(', ')
        } else if (typeof subItem.user_answer === 'string') {
          // 单选题、判断题、问答题答案（字符串格式）
          return subItem.user_answer
        }
      }
      return ''
    },
    
    // 格式化用户提交的答案
    userAnswers() {
      if (!this.question || !this.question.option || !Array.isArray(this.question.option)) return ''
      
      // 处理用户提交的所有子题答案
      return this.question.option
        .map((subItem, index) => {
          if (subItem.user_answer) {
            let answerContent = ''
            
            // 根据子题类型格式化答案
            if (Array.isArray(subItem.user_answer)) {
              // 多选题答案（数组格式）
              answerContent = subItem.user_answer.map(item => {
                if (typeof item === 'string') {
                  return item.replace(/^ExamOptionCheck\./g, '')
                }
                return item
              }).join(', ')
            } else if (typeof subItem.user_answer === 'string') {
              // 单选题、判断题、问答题答案（字符串格式）
              answerContent = subItem.user_answer
            }
            
            return `<div class="sub-answer-section">
              <h4 class="sub-answer-title">试题 ${index + 1} 我提交的答案</h4>
              <div class="sub-answer-content">${answerContent || '未作答'}</div>
            </div>`
          }
          return `<div class="sub-answer-section">
            <h4 class="sub-answer-title">试题 ${index + 1} 我提交的答案</h4>
            <div class="sub-answer-content">未作答</div>
          </div>`
        })
        .join('')
    },
    
    // 判断是否可以提交
    canSubmit() {  
      // 已提交的题目不能再次提交
      if (this.question.is_submitted) {
        return false
      }
      
      // 所有模式下，只要题目未提交就可以提交（移除了答题模式限制和子题完成度校验）
      return true
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
  created() {
  },
  mounted() {

    
    // 确保选项状态正确初始化
    this.initializeOptionStatus();
    // 初始化背题模式状态
    this.initReviewMode();
    
    // 再次检查初始化后的数据
    this.$nextTick(() => {
      if (this.question && this.question.option) {
        this.question.option.forEach((subItem, idx) => {
        });
      }
    });
    
  },
  methods: {
    // 判断子题是否已答
    isSubQuestionAnswered(subItem) {
      if (!subItem || typeof subItem !== 'object') {
        return false
      }
      
      const examType = parseInt(subItem.exam_type || 0);
      
      switch (examType) {
        case 1: // 单选题
        case 3: // 判断题
          return !!subItem.selectedAnswer
        case 2: // 多选题
          return subItem.selectedAnswers && subItem.selectedAnswers.length > 0
        case 4: // 问答题
        case 5: // 问答题
          return subItem.user_answer && subItem.user_answer.trim() !== ''
        default:
          return false
      }
    },
    
    // 获取子题选项数组（兼容children和option两种字段）
    getSubItemOptions(subItem) {
      if (!subItem) {
        console.warn('子题数据为空');
        return [];
      }
      
      // 优先使用children，其次option
      const options = subItem.children || subItem.option || [];
      
      return options;
    },
    
    // 初始化选项状态
    initializeOptionStatus() {
      if (!this.question || !this.question.option || !Array.isArray(this.question.option)) {
        console.warn('题目数据不存在或格式错误');
        return;
      }
      
      this.question.option.forEach((subItem, subIndex) => {
        if (!subItem) {
          return;
        }
        
        // 初始化子题的基础字段
        this.$set(subItem, 'selectedAnswer', subItem.selectedAnswer || '');
        this.$set(subItem, 'selectedAnswers', subItem.selectedAnswers || []);
        this.$set(subItem, 'user_answer', subItem.user_answer || '');
        this.$set(subItem, 'is_correct', subItem.is_correct || false);
        this.$set(subItem, 'is_answered', subItem.is_answered || false);
        
        // 获取选项数组（兼容children和option两种字段名）
        let optionsArray = null;
        
        // 1. 先检查children字段
        if (subItem.children && Array.isArray(subItem.children) && subItem.children.length > 0) {
          optionsArray = subItem.children;
        }
        // 2. 如果children不存在，检查option字段
        else if (subItem.option && Array.isArray(subItem.option) && subItem.option.length > 0) {
          // 将option复制到children，确保模板可以访问
          this.$set(subItem, 'children', JSON.parse(JSON.stringify(subItem.option)));
          optionsArray = subItem.children;
        }
        // 3. 如果两个都不存在
        else {
          // 对于选择题类型，这是不正常的（注意：exam_type可能是字符串）
          if (subItem.exam_type == 1 || subItem.exam_type == 2 || subItem.exam_type == 3) {
            // 创建空数组避免错误
            this.$set(subItem, 'children', []);
          }
          return;
        }
        
        // 初始化每个选项
        if (optionsArray && optionsArray.length > 0) {
          optionsArray.forEach((option, optionIndex) => {
            if (!option) {
              this.$set(optionsArray, optionIndex, {
                title: '',
                check: '',
                status: '',
                is_user_correct: false,
                is_user_wrong: false,
                is_correct: false
              });
              return;
            }
            
            // 使用$set确保响应式更新
            this.$set(option, 'status', option.status || '');
            this.$set(option, 'is_user_correct', option.is_user_correct || false);
            this.$set(option, 'is_user_wrong', option.is_user_wrong || false);
            this.$set(option, 'is_correct', option.is_correct || false);
            this.$set(option, 'check', option.check || '');
            this.$set(option, 'title', option.title || '');

          });
        }
      });
      
      // 强制更新视图
      this.$forceUpdate();
    },
    
    // 获取题型标签
    getQuestionTypeLabel(examType) {
      // 使用统一的题型管理工具
      return getQuestionTypeName(examType)
    },
    
    // 获取选项前缀
    getOptionPrefix(index) {
      return String.fromCharCode(65 + index) // A, B, C, D...
    },

    // 格式化答案
    formatAnswer(answer) {
      if (!answer) return ''
      
      return answer
        .replace(/^ExamOptionCheck\./g, '')
        .toUpperCase()
        .trim()
    },
    
    // 格式化多选答案
    formatAnswers(answers) {
      if (!answers || !Array.isArray(answers) || answers.length === 0) return ''
      
      return answers
        .map(answer => this.formatAnswer(answer))
        .join(', ')
    },
    
    // 处理Tab切换
    handleTabChange(index) {
      this.currentTabIndex = index
    },
    
    // 显示答案（无论何种模式）
    showAnswerAfterSelection() {
      // 案例题的答案显示逻辑已在submitCaseQuestion中处理
      // 这里可以添加额外的显示逻辑（如果需要）
    },
    
    // 初始化背题模式
    initReviewMode() {
      try {

        // 当处于背题模式时，可以进行相应的初始化操作
        if (this.currentMode === 'reviewOnly' && this.question && this.question.option) {
          // 标记主题目为已提交，这样子题的样式绑定才能生效
          this.$set(this.question, 'is_submitted', true);
          this.$set(this.question, 'is_selected', true);
          
          // 初始化子题状态，确保在背题模式下显示正确的答案状态
          let totalSubCorrect = 0
          this.question.option.forEach((subItem, subIndex) => {
            // 确保子题对象存在
            if (subItem) {
              // 标记为已作答状态，便于在背题模式下正确显示
              this.$set(subItem, 'is_answered', true);
              
              // 生成 answer_str 字段（用于 AnswerDisplay 组件显示）
              if (subItem.check && !subItem.answer_str) {
                this.$set(subItem, 'answer_str', subItem.check);
              }
                  
              // 使用getSubItemOptions确保获取正确的选项数组
              const optionsArray = this.getSubItemOptions(subItem);
                  
              // 确保 optionsArray是数组类型
              if (!Array.isArray(optionsArray)) {
                return;
              }
              
              let isSubCorrect = false
              
              // 处理不同题型的答案检查
              if (subItem.exam_type == 1 || subItem.exam_type == 3) {
                // 单选题和判断题
                if (subItem.check && optionsArray.length > 0) {
                  // 规范化正确答案
                  const normalizedCorrectAnswer = subItem.check
                    .replace(/^ExamOptionCheck\./, '')
                    .toUpperCase()
                    .trim()
                  
                  // 标记正确选项并设置is_selected状态
                  optionsArray.forEach((option, optionIndex) => {
                    if (option && option.check) {
                      const normalizedOptionCheck = option.check
                        .replace(/^ExamOptionCheck\./, '')
                        .toUpperCase()
                        .trim()
                      const isCorrect = normalizedOptionCheck === normalizedCorrectAnswer
                      this.$set(option, 'is_correct', isCorrect)
                      this.$set(option, 'is_selected', isCorrect) // 自动选中正确选项
                      this.$set(option, 'is_user_correct', isCorrect) // 用户选择正确
                    }
                  })
                  
                  // 设置子题的user_answer和is_correct
                  this.$set(subItem, 'user_answer', normalizedCorrectAnswer)
                  this.$set(subItem, 'is_correct', true)
                  isSubCorrect = true
                }
              } else if (subItem.exam_type == 2) {
                // 多选题
                if (subItem.check && optionsArray.length > 0) {
                  // 获取正确答案并规范化，支持多种分隔符
                  const correctAnswers = (subItem.check || '')
                    .split(/[,，、]/)
                    .map(ans => ans
                      .replace(/^ExamOptionCheck\./, '')
                      .toUpperCase()
                      .trim()
                    )
                    .filter(Boolean)
                  
                  // 标记正确选项并设置is_selected状态
                  const selectedAnswers = []
                  optionsArray.forEach((option, optionIndex) => {
                    if (option && option.check) {
                      const normalizedOptionCheck = option.check
                        .replace(/^ExamOptionCheck\./, '')
                        .toUpperCase()
                        .trim()
                      const isCorrect = correctAnswers.includes(normalizedOptionCheck)
                      this.$set(option, 'is_correct', isCorrect)
                      this.$set(option, 'is_selected', isCorrect) // 自动选中正确选项
                      this.$set(option, 'is_user_correct', isCorrect) // 用户选择正确
                      
                      if (isCorrect) {
                        selectedAnswers.push(normalizedOptionCheck)
                      }
                    }
                  })
                  
                  // 设置子题的user_answer和is_correct
                  this.$set(subItem, 'user_answer', selectedAnswers) // 数组格式
                  this.$set(subItem, 'is_correct', true)
                  isSubCorrect = true
                }
              } else if (subItem.exam_type == 4 || subItem.exam_type == 5) {
                // 问答题
                this.$set(subItem, 'user_answer', subItem.user_answer || '')
                this.$set(subItem, 'is_correct', undefined) // 问答题不自动判断对错
              }
              
              // 记录正确数量
              if (isSubCorrect) {
                totalSubCorrect++
              }
            }
          });
          
          // 设置主题目的is_correct字段
          this.$set(this.question, 'is_correct', totalSubCorrect === this.question.option.length)
          
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
                question: this.question,
                isCorrect: this.question.is_correct
              });
            });
          });
        } else {
          console.log('未满足条件，不执行 initReviewMode');
        }
      } catch (error) {
        console.error('初始化背题模式失败', error);
      }
    },
    
    // 选择选项 - 参考CheckBoxOption.vue的多选题实现
    selectOption(subIndex, childIndex, examType) {
      
      // 已提交且非学练模式则不可再选择
      if (this.question.is_submitted && this.currentMode !== 'learnPractice') {
        console.log('题目已提交，不可选择');
        return;
      }
      
      const subItem = this.question.option[subIndex]
      if (!subItem) {
        console.log('子题数据无效');
        return;
      }
      
      // 使用getSubItemOptions确保获取正确的选项数组
      const optionsArray = this.getSubItemOptions(subItem);
      if (!optionsArray || !Array.isArray(optionsArray) || !optionsArray[childIndex]) {
        console.log('选项数据无效');
        return;
      }
      
      const selectedOption = optionsArray[childIndex];
      
      // 判断是否为多选题
      const isMultiple = examType == 2
      
      // 处理单选逻辑（单选题和判断题）
      if (!isMultiple) {
        // 清除所有选项的选中状态
        optionsArray.forEach((opt, idx) => {
          this.$set(opt, 'status', opt === selectedOption ? 'selected' : '')
        })
        // 更新单选题答案
        this.$set(subItem, 'selectedAnswer', selectedOption.check)
        this.$set(subItem, 'selectedAnswers', [])
        
        // 标记子题为已作答
        this.$set(subItem, 'is_answered', true);
      } else {
        // 多选题逻辑 - 参考CheckBoxOption.vue实现
        // 切换选项的选中状态
        const newStatus = selectedOption.status === 'selected' ? '' : 'selected'
        
        // 使用$set更新选项状态，确保响应式更新
        this.$set(selectedOption, 'status', newStatus)
        
        // 更新子题选中状态
        this.$set(subItem, 'is_selected', true)
        
        // 在DOM更新后处理选中状态更新
        this.$nextTick(() => {
          // 更新多选题答案数组
          const selectedAnswers = optionsArray
            .filter(opt => opt.status === 'selected')
            .map(opt => opt.check)
                    
          // 确保响应式更新，创建新数组
          this.$set(subItem, 'selectedAnswers', [...selectedAnswers])
          this.$set(subItem, 'selectedAnswer', '')
          
          // 标记子题为已作答或未作答
          this.$set(subItem, 'is_answered', selectedAnswers.length > 0);
          
          // 强制触发视图更新
          this.$forceUpdate()

        })
        
        // 标记子题为已作答（临时，最终状态在nextTick中更新）
        this.$set(subItem, 'is_answered', true);
      }
      
      // 强制触发视图更新
      this.$forceUpdate()
      
      // 强制更新tabs显示状态
      this.$nextTick(() => {
        this.$forceUpdate()
      })
      
      // 移除自动提交功能，改为手动提交
    },
    
    // 处理问答题输入事件
    handleEssayInput(subIndex, e) {
      try {
        // 如果题目已提交，不处理输入事件
        if (this.question.is_submitted) return
        
        const subItem = this.question.option[subIndex]
        if (!subItem) return
        
        // 处理输入事件
        const value = (e && e.detail && typeof e.detail.value === 'string') ? e.detail.value : (typeof e === 'string' ? e : '')
        
        // 更新子题的user_answer
        this.$set(subItem, 'user_answer', value)
        
        // 在所有模式下，如果用户输入了内容，更新答题状态
        if (value && value.trim()) {
          this.$set(subItem, 'is_answered', true)
        } else {
          this.$set(subItem, 'is_answered', false)
        }
        
        // 触发父组件的答案输入事件
        this.$emit('answer-input', {
          questionIndex: this.questionIndex,
          subIndex: subIndex,
          answer: value,
          question: this.question
        })
        
        // 强制更新tabs显示状态
        this.$nextTick(() => {
          this.$forceUpdate()
        })
      } catch (error) {
        console.error('子试题问答题输入处理失败:', error)
      }
    },
    // 处理问答题输入失焦事件 - 确保用户输入被保存
    handleEssayBlur(subIndex, e) {
      // 如果题目已提交，不处理失焦事件
      if (this.question.is_submitted) return
      
      const subItem = this.question.option[subIndex]
      if (!subItem) return
      
      subItem.user_answer = (subItem.user_answer || '').trim()
      this.$set(this.question.option, subIndex, subItem)
      
      // 在所有模式下，如果用户输入了内容，更新答题状态
      if (subItem.user_answer) {
        this.$set(subItem, 'is_answered', true);
      }
      
      // 强制触发视图更新
      this.$forceUpdate()
      
      // 移除自动提交功能，改为手动提交
    },
    
    // 判断当前案例题是否全部作答（包括问答题校验）
    isCaseAllAnswered() {
      const subQuestions = this.question.option || []
      
      // 遍历所有子题进行校验
      for (let i = 0; i < subQuestions.length; i++) {
        const subItem = subQuestions[i]
        
        // 确保子题数据有效
        if (!subItem || typeof subItem !== 'object') {
          return false
        }
        
        // 转换exam_type为数字类型，确保比较准确
        const examType = parseInt(subItem.exam_type || 0);
        
        // 根据题型判断是否已作答
        switch (examType) {
          case 1: // 单选题
          case 3: // 判断题
            // 检查是否有选中的选项
            if (!subItem.selectedAnswer) {
              return false
            }
            break;
          case 2: // 多选题
            // 检查是否有选中的选项
            if (!subItem.selectedAnswers || subItem.selectedAnswers.length === 0) {
              return false
            }
            break;
          case 4: // 问答题
          case 5: // 问答题
            // 检查user_answer是否存在且不为空字符  （trim()移除首尾空格）
            if (!subItem.user_answer || subItem.user_answer.trim() === '') {
              return false
            }
            break;
          default: // 未知题型：默认认为已作答，避免按钮被不必要地禁用
            break;
        }
      }
      return true
    },
    
    // 处理提交点击事件
    handleSubmitClick() {

      // 背题模式下特殊处理：即使已提交，也允许查看解析
      if (this.currentMode === 'reviewOnly') {
        // 如果题目未提交，先提交
        if (!this.question.is_submitted) {
          try {
            // 直接提交
            this.submitCaseQuestion();
          } catch (error) {
            this.showToastMessage('提交失败，请重试');
          }
        } else {
          // 已提交则直接显示解析
          this.$emit('answer-shown', {
            questionIndex: this.questionIndex,
            question: this.question,
            isCorrect: this.question.is_correct
          });
        }
        return;
      }
      
      // 非背题模式下的常规处理（移除了子题完成度校验）
      // 防止重复提交
      if (this.question.is_submitted) {
        this.showToastMessage('题目已提交');
        return;
      }
      
      // 直接显示确认提交弹窗
      this.confirmModalTitle = '确认提交'
      this.confirmModalContent = '提交后不可修改答案，确定要提交吗？'
      this.showConfirmModal = true
    },
    
    // 处理图鸟UI模态框按钮点击事件
    handleModalClick({ index }) {
      // 关闭模态框
      this.showConfirmModal = false
      
      // 用户点击确定按钮（索引为1）
      if (index === 1) {
        try {
          // 直接提交
          this.submitCaseQuestion();
        } catch (error) {
          console.error('提交点击事件处理失败:', error);
          this.showToastMessage('提交失败，请重试');
        }
      } else {
        // 用户点击取消按钮（索引为0）
        console.log('用户取消提交');
      }
    },
    
    // 显示图鸟UI提示
    showToastMessage(message) {
      try {
        this.$refs.toast.show({
          title: message,
          duration: 2000
        })
      } catch (error) {
        console.error('显示Toast失败:', error)
        // 兼容处理：如果图鸟UI Toast组件加载失败，使用原生的uni.showToast
        uni.showToast({
          title: message,
          icon: 'none',
          duration: 2000
        })
      }
    },
    
    // 提交案例题答案（增强校验逻辑）
    submitCaseQuestion() {
      try {

        // 验证question对象
        if (!this.question || typeof this.question !== 'object') {
          this.showToastMessage('题目数据异常');
          return
        }
        
        // 获取子题列表
        const subQuestions = this.question.option || []

        if (!Array.isArray(subQuestions) || subQuestions.length === 0) {
          this.showToastMessage('子题数据异常');
          return
        }
        
        // 必须所有子试题均已作答（含问答题），背题模式下跳过检查
        // 注意：背题模式下直接跳过检查，因为背题模式下用户可以直接提交
        const allAnswered = this.currentMode === 'reviewOnly' ? true : this.isCaseAllAnswered()

        // 背题模式下强制跳过所有检查，直接提交
        // if (!allAnswered && this.currentMode !== 'reviewOnly') {
        //   this.showToastMessage('请完成所有子试题的作答');
        //   return
        // }
        
        // 记录提交前的状态，用于错误恢复
        const beforeSubmitState = subQuestions.map(q => ({
          is_correct: q.is_correct,
          user_answer: q.user_answer,
          selectedAnswer: q.selectedAnswer,
          selectedAnswers: [...(q.selectedAnswers || [])]
        }))
        
        // 标记题目为已提交 - 使用$set确保响应式更新
        this.$set(this.question, 'is_submitted', true)
        this.$set(this.question, 'is_selected', true)
        this.$set(this.question, 'submitted_at', new Date().toISOString())
        
        // 显示答案（无论何种模式）
        this.showAnswerAfterSelection();
        
        // 判断每个子题是否正确
        let totalCorrect = 0
        const results = []
        
        for (let i = 0; i < subQuestions.length; i++) {
          const subItem = subQuestions[i]
          
          // 子题数据验证
          if (!subItem || typeof subItem !== 'object') {
            continue
          }
          
          let isSubCorrect = false
          
          try {
            // 优先处理背题模式
            if (this.currentMode === 'reviewOnly') {
              // 背题模式下，直接根据正确答案判断，不需要用户作答
              if (subItem.exam_type == 4 || subItem.exam_type == 5) {
                // 背题模式下的问答题，直接标记为已作答，判断为对
                // 自动判断对
                isSubCorrect = true;
              } else {
                // 对于选择题，直接标记正确答案
                const optionsArray = this.getSubItemOptions(subItem);
                if (optionsArray && Array.isArray(optionsArray) && optionsArray.length > 0) {
                  // 获取正确答案并规范化，支持多种分隔符
                const correctAnswers = (subItem.check || '')
                  .split(/[,，、]/)
                  .map(ans => ans
                    .replace(/^ExamOptionCheck\./, '')
                    .toUpperCase()
                    .trim()
                  )
                  .filter(Boolean)
                  
                  // 在背题模式下，如果是单选题或判断题，自动选择正确答案
                  if (subItem.exam_type == 1 || subItem.exam_type == 3) {
                    const correctOption = optionsArray.find(opt => {
                      const normalizedCheck = opt.check.replace(/^ExamOptionCheck\./, '').toUpperCase().trim();
                      return correctAnswers.includes(normalizedCheck);
                    });
                    if (correctOption) {
                      this.$set(subItem, 'selectedAnswer', correctOption.check);
                      isSubCorrect = true;
                    }
                  }
                  // 在背题模式下，如果是多选题，自动选择所有正确答案
                  else if (subItem.exam_type == 2) {
                    const correctOptions = optionsArray.filter(opt => {
                      const normalizedCheck = opt.check.replace(/^ExamOptionCheck\./, '').toUpperCase().trim();
                      return correctAnswers.includes(normalizedCheck);
                    });
                    if (correctOptions.length > 0) {
                      const correctAnswersList = correctOptions.map(opt => opt.check);
                      this.$set(subItem, 'selectedAnswers', correctAnswersList);
                      isSubCorrect = true;
                    }
                  }
                }
              }
            }
            // 非背题模式下的常规处理
            else {
              // 处理单选题和判断题
              if (subItem.selectedAnswer) {
                // 移除可能的前缀，如"ExamOptionCheck."
                const normalizedUserAnswer = subItem.selectedAnswer
                  .replace(/^ExamOptionCheck\./, '')
                  .toUpperCase()
                  .trim()
                // 获取正确答案并规范化，支持多种分隔符
                const correctAnswers = (subItem.check || '')
                  .split(/[,，、]/)
                  .map(ans => ans
                    .replace(/^ExamOptionCheck\./, '')
                    .toUpperCase()
                    .trim()
                  )
                  .filter(Boolean)
                isSubCorrect = correctAnswers.includes(normalizedUserAnswer)
              } 
              // 处理多选题
              else if (subItem.selectedAnswers && subItem.selectedAnswers.length > 0) {
                // 获取正确答案并规范化，支持多种分隔符
                const correctAnswers = (subItem.check || '')
                  .split(/[,，、]/)
                  .map(ans => ans
                    .replace(/^ExamOptionCheck\./, '')
                    .toUpperCase()
                    .trim()
                  )
                  .filter(Boolean)
                
                // 获取用户答案并规范化
                const userAnswers = subItem.selectedAnswers
                  .map(ans => ans
                    .replace(/^ExamOptionCheck\./, '')
                    .toUpperCase()
                    .trim()
                  )
                  .filter(Boolean)
                
                // 排序后比较，确保顺序不影响结果
                const sortedCorrectAnswers = correctAnswers.sort().join(',')
                const sortedUserAnswers = userAnswers.sort().join(',')
                isSubCorrect = sortedCorrectAnswers === sortedUserAnswers
              }
              // 处理问答题
              else if ((subItem.exam_type == 4 || subItem.exam_type == 5) && subItem.user_answer) {
                // 问答题答案比较逻辑可能需要根据实际需求调整
                // 这里我们不自动判断对错，只显示参考答案
                // 用户需要自己判断答案是否正确
                isSubCorrect = undefined; // 不自动判断对错
              }
            }
            
            // 记录答题结果
            results.push({
              subQuestionIndex: i,
              isCorrect: isSubCorrect,
              examType: subItem.exam_type,
              submittedAt: new Date().toISOString()
            })
            
          } catch (error) {
            isSubCorrect = false
            
            // 记录错误结果
            results.push({
              subQuestionIndex: i,
              isCorrect: false,
              error: String(error),
              examType: subItem.exam_type || null,
              submittedAt: new Date().toISOString()
            })
          }
          
          // 设置子题的user_answer字段，确保格式正确
          let subUserAnswer = ''
          if (subItem.exam_type == 1 || subItem.exam_type == 3) {
            // 单选题/判断题：字符串格式
            subUserAnswer = subItem.selectedAnswer ? subItem.selectedAnswer.replace(/^ExamOptionCheck\./, '') : ''
          } else if (subItem.exam_type == 2) {
            // 多选题：数组格式
            subUserAnswer = subItem.selectedAnswers.map(ans => ans.replace(/^ExamOptionCheck\./, '')).filter(Boolean)
          } else if (subItem.exam_type == 4 || subItem.exam_type == 5) {
            // 问答题：字符串格式
            subUserAnswer = subItem.user_answer || ''
          }
          this.$set(subItem, 'user_answer', subUserAnswer)
          
          subItem.is_correct = isSubCorrect
          subItem.submitted_at = new Date().toISOString()
          // 设置子题已作答标记，用于父组件的shouldShowAnswer计算属性判断是否显示答案
          subItem.is_answered = true
          
          if (isSubCorrect) totalCorrect++
          
          // 更新选项状态，标记正确和错误选项
          // 答题模式下不显示正确/错误状态
          const optionsArray = subItem.children || subItem.option || [];
          if (this.currentMode !== 'normal' && optionsArray && Array.isArray(optionsArray) && optionsArray.length > 0) {
            try {
              // 获取正确答案并规范化，支持多种分隔符
              const correctAnswers = (subItem.check || '')
                .split(/[,，、]/)
                .map(ans => ans
                  .replace(/^ExamOptionCheck\./, '')
                  .toUpperCase()
                  .trim()
                )
                .filter(Boolean)
              
              optionsArray.forEach(childOption => {
                // 重置状态，确保每次更新都是独立的 ，避免多个子题共享状态
                childOption.is_correct = false
                childOption.is_user_correct = false
                childOption.is_user_wrong = false
                
                // 标记正确选项
                const normalizedCheck = childOption.check
                  .replace(/^ExamOptionCheck\./, '')
                  .toUpperCase()
                  .trim()
                
                if (correctAnswers.includes(normalizedCheck)) {
                  childOption.is_correct = true
                }
                
                // 标记用户选择的正确和错误选项
                if (childOption.is_selected) {
                  if (correctAnswers.includes(normalizedCheck)) {
                    childOption.is_user_correct = true
                  } else {
                    childOption.is_user_wrong = true
                  }
                }
                // 背题模式下，自动标记正确选项为选中状态
                if (this.currentMode === 'reviewOnly' && correctAnswers.includes(normalizedCheck)) {
                  this.$set(childOption, 'is_selected', true);
                  childOption.is_user_correct = true;
                }
              })
            } catch (error) {
              console.error(`更新选项状态错误(试题${i+1}):`, error)
            }
          }
        }
        
        // 判断整个案例题是否全部正确 - 使用$set确保响应式更新
        this.$set(this.question, 'is_correct', totalCorrect === subQuestions.length)
        this.$set(this.question, 'sub_questions_results', results)
        this.$set(this.question, 'correct_count', totalCorrect)
        
        // 设置主题目的已答状态，供答题卡识别
        this.$set(this.question, 'is_answered', true)
        this.$set(this.question, 'is_selected', true)
        
        // 设置 user_answer 字段，用于答题卡显示已答状态
        // 使用子题结果的简单摘要
        const answerSummary = subQuestions.map((sub, idx) => {
          const selectedOpts = (sub.children || sub.option || []).filter(opt => opt.is_selected)
          return selectedOpts.map(opt => opt.check).join(',')
        }).filter(Boolean).join('; ')
        this.$set(this.question, 'user_answer', answerSummary)
        
        // 触发父组件事件，通知答案已提交
        this.$emit('answer-submitted', {
          questionIndex: this.questionIndex,
          question: this.question,
          results: results
        })
        
        // 触发submit事件，确保父组件能够更新swiperList
        this.$emit('submit', {
          questionIndex: this.questionIndex,
          question: this.question,
          results: results
        })
        
        // 模式控制跳转
        this.handleSubmissionByMode();
        
        // 触发answer-shown事件，确保父组件能够显示参考答案、解析、笔记等内容
        this.$emit('answer-shown', {
          questionIndex: this.questionIndex,
          question: this.question,
          isCorrect: this.question.is_correct
        });
        
        // 显示提交成功提示
        this.showToastMessage('答案提交成功');
        
        // 提交后跳转到子试题的第一题
        this.$nextTick(() => {
          this.currentTabIndex = 0;
        });
      } catch (error) {
        // 显示错误提示
        this.showToastMessage('答案提交失败，请重试')
      }
    },
  
  // 根据模式处理提交后的逻辑
    handleSubmissionByMode() {
      try {

        switch (this.currentMode) {
          case 'normal':
            // 答题模式：提交后跳转下一题
            this.$nextTick(() => {
              setTimeout(() => {
                this.$emit('next-question', {
                  questionIndex: this.questionIndex
                });
              }, 300);
            });
            break;
          case 'learnPractice':
            // 学练结合模式：提交后不自动跳转，触发显示解析和笔记
            // 触发父组件显示解析和笔记组件
            this.$emit('answer-shown', {
              questionIndex: this.questionIndex,
              question: this.question,
              isCorrect: this.question.is_correct
            });
            break;
          case 'reviewOnly':
            // 背题模式：仅提示（进入时已自动显示）
            break;
          default:
            break;
        }
      } catch (error) {
        console.error('处理提交后逻辑失败:', error);
      }
    },
  }
}
</script>

<style lang="scss" scoped>
.compound-question-container {
  background-color: #fff;
  margin: 20rpx;
  margin-bottom: 20rpx;
}

.question-area {
  padding: 20rpx;
  margin-bottom: 30rpx;
}

.question-header {
  display: flex;
  align-items: center;
  margin-bottom: 20rpx;
}

.question-info {
  display: flex;
  align-items: center;
  width: 100%;
}

.question-number {
  font-size: 32rpx;
  font-weight: bold;
  margin-right: 10rpx;
}

.question-type-tag {
  font-size: 24rpx;
  padding: 4rpx 12rpx;
  border-radius: 6rpx;
  font-weight: bold;
}

.question-content {
  font-size: 28rpx;
  line-height: 1.6;
}

.case-subquestions-area {
  flex: 1;
}

.subquestion-content {
  margin-top: 20rpx;
}

.subquestion-item {
  width: 100%; /* ✅ 默认全宽 */
  box-sizing: border-box; /* ✅ 确保 padding 不超出容器 */
  border-radius: 12rpx;
  padding: 10rpx;
}

.subquestion-title {
  margin-bottom: 20rpx;
}

.subquestion-number {
  font-size: 28rpx;
  font-weight: bold;
  margin-right: 10rpx;
}

.subquestion-type-tag {
  font-size: 28rpx;
  padding: 4rpx 12rpx;
  border-radius: 6rpx;
  margin-right: 10rpx;
}

.subquestion-options {
  margin-top: 20rpx;
}

/* 响应式布局样式 */
.responsive-layout {
  /* 默认移动端布局 */
  display: block;
  width: 100%;
}

/* 大屏设备水平分栏布局 (414px 及以上) */
@media screen and (min-width: 414px) {
  .responsive-layout {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
  }
  
  .subquestion-item {
    width: calc(50% - 10rpx);
  }
}

/* 小屏设备垂直排列 (375px 及以下) */
@media screen and (max-width: 375px) {
  .responsive-layout {
    display: block;
  }
  
  .subquestion-item {
    width: 100%;
  }
}

.option-item {
  display: flex;
  align-items: flex-start;
  padding: 16rpx 20rpx;
  margin-bottom: 16rpx;
  background-color: #fff;
  border-radius: 8rpx;
  border: 2rpx solid #e9ecef;
  cursor: pointer;
  transition: all 0.3s ease;
  min-height: 80rpx;
  
  &.selected {
    border-color: #366EF4;
    background-color: rgba(54, 110, 244, 0.05);
  }
  
  &.correct {
    border-color: #52c41a; /* 答对的选项边框设为绿色 */
    background-color: rgba(82, 196, 26, 0.1);
  }
  
  &.user-correct {
    border-color: #52c41a; /* 答对的选项边框设为绿色 */
    background-color: rgba(82, 196, 26, 0.1);
  }
  
  &.user-wrong {
    border-color: #ff4d4f; /* 答错的选项边框设为红色 */
    background-color: rgba(255, 77, 79, 0.1);
  }
  
  &.disabled {
    cursor: not-allowed;
    opacity: 0.9;
    pointer-events: none;
  }
  
  &:last-child {
    margin-bottom: 0;
  }
}

.option-prefix {
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

.option-content {
  flex: 1;
  font-size: 28rpx;
  line-height: 1.5;
}

/* 问答题区域样式  start */
.subquestion-essay-area {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.essay-input-container {
  flex: 1;
  background-color: #FFFFFF;
  border-radius: 12rpx;
  padding: 20rpx;
  margin-bottom: 30rpx;
  border: 2rpx solid #a5a5a5;
  
  .comment-textarea {
    width: 100%;
    height: 150rpx;
    resize: none;
    overflow: hidden;
    font-size: 28rpx;
    line-height: 1.5;

  }
}

.essay-input-container-disabled {
  background-color: #F8F9FA;
  border: 2rpx solid #e9ecef;
}

/* 问答题区域样式  end */

.essay-input {
    width: 100%;
    min-height: 200rpx;
    padding: 16rpx;
    border: 2rpx solid #e9ecef;
    border-radius: 8rpx;
    font-size: 28rpx;
    line-height: 1.5;
    box-sizing: border-box;
    transition: border-color 0.3s ease;
    
    &:disabled {
      background-color: #f5f5f5;
      cursor: not-allowed;
      border-color: #d9d9d9;
    }
    
    &:focus {
      border-color: #366EF4;
      outline: none;
    }
  }

.subquestion-answer-area {
  margin-top: 30rpx;
  padding-top: 20rpx;
  border-top: 2rpx solid #e9ecef;
}

.section-title {
  font-size: 28rpx;
  font-weight: bold;
  color: #333;
  margin-bottom: 16rpx;
}

.user-answer-section {
    margin-bottom: 24rpx;
    padding: 16rpx;
    background-color: #f0f5ff;
    border-radius: 8rpx;
    border-left: 4rpx solid #366EF4;
  }
  
  .correct-answer-section,
  .analysis-section,
  .comment-section {
    margin-bottom: 20rpx;
  }

.essay-answer-content,
.essay-correct-answer {
  padding: 16rpx;
  background-color: #f8f9fa;
  border-radius: 8rpx;
  font-size: 28rpx;
  line-height: 1.5;
}



.selected-answer,
  .selected-answers {
    padding: 16rpx;
    background-color: #f8f9fa;
    border-radius: 8rpx;
    font-size: 28rpx;
    line-height: 1.5;
    border-left: 4rpx solid #366EF4;
    margin-top: 8rpx;
    
    &.correct {
      color: #52c41a;
      border-left-color: #52c41a;
    }
    
    &.wrong {
      color: #ff4d4f;
      border-left-color: #ff4d4f;
    }
  }
  
  .option-correct-answer {
    padding: 16rpx;
    background-color: #f0fff0;
    border-radius: 8rpx;
    font-size: 28rpx;
    line-height: 1.5;
    border-left: 4rpx solid #52c41a;
  }
.submit-btn {
  background-color: #366EF4;
  color: white;
  border-radius: 16rpx;
  font-size: 32rpx;
  width: 100%;
  border: none;
}

.case-result-section-user {
  margin-top: 30rpx;
  padding: 24rpx;
  background-color: #f5f9ff;
  border-radius: 12rpx;
  border-left: 4rpx solid #36f4c5;
}

.case-result-section {
  margin-top: 30rpx;
  padding: 24rpx;
  background-color: #f5f9ff;
  border-radius: 12rpx;
  border-left: 4rpx solid #366EF4;
}

.result-title {
  font-size: 30rpx;
  font-weight: bold;
  color: #333;
  margin-bottom: 20rpx;
  padding-bottom: 12rpx;
  border-bottom: 1rpx solid #e9ecef;
}

.result-content {
  display: flex;
  flex-direction: column;
}

.result-text {
  font-size: 28rpx;
  color: #333;
  line-height: 1.7;
  margin-bottom: 24rpx;
  word-break: break-word;
}

/* 为mp-html组件添加样式 - 使用兼容WXSS的语法 */
.result-content {
  /* 直接设置mp-html组件的样式 */
  font-size: 28rpx;
  color: #333;
  line-height: 1.7;
  white-space: normal;
  word-wrap: break-word;
  word-break: break-all;
}

/* 为参考答案区域添加段落间距 */
.result-content p {
  margin-bottom: 20rpx;
  line-height: 1.7;
}

.result-content p:last-child {
  margin-bottom: 0;
}

/* 为列表添加样式 */
.result-content ul,
.result-content ol {
  margin-bottom: 20rpx;
  padding-left: 32rpx;
}

.result-content li {
  margin-bottom: 12rpx;
  line-height: 1.6;
}

.result-content li:last-child {
  margin-bottom: 0;
}

/* 为子题答案区域添加样式 */
.sub-answer-section {
  margin-bottom: 32rpx;
  padding-bottom: 24rpx;
  border-bottom: 1rpx solid #e9ecef;
}

.sub-answer-section:last-child {
  margin-bottom: 0;
  padding-bottom: 0;
  border-bottom: none;
}

.sub-answer-title {
  font-size: 28rpx;
  font-weight: bold;
  color: #366EF4;
  margin-bottom: 16rpx;
  padding-bottom: 8rpx;
  border-bottom: 1rpx solid #e9ecef;
}

.sub-answer-content {
  font-size: 28rpx;
  color: #333;
  line-height: 1.7;
}

.sub-answer-content p {
  margin-bottom: 16rpx;
  line-height: 1.7;
}

.sub-answer-content p:last-child {
  margin-bottom: 0;
}

.sub-answer-content ul,
.sub-answer-content ol {
  margin-bottom: 16rpx;
  padding-left: 32rpx;
}

.sub-answer-content li {
  margin-bottom: 8rpx;
  line-height: 1.6;
}

.sub-answer-content li:last-child {
  margin-bottom: 0;
}

.result-rate {
  font-size: 28rpx;
  color: #366EF4;
  margin-bottom: 10rpx;
}

.result-overall {
  font-size: 28rpx;
  font-weight: bold;
  
  &.correct {
    color: #52c41a;
  }
  
  &.wrong {
    color: #ff4d4f;
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

