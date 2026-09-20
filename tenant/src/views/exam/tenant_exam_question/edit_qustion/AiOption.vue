<!--
 * @description AI 试题组件，用于处理 AI 相关的试题编辑和管理功能。
 * @author 系统自动生成备注
 * @date 当前日期
-->
<template>
    <div class="ai-option-container">
        <!-- 试题设置区域 -->
        <el-card class="settings-card" shadow="never">
            <template #header>
                <div class="card-header">
                    <span>试题设置</span>
                </div>
            </template>
            <el-form :model="formData" label-width="100px" size="default">
                <!-- 章节选择 -->
                <el-form-item label="章节：">
                    <el-cascader
                        v-model="selectedChapterPath"
                        :options="chapterOptions"
                        :props="{ label: 'title', value: 'uid', children: 'children' }"
                        placeholder="请选择章节"
                        clearable
                        style="width: 100%"
                        @change="onChapterChange"
                    />
                </el-form-item>

                <!-- 难度设置 -->
                <el-form-item label="难度：">
                    <el-select v-model="formData.exam_level" placeholder="请选择难度" style="width: 100%">
                        <el-option
                            v-for="item in difficultyList"
                            :key="item.value"
                            :label="item.name"
                            :value="item.value"
                        />
                    </el-select>
                </el-form-item>

                <!-- 分值设置 -->
                <el-form-item label="分值：">
                    <el-input-number
                        v-model="formData.score"
                        :min="0"
                        :max="100"
                        :step="0.5"
                        style="width: 100%"
                    />
                </el-form-item>

                <!-- 知识点设置 -->
                <el-form-item label="知识点：">
                    <el-select
                        v-model="formData.knowledge_uid"
                        multiple
                        collapse-tags
                        collapse-tags-tooltip
                        placeholder="请选择知识点"
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

                <!-- 标签设置 -->
                <el-form-item label="标签：">
                    <el-select
                        v-model="formData.label_uid"
                        multiple
                        collapse-tags
                        collapse-tags-tooltip
                        placeholder="请选择标签"
                        style="width: 100%"
                    >
                        <el-option
                            v-for="item in labelList"
                            :key="item.uid"
                            :label="item.title"
                            :value="item.uid"
                        />
                    </el-select>
                </el-form-item>

                <!-- 显示状态 -->
                <el-form-item label="显示状态：">
                    <el-switch
                        v-model="formData.is_show"
                        :active-value="1"
                        :inactive-value="0"
                        active-text="显示"
                        inactive-text="隐藏"
                    />
                </el-form-item>
            </el-form>
        </el-card>

        <!-- AI 生成区域 -->
        <el-card class="ai-generate-card" shadow="never">
            <template #header>
                <div class="card-header">
                    <span>AI 生成试题</span>
                    <div>
                        <el-button type="primary" link @click="openPromptDialog">
                            <el-icon><Edit /></el-icon>编辑提示词
                        </el-button>
                        <el-button type="success" icon="ChatDotRound" @click="dialogVisible = true">
                            调用AI生成试题
                        </el-button>
                    </div>
                </div>
            </template>

            <!-- 图片上传 -->
            <div class="upload-section">
                <el-upload
                    class="upload-area"
                    drag
                    action="#"
                    :auto-upload="false"
                    :on-change="handleImageChange"
                    :show-file-list="false"
                    accept="image/*"
                >
                    <el-icon class="el-icon--upload"><upload-filled /></el-icon>
                    <div class="el-upload__text">拖拽图片到此处或 <em>点击上传</em></div>
                    <template #tip>
                        <div class="el-upload__tip">支持识别单选题、多选题、判断题、填空题、问答题</div>
                    </template>
                </el-upload>

                <!-- 已上传图片预览 -->
                <div v-if="imageUrl" class="image-preview">
                    <el-image :src="imageUrl" fit="contain" style="width: 200px; height: 200px" />
                    <div class="image-actions">
                        <el-button type="danger" size="small" @click="deleteImage">删除图片</el-button>
                        <el-button
                            type="primary"
                            size="small"
                            :loading="recognizing"
                            @click="recognizeQuestion"
                        >
                            {{ recognizing ? '识别中...' : '开始识别' }}
                        </el-button>
                    </div>
                </div>
            </div>

            <!-- 编辑区和预览区 -->
            <div class="edit-preview-area">
                <div class="edit-section">
                    <div class="section-title">
                        <el-button type="primary" link disabled>编辑区</el-button>
                    </div>
                    <el-input
                        type="textarea"
                        v-model="formData.content"
                        :autosize="{ minRows: 20, maxRows: 20 }"
                        :placeholder="placeholder"
                        resize="none"
                        :show-word-limit="true"
                        maxlength="65535"
                    />
                </div>
                <div class="preview-section">
                    <div class="section-title">
                        <el-button type="primary" link disabled>预览区</el-button>
                    </div>
                    <el-input
                        type="textarea"
                        v-model="formData.content"
                        :autosize="{ minRows: 20, maxRows: 20 }"
                        :placeholder="placeholder"
                        resize="none"
                        :show-word-limit="true"
                        maxlength="65535"
                        disabled
                    />
                </div>
            </div>
        </el-card>

        <!-- 识别结果编辑区 -->
        <el-card v-if="recognitionResult" class="result-card" shadow="never">
            <template #header>
                <div class="card-header">
                    <span>识别结果</span>
                    <el-button type="primary" @click="saveQuestion" :loading="saving">
                        {{ saving ? '保存中...' : '保存试题' }}
                    </el-button>
                </div>
            </template>

            <el-form :model="recognitionResult" label-width="100px">
                <!-- 题干 -->
                <el-form-item label="题干：">
                    <el-input
                        v-model="recognitionResult.title"
                        type="textarea"
                        :rows="3"
                        placeholder="请输入题干"
                    />
                </el-form-item>

                <!-- 题型 -->
                <el-form-item label="题型：">
                    <el-select v-model="recognitionResult.exam_type" placeholder="请选择题型">
                        <el-option label="单选题" :value="1" />
                        <el-option label="多选题" :value="2" />
                        <el-option label="判断题" :value="3" />
                        <el-option label="填空题" :value="4" />
                        <el-option label="问答题" :value="5" />
                        <el-option label="案例题" :value="6" />
                    </el-select>
                </el-form-item>

                <!-- 选项 -->
                <el-form-item v-if="[1, 2, 3].includes(recognitionResult.exam_type)" label="选项：">
                    <div
                        v-for="(option, index) in recognitionResult.options"
                        :key="option._id"
                        class="option-item"
                    >
                        <span class="option-label">{{ String.fromCharCode(65 + index) }}</span>
                        <el-input
                            v-model="option.content"
                            placeholder="请输入选项内容"
                            style="flex: 1"
                        />
                    </div>
                </el-form-item>

                <!-- 正确答案 -->
                <el-form-item label="正确答案：">
                    <el-input
                        v-model="recognitionResult.correct_answer"
                        placeholder="请输入正确答案"
                    />
                </el-form-item>

                <!-- 解析 -->
                <el-form-item label="解析：">
                    <exam-editor
                        v-model="recognitionResult.analysis"
                        :height="150"
                        width="100%"
                    />
                </el-form-item>

                <!-- 名师点评 -->
                <el-form-item label="名师点评：">
                    <exam-editor
                        v-model="recognitionResult.commentaries"
                        :height="150"
                        width="100%"
                    />
                </el-form-item>
            </el-form>
        </el-card>

        <!-- AI 生成对话框 -->
        <el-dialog
            v-model="dialogVisible"
            title="AI生成提示词"
            width="500px"
            class="custom-dialog"
        >
            <div class="dialog-content">
                <el-input
                    v-model="editingPrompt"
                    type="textarea"
                    :rows="6"
                    placeholder="请输入AI提示词内容"
                    maxlength="500"
                    show-word-limit
                />
            </div>
            <template #footer>
                <el-button @click="dialogVisible = false">取 消</el-button>
                <el-button @click="resetPrompt">重置默认</el-button>
                <el-button type="primary" @click="savePrompt">确 定</el-button>
            </template>
        </el-dialog>

        <!-- 识别结果详情对话框 -->
        <el-dialog v-model="resultDialogVisible" title="识别成功" width="500px">
            <div class="result-details">
                <p><strong>题目：</strong>{{ recognitionResultDetails.title }}</p>
                <p><strong>题型：</strong>{{ recognitionResultDetails.examTypeName }}</p>
                <p>
                    <strong>选项：</strong>
                    <el-tag :type="recognitionResultDetails.hasOptions ? 'success' : 'danger'">
                        {{ recognitionResultDetails.hasOptions ? '识别成功' : '未识别到选项' }}
                    </el-tag>
                </p>
                <p>
                    <strong>解析：</strong>
                    <el-tag :type="recognitionResultDetails.hasAnalysis ? 'success' : 'danger'">
                        {{ recognitionResultDetails.hasAnalysis ? '识别成功' : '未识别到解析' }}
                    </el-tag>
                </p>
                <p><strong>匹配知识点：</strong>{{ recognitionResultDetails.matchedKnowledgeCount }} 个</p>
                <p><strong>Token使用：</strong>输入 {{ recognitionResultDetails.tokenUsage.input_tokens }} / 输出 {{ recognitionResultDetails.tokenUsage.output_tokens }} / 总计 {{ recognitionResultDetails.tokenUsage.total_tokens }}</p>
                <p><strong>使用模型：</strong>{{ recognitionResultDetails.model }}</p>
            </div>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, watch } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { UploadFilled, Edit } from '@element-plus/icons-vue'
