/**
 * 性能优化工具类
 * 基于微信小程序性能优化最佳实践
 */

/**
 * 防抖函数
 * 用于减少高频事件触发次数（如搜索输入、滚动事件）
 * @param {Function} func - 要执行的函数
 * @param {Number} delay - 延迟时间（毫秒）
 * @returns {Function} - 防抖后的函数
 */
export const debounce = (func, delay = 300) => {
  let timer = null
  return function(...args) {
    if (timer) clearTimeout(timer)
    timer = setTimeout(() => {
      func.apply(this, args)
    }, delay)
  }
}

/**
 * 节流函数
 * 用于限制函数执行频率（如滚动加载、按钮点击）
 * @param {Function} func - 要执行的函数
 * @param {Number} delay - 间隔时间（毫秒）
 * @returns {Function} - 节流后的函数
 */
export const throttle = (func, delay = 300) => {
  let lastTime = 0
  return function(...args) {
    const now = Date.now()
    if (now - lastTime >= delay) {
      lastTime = now
      func.apply(this, args)
    }
  }
}

/**
 * 图片懒加载管理器
 * 用于优化图片加载性能
 */
export class ImageLazyLoader {
  constructor() {
    this.observer = null
    this.images = []
  }

  /**
   * 初始化懒加载
   * @param {Object} options - 配置选项
   */
  init(options = {}) {
    const {
      threshold = 100, // 提前加载距离（px）
      placeholder = '/static/placeholder.png' // 占位图
    } = options

    this.threshold = threshold
    this.placeholder = placeholder
  }

  /**
   * 添加需要懒加载的图片
   * @param {String} selector - 图片选择器
   */
  observe(selector) {
    // 小程序使用 IntersectionObserver
    this.observer = uni.createIntersectionObserver()
    this.observer
      .relativeToViewport({ bottom: this.threshold })
      .observe(selector, (res) => {
        if (res.intersectionRatio > 0) {
          // 图片进入可视区域，加载真实图片
          this.loadImage(res.id)
        }
      })
  }

  /**
   * 加载图片
   * @param {String} id - 图片ID
   */
  loadImage(id) {
    // 实现图片加载逻辑
    // 在实际使用中，需要配合组件数据更新
  }

  /**
   * 销毁观察器
   */
  destroy() {
    if (this.observer) {
      this.observer.disconnect()
      this.observer = null
    }
  }
}

/**
 * 数据缓存管理器
 * 用于优化频繁的数据请求
 */
export class DataCache {
  constructor() {
    this.cache = new Map()
    this.expireTime = new Map()
    this.persistKeys = new Set() // 需要持久化的key
  }

  /**
   * 设置缓存
   * @param {String} key - 缓存键
   * @param {Any} value - 缓存值
   * @param {Number} expire - 过期时间（毫秒），默认5分钟
   * @param {Boolean} persist - 是否持久化到本地存储
   */
  set(key, value, expire = 5 * 60 * 1000, persist = false) {
    this.cache.set(key, value)
    this.expireTime.set(key, Date.now() + expire)
    
    // 如果需要持久化
    if (persist) {
      this.persistKeys.add(key)
      try {
        uni.setStorageSync(`cache_${key}`, {
          value,
          expireAt: Date.now() + expire
        })
      } catch (e) {
        console.error('缓存持久化失败:', e)
      }
    }
  }

  /**
   * 获取缓存
   * @param {String} key - 缓存键
   * @param {Boolean} checkStorage - 是否检查本地存储
   * @returns {Any} - 缓存值，如果过期或不存在返回null
   */
  get(key, checkStorage = true) {
    // 先检查内存缓存
    if (this.cache.has(key)) {
      const expireAt = this.expireTime.get(key)
      if (Date.now() <= expireAt) {
        return this.cache.get(key)
      }
      // 内存缓存过期
      this.delete(key)
    }
    
    // 检查本地存储
    if (checkStorage) {
      try {
        const stored = uni.getStorageSync(`cache_${key}`)
        if (stored && stored.expireAt > Date.now()) {
          // 恢复到内存缓存
          this.cache.set(key, stored.value)
          this.expireTime.set(key, stored.expireAt)
          return stored.value
        } else if (stored) {
          // 本地缓存过期
          uni.removeStorageSync(`cache_${key}`)
        }
      } catch (e) {
        console.error('读取缓存失败:', e)
      }
    }
    
    return null
  }

  /**
   * 删除缓存
   * @param {String} key - 缓存键
   */
  delete(key) {
    this.cache.delete(key)
    this.expireTime.delete(key)
    this.persistKeys.delete(key)
    
    try {
      uni.removeStorageSync(`cache_${key}`)
    } catch (e) {
      console.error('删除缓存失败:', e)
    }
  }

  /**
   * 清空所有缓存
   */
  clear() {
    this.cache.clear()
    this.expireTime.clear()
    
    // 清理持久化缓存
    for (const key of this.persistKeys) {
      try {
        uni.removeStorageSync(`cache_${key}`)
      } catch (e) {
        console.error('清理缓存失败:', e)
      }
    }
    this.persistKeys.clear()
  }

