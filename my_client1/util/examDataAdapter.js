/**
 * 答题系统数据适配器
 * 统一处理后端返回的答题数据，减少重复代码
 * @module ExamDataAdapter
 */

// @ts-check
/// <reference path="../typings/exam.d.ts" />

/**
 * @typedef {import('../typings/exam.d.ts').QuestionOption} QuestionOption
 * @typedef {import('../typings/exam.d.ts').Question} Question
 * @typedef {import('../typings/exam.d.ts').UserAnswer} UserAnswer
 * @typedef {import('../typings/exam.d.ts').ProcessedQuestion} ProcessedQuestion
 * @typedef {import('../typings/exam.d.ts').ExamHistory} ExamHistory
 * @typedef {import('../typings/exam.d.ts').Statistics} Statistics
 * @typedef {import('../typings/exam.d.ts').AnswerCheckResult} AnswerCheckResult
 */

/**
 * 题型映射表
 * @type {Record<number, string>}
 */
const EXAM_TYPE_MAP = {
  1: '判断题',
  2: '单选题',
  3: '多选题',
  4: '填空题',
  5: '问答题',
  6: '案例题'
};

/**
 * 错误日志记录器
 * @param {string} context - 错误上下文
 * @param {Error|string} error - 错误信息
 * @param {Record<string, any>} [extraData={}] - 额外数据
 * @returns {void}
 */
function logError(context, error, extraData = {}) {
  const timestamp = new Date().toISOString()
  const errorMessage = error instanceof Error ? error.message : String(error)
  const errorStack = error instanceof Error ? error.stack : ''
  
  console.error(`[ExamDataAdapter] ${timestamp}`, {
    context,
    error: errorMessage,
    stack: errorStack,
    ...extraData
  })
  
  // 可选：上报错误到监控系统
  // if (typeof uni !== 'undefined' && uni.$errorTracker) {
  //   uni.$errorTracker.report({
  //     module: 'ExamDataAdapter',
  //     context,
  //     error: errorMessage,
  //     ...extraData
  //   })
  // }
}

/**
 * 统一错误提示
 * @param {string} message - 错误消息
 * @param {number} [duration=2000] - 显示时长(毫秒)
 * @returns {void}
 */
function showErrorToast(message, duration = 2000) {
  // 使用类型断言解决uni未定义的问题
  // @ts-ignore: 忽略uni未定义的错误，在小程序环境中会自动可用
  if (typeof uni !== 'undefined') {
    // @ts-ignore: 忽略uni.showToast的类型检查
    uni.showToast({
      title: message,
      icon: 'none',
      duration: duration
    })
  } else if (typeof console !== 'undefined') {
    console.warn('[Toast]', message)
  }
}

/**
 * 递归解析JSON,处理多重编码
 * @template T
 * @param {T} data - 需要解析的数据
 * @param {number} [maxAttempts=3] - 最大尝试次数
 * @returns {T|any} 解析后的数据
 */
function recursiveParseJson(data, maxAttempts = 3) {
  try {
    let parsed = data
    for (let i = 0; i < maxAttempts; i++) {
      if (typeof parsed !== 'string') break
      try {
        parsed = JSON.parse(parsed)
      } catch (e) {
        // 单次解析失败，停止尝试
        break
      }
    }
    return parsed
  } catch (error) {
    logError('recursiveParseJson', /** @type {Error} */ (error), { data })
    return data // 返回原始数据
  }
}

/**
 * 统一时间戳为 13 位(毫秒)
 * @param {number|string|undefined|null} timestamp - 时间戳(可能为 10 位或 13 位)
 * @returns {number} 13 位时间戳(毫秒)
 */
function normalizeTimestamp(timestamp) {
  try {
    if (!timestamp) return Date.now()
    
    // 如果是字符串，尝试转换
    if (typeof timestamp === 'string') {
      // 尝试解析日期字符串
      const date = new Date(timestamp)
      if (!isNaN(date.getTime())) {
        return date.getTime()
      }
      
      // 尝试转换为数字
      const numTimestamp = parseFloat(timestamp)
      if (!isNaN(numTimestamp)) {
        timestamp = numTimestamp
      } else {
        logError('normalizeTimestamp', '无法解析的时间字符串', { timestamp })
        return Date.now()
      }
    }
    
    timestamp = typeof timestamp === 'number' ? Math.floor(timestamp) : parseInt(String(timestamp))
    
    // 10 位时间戳（秒），转换为 13 位（毫秒）
    if (timestamp < 10000000000) {
      return timestamp * 1000
    }
    
    // 已经是 13 位，直接返回
    return timestamp
  } catch (error) {
    logError('normalizeTimestamp', /** @type {Error} */ (error), { timestamp })
    return Date.now()
  }
}