import type { UploadFile } from 'element-plus'

import { apiTenantExamChapterTree } from '@/api/exam/tenant_exam_chapter'
import { apiTenantExamKnowledgeLists } from '@/api/exam/tenant_exam_knowledge'
import { apiTenantExamLabelLists } from '@/api/exam/tenant_exam_label'
import { apiTenantExamQuestionAdd } from '@/api/exam/tenant_exam_question'
import { apiTenantExamAiRecognize } from '@/api/exam/tenant_exam_ai'

interface Props {
    libraryUid: string
}

const props = defineProps<Props>()

// 对话框显示状态
const dialogVisible = ref(false)
const resultDialogVisible = ref(false)

// 默认提示词
const defaultPrompt = ref(
    '请识别这张图片中的试题，提取出题型（单选题、多选题、判断题、填空题、问答题、案例题）、题干、选项（如果有）、正确答案和解析内容，以及笔记第一条的内容部分（没有笔记的话就不识别，不含用户名）。用JSON格式输出。题型字段为exam_type_name。'
)
const customPrompt = ref('')
const editingPrompt = ref('')

// 占位符文本
const placeholder = ref('AI生成将根据文本输入的格式生成试题')

// 图片相关
const imageUrl = ref('')
const imageFile = ref<File | null>(null)
const recognizing = ref(false)
const saving = ref(false)

