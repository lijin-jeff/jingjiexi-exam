/**
 * API 缓存管理工具
 * 统一管理所有 API 请求的缓存策略
 */

import { dataCache } from './performance.js'

/**
 * 缓存配置
 */
const CACHE_CONFIG = {
  // 题库列表 - 缓存10分钟
  questionLibList: {
    expire: 10 * 60 * 1000,
    persist: true
  },
  
  // 轮播图 - 缓存30分钟
  banner: {
    expire: 30 * 60 * 1000,
    persist: true
  },
  
  // 系统配置 - 缓存1小时
  sysConfig: {
    expire: 60 * 60 * 1000,
    persist: true
  },
  
  // 用户信息 - 缓存5分钟
  userInfo: {
    expire: 5 * 60 * 1000,
    persist: false
  },
  
  // 考试历史 - 缓存2分钟
  examHistory: {
    expire: 2 * 60 * 1000,
    persist: false
  },
  
  // 题目详情 - 缓存10分钟
  questionDetail: {
    expire: 10 * 60 * 1000,
    persist: true
  },
  
  // 章节列表 - 缓存15分钟
  chapterList: {
    expire: 15 * 60 * 1000,
    persist: true
  },
  
  // 统计数据 - 不缓存（实时数据）
  statistics: {
    expire: 0,
    persist: false
  }
}

/**
 * API 缓存封装类
 */
export class ApiCache {
  /**
   * 获取题库列表（带缓存）
   * @param {Function} apiFunc - API 请求函数
   * @param {Object} params - 请求参数
   * @returns {Promise}
   */
  static async getQuestionLibList(apiFunc, params = {}) {
    const key = `questionLibList_${JSON.stringify(params)}`
    const config = CACHE_CONFIG.questionLibList
    
    return dataCache.getOrFetch(
      key,
      () => apiFunc(params),
      config.expire,
      config.persist
    )
  }
  
  /**
   * 获取轮播图（带缓存）
   * @param {Function} apiFunc - API 请求函数
   * @param {Object} params - 请求参数
   * @returns {Promise}
   */
  static async getBanner(apiFunc, params = {}) {
    const key = `banner_${JSON.stringify(params)}`
    const config = CACHE_CONFIG.banner
    
    return dataCache.getOrFetch(
      key,
      () => apiFunc(params),
      config.expire,
      config.persist
    )
  }
  
  /**
   * 获取系统配置（带缓存）
   * @param {Function} apiFunc - API 请求函数
   * @returns {Promise}
   */
  static async getSysConfig(apiFunc) {
    const key = 'sysConfig'
    const config = CACHE_CONFIG.sysConfig
    
    // 直接请求数据，不使用缓存，解决sysConfig请求不发出的问题
    try {
      const data = await apiFunc()
      // 更新缓存
      dataCache.set(key, data, config.expire, config.persist)
      return data
    } catch (error) {
      console.error(`获取系统配置失败 [${key}]:`, error)
      // 如果请求失败，尝试从缓存获取
      const cached = dataCache.get(key)
      if (cached !== null) {
        return cached
      }
      throw error
    }
  }
  
  /**
   * 获取用户信息（带缓存）
   * @param {Function} apiFunc - API 请求函数
   * @returns {Promise}
   */
  static async getUserInfo(apiFunc) {
    const key = 'userInfo'
    const config = CACHE_CONFIG.userInfo
    
    return dataCache.getOrFetch(
      key,
      apiFunc,
      config.expire,
      config.persist
    )
  }
  
  /**
   * 获取考试历史（带缓存）
   * @param {Function} apiFunc - API 请求函数
   * @param {Number} page - 页码
   * @param {Number} limit - 每页数量
   * @returns {Promise}
   */
  static async getExamHistory(apiFunc, page = 1, limit = 20) {
    const key = `examHistory_${page}_${limit}`
    const config = CACHE_CONFIG.examHistory
    
    return dataCache.getOrFetch(
      key,
      () => apiFunc({ page, limit }),
      config.expire,
      config.persist
    )
  }
  
  /**
   * 获取题目详情（带缓存）
   * @param {Function} apiFunc - API 请求函数
   * @param {Number} questionId - 题目ID
   * @returns {Promise}
   */
  static async getQuestionDetail(apiFunc, questionId) {
    const key = `questionDetail_${questionId}`
    const config = CACHE_CONFIG.questionDetail
    
    return dataCache.getOrFetch(
      key,
      () => apiFunc(questionId),
      config.expire,
      config.persist
    )
  }
  
  /**
   * 获取章节列表（带缓存）
   * @param {Function} apiFunc - API 请求函数
   * @param {Object} params - 请求参数
   * @returns {Promise}
   */
  static async getChapterList(apiFunc, params = {}) {
    const key = `chapterList_${JSON.stringify(params)}`
    const config = CACHE_CONFIG.chapterList
    
    return dataCache.getOrFetch(
      key,
      () => apiFunc(params),
      config.expire,
      config.persist
    )
  }
  
  /**
   * 清除指定类型的缓存
   * @param {String} type - 缓存类型
   */
  static clearCache(type) {
    switch (type) {
      case 'questionLib':
        // 清除所有题库相关缓存
        dataCache.cache.forEach((value, key) => {
          if (key.startsWith('questionLibList_')) {
            dataCache.delete(key)
          }
        })
        break
      
      case 'user':
        // 清除用户相关缓存
        dataCache.delete('userInfo')
        break
      
      case 'exam':
        // 清除考试历史缓存
        dataCache.cache.forEach((value, key) => {
          if (key.startsWith('examHistory_')) {
            dataCache.delete(key)
          }
        })
        break
      
      case 'all':
        // 清除所有缓存
        dataCache.clear()
        break
    }
  }
  
  /**
   * 清除过期缓存
   */
  static clearExpired() {
    dataCache.clearExpired()
  }
}

// 默认导出
export default ApiCache
