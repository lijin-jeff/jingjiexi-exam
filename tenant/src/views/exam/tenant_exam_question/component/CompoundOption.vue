<template>
    <!-- 案例题 -->
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
                        :key="item.value"
                        :value="item.value"
                        :label="item.label"
                    >
                    </el-option>
                </el-select>
            </el-form-item>
            <el-form-item label="试题材料" prop="title">
                <exam-editor v-model="formData.title" :height="200" width="100%" />
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
                        :step="1"
                        style="width: 100%"
                        v-model="formData.score"
                        clearable
                        placeholder="请输入"
                        disabled
                    />
                    <div class="form-tips">
                        默认为2， 可以为2-100之间的小数（如2.0、2.5、3.0）。根据子试题分值汇总计算。
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
            <el-form-item label="子项试题">
                <div
                    v-for="(item, index) in formData.option"
                    :key="item.check || index"
                    class="with100 margin-bottom20 border p-4 rounded"
                >
                    <!-- 子试题标题和操作按钮 -->
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-base font-medium">子试题 {{ index + 1 }}</h4>
                        <div class="display-flex-start-center">
                            <el-button type="success" icon="Plus" link @click="addQuestion"
                                >添加子试题
                            </el-button>
                            <el-button
                                v-if="formData.option.length > 1"
                                type="danger"
                                icon="Delete"
                                link
                                @click="deleteQuestion(index)"
                                >删除试题
                            </el-button>
                        </div>
                    </div>

                    <!-- 子试题类型选择 -->
                    <el-form-item label="子试题类型">
                        <el-radio-group
                            placeholder="请选择"
                            v-model="subQuestionTypes[index]"
                            @change="() => handleQuestionTypeChange(index)"
                        >
                            <el-radio
                                v-for="(typeItem, typeIndex) in (dictData.exam_type || []).filter(
                                    (i: any) => {
                                        const value =
                                            typeof i.value === 'string'
                                                ? parseInt(i.value)
                                                : i.value
                                        return value !== 6
                                    }
                                )"
                                :key="typeIndex"
                                :value="parseInt(typeItem.value)"
                                :label="parseInt(typeItem.value)"
                            >
                                {{ typeItem.name }}
                            </el-radio>
                        </el-radio-group>
                    </el-form-item>

                    <!-- 子试题题干编辑器 -->
                    <div class="margin-top10">
                        <exam-editor
                            v-model="formData.option[index].title"
                            :height="150"
                            width="100%"
                            placeholder="请输入子试题题干"
                        />
                    </div>
                    <!-- 子试题分值，默认1分 -->
                    <div class="margin-left50 mt-3">
                        <div class="text-sm text-gray-500 mb-2">子试题分值:</div>
                        <div style="width: 100%">
                            <el-input-number
                                :min="1"
                                :step="0.5"
                                style="width: 100%"
                                v-model="formData.option[index].childrenScore"
                                clearable
                                placeholder="请输入"
                                @change="calculateTotalScore"
                            />
                            <div class="form-tips">
                                默认为1，可以为1-100之间的小数（如1.0、1.5、2.0、2.5）。
                            </div>
                        </div>
                    </div>

                    <!-- 子试题选项或答案区域 - 根据题型动态显示 -->
                    <div class="margin-left50 mt-3">
                        <!-- 单选题 -->
                        <div v-if="subQuestionTypes[index] === 1">
                            <div class="text-sm text-gray-500 mb-2">单选题选项设置:</div>
                            <div
                                v-for="(childrenItem, childrenIndex) in item.children"
                                :key="childrenItem.check"
                                class="with100 mb-3 p-2 bg-gray-50 rounded"
                            >
                                <div class="flex items-start">
                                    <div class="mt-1 mr-3">
                                        <el-radio-group
                                            v-model="formData.option[index].selectedAnswer"
                                        >
                                            <el-radio
                                                :value="childrenItem.check"
                                                :label="childrenItem.check"
                                                size="large"
                                                name="option_check"
                                                @change="optionCheck(index)"
                                            />
                                        </el-radio-group>
                                    </div>
                                    <div class="flex-1">
                                        <exam-editor
                                            v-model="childrenItem.title"
                                            :height="120"
                                            width="100%"
                                            placeholder="请输入选项内容"
                                        />
                                    </div>
                                    <div class="flex items-center ml-2">
                                        <el-button
                                            type="danger"
                                            icon="Delete"
                                            link
                                            @click="deleteOption(index, childrenIndex)"
                                            :disabled="item.children.length <= 1"
                                            >删除选项
                                        </el-button>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <el-button
                                    type="primary"
                                    size="small"
                                    @click="addOption(index)"
                                    :disabled="item.children.length >= 5"
                                    >添加选项
                                </el-button>
                            </div>
                        </div>

                        <!-- 多选题 -->
                        <div v-else-if="subQuestionTypes[index] === 2">
                            <div class="text-sm text-gray-500 mb-2">多选题选项设置:</div>
                            <div
                                v-for="(childrenItem, childrenIndex) in item.children"
                                :key="childrenItem.check"
                                class="with100 mb-3 p-2 bg-gray-50 rounded"
                            >
                                <div class="flex items-start">
                                    <!-- 将checkbox移到group外部，但保持原有的数据绑定 -->
                                    <el-checkbox-group
                                        v-model="formData.option[index].selectedAnswers"
                                        style="display: contents"
                                    >
                                        <div class="mt-1 mr-3">
                                            <el-checkbox
                                                :value="childrenItem.check"
                                                :label="childrenItem.check"
                                                size="large"
                                                name="option_check"
                                            />
                                        </div>
                                    </el-checkbox-group>

                                    <!-- 将编辑器放在checkbox-group外部 -->
                                    <div class="flex-1">
                                        <exam-editor
                                            v-model="childrenItem.title"
                                            :height="120"
                                            width="100%"
                                            placeholder="请输入选项内容"
                                        />
                                    </div>
                                    <div class="flex items-center ml-2">
                                        <el-button
                                            type="danger"
                                            icon="Delete"
                                            link
                                            @click="deleteOption(index, childrenIndex)"
                                            :disabled="item.children.length <= 1"
                                            >删除选项
                                        </el-button>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <el-button
                                    type="primary"
                                    size="small"
                                    @click="addOption(index)"
                                    :disabled="item.children.length >= 5"
                                    >添加选项
                                </el-button>
                            </div>
                        </div>

                        <!-- 判断题 -->
                        <div v-else-if="subQuestionTypes[index] === 3">
                            <div class="text-sm text-gray-500 mb-2">判断题答案设置:</div>
                            <div class="p-3 bg-gray-50 rounded">
                                <el-radio-group
                                    @change="() => optionCheck(index)"
                                    v-model="formData.option[index].judeAnswer"
                                >
                                    <div class="flex space-x-4">
                                        <el-radio
                                            :value="ExamOptionCheck.A"
                                            label="正确"
                                            size="large"
                                        />
                                        <el-radio
                                            :value="ExamOptionCheck.B"
                                            label="错误"
                                            size="large"
                                        />
                                    </div>
                                </el-radio-group>
                            </div>
                        </div>

                        <!-- 问答题 -->
                        <div
                            v-else-if="
                                subQuestionTypes[index] === 4 ||
                                parseInt(
                                    dictData.exam_type?.find((item: any) => item.name === '问答题')
                                        ?.value
                                ) === subQuestionTypes[index]
                            "
                        >
                            <div class="text-sm text-gray-500 mb-2">问答题参考答案设置:</div>
                            <div class="p-3 bg-gray-50 rounded">
                                <exam-editor
                                    v-model="formData.option[index].answerContent"
                                    :height="150"
                                    width="100%"
                                    placeholder="请输入参考答案"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </el-form-item>
            <div class="display-flex">
                <el-button type="primary" @click="saveForm">保存试题</el-button>
                <el-button type="warning" @click="resetForm">重置数据</el-button>
            </div>
        </el-form>
    </div>
