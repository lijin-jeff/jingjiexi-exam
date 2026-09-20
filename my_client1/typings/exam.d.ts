/**
 * 答题系统类型定义
 * @module ExamTypes
 */

/**
 * 题目选项
 */
export interface QuestionOption {
  /** 选项标识 (A, B, C, D) */
  check: string
  /** 选项内容 */
  title: string
  /** 是否为正确答案(字符串) */
  is_check?: string | number
  /** 是否为正确答案(布尔值) */
  is_correct?: boolean
}

/**
 * 题目数据
 */
export interface Question {
  /** 题目唯一标识 */
  uid: string
  /** 题目标题 */
  title: string
  /** 题目选项（标准字段） */
  options?: QuestionOption[] | string
  /** 题目选项（兼容字段，与options含义相同） */
  option?: QuestionOption[] | string
  /** 正确答案 */
  answer: string | string[]
  /** 题目解析 */
  analysis?: string
  /** 题目评论 */
  commentaries?: string
  /** 题型 (0:判断 1:单选 2:多选 3:案例) */
  exam_type: number
  /** 题型名称 */
  exam_type_name?: string
  /** 难度等级 */
  level?: number
  /** 考试难度等级 */
  exam_level?: number
  /** 积分 */
  integral?: number
  /** 分数 */
  score?: number
  /** 章节信息 */
  chapter?: any
  /** 知识点 */
  Knowledge?: any[]
  /** 标签数据 */
  label_data?: any[]
  /** 案例题选项 */
  case_options?: any[]
}

/**
 * 用户答案
 */
export interface UserAnswer {
  /** 题目唯一标识 */
  uid: string
  /** 用户答案 */
  user_answer: string | string[]
  /** 是否正确 */
  is_correct?: boolean
  /** 是否半对 */
  is_half_correct?: boolean
  /** 选项数据(扩展字段) */
  option?: any[]
  /** 其他字段 */
  [key: string]: any
}

/**
 * 处理后的题目
 */
export interface ProcessedQuestion {
  /** 题目唯一标识 */
  uid: string
  /** 题目标题 */
  title: string
  /** 处理后的选项 */
  options: QuestionOption[]
  /** 正确答案(字符串) */
  correct_answer: string
  /** 用户答案(字符串) */
  user_answer: string
  /** 题目解析 */
  analysis: string
  /** 是否正确 */
  is_correct: boolean
  /** 是否半对 */
  is_half_correct: boolean
  /** 题型 */
  exam_type: number
  /** 题型名称 */
  exam_type_name: string
  /** 难度等级 */
  level: number
  /** 考试难度等级 */
  exam_level: number
  /** 积分 */
  integral: number
  /** 分数 */
  score: number
  /** 章节信息 */
  chapter: any
  /** 知识点 */
  knowledge: any[]
  /** 标签数据 */
  labels: any[]
  /** 是否为案例题 */
  is_case_question: boolean
  /** 子试题列表 */
  sub_questions: any[]
}

/**
 * 考试历史记录
 */
export interface ExamHistory {
  /** 提交时间(13位时间戳) */
  submit_time: number
  /** 创建时间(13位时间戳) */
  create_time: number
  /** 用户答案 */
  user_answer: string[]
  /** 正确答案 */
  correct_answer: string[]
  /** 是否正确 */
  is_correct: boolean
  /** 是否半对 */
  is_half_correct: boolean
  /** 其他字段 */
  [key: string]: any
}

/**
 * 统计数据
 */
export interface Statistics {
  /** 总题数 */
  total_count: number
  /** 已答题数 */
  answered_count?: number
  /** 正确数 */
  correct_count: number
  /** 错误数 */
  error_count: number
  /** 正确率 */
  accuracy_rate: number
}

/**
 * 答案正确性检查结果
 */
export interface AnswerCheckResult {
  /** 是否完全正确 */
  isCorrect: boolean
  /** 是否部分正确(多选题半对) */
  isHalfCorrect: boolean
}
