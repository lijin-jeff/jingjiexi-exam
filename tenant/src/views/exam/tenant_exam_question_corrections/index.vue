<template>
    <div>
        <el-card class="!border-none mb-4" shadow="never">
            <el-form class="mb-[-16px]" :model="queryParams" inline>
                <el-form-item label="题目ID" prop="question_uid">
                    <el-input
                        class="w-[280px]"
                        v-model="queryParams.question_uid"
                        clearable
                        placeholder="请输入题目ID"
                    />
                </el-form-item>
                <el-form-item label="题目名称" prop="question_name">
                    <el-input
                        class="w-[280px]"
                        v-model="queryParams.question_name"
                        clearable
                        placeholder="请输入题目名称"
                    />
                </el-form-item>
                <el-form-item label="纠错原因" prop="correction_reason">
                    <el-input
                        class="w-[280px]"
                        v-model="queryParams.correction_reason"
                        clearable
                        placeholder="请输入纠错原因"
                    />
                </el-form-item>
                <el-form-item label="用户ID" prop="user_id">
                    <el-input
                        class="w-[280px]"
                        v-model="queryParams.user_id"
                        clearable
                        placeholder="请输入用户ID"
                    />
                </el-form-item>
                <el-form-item label="用户昵称" prop="user_nickname">
                    <el-input
                        class="w-[280px]"
                        v-model="queryParams.user_nickname"
                        clearable
                        placeholder="请输入用户昵称"
                    />
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="resetPage">查询</el-button>
                    <el-button @click="resetParams">重置</el-button>
                </el-form-item>
            </el-form>
        </el-card>
        <el-card class="!border-none" v-loading="pager.loading" shadow="never">
            <el-button
                v-perms="['exam.tenant_exam_question_corrections/delete']"
                :disabled="!selectData.length"
                @click="handleDelete(selectData)"
            >
                删除
            </el-button>
            <div class="mt-4">
                <el-table
                    :data="pager.lists"
                    stripe
                    border
                    @selection-change="handleSelectionChange"
                >
                    <el-table-column type="selection" width="55" align="center" />
                    <el-table-column label="序号" prop="id" width="80" align="center" />
                    <el-table-column
                        label="题目ID"
                        prop="question_uid"
                        width="100"
                        align="center"
                    />
                    <el-table-column
                        label="题目名称"
                        prop="question_name"
                        min-width="200"
                        show-overflow-tooltip
                    />
                    <el-table-column label="题型" width="100" align="center">
                        <template #default="{ row }">
                            <el-tag :type="getExamTypeTagType(row.exam_type)" size="small">
                                {{ getExamTypeName(row.exam_type) }}
                            </el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="纠错原因"
                        prop="correction_reason"
                        min-width="150"
                        show-overflow-tooltip
                    />
                    <el-table-column label="用户信息" width="180" align="center">
                        <template #default="{ row }">
                            <div class="flex flex-col gap-1">
                                <el-button
                                    v-perms="['user.user/detail']"
                                    type="primary"
                                    link
                                    size="small"
                                >
                                    <router-link
                                        :to="{
                                            path: getRoutePath('user.user/detail'),
                                            query: {
                                                id: row.user_id
                                            }
                                        }"
                                    >
                                        ID: {{ row.user_id }}
                                    </router-link>
                                </el-button>
                                <el-button
                                    v-perms="['user.user/lists']"
                                    type="info"
                                    link
                                    size="small"
                                >
                                    <router-link
                                        :to="{
                                            path: getRoutePath('user.user/lists'),
                                            query: {
                                                page_no: 1,
                                                page_size: 15,
                                                keyword: row.user_nickname
                                            }
                                        }"
                                    >
                                        {{ row.user_nickname }}
                                    </router-link>
                                </el-button>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="平台反馈"
                        prop="platform_feedback"
                        min-width="150"
                        show-overflow-tooltip
                    >
                        <template #default="{ row }">
                            <el-tag v-if="row.platform_feedback" type="success" size="small"
                                >已回复</el-tag
                            >
                            <el-tag v-else type="warning" size="small">未回复</el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="反馈时间"
                        prop="feedback_time"
                        width="160"
                        align="center"
                    />
                    <el-table-column
                        label="提交时间"
                        prop="create_time"
                        width="160"
                        align="center"
                    />
                    <el-table-column label="操作" width="200" fixed="right" align="center">
                        <template #default="{ row }">
                            <el-button
                                type="primary"
                                link
                                @click="handleViewDetail(row)"
                                :icon="View"
                            >
                                查看
                            </el-button>
                            <el-button
                                type="success"
                                link
                                @click="copyQuestionUid(row.question_uid)"
                                :icon="CopyDocument"
                            >
                                UID
                            </el-button>
                            <el-button
                                v-perms="['exam.tenant_exam_question_corrections/delete']"
                                type="danger"
                                link
                                @click="handleDelete(row.id)"
                                :icon="Delete"
                            >
                                删除
                            </el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
            <div class="flex mt-4 justify-end">
                <pagination v-model="pager" @change="getLists" />
            </div>
        </el-card>

        <!-- 查看详情弹窗 -->
        <el-dialog
            v-model="showDetailDialog"
            width="800px"
            title="纠错详情"
            :close-on-click-modal="false"
        >
            <el-descriptions :column="2" border v-if="currentDetail">
                <el-descriptions-item label="纠错ID">
                    {{ currentDetail.id }}
                </el-descriptions-item>
                <el-descriptions-item label="题目ID">
                    {{ currentDetail.question_uid }}
                </el-descriptions-item>
                <el-descriptions-item label="题目名称" :span="2">
                    {{ currentDetail.question_name }}
                </el-descriptions-item>
                <el-descriptions-item label="题型">
                    <el-tag :type="getExamTypeTagType(currentDetail.exam_type)" size="small">
                        {{ getExamTypeName(currentDetail.exam_type) }}
                    </el-tag>
                </el-descriptions-item>
                <el-descriptions-item label="题目UID">
                    <span>{{ currentDetail.question_uid }}</span>
                    <el-button
                        type="primary"
                        link
                        size="small"
                        @click="copyQuestionUid(currentDetail.question_uid)"
                        :icon="CopyDocument"
                    >
                        复制
                    </el-button>
                </el-descriptions-item>
                <el-descriptions-item label="纠错原因" :span="2">
                    <div class="whitespace-pre-wrap">{{ currentDetail.correction_reason }}</div>
                </el-descriptions-item>
                <el-descriptions-item label="用户ID">
                    <el-button v-perms="['user.user/detail']" type="primary" link size="small">
                        <router-link
                            :to="{
                                path: getRoutePath('user.user/detail'),
                                query: {
                                    id: currentDetail.user_id
                                }
                            }"
                        >
                            {{ currentDetail.user_id }}
                        </router-link>
                    </el-button>
                </el-descriptions-item>
                <el-descriptions-item label="用户昵称">
                    {{ currentDetail.user_nickname }}
                </el-descriptions-item>
                <el-descriptions-item label="平台反馈" :span="2">
                    <div v-if="showFeedbackEditor" class="mt-2">
                        <el-input
                            v-model="feedbackForm.platform_feedback"
                            type="textarea"
                            placeholder="请输入平台反馈内容"
                            :rows="4"
                        />
                    </div>
                    <div v-else class="whitespace-pre-wrap">
                        {{ currentDetail.platform_feedback || '暂无反馈' }}
                    </div>
                </el-descriptions-item>
                <el-descriptions-item label="反馈时间">
                    {{ currentDetail.feedback_time || '未反馈' }}
                </el-descriptions-item>
                <el-descriptions-item label="提交时间">
                    {{ currentDetail.create_time }}
                </el-descriptions-item>
            </el-descriptions>
            <template #footer>
                <div class="flex justify-end gap-2">
                    <el-button @click="showDetailDialog = false">关闭</el-button>
                    <el-button
                        v-if="!showFeedbackEditor"
                        type="primary"
                        @click="handleEditFeedback"
                        :icon="EditPen"
                    >
                        编辑反馈
                    </el-button>
                    <el-button
                        v-if="showFeedbackEditor"
                        type="success"
                        @click="handleSaveFeedback"
                        :icon="Check"
                    >
                        保存反馈
                    </el-button>
                    <el-button
                        v-if="showFeedbackEditor"
                        type="warning"
                        @click="handleCancelFeedback"
                        :icon="Close"
                    >
                        取消编辑
                    </el-button>
                    <el-button
                        type="danger"
                        @click="handleDeleteFromDetail"
                        v-perms="['exam.tenant_exam_question_corrections/delete']"
                        :icon="Delete"
                    >
                        删除此纠错
                    </el-button>
                </div>
            </template>
        </el-dialog>
    </div>
    <el-dialog
        v-model="showQuestionEdit"
        width="80%"
        title="试题编辑"
        :close-on-click-modal="false"
    >
        <RadioOption
            v-if="questionType === 1"
            :library-uid="libraryUid"
            :id="questionId"
            @success="editSuccess"
        />
        <CheckBoxOption
            v-else-if="questionType === 2"
            :library-uid="libraryUid"
            :id="questionId"
            @success="editSuccess"
        />
        <JudeOption
            v-else-if="questionType === 3"
            :library-uid="libraryUid"
            :id="questionId"
            @success="editSuccess"
        />
        <WriteOption v-else-if="questionType === 4" />
        <QuestionOption
            v-else-if="questionType === 5"
            :library-uid="libraryUid"
            :id="questionId"
            @success="editSuccess"
        />
        <CompoundOption v-else-if="questionType === 6" :library-uid="libraryUid" :id="questionId" />
        <ClozeOption v-else-if="questionType === 7" :library-uid="libraryUid" :id="questionId" />
    </el-dialog>