/**
 * 统一答案格式为数组
 * @param {string|string[]|any} answer - 答案(可能为字符串、数组或 JSON)
 * @returns {string[]} 答案数组
 */
function normalizeAnswer(answer) {
  try {
    if (!answer) return []
    
    // 如果已经是数组
    if (Array.isArray(answer)) {
      return answer.filter(v => v !== null && v !== '')
    }
    
    // 如果是字符串
    if (typeof answer === 'string') {
      // 尝试解析 JSON
      try {
        const parsed = recursiveParseJson(answer)
        if (Array.isArray(parsed)) {
          return parsed.filter(v => v !== null && v !== '')
        }
      } catch (e) {
        // 解析失败，继续
      }
      
      // 处理逗号分隔的字符串
      return answer.split(',')
        .map(v => v.trim())
        .filter(v => v !== '')
    }
    
    // 其他类型转为数组
    return [answer]
  } catch (error) {
    logError('normalizeAnswer', /** @type {Error} */ (error), { answer })
    return []
  }
}

/**
 * 检查答案是否正确
 * @param {Question} question - 题目数据
 * @param {UserAnswer} userAnswer - 用户答案数据
 * @returns {{isCorrect: boolean, isHalfCorrect: boolean}} 答案正确性结果
 */
function checkAnswer(question, userAnswer) {
  // 优先使用后端返回的布尔值
  if (typeof userAnswer.is_correct === 'boolean') {
    return {
      isCorrect: userAnswer.is_correct,
      isHalfCorrect: userAnswer.is_half_correct || false
    }
  }
  
  // 降级方案：手动对比
  const userAns = normalizeAnswer(userAnswer.user_answer).sort()
  const correctAns = normalizeAnswer(question.answer).sort()
  
  const isCorrect = JSON.stringify(userAns) === JSON.stringify(correctAns)
  
  // 判断多选题半对状态
  let isHalfCorrect = false
  if (question.exam_type === 2 && userAns.length > 0 && correctAns.length > 0 && !isCorrect) {
    const correctSelections = userAns.filter(ans => correctAns.includes(ans)).length
    const wrongSelections = userAns.filter(ans => !correctAns.includes(ans)).length
    
    // 半对条件：有正确选择，但不是全对
    if (correctSelections > 0 && (wrongSelections > 0 || correctSelections < correctAns.length)) {
      isHalfCorrect = true
    }
  }
  
  return { isCorrect, isHalfCorrect }
}

/**
 * 处理选项数据
 * @param {QuestionOption[]|any} options - 选项数组
 * @param {string[]} correctAnswer - 正确答案数组
 * @param {UserAnswer} userAnswer - 用户答案对象
 * @returns {QuestionOption[]} 处理后的选项数组
 */
function processOptions(options, correctAnswer, userAnswer) {
  if (!Array.isArray(options)) return []
  
  const correctAns = normalizeAnswer(correctAnswer)
  const userAns = normalizeAnswer(userAnswer.user_answer)
  
  return options.map((opt, idx) => {
    // 检查是否是正确答案
    const isCorrect = correctAns.includes(opt.check) || correctAns.includes(String(opt.check))
    
    // 检查是否被用户选择
    let isSelected = userAns.includes(opt.check) || userAns.includes(String(opt.check))
    
    // 兼容其他选择状态字段
    isSelected = isSelected || 
                opt.is_selected || 
                opt.status === 'selected' ||
                (userAnswer.option && userAnswer.option[idx] && userAnswer.option[idx].is_selected)
    
    return {
      check: opt.check,
      title: opt.title || '',
      content: opt.title || '',
      is_correct: isCorrect,
      is_selected: isSelected
    }
  })
}

/**
 * 处理案例题子试题
 * @param {Array<any>} caseOptions - 案例题选项数组
 * @param {UserAnswer} userAnswer - 用户答案对象
 * @param {Array<string>} correctAnswers - 正确答案数组（可选）
 * @returns {Array<any>} 处理后的子试题数组
 */
