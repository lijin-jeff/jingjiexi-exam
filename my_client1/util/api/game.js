import request from "@/util/request"

/**
 * 游戏列表
 * @param {Object} params
 */
export function gameGroupList(params) {
	return request.get('index/game.game/gameGroupList', {params:params}).then(res => {
		return res
	})
}

/**
 * 游戏详情
 * @param {Object} params
 */
export function grameGroupContent(params) {
	return request.get('index/game.game/gameGroupDetail', {params:params}).then(res => {
		return res
	})
}

/**
 * 游戏试题列表
 * @param {Object} params
 */
export function gameExamList(params) {
		return request.get('index/game.game/gameExam', {params:params}).then(res => {
			return res
		})
}

/**
 * 游戏积分排行
 * @param {Object} params
 */
export function gameScoreRankList(params) {
		return request.get('index/game.game/scoreRank', {params:params}).then(res => {
			return res
		})
}