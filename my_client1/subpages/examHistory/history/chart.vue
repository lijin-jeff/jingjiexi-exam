<template>
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
          <text class="tn-text-bold tn-text-xl tn-color-white">
            成绩分析
          </text>
        </view>
      </tn-nav-bar>
    </view>
    <view class="page-content" :style="{paddingTop: vuex_custom_bar_height + 'px'}">
      <!-- 答题分析概览卡片 -->
      <view class="stat-overview">
        <view class="overview-header">
          <view class="overview-title">
            <text class="tn-icon-bar-chart tn-color-blue tn-margin-right-xs" />
            <text class="title-text">
              答题分析
            </text>
          </view>
          <view class="share-btn" hover-class="share-btn-hover" @click="shareReport">
            <text class="tn-icon-share tn-color-gray">分享</text>
          </view>
        </view>
        <!-- 分析数据网格 -->
        <view class="stat-grid">
          <view class="stat-card">
            <view
              class="stat-icon"
              style="background-color: #e6f7ff;"
            >
              <text class="tn-icon-success-circle tn-color-blue tn-text-xl" />
            </view>
            <view class="stat-info">
              <text class="stat-label">
                正确题数
              </text>
              <text class="stat-value">
                {{ resultData.correct_count }}
              </text>
            </view>
          </view>
          
          <view class="stat-card">
            <view
              class="stat-icon"
              style="background-color: #fff2f0;"
            >
              <text class="tn-icon-close-circle tn-color-red tn-text-xl" />
            </view>
            <view class="stat-info">
              <text class="stat-label">
                错误题数
              </text>
              <text class="stat-value">
                {{ resultData.error_count }}
              </text>
            </view>
          </view>
        </view>
        
        <view class="stat-grid">
          <view class="stat-card">
            <view
              class="stat-icon"
              style="background-color: #f6ffed;"
            >
              <text class="tn-icon-folder tn-color-green tn-text-xl" />
            </view>
            <view class="stat-info">
              <text class="stat-label">
                总题数
              </text>
              <text class="stat-value">
                {{ resultData.total_count }}
              </text>
            </view>
          </view>
          
          <view class="stat-card">
            <view
              class="stat-icon"
              style="background-color: #fef3c7;"
            >
              <text
                class="tn-icon-star tn-text-xl"
                style="color: #fbbf24;"
              />
            </view>
            <view class="stat-info">
              <text class="stat-label">
                正确率
              </text>
              <text class="stat-value">
                {{ resultData.accuracy }}%
              </text>
            </view>
          </view>
        </view>
      </view>
      
      <!-- 成绩进度条-->
      <view class="score-section">
        <view class="section-title">
          <text class="tn-icon-trending-up tn-color-green tn-margin-right-xs" />
          <text>成绩进度</text>
        </view>
        
        <view class="progress-container">
          <view class="progress-label">
            <text class="label-text">
              答题进度
            </text>
            <text class="label-percentage">
              {{ resultData.accuracy }}%
            </text>
          </view>
          <view class="progress-bar-wrapper">
            <view
              class="progress-bar"
              :style="{width: resultData.accuracy + '%'}"
            />
          </view>
        </view>
      </view>
      
      <!-- 题型分布 -->
      <view
        v-if="!isLoading && questionTypeDistribution.length > 0"
        class="distribution-section"
      >
        <view class="section-title">
          <text class="tn-icon-layers tn-color-blue tn-margin-right-xs" />
          <text>题型分布</text>
        </view>
        
        <view class="distribution-list">
          <block
            v-for="(item, index) in questionTypeDistribution"
            :key="index"
          >
            <view class="distribution-item">
              <view class="distribution-header">
                <text class="type-name">
                  {{ item.typeName }}
                </text>
                <text class="type-count">
                  {{ item.count }} 题
                </text>
              </view>
              <view class="distribution-bar-wrapper">
                <view
                  class="distribution-bar"
                  :style="{width: (item.count / resultData.total_count * 100) + '%', backgroundColor: item.color}"
                />
              </view>
            </view>
          </block>
        </view>
      </view>
      
      <!-- 难度分布 -->
      <view
        v-if="!isLoading && difficultyDistribution.length > 0"
        class="distribution-section"
      >
        <view class="section-title">
          <text class="tn-icon-fire tn-color-red tn-margin-right-xs" />
          <text>难度分布</text>
        </view>
        
        <view class="distribution-list">
          <block
            v-for="(item, index) in difficultyDistribution"
            :key="index"
          >
            <view class="distribution-item">
              <view class="distribution-header">
                <text class="type-name">
                  {{ item.levelName }}
                </text>
                <text class="type-count">
                  {{ item.count }} 题
                </text>
              </view>
              <view class="distribution-bar-wrapper">
                <view
                  class="distribution-bar"
                  :style="{width: (item.count / resultData.total_count * 100) + '%', backgroundColor: item.color}"
                />
              </view>
            </view>
          </block>
        </view>
      </view>
      
      <!-- 薄弱知识点 -->
      <view
        v-if="!isLoading && knowledgeAnalysis.length > 0"
        class="knowledge-section"
      >
        <view class="section-title">
          <text class="tn-icon-bulb tn-color-orange tn-margin-right-xs" />
          <text>薄弱知识点</text>
        </view>
        
        <view class="knowledge-list">
          <block
            v-for="(item, index) in knowledgeAnalysis"
            :key="index"
          >
            <view class="knowledge-item">
              <view class="knowledge-header">
                <text class="knowledge-name">
                  {{ item.name }}
                </text>
                <view class="knowledge-stats">
                  <text class="stat-text correct">正确 {{ item.correct }}</text>
                  <text class="stat-text error">错误 {{ item.error }}</text>
                  <text 
                    class="stat-text accuracy" 
                    :class="[item.accuracy >= 80 ? 'high' : (item.accuracy >= 60 ? 'medium' : 'low')]"
                  >
                    {{ item.accuracy }}%
                  </text>
                </view>
              </view>
              <view class="knowledge-bar-wrapper">
                <view
                  class="knowledge-bar"
                  :style="{width: item.accuracy + '%', backgroundColor: getAccuracyColor(item.accuracy)}"
                />
              </view>
            </view>
          </block>
        </view>
      </view>
      
      <!-- 答题速度分析 -->
      <view
        v-if="!isLoading && speedAnalysis.avgTimePerQuestion > 0"
        class="speed-section"
      >
        <view class="section-title">
          <text class="tn-icon-stopwatch tn-color-purple tn-margin-right-xs" />
          <text>答题速度</text>
        </view>
        
        <view class="speed-grid">
          <view class="speed-card">
            <text class="speed-label">平均用时</text>
            <text class="speed-value">{{ speedAnalysis.avgTimePerQuestion }}秒</text>
          </view>
          <view class="speed-card">
            <text class="speed-label">最快</text>
            <text class="speed-value">{{ speedAnalysis.fastestTime }}秒</text>
          </view>
          <view class="speed-card">
            <text class="speed-label">最慢</text>
            <text class="speed-value">{{ speedAnalysis.slowestTime }}秒</text>
          </view>
        </view>
      </view>
      
      <!-- 答题时间分布 -->
      <view class="time-section">
        <view class="section-title">
          <text class="tn-icon-time tn-color-orange tn-margin-right-xs" />
          <text>答题信息</text>
        </view>
        
        <view class="info-list">
          <view class="info-item">
            <view class="info-label">
              答题时间
            </view>
            <view class="info-value">
              {{ resultData.submitTime || '-' }}
            </view>
          </view>
          <view class="info-item">
            <view class="info-label">
              总耗时
            </view>
            <view class="info-value">
              {{ formatPracticeTime(resultData.timeSpent) }}
            </view>
          </view>
          <view class="info-item">
            <view class="info-label">
              平均答题时间
            </view>
            <view class="info-value">
              {{ resultData.avgTime || '-' }}
            </view>
          </view>
        </view>
      </view>
      
      <!-- 加载状态 -->
      <view v-if="isLoading" class="loading-container">
        <text class="tn-icon-loading tn-text-xl" />
        <text class="tn-margin-top-sm">加载中...</text>
      </view>
      
      <!-- 空状态 -->
      <view v-if="!isLoading && questionTypeDistribution.length === 0" class="empty-container">
        <tn-empty mode="list" text="暂无统计数据" />
      </view>
      
      <!-- 建议提示 -->
      <view class="advice-section">
        <view class="advice-title">
          <text class="tn-icon-alert-circle tn-color-orange tn-margin-right-xs" />
          <text>学习建议</text>
        </view>
        
        <view class="advice-content">
          <block v-if="resultData.accuracy >= 80">
            <text class="advice-text">
               恭喜你！答题成绩优异，继续保持良好的学习状态！
            </text>
          </block>
          <block v-else-if="resultData.accuracy >= 60">
            <text class="advice-text">
               再加把劲！建议复习错题，巩固知识薄弱环节！
            </text>
          </block>
          <block v-else>
            <text class="advice-text">
               加油！建议重新学习相关知识点，然后再练习一遍！
            </text>
          </block>
        </view>
      </view>
      
      <!-- 底部操作按钮 -->
      <view class="button-group">
        <tn-button 
          :background-color="mainColor" 
          width="100%" 
          font-color="tn-color-white"
          height="80rpx"
          font-size="28"
          @click="goBackToAnalysis"
        >
          返回答题详情
        </tn-button>
        
        <view style="height: 20rpx;" />
        
        <tn-button 
          background-color="tn-bg-gray--light"
          width="100%" 
          font-color="tn-color-dark"
          height="80rpx"
          font-size="28"
          @click="goBackToHistory"
        >
          返回答题历史
        </tn-button>
      </view>

    </view>

  </view>
