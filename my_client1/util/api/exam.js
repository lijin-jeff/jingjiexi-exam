import request from "@/util/request"

export default {
	apiQuestionCategoryTree() { // 题库分类
		return request.get('api/exam.category/category').then(res => {
			return res
		})
	},
	apiRecommendQuestionLib(params) { // 推荐题库
		return request.get('api/exam.questionlib/recommendList', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiHotQuestionLib(params) { // 热门题库
		return request.get('api/exam.questionlib/hotList', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiQuestionLib(params) { // 题库列表
		return request.get('api/exam.questionlib/questionList', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiQuestionLibDetail(params) { // 题库详情
		return request.get('api/exam.questionlib/questionLibDetail', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiQuestionMenu(params) { // 题库菜单配置
		return request.get('api/exam.questionlib/questionMenu', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiQuestionRandList(params) { // 随机练习试题列表
		return request.get('api/exam.questionlib/randOptionList', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiQuestionOrderList(params) { // 试题列表
		return request.get('api/exam.questionlib/orderOptionList', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiQuestionOrderListByUids(params) { // 试题列表
		return request.get('api/exam.questionlib/orderOptionListByUids', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiQuestionChapterExamList(params) { // 章节练习试题列表
		return request.get('api/exam.questionlib/chapterOptionList', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiQuestionTypeExamList(params) { // 题型练习试题列表
		return request.get('api/exam.questionlib/typeOptionList', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiQuestionSearchList(params) { // 题库试题搜索列表
		return request.get('api/exam.questionlib/searchOptionList', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiQuestionChapterList(params) { // 题库章节列表
		return request.get('api/exam.questionlib/chapterList', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiQuestionLibraryStats(params) { // 题库整体统计数据
		return request.get('api/exam.questionlib/libraryStats', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiQuestionTypeList(params) { // 题库题型列表
		return request.get('api/exam.questionlib/questionTypeList', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiMockExaminationConfig(params) { // 提交模拟考试配置
		return request.post('api/exam.examination/mockExaminationConfig', params).then(res => {
			return res
		})
	},
	apiMockExaminationList(params) { // 模拟考试拉取
		return request.get('api/exam.examination/mockQuestionList', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiMockExaminationSave(params) { // 模拟考试提交
		return request.post('api/exam.examination/saveMockExamination', params).then(res => {
			return res
		})
	},
	apiMockExaminationHistoryList(params) { // 模拟考试历史列表
		return request.get('api/exam.examination/mockExaminationList', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiQuestionCollection(params) { // 试题收藏action=1，收藏；action=2，取消收藏
		return request.post('api/exam.questionlib/questionCollection', params).then(res => {
			return res
		})
	},
	apiQuestionCollectionList(params) { // 试题收藏列表
		return request.get('api/exam.questionlib/collectionOptionList', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiCollectStats(params) { // 收藏统计
		return request.get('api/exam.questionlib/collectStats', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiQuestionErrorRemove(params) { // 错题移除
		return request.post('api/exam.questionlib/questionErrorRemove', params).then(res => {
			return res
		})
	},
	apiExaminationList(params) { // 考试列表
		return request.get('api/exam.examination/examinationList', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiExaminationContent(params) { // 考试详情
		return request.get('api/exam.examination/examinationContent', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiExaminationQuestionList(params) { // 考试试题列表
		return request.get('api/exam.questionlib/examinationQuestionList', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiSubmitExamination(params) { // 提交考试
		return request.post('api/exam.examination/submitExamination', params).then(res => {
			return res
		})
	},
	apiExaminationHistory(params) { // 考试历史记录
		return request.get('api/exam.examination/getExaminationHistory', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiErroCorrect(params) { // 纠错提交
		return request.post('api/exam.questionlib/errorCorrect', params).then(res => {
			return res
		})
	},	
	apiAddQuestionLike(params) { // 题库试题点赞
		return request.post("api/exam.questionlib/questionLike", params).then(res => {
			return res
		})
	}
	,
	apiSelectedQuestionCount(params) { // 已选择的题数
		return request.get('api/exam.questionlib/selectedQuestionCount', {
			params: params
		}).then(res => {
			return res
		})
	},

	apiMockExaminationResultDetail(params) { // 模拟考试答题结果详情
		return request.get('api/exam.examination/mockExaminationDetail', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiEliminateError(params) { // 标记错题为已消灭
		return request.post('api/exam.questionlib/eliminateError', params).then(res => {
			return res
		})
	},
	apiRestoreError(params) { // 取消错题已消灭状态
		return request.post('api/exam.questionlib/restoreError', params).then(res => {
			return res
		})
	},
	apiErrorStats(params) { // 获取错题统计数据
		return request.get('api/exam.questionlib/errorStats', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiQuestionErrorList(params) { // 获取错题列表
		return request.get('api/exam.questionlib/errorOptionList', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiExaminationHistoryDetail(params) { // 考试历史详情
		return request.get('api/exam.examination/getExaminationHistoryDetail', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiRankingList(params) { // 做题排行榜列表
		return request.get('api/exam.rank/rankingList', {
			params: params
		}).then(res => {
			return res
		})
	},
	
	// ======================== 答题进度相关 API ========================
	
	/**
	 * 保存答题进度
	 * @param {Object} params - 进度数据
	 * @param {string} params.user_uid - 用户UID
	 * @param {string} params.library_uid - 题库UID
	 * @param {number} params.questions_type - 练习类型（1:章节 2:试卷 3:随机 ...）
	 * @param {string} [params.chapter_uid] - 章节UID（章节练习时必填）
	 * @param {Array} params.questions - 题目列表
	 * @param {number} params.current_index - 当前题目索引
	 * @param {string} params.mode - 做题模式（sequence/random/exam）
	 * @param {Array} params.answers - 已答题数据
	 * @param {number} [params.elapsed_time] - 已用时间（秒）
	 * @returns {Promise}
	 */
	apiSaveProgress(params) {
		return request.post('api/exam.examination/saveProgress', params).then(res => {
			return res
		})
	},
	
	/**
	 * 加载答题进度
	 * @param {Object} params - 查询参数
	 * @param {string} params.user_uid - 用户UID
	 * @param {string} params.library_uid - 题库UID
	 * @param {number} params.questions_type - 练习类型
	 * @param {string} [params.chapter_uid] - 章节UID
	 * @returns {Promise<{progress_data, current_index, answered_count, elapsed_time, updated_at}>}
	 */
	apiLoadProgress(params) {
		return request.get('api/exam.examination/loadProgress', {
			params: params
		}).then(res => {
			return res
		})
	},
	
	/**
	 * 清除答题进度
	 * @param {Object} params - 查询参数
	 * @param {string} params.user_uid - 用户UID
	 * @param {string} params.library_uid - 题库UID
	 * @param {number} params.questions_type - 练习类型
	 * @param {string} [params.chapter_uid] - 章节UID
	 * @returns {Promise}
	 */
	apiClearProgress(params) {
		return request.post('api/exam.examination/clearProgress', params).then(res => {
			return res
		})
	},
	
	// ======================== 试题管理相关 API ========================
	
	/**
	 * 添加试题
	 * @param {Object} params - 试题数据
	 * @returns {Promise}
	 */
	apiExamQuestionAdd(params) {
		return request.post('api/exam.question_edit/questionAdd', params).then(res => {
			return res
		})
	},
	
	/**
	 * 编辑试题
	 * @param {Object} params - 试题数据
	 * @returns {Promise}
	 */
	apiExamQuestionEdit(params) {
		return request.post('api/exam.question_edit/questionEdit', params).then(res => {
			return res
		})
	},
	/**
	 * 隐藏试题
	 * @param {Object} params - 试题数据
	 * @returns {Promise}
	 */
	apiExamQuestionHide(params) {
		return request.post('api/exam.question_edit/questionShow', params).then(res => {
			return res
		})
	},

	/**
	 * 删除试题
	 * @param {Object} params - 删除参数
	 * @returns {Promise}
	 */
	apiExamQuestionDelete(params) {
		return request.post('api/exam.question_edit/questionDelete', params).then(res => {
			return res
		})
	},
	

	/**
	 * 试题详情
	 * @param {Object} params - 查询参数
	 * @returns {Promise}
	 */
	apiQuestionDetail(params) { // 试题详情
		return request.get('api/exam.question_edit/questionDetail', {
			params: params
		}).then(res => {
			return res
		})
	},
	
	/**
	 * 获取纠错列表
	 * @param {Object} params - 查询参数
	 * @returns {Promise}
	 */
	myErrorCorrectList(params) {
    	return request.get('api/exam.questionlib/myErrorCorrectList', { params }).then(res => {
        	return res
    	})
	},
	/**
	 * 获取知识点列表
	 * @param {Object} params - 查询参数
	 * @returns {Promise}
	 */
	apiKnowledgeList(params) {
		return request.get('api/exam.questionlib/knowledgeList', {
			params: params
		}).then(res => {
			return res
		})
	},
	/**
	 * 获取标签列表
	 * @param {Object} params - 查询参数
	 * @returns {Promise}
	 */
	apiLabelList(params) {
		return request.get('api/exam.questionlib/LabelList', {
			params: params
		}).then(res => {
			return res
		})
	},
	/**
	 * 获取题目结构信息
	 * @param {Object} params - 查询参数
	 * @returns {Promise}
	 */
	getQuestionStructure(params) {
		return request.get('api/exam.questionlib/getQuestionStructure', {
			params: params
		}).then(res => {
			return res
		})
	}
}