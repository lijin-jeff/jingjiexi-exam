<template>
    <!-- 单选题 -->
    <div>
        <el-form label-width="100px" :rules="formRules" ref="formRef" :model="formData">
            <el-form-item label="试题章节" prop="chapter_uid">
                <el-tree-select
                    v-model="formData.chapter_uid"
                    :data="chapterList"
                    clearable
                    filterable
                    node-key="uid"
                    :props="{
                        label: 'title',
                        children: 'children'
                    }"
                    :default-expand-all="false"
                    placeholder="请选择试题章节"
                    check-strictly
                    style="width: 100%"
                    @change="handleChapterChange"
                />
            </el-form-item>
            <el-form-item label="章节知识点" prop="knowledge_uid">
                <el-select
                    v-model="formData.knowledge_uid"
                    clearable
                    filterable
                    multiple
                    placeholder="请选择章节知识点（非必选）"
                    style="width: 100%"
                    @change="handleKnowledgeChange"
                >
                    <el-option
                        v-for="item in knowledgeList"
                        :key="item.uid"
                        :label="item.title"
                        :value="item.uid"
                    />
                </el-select>
            </el-form-item>
            <el-form-item label="试题标签" prop="label_uid">
                <el-select
                    clearable
                    multiple
                    placeholder="请选择试题标签（可多选，非必选）"
                    v-model="formData.label_uid"
                    style="width: 100%"
                    @change="handleLabelChange"
                >
                    <el-option
                        v-for="item in labelList"
                        :key="item.uid"
                        :value="item.uid"
                        :label="item.title"
                    >
                    </el-option>
                </el-select>
            </el-form-item>
            <el-form-item label="试题题干" prop="title">
                <exam-editor v-model="formData.title" :height="150" width="100%" />
            </el-form-item>
            <el-form-item label="显示权重" prop="sort">
                <div style="width: 100%">
                    <el-input-number
                        :min="0"
                        :step="1"
                        v-model="formData.sort"
                        style="width: 100%"
                        clearable
                        placeholder="请输入"
                    />
                    <div class="form-tips">默认为0， 只能为大于0的整数。值越大越排前显示。</div>
                </div>
            </el-form-item>
            <el-form-item label="试题积分" prop="integral">
                <div style="width: 100%">
                    <el-input-number
                        :min="0"
                        :max="100"
                        :step="0.5"
                        style="width: 100%"
                        v-model="formData.integral"
                        clearable
                        placeholder="请输入"
                    />
                    <div class="form-tips">
                        默认为0， 可以为0-100之间的小数（如1.0、1.5、2.0、2.5）。
                    </div>
                </div>
            </el-form-item>
            <el-form-item label="试题分值" prop="score">
                <div style="width: 100%">
                    <el-input-number
                        :min="2"
                        :step="0.5"
                        style="width: 100%"
                        v-model="formData.score"
                        clearable
                        placeholder="请输入"
                    />
                    <div class="form-tips">
                        默认为2， 可以为2-100之间的小数（如2.0、2.5、3.0）。
                    </div>
                </div>
            </el-form-item>
            <el-form-item label="启用状态" prop="is_show">
                <el-radio-group v-model="formData.is_show" placeholder="请选择">
                    <el-radio
                        v-for="(item, index) in dictData.show_status"
                        :key="index"
                        :value="parseInt(item.value)"
                        :label="parseInt(item.value)"
                    >
                        {{ item.name }}
                    </el-radio>
                </el-radio-group>
            </el-form-item>
            <el-form-item label="试题难度" prop="exam_level">
                <el-radio-group v-model="formData.exam_level" placeholder="请选择试题难度">
                    <el-radio
                        v-for="(item, index) in dictData.exam_level"
                        :key="index"
                        :value="parseInt(item.value)"
                        :label="parseInt(item.value)"
                    >
                        {{ item.name }}
                    </el-radio>
                </el-radio-group>
            </el-form-item>
            <el-form-item label="试题解析" prop="analysis">
                <editor v-model="formData.analysis" :height="300" width="100%" />
            </el-form-item>
            <el-form-item label="名师点评" prop="commentaries">
                <editor v-model="formData.commentaries" :height="300" width="100%" />
            </el-form-item>
            <el-form-item label="答案选项" prop="answer">
                <div v-for="(item, index) in formData.option" :key="item.check" class="with100">
                    <div>
                        <el-radio-group @change="optionCheck" v-model="formData.selectAnswer">
                            <div class="" style="width: 100%">
                                <div class="padding-right20 display-flex-start-center">
                                    <div class="padding-right20">
                                        <el-radio
                                            :value="item.check"
                                            :label="`选项` + item.check"
                                            size="large"
                                            name="option_check"
                                        />
                                    </div>
                                    <div class="display-flex-start-center">
                                        <el-button
                                            type="success"
                                            icon="Plus"
                                            link
                                            @click="addOption"
                                            >添加选项
                                        </el-button>
                                        <el-button
                                            type="danger"
                                            icon="Delete"
                                            link
                                            @click="deleteOption(index)"
                                            >删除选项
                                        </el-button>
                                    </div>
                                </div>
                            </div>
                        </el-radio-group>
                    </div>
                    <!--                    编辑器不能嵌套到radio-group内，否则编辑器内的内容不会被显示-->
                    <div class="margin-top10">
                        <exam-editor
                            v-model="formData.option[index].title"
                            :height="150"
                            width="100%"
                        />
                    </div>
                </div>
            </el-form-item>
            <div class="display-flex-start-center">
                <el-button type="primary" @click="saveForm">保存试题</el-button>
                <el-button type="warning">重置数据</el-button>
            </div>
        </el-form>
    </div>