</template>

<script setup lang="ts">
import type { FormInstance } from 'element-plus'
import { ElMessage } from 'element-plus'
import { onMounted, ref, reactive, watch } from 'vue'

import { apiTenantExamChapterTree } from '@/api/exam/tenant_exam_chapter'
import { apiTenantExamKnowledgeTree } from '@/api/exam/tenant_exam_knowledge'
import { apiTenantExamLabelLists } from '@/api/exam/tenant_exam_label'
import {
    apiTenantExamQuestionAdd,
    apiTenantExamQuestionDetail,
    apiTenantExamQuestionEdit
} from '@/api/exam/tenant_exam_question'
import { useDictData } from '@/hooks/useDictOptions'
import { ExamOptionCheck } from '@/utils/enums'

interface Props {
    id?: number
    libraryUid: string
    isCopy?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    isCopy: false
})
const emit = defineEmits(['success', 'reset'])

const formData = reactive({
    uid: '',
    chapter_uid: '',
    knowledge_uid: [],
    label_uid: [],
    library_uid: props.libraryUid,
    title: '',
    // 试题积分，默认0分
    integral: 0,
    score: 2,
    analysis: '',
    is_show: 1,
    sort: 0,
    exam_level: 1,
    exam_type: 6,
    selectAnswer: '',
    answer: [],
    option: [
        {
            check: '',
            title: '请输入子试题题干',
            exam_type: 4, //子试题类型，默认问答题
            // 子试题分值，默认1分
            childrenScore: 1,
            children: [
                {
                    check: ExamOptionCheck.A,
                    title: '请输入试题选项'
                }
            ],
            selectedAnswer: '', // 单选题答案
            selectedAnswers: [], // 多选题答案
            judeAnswer: '', //判断题答案
            answerContent: '' // 问答题答案内容
        }
    ],
    commentaries: ''
})