</template>

<script lang="ts" setup name="tenantExamQuestionCorrectionsLists">
import { Check, Close, CopyDocument, Delete, EditPen, View } from '@element-plus/icons-vue'

import { apiTenantExamQuestionDetail } from '@/api/exam/tenant_exam_question'
import {
    apiTenantExamQuestionCorrectionsDelete,
    apiTenantExamQuestionCorrectionsEdit,
    apiTenantExamQuestionCorrectionsLists
} from '@/api/exam/tenant_exam_question_corrections'
import { usePaging } from '@/hooks/usePaging'
import { getRoutePath } from '@/router'
import feedback from '@/utils/feedback'
import CheckBoxOption from '@/views/exam/tenant_exam_question/component/CheckBoxOption.vue'
import ClozeOption from '@/views/exam/tenant_exam_question/component/ClozeOption.vue'
import CompoundOption from '@/views/exam/tenant_exam_question/component/CompoundOption.vue'
import JudeOption from '@/views/exam/tenant_exam_question/component/JudeOption.vue'
import QuestionOption from '@/views/exam/tenant_exam_question/component/QuestionOption.vue'
import RadioOption from '@/views/exam/tenant_exam_question/component/RadioOption.vue'
import WriteOption from '@/views/exam/tenant_exam_question/component/WriteOption.vue'

