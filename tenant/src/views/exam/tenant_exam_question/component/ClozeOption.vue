<template>
    <!-- 完形填空 -->
    <div>
        <el-form label-width="100px" :model="formData" ref="formRef" :rules="formRules">
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
            <el-form-item label="试题材料" prop="title">
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
                        :min="1"
                        :step="0.5"
                        style="width: 100%"
                        v-model="formData.score"
                        clearable
                        placeholder="请输入"
                    />
                    <div class="form-tips">
                        默认为1，可以为1-100之间的小数（如1.0、1.5、2.0、2.5）。
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
            <el-form-item label="子项试题">
                <div
                    v-for="(item, index) in formData.option"
                    :key="item.check"
                    class="with100 margin-bottom20"
                >
                    <div>
                        <el-checkbox-group v-model="formData.answer">
                            <div class="" style="width: 100%">
                                <div class="padding-right20 display-flex-start-center">
                                    <div class="display-flex-start-center">
                                        <el-button
                                            type="success"
                                            icon="Plus"
                                            link
                                            @click="addQuestion"
                                            >添加子试题
                                        </el-button>
                                        <el-button
                                            type="danger"
                                            icon="Delete"
                                            link
                                            @click="deleteQuestion(index)"
                                            >删除试题
                                        </el-button>
                                    </div>
                                </div>
                            </div>
                        </el-checkbox-group>
                    </div>
                    <!--                    编辑器不能嵌套到radio-group内，否则编辑器内的内容不会被显示-->
                    <div class="margin-top10">
                        <exam-editor
                            v-model="formData.option[index].title"
                            :height="150"
                            width="100%"
                            placeholder="请输入子试题题干"
                        />
                        <div class="margin-left50">
                            <div
                                v-for="(childrenItem, childrenIndex) in item.children"
                                :key="childrenItem.check"
                                class="with100"
                            >
                                <el-checkbox-group v-model="formData.answer">
                                    <div class="" style="width: 100%">
                                        <div class="padding-right20 display-flex-start-center">
                                            <div class="padding-right">
                                                <el-checkbox
                                                    :value="childrenItem.check"
                                                    :label="childrenItem.check"
                                                    size="large"
                                                    name="option_check"
                                                />
                                            </div>
                                            <div class="display-flex-start-center">
                                                <el-button
                                                    type="success"
                                                    icon="Plus"
                                                    link
                                                    @click="addOption(index)"
                                                    >添加选项
                                                </el-button>
                                                <el-button
                                                    type="danger"
                                                    icon="Delete"
                                                    link
                                                    @click="deleteOption(index, childrenIndex)"
                                                    >删除选项
                                                </el-button>
                                            </div>
                                        </div>
                                    </div>
                                </el-checkbox-group>
                                <div class="margin-top10">
                                    <exam-editor
                                        v-model="formData.option[index].children[childrenIndex].title"
                                        :height="120"
                                        width="100%"
                                        placeholder="请输入选项内容"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </el-form-item>
            <div class="display-flex">
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
import { apiTenantExamQuestionAdd, apiTenantExamQuestionEdit } from '@/api/exam/tenant_exam_question'
import { useDictData } from '@/hooks/useDictOptions'
import { ExamOptionCheck } from '@/utils/enums'

interface OptionChild {
    check: ExamOptionCheck
    title: string
}

interface OptionItem {
    check: string
    title: string
    children: OptionChild[]
}

interface Props {
    id?: number
    libraryUid: string
    isCopy?: boolean
}

const emit = defineEmits(['success'])
const props = withDefaults(defineProps<Props>(), {
    isCopy: false
})