</template>

<script setup lang="ts">
import type { FormInstance } from 'element-plus'

import { apiTenantExamChapterTree } from '@/api/exam/tenant_exam_chapter'
import { apiTenantExamKnowledgeTree } from '@/api/exam/tenant_exam_knowledge'
import { apiTenantExamLabelLists } from '@/api/exam/tenant_exam_label'
import {
    apiTenantExamQuestionAdd,
    apiTenantExamQuestionDetail,
    apiTenantExamQuestionEdit
} from '@/api/exam/tenant_exam_question'
import Editor from '@/components/editor/index.vue' // 请根据实际路径调整
// 添加组件导入
import ExamEditor from '@/components/exam-editor/index.vue'
import { useDictData } from '@/hooks/useDictOptions'
import { ExamOptionCheck } from '@/utils/enums'
interface Props {
    id?: number
    libraryUid: string
    isCopy?: boolean
}

interface OptionItem {
    check: ExamOptionCheck
    title: string
    is_check: boolean
}

interface QuestionDetailRes {
    option: Array<{
        check: ExamOptionCheck
        is_check: boolean
        title: string
    }>
    commentaries?: string
    label_uid?: string | string[]
    knowledge_uid?: string | string[]
    [key: string]: any
}

const emit = defineEmits(['success'])

const props = withDefaults(defineProps<Props>(), {
    isCopy: false
})
const formRef = shallowRef<FormInstance>()
const formData = reactive({
    id: props.id || 0,
    chapter_uid: '',
    title: '',
    integral: 0,
    score: 2,
    analysis: '',
    commentaries: '',
    is_show: 1,
    sort: 0,
    selectAnswer: '',
    answer: [] as ExamOptionCheck[],
    exam_type: 1,
    exam_level: 1,
    knowledge_uid: [] as string[],
    label_uid: [] as string[],
    library_uid: props.libraryUid,
    option: [
        {
            check: ExamOptionCheck.A,
            title: '请输入选项值',
            is_check: false
        }
    ] as OptionItem[]
})
const chapterList = reactive<any[]>([])
const knowledgeList = reactive<any[]>([])
const labelList = reactive<any[]>([])
const { dictData } = useDictData('show_status,exam_level')
const formRules = reactive<any>({
    title: [
        {
            required: true,
            message: '请输入试题题干',
            trigger: ['blur']
        }
    ],
    is_show: [
        {
            required: true,
            message: '请选择试题显示状态',
            trigger: ['blur']
        }
    ],
    sort: [
        {
            required: true,
            message: '请输入试题显示权重',
            trigger: ['blur']
        }
    ],
    // 试题积分，默认0分
    integral: [
        { required: true, message: '请输入试题积分', trigger: ['blur'] },
        { type: 'number', min: 0, max: 100, message: '积分范围为0-100', trigger: ['blur'] }
    ],
    // 试题分值，默认2分
    score: [
        {
            required: true,
            message: '请输入试题分值',
            trigger: ['blur']
        },
        {
            type: 'number',
            message: '请输入大于等于2的数字',
            min: 2
        }
    ],
    answer: [
        {
            required: true,
            message: '请选勾选题答案',
            trigger: ['blur']
        }
    ],
    exam_level: [
        {
            required: true,
            message: '请勾选试题难度',
            trigger: ['blur']
        }
    ],
    chapter_uid: [
        {
            required: true,
            message: '请选择试题章节',
            trigger: ['change']
        }
    ]
})

