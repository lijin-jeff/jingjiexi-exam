/**
 * 题型管理工具类
 * 统一管理题型数据，避免硬编码
 */

// 题型缓存，避免重复请求
let questionTypeCache = null
let cacheLibraryUid = null

/**
 * 获取题型列表
 * @param {Object} api - API实例
 * @param {String} libraryUid - 题库UID
 * @param {Boolean} forceRefresh - 是否强制刷新缓存
 * @returns {Promise<Array>} 题型列表
 */
export async function getQuestionTypes(api, libraryUid, forceRefresh = false) {
  try {
    // 如果有缓存且题库UID相同，且不强制刷新，直接返回缓存
    if (!forceRefresh && questionTypeCache && cacheLibraryUid === libraryUid) {
      return questionTypeCache
    }

    // 调用API获取题型列表
    const res = await api.apiQuestionTypeList({ uid: libraryUid })
    
    if (res && res.data && res.data.exam_type_list) {
      questionTypeCache = res.data.exam_type_list
      cacheLibraryUid = libraryUid
      return questionTypeCache
    }
    
    // 如果API返回空，使用默认题型
    return getDefaultQuestionTypes()
  } catch (error) {
    console.error('获取题型列表失败:', error)
    // 返回默认题型作为后备
    return getDefaultQuestionTypes()
  }
}

/**
 * 根据题型值获取题型名称
 * @param {String|Number} typeValue - 题型值（1-6）
 * @param {Array} questionTypes - 题型列表（可选）
 * @returns {String} 题型名称
 */
export function getQuestionTypeName(typeValue, questionTypes = null) {
  const type = String(typeValue)
  
  // 如果提供了题型列表，从列表中查找
  if (questionTypes && Array.isArray(questionTypes)) {
    const found = questionTypes.find(item => String(item.value) === type)
    if (found) return found.name
  }
  
  // 使用缓存的题型列表
  if (questionTypeCache) {
    const found = questionTypeCache.find(item => String(item.value) === type)
    if (found) return found.name
  }
  
  // 后备方案：使用默认映射
  const defaultMap = {
    '1': '单选题',
    '2': '多选题',
    '3': '判断题',
    '4': '填空题',
    '5': '问答题',
    '6': '案例题'
  }
  
  return defaultMap[type] || `题型${type}`
}

/**
 * 根据题型名称获取题型值
 * @param {String} typeName - 题型名称
 * @param {Array} questionTypes - 题型列表（可选）
 * @returns {String} 题型值
 */
export function getQuestionTypeValue(typeName, questionTypes = null) {
  // 如果提供了题型列表，从列表中查找
  if (questionTypes && Array.isArray(questionTypes)) {
    const found = questionTypes.find(item => item.name === typeName)
    if (found) return String(found.value)
  }
  
  // 使用缓存的题型列表
  if (questionTypeCache) {
    const found = questionTypeCache.find(item => item.name === typeName)
    if (found) return String(found.value)
  }
  
  // 后备方案：使用默认映射
  const defaultMap = {
    '单选题': '1',
    '多选题': '2',
    '判断题': '3',
    '填空题': '4',
    '问答题': '5',
    '案例题': '6'
  }
  
  return defaultMap[typeName] || ''
}

/**
 * 获取默认题型列表（后备方案）
 * @returns {Array} 默认题型列表
 */
function getDefaultQuestionTypes() {
  return [
    { name: '单选题', value: '1' },
    { name: '多选题', value: '2' },
    { name: '判断题', value: '3' },
    { name: '填空题', value: '4' },
    { name: '问答题', value: '5' },
    { name: '案例题', value: '6' }
  ]
}

/**
 * 清除题型缓存
 */
export function clearQuestionTypeCache() {
  questionTypeCache = null
  cacheLibraryUid = null
}

/**
 * 预加载题型数据
 * @param {Object} api - API实例
 * @param {String} libraryUid - 题库UID
 */
export async function preloadQuestionTypes(api, libraryUid) {
  await getQuestionTypes(api, libraryUid, true)
}

export default {
  getQuestionTypes,
  getQuestionTypeName,
  getQuestionTypeValue,
  clearQuestionTypeCache,
  preloadQuestionTypes
}
