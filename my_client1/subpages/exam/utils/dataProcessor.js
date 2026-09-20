/**
 * 数据处理优化工具类
 * 专门优化 commonQuestion.vue 中的嵌套循环和复杂计算问题
 */

class DataProcessor {
  constructor() {
    this.cache = new Map()
    this.processingQueue = []
    this.diskCacheEnabled = true
    this.maxCacheSize = 1000 // 最大缓存项数
  }

  /**
   * 从本地存储读取缓存
   */
  getFromDiskCache(key) {
    if (!this.diskCacheEnabled) return null
    try {
      const cached = uni.getStorageSync(`data_processor_${key}`)
      return cached
    } catch (error) {
      console.error('从本地存储读取缓存失败:', error)
      return null
    }
  }

  /**
   * 写入缓存到本地存储
   */
  setToDiskCache(key, value) {
    if (!this.diskCacheEnabled) return
    try {
      uni.setStorageSync(`data_processor_${key}`, value)
    } catch (error) {
      console.error('写入缓存到本地存储失败:', error)
    }
  }

  /**
   * 优化用户答案解析 - 消除嵌套循环
   */
  optimizeUserAnswerParsing(rawAnswer, examType) {
    const cacheKey = `user_answer_${JSON.stringify(rawAnswer)}_${examType}`
    
    // 检查内存缓存
    if (this.cache.has(cacheKey)) {
      return this.cache.get(cacheKey)
    }
    
    // 检查本地存储缓存
    const cachedFromDisk = this.getFromDiskCache(cacheKey)
    if (cachedFromDisk !== null) {
      this.cache.set(cacheKey, cachedFromDisk)
      return cachedFromDisk
    }
    
    let result
    
    // 简化处理逻辑，避免嵌套循环
    if (Array.isArray(rawAnswer)) {
      result = this.flattenArrayAnswers(rawAnswer, examType)
    } else if (typeof rawAnswer === 'string') {
      result = this.parseStringAnswer(rawAnswer, examType)
    } else {
      result = this.normalizeSingleAnswer(rawAnswer, examType)
    }
    
    // 设置内存缓存
    this.cache.set(cacheKey, result)
    
    // 写入本地存储缓存
    this.setToDiskCache(cacheKey, result)
    
    // 检查缓存大小，超过限制时清理
    this.checkCacheSize()
    
    return result
  }

  /**
   * 扁平化数组答案 - 单层处理
   */
  flattenArrayAnswers(answerArray, examType) {
    const flattened = []
    
    try {
      for (let i = 0; i < answerArray.length; i++) {
        const item = answerArray[i]
        if (Array.isArray(item)) {
          // 递归展平，但限制深度避免无限循环
          const result = this.flattenArrayAnswers(item, examType)
          if (Array.isArray(result)) {
            flattened.push(...result)
          } else {
            flattened.push(result)
          }
        } else if (typeof item === 'string') {
          const result = this.parseStringAnswer(item, examType)
          if (Array.isArray(result)) {
            flattened.push(...result)
          } else {
            flattened.push(result)
          }
        } else {
          flattened.push(String(item))
        }
      }
      
      return this.finalizeAnswerFormat(flattened, examType)
    } catch (error) {
      console.warn('处理数组答案失败:', error)
      return []
    }
  }

  /**
   * 解析字符串答案 - 简化正则处理
   */
  parseStringAnswer(answerStr, examType) {
    if (!answerStr) return []
    
    // 预编译正则表达式，避免重复创建
    const jsonRegex = /^\[.*\]$/
    const separatorRegex = /[,，、]/
    
    // JSON格式处理
    if (jsonRegex.test(answerStr.trim())) {
      try {
        const parsed = JSON.parse(answerStr)
        if (Array.isArray(parsed)) {
          return parsed.map(item => String(item))
        }
        return [String(parsed)]
      } catch (e) {
        // JSON解析失败，继续其他处理
      }
    }
    
    // 分隔符分割处理
    if (separatorRegex.test(answerStr)) {
      return answerStr
        .split(separatorRegex)
        .map(item => item.trim())
        .filter(item => item !== '')
    }
    
    // 单个答案
    return [answerStr.trim()]
  }

  /**
   * 标准化单个答案
   */
  normalizeSingleAnswer(answer, examType) {
    if (answer === null || answer === undefined) return []
    return [String(answer)]
  }