// 章节选择
const selectedChapterPath = ref<string[]>([])
const chapterOptions = ref<any[]>([])

// 难度列表
const difficultyList = ref<any[]>([])

// 知识点列表
const knowledgeList = ref<any[]>([])

// 标签列表
const labelList = ref<any[]>([])

// 表单数据
const formData = reactive({
    chapter_uid: '',
    library_uid: props.libraryUid,
    content: '',
    exam_level: '',
    score: 1,
    integral: 0,
    knowledge_uid: [] as string[],
    label_uid: [] as string[],
    is_show: 1
})

// 识别结果
const recognitionResult = ref<any>(null)
const recognitionResultDetails = reactive({
    title: '',
    examTypeName: '',
    hasOptions: false,
    hasAnalysis: false,
    matchedKnowledgeCount: 0,
    tokenUsage: {
        input_tokens: 0,
        output_tokens: 0,
        total_tokens: 0
    },
    model: ''
})

// 选项ID计数器
let optionIdCounter = 0

// 停用词集合
const STOP_WORDS = new Set([
    '的', '了', '是', '在', '有', '和', '就', '不', '人', '都', '一', '一个', '上', '也', '很', '到', '说', '要', '去', '你', '会', '着', '没有', '看', '好', '自己', '这', '正确', '错误', '对', '错', '选项', '答案', '解析', '题干', '问题', '下列', '哪些', '哪个', '什么', '如何', '为什么', '怎样', '是否', '可能', '应该', '可以', '必须', '不能', '不是', '属于', '不属于', '包括', '不包括', '关于', '对于', '根据', '依据', '按照', '遵循', '符合', '不符合', '或者', '与', '及', '等', '其中', '主要', '重要', '关键', '因此', '所以', '因为', '由于', '虽然', '但是', '然而', '如果', '那么', '只要', '只有', '无论', '不管', '尽管', '即使', '既然', '那么', '而且', '并且', '或者', '还是', '要么', '与其', '不如', '宁可', '也不', '一边', '一边', '一方面', '另一方面', '首先', '其次', '再次', '最后', '总之', '综上所述', '由此可见', '因此可见'
])

