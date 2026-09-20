<template>
    <el-card class="!border-none margin-bottom20" shadow="never">
        <el-page-header content="试题列表" @back="$router.back()" />
    </el-card>
    <el-card class="!border-none mb-4" shadow="never">
        <el-form class="mb-[-16px]" :model="queryParams" inline>
            <el-row>
                <el-form-item label="试题 uid" prop="uid">
                    <el-input
                        class="w-[280px]"
                        v-model="queryParams.uid"
                        clearable
                        placeholder="请输入题库试题 uid"
                    />
                </el-form-item>

                <el-form-item label="试题题干" prop="title">
                    <el-input
                        class="w-[140px]"
                        v-model="queryParams.title"
                        clearable
                        placeholder="请输入试题题干"
                    />
                </el-form-item>
                <el-form-item label="试题题型" prop="exam_type">
                    <el-select
                        style="width: 100px"
                        v-model="queryParams.exam_type"
                        clearable
                        placeholder="请选择试题题型"
                    >
                        <el-option label="全部" value=""></el-option>
                        <el-option
                            v-for="(item, index) in dictData.exam_type"
                            :key="index"
                            :label="item.name"
                            :value="item.value"
                        />
                    </el-select>
                </el-form-item>
                <el-form-item label="试题章节" prop="chapter_uid">
                    <el-tree-select
                        v-model="queryParams.chapter_uid"
                        :data="chapterList"
                        clearable
                        filterable
                        node-key="uid"
                        :props="{
                            label: 'title',
                            children: 'children'
                        }"
                        :default-expand-all="false"
                        placeholder="请选择章节"
                        check-strictly
                        style="width: 180px"
                        @change="handleChangechapter"
                    />
                </el-form-item>
                <el-form-item label="知识点" prop="knowledge_uid">
                    <el-select
                        v-model="queryParams.knowledge_uid"
                        clearable
                        filterable
                        placeholder="请选择知识点"
                        style="width: 180px"
                        @change="handleChangeknowledge"
                    >
                        <el-option label="全部" value=""></el-option>
                        <el-option
                            v-for="item in knowledgeList"
                            :key="item.uid"
                            :label="item.title"
                            :value="item.uid"
                        />
                    </el-select>
                </el-form-item>
                <el-form-item label="标签" prop="label_uid">
                    <el-select
                        v-model="queryParams.label_uid"
                        clearable
                        filterable
                        placeholder="请选择标签"
                        style="width: 150px"
                    >
                        <el-option label="全部" value=""></el-option>
                        <el-option
                            v-for="(item, index) in labelList"
                            :key="index"
                            :label="item.title"
                            :value="item.uid"
                        />
                    </el-select>
                </el-form-item>
                <el-form-item label="显示状态" prop="is_show">
                    <el-select
                        style="width: 100px"
                        v-model="queryParams.is_show"
                        clearable
                        placeholder="请选择显示状态"
                    >
                        <el-option label="全部" value=""></el-option>
                        <el-option
                            v-for="(item, index) in dictData.show_status"
                            :key="index"
                            :label="item.name"
                            :value="item.value"
                        />
                    </el-select>
                </el-form-item>
                <el-form-item label="试题难度" prop="exam_level">
                    <el-select
                        style="width: 100px"
                        v-model="queryParams.exam_level"
                        clearable
                        placeholder="请选择试题难度"
                    >
                        <el-option label="全部" value=""></el-option>
                        <el-option
                            v-for="(item, index) in dictData.exam_level"
                            :key="index"
                            :label="item.name"
                            :value="item.value"
                        />
                    </el-select>
                </el-form-item>
            </el-row>
            <el-row>
                <el-form-item>
                    <el-button type="primary" @click="resetPage">查询</el-button>
                    <el-button @click="resetParams">重置</el-button>
                </el-form-item>
            </el-row>
        </el-form>
    </el-card>
    <el-card class="!border-none" v-loading="pager.loading" shadow="never">
        <el-button
            v-perms="['exam.tenant_exam_question/add']"
            type="primary"
            @click="handleAdd"
            :icon="Plus"
        >
            新增试题
        </el-button>
        <el-button
            v-perms="['exam.tenant_exam_question/delete']"
            :disabled="!selectData.length"
            @click="handleDelete(selectData)"
            :icon="Delete"
        >
            批量删除
        </el-button>
        <div class="mt-4">
            <el-table :data="pager.lists" stripe border @selection-change="handleSelectionChange">
                <el-table-column type="selection" width="55" align="center" />
                <el-table-column label="试题UID" prop="uid" width="180" align="center">
                    <template #default="{ row }">
                        <el-tag type="info" effect="plain" size="small">
                            {{ row.uid }}
                        </el-tag>
                    </template>
                </el-table-column>
                <el-table-column label="题型" width="100" align="center">
                    <template #default="{ row }">
                        <el-tag :type="getExamTypeTagType(row.exam_type)" size="small">
                            <dict-value :options="dictData.exam_type" :value="row.exam_type" />
                        </el-tag>
                    </template>
                </el-table-column>
                <el-table-column label="题干" prop="title" min-width="250" show-overflow-tooltip>
                    <template #default="{ row }">
                        <el-text class="question-title" truncated>
                            {{ row.title }}
                        </el-text>
                    </template>
                </el-table-column>
                <el-table-column label="难度" width="100" align="center">
                    <template #default="{ row }">
                        <el-tag :type="getLevelTagType(row.exam_level)" size="small" effect="plain">
                            <dict-value :options="dictData.exam_level" :value="row.exam_level" />
                        </el-tag>
                    </template>
                </el-table-column>
                <el-table-column label="知识点" width="140" show-overflow-tooltip>
                    <template #default="{ row }">
                        <div
                            v-if="row.knowledge && row.knowledge.length > 0"
                            class="flex flex-wrap gap-1"
                        >
                            <el-tag
                                v-for="(item, index) in row.knowledge"
                                :key="index"
                                type="success"
                                size="small"
                                effect="plain"
                            >
                                {{ item.title }}
                            </el-tag>
                        </div>
                        <span v-else class="text-gray-400">-</span>
                    </template>
                </el-table-column>
                <el-table-column label="标签" width="180" show-overflow-tooltip>
                    <template #default="{ row }">
                        <div v-if="row.labels?.length" class="flex flex-wrap gap-1">
                            <el-tag
                                v-for="(label, index) in row.labels.slice(0, 2)"
                                :key="index"
                                size="small"
                                effect="plain"
                            >
                                {{ label.title }}
                            </el-tag>
                            <el-tag v-if="row.labels.length > 2" size="small" type="info">
                                +{{ row.labels.length - 2 }}
                            </el-tag>
                        </div>
                        <span v-else class="text-gray-400">-</span>
                    </template>
                </el-table-column>
                <el-table-column label="分值" prop="score" width="80" align="center">
                    <template #default="{ row }">
                        <el-text type="primary" class="font-semibold">{{ row.score }}</el-text>
                    </template>
                </el-table-column>
                <el-table-column
                    label="答案"
                    prop="answer"
                    width="100"
                    align="center"
                    show-overflow-tooltip
                >
                    <template #default="{ row }">
                        <el-tag v-if="row.answer" type="warning" size="small" effect="plain">
                            {{ formatAnswer(row.answer) }}
                        </el-tag>
                        <span v-else class="text-gray-400">-</span>
                    </template>
                </el-table-column>
                <el-table-column label="权重" prop="sort" width="80" align="center">
                    <template #default="{ row }">
                        <el-text type="info" size="small">{{ row.sort }}</el-text>
                    </template>
                </el-table-column>
                <el-table-column label="状态" width="100" align="center">
                    <template #default="{ row }">
                        <el-tag :type="row.is_show === 1 ? 'success' : 'danger'" size="small">
                            <dict-value :options="dictData.show_status" :value="row.is_show" />
                        </el-tag>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="150" fixed="right" align="center">
                    <template #default="{ row }">
                        <el-button
                            v-perms="['exam.tenant_exam_question/edit']"
                            type="primary"
                            link
                            @click="handleEdit(row)"
                            :icon="Edit"
                        >
                        </el-button>
                        <el-button
                            v-perms="['exam.tenant_exam_question/edit']"
                            type="primary"
                            link
                            @click="handleCopy(row)"
                            :icon="CopyDocument"
                        >
                        </el-button>
                        <el-button
                            v-perms="['exam.tenant_exam_question/delete']"
                            type="danger"
                            link
                            @click="handleDelete(row.id)"
                            :icon="Delete"
                        >
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="flex mt-4 justify-end">
            <pagination v-model="pager" @change="getLists" />
        </div>
    </el-card>
    <el-dialog v-model="showQuestionEdit" width="80%" title="试题编辑">
        <RadioOption
            v-if="questionType === 1"
            :library-uid="libraryUid"
            :id="questionId"
            :is-copy="isCopyOperation"
            @success="editSuccess"
        />
        <CheckBoxOption
            v-else-if="questionType === 2"
            :library-uid="libraryUid"
            :id="questionId"
            :is-copy="isCopyOperation"
            @success="editSuccess"
        />
        <JudeOption
            v-else-if="questionType === 3"
            :library-uid="libraryUid"
            :id="questionId"
            :is-copy="isCopyOperation"
            @success="editSuccess"
        />
        <WriteOption v-else-if="questionType === 4" />
        <QuestionOption
            v-else-if="questionType === 5"
            :library-uid="libraryUid"
            :id="questionId"
            :is-copy="isCopyOperation"
            @success="editSuccess"
        />
        <CompoundOption
            v-else-if="questionType === 6"
            :library-uid="libraryUid"
            :id="questionId"
            :is-copy="isCopyOperation"
            @success="editSuccess"
        />
        <ClozeOption
            v-else-if="questionType === 7"
            :library-uid="libraryUid"
            :id="questionId"
            :is-copy="isCopyOperation"
            @success="editSuccess"
        />
    </el-dialog>