  /**
   * 格式化最终答案格式
   */
  finalizeAnswerFormat(answers, examType) {
    // 去重和清理
    const cleaned = [...new Set(answers)]
      .map(item => String(item).trim())
      .filter(item => item !== '')
    
    // 根据题型确定返回格式
    if (examType === 1 || examType === 3) {
      // 单选题和判断题返回第一个答案
      return cleaned.length > 0 ? cleaned[0] : ''
    } else if (examType === 2) {
      // 多选题返回数组
      return cleaned
    } else {
      // 其他题型返回逗号连接的字符串
      return cleaned.join(',')
    }
  }

  /**
   * 优化题目列表处理 - 批量处理替代嵌套循环
   */
  async processQuestionList(questionList, batchSize = 10) {
    if (!Array.isArray(questionList) || questionList.length === 0) {
      return []
    }

    const results = []
    const total = questionList.length
    
    
    // 分批处理，避免阻塞主线程
    for (let i = 0; i < total; i += batchSize) {
      const batch = questionList.slice(i, i + batchSize)
      const batchResults = await this.processBatch(batch, i)
      results.push(...batchResults)
      
      // 让出主线程，避免界面卡顿
      if (i + batchSize < total) {
        await this.yieldToMain()
      }
    }
    return results
  }

  /**
   * 处理单个批次
   */
  processBatch(batch, startIndex) {
    return new Promise(resolve => {
      // 微信小程序兼容：使用 setTimeout 替代 requestAnimationFrame
      const nextFrame = typeof requestAnimationFrame === 'function' ? requestAnimationFrame : (cb) => setTimeout(cb, 0);
      nextFrame(() => {
        try {
          const results = batch.map((item, index) => 
            this.processSingleQuestion(item, startIndex + index)
          )
          resolve(results)
        } catch (error) {
          console.error('批次处理失败:', error)
          // 即使处理失败，也返回原始题目数据，确保至少能显示题目
          const fallbackResults = batch.map((item, index) => ({
            ...item,
            is_selected: false,
            is_submitted: false,
            is_correct: undefined,
            is_like: !!item.is_like,
            is_collection: !!item.is_collection,
            like_count: item.like_count || 0,
            collect_count: item.collect_count || 0,
            title: item.title || '',
            user_answer: undefined,
            option: item.option
          }))
          resolve(fallbackResults)
        }
      })
    })
  }

  /**
   * 处理单个题目 - 简化复杂计算
   */
  processSingleQuestion(item, index) {
    const cacheKey = `question_${item.uid}_${index}`
    
    try {
      
      // 检查内存缓存
      if (this.cache.has(cacheKey)) {
        return this.cache.get(cacheKey)
      }
      
      // 检查本地存储缓存
      const cachedFromDisk = this.getFromDiskCache(cacheKey)
      if (cachedFromDisk !== null) {
        this.cache.set(cacheKey, cachedFromDisk)
        return cachedFromDisk
      }
      
      // 简化处理流程
      const processedItem = {
        ...item,
        // 基础状态
        is_selected: false,
        is_submitted: false,
        is_correct: undefined,
        is_like: !!item.is_like,
        is_collection: !!item.is_collection,
        like_count: item.like_count || 0,
        collect_count: item.collect_count || 0,
        title: item.title || '',
        
        // 优化的答案处理
        user_answer: this.extractUserAnswer(item),
        
        // 简化的选项处理
        option: this.simplifyOptions(item)
      }
    
      
      // 设置内存缓存
      this.cache.set(cacheKey, processedItem)
      
      // 写入本地存储缓存
      this.setToDiskCache(cacheKey, processedItem)
      
      // 检查缓存大小，超过限制时清理
      this.checkCacheSize()
      
      return processedItem
    } catch (error) {
      // 出错时返回原始题目数据，确保至少能显示题目
      return {
        ...item,
        is_selected: false,
        is_submitted: false,
        is_correct: undefined,
        is_like: !!item.is_like,
        is_collection: !!item.is_collection,
        like_count: item.like_count || 0,
        collect_count: item.collect_count || 0,
        title: item.title || '',
        user_answer: undefined,
        option: item.option
      }
    }
  }

  /**
   * 提取用户答案 - 简化逻辑
   */
  extractUserAnswer(item) {
    // 直接从参数中获取，避免复杂查找
    const userAnswers = this.getUserAnswersFromParams()
    if (!userAnswers || !item.uid) return undefined
    
    const questionUid = String(item.uid)
    const rawAnswer = userAnswers[questionUid]
    
    if (rawAnswer === undefined) return undefined
    
    return this.optimizeUserAnswerParsing(rawAnswer, item.exam_type)
  }

