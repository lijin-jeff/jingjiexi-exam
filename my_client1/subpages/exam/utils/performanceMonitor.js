/**
 * 性能监控工具
 * 收集和分析 commonQuestion.vue 组件的性能指标
 */

// 🔧 微信小程序兼容：提供 performance API polyfill
const getPerformanceNow = () => {
  // 优先使用原生 performance.now()
  if (typeof performance !== 'undefined' && performance.now) {
    return performance.now();
  }
  // 降级到 Date.now()
  return Date.now();
};

const getMemoryInfo = () => {
  // 微信小程序不支持 performance.memory
  if (typeof performance !== 'undefined' && performance.memory) {
    return {
      used: performance.memory.usedJSHeapSize,
      total: performance.memory.totalJSHeapSize,
      limit: performance.memory.jsHeapSizeLimit
    };
  }
  // 返回默认值
  return {
    used: 0,
    total: 0,
    limit: 0
  };
};

class PerformanceMonitor {
  constructor() {
    this.metrics = {
      // 渲染性能
      renderTimes: [],        // 渲染耗时记录
      firstRender: 0,         // 首次渲染时间
      averageRenderTime: 0,   // 平均渲染时间
      
      // 滑动性能
      swipeOperations: [],    // 滑动操作记录
      averageSwipeTime: 0,    // 平均滑动时间
      swipeFailures: 0,       // 滑动失败次数
      
      // 数据加载性能
      dataLoadTimes: [],      // 数据加载耗时
      averageLoadTime: 0,     // 平均加载时间
      cacheHitRate: 0,        // 缓存命中率
      
      // 计算性能
      computationTimes: [],   // 计算耗时记录
      virtualListCalcs: [],   // 虚拟列表计算记录
      answerProcessTimes: [], // 答案处理耗时
      
      // 内存使用
      memoryUsage: [],        // 内存使用记录
      maxMemory: 0,           // 最大内存使用
      
      // 用户交互
      userActions: [],        // 用户操作记录
      responseTimes: [],      // 响应时间记录
      
      // 错误统计
      errors: [],             // 错误记录
      errorRate: 0,           // 错误率
    }
    
    this.startTime = Date.now()
    this.isActive = true
    this.sampleRate = 1.0 // 采样率，1.0表示100%采样
  }
  
  // ==================== 渲染性能监控 ====================
  
  /**
   * 记录渲染开始时间
   */
  startRenderTracking() {
    if (!this.shouldSample()) return null
    return {
      startTime: getPerformanceNow(),
      id: Math.random().toString(36).substr(2, 9)
    }
  }
  
  /**
   * 记录渲染完成
   */
  endRenderTracking(trackingInfo) {
    if (!trackingInfo || !this.shouldSample()) return
    
    const renderTime = getPerformanceNow() - trackingInfo.startTime
    this.metrics.renderTimes.push({
      time: Date.now(),
      duration: renderTime,
      id: trackingInfo.id
    })
    
    // 更新平均渲染时间
    this.calculateAverage('renderTimes', 'averageRenderTime')
    
    // 记录首次渲染时间
    if (this.metrics.firstRender === 0) {
      this.metrics.firstRender = renderTime
    }
    
    console.log(`[性能监控] 渲染完成: ${renderTime.toFixed(2)}ms`)
  }
  
  // ==================== 滑动性能监控 ====================
  
  /**
   * 记录滑动操作开始
   */
  startSwipeTracking(swipeData) {
    // 🔑 修复：强制记录滑动操作，确保性能监控总是能触发
    // 忽略shouldSample()检查，因为滑动操作是核心功能
    return {
      startTime: getPerformanceNow(),
      ...swipeData,
      // 添加标记，确保endSwipeTracking知道这是强制记录的
      forced: true
    }
  }
  
  /**
   * 记录滑动操作完成
   */
  endSwipeTracking(trackingInfo, result) {
    if (!trackingInfo) return
    // 🔑 修复：如果是强制记录的滑动操作，忽略shouldSample()检查
    if (!trackingInfo.forced && !this.shouldSample()) return
    
    // 🔑 修复：使用统一的时间计算方式
    // 避免getPerformanceNow()与Date.now()的时间戳差异问题
    let swipeTime;
    if (result && result.duration !== undefined) {
      // 如果提供了计算好的duration，直接使用
      swipeTime = result.duration;
    } else if (trackingInfo.startTime && typeof trackingInfo.startTime === 'number') {
      // 否则使用Date.now()计算时间差
      swipeTime = Date.now() - trackingInfo.startTime;
    } else {
      // 兜底方案
      swipeTime = getPerformanceNow() - (trackingInfo.startTime || 0);
    }
    
    const record = {
      time: Date.now(),
      duration: swipeTime,
      fromIndex: trackingInfo.fromIndex,
      toIndex: trackingInfo.toIndex,
      direction: trackingInfo.direction,
      success: result?.success ?? true,
      ...result
    }
    
    this.metrics.swipeOperations.push(record)
    this.calculateAverage('swipeOperations', 'averageSwipeTime')
    
    // 统计失败次数
    if (!record.success) {
      this.metrics.swipeFailures++
    }
    
    console.log(`[性能监控] 滑动${record.success ? '成功' : '失败'}: ${swipeTime.toFixed(2)}ms`, {
      from: record.fromIndex,
      to: record.toIndex,
      direction: record.direction
    })
  }
  