</template>

<script>
import template_page_mixin from '@/libs/mixin/template_page_mixin.js'
import ExamDataAdapter from '@/util/examDataAdapter.js'

// 题型颜色常量
const TYPE_COLORS = ['#0E7DFF', '#52C41A', '#FF4D4F', '#FAAD14', '#13C2C2', '#EB2F96']

// 难度颜色映射
const DIFFICULTY_COLORS = {
  1: '#52C41A',  // 简单-绿色
  2: '#FAAD14',  // 中等-橙色
  3: '#FF4D4F'   // 困难-红色
}

export default {
  name: 'ExamChart',
  components: {},
  mixins: [template_page_mixin],
  data() {
    return {
      mainColor: getApp().globalData.mainColor,
      uid: '',
      id: 0,
      examTitle: '练习报告',
      isLoading: false,
      practiceTime: '0:00:00', // 练习时长
      resultData: {
        correct_count: 0,
        error_count: 0,
        total_count: 0,
        accuracy: 0,
        submitTime: '',
        timeSpent: 0,
        avgTime: ''
      },
      questionTypeDistribution: [],
      difficultyDistribution: [],
      knowledgeAnalysis: [],
      speedAnalysis: {
        avgTimePerQuestion: 0,
        fastestTime: 0,
        slowestTime: 0
      }
    }
  },
  onLoad(option) {
    this.uid = option.uid || ''
    this.id = option.id || 0
    if (this.uid || this.id) {
      this.fetchAnalysisData()
    }
  },
  
  // 分享给朋友
  onShareAppMessage(res) {
    return {
      title: `我的${this.examTitle}成绩分析 - 正确率${this.resultData.accuracy}%`,
      path: `/pages/index/index`
    }
  },
  
  // 分享到朋友圈
  onShareTimeline() {
    return {
      title: `我的${this.examTitle}成绩分析 - 正确率${this.resultData.accuracy}%`,
      query: ``
    }
  },
  methods: {
    goBack() {
      uni.navigateBack()
    },
  
    goBackToAnalysis() {
      this.$func.navigatorTo(`/subpages/examHistory/history/analysis?uid=${this.uid}&id=${this.id}`)
    },
    
    goBackToHistory() {
      uni.navigateBack({delta: 2})
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
            title: '分享成绩分析',
            content: '请点击右上角「...」按钮，选择分享给朋友或分享到朋友圈',
            showCancel: false,
            confirmText: '我知道了'
          })
        },
        fail: (err) => {
          console.error('开启分享菜单失败:', err)
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
    
    fetchAnalysisData() {
      this.isLoading = true
      uni.showLoading({
        title: '加载中...',
        icon: 'none'
      })
      
      // 获取模拟考试历史详情
      this.$api.apiExaminationHistoryDetail({
        uid: this.uid,
        id: this.id
      }).then(res => {
        if (res.code === 1 && res.data) {
          const mockRecord = res.data
          
          // 设置考试标题
          this.examTitle = mockRecord.title || '练习报告'
          
          // 使用后端返回的统计字段
          this.resultData.correct_count = mockRecord.correct_count || 0
          this.resultData.error_count = mockRecord.error_count || 0
          this.resultData.total_count = mockRecord.options_count || (mockRecord.correct_count || 0) + (mockRecord.error_count || 0)
          this.resultData.accuracy = this.resultData.total_count > 0 ? Math.round((this.resultData.correct_count / this.resultData.total_count) * 100) : 0
          
          // 格式化提交时间 - 使用数据适配器
          const submitTimeValue = mockRecord.submit_time || mockRecord.create_time
          this.resultData.submitTime = this.formatSubmitTime(submitTimeValue)
          
          // 格式化练习时长 - 处理多种可能的字段名
          const durationSeconds = mockRecord.practice_duration || mockRecord.duration || mockRecord.time_used || 0
          this.resultData.timeSpent = durationSeconds
          
          // 计算平均答题时间
          if (durationSeconds > 0 && this.resultData.total_count > 0) {
            this.resultData.avgTime = Math.round(durationSeconds / this.resultData.total_count) + '秒'
          } else {
            this.resultData.avgTime = '-'
          }
          
          // 从 options 字段解析真实题型分布
          if (mockRecord.options) {
            this.parseOptionsData(mockRecord.options)
          } else if (mockRecord.question_uid) {
            // 降级方案：通过题库uid获取题型
            this.fetchQuestionTypeDistribution(mockRecord.question_uid)
          } else {
            this.isLoading = false
            uni.hideLoading()
          }
        } else {
          this.$func.showToast(res.msg || '获取数据失败')
          this.isLoading = false
          uni.hideLoading()
        }
      }).catch(error => {
        console.error('获取数据失败:', error)
        this.$func.showToast('网络错误，请重试')
        this.isLoading = false
        uni.hideLoading()
      })
    },
    
    // 从 options 字段解析题型分布、难度分布等统计信息
    parseOptionsData(optionsData) {
      try {
        // 使用数据适配器递归解析 JSON
        let optionsList = ExamDataAdapter.recursiveParseJson(optionsData)
        
        if (!Array.isArray(optionsList) || optionsList.length === 0) {
          console.warn('[chart.vue] options 数据格式不正确:', optionsData)
          this.isLoading = false
          uni.hideLoading()
          return
        }
        
        // 提取所有题目uid
        const questionUids = optionsList.map(item => item.uid).filter(uid => uid).join(',')
        
        if (!questionUids) {
          console.warn('[chart.vue] 无法提取题目uid')
          this.isLoading = false
          uni.hideLoading()
          return
        }
        
        // 获取题目详情用于统计分析
        this.fetchQuestionDetailsForAnalysis(questionUids, optionsList)
      } catch (error) {
        console.error('[chart.vue] 解析options失败:', error)
        this.isLoading = false
        uni.hideLoading()
      }
    },
    
    // 获取题目详情用于统计分析
    fetchQuestionDetailsForAnalysis(questionUids, userAnswers) {
      this.$api.apiQuestionOrderList({
        analysis_quid: questionUids,
        page_no: 1,
        page_size: 999
      }).then(res => {
        if (res.code === 1 && res.data) {
          const questionList = res.data.list || res.data || []
          if (Array.isArray(questionList) && questionList.length > 0) {
            // 使用数据适配器处理题目数据
            const processedQuestions = ExamDataAdapter.processQuestionList(questionList, userAnswers)
            
            // 统计题型分布
            this.analyzeTypeDistribution(processedQuestions)
            
            // 统计难度分布
            this.analyzeDifficultyDistribution(processedQuestions)
            
            // 统计知识点分析
            this.analyzeKnowledge(processedQuestions)
            
            // 统计答题速度
            this.analyzeSpeed(processedQuestions)
          }
        }
        this.isLoading = false
        uni.hideLoading()
      }).catch(error => {
        console.error('[chart.vue] 获取题目详情失败:', error)
        this.isLoading = false
        uni.hideLoading()
      })
    },
    
    fetchQuestionTypeDistribution(libraryUid) {
      this.$api.apiQuestionTypeList({
        uid: libraryUid
      }).then(res => {
        if (res.code === 1 && res.data) {
          this.questionTypeDistribution = res.data.map((item, index) => ({
            typeName: item.type_name || item.title,
            count: item.option_count || 0,
            color: TYPE_COLORS[index % TYPE_COLORS.length]
          }))
        }
        this.isLoading = false
        uni.hideLoading()
      }).catch(error => {
        console.error('获取题型分布失败:', error)
        this.isLoading = false
        uni.hideLoading()
      })
    },
    
    // 统计题型分布
    analyzeTypeDistribution(questions) {
      const typeMap = {}
      questions.forEach(q => {
        const typeName = q.exam_type_name || '未知题型'
        if (!typeMap[typeName]) {
          typeMap[typeName] = 0
        }
        typeMap[typeName]++
      })
      
      this.questionTypeDistribution = Object.entries(typeMap).map(([typeName, count], index) => ({
        typeName,
        count,
        color: TYPE_COLORS[index % TYPE_COLORS.length]
      }))
    },
    
    // 统计难度分布
    analyzeDifficultyDistribution(questions) {
      const difficultyMap = { 1: 0, 2: 0, 3: 0 }
      const difficultyNames = { 1: '简单', 2: '中等', 3: '困难' }
      
      questions.forEach(q => {
        const exam_level = q.exam_level || 1
        if (difficultyMap[exam_level] !== undefined) {
          difficultyMap[exam_level]++
        }
      })
      
      this.difficultyDistribution = Object.entries(difficultyMap)
        .filter(([_, count]) => count > 0)
        .map(([exam_level, count]) => ({
          exam_level: parseInt(exam_level),
          levelName: difficultyNames[exam_level],
          count,
          color: DIFFICULTY_COLORS[exam_level]
        }))
    },
    
    // 统计知识点分析
    analyzeKnowledge(questions) {
      const knowledgeMap = {}
      
      questions.forEach(q => {
        // 处理知识点数组
        const knowledgeList = Array.isArray(q.knowledge) ? q.knowledge : []
        knowledgeList.forEach(k => {
          const knowledgeName = k.name || k.title || '未分类'
          if (!knowledgeMap[knowledgeName]) {
            knowledgeMap[knowledgeName] = {
              name: knowledgeName,
              total: 0,
              correct: 0,
              error: 0
            }
          }
          knowledgeMap[knowledgeName].total++
          if (q.is_correct) {
            knowledgeMap[knowledgeName].correct++
          } else if (q.user_answer && q.user_answer !== '-') {
            knowledgeMap[knowledgeName].error++
          }
        })
      })
      
      this.knowledgeAnalysis = Object.values(knowledgeMap)
        .map(item => ({
          ...item,
          accuracy: item.total > 0 ? Math.round((item.correct / item.total) * 100) : 0
        }))
        .sort((a, b) => a.accuracy - b.accuracy) // 按正确率升序，薄弱点在前
        .slice(0, 5) // 只取前5个薄弱知识点
    },
    
    // 统计答题速度
    analyzeSpeed(questions) {
      const times = []
      questions.forEach(q => {
        if (q.time_spent && q.time_spent > 0) {
          times.push(q.time_spent)
        }
      })
      
      if (times.length > 0) {
        const sum = times.reduce((a, b) => a + b, 0)
        this.speedAnalysis.avgTimePerQuestion = Math.round(sum / times.length)
        this.speedAnalysis.fastestTime = Math.min(...times)
        this.speedAnalysis.slowestTime = Math.max(...times)
      }
    },
    
    // 格式化提交时间
    formatSubmitTime(timestamp) {
      if (!timestamp) return '-'
      
      try {
        // 使用数据适配器统一时间戳
        const normalizedTimestamp = ExamDataAdapter.normalizeTimestamp(timestamp)
        const date = new Date(normalizedTimestamp)
        
        if (isNaN(date.getTime())) {
          return '-'
        }
        
        const year = date.getFullYear()
        const month = String(date.getMonth() + 1).padStart(2, '0')
        const day = String(date.getDate()).padStart(2, '0')
        const hour = String(date.getHours()).padStart(2, '0')
        const minute = String(date.getMinutes()).padStart(2, '0')
        
        return `${year}.${month}.${day} ${hour}:${minute}`
      } catch (error) {
        return '-'
      }
    },
    
    // 格式化练习时长
    formatPracticeTime(seconds) {      
      // 处理 undefined 和 null
      if (seconds === undefined || seconds === null) {
        return '0分钟'
      }
      const numSeconds = parseFloat(seconds)      
      // 处理无效数字
      if (isNaN(numSeconds) || numSeconds <= 0) {
        return '0分钟'
      }
      
      const hours = Math.floor(numSeconds / 3600)
      const minutes = Math.floor((numSeconds % 3600) / 60)
      const secs = Math.floor(numSeconds % 60)
      
      let result = ''
      if (hours > 0) {
        result = `${hours}小时${minutes}分钟`
      } else if (minutes > 0) {
        result = `${minutes}分钟`
      } else {
        result = `${secs}秒`
      }
        return result
    },
    
    // 获取正确率颜色
    getAccuracyColor(accuracy) {
      if (accuracy >= 80) return '#52c41a'
      if (accuracy >= 60) return '#faad14'
      return '#ff4d4f'
    },
    
    // 获取正确率样式类
    getAccuracyClass(accuracy) {
      if (accuracy >= 80) return 'high'
      if (accuracy >= 60) return 'medium'
      return 'low'
    }
  }
}
</script>

