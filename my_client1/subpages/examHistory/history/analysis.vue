<template>
  <view>
    <view class="page tn-safe-area-inset-bottom">
    <view class="tn-navbg" :style="{height: vuex_custom_bar_height + 'px'}">
      <tn-nav-bar fixed alpha customBack>
        <template #back>
          <view class="tn-custom-nav-bar__back">
          <text
            class="icon tn-icon-left tn-color-white"
            @click="goBack"
          />
          <text
            class="icon tn-icon-home-capsule-fill tn-color-white tn-margin-left-sm"
            @click="goHome"
          />
          </view>
        </template>
        <view class="tn-flex tn-flex-col-center tn-flex-row-center">
          <text class="tn-text-bold tn-text-xl tn-color-white">练习报告</text>
        </view>
      </tn-nav-bar>
	</view>
    <view class="page-content" :style="{paddingTop: vuex_custom_bar_height + 'px'}">
      <!-- 顶部背景装饰 -->
      <view class="top-bg-decoration" :style="{backgroundColor: mainColor}">
        <text class="exam-title">{{examTitle}}</text>
      </view>
            
      
      <!-- 主卡片 -->
      <view class="main-card">
        <view class="card-header">
        <!-- 提交时间 -->
        <view class="submit-time">
          <text class="time-label">交卷时间：</text>
          <text class="time-value">{{submitTime}}</text>
        </view>
        <view class="nav-right-btn" @click="shareReport">
          <text class="tn-icon-share tn-color-gray">分享</text>
        </view>
        </view>
        <!-- 圆形进度条 - 正确率 -->
        <view class="circular-progress-section">
          <view class="circular-progress">
            <view class="progress-ring">
              <view class="progress-circle" :style="{background: getProgressGradient()}">
                <view class="progress-inner">
                  <text class="accuracy-percentage">{{resultData.accuracy}}%</text>
                  <text class="accuracy-label-text">— 正确率 —</text>
                </view>
              </view>
            </view>
          </view>
          <text class="progress-desc">正确题数/答题数({{resultData.correct_count}}/{{resultData.total_count}})</text>
        </view>
        
        <!-- 统计数据行 -->
        <view class="stats-row">
          <view class="stat-col">
            <text class="stat-label">练习时长</text>
            <text class="stat-value">{{practiceTime}}</text>
          </view>
          <view class="stat-col">
            <text class="stat-label">我的错题</text>
            <text class="stat-value stat-error">{{resultData.error_count}}</text>
          </view>
          <view class="stat-col">
            <text class="stat-label">本次答题数</text>
            <text class="stat-value">{{resultData.total_count}}</text>
          </view>
        </view>
      </view>
      
      <!-- 答题卡 -->
      <view class="answer-card" v-if="questionList.length > 0 && groupedQuestions.length > 0">
        <view class="card-header">
          <text class="card-title">答题卡</text>
          <view class="status-legend">
            <view class="legend-item">
              <view class="legend-dot correct-dot"></view>
              <text class="legend-text">正确</text>
            </view>
            <view class="legend-item">
              <view class="legend-dot half-dot"></view>
              <text class="legend-text">半对</text>
            </view>
            <view class="legend-item">
              <view class="legend-dot error-dot"></view>
              <text class="legend-text">错误</text>
            </view>
            <view class="legend-item">
              <view class="legend-dot undone-dot"></view>
              <text class="legend-text">未做</text>
            </view>
          </view>
        </view>
        
        <!-- 按题型分组显示答题卡 -->
        <view  v-if="groupedQuestions.length > 0">
          <block v-for="(group, groupIndex) in groupedQuestions" :key="groupIndex">
            <view class="type-group">
              <text class="type-title">{{group.typeName}}</text>
              <view class="answer-numbers">
                <view 
                  class="answer-number" 
                  v-for="(q, qIndex) in group.questions" 
                  :key="qIndex"
                  :class="[!q.user_answer || q.user_answer === '-' ? 'undone' : (q.exam_type === 5 || q.exam_type === 6 ? 'correct' : (q.is_half_correct ? 'half' : (q.is_correct ? 'correct' : 'error')))]"
                  hover-class="answer-number-hover"
                  @click="scrollToQuestion(q.originalIndex)">
                  <text>{{q.displayNumber}}</text>
                </view>
              </view>
            </view>
          </block>
        </view>
      </view>
      
      <!-- 答题详情列表 -->
      <view class="question-list" v-if="questionList.length > 0">
        <view class="section-title">题目解析</view>
        <block v-for="(question, index) in questionList" :key="index">
          <view 
            :id="'question-item-' + index"
            class="question-item" 
            :class="{ 'question-correct': question.is_correct, 'question-error': !question.is_correct }">
            <view class="question-header">
              <view class="question-number" :style="{backgroundColor: question.is_correct ? '#52c41a' : '#ff4d4f'}">{{index + 1}}</view>
              <view class="question-info">
                <mp-html class="question-title" :content="question.title"></mp-html>
                <view class="question-meta">
                  <text class="meta-item">
                    <text class="meta-label">题型：</text>
                    <text class="meta-value">{{question.exam_type_name}}</text>
                  </text>
                  <text class="meta-item">
                    <text class="meta-label">难度：</text>
                    <text class="meta-value difficulty-level" :class="'exam_level-' + question.exam_level">{{['', '简单', '中等', '困难'][question.exam_level] || '未知'}}</text>
                  </text>
                  <text class="meta-item">
                    <text class="meta-label">分值：</text>
                    <text class="meta-value">{{question.score || 0}}分</text>
                  </text>
                </view>
              </view>
              <view class="question-status" :class="question.is_correct ? 'status-correct' : 'status-error'">
                <text class="tn-icon-success-circle" v-if="question.is_correct"></text>
                <text class="tn-icon-close-circle" v-else></text>
              </view>
            </view>
            
            <view class="question-content">
              <!-- 普通题显示逻辑 -->
              <template v-if="!question.is_case_question">
                <!-- 选项列表 - 根据题型显示不同样式 -->
                <view class="option-list" v-if="question.options && question.options.length > 0">
                  <view class="option-item" 
                        v-for="(option, optIndex) in question.options" 
                        :key="optIndex"
                        :class="{ 
                          'option-selected': option.is_selected, 
                          'option-correct': option.is_correct,
                          'option-wrong': option.is_selected && !option.is_correct,
                          'essay-option': [5, 6].includes(question.exam_type) // 5=填空题, 6=问答题
                        }">
                      <!-- 非问答题显示序号，问答题不显示 -->
                      <view class="option-letter" v-if="![5, 6].includes(question.exam_type)">{{getOptionLabel(optIndex)}}</view>
                      <mp-html class="option-content" :content="option.content"></mp-html>
                  </view>
                </view>
                
                <!-- 用户回答 - 根据题型显示不同标签 -->
                <view class="user-answer" v-if="question.user_answer">
                  <text class="answer-label">{{[5, 6].includes(question.exam_type) ? '用户回答：' : '用户选择：'}}</text>
                  <mp-html class="answer-value-html" :content="question.user_answer"></mp-html>
                </view>
                
                <!-- 正确答案 -->
                <view class="correct-answer" v-if="question.correct_answer">
                  <text class="answer-label">正确答案：</text>
                  <mp-html class="answer-value-html" :content="question.correct_answer"></mp-html>
                </view>
              </template>
              
              <!-- 案例题子试题显示逻辑 -->
              <template v-else-if="question.is_case_question && question.sub_questions.length > 0">
                <view class="case-sub-questions">
                  <view class="sub-question" 
                        v-for="(subQ, subIdx) in question.sub_questions" 
                        :key="subIdx"
                        :class="{ 
                          'sub-question-correct': subQ.is_correct, 
                          'sub-question-error': !subQ.is_correct
                        }">
                    <!-- 子试题标题 -->
                    <view class="sub-question-title">
                      <text class="sub-question-number">子题 {{subQ.case_index}}：</text>
                      <mp-html class="sub-question-content" :content="subQ.title"></mp-html>
                      <text class="sub-question-score">({{subQ.score}}分)</text>
                    </view>
                    
                    <!-- 子试题选项（如果有） -->
                    <view class="sub-option-list" v-if="subQ.children && subQ.children.length > 0">
                      <view class="sub-option-item" 
                            v-for="(childOpt, childIdx) in subQ.children" 
                            :key="childIdx"
                            :class="{ 
                              'sub-option-selected': subQ.is_selected || subQ.status === 'selected', 
                              'sub-option-correct': subQ.is_correct || subQ.is_check === '1',
                              'sub-option-wrong': (subQ.is_selected || subQ.status === 'selected') && !(subQ.is_correct || subQ.is_check === '1'),
                              'sub-essay-option': [5, 6].includes(subQ.exam_type) // 5=填空题, 6=问答题
                            }">
                        <!-- 非问答题显示序号，问答题不显示 -->
                        <view class="sub-option-letter" v-if="![5, 6].includes(subQ.exam_type)">{{getOptionLabel(childIdx)}}</view>
                        <mp-html class="sub-option-content" :content="childOpt.title"></mp-html>
                      </view>
                    </view>
                    
                    <!-- 子试题用户回答 -->
                    <view class="sub-user-answer" v-if="subQ.user_answer">
                      <text class="answer-label">{{[5, 6].includes(subQ.exam_type) ? '用户回答：' : '用户选择：'}}</text>
                      <text class="answer-value">{{subQ.user_answer}}</text>
                    </view>
                    
                    <!-- 子试题正确答案 -->
                    <view class="sub-correct-answer" v-if="subQ.correct_answer">
                      <text class="answer-label">正确答案：</text>
                      <mp-html class="sub-answer-value-html" :content="subQ.correct_answer"></mp-html>
                    </view>
                  </view>
                </view>
              </template>
              
              <!-- 解析 - 适用于所有题型 -->
              <view class="answer-analysis" v-if="question.analysis">
                <text class="analysis-label">解析：</text>
                <view class="analysis-content">
                  <mp-html :content="question.analysis"></mp-html>
                </view>
              </view>
            </view>
          </view>
        </block>
      </view>
      
      <!-- 加载状态 -->
      <view class="loading-container" v-if="isLoading">
        <text class="tn-icon-loading tn-text-xl"></text>
        <text class="tn-margin-top-sm">加载中...</text>
      </view>
      
      <!-- 空状态 -->
      <view class="empty-container" v-if="!isLoading && questionList.length === 0">
        <tn-empty mode="list" text="暂无答题数据"></tn-empty>
      </view>
      
      <!-- 底部按钮 -->
      <view class="bottom-buttons" v-if="questionList.length > 0">
        <tn-button 
          backgroundColor="tn-main-gradient-orangered" 
          padding="40rpx"
          fontColor="tn-color-white"
         @click="showQuestionCommonBtn('error')">
          错题解析
        </tn-button>
        <tn-button 
          backgroundColor="tn-main-gradient-indigo" 
          padding="40rpx"
          fontColor="tn-color-white" 
          @click="showQuestionCommonBtn('all')">
          全部解析
        </tn-button>
        <tn-button 
          backgroundColor="tn-main-gradient-blue" 
          padding="40rpx"
          fontColor="tn-color-white" 
          @click="showChart">
          答题分析
        </tn-button>
      </view>
    </view>
    <!-- 返回顶部按钮 -->
    <view 
      v-if="showBackToTop" 
      class="back-to-top-btn"
      :style="{backgroundColor: mainColor}"
      hover-class="back-to-top-btn-hover"
      @click="handleFabBtnClick"
    >
      <text class="tn-icon-up tn-color-white tn-text-xl"></text>
    </view>
      
  </view>