// 监听章节选择变化
const onChapterChange = (value: any[]) => {
    if (value && value.length > 0) {
        formData.chapter_uid = value[value.length - 1]
        loadKnowledgeList()
    } else {
        formData.chapter_uid = ''
        knowledgeList.value = []
        formData.knowledge_uid = []
    }
}

// 加载章节列表
const loadChapterList = async () => {
    try {
        const res = await apiTenantExamChapterTree({ library_uid: props.libraryUid })
        if (res && res.lists) {
            chapterOptions.value = res.lists
        }
    } catch (error) {
        console.error('获取章节列表失败:', error)
    }
}

// 加载难度列表
const loadDifficultyList = async () => {
    // 从字典获取难度数据，这里简化处理
    difficultyList.value = [
        { name: '简单', value: '1' },
        { name: '中等', value: '2' },
        { name: '困难', value: '3' }
    ]
    formData.exam_level = '2' // 默认中等
}

// 加载知识点列表
const loadKnowledgeList = async () => {
    if (!formData.chapter_uid) {
        knowledgeList.value = []
        return
    }
    try {
        const res = await apiTenantExamKnowledgeLists({
            library_uid: props.libraryUid,
            chapter_uid: formData.chapter_uid
        })
        if (res && res.lists) {
            knowledgeList.value = res.lists
        }
    } catch (error) {
        console.error('获取知识点列表失败:', error)
    }
}

// 加载标签列表
const loadLabelList = async () => {
    try {
        const res = await apiTenantExamLabelLists({ library_uid: props.libraryUid })
        if (res && res.lists) {
            labelList.value = res.lists
        }
    } catch (error) {
        console.error('获取标签列表失败:', error)
    }
}

// 图片上传处理
const handleImageChange = (uploadFile: UploadFile) => {
    if (uploadFile.raw) {
        imageFile.value = uploadFile.raw
        imageUrl.value = URL.createObjectURL(uploadFile.raw)
        recognitionResult.value = null
    }
}

// 删除图片
const deleteImage = () => {
    imageUrl.value = ''
    imageFile.value = null
    recognitionResult.value = null
}

// 打开提示词编辑对话框
const openPromptDialog = () => {
    editingPrompt.value = customPrompt.value || defaultPrompt.value
    dialogVisible.value = true
}

// 保存提示词
const savePrompt = () => {
    if (!editingPrompt.value.trim()) {
        ElMessage.warning('提示词不能为空')
        return
    }
    customPrompt.value = editingPrompt.value
    localStorage.setItem('aiOptionCustomPrompt', customPrompt.value)
    dialogVisible.value = false
    ElMessage.success('保存成功')
}

// 重置提示词
const resetPrompt = () => {
    editingPrompt.value = defaultPrompt.value
    ElMessage.info('已重置为默认提示词')
}

