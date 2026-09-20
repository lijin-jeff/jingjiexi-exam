<template>
    <!-- 多选题 -->
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
                    clearable
                    multiple
                    placeholder="请选择章节知识点（非必选）"
                    v-model="formData.knowledge_uid"
                    style="width: 100%"
                >
                    <el-option
                        v-for="item in knowledgeList"
                        :key="item.uid"
                        :value="item.uid"
                        :label="item.title"
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
                        :controls-position="'right'"
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
                        :controls-position="'right'"
                        style="width: 100%"
                        v-model="formData.score"
                        clearable
                        placeholder="请输入"
                    />
                    <div class="form-tips">默认为2，可以为2-100之间的小数（如2.0、2.5、3.0）。</div>
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
                <editor v-model="formData.commentaries" :height="300" width="100%"  />
            </el-form-item>
            <el-form-item label="答案选项">
                <div v-for="(item, index) in formData.option" :key="item.check" class="with100">
                    <div>
                        <!-- 移除el-checkbox-group上错误的:label属性 -->
                        <el-checkbox-group v-model="formData.answer">
                            <div class="" style="width: 100%">
                                <div class="padding-right20 display-flex-start-center">
                                    <div class="padding-right">
                                        <el-checkbox
                                            :value="item.check"
                                            :label="item.check"
                                            size="large"
                                            name="option_check"
                                        />
                                    </div>
                                    <div class="display-flex-start-center">
                                        <el-button
                                            type="danger"
                                            icon="Delete"
                                            link
                                            @click.stop="deleteOption(index)"
                                            >删除选项
                                        </el-button>
                                    </div>
                                </div>
                            </div>
                        </el-checkbox-group>
                    </div>
                    <!-- 编辑器不能嵌套到radio-group内，否则编辑器内的内容不会被显示-->
                    <div class="margin-top10">
                        <exam-editor
                            v-model="formData.option[index].title"
                            :height="150"
                            width="100%"
                        />
                    </div>
                </div>
                <div class="display-flex-start-center">
                    <el-button type="success" icon="Plus" link @click="addOption"
                        >添加选项
                    </el-button>
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
const emit = defineEmits(['success'])
const props = withDefaults(defineProps<Props>(), {
    isCopy: false
})
// 定义选项类型
interface OptionItem {
    check: string
    title: string
    is_check: boolean
}

const formData = reactive({
    id: props.id || 0,
    library_uid: props.libraryUid,
    title: '',
    integral: 0,
    score: 2,
    analysis: '',
    commentaries: '',
    is_show: 1,
    exam_type: 2,
    sort: 0,
    exam_level: 1,
    chapter_uid: '',
    knowledge_uid: [] as string[],
    label_uid: [] as string[],
    selectAnswer: '',
    answer: [] as string[],
    option: [
        {
            check: ExamOptionCheck.A as unknown as string,
            title: '请输入选项值',
            is_check: false
        }
    ] as OptionItem[]
})
const { dictData } = useDictData('show_status,exam_level')
const chapterList = reactive<any[]>([])
const knowledgeList = reactive<any[]>([])
const labelList = reactive<any[]>([])
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
        {
            type: 'number',
            message: '请输入0-100之间的数字',
            min: 0,
            max: 100
        }
    ],
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
        },
        {
            validator: (rule: any, value: string[], callback: any) => {
                if (value.length < 1) {
                    callback(new Error('请选勾选题答案'))
                } else {
                    callback()
                }
            },
            trigger: ['blur', 'change']
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

watch(
    () => formData.answer,
    (newVal) => {
        // 同步更新每个选项的is_check状态
        formData.option.forEach((option) => {
            option.is_check = newVal.includes(option.check)
        })
    },
    { deep: true }
)

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
    console.log('saveForm', formData)
    // await formRef.value?.validate()
    const data = { ...formData } as any
    await formRef.value?.validate(async (valid: boolean) => {
        if (valid) {
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

            // 根据是否是复制操作和是否存在id来决定调用哪个API方法
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
        } else {
            console.log('error submit!!', data)
        }
    })
}

const addOption = () => {
    const enumKeys = Object.keys(ExamOptionCheck).filter((k) => isNaN(Number(k)))
    const lastIndex = formData.option.length
    if (enumKeys[lastIndex] !== undefined) {
        formData.option.push({
            check: enumKeys[lastIndex] as string,
            title: '请输入选项值',
            is_check: false
        })
    }
}
const deleteOption = (index: number) => {
    formData.option.splice(index, 1)
    rebuildOption()
}

const rebuildOption = () => {
    //console.log('rebuildOption', formData.option)
    const enumKeys = Object.keys(ExamOptionCheck).filter((k) => isNaN(Number(k)))
    const option: OptionItem[] = []
    for (let i = 0; i < formData.option.length; i++) {
        option.push({
            title: formData.option[i].title,
            check: enumKeys[i] as string,
            is_check: formData.option[i].is_check
        })
    }
    formData.option = option
}

const fetchQuestionDetail = async () => {
    try {
        const res = await apiTenantExamQuestionDetail({ id: props.id })

        // 处理标签数据
        let labelUid = res.label_uid || ''
        if (typeof labelUid === 'string') {
            labelUid = labelUid.split(',').map((item: string) => item.trim())
        } else if (!Array.isArray(labelUid)) {
            labelUid = []
        }

        // 处理知识点数据
        let knowledgeUid = res.knowledge_uid || []
        if (typeof knowledgeUid === 'string') {
            knowledgeUid = knowledgeUid.split(',').map((item: string) => item.trim())
        } else if (!Array.isArray(knowledgeUid)) {
            knowledgeUid = []
        }

        // 根据is_check状态构建选中数组
        const answer = res.option
            .filter((item: any) => item.is_check)
            .map((item: any) => item.check)

        // 同步选项选中状态
        const option = res.option.map((item: any) => ({
            check: item.check as string,
            title: item.title,
            is_check: answer.includes(item.check)
        }))

        // 合并数据，确保数值类型正确
        Object.assign(formData, {
            ...res,
            answer,
            selectAnswer: res.answer,
            commentaries: res.commentaries || '',
            label_uid: labelUid,
            knowledge_uid: knowledgeUid,
            option,
            // 确保积分和其他数值字段为数字类型
            integral: Number(res.integral) || 0,
            score: Number(res.score) || 2,
            sort: Number(res.sort) || 0,
            exam_level: Number(res.exam_level) || 1,
            is_show: Number(res.is_show) || 1
        })

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

const fetchChapterList = async () => {
    apiTenantExamChapterTree({ library_uid: formData.library_uid }).then((res) => {
        Object.assign(chapterList, res.lists)
        //console.log(chapterList)
    })
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

// 统一异步数据获取方法
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
            res.lists.forEach((item: { title?: string; uid?: string }) => {
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