<style scoped lang="scss">
	@import "@/scss/custom_nav_bar.scss";
.stat-overview {
  background: #fff;
  padding: 30rpx 20rpx;
  margin: 20rpx;
  border-radius: 16rpx;
  box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.05);
  
  .overview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30rpx;
  }
  
  .overview-title {
    display: flex;
    align-items: center;
    font-size: 32rpx;
    font-weight: bold;
    color: #333;
    
    .title-text {
      margin-left: 10rpx;
    }
  }
  
  .share-btn {
    padding: 10rpx 20rpx;
    background: #f5f7fa;
    border-radius: 20rpx;
    font-size: 26rpx;
    color: #666;
    display: flex;
    align-items: center;
    transition: all 0.3s ease;
    
    &.share-btn-hover {
      background: #e8ecf1;
      transform: scale(0.95);
    }
  }
  
  .stat-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20rpx;
    margin-bottom: 20rpx;
    
    &:last-child {
      margin-bottom: 0;
    }
    
    .stat-card {
      display: flex;
      align-items: center;
      padding: 20rpx;
      background-color: #f5f5f5;
      border-radius: 12rpx;
      
      .stat-icon {
        width: 70rpx;
        height: 70rpx;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15rpx;
        flex-shrink: 0;
      }
      
      .stat-info {
        display: flex;
        flex-direction: column;
        
        .stat-label {
          font-size: 24rpx;
          color: #999;
          margin-bottom: 8rpx;
        }
        
        .stat-value {
          font-size: 36rpx;
          font-weight: bold;
          color: #333;
        }
      }
    }
  }
}