// 识别试题
const recognizeQuestion = async () => {
    if (!imageFile.value) {
        ElMessage.warning('请先上传图片')
        return
    }

    recognizing.value = true

    try {
        // 调用服务端AI识别API
        const formDataUpload = new FormData()
        formDataUpload.append('image', imageFile.value)
        formDataUpload.append('prompt', customPrompt.value || defaultPrompt.value)
        formDataUpload.append('library_uid', props.libraryUid)

        const res = await apiTenantExamAiRecognize(formDataUpload)

        if (res && res.code === 1 && res.data) {
            parseRecognitionResult(res.data.result)

            // 更新识别详情
            recognitionResultDetails.title = recognitionResult.value?.title || '未识别到标题'
            recognitionResultDetails.examTypeName = recognitionResult.value?.exam_type_name || '未识别'
            recognitionResultDetails.hasOptions = recognitionResult.value?.options && recognitionResult.value.options.length > 0
            recognitionResultDetails.hasAnalysis = !!(recognitionResult.value?.analysis && recognitionResult.value.analysis.trim())
            recognitionResultDetails.matchedKnowledgeCount = formData.knowledge_uid.length
            recognitionResultDetails.tokenUsage = res.data.usage || { input_tokens: 0, output_tokens: 0, total_tokens: 0 }
            recognitionResultDetails.model = res.data.model || 'doubao'

            // 自动匹配知识点
            autoMatchKnowledge()

            // 显示结果对话框
            resultDialogVisible.value = true

            ElMessage.success('识别成功')
        } else {
            ElMessage.error(res?.msg || '识别失败')
        }
    } catch (error) {
        console.error('识别失败:', error)
        ElMessage.error('识别失败，请重试')
    } finally {
        recognizing.value = false
    }
}

// 解析识别结果
const parseRecognitionResult = (result: any) => {
    // 获取题干
    const questionStem = result.question_stem || result.title || result.stem || result.question || ''

    // 获取选项
    let optionsArray: any[] = []
    if (result.options) {
        if (typeof result.options === 'object' && result.options !== null && !Array.isArray(result.options)) {
            optionsArray = Object.entries(result.options).map(([key, value]: [string, any]) => ({
                letter: key,
                content: value
            }))
        } else if (Array.isArray(result.options)) {
            optionsArray = result.options.map((opt: any, index: number) => {
                if (typeof opt === 'object' && opt !== null) {
                    return {
                        letter: opt.letter || opt.key || String.fromCharCode(65 + index),
                        content: opt.content || opt.value || opt.text || ''
                    }
                }
                return {
                    letter: String.fromCharCode(65 + index),
                    content: String(opt)
                }
            })
        }
    }

    // 获取正确答案
    const correctAnswer = (result.correct_answer || result.answer || result.right_answer || result.correct || '').toUpperCase()

    // 确定题型
    let examType = 1
    let examTypeName = '单选题'

    if (optionsArray.length > 0) {
        if (optionsArray.length === 2) {
            examType = 3
            examTypeName = '判断题'
        } else if (correctAnswer.length > 1) {
            examType = 2
            examTypeName = '多选题'
        } else if (result.exam_type_name) {
            const aiExamTypeName = result.exam_type_name
            if (['单选题', '多选题', '判断题', '填空题', '问答题', '案例题'].includes(aiExamTypeName)) {
                examTypeName = aiExamTypeName
                switch (aiExamTypeName) {
                    case '单选题': examType = 1; break
                    case '多选题': examType = 2; break
                    case '判断题': examType = 3; break
                    case '填空题': examType = 4; break
                    case '问答题': examType = 5; break
                    case '案例题': examType = 6; break
                }
            }
        }
    } else {
        if (result.exam_type_name) {
            const aiExamTypeName = result.exam_type_name
            if (['填空题', '问答题', '案例题'].includes(aiExamTypeName)) {
                examTypeName = aiExamTypeName
                switch (aiExamTypeName) {
                    case '填空题': examType = 4; break
                    case '问答题': examType = 5; break
                    case '案例题': examType = 6; break
                }
            } else {
                examType = 5
                examTypeName = '问答题'
            }
        } else {
            examType = 5
            examTypeName = '问答题'
        }
    }

    // 处理判断题选项
    let processedOptions = optionsArray
    if (examType === 3) {
        if (processedOptions.length !== 2) {
            processedOptions = [
                { letter: 'A', content: '正确' },
                { letter: 'B', content: '错误' }
            ]
        }
    }

    // 为选项添加唯一ID
    processedOptions = processedOptions.map((opt: any) => ({
        ...opt,
        _id: ++optionIdCounter
    }))

    // 获取解析和点评
    const analysis = result.analysis || result.explanation || result.parse || result.solution || ''
    let commentaries = result.first_note_content || result.first_note || result.commentaries || result.note || ''

    // 如果点评为空，自动生成
    if (!commentaries || !commentaries.trim()) {
        commentaries = generateCommentaries(questionStem, examTypeName, analysis)
    }

    recognitionResult.value = {
        ...result,
        exam_type: examType,
        exam_type_name: examTypeName,
        question_stem: questionStem,
        options: processedOptions,
        title: questionStem,
        analysis: analysis,
        commentaries: commentaries,
        correct_answer: correctAnswer
    }

    // 根据题型设置默认分值
    switch (examType) {
        case 1:
        case 3:
            formData.score = 1
            formData.integral = 1
            break
        case 2:
            formData.score = 2
            formData.integral = 2
            break
        case 4:
        case 5:
            formData.score = 2
            formData.integral = 2
            break
        case 6:
            formData.score = 5
            formData.integral = 5
            break
    }
}