</view>
</template>

<script>
import template_page_mixin from '@/libs/mixin/template_page_mixin.js'
import ExamDataAdapter from '@/util/examDataAdapter.js'

export default {
  name: 'QuestionAnalysis',
  mixins: [template_page_mixin],
  data() {
    return {
      mainColor: getApp().globalData.mainColor,
      uid: '',
      examination_uid: '',
      id: 0,
      isLoading: false,
      examTitle: '建设工程经济每日一练', // 考试标题
      submitTime: '', // 提交时间
      practiceTime: '0:00:00', // 练习时长
      resultData: {
        correct_count: 0,
        error_count: 0,
        total_count: 0,
        accuracy: 0
      },
      questionList: [],
      groupedQuestions: [], // 按题型分组的题目
      showBackToTop: false,
      errorOptionUid: '',
      allQuestionUid: '',
      selectedErrorItem: null
    }
  },
  computed: {
    // 获取进度条渐变色
    getProgressGradient() {
      return () => {
        const accuracy = this.resultData.accuracy || 0
        if (accuracy >= 80) {
          return 'conic-gradient(#52c41a 0%, #52c41a ' + accuracy + '%, #e8e8e8 ' + accuracy + '%, #e8e8e8 100%)'
        } else if (accuracy >= 60) {
          return 'conic-gradient(#faad14 0%, #faad14 ' + accuracy + '%, #e8e8e8 ' + accuracy + '%, #e8e8e8 100%)'
        } else {
          return 'conic-gradient(#ff4d4f 0%, #ff4d4f ' + accuracy + '%, #e8e8e8 ' + accuracy + '%, #e8e8e8 100%)'
        }
      }
    }
  },
  onLoad(option) {
    this.uid = option.uid || ''
    this.examination_uid = option.examination_uid || ''
    this.id = option.id || 0
    // 只有当uid或id有一个时请求数据
    if (this.uid || this.id) {
      this.fetchResultData()
    }
  },
  
  // 分享给朋友
  onShareAppMessage(res) {
    return {
      title: `我的${this.examTitle}成绩单 - 正确率${this.resultData.accuracy}%`,
      path: `/pages/index/index`
    }
  },
  
  // 分享到朋友圈
  onShareTimeline() {
    return {
      title: `我的${this.examTitle}成绩单 - 正确率${this.resultData.accuracy}%`,
      query: ``
    }
  },
  onPageScroll(e) {
			// 监听页面滚动，控制返回顶部按钮显示			
			this.showBackToTop = e.scrollTop > 300;
	},
  methods: {
    goBack() {
      uni.navigateBack()
    },
    
    handleFabBtnClick() {
      uni.pageScrollTo({
        scrollTop: 0,
        duration: 300
      })
    },
    getOptionLabel(index) {
      return String.fromCharCode(65 + index)
    },
    
    fetchResultData() {
      this.isLoading = true
      uni.showLoading({
        title: '加载中',
        icon: 'none'
      })
      
      // 使用考试历史详情API获取单条记录详情
      this.$api.apiExaminationHistoryDetail({
        uid: this.uid,
        id: this.id
      }).then(res => {
        if (res.code === 1 && res.data) {
          const mockRecord = res.data
          // error_option_uid 可能是字符串或数组，需要处理
          if (Array.isArray(mockRecord.error_option_uid)) {
            this.errorOptionUid = mockRecord.error_option_uid.join(',') || ''
          } else {
            this.errorOptionUid = mockRecord.error_option_uid || ''
          }
          this.resultData.correct_count = mockRecord.correct_count || 0
          this.resultData.error_count = mockRecord.error_count || 0
          this.resultData.total_count = (mockRecord.correct_count || 0) + (mockRecord.error_count || 0)
          this.resultData.accuracy = this.resultData.total_count > 0 ? Math.round((this.resultData.correct_count / this.resultData.total_count) * 100) : 0
          
          // 设置考试标题
          this.examTitle = mockRecord.title || '练习报告'
          
          // 格式化提交时间 - 处理多种可能的字段名
          const submitTimeValue = mockRecord.submit_time || mockRecord.submitTime || mockRecord.create_time || mockRecord.createTime
          
          // 如果提交时间只是纯时间，尝试使用创建时间的日期部分
          const createTimeValue = mockRecord.create_time || mockRecord.createTime
          this.submitTime = this.formatSubmitTime(submitTimeValue, createTimeValue)
          
          // 格式化练习时长 - 处理多种可能的字段名
          const durationValue = mockRecord.practice_duration || mockRecord.duration || mockRecord.time_used || 0
          this.practiceTime = this.formatPracticeTime(durationValue)
          
          // 递归解析JSON函数，处理多重编码
          const recursiveParseJson = (data, maxAttempts = 3) => {
            let parsed = data
            for (let i = 0; i < maxAttempts; i++) {
              if (typeof parsed !== 'string') break
              try {
                parsed = JSON.parse(parsed)
              } catch (e) {
                break
              }
            }
            return parsed
          }
          
          // 获取该次答题的详细题目信息
          if (mockRecord.options) {
            try {
              // 解析options JSON字符串为数组（处理多重JSON编码）
              let optionsList = mockRecord.options
              // 递归解析，最多尝试3次
              optionsList = recursiveParseJson(optionsList)
                            
                            
              // 确保 optionsList 是数组
              if (!Array.isArray(optionsList)) {
                this.isLoading = false
                uni.hideLoading()
                return
              }
              
              // 提取所有uid值，并过滤掉空值
              const questionUids = optionsList.map(item => item.uid).filter(uid => uid).join(',')
              this.allQuestionUid = questionUids
              if (!questionUids) {
                this.isLoading = false
                uni.hideLoading()
                return
              }
              
              // 传递用户的答题记录给fetchQuestionDetails
              this.fetchQuestionDetails(questionUids, optionsList)
            } catch (e) {
              this.isLoading = false
              uni.hideLoading()
            }
          } else if (mockRecord.examination_uid) {
            // 兼容旧版本，当options不存在时使用examination_uid
            this.fetchQuestionDetails(mockRecord.examination_uid)
          } else {
            this.isLoading = false
            uni.hideLoading()
          }
        } else {
          this.$func.showToast('获取数据失败')
          this.isLoading = false
          uni.hideLoading()
        }
      }).catch(error => {
        this.$func.showToast('网络错误，请重试')
        this.isLoading = false
        uni.hideLoading()
      })
    },
    
    fetchQuestionDetails(questionUids, userAnswers) {
      if (!questionUids) {
        this.isLoading = false
        uni.hideLoading()
        return
      }

      // 获取题库中的所有题目
      this.$api.apiQuestionOrderList({
        analysis_quid: questionUids,
        question_count: this.resultData.total_count,
        page_no: 1,
        page_size: 999
      }).then(res => {
        if (res.code === 1 && res.data) {
          // 处理两种可能的响应格式：res.data.list 或 res.data是一个数组
          const questionList = (res.data.list || res.data || [])
          if (Array.isArray(questionList) && questionList.length > 0) {
            const processedList = this.processQuestionList(questionList, userAnswers)
            // 使用 Vue.set 确保响应式更新
            this.$set(this, 'questionList', processedList)
            // 处理完题目后，按题型分组
            this.groupQuestionsByType()
          } else {
            this.isLoading = false
            uni.hideLoading()
          }
        } else {
          this.isLoading = false
          uni.hideLoading()
        }
        this.isLoading = false
        uni.hideLoading()
      }).catch(error => {
        this.isLoading = false
        uni.hideLoading()
      })
    },
    
    processQuestionList(questions, userAnswers = []) {
      const result = ExamDataAdapter.processQuestionList(questions, userAnswers)
      return result
    },
    
    // 格式化提交时间
    formatSubmitTime(timestamp, fallbackDate) {
      if (!timestamp) return ''
      
      // 如果已经是完整的日期时间字符串，直接返回
      if (typeof timestamp === 'string' && (timestamp.includes('-') || timestamp.includes('/'))) {
        return timestamp
      }
      
      // 处理纯时间字符串（如 "22:47:12"）
      if (typeof timestamp === 'string' && timestamp.includes(':') && !timestamp.includes('-') && !timestamp.includes('/')) {
        const baseDate = fallbackDate 
          ? new Date(ExamDataAdapter.normalizeTimestamp(fallbackDate))
          : new Date()
        
        if (!isNaN(baseDate.getTime())) {
          const year = baseDate.getFullYear()
          const month = String(baseDate.getMonth() + 1).padStart(2, '0')
          const day = String(baseDate.getDate()).padStart(2, '0')
          return `${year}.${month}.${day} ${timestamp.substring(0, 5)}`
        }
      }
      
      // 使用适配器统一时间戳为 13 位
      const normalizedTimestamp = ExamDataAdapter.normalizeTimestamp(timestamp)
      const date = new Date(normalizedTimestamp)
      
      if (isNaN(date.getTime())) {
        return ''
      }
      
      const year = date.getFullYear()
      const month = String(date.getMonth() + 1).padStart(2, '0')
      const day = String(date.getDate()).padStart(2, '0')
      const hour = String(date.getHours()).padStart(2, '0')
      const minute = String(date.getMinutes()).padStart(2, '0')
      
      return `${year}.${month}.${day} ${hour}:${minute}`
    },
    
    // 格式化练习时长
    formatPracticeTime(seconds) {
      // ✅ 先转换为数字再判断
      if (typeof seconds === 'string') {
        seconds = parseInt(seconds, 10)
      }
      
      // ✅ 使用严格的数字验证
      if (!seconds || isNaN(seconds) || seconds <= 0) {
        return '0:00:00'
      }
      
      const hours = Math.floor(seconds / 3600)
      const minutes = Math.floor((seconds % 3600) / 60)
      const secs = Math.floor(seconds % 60)
      return `${hours}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`
    },
    
    // 按题型分组题目
    groupQuestionsByType() {
      const groups = {}
      this.questionList.forEach((q, index) => {
        const typeKey = q.exam_type || 0
        const typeName = q.exam_type_name || '未知题型'
        
        if (!groups[typeKey]) {
          groups[typeKey] = {
            typeName: typeName,
            questions: []
          }
        }
        
        groups[typeKey].questions.push({
          ...q,
          originalIndex: index,
          displayNumber: index + 1  // 修改为全局连续编号，与做题页面保持一致
        })
      })
      
      const groupedArray = Object.values(groups)
      // 使用 Vue.set 确保响应式更新
      this.$set(this, 'groupedQuestions', groupedArray)
    },
    
    // 滚动到指定题目
    scrollToQuestion(index) {
      const query = uni.createSelectorQuery().in(this)
      query.select('#question-item-' + index).boundingClientRect()
      query.selectViewport().scrollOffset()
      query.exec((res) => {
        if (res[0]) {
          const scrollTop = res[0].top + res[1].scrollTop - 100
          uni.pageScrollTo({
            scrollTop: scrollTop,
            duration: 300
          })
        }
      })
    },
    // 点击跳转做题页面
    showQuestionCommonBtn(active) {
      // 准备传递给commonQuestion.vue的参数
      let questionUids = []
      this.selectedErrorItem = null
      if (active === 'error') {
        // active === 'error'，表示错题题目，此处应获取本次答题所有错题的uid
        if (this.errorOptionUid) {
          // 尝试解析 JSON 字符串，如果失败则按逗号分割
          try {
            const parsed = JSON.parse(this.errorOptionUid)
            questionUids = Array.isArray(parsed) ? parsed.filter(uid => uid) : []
          } catch (e) {
            questionUids = this.errorOptionUid.split(',').filter(uid => uid)
          }
        }
      } else {
        // 否则active === 'all'，表示全部题目，此处应获取本次答题所有题目的uid
        if (this.allQuestionUid) {
          try {
            const parsed = JSON.parse(this.allQuestionUid)
            questionUids = Array.isArray(parsed) ? parsed.filter(uid => uid) : []
          } catch (e) {
            questionUids = this.allQuestionUid.split(',').filter(uid => uid)
          }
        }
      }
      
      // 验证是否有有效的题目
      if (questionUids.length === 0) {
        uni.showToast({
          title: active === 'error' ? '暂无错题' : '暂无题目',
          icon: 'none'
        })
        return
      }
      
      // 将数组转换为逗号分隔的字符串
      const joinedString = questionUids.join(',')
      this.selectedErrorItem = joinedString
      
      // 收集用户答案
      const userAnswers = {};
      
      // 根据questionUids从questionList中找到对应的问题，提取用户答案
      questionUids.forEach(uid => {
        // 确保uid为字符串类型
        const uidStr = String(uid);
        const question = this.questionList.find(q => String(q.uid) === uidStr);
        if (question) {
          // 使用字符串作为userAnswers的key，确保与commonQuestion组件中的处理一致
          // 收集用户答案，即使是'-'也收集，后续由commonQuestion处理
          userAnswers[uidStr] = question.user_answer;
          
          // 检查是否是案例题（exam_type=6），如果是，收集子试题的答案
          if (question.exam_type === 6 && question.option && Array.isArray(question.option)) {
            // 遍历案例题的子试题
            question.option.forEach(subQuestion => {
              if (subQuestion.uid) {
                const subUidStr = String(subQuestion.uid);
                userAnswers[subUidStr] = subQuestion.user_answer;
              }
            });
          }
        } else {
          // 调试：记录未找到的题目

        }
      });
      


      

      const examSettings = {
        history_uid: this.uid,
        history_id: this.id,
        analysis_quid: joinedString,
        question_count: questionUids.length,
        questions_error: 1,
        questions_type: 0, // 0表示错题解析
        mode: 'reviewOnly', // 背题模式
        practice_mode: 3,
        userAnswers: userAnswers, // 添加用户答案
        active: active // 添加active参数，标识是全部解析还是错题解析
      }
      
      // 重置选中的错题项
      this.selectedErrorItem = null
      
      // 直接跳转到答题页面，不需要经过设置步骤
      uni.navigateTo({
        url: '/subpages/exam/components/commonQuestion?examSettings=' + encodeURIComponent(JSON.stringify(examSettings))
      })
    },
    
    // 分享报告
    shareReport() {
      // 微信小程序环境：直接触发分享菜单
      // #ifdef MP-WEIXIN
      uni.showShareMenu({
        withShareTicket: true,
        menus: ['shareAppMessage', 'shareTimeline'],
        success: () => {
          // 提示用户点击右上角分享
          uni.showModal({
            title: '分享成绩单',
            content: '请点击右上角「...」按钮，选择分享给朋友或分享到朋友圈',
            showCancel: false,
            confirmText: '我知道了'
          })
        },
        fail: (err) => {
            uni.showToast({
              title: '分享功能开启失败',
              icon: 'none'
            })
          }
      })
      // #endif
      
      // 非微信小程序环境：提示不支持
      // #ifndef MP-WEIXIN
      uni.showToast({
        title: '当前环境不支持分享',
        icon: 'none'
      })
      // #endif
    },
    
    // 查看错题解析
    viewErrorAnalysis() {
      // 跳转到错题解析页面，或者滚动到第一道错题
      const firstErrorIndex = this.questionList.findIndex(q => !q.is_correct && q.user_answer !== '-')
      if (firstErrorIndex !== -1) {
        this.scrollToQuestion(firstErrorIndex)
      } else {
        uni.showToast({
          title: '没有错题',
          icon: 'none'
        })
      }
    },
    
    showChart() {
      this.$func.navigatorTo('/subpages/examHistory/history/chart?id=' + this.id);
    }
  }
}
</script>

