<template>
    <div class="shop-pages h-[530px]">
        <div class="search-container mb-4">
            <el-input
                v-model="searchKeyword"
                placeholder="请输入页面标题或路径"
                clearable
                size="small"
                class="w-full"
            >
                <template #prefix>
                    <el-icon><Search /></el-icon>
                </template>
            </el-input>
        </div>
        <div class="link-list flex flex-wrap">
            <div
                class="link-item border border-br px-5 py-[5px] rounded-[3px] cursor-pointer mr-[10px] mb-[10px]"
                v-for="(item, index) in filteredPages"
                :class="{
                    'border-primary text-primary':
                        modelValue.path == item.path && modelValue.name == item.name
                }"
                :key="index"
                @click="handleSelect(item)"
            >
                {{ item.name }}
            </div>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { Search } from '@element-plus/icons-vue'
import type { PropType } from 'vue'
import { computed, ref } from 'vue'

import { type Link, LinkTypeEnum } from '.'

defineProps({
    modelValue: {
        type: Object as PropType<Link>,
        default: () => ({})
    }
})

const emit = defineEmits<{
    (event: 'update:modelValue', value: Link): void
}>()

// 小程序页面数据 - 从pages.json中提取
const pagesData = [
    { path: '/pages/index/index', name: '首页', type: LinkTypeEnum.MINI_EXAM },
    { path: '/pages/index/search', name: '全局搜索', type: LinkTypeEnum.MINI_EXAM },
    { path: '/subpages/common/helpContent', name: '帮助详情', type: LinkTypeEnum.MINI_EXAM },
    { path: '/subpages/common/helpList', name: '帮助中心', type: LinkTypeEnum.MINI_EXAM },
    { path: '/subpages/common/policyContent', name: '政策详情', type: LinkTypeEnum.MINI_EXAM },
    { path: '/subpages/common/serviceQrCode', name: '客服二维码', type: LinkTypeEnum.MINI_EXAM },
    { path: '/subpages/common/update', name: '版本更新', type: LinkTypeEnum.MINI_EXAM },
    { path: '/subpages/countdown/list', name: '倒计时列表', type: LinkTypeEnum.MINI_EXAM },
    { path: '/subpages/countdown/followList', name: '我的倒计时', type: LinkTypeEnum.MINI_EXAM },
    { path: '/subpages/countdown/detail', name: '倒计时详情', type: LinkTypeEnum.MINI_EXAM },
    { path: '/subpages/exam/questionContent', name: '题库首页', type: LinkTypeEnum.MINI_EXAM },
    { path: '/subpages/exam/questionSetting', name: '答题设置', type: LinkTypeEnum.MINI_EXAM },
    {
        path: '/subpages/examChapter/questionChapter',
        name: '章节练习',
        type: LinkTypeEnum.MINI_EXAM
    },
    {
        path: '/subpages/examChapter/questionChapterOrder',
        name: '章节顺序',
        type: LinkTypeEnum.MINI_EXAM
    },
    {
        path: '/subpages/examCollection/questionCollection',
        name: '我的收藏',
        type: LinkTypeEnum.MINI_EXAM
    },
    { path: '/subpages/examError/questionError', name: '错题本', type: LinkTypeEnum.MINI_EXAM },
    {
        path: '/subpages/examHistory/examinationHistory',
        name: '答题历史',
        type: LinkTypeEnum.MINI_EXAM
    },
    {
        path: '/subpages/examHistory/history/analysis',
        name: '成绩分析',
        type: LinkTypeEnum.MINI_EXAM
    },
    { path: '/subpages/examHistory/history/chart', name: '图表分析', type: LinkTypeEnum.MINI_EXAM },
    { path: '/subpages/examMn/questionMnSetting', name: '模拟设置', type: LinkTypeEnum.MINI_EXAM },
    { path: '/subpages/examMn/questionMock', name: '模拟考试', type: LinkTypeEnum.MINI_EXAM },
    { path: '/subpages/examOther/correctionForm', name: '纠错反馈', type: LinkTypeEnum.MINI_EXAM },
    { path: '/subpages/examOther/myNoteList', name: '笔记列表', type: LinkTypeEnum.MINI_EXAM },
    { path: '/subpages/examOther/noteDetail', name: '笔记详情', type: LinkTypeEnum.MINI_EXAM },
    { path: '/subpages/examOther/questionRank', name: '排行榜', type: LinkTypeEnum.MINI_EXAM },
    { path: '/subpages/examOther/questionSearch', name: '题目搜索', type: LinkTypeEnum.MINI_EXAM },
    {
        path: '/subpages/examination/examinationContent',
        name: '考试内容',
        type: LinkTypeEnum.MINI_EXAM
    },
    {
        path: '/subpages/examination/examinationQuestion',
        name: '考试题目',
        type: LinkTypeEnum.MINI_EXAM
    },
    {
        path: '/subpages/examination/examinationQuestionLib',
        name: '题库列表',
        type: LinkTypeEnum.MINI_EXAM
    },
    { path: '/subpages/integral/integralHistory', name: '积分明细', type: LinkTypeEnum.MINI_EXAM },
    { path: '/subpages/integral/ranking', name: '积分排行', type: LinkTypeEnum.MINI_EXAM },
    { path: '/subpages/news/content', name: '资讯详情', type: LinkTypeEnum.MINI_EXAM },
    { path: '/subpages/news/articleList', name: '资讯列表', type: LinkTypeEnum.MINI_EXAM },
    { path: '/subpages/resource/content', name: '资源详情', type: LinkTypeEnum.MINI_EXAM },
    { path: '/subpages/resource/resourceList', name: '资源列表', type: LinkTypeEnum.MINI_EXAM },
    { path: '/subpages/user/login', name: '登录页面', type: LinkTypeEnum.MINI_EXAM },
    { path: '/subpages/user/member', name: '会员中心', type: LinkTypeEnum.MINI_EXAM },
    { path: '/subpages/user/message', name: '消息通知', type: LinkTypeEnum.MINI_EXAM },
    { path: '/subpages/user/set', name: '个人信息设置', type: LinkTypeEnum.MINI_EXAM },
    { path: '/subpages/user/userdata', name: '个人信息', type: LinkTypeEnum.MINI_EXAM },
    { path: '/subpages/examOther/myCorrectionList', name: '我的纠错', type: LinkTypeEnum.MINI_EXAM }
]

// 响应式数据
const searchKeyword = ref('')

// 过滤后的页面列表
const filteredPages = computed(() => {
    if (!searchKeyword.value) {
        return pagesData
    }
    const keyword = searchKeyword.value.toLowerCase()
    return pagesData.filter(
        (page) =>
            page.name.toLowerCase().includes(keyword) || page.path.toLowerCase().includes(keyword)
    )
})

const handleSelect = (value: Link) => {
    emit('update:modelValue', value)
}
</script>

<style lang="scss" scoped>
// 搜索容器样式
.search-container {
    margin-bottom: 16px;
}
</style>