// 生成名师点评
const generateCommentaries = (questionStem: string, examTypeName: string, analysis: string) => {
    const templates = [
        '本题考查基础知识掌握',
        '重点考查核心概念理解',
        '考查综合分析能力',
        '本题需掌握关键要点',
        '考查知识运用能力',
        '本题考查逻辑推理',
        '重点考查判断能力',
        '考查知识记忆理解'
    ]

    const examTypeComment: Record<string, string> = {
        '单选题': '单选题需准确理解',
        '多选题': '多选题需全面分析',
        '判断题': '判断题需仔细辨析',
        '填空题': '填空题需准确记忆',
        '问答题': '问答题需综合阐述',
        '案例题': '案例题需深入分析'
    }

    const typeComment = examTypeComment[examTypeName] || '本题考查综合能力'
    const baseComment = templates[Math.floor(Math.random() * templates.length)]

    return `${typeComment}，${baseComment}`.substring(0, 20)
}

// 自动匹配知识点
const autoMatchKnowledge = () => {
    if (!recognitionResult.value || knowledgeList.value.length === 0) {
        return
    }

    const content = (recognitionResult.value.title || '') + ' ' + (recognitionResult.value.analysis || '') + ' ' + (recognitionResult.value.commentaries || '')
    const keywords = extractKeywords(content)

    if (keywords.length === 0) {
        return
    }

    const matchedKnowledge = knowledgeList.value.map((item: any) => {
        const matchScore = calculateMatchScore(item.title, keywords)
        return {
            ...item,
            score: matchScore
        }
    })

    const threshold = 0.15
    const sortedKnowledge = matchedKnowledge
        .filter((item: any) => item.score >= threshold)
        .sort((a: any, b: any) => b.score - a.score)

    const maxMatches = 5
    const selectedItems = sortedKnowledge.slice(0, maxMatches)

    if (selectedItems.length > 0) {
        formData.knowledge_uid = selectedItems.map((item: any) => item.uid)
        recognitionResultDetails.matchedKnowledgeCount = selectedItems.length
    }
}

// 提取关键词
const extractKeywords = (text: string) => {
    if (!text) return []

    let cleanedText = text.replace(/[\s\p{P}\p{S}]/gu, ' ')
    cleanedText = cleanedText.toLowerCase()
    const words = cleanedText.split(' ')
    const keywords = words
        .filter((word: string) => word.length > 1)
        .filter((word: string) => !STOP_WORDS.has(word))

    const wordCount: Record<string, number> = {}
    keywords.forEach((word: string) => {
        wordCount[word] = (wordCount[word] || 0) + 1
    })

    return Object.keys(wordCount)
        .sort((a, b) => wordCount[b] - wordCount[a])
        .slice(0, 100)
}