const chapterList = reactive<any[]>([])
const knowledgeList = reactive<any[]>([])
const labelList = reactive<any[]>([])
const { dictData } = useDictData('exam_level,show_status,exam_type')
const examOptionType = ref(1)
const examOptionTypeChange = (val: number) => {
    examOptionType.value = val
}
const formRef = shallowRef<FormInstance>()
const formRules = reactive<any>({
    title: [
        { required: true, message: '请输入试题题干', trigger: ['blur'] },
        { min: 5, message: '题干不能少于5个字符', trigger: ['blur'] },
        { max: 3500, message: '题干不能超过3500个字符', trigger: ['blur'] }
    ],
    is_show: [{ required: true, message: '请选择试题显示状态', trigger: ['blur'] }],
    sort: [
        { required: true, message: '请输入试题显示权重', trigger: ['blur'] },
        { type: 'number', min: 0, max: 9999, message: '权重范围为0-9999', trigger: ['blur'] }
    ],
    // 试题积分，默认0分
    integral: [
        { required: true, message: '请输入试题积分', trigger: ['blur'] },
        { type: 'number', min: 0, max: 100, message: '积分范围为0-100', trigger: ['blur'] }
    ],
    score: [
        { required: true, message: '请输入试题分值', trigger: ['blur'] },
        { type: 'number', min: 2, max: 100, message: '分值范围为2-100', trigger: ['blur'] }
    ],
    exam_level: [
        { required: true, message: '请勾选试题难度', trigger: ['blur'] },
        { type: 'number', min: 1, max: 5, message: '难度范围为1-5', trigger: ['blur'] }
    ],
    chapter_uid: [{ required: true, message: '请选择试题章节', trigger: ['change'] }],
    analysis: [
        { required: false, message: '请输入答案解析', trigger: ['blur'] },
        { max: 2000, message: '答案解析不能超过2000个字符', trigger: ['blur'] }
    ]
})

watch(
    () => props.id,
    (newValue, oldValue) => {
        console.log('props.id变化 - 新值:', newValue, '旧值:', oldValue)
        if (newValue !== oldValue) {
            if (newValue) {
                // 清除之前的数据
                subQuestionTypes.value = []
                // 重新获取数据
                fetchQuestionDetail()
            } else {
                // 当没有id时，重置表单到初始状态
                resetForm()
            }
        }
    },
    { immediate: true } // 立即执行
)