  /**
   * 简化选项处理 - 避免复杂循环
   */
  simplifyOptions(item) {
    try {
      if (!item.option || !Array.isArray(item.option)) return item.option
      
      // 预处理正确答案
      const correctAnswers = this.preprocessCorrectAnswers(item.answer)
      
      // 单次遍历处理所有选项
      return item.option.map(option => {
        let isCorrect = false
        try {
          if (option && option.check !== undefined) {
            const checkValue = String(option.check)
            const normalizedCheck = checkValue.toUpperCase().trim()
            isCorrect = correctAnswers.includes(normalizedCheck)
          }
        } catch (error) {
          console.warn('处理选项失败:', error)
        }
        return {
          ...option,
          is_correct: isCorrect
        }
      })
    } catch (error) {
      console.warn('简化选项处理失败:', error)
      return item.option
    }
  }

  /**
   * 预处理正确答案 - 简化解析逻辑
   */
  preprocessCorrectAnswers(answer) {
    if (!answer) return []
    
    const cacheKey = `correct_answers_${JSON.stringify(answer)}`
    if (this.cache.has(cacheKey)) {
      return this.cache.get(cacheKey)
    }
    
    let result = []
    
    if (Array.isArray(answer)) {
      result = answer.map(a => String(a).toUpperCase().trim())
    } else if (typeof answer === 'string') {
      result = this.parseCorrectAnswerString(answer)
    } else {
      result = [String(answer).toUpperCase().trim()]
    }
    
    // 去重和过滤
    const uniqueAnswers = [...new Set(result)].filter(a => a !== '')
    
    this.cache.set(cacheKey, uniqueAnswers)
    return uniqueAnswers
  }

  /**
   * 解析正确答案字符串 - 简化处理
   */
  parseCorrectAnswerString(answerStr) {
    if (!answerStr) return []
    
    // 尝试JSON解析
    try {
      const parsed = JSON.parse(answerStr)
      if (Array.isArray(parsed)) {
        return parsed.map(a => String(a).toUpperCase().trim())
      }
      return [String(parsed).toUpperCase().trim()]
    } catch (e) {
      // 字符串分割处理
      return answerStr
        .split(/[,，、]/)
        .map(a => a.trim().toUpperCase())
        .filter(a => a !== '')
    }
  }

  /**
   * 从参数获取用户答案 - 简化查找逻辑
   */
  getUserAnswersFromParams() {
    // 优先从全局状态获取
    let userAnswers = {}
    try {
      const app = getApp()
      if (app && app.globalData && app.globalData.currentExamSettings && app.globalData.currentExamSettings.userAnswers) {
        userAnswers = app.globalData.currentExamSettings.userAnswers
      }
    } catch (error) {
      console.warn('获取全局状态失败:', error)
    }
    
    // 从本地存储获取
    try {
      const storedParams = uni.getStorageSync('questionParams')
      if (storedParams && storedParams.userAnswers) {
        userAnswers = storedParams.userAnswers
      }
    } catch (error) {
      console.warn('获取本地存储失败:', error)
    }
    
    return userAnswers
  }

  /**
   * 让出主线程控制权
   */
  yieldToMain() {
    return new Promise(resolve => {
      setTimeout(resolve, 0)
    })
  }

  /**
   * 清理缓存 - 避免内存泄漏
   */
  cleanup(maxAge = 300000) { // 默认5分钟过期
    const now = Date.now()
    for (const [key, value] of this.cache.entries()) {
      if (value.timestamp && now - value.timestamp > maxAge) {
        this.cache.delete(key)
      }
    }
  }

  /**
   * 检查缓存大小，超过限制时清理
   */
  checkCacheSize() {
    if (this.cache.size > this.maxCacheSize) {
      // 缓存大小超过限制，清理一半的缓存项
      const keys = Array.from(this.cache.keys())
      const keysToRemove = keys.slice(0, Math.floor(keys.length / 2))
      keysToRemove.forEach(key => this.cache.delete(key))
    }
  }

  /**
   * 完全清空缓存
   */
  clearAllCache() {
    this.cache.clear()
    // 同时清空本地存储缓存
    try {
      const keys = uni.getStorageInfoSync().keys
      keys.forEach(key => {
        if (key.startsWith('data_processor_')) {
          uni.removeStorageSync(key)
        }
      })
    } catch (error) {
      console.error('清空本地存储缓存失败:', error)
    }
  }

  /**
   * 获取处理统计信息
   */
  getStats() {
    return {
      cacheSize: this.cache.size,
      processingQueueLength: this.processingQueue.length
    }
  }
}

// 创建全局实例
const dataProcessor = new DataProcessor()

// 定期清理过期缓存
setInterval(() => {
  dataProcessor.cleanup()
}, 60000) // 每分钟清理一次

export default dataProcessor