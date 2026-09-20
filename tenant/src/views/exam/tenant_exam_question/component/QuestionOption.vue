<template>
    <!-- 问答题 -->
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
                    @change="
                        (val: any) => {
                            console.log('标签已选:', val)
                        }
                    "
                >
                    <el-option
                        v-for="item in labelList"
                        :key="item.value"
                        :value="item.value"
                        :label="item.label"
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
                        :max="100"
                        :step="0.5"
                        style="width: 100%"
                        v-model="formData.score"
                        clearable
                        placeholder="请输入"
                    />
                    <div class="form-tips">默认为2，可以为2-100之间的小数（如2.0、2.5、3.0）。</div>
                </div>
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
            <el-form-item label="试题解析" prop="analysis">
                <editor v-model="formData.analysis" :height="300" width="100%" />
            </el-form-item>
            <el-form-item label="名师点评" prop="commentaries">
                <editor v-model="formData.commentaries" :height="300" width="100%" />
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
import { ElMessage } from 'element-plus'

import { apiTenantExamChapterTree } from '@/api/exam/tenant_exam_chapter'
import { apiTenantExamKnowledgeTree } from '@/api/exam/tenant_exam_knowledge'
import { apiTenantExamLabelLists } from '@/api/exam/tenant_exam_label'
import {
    apiTenantExamQuestionAdd,
    apiTenantExamQuestionDetail,
    apiTenantExamQuestionEdit
} from '@/api/exam/tenant_exam_question'
import { useDictData } from '@/hooks/useDictOptions'
interface Props {
    id?: number
    libraryUid: string
    isCopy?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    isCopy: false
})

interface QuestionDetailRes {
    labels?: any[]
    knowledge_uid?: string | string[]
    label_uid?: string | string[]
    [key: string]: any
}

const emit = defineEmits(['success'])

const formData = reactive({
    id: props.id || 0,
    title: '',
    integral: 0,
    score: 2,
    analysis: '',
    commentaries: '',
    is_show: 1,
    sort: 0,
    exam_type: 5,
    exam_level: 0,
    answer: '',
    chapter_uid: '',
    knowledge_uid: [] as string[],
    label_uid: [] as string[],
    library_uid: props.libraryUid
})

const chapterList = reactive<any[]>([])
const knowledgeList = reactive<any[]>([])
const labelList = reactive<any[]>([])
const { dictData } = useDictData('show_status,exam_level')
const formRef = shallowRef<FormInstance>()
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
            min: 2,
            max: 100
        }
    ],
    exam_level: [
        {
            required: true,
            message: '请勾选试题难度',
            trigger: ['blur']
        }
    ],
    chapter_uid: [{ required: true, message: '请选择试题章节', trigger: ['change'] }]
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
    await formRef.value?.validate((valid: boolean) => {
        if (valid) {
            // 去除editor和exam-editor组件输入数据的首尾空格和空行
            data.title = trimContent(data.title)
            data.analysis = trimContent(data.analysis)
            data.commentaries = trimContent(data.commentaries)

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
                apiTenantExamQuestionAdd(data)
            } else if (props.id) {
                // 编辑操作，保留原id
                apiTenantExamQuestionEdit(data)
            } else {
                // 新增操作，id设为0
                data.id = 0
                apiTenantExamQuestionAdd(data)
            }
            
            // 添加成功之后，将表单数据清空
            emit('success', data)
        } else {
            console.log('error submit!!', data)
        }
    })
}

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
            exam_level: Number(res.exam_level) || 0,
            is_show: Number(res.is_show) || 1
        })

        // 处理知识点数据
        if (typeof questionRes.knowledge_uid === 'string') {
            formData.knowledge_uid = questionRes.knowledge_uid
                .split(',')
                .map((item: string) => item.trim())
        } else if (Array.isArray(questionRes.knowledge_uid)) {
            formData.knowledge_uid = questionRes.knowledge_uid
        }

        // 处理标签数据
        if (typeof questionRes.label_uid === 'string') {
            formData.label_uid = questionRes.label_uid.split(',').map((item: string) => item.trim())
        } else if (Array.isArray(questionRes.label_uid)) {
            formData.label_uid = questionRes.label_uid
        }

        // 加载章节列表
        await fetchChapterList()

        // 如果有章节ID，加载知识点列表
        if (formData.chapter_uid) {
            await fetchKnowledgeList()
        }

        // 将 API 返回的 labels 数据赋值给 labelList
        if (questionRes.labels) {
            labelList.splice(0, labelList.length, ...questionRes.labels)
        }
    } catch (error) {
        console.error('获取试题详情失败:', error)
    }
}

const fetchChapterList = async () => {
    try {
        const res = await apiTenantExamChapterTree({
            library_uid: formData.library_uid
        })
        console.log('章节数据响应:', res)
        const lists = res?.lists || res?.data?.lists || []
        chapterList.length = 0
        chapterList.push(...lists)
        console.log('章节树数据:', chapterList)
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
        console.log('选择的章节ID:', value)
        fetchKnowledgeList()
    } else {
        console.error('chapter_uid赋值失败:')
    }
}

// 处理知识选择变化
const fetchKnowledgeList = async () => {
    try {
        const res = await apiTenantExamKnowledgeTree({
            chapter_uid: formData.chapter_uid
        })
        console.log('知识点数据响应:', res)
        // 兼容API返回的数据结构，确保正确提取知识点列表
        const lists = res?.lists || res?.data?.lists || res || []
        // 正确更新响应式数组
        knowledgeList.length = 0
        knowledgeList.push(...lists)
        console.log('知识点列表数据:', knowledgeList)
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
        labelList.splice(0, labelList.length)
        if (res?.lists && Array.isArray(res.lists)) {
            res.lists.forEach((item: any) => {
                labelList.push({
                    label: item?.title || '',
                    value: item?.uid || ''
                })
            })
        } else {
            ElMessage.warning('未获取到标签数据')
        }
    } catch (error) {
        console.error('获取标签列表失败:', error)
        ElMessage.error('获取标签失败') // 新增：错误提示
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