const addOption = (index: number) => {
    try {
        // 确保索引有效
        if (index < 0 || index >= formData.option.length) return

        const enumKeys = (Object.keys(ExamOptionCheck) || []).filter((k) => isNaN(Number(k)))
        const lastIndex = formData.option[index].children.length

        // 确保选项数量不超过5个
        if (lastIndex >= 5) {
            ElMessage.warning('选项数量不能超过5个')
            return
        }

        if (enumKeys[lastIndex] !== undefined) {
            formData.option[index].children.push({
                check: ExamOptionCheck[enumKeys[lastIndex] as keyof typeof ExamOptionCheck],
                title: '请输入选项内容'
            })
        }
    } catch (error) {
        console.error('添加选项出错:', error)
    }
}
// 删除选项
const deleteOption = (index: number, childrenIndex: number) => {
    try {
        // 确保索引有效
        if (
            index < 0 ||
            index >= formData.option.length ||
            !formData.option[index].children ||
            childrenIndex < 0 ||
            childrenIndex >= formData.option[index].children.length
        ) {
            return
        }

        // 确保至少保留一个选项（除了问答题）
        const currentType = subQuestionTypes.value[index]
        if (currentType === 1 || currentType === 2) {
            // 单选或多选题
            if (formData.option[index].children.length > 1) {
                const deletedOption = formData.option[index].children[childrenIndex].check
                formData.option[index].children.splice(childrenIndex, 1)
                rebuildOption()

                // 重置可能受影响的答案
                if (currentType === 1 && formData.option[index].selectedAnswer === deletedOption) {
                    formData.option[index].selectedAnswer =
                        formData.option[index].children[0]?.check || ''
                } else if (currentType === 2) {
                    formData.option[index].selectedAnswers = formData.option[
                        index
                    ].selectedAnswers.filter((ans) => ans !== deletedOption)
                }
            } else {
                ElMessage.warning('至少保留一个选项')
            }
        } else if (currentType === 3) {
            // 判断题
            ElMessage.warning('判断题不能删除选项')
        }
    } catch (error) {
        console.error('删除选项出错:', error)
        ElMessage.error('删除失败，请重试')
    }
}

const rebuildOption = () => {
    try {
        const enumKeys = (Object.keys(ExamOptionCheck) || []).filter((k) => isNaN(Number(k)))
        for (let i = 0; i < formData.option.length; i++) {
            if (!formData.option[i].children || !Array.isArray(formData.option[i].children)) {
                formData.option[i].children = []
                continue
            }

            const option = []
            for (let j = 0; j < formData.option[i].children.length; j++) {
                if (enumKeys[j] !== undefined) {
                    option.push({
                        title: formData.option[i].children[j].title || '',
                        check: ExamOptionCheck[enumKeys[j] as keyof typeof ExamOptionCheck]
                    })
                }
            }
            formData.option[i].children = option

            // 如果是单选题，确保selectedAnswer引用的是存在的选项
            if (subQuestionTypes.value[i] === 1) {
                const validOptions = formData.option[i].children.map((opt) => opt.check)
                const selectedAnswer = formData.option[i].selectedAnswer as ExamOptionCheck
                if (!validOptions.includes(selectedAnswer)) {
                    formData.option[i].selectedAnswer = validOptions[0] || ''
                }
            }
            // 如果是多选题，确保selectedAnswers只包含存在的选项
            else if (subQuestionTypes.value[i] === 2) {
                const validOptions = formData.option[i].children.map((opt) => opt.check)
                formData.option[i].selectedAnswers = formData.option[i].selectedAnswers.filter(
                    (ans) => validOptions.includes(ans)
                )
            }
        }
    } catch (error) {
        console.error('重建选项出错:', error)
    }
}

// 选项检查（单选、多选、判断和问答）
const optionCheck = (index: number) => {
    // 确保索引有效
    if (index < 0 || index >= formData.option.length) return

    // 确保数据格式正确
    if (subQuestionTypes.value[index] === 1) {
        // 单选题的答案处理
        formData.option[index].check = formData.option[index].selectedAnswer
    } else if (subQuestionTypes.value[index] === 2) {
        // 多选题的答案处理
        formData.option[index].check = Array.isArray(formData.option[index].selectedAnswers)
            ? formData.option[index].selectedAnswers.join(',')
            : ''
    } else if (subQuestionTypes.value[index] === 3) {
        // 判断题的答案处理
        formData.option[index].check = formData.option[index].judeAnswer || ''
    } else if (subQuestionTypes.value[index] === 4) {
        // 问答题的答案处理
        formData.option[index].check = formData.option[index].answerContent || ''
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
        fetchKnowledgeList()
    }
}

// 获取知识点列表
const fetchKnowledgeList = async () => {
    try {
        const res = await apiTenantExamKnowledgeTree({
            chapter_uid: formData.chapter_uid
        })
        // 清空并重新填充知识点列表
        knowledgeList.splice(0, knowledgeList.length)
        // 兼容API返回的数据格式，知识点数据可能在res.data或res.lists中
        const lists = res?.data || res?.lists || res || []
        if (Array.isArray(lists)) {
            lists.forEach((item) => {
                knowledgeList.push(item)
            })
        }
    } catch (error) {
        console.error('获取知识点失败:', error)
        ElMessage.error('获取知识点列表失败，请重试')
    }
}

