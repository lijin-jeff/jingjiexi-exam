import request from "@/util/request"

export function getActivityList(params) {
	return request.get("index/activity.activity/activityList", {
		params: params,
	}).then(res => {
		return res
	})
}

/**
 * 活动详情
 * @param {Object} params
 */
export function getActivityContent(params) {
	return request.get("index/activity.activity/activityContent", {
		params: params,
	}).then(res => {
		return res
	})
}

/**
 * 获取答题试题列表
 * @param {Object} params
 */
export function getActivityExamList(params) {
	return request.get("index/activity.activity/examList", {
		params: params,
	}).then(res => {
		return res
	})
}

/**
 * 提交答题活动试题
 * @param {Object} params
 */
export function submitActivityExam(params) {
	return request.post("index/activity.activity/examSubmit", params).then(res => {
		return res
	})
}

/**
 * 活动奖品列表
 * @param {Object} params
 */
export function getActivityPrizeList(params) {
	return request.get("index/activity.activity/prizeList", {
		params: params
	}).then(res => {
		return res
	})
}

/**
 * 活动积分排行
 * @param {Object} params
 */
export function getActivityRankList(params) {
	return request.get("index/activity.activity/scoreRank", {
		params: params
	}).then(res => {
		return res
	})
}