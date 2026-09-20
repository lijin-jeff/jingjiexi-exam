import request from "@/util/request"

export default {
	apiCourseSecondCategory(params) {
		return request.get("index/course.category/secondList", {
			params: params
		}).then(res => {
			return res
		})
	},
	apiCourseList(params) {
		return request.get('index/course.course/courseList', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiHistoryCourseList(params) {
		return request.get('index/course.history/courseList', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiCourseContent(params) {
		return request.get('index/course.course/courseContent', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiCourseChapterList(params) {
		return request.get('index/course.chapter/chapterList', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiCourseChapterArticleContent(params) {
		return request.get('index/course.chapter/articleContent', {
			params: params
		}).then(res => {
			return res
		})
	},
	apiCourseCollection(params) {
		return request.post('index/course.course/courseCollection', params).then(res => {
			return res
		})
	},
}