// 处理知识点选择变化
const handleKnowledgeChange = (value: any) => {
    formData.knowledge_uid = value || []
}

// 处理标签选择变化
const handleLabelChange = (value: any) => {
    // 标签值已通过v-model自动绑定到formData.label_uid
    console.log('选择的标签:', value)
}

// 为每个子试题创建独立的类型状态
const subQuestionTypes = ref<number[]>([4])

// 处理题型变化
const handleQuestionTypeChange = (index: number) => {
    const newType = subQuestionTypes.value[index]

    // 确保索引有效
    if (index < 0 || index >= formData.option.length) return

    // 清空当前题型相关的答案数据
    if (newType === 1) {
        // 单选题
        formData.option[index].selectedAnswers = []
        formData.option[index].answerContent = ''
        formData.option[index].judeAnswer = ''
        formData.option[index].exam_type = 1
        // 确保单选题至少有一个选项
        if (!formData.option[index].children || formData.option[index].children.length === 0) {
            formData.option[index].children = [
                { check: ExamOptionCheck.A, title: '请输入试题选项' }
            ]
        }
    } else if (newType === 2) {
        // 多选题
        formData.option[index].selectedAnswer = ''
        formData.option[index].answerContent = ''
        formData.option[index].judeAnswer = ''
        formData.option[index].exam_type = 2
        // 确保多选题至少有一个选项
        if (!formData.option[index].children || formData.option[index].children.length === 0) {
            formData.option[index].children = [
                { check: ExamOptionCheck.A, title: '请输入试题选项' }
            ]
        }
    } else if (newType === 3) {
        // 判断题
        formData.option[index].selectedAnswer = ''
        formData.option[index].selectedAnswers = []
        formData.option[index].answerContent = ''
        formData.option[index].exam_type = 3
        // 对于判断题，使用固定的两个选项
        formData.option[index].children = [
            { check: ExamOptionCheck.A, title: '正确' },
            { check: ExamOptionCheck.B, title: '错误' }
        ]
    } else if (newType === 4) {
        // 问答题
        formData.option[index].selectedAnswer = ''
        formData.option[index].selectedAnswers = []
        formData.option[index].judeAnswer = ''
        formData.option[index].exam_type = 4
        formData.option[index].children = []
    }
}

// 添加子试题
const addQuestion = () => {
    // 限制最大子试题数量
    if (formData.option.length >= 10) {
        ElMessage.warning('子试题数量不能超过10个')
        return
    }

    formData.option.push({
        check: '',
        title: '请输入子试题题干',
        exam_type: 4,
        children: [{ check: ExamOptionCheck.A, title: '请输入试题选项' }],
        selectedAnswer: '',
        selectedAnswers: [],
        // 子试题分值，默认1分
        childrenScore: 1,
        judeAnswer: '',
        answerContent: ''
    })
    subQuestionTypes.value.push(4) // 默认添加问答题
    // 添加后重新计算总分值
    calculateTotalScore()
}

// 计算所有子试题分值总和并更新主试题分值
const calculateTotalScore = () => {
    let total = 0
    formData.option.forEach((item) => {
        // 确保childrenScore是数字，默认为0
        const score = Number(item.childrenScore) || 0
        total += score
    })
    formData.score = total
}

// 删除子试题
const deleteQuestion = (index: number) => {
    try {
        if (formData.option.length > 1) {
            formData.option.splice(index, 1)
            subQuestionTypes.value.splice(index, 1)
            ElMessage.success('子试题已删除')
            // 删除后重新计算总分值
            calculateTotalScore()
        } else {
            ElMessage.warning('至少保留一个子试题')
        }
    } catch (error) {
        console.error('删除子试题出错:', error)
        ElMessage.error('删除失败，请重试')
    }
}

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