// 试题编辑对话框状态
const showQuestionEdit = ref(false)
const questionType = ref(0)
const libraryUid = ref('')
const questionId = ref(0)

// 详情弹窗状态
const showDetailDialog = ref(false)
const currentDetail = ref<any>(null)

// 反馈编辑相关状态
const showFeedbackEditor = ref(false)
const feedbackForm = reactive({
    platform_feedback: ''
})

// 查询条件
const queryParams = reactive({
    question_uid: '',
    question_name: '',
    correction_reason: '',
    user_id: '',
    user_nickname: '',
    platform_feedback: ''
})

// 选中数据
const selectData = ref<any[]>([])

// 表格选择后回调事件
const handleSelectionChange = (val: any[]) => {
    selectData.value = val.map(({ id }) => id)
}

// 分页相关
const { pager, getLists, resetParams, resetPage } = usePaging({
    fetchFun: apiTenantExamQuestionCorrectionsLists,
    params: queryParams
})

// 编辑
const handleEdit = async (data: any) => {
    // 获取题目详情
    const res = await apiTenantExamQuestionDetail({
        id: data.question_uid,
        exam_type: data.exam_type
    })

    // 设置题目类型和ID
    questionType.value = data.exam_type
    questionId.value = data.question_uid
    libraryUid.value = res.library_uid || ''

    // 打开编辑对话框
    showQuestionEdit.value = true
}

