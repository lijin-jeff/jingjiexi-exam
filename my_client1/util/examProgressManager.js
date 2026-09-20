/**
 * 答题进度管理工具
 * 统一管理所有题型的答题进度保存和恢复
 */

// 防抖计时器（避免频繁保存）
let autoSaveTimer = null;
const AUTO_SAVE_DELAY = 500; // 500ms防抖延迟
const CACHE_EXPIRY_TIME = 30 * 24 * 60 * 60 * 1000; // 30天过期时间（毫秒）
const MAX_CACHE_SIZE = 5 * 1024 * 1024; // 5MB 最大缓存大小限制

/**
 * 生成缓存键名
 * @param {number} questionsType - 题型类型
 * @param {string} chapterUid - 章节UID（章节练习时使用）
 * @param {string} userId - 用户ID
 * @returns {string} - 缓存键名
 */
function generateCacheKey(questionsType, chapterUid = '', userId = '') {
	const baseKey = `exam_progress_${userId}_${questionsType}`;
	
	// 章节练习需要加上章节UID
	if (questionsType === 1 && chapterUid) {
		return `${baseKey}_chapter_${chapterUid}`;
	}
	
	return baseKey;
}

/**
 * 检查缓存大小是否超出限制
 * @param {Object} data - 要存储的数据
 * @returns {boolean} - 是否超出限制
 */
function isCacheSizeExceeded(data) {
	try {
		const dataString = JSON.stringify(data);
		// 在小程序环境中，使用字符串长度估算大小
		// 每个字符大约 2 字节（UTF-16）
		const dataSize = dataString.length * 2;
		return dataSize > MAX_CACHE_SIZE;
	} catch (error) {
		console.error('[答题进度] 检查缓存大小失败:', error);
		return false; // 出错时默认返回未超出限制，确保功能正常
	}
}

/**
 * 保存答题进度
 * @param {Object} progressData - 进度数据
 * @param {boolean} isSync - 是否同步保存（默认异步）
 * @returns {Promise<boolean>|boolean} - 是否保存成功
 */
export function saveExamProgress(progressData, isSync = false) {
	try {
		const {
			questionsType,
			chapterUid = '',
			userId = '',
			questionList = [],
			currentQuestionIndex = 0,
			answerHistory = [],
			startTime = Date.now(),
			totalTime = 0,
			examSettings = {},
			submitInfo = {}
		} = progressData;
		
		// 错题练习(6)和收藏练习(4)不保存进度
		if (questionsType === 6 || questionsType === 4) {
			return isSync ? false : Promise.resolve(false);
		}
		
		// 验证必要参数
		if (!questionsType || !questionList || questionList.length === 0) {
			return isSync ? false : Promise.resolve(false);
		}
		
		// 构建缓存键
		const cacheKey = generateCacheKey(questionsType, chapterUid, userId);
		
		// 构建完整的进度数据
		const progress = {
			questionsType,
			chapterUid,
			userId,
			questionList,
			currentQuestionIndex,
			answerHistory,
			startTime,
			totalTime,
			examSettings,
			submitInfo,
			savedAt: Date.now(),
			expiresAt: Date.now() + CACHE_EXPIRY_TIME, // 添加过期时间戳
			version: '1.0.2' // 更新版本号
		};
		
		// 检查缓存大小是否超出限制
		if (isCacheSizeExceeded(progress)) {
			console.warn('[答题进度] 缓存大小超出限制，已清除旧缓存');
			// 清除旧缓存
			clearAllExamProgress(userId);
			// 再次检查大小
			if (isCacheSizeExceeded(progress)) {
				console.error('[答题进度] 缓存大小仍然超出限制，保存失败');
				return isSync ? false : Promise.resolve(false);
			}
		}
		
		if (isSync) {
			// 同步保存到本地存储
			uni.setStorageSync(cacheKey, progress);
			// 同步保存索引
			updateProgressIndex(questionsType, chapterUid, userId, cacheKey);
			return true;
		} else {
			// 异步保存到本地存储
			return new Promise((resolve) => {
				uni.setStorage({
					key: cacheKey,
					data: progress,
					success: () => {
						updateProgressIndex(questionsType, chapterUid, userId, cacheKey);
						resolve(true);
					},
					fail: (err) => {
						console.error('[答题进度] 异步保存失败:', err);
						resolve(false);
					}
				});
			});
		}
	} catch (error) {
		console.error('[答题进度] 保存过程出错:', error);
		return isSync ? false : Promise.resolve(false);
	}
}