function processCaseSubQuestions(caseOptions, userAnswer, correctAnswers = []) {
  try {
    if (!Array.isArray(caseOptions)) {
      logError('processCaseSubQuestions', '案例选项不是数组', { caseOptions })
      return []
    }
  
  return caseOptions.map((caseOpt, caseIdx) => {
    if (!caseOpt.children || !Array.isArray(caseOpt.children)) {
      return null
    }
    
    // 解析子试题的用户答案
    let subUserAnswer = '-'
    let subIsCorrect = false
    
    // ✅ 优先从 option 中获取用户答案（查询题目时）
    if (caseOpt.selectedAnswer) {
      // 单选题、判断题
      subUserAnswer = caseOpt.selectedAnswer
    } else if (caseOpt.selectedAnswers && Array.isArray(caseOpt.selectedAnswers) && caseOpt.selectedAnswers.length > 0) {
      // 多选题
      subUserAnswer = caseOpt.selectedAnswers.join(',')
    } else if (caseOpt.user_answer) {
      // 填空题、问答题
      subUserAnswer = caseOpt.user_answer
    } else if (userAnswer.user_answer) {
      // ✅ 降级方案：从 userAnswer.user_answer 获取（答题历史时）
      try {
        const parsedUserAnswer = recursiveParseJson(userAnswer.user_answer)
        // ✅ 将答案数组转换为字符串
        const answerData = parsedUserAnswer[caseIdx]?.answer
        if (Array.isArray(answerData) && answerData.length > 0) {
          subUserAnswer = answerData.join(',')
        } else if (answerData) {
          subUserAnswer = String(answerData)
        }
      } catch (e) {
        logError('processCaseSubQuestions - 解析user_answer', /** @type {Error} */ (e), { caseIdx })
      }
    }
    
    // ✅ 检查子试题的正确性：优先使用后端返回的is_correct
    if (typeof userAnswer.is_correct === 'boolean') {
      // 案例题主题的is_correct，所有子题继承
      subIsCorrect = userAnswer.is_correct
    } else if (userAnswer.option && userAnswer.option[caseIdx]) {
      // 兼容旧版本：检查option中的标记
      const optionData = userAnswer.option[caseIdx]
      subIsCorrect = optionData.is_correct || 
                    optionData.is_user_correct || 
                    !optionData.is_user_wrong
    }
    
    // ✅ 生成子试题的正确答案
    let subCorrectAnswer = ''
    
    // ✅ 优先使用传入的correctAnswers数组（从案例题主题的answer字段解析）
    if (correctAnswers && correctAnswers[caseIdx]) {
      subCorrectAnswer = correctAnswers[caseIdx]
    } else if (caseOpt.judeAnswer) {
      // 判断题的正确答案
      subCorrectAnswer = caseOpt.judeAnswer
    } else if (caseOpt.answerContent) {
      // 填空题/问答题的答案内容（HTML格式）
      subCorrectAnswer = caseOpt.answerContent
    } else if (caseOpt.children) {
      // 从子选项中提取正确答案
      const correctOptions = caseOpt.children.filter((/** @type {{ is_check?: string; is_correct?: boolean; }} */ child) => 
        child.is_check === '1' || child.is_correct
      )
      subCorrectAnswer = correctOptions.map((/** @type {{ check: string; }} */ child) => child.check).join(',')
    }
    
    const result = {
      title: caseOpt.title || '',
      children: caseOpt.children || [],
      user_answer: subUserAnswer,
      correct_answer: subCorrectAnswer,
      is_correct: subIsCorrect,
      exam_type: parseInt(caseOpt.exam_type || '1'),
      score: parseFloat(caseOpt.childrenScore || '0'),
      integral: parseFloat(caseOpt.integral || '0'),
      case_index: caseIdx + 1
    }
    
    return result
  }).filter(Boolean)
  } catch (error) {
    logError('processCaseSubQuestions', /** @type {Error} */ (error), { caseOptionsCount: caseOptions?.length })
    return []
  }
}

/**
 * 处理题目列表数据
 * @param {Question[]} questions - 题目列表
 * @param {UserAnswer[]} [userAnswers=[]] - 用户答案列表
 * @returns {ProcessedQuestion[]} 处理后的题目列表
 */