  // ==================== 数据加载监控 ====================
  
  /**
   * 记录数据加载开始
   */
  startDataLoad(label = 'unknown') {
    if (!this.shouldSample()) return null
    return {
      startTime: getPerformanceNow(),
      label: label
    }
  }
  
  /**
   * 记录数据加载完成
   */
  endDataLoad(trackingInfo, success = true, dataSize = 0) {
    if (!trackingInfo || !this.shouldSample()) return
    
    // 确保trackingInfo.startTime存在且是数字
    if (!trackingInfo.startTime || typeof trackingInfo.startTime !== 'number') {
      console.warn('[性能监控] 无效的trackingInfo.startTime:', trackingInfo.startTime)
      return
    }
    
    const loadTime = getPerformanceNow() - trackingInfo.startTime
    const record = {
      time: Date.now(),
      duration: loadTime,
      label: trackingInfo.label,
      success: success,
      dataSize: dataSize
    }
    
    this.metrics.dataLoadTimes.push(record)
    this.calculateAverage('dataLoadTimes', 'averageLoadTime')
    
    console.log(`[性能监控] 数据加载${success ? '成功' : '失败'}: ${loadTime.toFixed(2)}ms (${dataSize}项)`)
  }
  
  // ==================== 计算性能监控 ====================
  
  /**
   * 记录计算操作
   */
  trackComputation(operation, computeFn) {
    if (!this.shouldSample()) return computeFn()
    
    const startTime = getPerformanceNow()
    const result = computeFn()
    const duration = getPerformanceNow() - startTime
    
    const record = {
      time: Date.now(),
      operation: operation,
      duration: duration,
      result: result
    }
    
    this.metrics.computationTimes.push(record)
    
    if (operation.includes('virtualList')) {
      this.metrics.virtualListCalcs.push(record)
    } else if (operation.includes('answer')) {
      this.metrics.answerProcessTimes.push(record)
    }
    
    console.log(`[性能监控] ${operation}: ${duration.toFixed(2)}ms`)
    return result
  }
  
  // ==================== 用户交互监控 ====================
  
  /**
   * 记录用户操作
   */
  trackUserAction(action, handler) {
    if (!this.shouldSample()) return handler()
    
    const startTime = getPerformanceNow()
    const result = handler()
    const responseTime = getPerformanceNow() - startTime
    
    const record = {
      time: Date.now(),
      action: action,
      responseTime: responseTime,
      timestamp: Date.now() - this.startTime
    }
    
    this.metrics.userActions.push(record)
    this.metrics.responseTimes.push(responseTime)
    
    console.log(`[性能监控] 用户操作 ${action}: ${responseTime.toFixed(2)}ms`)
    return result
  }
  
  // ==================== 错误监控 ====================
  
  /**
   * 记录错误
   */
  recordError(error, context = {}) {
    const record = {
      time: Date.now(),
      error: error.message || error,
      stack: error.stack || '',
      context: context,
      timestamp: Date.now() - this.startTime
    }
    
    this.metrics.errors.push(record)
    this.calculateErrorRate()
    
    console.error('[性能监控] 错误记录:', record)
  }
  
  // ==================== 内存监控 ====================
  
  /**
   * 采样内存使用情况
   */
  sampleMemoryUsage() {
    if (!this.shouldSample()) return
    
    const memInfo = getMemoryInfo();
    if (memInfo.used === 0 && memInfo.total === 0) {
      // 当前环境不支持内存监控
      return;
    }
    
    const memoryInfo = {
      time: Date.now(),
      used: memInfo.used,
      total: memInfo.total,
      limit: memInfo.limit
    }
    
    this.metrics.memoryUsage.push(memoryInfo)
    
    if (memoryInfo.used > this.metrics.maxMemory) {
      this.metrics.maxMemory = memoryInfo.used
    }
    
    console.log(`[性能监控] 内存使用: ${(memoryInfo.used / 1024 / 1024).toFixed(2)}MB`)
  }
  
  // ==================== 工具方法 ====================
  
  /**
   * 计算平均值
   */
  calculateAverage(sourceKey, targetKey) {
    const records = this.metrics[sourceKey]
    if (records.length === 0) return
    
    const sum = records.reduce((acc, record) => acc + record.duration, 0)
    this.metrics[targetKey] = sum / records.length
  }
  
  /**
   * 计算错误率
   */
  calculateErrorRate() {
    const totalActions = this.metrics.userActions.length
    const totalErrors = this.metrics.errors.length
    this.metrics.errorRate = totalActions > 0 ? (totalErrors / totalActions) * 100 : 0
  }
  
  /**
   * 采样判断
   */
  shouldSample() {
    return this.isActive && Math.random() < this.sampleRate
  }
  
