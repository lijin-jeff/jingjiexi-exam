import request from '@/utils/request'

// 题库章节列表
export function apiTenantExamChapterLists(params: any) {
    //console.log(params)
    return request.get({ url: '/exam.tenant_exam_chapter/lists', params })
}

// 添加题库章节
export function apiTenantExamChapterAdd(params: any) {
    return request.post({ url: '/exam.tenant_exam_chapter/add', params })
}

// 编辑题库章节
export function apiTenantExamChapterEdit(params: any) {
    return request.post({ url: '/exam.tenant_exam_chapter/edit', params })
}

// 删除题库章节
export function apiTenantExamChapterDelete(params: any) {
    return request.post({ url: '/exam.tenant_exam_chapter/delete', params })
}

// 题库章节详情
export function apiTenantExamChapterDetail(params: any) {
    //console.log(params)
    return request.get({ url: '/exam.tenant_exam_chapter/detail', params })
}

// 试题章节父级列表
export function apiTenantExamChapterParent(params: object) {
    return request.get({ url: '/exam.tenant_exam_chapter/parentList', params })
}

// 试题章节树
export function apiTenantExamChapterTree(params: any) {
    return request.get({ url: '/exam.tenant_exam_chapter/categoryTree', params })
}

// 导出章节数据
export function apiTenantExamChapterExport(params: any) {
    return request.get({ url: '/exam.tenant_exam_chapter/export', params })
}

// 从txt文件导入章节
export function apiTenantExamChapterImportFromTxt(params: any) {
    return request.post({ url: '/exam.tenant_exam_chapter/importFromTxt', params })
}