// 保存表单
const saveForm = async () => {
    try {
        // 首先确保所有子试题的答案都已正确设置到check字段，并更新exam_type
        formData.option.forEach((item, index) => {
            // 根据subQuestionTypes更新exam_type
            item.exam_type = subQuestionTypes.value[index]
            item.childrenScore = Number(item.childrenScore ?? 1)
            if (subQuestionTypes.value[index] === 4) {
                // 对于问答题，直接在formData中设置处理后的answerContent作为check值
                // 确保answerContent存在且不为空字符串
                const content = item.answerContent || ''
                // 移除HTML标签，包括自闭合标签
                const cleanContent = content.replace(/<[^>]*>/g, '').trim()
                // 直接在formData中设置，确保原始数据也被更新
                item.check = cleanContent || ''
            } else {
                // 其他题型使用原始的optionCheck逻辑
                optionCheck(index)
            }
        })

        // 创建一个深拷贝，确保所有嵌套对象都被正确复制
        const data = JSON.parse(JSON.stringify(formData))

        // 不再强制验证子试题答案是否已设置
        // 保留对check字段的处理，但不阻止保存
        // 用户可以保存未设置答案的子试题

        // 去除editor和exam-editor组件输入数据的首尾空格和空行
        data.title = trimContent(data.title)
        data.analysis = trimContent(data.analysis)
        data.commentaries = trimContent(data.commentaries)

        // 处理子试题内容
        if (Array.isArray(data.option)) {
            data.option = data.option.map((option: any) => {
                // 处理子试题题干
                const processedOption = {
                    ...option,
                    title: trimContent(option.title),
                    answerContent: trimContent(option.answerContent)
                }

                // 处理子试题选项
                if (Array.isArray(processedOption.children)) {
                    processedOption.children = processedOption.children.map((child: any) => ({
                        ...child,
                        title: trimContent(child.title)
                    }))
                }

                return processedOption
            })
        }

        await formRef.value?.validate((valid: boolean) => {
            if (valid) {
                // 处理标签数据
                if (Array.isArray(data.label_uid)) {
                    data.label_uid = data.label_uid.join(',')
                }

                // 处理试题数据，设置选中标记并生成答案数组
                if (data.option && data.option.length > 0) {
                    data.option.forEach((item: any) => {
                        // 设置选中标记
                        item.is_check = true // 对于案例题，所有子试题都是答案的一部分
                    })

                    data.answer = data.option.map((item: any) => item.check)
                }

                // 保存数据 - 根据是否是复制操作和是否有id判断是编辑还是添加
                let savePromise
                if (props.isCopy) {
                    // 复制操作，强制调用新增API
                    data.id = 0
                    savePromise = apiTenantExamQuestionAdd(data)
                } else if (props.id) {
                    // 编辑操作，保留原id
                    data.id = props.id
                    savePromise = apiTenantExamQuestionEdit(data)
                } else {
                    // 新增操作，id设为0
                    data.id = 0
                    savePromise = apiTenantExamQuestionAdd(data)
                }

                savePromise
                    .then(() => {
                        ElMessage.success(
                            props.isCopy ? '复制成功' : props.id ? '编辑成功' : '添加成功'
                        )
                        emit('success', data)
                    })
                    .catch((error) => {
                        console.error('保存失败:', error)
                        ElMessage.error('保存失败，请重试')
                    })
            }
        })
    } catch (error) {
        console.error('保存表单出错:', error)
        ElMessage.error('保存过程中出现错误，请重试')
    }
}

// 重置表单
const resetForm = () => {
    formRef.value?.resetFields()
    // 重置子试题
    formData.option = [
        {
            check: '',
            title: '请输入子试题题干',
            exam_type: 4,
            // 子试题分值，默认1分
            childrenScore: 1,
            children: [{ check: ExamOptionCheck.A, title: '请输入试题选项' }],
            selectedAnswer: '',
            selectedAnswers: [],
            judeAnswer: '',
            answerContent: ''
        }
    ]
    subQuestionTypes.value = [4]
    emit('reset')
}