  /**
   * 清理过期缓存
   */
  clearExpired() {
    const now = Date.now()
    for (const [key, expireAt] of this.expireTime.entries()) {
      if (now > expireAt) {
        this.delete(key)
      }
    }
  }
  
  /**
   * 获取或设置缓存（带自动请求）
   * @param {String} key - 缓存键
   * @param {Function} fetchFunc - 数据获取函数
   * @param {Number} expire - 过期时间
   * @param {Boolean} persist - 是否持久化
   * @returns {Promise<Any>}
   */
  async getOrFetch(key, fetchFunc, expire = 5 * 60 * 1000, persist = false) {
    // 先尝试从缓存获取
    const cached = this.get(key)
    if (cached !== null) {
      return cached
    }
    
    // 缓存不存在，请求数据
    try {
      const data = await fetchFunc()
      this.set(key, data, expire, persist)
      return data
    } catch (error) {
      console.error(`获取数据失败 [${key}]:`, error)
      throw error
    }
  }
}

/**
 * 列表数据分页加载管理器
 * 用于优化长列表性能
 */
export class ListPagination {
  constructor(options = {}) {
    this.pageSize = options.pageSize || 20
    this.currentPage = 1
    this.hasMore = true
    this.loading = false
    this.list = []
  }

  /**
   * 加载下一页
   * @param {Function} fetchFunc - 数据获取函数
   * @returns {Promise}
   */
  async loadMore(fetchFunc) {
    if (this.loading || !this.hasMore) {
      return
    }

    this.loading = true
    try {
      const result = await fetchFunc(this.currentPage, this.pageSize)
      
      if (result && result.data) {
        const newData = result.data
        this.list = this.list.concat(newData)
        this.currentPage++
        
        // 判断是否还有更多数据
        if (newData.length < this.pageSize) {
          this.hasMore = false
        }
      } else {
        this.hasMore = false
      }
    } catch (error) {
      console.error('加载数据失败:', error)
    } finally {
      this.loading = false
    }

    return this.list
  }

  /**
   * 重置分页
   */
  reset() {
    this.currentPage = 1
    this.hasMore = true
    this.loading = false
    this.list = []
  }
}

/**
 * setData 优化器
 * 用于批量更新数据，减少渲染次数
 */
export class SetDataOptimizer {
  constructor(ctx) {
    this.ctx = ctx
    this.pendingData = {}
    this.timer = null
  }

  /**
   * 添加待更新的数据
   * @param {Object} data - 数据对象
   */
  add(data) {
    Object.assign(this.pendingData, data)
    
    // 延迟执行，收集多次更新
    if (this.timer) {
      clearTimeout(this.timer)
    }
    
    this.timer = setTimeout(() => {
      this.flush()
    }, 16) // 一帧时间
  }

  /**
   * 立即执行所有待更新的数据
   */
  flush() {
    if (Object.keys(this.pendingData).length > 0) {
      // Vue 使用 this.$set 或直接赋值
      Object.keys(this.pendingData).forEach(key => {
        this.ctx[key] = this.pendingData[key]
      })
      this.pendingData = {}
    }
    
    if (this.timer) {
      clearTimeout(this.timer)
      this.timer = null
    }
  }
}

/**
 * 性能监控工具
 * 用于监控页面性能指标
 */
export class PerformanceMonitor {
  constructor() {
    this.marks = new Map()
  }

  /**
   * 标记开始时间
   * @param {String} name - 标记名称
   */
  start(name) {
    this.marks.set(name, Date.now())
  }

  /**
   * 标记结束时间并输出耗时
   * @param {String} name - 标记名称
   * @returns {Number} - 耗时（毫秒）
   */
  end(name) {
    if (!this.marks.has(name)) {
      console.warn(`未找到标记: ${name}`)
      return 0
    }

    const startTime = this.marks.get(name)
    const duration = Date.now() - startTime
    
    this.marks.delete(name)
    
    return duration
  }

  /**
   * 清除所有标记
   */
  clear() {
    this.marks.clear()
  }
}

/**
 * 请求合并工具
 * 用于合并相同的请求，避免重复请求
 */
export class RequestMerger {
  constructor() {
    this.pending = new Map()
  }

  /**
   * 执行请求（自动合并相同请求）
   * @param {String} key - 请求唯一标识
   * @param {Function} requestFunc - 请求函数
   * @returns {Promise}
   */
  async request(key, requestFunc) {
    // 如果已有相同请求正在进行，直接返回该请求的 Promise
    if (this.pending.has(key)) {
      return this.pending.get(key)
    }

    // 创建新请求
    const promise = requestFunc()
      .then(result => {
        this.pending.delete(key)
        return result
      })
      .catch(error => {
        this.pending.delete(key)
        throw error
      })

    this.pending.set(key, promise)
    return promise
  }

  /**
   * 清除所有待处理请求
   */
  clear() {
    this.pending.clear()
  }
}

// 创建单例实例
export const dataCache = new DataCache()
export const performanceMonitor = new PerformanceMonitor()
export const requestMerger = new RequestMerger()

// 默认导出
export default {
  debounce,
  throttle,
  ImageLazyLoader,
  DataCache,
  ListPagination,
  SetDataOptimizer,
  PerformanceMonitor,
  RequestMerger,
  dataCache,
  performanceMonitor,
  requestMerger
}