.score-section {
  background: #fff;
  padding: 30rpx 20rpx;
  margin: 20rpx;
  border-radius: 16rpx;
  box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.05);
  
  .section-title {
    display: flex;
    align-items: center;
    font-size: 28rpx;
    font-weight: bold;
    color: #333;
    margin-bottom: 25rpx;
    
    text {
      margin-left: 8rpx;
    }
  }
  
  .progress-container {
    .progress-label {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15rpx;
      
      .label-text {
        font-size: 26rpx;
        color: #666;
      }
      
      .label-percentage {
        font-size: 28rpx;
        font-weight: bold;
        color: #0E7DFF;
      }
    }
    
    .progress-bar-wrapper {
      width: 100%;
      height: 12rpx;
      background-color: #e6e6e6;
      border-radius: 6rpx;
      overflow: hidden;
      
      .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #0E7DFF 0%, #5B9EFE 100%);
        border-radius: 6rpx;
        transition: width 0.3s ease;
      }
    }
  }
}

.distribution-section {
  background: #fff;
  padding: 30rpx 20rpx;
  margin: 20rpx;
  border-radius: 16rpx;
  box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.05);
  
  .section-title {
    display: flex;
    align-items: center;
    font-size: 28rpx;
    font-weight: bold;
    color: #333;
    margin-bottom: 25rpx;
    
    text {
      margin-left: 8rpx;
    }
  }
  
  .distribution-list {
    .distribution-item {
      margin-bottom: 25rpx;
      
      &:last-child {
        margin-bottom: 0;
      }
      
      .distribution-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10rpx;
        
        .type-name {
          font-size: 26rpx;
          color: #333;
          font-weight: bold;
        }
        
        .type-count {
          font-size: 24rpx;
          color: #999;
        }
      }
      
      .distribution-bar-wrapper {
        width: 100%;
        height: 20rpx;
        background-color: #f0f0f0;
        border-radius: 10rpx;
        overflow: hidden;
        
        .distribution-bar {
          height: 100%;
          border-radius: 10rpx;
          transition: width 0.3s ease;
        }
      }
    }
  }
}