watch(
    () => props.id,
    (newValue, oldValue) => {
        console.log('新值:', newValue)
        console.log('旧值:', oldValue)
        if (newValue !== oldValue) {
            fetchQuestionDetail()
        }
    }
)

watch(
    () => formData.answer,
    (newVal) => {
        // 同步更新每个选项的 is_check 状态
        formData.option.forEach((item) => {
            item.is_check = newVal.includes(item.check)
        })
    },
    { deep: true }
)

onActivated(() => {
    console.log('onActivated')
})

// 去除字符串首尾的空行和空格
// 去除字符串首尾的空行和空格，包括HTML空段落
const trimContent = (content: string): string => {
    if (!content) return content
    // 去除首尾空格和空行
    let trimmed = content.replace(/^[\s\n]+|[\s\n]+$/g, '')
    // 去除首尾的空段落（<p><br></p> 或 <p></p>）
    trimmed = trimmed.replace(/^(<p>\s*(<br\s*\/?>)?\s*<\/p>\s*)+/i, '')
    trimmed = trimmed.replace(/(\s*<p>\s*(<br\s*\/?>)?\s*<\/p>)+$/i, '')
    return trimmed
}

const saveForm = async () => {
    const data = { ...formData } as any
    try {
        await formRef.value?.validate()

        // 去除editor和exam-editor组件输入数据的首尾空格和空行
        data.title = trimContent(data.title)
        data.analysis = trimContent(data.analysis)
        data.commentaries = trimContent(data.commentaries)

        // 处理选项中的编辑器内容
        if (Array.isArray(data.option)) {
            data.option = data.option.map((option: any) => ({
                ...option,
                title: trimContent(option.title)
            }))
        }

        if (Array.isArray(data.label_uid)) {
            data.label_uid = data.label_uid.join(',')
        }
        if (Array.isArray(data.knowledge_uid)) {
            data.knowledge_uid = data.knowledge_uid.join(',')
        }

        // 根据isCopy和id判断操作类型
        if (props.isCopy) {
            // 复制操作，强制调用新增API
            data.id = 0
            await apiTenantExamQuestionAdd(data)
        } else if (props.id) {
            // 编辑操作，保留原id
            await apiTenantExamQuestionEdit(data)
        } else {
            // 新增操作，id设为0
            data.id = 0
            await apiTenantExamQuestionAdd(data)
        }
        emit('success', data)
    } catch (error) {
        console.log('error submit!!', error)
    }
}

const addOption = () => {
    const enumKeys = Object.keys(ExamOptionCheck).filter((k) => isNaN(Number(k)))
    const lastIndex = formData.option.length
    if (enumKeys[lastIndex] !== undefined) {
        formData.option.push({
            check: ExamOptionCheck[enumKeys[lastIndex] as keyof typeof ExamOptionCheck],
            title: '请输入选项值',
            is_check: false
        })
    }
}
const deleteOption = (index: number) => {
    formData.option.splice(index, 1)
    rebuildOption()
}

const optionCheck = (check: string | number | boolean | undefined) => {
    formData.answer = [check as ExamOptionCheck]
}

const rebuildOption = () => {
    const enumKeys = Object.keys(ExamOptionCheck).filter((k) => isNaN(Number(k)))
    const option: OptionItem[] = []
    for (let i = 0; i < formData.option.length; i++) {
        option.push({
            title: formData.option[i].title,
            check: ExamOptionCheck[enumKeys[i] as keyof typeof ExamOptionCheck],
            is_check: formData.option[i].is_check
        })
    }
    formData.option = option
}