// 计算匹配度
const calculateMatchScore = (knowledgeLabel: string, keywords: string[]) => {
    if (!knowledgeLabel || keywords.length === 0) return 0

    const labelLower = knowledgeLabel.toLowerCase()
    let score = 0

    const matchedKeywords = keywords.filter((keyword: string) => labelLower.includes(keyword))

    if (matchedKeywords.length > 0) {
        score = matchedKeywords.length / Math.max(keywords.length, 1) * 0.4

        const exactMatches = keywords.filter((keyword: string) => labelLower === keyword)
        if (exactMatches.length > 0) {
            score += exactMatches.length * 0.3
        }

        const prefixMatches = keywords.filter((keyword: string) => labelLower.startsWith(keyword))
        if (prefixMatches.length > 0) {
            score += prefixMatches.length * 0.2
        }

        const containsMatches = matchedKeywords.filter((keyword: string) => !exactMatches.includes(keyword) && !prefixMatches.includes(keyword))
        if (containsMatches.length > 0) {
            score += containsMatches.length * 0.1
        }

        score = Math.min(score, 1)
    }

    return score
}

// 清理文本内容
const cleanTextContent = (text: string) => {
    if (!text) return text
    let cleaned = text
    cleaned = cleaned.replace(/[\u0000-\u001F\u007F-\u009F]/g, '')
    cleaned = cleaned.replace(/\\u[0-9a-fA-F]{4}/g, (match: string) => {
        try {
            return String.fromCharCode(parseInt(match.slice(2), 16))
        } catch (e) {
            return ''
        }
    })
    cleaned = cleaned.replace(/\s+/g, ' ').trim()
    return cleaned
}

// 去除首尾空行和空格
const trimContent = (content: string) => {
    if (!content) return content
    let trimmed = content.replace(/^[\s\n]+|[\s\n]+$/g, '')
    trimmed = trimmed.replace(/^(\s*<p>\s*(<br\s*\/?>)?\s*<\/p>\s*)+/i, '')
    trimmed = trimmed.replace(/(\s*<p>\s*(<br\s*\/?>)?\s*<\/p>)+$/i, '')
    return trimmed
}

// 表单验证
const validateForm = () => {
    if (!recognitionResult.value) {
        ElMessage.warning('请先完成识别')
        return false
    }

    if (!formData.chapter_uid) {
        ElMessage.warning('请选择章节')
        return false
    }

    if (!recognitionResult.value.title || !recognitionResult.value.title.trim()) {
        ElMessage.warning('题干不能为空')
        return false
    }

    if (!recognitionResult.value.analysis || !recognitionResult.value.analysis.trim()) {
        ElMessage.warning('解析不能为空')
        return false
    }

    const examType = recognitionResult.value.exam_type
    const options = recognitionResult.value.options || []

    if ([1, 2, 3].includes(examType)) {
        if (options.length === 0) {
            ElMessage.warning('请添加选项')
            return false
        }
        const emptyOption = options.find((opt: any) => !opt.content || !opt.content.trim())
        if (emptyOption) {
            ElMessage.warning('选项内容不能为空')
            return false
        }
    }

    if (!recognitionResult.value.correct_answer || !recognitionResult.value.correct_answer.trim()) {
        ElMessage.warning('请填写正确答案')
        return false
    }

    return true
}