// 编辑成功回调
const editSuccess = () => {
    showQuestionEdit.value = false
    getLists() // 刷新列表
}

// 获取题型名称
const getExamTypeName = (type: number) => {
    const typeMap: Record<number, string> = {
        1: '单选题',
        2: '多选题',
        3: '判断题',
        4: '问答题',
        5: '填空题',
        6: '案例题'
    }
    return typeMap[type] || '未知题型'
}

// 获取题型标签类型
const getExamTypeTagType = (
    type: number
): 'success' | 'warning' | 'info' | 'danger' | 'primary' | undefined => {
    const typeMap: Record<number, 'success' | 'warning' | 'info' | 'danger' | 'primary'> = {
        1: 'primary',
        2: 'success',
        3: 'warning',
        4: 'danger',
        5: 'info',
        6: 'primary'
    }
    return typeMap[type]
}

// 查看详情
const handleViewDetail = (row: any) => {
    currentDetail.value = { ...row }
    // 初始化反馈表单
    feedbackForm.platform_feedback = row.platform_feedback || ''
    showFeedbackEditor.value = false
    showDetailDialog.value = true
}

// 编辑反馈
const handleEditFeedback = () => {
    showFeedbackEditor.value = true
}

// 保存反馈
const handleSaveFeedback = async () => {
    // 调用API保存反馈
    await apiTenantExamQuestionCorrectionsEdit({
        id: currentDetail.value.id,
        platform_feedback: feedbackForm.platform_feedback
    })
    feedback.msgSuccess('反馈保存成功')
    // 更新当前详情数据
    currentDetail.value.platform_feedback = feedbackForm.platform_feedback
    currentDetail.value.feedback_time = new Date().toISOString()
    // 退出编辑模式
    showFeedbackEditor.value = false
    // 刷新列表
    getLists()
}

// 取消编辑反馈
const handleCancelFeedback = () => {
    // 恢复原始值
    feedbackForm.platform_feedback = currentDetail.value.platform_feedback || ''
    showFeedbackEditor.value = false
}

// 从详情弹窗删除
const handleDeleteFromDetail = async () => {
    if (!currentDetail.value) return
    await feedback.confirm('确定要删除此纠错记录？')
    await apiTenantExamQuestionCorrectionsDelete({ id: currentDetail.value.id })
    showDetailDialog.value = false
    currentDetail.value = null
    getLists()
    feedback.msgSuccess('删除成功')
}

// 复制题目UID
const copyQuestionUid = async (questionUid: string) => {
    try {
        await navigator.clipboard.writeText(questionUid)
        feedback.msgSuccess('复制成功')
    } catch (err) {
        feedback.msgError('复制失败，请手动复制')
    }
}

// 删除
const handleDelete = async (id: number | any[]) => {
    await feedback.confirm('确定要删除？')
    await apiTenantExamQuestionCorrectionsDelete({ id })
    getLists()
}

getLists()
</script>