const formData = reactive({
    id: props.id || 0,
    uid: '',
    chapter_uid: '',
    library_uid: props.libraryUid,
    title: '',
    integral: 0,
    score: 1,
    analysis: '',
    is_show: 1,
    sort: 0,
    exam_level: 1,
    selectAnswer: '',
    answer: [] as string[],
    exam_type: 7, // 完形填空题型
    knowledge_uid: [] as string[],
    label_uid: [] as string[],
    option: [
        {
            check: '',
            title: '请输入子试题题干',
            children: [
                {
                    check: ExamOptionCheck.A,
                    title: '请输入选项内容'
                }
            ]
        }
    ] as OptionItem[]
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
    integral: [
        { required: true, message: '请输入试题积分', trigger: ['blur'] },
        { type: 'number', min: 0, max: 100, message: '积分范围为0-100', trigger: ['blur'] }
    ],
    score: [
        {
            required: true,
            message: '请输入试题分值',
            trigger: ['blur']
        },
        {
            type: 'number',
            message: '请输入大于等于1的数字',
            min: 1
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

// 去除字符串首尾的空行和空格
// 去除字符串首尾的空行和空格，包括HTML空段落
const trimContent = (content: string): string => {
    if (!content) return content
    // 去除首尾空格和空行
    let trimmed = content.replace(/^[\s\n]+|[\s\n]+$/g, '')
    // 去除首尾的空段落（<p><br></p> 或 <p></p>）
    trimmed = trimmed.replace(/^(\s*<p>\s*(<br\s*\/?>)?\s*<\/p>\s*)+/i, '')
    trimmed = trimmed.replace(/(\s*<p>\s*(<br\s*\/?>)?\s*<\/p>)+$/i, '')
    return trimmed
}

const addQuestion = () => {
    formData.option.push({
        check: '',
        title: '请输入子试题题干',
        children: [
            {
                check: ExamOptionCheck.A,
                title: '请输入选项内容'
            }
        ]
    })
}
const deleteQuestion = (index: number) => {
    formData.option.splice(index, 1)
}

const addOption = (index: number) => {
    const enumKeys = Object.keys(ExamOptionCheck).filter((k) => isNaN(Number(k)))
    const lastIndex = formData.option[index].children.length
    if (formData.option[index].children.length < 4) {
        if (enumKeys[lastIndex] !== undefined) {
            formData.option[index].children.push({
                check: ExamOptionCheck[enumKeys[lastIndex] as keyof typeof ExamOptionCheck],
                title: '请输入选项值'
            })
        }
    }
}
const deleteOption = (index: number, childrenIndex: number) => {
    formData.option[index].children.splice(childrenIndex, 1)
    rebuildOption()
}

const rebuildOption = () => {
    const enumKeys = Object.keys(ExamOptionCheck).filter((k) => isNaN(Number(k)))
    for (let i = 0; i < formData.option.length; i++) {
        const option: OptionChild[] = []
        for (let j = 0; j < formData.option[i].children.length; j++) {
            option.push({
                title: formData.option[i].children[j].title,
                check: ExamOptionCheck[enumKeys[j] as keyof typeof ExamOptionCheck]
            })
        }
        formData.option[i].children = option
    }
}

// 保存表单
const saveForm = async () => {
    const data = { ...formData } as any

    await formRef.value?.validate(async (valid: boolean) => {
        if (valid) {
            // 去除editor和exam-editor组件输入数据的首尾空格和空行
            data.title = trimContent(data.title)
            data.analysis = trimContent(data.analysis)

            // 处理子试题内容
            if (Array.isArray(data.option)) {
                data.option = data.option.map((option: OptionItem) => {
                    const processedOption = {
                        ...option,
                        title: trimContent(option.title)
                    }

                    // 处理子试题选项
                    if (Array.isArray(processedOption.children)) {
                        processedOption.children = processedOption.children.map((child) => ({
                            ...child,
                            title: trimContent(child.title)
                        }))
                    }

                    return processedOption
                })
            }

            if (Array.isArray(data.label_uid)) {
                data.label_uid = data.label_uid.join(',')
            }
            if (Array.isArray(data.knowledge_uid)) {
                data.knowledge_uid = data.knowledge_uid.join(',')
            }

            // 保存数据 - 根据是否是复制操作和是否有id判断是编辑还是添加
            if (props.isCopy) {
                // 复制操作，强制调用新增API
                data.id = 0
                await apiTenantExamQuestionAdd(data)
            } else if (props.id) {
                // 编辑操作，保留原id
                data.id = props.id
                await apiTenantExamQuestionEdit(data)
            } else {
                // 新增操作，id设为0
                data.id = 0
                await apiTenantExamQuestionAdd(data)
            }
            emit('success', data)
        } else {
            console.log('error submit!!', data)
        }
    })
}

// 获取章节列表
const fetchChapterList = async () => {
    try {
        const res = await apiTenantExamChapterTree({
            library_uid: formData.library_uid
        })
        chapterList.length = 0
        if (res?.lists && Array.isArray(res.lists)) {
            chapterList.push(...res.lists)
        }
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
        ElMessage.error('获取标签失败')
    }
}

// 初始化数据
fetchLabelList()
fetchChapterList()
</script>

<style scoped>
.custom-input :deep(.el-input__inner) {
    border-color: #67c23a !important;
    border-width: 2px;
}

.custom-input :deep(.el-input__inner:focus) {
    border-color: #409eff !important;
    box-shadow: 0 0 4px rgba(64, 158, 255, 0.3);
}
</style>