/**
 * 获取答题进度
 * @param {number} questionsType - 题型类型
 * @param {string} chapterUid - 章节UID（章节练习时使用）
 * @param {string} userId - 用户ID
 * @returns {Object|null} - 进度数据或null
 */
export function getExamProgress(questionsType, chapterUid = '', userId = '') {
	try {
		// 错题练习(6)和收藏练习(4)不获取进度
		if (questionsType === 6 || questionsType === 4) {
			return null;
		}
		
		const cacheKey = generateCacheKey(questionsType, chapterUid, userId);
		const cacheData = uni.getStorageSync(cacheKey);
		
		if (!cacheData) {
			return null;
		}
		
		// 修复：解析JSON字符串
		const progress = typeof cacheData === 'string' ? JSON.parse(cacheData) : cacheData;
		
		// 检查是否过期
		const now = Date.now();
		if (progress.expiresAt && progress.expiresAt < now) {
			console.warn('[答题进度] 缓存数据已过期，已清除');
			clearExamProgress(questionsType, chapterUid, userId);
			return null;
		}
		
		// 修复：兼容两种数据结构：questions 和 questionList
		const questionList = progress.questions || progress.questionList || [];
		
		// 验证数据完整性
		if (questionList.length === 0) {
			console.warn('[答题进度] 缓存数据不完整，已清除');
			clearExamProgress(questionsType, chapterUid, userId);
			return null;
		}
		
		// 对于旧版本数据，添加过期时间戳
		if (!progress.expiresAt) {
			progress.expiresAt = now + CACHE_EXPIRY_TIME;
			progress.version = '1.0.2';
			// 自动更新旧数据
			try {
				uni.setStorageSync(cacheKey, progress);
			} catch (error) {
				console.warn('[答题进度] 更新旧数据失败:', error);
			}
		}
		
		return progress;
	} catch (error) {
		console.error('[答题进度] 读取失败:', error);
		return null;
	}
}

/**
 * 检查是否有答题进度
 * @param {number} questionsType - 题型类型
 * @param {string} chapterUid - 章节UID（章节练习时使用）
 * @param {string} userId - 用户ID
 * @returns {boolean} - 是否有进度
 */
export function hasExamProgress(questionsType, chapterUid = '', userId = '') {
	try {
		// 错题练习(6)和收藏练习(4)总是返回false
		if (questionsType === 6 || questionsType === 4) {
			return false;
		}
		
		const progress = getExamProgress(questionsType, chapterUid, userId);
		// 修复：兼容 questions 和 questionList 两种结构
		if (!progress) return false;
		const questionList = progress.questions || progress.questionList || [];
		return questionList.length > 0;
	} catch (error) {
		console.error('[答题进度] 检查失败:', error);
		return false;
	}
}

/**
 * 清除答题进度
 * @param {number} questionsType - 题型类型
 * @param {string} chapterUid - 章节UID（章节练习时使用）
 * @param {string} userId - 用户ID
 * @returns {boolean} - 是否清除成功
 */
export function clearExamProgress(questionsType, chapterUid = '', userId = '') {
	try {
		const cacheKey = generateCacheKey(questionsType, chapterUid, userId);
		uni.removeStorageSync(cacheKey);
		
		// 更新索引
		removeProgressIndex(questionsType, chapterUid, userId);
		
		return true;
	} catch (error) {
		console.error('[答题进度] 清除失败:', error);
		return false;
	}
}

/**
 * 获取所有章节的进度状态（用于章节练习列表）
 * @param {number} questionsType - 题型类型（应为1）
 * @param {Array} chapterList - 章节列表
 * @param {string} userId - 用户ID
 * @returns {Object} - 章节UID到进度状态的映射
 */
export function getChapterProgressMap(questionsType = 1, chapterList = [], userId = '') {
	try {
		const progressMap = {};
		
		chapterList.forEach(chapter => {
			const chapterUid = chapter.uid || chapter.id;
			if (chapterUid) {
				progressMap[chapterUid] = hasExamProgress(questionsType, chapterUid, userId);
			}
		});
		
		return progressMap;
	} catch (error) {
		console.error('[答题进度] 获取章节进度映射失败:', error);
		return {};
	}
}