<style scoped lang="scss">
	@import "@/scss/custom_nav_bar.scss";
.page {
  background: #f5f7fa;
  min-height: 100vh;
}

.page-content {
  padding-bottom: 120rpx;
}

// 顶部背景装饰
.top-bg-decoration {
  height: 280rpx;
  padding: 40rpx 30rpx;
  position: relative;
  overflow: hidden;
  
  .exam-title {
    font-size: 32rpx;
    color: #ffffff;
    font-weight: 500;
  }
  
  // 背景装饰元素
  &::after {
    content: '';
    position: absolute;
    right: -50rpx;
    top: -50rpx;
    width: 300rpx;
    height: 300rpx;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
  }
}

// 主卡片
.main-card {
  background: #ffffff;
  border-radius: 24rpx;
  margin: -180rpx 30rpx 30rpx;
  padding: 40rpx 30rpx;
  box-shadow: 0 8rpx 32rpx rgba(0, 0, 0, 0.08);
  position: relative;
  z-index: 2;
}

// 提交时间
.submit-time {
  display: flex;
  align-items: center;
  font-size: 24rpx;
  color: #999;
  
  .time-label {
    margin-right: 10rpx;
  }
  
  .time-value {
    color: #666;
  }
}

// 圆形进度条区域
.circular-progress-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 50rpx;
}