// 获取试题详情并初始化数据
const fetchQuestionDetail = async () => {
    try {
        if (!props.id) {
            console.warn('没有提供试题ID')
            return
        }

        console.log('正在获取试题详情，ID:', props.id)
        const res = await apiTenantExamQuestionDetail({ id: props.id })
        console.log('获取到的试题详情数据:', res)

        // 确保res是有效的对象
        if (!res || typeof res !== 'object') {
            throw new Error('获取的试题详情数据格式无效')
        }

        // 重置formData，然后再赋值
        Object.keys(formData).forEach((key) => {
            if (key !== 'option' && key !== 'answer') {
                ;(formData as any)[key] = ''
            }
        })

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

        // 确保option是数组
        if (!Array.isArray(formData.option)) {
            formData.option = []
        }

        // 初始化每个子试题的类型和答案数据
        subQuestionTypes.value = []
        if (Array.isArray(formData.option)) {
            formData.option.forEach((item: any, index: number) => {
                // 确保childrenScore有默认值
                if (item.childrenScore === undefined) {
                    item.childrenScore = 1
                }

                // 优先使用item.exam_type来设置子试题类型
                let examType = Number(item.exam_type) || 1
                // 更新item.exam_type的值，使其与examType一致
                item.exam_type = examType
                subQuestionTypes.value[index] = examType

                // 根据题型初始化答案数据
                if (examType === 1) {
                    // 单选题
                    item.selectedAnswer = item.check || ''
                    item.selectedAnswers = []
                    item.judeAnswer = ''
                    item.answerContent = ''
                    // 确保单选题至少有一个选项
                    if (!item.children || item.children.length === 0) {
                        item.children = [
                            { check: ExamOptionCheck.A, title: '请输入试题选项' }
                        ]
                    }
                } else if (examType === 2) {
                    // 多选题
                    item.selectedAnswer = ''
                    item.selectedAnswers = item.check ? item.check.split(',') : []
                    item.judeAnswer = ''
                    item.answerContent = ''
                    // 确保多选题至少有一个选项
                    if (!item.children || item.children.length === 0) {
                        item.children = [
                            { check: ExamOptionCheck.A, title: '请输入试题选项' }
                        ]
                    }
                } else if (examType === 3) {
                    // 判断题
                    item.selectedAnswer = ''
                    item.selectedAnswers = []
                    item.judeAnswer = item.check || ''
                    item.answerContent = ''
                    // 对于判断题，使用固定的两个选项
                    item.children = [
                        { check: ExamOptionCheck.A, title: '正确' },
                        { check: ExamOptionCheck.B, title: '错误' }
                    ]
                } else if (examType === 4) {
                    // 问答题
                    item.selectedAnswer = ''
                    item.selectedAnswers = []
                    item.judeAnswer = ''
                    item.answerContent = item.check || ''
                    // 问答题不需要选项，清空children数组
                    item.children = []
                }
            })
        }
        // 确保subQuestionTypes数组的长度与formData.option数组的长度一致
        subQuestionTypes.value = subQuestionTypes.value.slice(0, formData.option.length)

        console.log('初始化后的subQuestionTypes:', subQuestionTypes.value)
        // 初始化后计算总分值
        calculateTotalScore()
    } catch (error) {
        console.error('获取试题详情失败:', error)
        ElMessage.error('获取试题详情失败，请重试')
    }
}
// 其他相关函数也需要进行相应调整以支持新的题型
const fetchLabelList = async () => {
    try {
        const res = await apiTenantExamLabelLists({
            library_uid: formData.library_uid
        })
        // 清空并重新填充标签列表
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
onMounted(async () => {
    try {
        await Promise.all([fetchLabelList(), fetchChapterList()])

        // 初始化第一个子试题的选项检查
        if (formData.option.length > 0) {
            optionCheck(0)
        }

        //判断如果是编辑，则加载试题详情
        if (props.id) {
            fetchQuestionDetail()
        } else {
            // 新建试题时，初始化总分值
            calculateTotalScore()
            // 初始化subQuestionTypes，确保与formData.option中的exam_type一致
            subQuestionTypes.value = formData.option.map((item: any) => Number(item.exam_type) || 1)
        }
    } catch (error) {
        console.error('组件初始化失败:', error)
    }
})
</script>

<style scoped>
/* 可添加自定义样式 */
.flex {
    display: flex;
}
.items-center {
    align-items: center;
}
.justify-between {
    justify-content: space-between;
}
.items-start {
    align-items: flex-start;
}
.mb-2 {
    margin-bottom: 8px;
}
.mb-3 {
    margin-bottom: 12px;
}
.mt-1 {
    margin-top: 4px;
}
.mt-3 {
    margin-top: 12px;
}
.mr-3 {
    margin-right: 12px;
}
.text-gray-500 {
    color: #606266;
}
.text-sm {
    font-size: 13px;
}
.text-base {
    font-size: 14px;
}
.font-medium {
    font-weight: 500;
}
.bg-gray-50 {
    background-color: #f5f7fa;
}
.p-2 {
    padding: 8px;
}
.p-3 {
    padding: 12px;
}
.p-4 {
    padding: 16px;
}
.rounded {
    border-radius: 4px;
}
.border {
    border: 1px solid #ebeef5;
}
.space-x-4 > * {
    margin-right: 16px;
}
.space-x-4 > *:last-child {
    margin-right: 0;
}
</style>
