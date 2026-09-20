/**
 * commonQuestion.vue 优化应用示例
 * 展示如何应用各种优化工具解决嵌套循环和复杂计算问题
 */

import dataProcessor from './dataProcessor.js'
import swipeHandler from './swipeHandler.js'

// 优化后的组件混入
export const OptimizedMixin = {
  data() {
    return {
      // 使用优化工具替代原有复杂逻辑
      optimizedTools: {
        dataProcessor,
        swipeHandler
      },
      
      // 简化的状态管理
      optimizedState: {
        processedQuestions: [],
        lastSwipeResult: null
      }
    }
  },

  computed: {
    // 优化的当前题目获取
    optimizedCurrentQuestion() {
      if (!this.swiperList || this.swiperList.length === 0) return null
      const safeIndex = Math.max(0, Math.min(this.swiperCurrentIndex, this.swiperList.length - 1))
      return this.swiperList[safeIndex]
    }
  },

  methods: {
    /**
     * 优化的题目列表加载
     */
    async optimizedFetchQuestionList() {
      try {
        uni.showLoading({ title: '加载中...' })
        
        const apiMethod = this.getApiMethod()
        const params = { ...this.questionParams }
        const res = await this.$api[apiMethod](params)
        
        if (res?.code === 1 && res.data) {
          // 使用优化的数据处理器
          this.optimizedState.processedQuestions = 
            await this.optimizedTools.dataProcessor.processQuestionList(
              res.data.list || res.data,
              5 // 批处理大小
            )
          
          this.swiperList = this.optimizedState.processedQuestions
          this.calculateAnsweredCount()
        } else {
          this.$func.showToast(res?.msg || '获取题目失败')
        }
      } catch (error) {
        console.error('加载题目失败:', error)
        this.$func.showToast('加载失败，请重试')
      } finally {
        uni.hideLoading()
      }
    },

    /**
     * 优化的滑动处理
     */
    async optimizedHandleSwiperChange(e) {
      const currentState = {
        swiperCurrentIndex: this.swiperCurrentIndex,
        swiperList: this.swiperList
      }
      
      if (this.optimizedTools.swipeHandler && typeof this.optimizedTools.swipeHandler.handleSwipe === 'function') {
        const result = await this.optimizedTools.swipeHandler.handleSwipe(e, currentState)
        
        if (result.handled) {
          this.swiperCurrentIndex = result.targetIndex
          this.optimizedState.lastSwipeResult = result
          
          // 触发相关事件
          this.$emit('swiper-change', result)
        }
      }
    },

    /**
     * 优化的答案处理
     */
    optimizedHandleAnswerChange(data) {
      const { questionIndex, answer } = data
      
      if (this.swiperList[questionIndex]) {
        // 使用优化的数据处理器标准化答案
        const processedAnswer = this.optimizedTools.dataProcessor.optimizeUserAnswerParsing(
          answer,
          this.swiperList[questionIndex].exam_type
        )
        
        this.swiperList[questionIndex].user_answer = processedAnswer
        
        // 更新答题统计
        this.calculateAnsweredCount()
      }
    },

    /**
     * 优化的选项处理
     */
    optimizedInitQuestionList(list) {
      // 使用批处理优化
      return list.map(item => 
        this.optimizedTools.dataProcessor.processSingleQuestion(item)
      )
    },

    /**
     * 性能监控方法
     */
    monitorPerformance() {
      return {
        dataProcessor: this.optimizedTools.dataProcessor && typeof this.optimizedTools.dataProcessor.getStats === 'function' ? this.optimizedTools.dataProcessor.getStats() : {},
        swipeHandler: this.optimizedTools.swipeHandler && typeof this.optimizedTools.swipeHandler.getStats === 'function' ? this.optimizedTools.swipeHandler.getStats() : {},
        cacheSize: this.optimizedState.processedQuestions.length
      }
    },

    /**
     * 清理优化资源
     */
    cleanupOptimizations() {
      if (this.optimizedTools.dataProcessor && typeof this.optimizedTools.dataProcessor.clearAllCache === 'function') {
        this.optimizedTools.dataProcessor.clearAllCache()
      }
      if (this.optimizedTools.swipeHandler && typeof this.optimizedTools.swipeHandler.reset === 'function') {
        this.optimizedTools.swipeHandler.reset()
      }
      this.optimizedState.processedQuestions = []
    }
  },

  // 生命周期优化
  beforeDestroy() {
    this.cleanupOptimizations()
  }
}

// 使用示例
/*
在原组件中这样使用：

import { OptimizedMixin } from './optimizedMixin.js'

export default {
  mixins: [OptimizedMixin],
  
  methods: {
    // 替换原有的复杂方法
    async fetchQuestionList() {
      await this.optimizedFetchQuestionList()
    },
    
    handleSwiperChange(e) {
      this.optimizedHandleSwiperChange(e)
    },
    
    // 在模板中使用优化的计算属性
    // :virtual-list="optimizedVirtualList.items"
    // :current-index="optimizedVirtualList.virtualIndex"
  }
}
*/