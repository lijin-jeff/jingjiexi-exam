/**
 * 答题页面状态管理模块 (Vue 2 兼容版)
 * 模拟 Pinia API 风格的状态管理
 */

// 导入 Vue 实例
import Vue from 'vue'

class ExamStore {
  constructor() {
    // 使用 Vue.observable 创建响应式状态
    this.state = Vue.observable({
      // 题目相关
      swiperList: [],
      swiperCurrentIndex: 0,
      answeredCount: 0,
      
      // 模式相关
      currentMode: 'learnPractice',
      isExamination: false,
      examTimeInSeconds: 0,
      
      // UI 状态
      showAnswerCard: false,
      showModeSwitch: false,
      forceShowAnswer: false,
      disableSwipe: false,
      
      // 计时器相关
      seconds: 0,
      timerId: null,
      history_id: 0,
      
      // 模态框状态
      showConfirmModal: false,
      showResultModal: false,
      resultMessage: '',
      confirmModalContent: '确定要提交答题吗？提交后将无法修改答案',
      
      // 防抖相关
      isLiking: {},
      likeDebounceTimer: null,
      shareDebounceTimer: null,
      _lastProcessedIndex: -1,
      _lastSwiperChangeTime: 0,
      _lastButtonClickTime: 0,
      
      // 性能优化缓存
      _cachedCurrentQuestion: null,
      _cachedQuestionIndex: -1,
      
      // 其他状态
      selectedCategoryUid: '',
      shouldRestoreOnMount: false,
      shouldStartNewPractice: false,
      savedProgress: null,
      currentShareQuestion: null,
      isSubmitting: false
    })
    
    // 订阅者列表
    this.subscribers = []
    
    // 计算属性缓存
    this.computedCache = new Map()
    
  }
  
  // ==================== 状态访问 ====================
  
  getState() {
    return { ...this.state }
  }
  
  // ==================== 计算属性 ====================
  
  getCurrentQuestion() {
    const cacheKey = 'currentQuestion';
    const currentIndex = this.state.swiperCurrentIndex;
    
    // 检查缓存是否有效
    const cached = this.computedCache.get(cacheKey);
    if (cached && cached.index === currentIndex) {
      return cached.value;
    }
    
    // 直接返回当前题目，与 commonQuestion.vue 中的实现保持一致
    const question = this.state.swiperList[currentIndex];
    
    // 更新缓存
    this.computedCache.set(cacheKey, {
      index: currentIndex,
      value: question
    });
    
    return question;
  }
  
  getProgressPercent() {
    if (this.state.swiperList.length === 0) return 0
    return ((this.state.swiperCurrentIndex + 1) / this.state.swiperList.length) * 100
  }
  
  getIsLastQuestion() {
    return this.state.swiperCurrentIndex === this.state.swiperList.length - 1
  }
  
  getIsFirstQuestion() {
    return this.state.swiperCurrentIndex === 0
  }
  
  // ==================== 状态修改方法 ====================
  
  setState(newState) {
    const oldState = { ...this.state }
    
    // 使用 Vue.set 或直接赋值来保持响应式
    Object.keys(newState).forEach(key => {
      if (key in this.state) {
        // 直接修改响应式对象的属性
        this.state[key] = newState[key]
      }
    })
    
    
    this.notifySubscribers(oldState, this.state)
    this.clearComputedCache()
  }
  
  // 题目操作
  setSwiperList(list) {
    const oldState = { ...this.state }
    
    
    // 直接替换数组以触发响应式更新
    if (Array.isArray(list)) {
      // 使用 Vue.set 确保响应式更新
      Vue.set(this.state, 'swiperList', list)
      
    } else {
      console.error('[Store] setSwiperList 接收到非数组参数:', list)
    }
    
    this.notifySubscribers(oldState, this.state)
    this.clearComputedCache()
    this.calculateAnsweredCount()
  }
  
  setCurrentIndex(index) {
    if (index >= 0 && index < this.state.swiperList.length) {
      this.setState({ swiperCurrentIndex: index })
    }
  }
  
  nextQuestion() {
    if (this.state.swiperCurrentIndex < this.state.swiperList.length - 1) {
      this.setState({ 
        swiperCurrentIndex: this.state.swiperCurrentIndex + 1,
        forceShowAnswer: false
      })
      return true
    }
    return false
  }
  
  prevQuestion() {
    if (this.state.swiperCurrentIndex > 0) {
      this.setState({ 
        swiperCurrentIndex: this.state.swiperCurrentIndex - 1,
        forceShowAnswer: false
      })
      return true
    }
    return false
  }
  
  // 更新题目数据
  updateQuestion(questionUid, updatedData) {
    const index = this.state.swiperList.findIndex(q => q.uid === questionUid)
    if (index !== -1) {
      // 创建包含 swiperList 深拷贝的 oldState
      const oldState = {
        ...this.state,
        swiperList: this.state.swiperList.map(q => ({ ...q }))
      }
      
      // 使用 Vue.set 确保响应式更新
      const updatedQuestion = { ...this.state.swiperList[index], ...updatedData }
      Vue.set(this.state.swiperList, index, updatedQuestion)
      
      if (index === this.state.swiperCurrentIndex) {
        this.clearComputedCache()
      }
      
      this.notifySubscribers(oldState, this.state)
      this.calculateAnsweredCount()
    }
  }
  