/**
 * 更新进度索引（用于快速查找所有进度）
 * @param {number} questionsType - 题型类型
 * @param {string} chapterUid - 章节UID
 * @param {string} userId - 用户ID
 * @param {string} cacheKey - 缓存键
 */
function updateProgressIndex(questionsType, chapterUid, userId, cacheKey) {
	try {
		const indexKey = `exam_progress_index_${userId}`;
		let index = uni.getStorageSync(indexKey) || {};
		
		const typeKey = `type_${questionsType}`;
		if (!index[typeKey]) {
			index[typeKey] = [];
		}
		
		// 避免重复
		if (!index[typeKey].includes(cacheKey)) {
			index[typeKey].push(cacheKey);
		}
		
		uni.setStorageSync(indexKey, index);
	} catch (error) {
		console.warn('[答题进度] 更新索引失败:', error);
	}
}

/**
 * 移除进度索引
 * @param {number} questionsType - 题型类型
 * @param {string} chapterUid - 章节UID
 * @param {string} userId - 用户ID
 */
function removeProgressIndex(questionsType, chapterUid, userId) {
	try {
		const indexKey = `exam_progress_index_${userId}`;
		let index = uni.getStorageSync(indexKey) || {};
		
		const typeKey = `type_${questionsType}`;
		const cacheKey = generateCacheKey(questionsType, chapterUid, userId);
		
		if (index[typeKey]) {
			index[typeKey] = index[typeKey].filter(key => key !== cacheKey);
		}
		
		uni.setStorageSync(indexKey, index);
	} catch (error) {
		console.warn('[答题进度] 移除索引失败:', error);
	}
}

/**
 * 计算题目数据的哈希值，用于检测变化
 * @param {Object} question - 题目数据
 * @returns {string} - 哈希值
 */
function getQuestionHash(question) {
	try {
		// 只计算与用户答案相关的字段
		const relevantFields = {
			uid: question.uid,
			user_answer: question.user_answer,
			is_submitted: question.is_submitted,
			option: question.option?.map(opt => ({
				uid: opt.uid,
				is_selected: opt.is_selected,
				selectedAnswer: opt.selectedAnswer,
				selectedAnswers: opt.selectedAnswers,
				user_answer: opt.user_answer
			}))
		};
		return JSON.stringify(relevantFields);
	} catch (error) {
		console.error('[答题进度] 计算题目哈希失败:', error);
		return '';
	}
}

/**
 * 增量更新答题进度
 * @param {Object} progressData - 进度数据
 * @returns {Promise<boolean>} - 是否保存成功
 */
export async function incrementalSaveExamProgress(progressData) {
	try {
		const {
			questionsType,
			chapterUid = '',
			userId = '',
			questionList = [],
			currentQuestionIndex = 0,
			answerHistory = [],
			startTime = Date.now(),
			totalTime = 0,
			examSettings = {},
			submitInfo = {}
		} = progressData;
		
		// 错题练习(6)和收藏练习(4)不保存进度
		if (questionsType === 6 || questionsType === 4) {
			return false;
		}
		
		// 验证必要参数
		if (!questionsType || !questionList || questionList.length === 0) {
			return false;
		}
		
		// 构建缓存键
		const cacheKey = generateCacheKey(questionsType, chapterUid, userId);
		
		// 获取当前缓存的数据
		const currentCache = uni.getStorageSync(cacheKey);
		const currentData = currentCache ? (typeof currentCache === 'string' ? JSON.parse(currentCache) : currentCache) : null;
		
		// 计算变化的题目
		let changedQuestions = questionList;
		if (currentData && currentData.questionList) {
			// 只保存发生变化的题目
			changedQuestions = questionList.filter((question, index) => {
				const currentQuestion = currentData.questionList[index];
				if (!currentQuestion) return true;
				return getQuestionHash(question) !== getQuestionHash(currentQuestion);
			});
			
			// 如果没有变化，直接返回成功
			if (changedQuestions.length === 0) {
				return true;
			}
		}
		
		// 构建进度数据
		const progress = {
			questionsType,
			chapterUid,
			userId,
			questionList: questionList, // 仍然保存完整的题目列表
			currentQuestionIndex,
			answerHistory,
			startTime,
			totalTime,
			examSettings,
			submitInfo,
			savedAt: Date.now(),
			expiresAt: Date.now() + CACHE_EXPIRY_TIME,
			version: '1.0.2',
			changedQuestionsCount: changedQuestions.length // 记录变化的题目数量
		};
		
		// 检查缓存大小
		if (isCacheSizeExceeded(progress)) {
			console.warn('[答题进度] 缓存大小超出限制，已清除旧缓存');
			clearAllExamProgress(userId);
			if (isCacheSizeExceeded(progress)) {
				console.error('[答题进度] 缓存大小仍然超出限制，保存失败');
				return false;
			}
		}
		
		// 保存数据
		return await saveExamProgress(progress, false);
	} catch (error) {
		console.error('[答题进度] 增量保存失败:', error);
		return false;
	}
}