.time-section {
  background: #fff;
  padding: 30rpx 20rpx;
  margin: 20rpx;
  border-radius: 16rpx;
  box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.05);
  
  .section-title {
    display: flex;
    align-items: center;
    font-size: 28rpx;
    font-weight: bold;
    color: #333;
    margin-bottom: 25rpx;
    
    text {
      margin-left: 8rpx;
    }
  }
  
  .info-list {
    .info-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15rpx 0;
      border-bottom: 1rpx solid #f0f0f0;
      
      &:last-child {
        border-bottom: none;
      }
      
      .info-label {
        font-size: 26rpx;
        color: #666;
      }
      
      .info-value {
        font-size: 26rpx;
        font-weight: bold;
        color: #0E7DFF;
      }
    }
  }
}

.advice-section {
  background: linear-gradient(135deg, #fff7e6 0%, #fff9f0 100%);
  padding: 30rpx 20rpx;
  margin: 20rpx;
  border-radius: 16rpx;
  border-left: 4rpx solid #faad14;
  box-shadow: 0 4rpx 20rpx rgba(250, 173, 20, 0.1);
  
  .advice-title {
    display: flex;
    align-items: center;
    font-size: 28rpx;
    font-weight: bold;
    color: #333;
    margin-bottom: 15rpx;
    
    text {
      margin-left: 8rpx;
    }
  }
  
  .advice-content {
    .advice-text {
      font-size: 26rpx;
      color: #666;
      line-height: 40rpx;
      word-break: break-word;
    }
  }
}

.button-group {
  padding: 20rpx 30rpx;
  background: #fff;
  margin: 20rpx;
  border-radius: 16rpx;
  box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.05);
  margin-bottom: 50rpx;
}

