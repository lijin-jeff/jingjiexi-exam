import request from '@/utils/request'

// 题库分类列表
export function apiExamCategoryLists(params: any) {
    return request.get({ url: '/exam.exam_category/lists', params }).catch((error) => {
        console.error('获取题库分类列表失败:', error)
        throw error
    })
}

// 添加题库分类
export function apiExamCategoryAdd(params: any) {
    return request.post({ url: '/exam.exam_category/add', params }).catch((error) => {
        console.error('添加题库分类失败:', error)
        throw error
    })
}

// 编辑题库分类
export function apiExamCategoryEdit(params: any) {
    return request.post({ url: '/exam.exam_category/edit', params }).catch((error) => {
        console.error('编辑题库分类失败:', error)
        throw error
    })
}

// 删除题库分类
export function apiExamCategoryDelete(params: any) {
    return request.post({ url: '/exam.exam_category/delete', params }).catch((error) => {
        console.error('删除题库分类失败:', error)
        throw error
    })
}

// 题库分类详情
export function apiExamCategoryDetail(params: any) {
    return request.get({ url: '/exam.exam_category/detail', params }).catch((error) => {
        console.error('获取题库分类详情失败:', error)
        throw error
    })
}

// 题库分类父级列表
export function apiExamCategoryParent() {
    return request.get({ url: '/exam.exam_category/parentList' }).catch((error) => {
        console.error('获取题库分类父级列表失败:', error)
        throw error
    })
}

// 题库分类树
export function apiExamCategoryTree() {
    return request
        .get({
            url: '/exam.exam_category/categoryTree'
        })
        .catch((error) => {
            console.error('获取题库分类树失败:', error)
            throw error
        })
}

// 更新题库分类状态
export async function apiExamCategoryStatus(params: any) {
    //console.log(params);
    return request.post({ url: '/exam.exam_category/updateStatus', params }).catch((error) => {
        console.error('更新题库分类状态失败:', error)
        throw error
    })
}