/**
 * 自动保存答题进度（带防抖和性能优化）
 * 用于用户切换题目时自动保存，避免频繁调用
 * @param {Object} progressData - 进度数据（推荐传入引用，克隆将在防抖结束后进行）
 * @returns {Promise<boolean>} - 是否保存成功
 */
export function autoSaveExamProgress(progressData) {
	return new Promise((resolve) => {
		// 清除之前的计时器
		if (autoSaveTimer) {
			clearTimeout(autoSaveTimer);
		}
		
		// 设置新的计时器（防抖）
		autoSaveTimer = setTimeout(async () => {
			try {
				// 性能优化：使用增量更新
				const result = await incrementalSaveExamProgress(progressData);
				resolve(result);
			} catch (error) {
				console.error('[答题进度] 自动保存失败:', error);
				resolve(false);
			}
		}, AUTO_SAVE_DELAY);
	});
}

/**
 * 清除所有答题进度（谨慎使用）
 * @param {string} userId - 用户ID
 * @returns {boolean} - 是否清除成功
 */
export function clearAllExamProgress(userId = '') {
	try {
		const indexKey = `exam_progress_index_${userId}`;
		const index = uni.getStorageSync(indexKey) || {};
		
		// 删除所有进度缓存
		Object.values(index).forEach(cacheKeys => {
			if (Array.isArray(cacheKeys)) {
				cacheKeys.forEach(cacheKey => {
					uni.removeStorageSync(cacheKey);
				});
			}
		});
		
		// 删除索引
		uni.removeStorageSync(indexKey);
		
		return true;
	} catch (error) {
		console.error('[答题进度] 清除所有进度失败:', error);
		return false;
	}
}

/**
 * 清理过期缓存
 * @param {string} userId - 用户ID
 * @returns {boolean} - 是否清理成功
 */
export function cleanExpiredCache(userId = '') {
	try {
		const indexKey = `exam_progress_index_${userId}`;
		const index = uni.getStorageSync(indexKey) || {};
		const now = Date.now();
		let cleanedCount = 0;
		
		// 遍历所有缓存键，检查是否过期
		Object.values(index).forEach(cacheKeys => {
			if (Array.isArray(cacheKeys)) {
				const validKeys = [];
				cacheKeys.forEach(cacheKey => {
					try {
						const cacheData = uni.getStorageSync(cacheKey);
						if (cacheData) {
							const progress = typeof cacheData === 'string' ? JSON.parse(cacheData) : cacheData;
							// 检查是否过期
							if (progress.expiresAt && progress.expiresAt < now) {
								// 过期，清除缓存
								uni.removeStorageSync(cacheKey);
								cleanedCount++;
							} else {
								// 未过期，保留在索引中
								validKeys.push(cacheKey);
							}
						}
					} catch (error) {
						// 解析失败，清除缓存
						uni.removeStorageSync(cacheKey);
						cleanedCount++;
					}
				});
				// 更新索引，只保留未过期的键
				cacheKeys.splice(0, cacheKeys.length, ...validKeys);
			}
		});
		
		// 更新索引
		uni.setStorageSync(indexKey, index);
		
		if (cleanedCount > 0) {
			console.log(`[答题进度] 清理了 ${cleanedCount} 个过期缓存`);
		}
		
		return true;
	} catch (error) {
		console.error('[答题进度] 清理过期缓存失败:', error);
		return false;
	}
}

export default {
	saveExamProgress,
	getExamProgress,
	hasExamProgress,
	clearExamProgress,
	getChapterProgressMap,
	clearAllExamProgress,
	autoSaveExamProgress,
	incrementalSaveExamProgress,
	cleanExpiredCache
}
