import request from '@/utils/request'

// 题库章节知识点列表
export function apiTenantExamKnowledgeLists(params: any) {
    //console.log(params)
    return request.get({ url: '/exam.tenant_exam_knowledge/lists', params })
}

// 添加题库章节知识点
export function apiTenantExamKnowledgeAdd(params: any) {
    return request.post({ url: '/exam.tenant_exam_knowledge/add', params })
}

// 编辑题库章节知识点
export function apiTenantExamKnowledgeEdit(params: any) {
    return request.post({ url: '/exam.tenant_exam_knowledge/edit', params })
}

// 删除题库章节知识点
export function apiTenantExamKnowledgeDelete(params: any) {
    return request.post({ url: '/exam.tenant_exam_knowledge/delete', params })
}

// 题库章节详情知识点
export function apiTenantExamKnowledgeDetail(params: any) {
    return request.get({ url: '/exam.tenant_exam_knowledge/detail', params })
}

// 试题章节知识点父级列表
export function apiTenantExamKnowledgeParent(params: object) {
    return request.get({ url: '/exam.tenant_exam_knowledge/parentList', params })
}

// 试题章节知识点树
export function apiTenantExamKnowledgeTree(params: any) {
    //console.log(params);
    return request.get({ url: '/exam.tenant_exam_knowledge/categoryTree', params })
}