// 保存试题
const saveQuestion = async () => {
    if (!validateForm()) {
        return
    }

    saving.value = true

    try {
        const questionData: any = {
            title: cleanTextContent(trimContent(recognitionResult.value.title)),
            exam_type: recognitionResult.value.exam_type,
            exam_level: formData.exam_level,
            score: formData.score,
            integral: formData.integral,
            is_show: formData.is_show,
            library_uid: props.libraryUid,
            chapter_uid: formData.chapter_uid,
            knowledge_uid: formData.knowledge_uid.join(','),
            label_uid: formData.label_uid.join(','),
            analysis: cleanTextContent(trimContent(recognitionResult.value.analysis || '')),
            commentaries: cleanTextContent(trimContent(recognitionResult.value.commentaries || ''))
        }

        const examType = recognitionResult.value.exam_type
        const options = recognitionResult.value.options || []

        switch (examType) {
            case 1:
            case 2:
            case 3:
                if (options.length > 0) {
                    questionData.option = options.map((opt: any, index: number) => ({
                        check: String.fromCharCode(65 + index),
                        title: cleanTextContent(trimContent(opt.content)),
                        is_check: recognitionResult.value.correct_answer &&
                            recognitionResult.value.correct_answer.includes(String.fromCharCode(65 + index)) ? "1" : ""
                    }))
                    questionData.answer = questionData.option
                        .filter((item: any) => item.is_check === "1")
                        .map((item: any) => item.check)
                }
                break
            case 4:
                if (recognitionResult.value.correct_answer) {
                    questionData.answer = [cleanTextContent(recognitionResult.value.correct_answer)]
                    questionData.option = [{ title: cleanTextContent(recognitionResult.value.correct_answer) }]
                }
                break
            case 5:
                if (recognitionResult.value.correct_answer) {
                    questionData.answer = cleanTextContent(recognitionResult.value.correct_answer)
                    questionData.option = [{ title: cleanTextContent(recognitionResult.value.correct_answer) }]
                }
                break
            case 6:
                if (options.length > 0) {
                    questionData.option = options.map((opt: any) => ({
                        title: cleanTextContent(trimContent(opt.content))
                    }))
                    if (recognitionResult.value.correct_answer) {
                        questionData.answer = cleanTextContent(recognitionResult.value.correct_answer)
                    }
                }
                break
        }

        const res = await apiTenantExamQuestionAdd(questionData)

        if (res && res.code === 1) {
            ElMessage.success('保存成功')
            // 重置表单
            imageUrl.value = ''
            imageFile.value = null
            recognitionResult.value = null
            formData.knowledge_uid = []
            formData.label_uid = []
        } else {
            if (res?.msg && res.msg.includes('相同标题的试题已存在')) {
                ElMessageBox.confirm(res.msg + '，是否仍要添加此试题？', '提示', {
                    confirmButtonText: '继续添加',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(async () => {
                    try {
                        const forceRes = await apiTenantExamQuestionAdd({ ...questionData, force: 1 })
                        if (forceRes && forceRes.code === 1) {
                            ElMessage.success('保存成功')
                            imageUrl.value = ''
                            imageFile.value = null
                            recognitionResult.value = null
                            formData.knowledge_uid = []
                            formData.label_uid = []
                        } else {
                            ElMessage.error(forceRes?.msg || '保存失败')
                        }
                    } catch (error) {
                        ElMessage.error('保存失败')
                    }
                })
            } else {
                ElMessage.error(res?.msg || '保存失败')
            }
        }
    } catch (error) {
        console.error('保存失败:', error)
        ElMessage.error('保存失败，请重试')
    } finally {
        saving.value = false
    }
}

// 初始化加载
onMounted(() => {
    loadChapterList()
    loadDifficultyList()
    loadLabelList()

    // 加载保存的提示词
    const savedPrompt = localStorage.getItem('aiOptionCustomPrompt')
    if (savedPrompt) {
        customPrompt.value = savedPrompt
    }
})
</script>

<style scoped>
.ai-option-container {
    padding: 20px;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.settings-card {
    margin-bottom: 20px;
}

.ai-generate-card {
    margin-bottom: 20px;
}

.upload-section {
    margin-bottom: 20px;
}

.upload-area {
    width: 100%;
}

.image-preview {
    margin-top: 20px;
    display: flex;
    align-items: center;
    gap: 20px;
}

.image-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.edit-preview-area {
    display: flex;
    gap: 20px;
}

.edit-section,
.preview-section {
    flex: 1;
}

.section-title {
    margin-bottom: 10px;
}

.option-item {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.option-label {
    font-weight: bold;
    min-width: 30px;
}

.result-details {
    line-height: 2;
}

.result-details p {
    margin: 10px 0;
}
</style>
