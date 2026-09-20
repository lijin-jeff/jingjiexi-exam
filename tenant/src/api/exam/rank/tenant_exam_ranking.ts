import request from '@/utils/request'

// 每日答题排行榜（按日更新）列表
export function apiTenantExamRankingDailyLists(params: any) {
    console.log(params)
    return request.get({ url: '/exam.tenant_exam_ranking_daily/lists', params })
}

// 编辑每日答题排行榜（按日更新）编辑
export function apiTenantExamRankingDailyEdit(params: any) {
    return request.post({ url: '/exam.tenant_exam_ranking_daily/edit', params })
}

// 删除每日答题排行榜（按日更新）删除
export function apiTenantExamRankingDailyDelete(params: any) {
    return request.post({ url: '/exam.tenant_exam_ranking_daily/delete', params })
}

// 每日答题排行榜（按日更新）详情
export function apiTenantExamRankingDailyDetail(params: any) {
    return request.get({ url: '/exam.tenant_exam_ranking_daily/detail', params })
}

// 每周答题排行榜表（周日结算）列表
export function apiTenantExamRankingWeeklyLists(params: any) {
    return request.get({ url: '/exam.tenant_exam_ranking_weekly/lists', params })
}

// 编辑每周答题排行榜表（周日结算）编辑
export function apiTenantExamRankingWeeklyEdit(params: any) {
    return request.post({ url: '/exam.tenant_exam_ranking_weekly/edit', params })
}

// 删除每周答题排行榜表（周日结算）删除
export function apiTenantExamRankingWeeklyDelete(params: any) {
    return request.post({ url: '/exam.tenant_exam_ranking_weekly/delete', params })
}

// 每周答题排行榜表（周日结算）详情
export function apiTenantExamRankingWeeklyDetail(params: any) {
    return request.get({ url: '/exam.tenant_exam_ranking_weekly/detail', params })
}

// 每月答题排行榜表（月结算）列表
export function apiTenantExamRankingMonthlyLists(params: any) {
    return request.get({ url: '/exam.tenant_exam_ranking_monthly/lists', params })
}

// 编辑每月答题排行榜表（月结算）编辑
export function apiTenantExamRankingMonthlyEdit(params: any) {
    return request.post({ url: '/exam.tenant_exam_ranking_monthly/edit', params })
}

// 删除每月答题排行榜表（月结算）删除
export function apiTenantExamRankingMonthlyDelete(params: any) {
    return request.post({ url: '/exam.tenant_exam_ranking_monthly/delete', params })
}

// 每月答题排行榜表（月结算）详情
export function apiTenantExamRankingMonthlyDetail(params: any) {
    return request.get({ url: '/exam.tenant_exam_ranking_monthly/detail', params })
}

// 总答题排行榜表（按年结算）列表
export function apiTenantExamRankingTotalLists(params: any) {
    return request.get({ url: '/exam.tenant_exam_ranking_total/lists', params })
}

// 编辑总答题排行榜表（按年结算）编辑
export function apiTenantExamRankingTotalEdit(params: any) {
    return request.post({ url: '/exam.tenant_exam_ranking_total/edit', params })
}

// 删除总答题排行榜表（按年结算）删除
export function apiTenantExamRankingTotalDelete(params: any) {
    return request.post({ url: '/exam.tenant_exam_ranking_total/delete', params })
}

// 总答题排行榜表（按年结算）详情
export function apiTenantExamRankingTotalDetail(params: any) {
    return request.get({ url: '/exam.tenant_exam_ranking_total/detail', params })
}
