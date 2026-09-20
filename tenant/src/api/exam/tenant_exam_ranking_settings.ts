import request from '@/utils/request'

// 添加排行榜设置表
export function apiTenantExamRankingSettingsAdd(params: any) {
    return request.post({ url: '/exam.tenant_exam_ranking_settings/add', params })
}

// 编辑排行榜设置表
export function apiTenantExamRankingSettingsEdit(params: any) {
    return request.post({ url: '/exam.tenant_exam_ranking_settings/edit', params })
}

// 排行榜设置表详情
export function apiTenantExamRankingSettingsDetail(params: any) {
    return request.get({ url: '/exam.tenant_exam_ranking_settings/detail', params })
}