.circular-progress {
  width: 300rpx;
  height: 300rpx;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 20rpx;
}

.progress-ring {
  width: 100%;
  height: 100%;
  position: relative;
}

.progress-circle {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

.progress-inner {
  width: 240rpx;
  height: 240rpx;
  background: #ffffff;
  border-radius: 50%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.accuracy-percentage {
  font-size: 80rpx;
  font-weight: bold;
  color: #333;
  line-height: 1;
  margin-bottom: 10rpx;
}

.accuracy-label-text {
  font-size: 24rpx;
  color: #999;
}

.progress-desc {
  font-size: 24rpx;
  color: #999;
}

// 统计数据行
.stats-row {
  display: flex;
  justify-content: space-around;
  padding: 30rpx 0;
  border-top: 2rpx solid #f0f0f0;
}

.stat-col {
  display: flex;
  flex-direction: column;
  align-items: center;
  
  .stat-label {
    font-size: 24rpx;
    color: #999;
    margin-bottom: 15rpx;
  }
  
  .stat-value {
    font-size: 40rpx;
    font-weight: bold;
    color: #333;
    
    &.stat-error {
      color: #ff4d4f;
    }
  }
}

// 答题卡
.answer-card {
  background: #ffffff;
  border-radius: 24rpx;
  margin: 0 30rpx 30rpx;
  padding: 30rpx;
  box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.05);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30rpx;
  padding-bottom: 20rpx;
  border-bottom: 2rpx solid #f0f0f0;
}

.card-title {
  font-size: 32rpx;
  font-weight: bold;
  color: #333;
}

.status-legend {
  display: flex;
  gap: 20rpx;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 8rpx;
}

.legend-dot {
  width: 20rpx;
  height: 20rpx;
  border-radius: 50%;
}

.legend-dot.correct-dot {
  background: #52c41a;
}

.legend-dot.half-dot {
  background: #faad14;
}

.legend-dot.error-dot {
  background: #ff4d4f;
}

.legend-dot.undone-dot {
  background: #d9d9d9;
}

.legend-text {
  font-size: 22rpx;
  color: #666;
}

.type-group {
  margin-bottom: 30rpx;
}

.type-group:last-child {
  margin-bottom: 0;
}

.type-title {
  font-size: 26rpx;
  color: #333;
  font-weight: 500;
  margin-bottom: 20rpx;
  display: block;
}

.answer-numbers {
  display: flex;
  flex-wrap: wrap;
  gap: 20rpx;
}

.answer-number {
  width: 80rpx;
  height: 80rpx;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28rpx;
  font-weight: 500;
  transition: all 0.3s ease;
}

.answer-number.correct {
  background: rgba(82, 196, 26, 0.1) !important;
  color: #52c41a !important;
  border: 2rpx solid #52c41a !important;
}

.answer-number.half {
  background: rgba(255, 193, 7, 0.1) !important;
  color: #ffc107 !important;
  border: 2rpx solid #ffc107 !important;
}

.answer-number.error {
  background: rgba(255, 77, 79, 0.1) !important;
  color: #ff4d4f !important;
  border: 2rpx solid #ff4d4f !important;
}

.answer-number.undone {
  background: #f5f5f5 !important;
  color: #999 !important;
  border: 2rpx solid #d9d9d9 !important;
}

.answer-number-hover {
  transform: scale(0.95);
}

// 题目列表区域优化

.question-list {
  background: #fff;
  padding: 30rpx 20rpx;
  margin: 20rpx;
  border-radius: 16rpx;
  box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.05);
  margin-bottom: 30rpx;
  
  .section-title {
    font-size: 32rpx;
    font-weight: bold;
    color: #333;
    margin-bottom: 30rpx;
    padding-bottom: 15rpx;
    border-bottom: 2rpx solid #f0f0f0;
  }
  
  .question-item {
    padding: 25rpx 0;
    border-bottom: 1rpx solid #f5f5f5;
    
    &:last-child {
      border-bottom: none;
    }
  }
  
  .question-header {
      display: flex;
      align-items: flex-start;
      margin-bottom: 20rpx;
      
      .question-number {
        width: 50rpx;
        height: 50rpx;
        border-radius: 50%;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24rpx;
        font-weight: bold;
        margin-right: 15rpx;
        flex-shrink: 0;
      }
      
      .question-info {
        flex: 1;
      }
      
      .question-title {
        font-size: 28rpx;
        color: #333;
        line-height: 40rpx;
        word-break: break-word;
        margin-bottom: 10rpx;
      }
      
      .question-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 20rpx;
        margin-bottom: 10rpx;
      }
      
      .meta-item {
        display: inline-flex;
        align-items: center;
        font-size: 22rpx;
      }
      
      .meta-label {
        color: #999;
        margin-right: 4rpx;
      }
      
      .meta-value {
        color: #666;
        font-weight: 500;
      }
      
      .difficulty-level {
        &.exam_level-1 {
          color: #52c41a;
        }
        &.exam_level-2 {
          color: #faad14;
        }
        &.exam_level-3 {
          color: #ff4d4f;
        }
      }
      
      .question-status {
        margin-left: 15rpx;
        font-size: 30rpx;
        
        &.status-correct {
          color: #52c41a;
        }
        
        &.status-error {
          color: #ff4d4f;
        }
      }
    }
  
  .question-content {
    padding-left: 30rpx;
    
    .question-desc {
      font-size: 26rpx;
      color: #666;
      margin-bottom: 20rpx;
      line-height: 40rpx;
    }
  }
  
  .option-list {
    margin-bottom: 20rpx;
    
    .option-item {
          display: flex;
          align-items: flex-start;
          padding: 15rpx 20rpx;
          margin-bottom: 12rpx;
          background-color: #f9f9f9;
          border-radius: 12rpx;
          border: 2rpx solid transparent;
          transition: all 0.3s ease;
          
          &.option-selected {
            background-color: #e6f7ff;
            border-color: #1890ff;
          }
          
          &.option-correct {
            background-color: #f6ffed;
            border-color: #52c41a;
          }
          
          &.option-wrong {
            background-color: #fff2f0;
            border-color: #ff4d4f;
          }
          
          // 问答题和填空题的特殊样式
          &.essay-option {
            background-color: #f0f8ff;
            border-color: #d4e6ff;
            
            .option-content {
              flex: 1;
              margin-left: 0;
            }
          }
          
          .option-letter {
            width: 40rpx;
            height: 40rpx;
            border-radius: 50%;
            background-color: #ddd;
            color: #666;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24rpx;
            font-weight: bold;
            margin-right: 15rpx;
            flex-shrink: 0;
          }
          
          .option-content {
            flex: 1;
            font-size: 26rpx;
            color: #666;
            line-height: 38rpx;
            word-break: break-word;
          }
        }
  }
  
  .user-answer,
  .correct-answer {
    font-size: 26rpx;
    margin-bottom: 20rpx;
    padding: 15rpx 20rpx;
    background-color: #f5f5f5;
    border-radius: 8rpx;
    
    .answer-label {
      color: #666;
      font-weight: bold;
      margin-right: 10rpx;
    }
    
    .answer-value {
      color: #0E7DFF;
      font-weight: bold;
    }
    
    .answer-value-html {
      display: inline-flex;
      color: #0E7DFF;
      font-weight: bold;
    }
  }
  
  .answer-analysis {
    margin-top: 20rpx;
    padding: 20rpx;
    background-color: #fffbe6;
    border-left: 4rpx solid #faad14;
    border-radius: 8rpx;
    
    .analysis-label {
      color: #666;
      font-weight: bold;
      display: block;
      margin-bottom: 10rpx;
    }
    
    .analysis-content {
      color: #666;
      font-size: 26rpx;
      line-height: 40rpx;
      word-break: break-word;
    }
  }
  
  /* 案例题样式 */
  .case-sub-questions {
    margin-top: 20rpx;
    padding: 0 0 20rpx 0;
  }
  
  .sub-question {
    margin-bottom: 30rpx;
    padding: 20rpx;
    background-color: #fafafa;
    border-radius: 12rpx;
    border: 2rpx solid #e8e8e8;
    
    &:last-child {
      margin-bottom: 0;
    }
    
    &.sub-question-correct {
      border-color: #b7eb8f;
      background-color: #f6ffed;
    }
    
    &.sub-question-error {
      border-color: #ffccc7;
      background-color: #fff2f0;
    }
  }
  
  .sub-question-title {
    display: flex;
    align-items: flex-start;
    margin-bottom: 15rpx;
    font-size: 26rpx;
    font-weight: bold;
    color: #333;
    
    .sub-question-number {
      margin-right: 10rpx;
      color: #1890ff;
    }
    
    .sub-question-content {
      flex: 1;
      line-height: 36rpx;
    }
    
    .sub-question-score {
      margin-left: 10rpx;
      font-size: 22rpx;
      color: #faad14;
      font-weight: normal;
    }
  }
  
  .sub-option-list {
    margin-bottom: 15rpx;
  }
  
  .sub-option-item {
    display: flex;
    align-items: flex-start;
    padding: 12rpx 16rpx;
    margin-bottom: 10rpx;
    background-color: #fff;
    border-radius: 8rpx;
    border: 1rpx solid #f0f0f0;
    
    &:last-child {
      margin-bottom: 0;
    }
    
    &.sub-option-selected {
      background-color: #e6f7ff;
      border-color: #91d5ff;
    }
    
    &.sub-option-correct {
      background-color: #f6ffed;
      border-color: #b7eb8f;
    }
    
    &.sub-option-wrong {
      background-color: #fff2f0;
      border-color: #ffccc7;
    }
    
    // 子试题问答题和填空题的特殊样式
    &.sub-essay-option {
      background-color: #f0f8ff;
      border-color: #d4e6ff;
      
      .sub-option-content {
        flex: 1;
        margin-left: 0;
      }
    }
  }
  
  .sub-option-letter {
    width: 36rpx;
    height: 36rpx;
    border-radius: 50%;
    background-color: #ddd;
    color: #666;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20rpx;
    font-weight: bold;
    margin-right: 12rpx;
    flex-shrink: 0;
  }
  
  .sub-option-content {
    flex: 1;
    font-size: 24rpx;
    color: #595959;
    line-height: 34rpx;
  }
  
  .sub-user-answer,
  .sub-correct-answer {
    font-size: 24rpx;
    margin-bottom: 12rpx;
    padding: 12rpx 16rpx;
    background-color: #f9f9f9;
    border-radius: 8rpx;
    
    .answer-label {
      color: #999;
      font-weight: bold;
      margin-right: 10rpx;
    }
    
    .sub-answer-value-html {
      display: inline-block;
      color: #0E7DFF;
      font-weight: bold;
    }
    
    .answer-value {
      color: #1890ff;
      font-weight: bold;
    }
  }
}

.loading-container,
.empty-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 100rpx 20rpx;
  color: #999;
}

.loading-container {
  animation: spin 1s linear infinite;
  
  text {
    display: block;
  }
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.bottom-buttons {
  padding: 20rpx 50rpx;
  background: transparent;
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  display: flex;
  justify-content: space-between;
  gap: 20rpx;
  z-index: 100;
  
  &::before {
    content: '';
    position: absolute;
    top: -20rpx;
    left: 0;
    right: 0;
    height: 40rpx;
    background: linear-gradient(to bottom, transparent, #f5f7fa);
  }
  
  ::v-deep .tn-button {
    height: 80rpx;
    font-size: 30rpx;
  }
  
}

.nav-right-btn {
  display: flex;
  align-items: center;
  gap: 10rpx;
  font-size: 28rpx;
  padding: 0 20rpx;
}

// 返回顶部按钮
.back-to-top-btn {
  position: fixed;
  bottom: 120rpx;
  right: 30rpx;
  width: 90rpx;
  height: 90rpx;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.15);
  z-index: 999;
  transition: all 0.3s ease;
  
  &.back-to-top-btn-hover {
    transform: scale(0.9);
  }
}
</style>