  // 模式切换
  switchMode(mode) {
    this.setState({
      currentMode: mode,
      forceShowAnswer: mode === 'reviewOnly'
    })
  }
  
  // 计时器操作
  startTimer() {
    if (this.state.timerId) {
      clearInterval(this.state.timerId)
    }
    
    const timerId = setInterval(() => {
      this.setState({ seconds: this.state.seconds + 1 })
      if (this.state.examTimeInSeconds > 0 && this.state.seconds >= this.state.examTimeInSeconds) {
        this.handleSubmitExam()
      }
    }, 1000)
    
    this.setState({ timerId })
  }
  
  stopTimer() {
    if (this.state.timerId) {
      clearInterval(this.state.timerId)
      this.setState({ timerId: null })
    }
  }
  
  resetTimer() {
    this.stopTimer()
    this.setState({ seconds: 0 })
  }
  
  // 模态框操作
  showConfirm(content = '') {
    const updates = { showConfirmModal: true }
    if (content) {
      updates.confirmModalContent = content
    }
    this.setState(updates)
  }
  
  hideConfirm() {
    this.setState({ 
      showConfirmModal: false,
      confirmModalContent: '确定要提交答题吗？提交后将无法修改答案'
    })
  }
  
  showResult(message) {
    this.setState({
      showResultModal: true,
      resultMessage: message
    })
  }
  
  hideResult() {
    this.setState({
      showResultModal: false,
      resultMessage: ''
    })
  }
  
  // 防抖相关
  setLastProcessedIndex(index) {
    this.setState({ _lastProcessedIndex: index })
  }
  
  setLastSwiperChangeTime(time) {
    this.setState({ _lastSwiperChangeTime: time })
  }
  
  setLastButtonClickTime(time) {
    this.setState({ _lastButtonClickTime: time })
  }
  
  // 缓存管理
  clearComputedCache() {
    this.computedCache.clear()
  }
  
  clearAllCache() {
    this.clearComputedCache()
    this.setState({
      isLiking: {},
      _cachedQuestionIndex: -1,
      _cachedCurrentQuestion: null
    })
    
    if (this.state.likeDebounceTimer) {
      clearTimeout(this.state.likeDebounceTimer)
    }
    if (this.state.shareDebounceTimer) {
      clearTimeout(this.state.shareDebounceTimer)
    }
    
    this.setState({
      likeDebounceTimer: null,
      shareDebounceTimer: null
    })
  }
  
  // 答题统计
  calculateAnsweredCount() {
    const count = this.state.swiperList.filter(q => 
      q.is_submitted || q.user_answer || q.selectedAnswer
    ).length
    this.setState({ answeredCount: count })
  }
  
  incrementAnsweredCount() {
    this.setState({ answeredCount: this.state.answeredCount + 1 })
  }
  
  decrementAnsweredCount() {
    if (this.state.answeredCount > 0) {
      this.setState({ answeredCount: this.state.answeredCount - 1 })
    }
  }
  
  // 考试提交
  handleSubmitExam() {
    this.stopTimer()
    // 这里可以调用提交考试的逻辑
  }
  
  // 重置所有状态
  resetAll() {
    const oldState = { ...this.state }
    
    
    const initialState = {
      swiperList: [],
      swiperCurrentIndex: 0,
      answeredCount: 0,
      currentMode: 'learnPractice',
      isExamination: false,
      examTimeInSeconds: 0,
      showAnswerCard: false,
      showModeSwitch: false,
      forceShowAnswer: false,
      disableSwipe: false,
      seconds: 0,
      timerId: null,
      history_id: 0,
      showConfirmModal: false,
      showResultModal: false,
      resultMessage: '',
      confirmModalContent: '确定要提交答题吗？提交后将无法修改答案',
      isLiking: {},
      likeDebounceTimer: null,
      shareDebounceTimer: null,
      _lastProcessedIndex: -1,
      _lastSwiperChangeTime: 0,
      _lastButtonClickTime: 0,
      _cachedCurrentQuestion: null,
      _cachedQuestionIndex: -1,
      selectedCategoryUid: '',
      shouldRestoreOnMount: false,
      shouldStartNewPractice: false,
      savedProgress: null,
      currentShareQuestion: null,
      isSubmitting: false
    }
    
    // 直接修改每个属性以保持响应式
    Object.keys(initialState).forEach(key => {
      if (key === 'swiperList') {
        // 数组需要特殊处理
        this.state.swiperList.splice(0, this.state.swiperList.length)
      } else {
        this.state[key] = initialState[key]
      }
    })
    
    this.clearAllCache()
    this.notifySubscribers(oldState, this.state)
    
  }
  
  // ==================== 订阅机制 ====================
  