.nav-right-btn {
  display: flex;
  align-items: center;
  gap: 10rpx;
  font-size: 28rpx;
  padding: 0 20rpx;
}

// 知识点分析区域
.knowledge-section {
  background: #fff;
  padding: 30rpx 20rpx;
  margin: 20rpx;
  border-radius: 16rpx;
  box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.05);
  
  .section-title {
    display: flex;
    align-items: center;
    font-size: 28rpx;
    font-weight: bold;
    color: #333;
    margin-bottom: 25rpx;
    
    text {
      margin-left: 8rpx;
    }
  }
  
  .knowledge-list {
    .knowledge-item {
      margin-bottom: 25rpx;
      
      &:last-child {
        margin-bottom: 0;
      }
      
      .knowledge-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10rpx;
        
        .knowledge-name {
          font-size: 26rpx;
          color: #333;
          font-weight: 500;
          flex: 1;
        }
        
        .knowledge-stats {
          display: flex;
          gap: 15rpx;
          align-items: center;
          
          .stat-text {
            font-size: 22rpx;
            
            &.correct {
              color: #52c41a;
            }
            
            &.error {
              color: #ff4d4f;
            }
            
            &.accuracy {
              font-weight: bold;
              
              &.high {
                color: #52c41a;
              }
              
              &.medium {
                color: #faad14;
              }
              
              &.low {
                color: #ff4d4f;
              }
            }
          }
        }
      }
      
      .knowledge-bar-wrapper {
        width: 100%;
        height: 20rpx;
        background-color: #f0f0f0;
        border-radius: 10rpx;
        overflow: hidden;
        
        .knowledge-bar {
          height: 100%;
          border-radius: 10rpx;
          transition: width 0.3s ease;
        }
      }
    }
  }
}

// 答题速度区域
.speed-section {
  background: #fff;
  padding: 30rpx 20rpx;
  margin: 20rpx;
  border-radius: 16rpx;
  box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.05);
  
  .section-title {
    display: flex;
    align-items: center;
    font-size: 28rpx;
    font-weight: bold;
    color: #333;
    margin-bottom: 25rpx;
    
    text {
      margin-left: 8rpx;
    }
  }
  
  .speed-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20rpx;
    
    .speed-card {
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 25rpx 15rpx;
      background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
      border-radius: 12rpx;
      
      .speed-label {
        font-size: 22rpx;
        color: #999;
        margin-bottom: 10rpx;
      }
      
      .speed-value {
        font-size: 32rpx;
        font-weight: bold;
        color: #0E7DFF;
      }
    }
  }
}

// 加载和空状态
.loading-container,
.empty-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 100rpx 20rpx;
  color: #999;
  
  text {
    display: block;
  }
}

.loading-container {
  text {
    animation: spin 1s linear infinite;
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
</style>