function processQuestionList(questions, userAnswers) {
  try {
    if (!Array.isArray(questions)) {
      logError('processQuestionList', '题目数据不是数组', { questions })
      showErrorToast('题目数据格式错误')
      return []
    }
    
    if (questions.length === 0) {
      console.warn('[ExamDataAdapter] 题目列表为空')
      return []
    }
    
    // 创建用户答案映射
    /** @type {Record<string, any>} */
    const userAnswersMap = {}
    if (Array.isArray(userAnswers)) {
      userAnswers.forEach((/** @type {{ uid?: string; }} */ answer) => {
        if (answer && answer.uid) {
          userAnswersMap[answer.uid] = answer
        }
      })
    } else {
      console.warn('[ExamDataAdapter] 用户答案不是数组，将使用空映射')
    }
    
    return questions.map((q, index) => {
      try {
        const currentUserAnswer = userAnswersMap[q.uid] || {}
    
    // 初始化变量
    /** @type {any[]} */
    let options = []
    let correctAnswer = ''
    let userAnswer = '-' 
    let subQuestions = []
    
    // 解析选项数据（可能是字符串或数组）
    let parsedOptions = []
    // ✅ 兼容两种字段名：option 和 options
    const optionField = q.option || q.options
    if (optionField) {
      if (typeof optionField === 'string') {
        try {
          parsedOptions = JSON.parse(optionField)
        } catch (e) {
          console.error('解析选项失败:', e)
        }
      } else if (Array.isArray(optionField)) {
        parsedOptions = optionField
      }
    }
    
    // 检查是否是案例题
    const isCaseQuestion = q.exam_type === 6 && 
                          parsedOptions && 
                          Array.isArray(parsedOptions) &&
                          parsedOptions.some(opt => opt.children && Array.isArray(opt.children))
    
    if (isCaseQuestion) {
      // ✅ 处理案例题：解析正确答案数组
      const caseCorrectAnswers = normalizeAnswer(q.answer)  // 解析 answer 字段
      subQuestions = processCaseSubQuestions(parsedOptions, currentUserAnswer, caseCorrectAnswers)
      
      // ✅ 为案例题设置 user_answer：如果有任何子题作答，则认为已作答
      const hasAnswered = subQuestions.some(sub => sub && sub.user_answer && sub.user_answer !== '-')
      userAnswer = hasAnswered ? '已作答' : '-'
      
      // ✅ 汇总正确答案（可选）
      if (caseCorrectAnswers.length > 0) {
        correctAnswer = caseCorrectAnswers.join(',')
      }
    } else if (parsedOptions && Array.isArray(parsedOptions)) {
      // 处理普通题目
      const answerArray = normalizeAnswer(q.answer)
      correctAnswer = answerArray.join(',')
      
      // 处理选项
      options = processOptions(parsedOptions, answerArray, currentUserAnswer)
      
      // 获取用户答案
      const userAns = normalizeAnswer(currentUserAnswer.user_answer)
      userAnswer = userAns.length > 0 ? userAns.join(',') : '-'
      
      // ✅ 问答题（exam_type === 5）特殊处理：只要有内容就认为已作答
      if (q.exam_type === 5 && currentUserAnswer.user_answer) {
        const rawAnswer = String(currentUserAnswer.user_answer || '').trim()
        if (rawAnswer && rawAnswer !== '-' && rawAnswer !== '[]' && rawAnswer !== '""') {
          userAnswer = '已作答'
        }
      }
      
      // 如果从 user_answer 获取失败，尝试从 option 中获取
      if (userAnswer === '-' && currentUserAnswer.option) {
        const selectedOptions = currentUserAnswer.option.filter((/** @type {{ is_selected?: boolean; status?: string; }} */ opt) => 
          opt.is_selected || opt.status === 'selected'
        )
        if (selectedOptions.length > 0) {
          userAnswer = selectedOptions.map((/** @type {{ check: string; }} */ opt) => opt.check).join(',')
        }
      }
    }
    
    // 检查答案正确性
    const { isCorrect, isHalfCorrect } = checkAnswer(q, currentUserAnswer)
    
    return {
      uid: q.uid, // 保留原始题目ID
      title: q.title || '',
      options: options,
      correct_answer: correctAnswer,
      user_answer: userAnswer,
      analysis: q.analysis || q.commentaries || '',
      is_correct: isCorrect,
      is_half_correct: isHalfCorrect,
      exam_type: q.exam_type || 0,
      exam_type_name: q.exam_type_name || EXAM_TYPE_MAP[q.exam_type] || '未知题型',
      level: q.level || 0,
      exam_level: q.exam_level || q.level || 0,
      integral: parseFloat(String(q.integral || 0)) || 0,
      score: parseFloat(String(q.score || 0)) || 0,
      chapter: q.chapter || {},
      knowledge: q.Knowledge || [],
      labels: q.label_data || [],
      is_case_question: isCaseQuestion,
      sub_questions: subQuestions
    }
      } catch (itemError) {
        logError('processQuestionList - 题目处理', /** @type {Error} */ (itemError), { questionIndex: index, questionId: q.uid })
        // 返回一个默认题目对象
        return {
          uid: q.uid || '',
          title: q.title || '题目加载失败',
          options: [],
          correct_answer: '',
          user_answer: '-',
          analysis: '',
          is_correct: false,
          is_half_correct: false,
          exam_type: q.exam_type || 0,
          exam_type_name: q.exam_type_name || EXAM_TYPE_MAP[q.exam_type] || '未知题型',
          level: q.level || 0,
          exam_level: q.exam_level || q.level || 0,
          integral: 0,
          score: 0,
          chapter: {},
          knowledge: [],
          labels: [],
          is_case_question: false,
          sub_questions: []
        }
      }
    })
  } catch (error) {
    logError('processQuestionList - 全局错误', /** @type {Error} */ (error), { questionsCount: questions?.length })
    showErrorToast('题目数据处理失败')
    return []
  }
}