  subscribe(callback) {
    this.subscribers.push(callback)
    return () => {
      const index = this.subscribers.indexOf(callback)
      if (index > -1) {
        this.subscribers.splice(index, 1)
      }
    }
  }
  
  notifySubscribers(oldState, newState) {
    // 提供默认值,防止未传参数导致订阅回调报错
    const safeOldState = oldState || this.state
    const safeNewState = newState || this.state
    
    // 性能优化：对于高频更新的状态（如 seconds, timerId），降低通知频率
    // 只有在关键状态发生变化时才通知订阅者
    const shouldNotify = this.shouldNotifySubscribers(safeOldState, safeNewState)
    
    if (!shouldNotify) {
      return
    }
    
    this.subscribers.forEach(callback => {
      try {
        callback(safeOldState, safeNewState)
      } catch (error) {
        console.error('Store subscriber error:', error)
      }
    })
  }
  
  // 判断是否需要通知订阅者
  shouldNotifySubscribers(oldState, newState) {
    // 关键状态列表（变化时必须通知）
    const criticalKeys = [
      'swiperList',
      'swiperCurrentIndex',
      'currentMode',
      'showAnswerCard',
      'showModeSwitch',
      'showConfirmModal',
      'showResultModal',
      'isSubmitting',
      'answeredCount'
    ]
    
    // 检查关键状态是否变化
    for (const key of criticalKeys) {
      if (oldState[key] !== newState[key]) {
        return true
      }
    }
    
    // 对于数组类型的特殊处理
    if (oldState.swiperList?.length !== newState.swiperList?.length) {
      return true
    }
    
    // 检查 swiperList 中的元素属性是否变化
    if (oldState.swiperList && newState.swiperList && oldState.swiperList.length === newState.swiperList.length) {
      for (let i = 0; i < oldState.swiperList.length; i++) {
        const oldItem = oldState.swiperList[i]
        const newItem = newState.swiperList[i]
        // 检查是否有属性变化，特别是 is_correct 属性
        if (oldItem && newItem && (oldItem.is_correct !== newItem.is_correct || oldItem.is_submitted !== newItem.is_submitted || oldItem.user_answer !== newItem.user_answer)) {
          return true
        }
      }
    }
    
    // 非关键状态变化（如 seconds, timerId），不通知
    return false
  }
  
  // ==================== 工具方法 ====================
  
  // 获取状态快照用于调试
  getSnapshot() {
    return {
      state: this.getState(),
      computed: {
        currentQuestion: this.getCurrentQuestion(),
        progressPercent: this.getProgressPercent(),
        isLastQuestion: this.getIsLastQuestion(),
        isFirstQuestion: this.getIsFirstQuestion()
      },
      subscribersCount: this.subscribers.length
    }
  }
}

// 创建全局单例实例
const examStore = new ExamStore()

// 导出 Pinia 风格的 API
export const useExamStore = () => {
  return {
    // 状态
    ...examStore.state,
    
    // 计算属性 (getter)
    currentQuestion: examStore.getCurrentQuestion(),
    progressPercent: examStore.getProgressPercent(),
    isLastQuestion: examStore.getIsLastQuestion(),
    isFirstQuestion: examStore.getIsFirstQuestion(),
    
    // 方法 (actions)
    setState: examStore.setState.bind(examStore),
    setSwiperList: examStore.setSwiperList.bind(examStore),
    setCurrentIndex: examStore.setCurrentIndex.bind(examStore),
    nextQuestion: examStore.nextQuestion.bind(examStore),
    prevQuestion: examStore.prevQuestion.bind(examStore),
    updateQuestion: examStore.updateQuestion.bind(examStore),
    switchMode: examStore.switchMode.bind(examStore),
    startTimer: examStore.startTimer.bind(examStore),
    stopTimer: examStore.stopTimer.bind(examStore),
    resetTimer: examStore.resetTimer.bind(examStore),
    showConfirm: examStore.showConfirm.bind(examStore),
    hideConfirm: examStore.hideConfirm.bind(examStore),
    showResult: examStore.showResult.bind(examStore),
    hideResult: examStore.hideResult.bind(examStore),
    setLastProcessedIndex: examStore.setLastProcessedIndex.bind(examStore),
    setLastSwiperChangeTime: examStore.setLastSwiperChangeTime.bind(examStore),
    setLastButtonClickTime: examStore.setLastButtonClickTime.bind(examStore),
    clearAllCache: examStore.clearAllCache.bind(examStore),
    calculateAnsweredCount: examStore.calculateAnsweredCount.bind(examStore),
    incrementAnsweredCount: examStore.incrementAnsweredCount.bind(examStore),
    decrementAnsweredCount: examStore.decrementAnsweredCount.bind(examStore),
    handleSubmitExam: examStore.handleSubmitExam.bind(examStore),
    resetAll: examStore.resetAll.bind(examStore),
    subscribe: examStore.subscribe.bind(examStore),
    getSnapshot: examStore.getSnapshot.bind(examStore)
  }
}

// 默认导出 store 实例
export default examStore