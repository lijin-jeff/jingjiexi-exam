/**
 * 性能测试和对比工具
 * 用于验证优化效果
 */

class PerformanceTester {
  constructor() {
    this.results = []
    this.currentTest = null
  }

  /**
   * 获取当前时间戳（兼容微信小程序环境）
   */
  _now() {
    // 优先使用 performance.now()，如果不存在则使用 Date.now()
    if (typeof performance !== 'undefined' && performance.now) {
      return performance.now()
    }
    return Date.now()
  }

  /**
   * 开始性能测试
   */
  startTest(testName, description) {
    this.currentTest = {
      name: testName,
      description: description,
      startTime: this._now(),
      measurements: []
    }
  }

  /**
   * 记录测量点
   */
  measure(label, data = {}) {
    if (!this.currentTest) return
    
    const now = this._now()
    this.currentTest.measurements.push({
      label: label,
      timestamp: now,
      data: data,
      elapsed: now - this.currentTest.startTime
    })
  }

  /**
   * 结束测试并保存结果
   */
  endTest() {
    if (!this.currentTest) return null
    
    const endTime = this._now()
    const result = {
      ...this.currentTest,
      endTime: endTime,
      totalTime: endTime - this.currentTest.startTime,
      measurements: this.currentTest.measurements
    }
    
    this.results.push(result)
    // 限制结果数量，只保留最近的10个测试结果
    if (this.results.length > 10) {
      this.results = this.results.slice(-10)
    }
    this.currentTest = null
    return result
  }

  /**
   * 测试数据处理性能
   */
  async testDataProcessing(dataProcessor, testData, iterations = 100) {
    this.startTest('Data Processing', '测试用户答案解析性能')
    
    // 测试原始方法
    this.measure('start_original')
    for (let i = 0; i < iterations; i++) {
      // 模拟原始复杂处理逻辑
      this.simulateOriginalProcessing(testData)
    }
    this.measure('end_original')
    
    // 测试优化方法
    this.measure('start_optimized')
    for (let i = 0; i < iterations; i++) {
      await dataProcessor.processQuestionList(testData, 5)
    }
    this.measure('end_optimized')
    
    return this.endTest()
  }

  /**
   * 测试虚拟列表计算性能
   */
  testVirtualListCalculation(optimizer, dataList, currentIndex, iterations = 1000) {
    this.startTest('Virtual List Calculation', '测试虚拟列表计算性能')
    
    this.measure('start_calculation')
    for (let i = 0; i < iterations; i++) {
      optimizer.getOptimizedVirtualList(dataList, currentIndex, 1)
    }
    this.measure('end_calculation')
    
    return this.endTest()
  }

  /**
   * 测试滑动处理性能
   */
  async testSwipeHandling(swipeHandler, testData, iterations = 100) {
    this.startTest('Swipe Handling', '测试滑动处理性能')
    
    const mockState = {
      swiperCurrentIndex: 0,
      swiperList: testData,
      virtualCurrentIndex: 0
    }
    
    this.measure('start_swipe')
    for (let i = 0; i < iterations; i++) {
      const mockEvent = {
        detail: { current: i % 3 }
      }
      await swipeHandler.handleSwipe(mockEvent, mockState)
    }
    this.measure('end_swipe')
    
    return this.endTest()
  }

  /**
   * 生成性能报告
   */
  generateReport() {
    const report = {
      summary: {
        totalTests: this.results.length,
        averageImprovement: 0
      },
      tests: this.results.map(result => ({
        name: result.name,
        description: result.description,
        totalTime: result.totalTime,
        measurements: result.measurements,
        performance: this.analyzePerformance(result)
      }))
    }
    
    // 计算平均性能提升
    const improvements = report.tests
      .map(test => test.performance.improvement)
      .filter(imp => imp !== null)
    
    if (improvements.length > 0) {
      report.summary.averageImprovement = 
        improvements.reduce((sum, imp) => sum + imp, 0) / improvements.length
    }
    
    return report
  }

  /**
   * 分析单个测试的性能
   */
  analyzePerformance(testResult) {
    const measurements = testResult.measurements
    
    // 查找原始和优化方法的时间
    const originalStart = measurements.find(m => m.label === 'start_original')
    const originalEnd = measurements.find(m => m.label === 'end_original')
    const optimizedStart = measurements.find(m => m.label === 'start_optimized')
    const optimizedEnd = measurements.find(m => m.label === 'end_optimized')
    
    if (originalStart && originalEnd && optimizedStart && optimizedEnd) {
      const originalTime = originalEnd.elapsed - originalStart.elapsed
      const optimizedTime = optimizedEnd.elapsed - optimizedStart.elapsed
      const improvement = ((originalTime - optimizedTime) / originalTime) * 100
      
      return {
        originalTime: originalTime,
        optimizedTime: optimizedTime,
        improvement: improvement,
        isImproved: improvement > 0
      }
    }
    
    return { improvement: null }
  }

  /**
   * 模拟原始复杂处理逻辑（用于对比测试）
   */
  simulateOriginalProcessing(dataList) {
    // 模拟原有的嵌套循环和复杂处理
    return dataList.map(item => {
      // 模拟复杂的用户答案处理
      if (item.userAnswers) {
        const processed = []
        Object.keys(item.userAnswers).forEach(key => {
          const answer = item.userAnswers[key]
          if (typeof answer === 'string') {
            let parsed = answer
            let attempts = 0
            while (typeof parsed === 'string' && attempts < 2) {
              try {
                parsed = JSON.parse(parsed)
                attempts++
              } catch (e) {
                break
              }
            }
            processed.push(parsed)
          }
        })
        item.processedAnswers = processed
      }
      
      // 模拟复杂的选项处理
      if (item.options) {
        item.options = item.options.map(option => ({
          ...option,
          is_correct: this.complexAnswerValidation(option.answer, item.correctAnswer)
        }))
      }
      
      return item
    })
  }

  /**
   * 复杂的答案验证逻辑（模拟原有代码）
   */
  complexAnswerValidation(userAnswer, correctAnswer) {
    if (!userAnswer || !correctAnswer) return false
    
    // 模拟复杂的字符串处理和比较逻辑
    const cleanUser = String(userAnswer).replace(/<[^>]*>/g, '').trim().toUpperCase()
    const cleanCorrect = String(correctAnswer).replace(/<[^>]*>/g, '').trim().toUpperCase()
    
    return cleanUser === cleanCorrect
  }

  /**
   * 清空测试结果
   */
  clearResults() {
    this.results = []
  }

  /**
   * 输出测试结果到控制台
   */
  logResults() {
    // 只输出最近的5个测试结果，避免控制台日志积累
    const recentResults = this.results.slice(-5)
    
    console.group('📊 性能测试报告')
    console.log(`总测试数: ${this.results.length} (显示最近5个)`)
    console.groupEnd()
    
    recentResults.forEach(test => {
      console.group(`🔹 ${test.name}`)
      console.log(`描述: ${test.description}`)
      console.log(`总耗时: ${test.totalTime.toFixed(2)}ms`)
      console.groupEnd()
    })
  }
}

// 创建全局实例
const performanceTester = new PerformanceTester()

export default performanceTester