/**
 * 格式化考试历史数据
 * @param {ExamHistory|null|undefined} historyData - 历史记录数据
 * @returns {ExamHistory|null} 格式化后的数据
 */
function formatExamHistory(historyData) {
  try {
    if (!historyData) {
      console.warn('[ExamDataAdapter] 历史数据为空')
      return null
    }
    
    return {
      ...historyData,
      // 统一时间戳格式
      submit_time: normalizeTimestamp(historyData.submit_time),
      create_time: normalizeTimestamp(historyData.create_time),
      // 统一答案格式
      user_answer: normalizeAnswer(historyData.user_answer),
      correct_answer: normalizeAnswer(historyData.correct_answer),
      // 确保布尔值类型
      is_correct: Boolean(historyData.is_correct),
      is_half_correct: Boolean(historyData.is_half_correct)
    }
  } catch (error) {
    logError('formatExamHistory', /** @type {Error} */ (error), { historyData })
    return historyData || null // 返回原始数据或null
  }
}

/**
 * 格式化统计数据（优先使用后端返回的统计字段）
 * @param {Partial<Statistics>|null|undefined} stats - 统计数据
 * @returns {Statistics} 格式化后的统计数据
 */
function formatStatistics(stats) {
  try {
    if (!stats) {
      console.warn('[ExamDataAdapter] 统计数据为空')
      return {
        total_count: 0,
        answered_count: 0,
        correct_count: 0,
        error_count: 0,
        accuracy_rate: 0
      }
    }
    
    // 优先使用后端返回的统计字段（数据库优化后新增）
    const useBackendStats = 'total_count' in stats && 'answered_count' in stats && 'accuracy_rate' in stats
    
    if (useBackendStats) {
      // 后端已计算好，直接使用
      return {
        total_count: stats.total_count || 0,
        answered_count: stats.answered_count || 0,
        correct_count: stats.correct_count || 0,
        error_count: stats.error_count || 0,
        accuracy_rate: typeof stats.accuracy_rate === 'number' ? stats.accuracy_rate : parseFloat(String(stats.accuracy_rate)) || 0
      }
    }
    
    // 兼容旧数据：前端计算
    const total = (stats.correct_count || 0) + (stats.error_count || 0)
    const accuracy = total > 0 ? ((stats.correct_count || 0) / total * 100).toFixed(2) : '0'
    
    // 安全处理accuracy_rate：可能是number或string
    const accuracyRate = stats.accuracy_rate !== undefined && stats.accuracy_rate !== null
      ? (typeof stats.accuracy_rate === 'number' ? stats.accuracy_rate : parseFloat(String(stats.accuracy_rate)))
      : parseFloat(accuracy)
    
    return {
      total_count: stats.total_count || total,
      answered_count: stats.answered_count || total,
      correct_count: stats.correct_count || 0,
      error_count: stats.error_count || 0,
      accuracy_rate: accuracyRate || 0
    }
  } catch (error) {
    logError('formatStatistics', /** @type {Error} */ (error), { stats })
    return {
      total_count: 0,
      answered_count: 0,
      correct_count: 0,
      error_count: 0,
      accuracy_rate: 0
    }
  }
}

// 导出所有方法
export default {
  // 工具方法
  logError,
  showErrorToast,
  // 数据处理方法
  recursiveParseJson,
  normalizeTimestamp,
  normalizeAnswer,
  checkAnswer,
  processOptions,
  processCaseSubQuestions,
  processQuestionList,
  formatExamHistory,
  formatStatistics
}