</template>

<script lang="ts" setup name="tenantExamQuestionLists">
import { CopyDocument, Delete, Edit, Plus } from '@element-plus/icons-vue'
import { nextTick, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import { apiTenantExamChapterTree } from '@/api/exam/tenant_exam_chapter'
import { apiTenantExamKnowledgeTree } from '@/api/exam/tenant_exam_knowledge'
import { apiTenantExamLabelLists } from '@/api/exam/tenant_exam_label'
import {
    apiTenantExamQuestionDelete,
    apiTenantExamQuestionLists
} from '@/api/exam/tenant_exam_question'
import { useDictData } from '@/hooks/useDictOptions'
import { usePaging } from '@/hooks/usePaging'
import feedback from '@/utils/feedback'

import CheckBoxOption from './component/CheckBoxOption.vue'
import ClozeOption from './component/ClozeOption.vue'
import CompoundOption from './component/CompoundOption.vue'
import JudeOption from './component/JudeOption.vue'
import QuestionOption from './component/QuestionOption.vue'
import RadioOption from './component/RadioOption.vue'
import WriteOption from './component/WriteOption.vue'

const router = useRouter()
const route = useRoute()

// 试题编辑
const showQuestionEdit = ref(false)
const questionType = ref(0)
const libraryUid = ref(String(route.query.library_uid || ''))
const questionId = ref(0)
const isCopyOperation = ref(false)

// 查询条件
const queryParams = reactive({
    uid: '',
    title: '',
    exam_type: '',
    is_show: '',
    exam_level: '',
    chapter_uid: String(route.query.chapter_uid || ''),
    knowledge_uid: '',
    label_uid: '',
    library_uid: String(route.query.library_uid || '')
})
const chapterList = reactive<any[]>([])
const knowledgeList = reactive<any[]>([])
const labelList = reactive<any[]>([])
// 选中数据
const selectData = ref<any[]>([])

// 表格选择后回调事件
const handleSelectionChange = (val: any[]) => {
    selectData.value = val.map(({ id }) => id)
}

// 获取字典数据
const { dictData } = useDictData('exam_level,show_status,exam_type')

// 分页相关
const { pager, getLists, resetParams, resetPage } = usePaging({
    fetchFun: apiTenantExamQuestionLists,
    params: queryParams
})

// 添加
const handleAdd = () => {
    router.push({
        path: '/exam/question/edit', // 修改为绝对路径
        query: {
            library_uid: route.query.library_uid
        }
    })
}

// 定义接口明确数据结构
interface QuestionEditData {
    exam_type: number
    id: number
}

// 编辑题目
const handleEdit = (data: QuestionEditData) => {
    // 参数验证
    if (!data || typeof data.exam_type !== 'number' || typeof data.id !== 'number') {
        console.error('无效的问题数据:', data)
        return
    }

    // 先更新数据再显示弹窗，避免无效状态
    questionType.value = data.exam_type
    questionId.value = data.id
    isCopyOperation.value = false // 标识为编辑操作
    // 强制重新渲染组件
    showQuestionEdit.value = false
    // 使用nextTick确保组件卸载后再重新挂载
    nextTick(() => {
        showQuestionEdit.value = true
    })
}

// 复制题目
const handleCopy = (data: QuestionEditData) => {
    // 参数验证
    if (!data || typeof data.exam_type !== 'number' || typeof data.id !== 'number') {
        console.error('无效的问题数据:', data)
        return
    }

    // 先更新数据再显示弹窗，避免无效状态
    questionType.value = data.exam_type
    questionId.value = data.id // 使用原id获取详情，然后在组件中重置id为0
    isCopyOperation.value = true // 标识为复制操作
    // 强制重新渲染组件
    showQuestionEdit.value = false
    // 使用nextTick确保组件卸载后再重新挂载
    nextTick(() => {
        showQuestionEdit.value = true
    })
}

const editSuccess = () => {
    showQuestionEdit.value = false
    getLists()
}

// 删除
const handleDelete = async (id: number | any[]) => {
    await feedback.confirm('确定要删除？')
    await apiTenantExamQuestionDelete({ id })
    getLists()
}

// 获取题型标签类型
const getExamTypeTagType = (
    type: number
): 'success' | 'warning' | 'danger' | 'info' | 'primary' => {
    const typeMap: Record<number, 'success' | 'warning' | 'danger' | 'info' | 'primary'> = {
        1: 'primary', // 单选题
        2: 'success', // 多选题
        3: 'warning', // 判断题
        4: 'danger', // 问答题
        5: 'info', // 填空题
        6: 'primary' // 案例题
    }
    return typeMap[type] || 'info'
}

// 获取难度标签类型
const getLevelTagType = (exam_level: number): 'success' | 'warning' | 'danger' | 'info' => {
    const typeMap: Record<number, 'success' | 'warning' | 'danger' | 'info'> = {
        1: 'success', // 简单
        2: 'info', // 中等
        3: 'warning', // 困难
        4: 'danger' // 非常困难
    }
    return typeMap[exam_level] || 'info'
}

// 格式化答案显示
const formatAnswer = (answer: string | any): string => {
    if (!answer) return '-'
    try {
        // 如果是JSON字符串，解析并转换
        if (typeof answer === 'string' && answer.startsWith('[')) {
            const parsed = JSON.parse(answer)
            return Array.isArray(parsed) ? parsed.join(',') : String(parsed)
        }
        // 如果是数组
        if (Array.isArray(answer)) {
            return answer.join(',')
        }
        return String(answer)
    } catch {
        return String(answer)
    }
}

const fetchChapterList = async () => {
    try {
        const res = await apiTenantExamChapterTree({
            library_uid: queryParams.library_uid
        })
        console.log('章节数据响应:', res)

        // 兼容两种数据结构：{ lists: [...] } 或 { data: { lists: [...] } }
        const lists = res?.lists || res?.data?.lists || []
        console.log('章节列表数据:', lists)

        // 正确更新响应式数组
        chapterList.length = 0
        chapterList.push(...lists)
        console.log('章节树数据:', chapterList)
    } catch (error) {
        console.error('获取章节数据失败:', error)
    }
}
fetchChapterList()

const handleChangechapter = (value: any) => {
    if (value) {
        queryParams.chapter_uid = value
        queryParams.knowledge_uid = '' // 清空知识点选择
        knowledgeList.length = 0
    }
    console.log(queryParams.chapter_uid)
    fetchKnowledgeList()
}
const handleChangeknowledge = (value: any) => {
    if (value) {
        queryParams.knowledge_uid = value
    }
}
const fetchKnowledgeList = async () => {
    try {
        const res = await apiTenantExamKnowledgeTree({
            chapter_uid: queryParams.chapter_uid
        })
        console.log('知识点数据响应:', res)

        // 正确更新响应式数组
        knowledgeList.length = 0
        knowledgeList.push(...(res || []))
        console.log('知识点列表数据:', knowledgeList)
    } catch (error) {
        console.error('获取知识点数据失败:', error)
    }
}
const fetchLabelList = async () => {
    try {
        const res = await apiTenantExamLabelLists({
            library_uid: queryParams.library_uid
        })
        console.log('标签数据响应:', res)

        // 检查res是否为有效数据且包含lists数组
        if (res?.lists && Array.isArray(res.lists)) {
            labelList.length = 0
            labelList.push(...res.lists)
            console.log('标签列表数据:', labelList)
        } else {
            console.warn('API返回的数据格式无效或缺少lists数组:', res)
        }
    } catch (error) {
        console.error('获取标签数据失败:', error)
    }
}
fetchLabelList()
getLists()
</script>

<style scoped>
.text-gray-400 {
    color: var(--el-text-color-placeholder);
}

.font-semibold {
    font-weight: 600;
}

.question-title {
    display: block;
    max-width: 100%;
}

.flex {
    display: flex;
}

.flex-wrap {
    flex-wrap: wrap;
}

.gap-1 {
    gap: 4px;
}
</style>