// 修复 fetchQuestionDetail 方法
const fetchQuestionDetail = async () => {
    try {
        const res = await apiTenantExamQuestionDetail({ id: props.id })
        const questionRes = res as QuestionDetailRes
        // 合并数据，确保数值类型正确
        Object.assign(formData, {
            ...res,
            // 确保积分和其他数值字段为数字类型
            integral: Number(res.integral) || 0,
            score: Number(res.score) || 2,
            sort: Number(res.sort) || 0,
            exam_level: Number(res.exam_level) || 1,
            is_show: Number(res.is_show) || 1
        })
        // 确保 answer 是数组格式
        formData.answer = questionRes.option
            .filter((item) => item.is_check)
            .map((item) => item.check)
        formData.selectAnswer = formData.answer[0] || ''
        // 同步选项选中状态
        formData.option.forEach((option) => {
            option.is_check = formData.answer.includes(option.check)
        })
        formData.commentaries = questionRes.commentaries || ''

        // 处理标签数据
        if (typeof questionRes.label_uid === 'string') {
            formData.label_uid = questionRes.label_uid.split(',').map((item: string) => item.trim())
        } else if (Array.isArray(questionRes.label_uid)) {
            formData.label_uid = questionRes.label_uid
        }

        // 处理知识点数据
        if (typeof questionRes.knowledge_uid === 'string') {
            formData.knowledge_uid = questionRes.knowledge_uid
                .split(',')
                .map((item: string) => item.trim())
        } else if (Array.isArray(questionRes.knowledge_uid)) {
            formData.knowledge_uid = questionRes.knowledge_uid
        } else {
            formData.knowledge_uid = []
        }

        // 加载章节列表
        await fetchChapterList()

        // 如果有章节ID，加载知识点列表
        if (formData.chapter_uid) {
            await fetchKnowledgeList()
        }
    } catch (error) {
        console.error('获取试题详情失败:', error)
    }
}

//获取章节树
const fetchChapterList = async () => {
    try {
        const res = await apiTenantExamChapterTree({
            library_uid: formData.library_uid
        })
        // 兼容两种数据结构
        const lists = res?.lists || res?.data?.lists || []
        chapterList.length = 0
        chapterList.push(...lists)
    } catch (error) {
        console.error('获取章节数据失败:', error)
    }
}
// 处理章节选择变化
const handleChapterChange = (value: any) => {
    if (value) {
        formData.chapter_uid = value
        formData.knowledge_uid = []
        knowledgeList.splice(0, knowledgeList.length)
        fetchKnowledgeList()
    } else {
        console.error('chapter_uid赋值失败:')
    }
}

// 处理知识点选择变化
const handleKnowledgeChange = (value: any) => {
    formData.knowledge_uid = value || []
}

// 处理标签选择变化
const handleLabelChange = (value: any) => {
    if (value) {
        formData.label_uid = value
        console.log('选择的标签ID:', value)
    }
}

// 统一异步数据获取方法
const fetchKnowledgeList = async () => {
    try {
        const res = await apiTenantExamKnowledgeTree({
            chapter_uid: formData.chapter_uid
        })
        // 兼容API返回的数据结构，确保正确提取知识点列表
        const lists = res?.lists || res?.data?.lists || res || []
        // 正确更新响应式数组
        knowledgeList.length = 0
        knowledgeList.push(...lists)
    } catch (error) {
        console.error('获取知识点失败:', error)
    }
}

// 获取标签列表
const fetchLabelList = async () => {
    try {
        const res = await apiTenantExamLabelLists({
            library_uid: formData.library_uid
        })
        console.log('标签数据响应:', res)

        labelList.length = 0
        if (res?.lists && Array.isArray(res.lists)) {
            labelList.push(...res.lists)
            console.log('标签列表数据:', labelList)
        } else {
            console.warn('未获取到标签数据')
        }
    } catch (error) {
        console.error('获取标签列表失败:', error)
    }
}
fetchLabelList()
fetchChapterList()
//判断如果是编辑，则加载试题详情
if (props.id) {
    fetchQuestionDetail()
}
</script>

<style scoped></style>