  /**
   * 获取性能报告
   */
  getPerformanceReport() {
    const totalTime = Date.now() - this.startTime
    
    return {
      summary: {
        totalTime: totalTime,
        isActive: this.isActive,
        sampleRate: this.sampleRate
      },
      rendering: {
        firstRender: this.metrics.firstRender,
        averageRenderTime: this.metrics.averageRenderTime,
        totalRenders: this.metrics.renderTimes.length
      },
      swiping: {
        averageSwipeTime: this.metrics.averageSwipeTime,
        totalSwipes: this.metrics.swipeOperations.length,
        swipeFailures: this.metrics.swipeFailures,
        successRate: this.metrics.swipeOperations.length > 0 
          ? ((this.metrics.swipeOperations.length - this.metrics.swipeFailures) / this.metrics.swipeOperations.length * 100).toFixed(2) + '%'
          : 'N/A'
      },
      loading: {
        averageLoadTime: this.metrics.averageLoadTime,
        totalLoads: this.metrics.dataLoadTimes.length
      },
      computation: {
        totalComputations: this.metrics.computationTimes.length,
        virtualListCalculations: this.metrics.virtualListCalcs.length,
        answerProcessing: this.metrics.answerProcessTimes.length
      },
      userExperience: {
        totalActions: this.metrics.userActions.length,
        averageResponseTime: this.metrics.responseTimes.length > 0
          ? (this.metrics.responseTimes.reduce((a, b) => a + b, 0) / this.metrics.responseTimes.length).toFixed(2) + 'ms'
          : 'N/A',
        errorRate: this.metrics.errorRate.toFixed(2) + '%'
      },
      memory: {
        maxMemory: (this.metrics.maxMemory / 1024 / 1024).toFixed(2) + 'MB',
        currentMemorySamples: this.metrics.memoryUsage.length
      }
    }
  }
  
  /**
   * 输出详细报告到控制台
   */
  printDetailedReport() {
    const report = this.getPerformanceReport()
    
    console.group('📊 性能监控报告')
    console.log('📈 概览:')
    console.table({
      '运行时间': `${report.summary.totalTime}ms`,
      '采样率': `${report.summary.sampleRate * 100}%`,
      '状态': report.summary.isActive ? '活跃' : '暂停'
    })
    
    console.log('\n🎨 渲染性能:')
    console.table({
      '首次渲染': `${report.rendering.firstRender.toFixed(2)}ms`,
      '平均渲染时间': `${report.rendering.averageRenderTime.toFixed(2)}ms`,
      '渲染次数': report.rendering.totalRenders
    })
    
    console.log('\n👉 滑动性能:')
    console.table({
      '平均滑动时间': `${report.swiping.averageSwipeTime.toFixed(2)}ms`,
      '滑动次数': report.swiping.totalSwipes,
      '失败次数': report.swiping.swipeFailures,
      '成功率': report.swiping.successRate
    })
    
    console.log('\n📥 数据加载:')
    console.table({
      '平均加载时间': `${report.loading.averageLoadTime.toFixed(2)}ms`,
      '加载次数': report.loading.totalLoads
    })
    
    console.log('\n⚡ 用户体验:')
    console.table({
      '总操作数': report.userExperience.totalActions,
      '平均响应时间': report.userExperience.averageResponseTime,
      '错误率': report.userExperience.errorRate
    })
    
    console.log('\n💾 内存使用:')
    console.table({
      '最大内存': report.memory.maxMemory,
      '内存采样数': report.memory.currentMemorySamples
    })
    
    console.groupEnd()
  }
  
  /**
   * 重置监控数据
   */
  reset() {
    this.metrics = {
      renderTimes: [],
      firstRender: 0,
      averageRenderTime: 0,
      swipeOperations: [],
      averageSwipeTime: 0,
      swipeFailures: 0,
      dataLoadTimes: [],
      averageLoadTime: 0,
      cacheHitRate: 0,
      computationTimes: [],
      virtualListCalcs: [],
      answerProcessTimes: [],
      memoryUsage: [],
      maxMemory: 0,
      userActions: [],
      responseTimes: [],
      errors: [],
      errorRate: 0
    }
    this.startTime = Date.now()
    console.log('[性能监控] 数据已重置')
  }
  
  /**
   * 暂停监控
   */
  pause() {
    this.isActive = false
    console.log('[性能监控] 已暂停')
  }
  
  /**
   * 恢复监控
   */
  resume() {
    this.isActive = true
    console.log('[性能监控] 已恢复')
  }
  
  /**
   * 设置采样率
   */
  setSampleRate(rate) {
    this.sampleRate = Math.max(0, Math.min(1, rate))
    console.log(`[性能监控] 采样率设置为: ${(this.sampleRate * 100).toFixed(0)}%`)
  }
}

// 创建全局实例
const performanceMonitor = new PerformanceMonitor()

// 定期输出内存使用情况
setInterval(() => {
  performanceMonitor.sampleMemoryUsage()
}, 30000) // 每30秒采样一次

// 页面卸载时输出最终报告
if (typeof window !== 'undefined') {
  window.addEventListener('beforeunload', () => {
    performanceMonitor.printDetailedReport()
  })
}

export default performanceMonitor