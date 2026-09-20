# commonQuestion.vue 分块加载与缓存改造计划（无虚拟列表）

## 1. 改造背景

当前 commonQuestion.vue 实现存在以下问题：
- 所有题目一次性渲染到 DOM 中，内存消耗大
- 初始加载时间长，特别是题目数量较多时
- 滑动切换可能出现卡顿，影响用户体验
- 缺乏有效的内存管理机制

## 2. 改造目标

- 实现分块加载，每次只加载和处理部分题目
- 实现动态DOM管理，及时移除不需要的题目
- 增强缓存机制，减少重复加载和处理
- 实现预加载，提升用户体验
- 优化内存管理，及时清理不需要的数据

## 3. 具体改造方案

### 3.1 核心改造点

#### A. 分块加载机制
- 修改 `fetchQuestionList` 方法，支持分块加载
- 实现题目数据的分批获取和处理
- 每次只加载固定数量的题目（如10题）
- 实现按需加载，只加载用户可能浏览的题目

#### B. 动态DOM管理
- 实现题目组件的动态创建和销毁
- 当题目不在可视区域时，从DOM中移除
- 只保留当前、上一个和下一个题目在DOM中
- 使用 keep-alive 缓存活跃的题目组件

#### C. 增强缓存系统
- 扩展 `dataProcessor.js` 的缓存机制
- 实现题目数据的本地缓存
- 缓存已加载的题目数据，避免重复处理
- 实现缓存过期机制，避免内存泄漏

#### D. 预加载策略
- 实现题目预加载，提前加载即将浏览的题目
- 根据用户浏览速度动态调整预加载数量
- 在空闲时间执行预加载
- 优先预加载用户可能浏览的题目

#### E. 内存管理优化
- 实现题目数据的生命周期管理
- 及时清理不再需要的题目数据
- 监控内存使用情况，避免内存泄漏
- 实现内存使用阈值，当内存使用过高时主动清理

### 3.2 技术实现方案

#### 分块加载实现
```javascript
// 分块加载题目
async fetchQuestionList(chunkIndex = 0, chunkSize = 10) {
  // 计算偏移量
  const offset = chunkIndex * chunkSize
  
  // 调用 API 获取分块数据
  const res = await this.$api[apiMethod]({
    ...this.questionParams,
    offset,
    limit: chunkSize
  })
  
  // 处理数据
  if (res && res.code === 1 && res.data) {
    const questionList = await dataProcessor.processQuestionList(res.data.list || res.data)
    
    // 添加到已加载题目中
    this.loadedQuestions.push(...questionList)
    
    // 更新总题目数
    this.totalQuestions = res.data.total || questionList.length
    
    // 检查是否需要继续加载
    if (this.loadedQuestions.length < this.totalQuestions) {
      // 预加载下一块
      this.preloadNextChunk(chunkIndex + 1, chunkSize)
    }
  }
}
```

#### 动态DOM管理实现
```javascript
// 动态管理题目DOM
<swiper 
  :current="swiperCurrentIndex"
  :disable-touch="disableSwipe"
  class="question-swiper"
  @animationfinish="handleSwiperChange"
>
  <swiper-item
    v-for="(item, index) in activeQuestions"
    :key="item.uid"
  >
    <keep-alive>
      <!-- 题目内容 -->
    </keep-alive>
  </swiper-item>
</swiper>

// 计算活跃题目
computed: {
  activeQuestions() {
    const currentIndex = this.swiperCurrentIndex
    const start = Math.max(0, currentIndex - 1)
    const end = Math.min(this.loadedQuestions.length - 1, currentIndex + 1)
    return this.loadedQuestions.slice(start, end + 1)
  }
}

// 处理滑动变化
handleSwiperChange(e) {
  const newIndex = e.detail.current
  this.swiperCurrentIndex = newIndex
  
  // 清理不需要的题目DOM
  this.cleanupInactiveQuestions()
  
  // 预加载即将浏览的题目
  this.preloadQuestions(newIndex)
}
```

#### 缓存增强实现
```javascript
// 增强 dataProcessor 的缓存机制
class EnhancedDataProcessor extends DataProcessor {
  constructor() {
    super()
    this.memoryCache = new Map()
    this.diskCache = {
      getItem: (key) => uni.getStorageSync(key),
      setItem: (key, value) => uni.setStorageSync(key, value),
      removeItem: (key) => uni.removeStorageSync(key)
    }
  }
  
  // 缓存题目数据
  cacheQuestionData(question) {
    const cacheKey = `question_${question.uid}`
    this.memoryCache.set(cacheKey, question)
    
    // 异步写入磁盘缓存
    setTimeout(() => {
      try {
        this.diskCache.setItem(cacheKey, question)
      } catch (error) {
        console.error('缓存题目数据失败:', error)
      }
    }, 0)
  }
  
  // 获取缓存的题目数据
  getCachedQuestionData(questionUid) {
    const cacheKey = `question_${questionUid}`
    
    // 优先从内存缓存获取
    if (this.memoryCache.has(cacheKey)) {
      return this.memoryCache.get(cacheKey)
    }
    
    // 从磁盘缓存获取
    try {
      const cachedData = this.diskCache.getItem(cacheKey)
      if (cachedData) {
        this.memoryCache.set(cacheKey, cachedData)
        return cachedData
      }
    } catch (error) {
      console.error('读取缓存题目数据失败:', error)
    }
    
    return null
  }
}
```

#### 预加载策略实现
```javascript
// 预加载题目
preloadQuestions(currentIndex) {
  // 预加载当前索引前后的题目
  const preloadRange = 2 // 预加载范围
  const start = Math.max(0, currentIndex - preloadRange)
  const end = Math.min(this.totalQuestions - 1, currentIndex + preloadRange)
  
  // 检查哪些题目需要预加载
  for (let i = start; i <= end; i++) {
    if (!this.isQuestionLoaded(i)) {
      this.loadQuestionChunk(Math.floor(i / this.chunkSize), this.chunkSize)
      break // 每次只加载一个chunk
    }
  }
}

// 检查题目是否已加载
isQuestionLoaded(index) {
  return index < this.loadedQuestions.length
}
```

### 3.3 预期效果

- 初始加载时间减少 60% 以上
- 内存消耗减少 50% 以上
- 滑动切换题目更加流畅
- 支持更大规模的题目列表（如100+题）
- 提升低端设备的运行性能

## 4. 改造风险与应对措施

### 4.1 风险
- 改造复杂度较高，可能影响现有功能
- 动态DOM管理可能导致用户体验问题
- 缓存机制可能导致数据不一致

### 4.2 应对措施
- 分阶段实施，先实现核心功能，再优化细节
- 充分测试，确保改造后功能正常
- 实现缓存失效机制，确保数据一致性
- 保留原有的完整加载模式作为 fallback

## 5. 改造步骤

1. **准备阶段**：分析现有代码，制定详细改造计划
2. **核心改造**：实现分块加载和动态DOM管理
3. **缓存增强**：实现题目数据缓存和预加载
4. **性能优化**：优化内存管理和加载策略
5. **测试验证**：充分测试各种场景，确保功能正常
6. **部署上线**：逐步推广改造后的版本

通过以上改造，commonQuestion.vue 将能够更高效地处理大规模题目列表，提供更流畅的用户体验，同时减少内存消耗和加载时间，而无需依赖虚拟列表技术。