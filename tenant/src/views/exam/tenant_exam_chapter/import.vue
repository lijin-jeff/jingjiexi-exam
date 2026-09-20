<template>
    <popup
        ref="popupRef"
        title="导入章节"
        :async="true"
        width="800px"
        @confirm="handleSubmit"
        @close="handleClose"
    >
        <div class="import-container">
            <el-alert
                title="导入说明"
                type="info"
                :closable="false"
                class="mb-4"
            >
                <template #default>
                    <div class="text-sm">
                        <p>1. 支持识别的章节格式：</p>
                        <ul class="list-disc ml-6 mt-1">
                            <li>一级章节：<code>第 X 章 章节名称</code>（如：第 1 章 建设工程基本法律知识）</li>
                            <li>二级章节：<code>X.Y 章节名称</code>（如：1.1 建设工程法律基础）</li>
                            <li>三级章节：<code>X.Y.Z 章节名称</code>（如：1.1.1 法律部门和法律体系）</li>
                        </ul>
                        <p class="mt-2">2. 请确保txt文件使用UTF-8编码</p>
                    </div>
                </template>
            </el-alert>

            <el-form ref="formRef" :model="formData" :rules="formRules" label-width="100px">
                
                <el-form-item label="上级章节" prop="parent_uid">
                    <el-tree-select
                        v-model="formData.parent_uid"
                        :data="parentList"
                        :props="{
                            value: 'uid',
                            label: 'title',
                            children: 'children'
                        }"
                        clearable
                        check-strictly
                        placeholder="请选择上级章节（可选，不选则作为顶级章节）"
                        class="w-full"
                    />
                </el-form-item>

                <el-form-item label="上传文件" prop="content">
                    <el-upload
                        ref="uploadRef"
                        :auto-upload="false"
                        :show-file-list="false"
                        accept=".txt"
                        :on-change="handleFileChange"
                        drag
                        class="w-full"
                    >
                        <el-icon class="el-icon--upload"><upload-filled /></el-icon>
                        <div class="el-upload__text">
                            将txt文件拖到此处，或<em>点击上传</em>
                        </div>
                        <template #tip>
                            <div class="el-upload__tip">
                                只能上传 .txt 文件，且文件编码为 UTF-8
                            </div>
                        </template>
                    </el-upload>
                </el-form-item>

                <el-form-item v-if="fileName" label="已选文件">
                    <el-tag type="success" closable @close="clearFile">
                        {{ fileName }}
                    </el-tag>
                </el-form-item>

                <el-form-item v-if="previewData.length > 0" label="预览">
                    <div class="preview-container">
                        <el-tree
                            :data="previewData"
                            :props="{ label: 'title', children: 'children' }"
                            default-expand-all
                            :expand-on-click-node="false"
                        >
                            <template #default="{ node, data }">
                                <span class="flex items-center">
                                    <el-tag 
                                        :type="getLevelTagType(data.level) as 'primary' | 'success' | 'warning' | 'danger' | 'info'" 
                                        size="small"
                                        class="mr-2"
                                    >
                                        {{ getLevelText(data.level) }}
                                    </el-tag>
                                    <span>{{ data.title }}</span>
                                </span>
                            </template>
                        </el-tree>
                    </div>
                </el-form-item>
            </el-form>
        </div>
    </popup>
</template>

<script lang="ts" setup>
import { UploadFilled } from '@element-plus/icons-vue'
import Popup from '@/components/popup/index.vue'
import { apiTenantExamChapterImportFromTxt, apiTenantExamChapterTree } from '@/api/exam/tenant_exam_chapter'
import feedback from '@/utils/feedback'

const emit = defineEmits(['success', 'close'])
const popupRef = shallowRef<InstanceType<typeof Popup>>()
const formRef = shallowRef()
const uploadRef = shallowRef()

const libraryUid = ref('')
const fileName = ref('')
const parentList = ref<any[]>([])
const previewData = ref<any[]>([])

const formData = reactive({
    library_uid: '',
    parent_uid: '',
    content: ''
})

const formRules = {
    library_uid: [{ required: true, message: '请选择题库', trigger: 'change' }]
}

const open = async (libUid: string) => {
    libraryUid.value = libUid
    formData.library_uid = libUid
    formData.parent_uid = ''
    formData.content = ''
    fileName.value = ''
    previewData.value = []
    // 获取章节树
    await fetchParentList()
    popupRef.value?.open()
}

const fetchParentList = async () => {
    try {
        const res: any = await apiTenantExamChapterTree({ library_uid: libraryUid.value })
        console.log('章节树响应:', res)
        
        // 兼容不同的返回格式
        const treeData = res?.lists || res?.data?.lists || res || []
        console.log('章节树数据:', treeData)
        
        // 添加顶级菜单选项
        const topLevelOption = { uid: '0', title: '顶级章节', children: treeData }
        parentList.value = [topLevelOption]
        console.log('父级菜单数据:', parentList.value)
    } catch (error) {
        console.error('获取章节树失败:', error)
        parentList.value = []
    }
}

const handleFileChange = (file: any) => {
    const rawFile = file.raw
    console.log('文件对象:', file)
    console.log('原始文件:', rawFile)
    
    if (!rawFile) {
        feedback.msgError('文件对象为空')
        return
    }
    
    if (!rawFile.name.endsWith('.txt')) {
        feedback.msgError('只能上传 .txt 文件')
        return
    }
    
    fileName.value = rawFile.name
    
    const reader = new FileReader()
    reader.onload = (e) => {
        const content = e.target?.result as string
        console.log('文件读取成功，内容长度:', content?.length)
        console.log('文件内容前 100 字符:', content?.substring(0, 100))
        formData.content = content
        parseAndPreview(content)
    }
    reader.onerror = () => {
        console.error('文件读取失败')
        feedback.msgError('文件读取失败')
    }
    reader.readAsText(rawFile, 'UTF-8')
}

const parseAndPreview = (content: string) => {
    const lines = content.split('\n')
    const chapters: any[] = []
    let currentChapter: any = null
    let currentSection: any = null
    
    for (const line of lines) {
        const trimmedLine = line.trim()
        if (!trimmedLine) continue
        
        const chapterMatch = trimmedLine.match(/^第\s*(\d+)\s*章\s+(.+)$/u)
        const sectionMatch = trimmedLine.match(/^(\d+)\.(\d+)\s+(.+)$/u)
        const subSectionMatch = trimmedLine.match(/^(\d+)\.(\d+)\.(\d+)\s+(.+)$/u)
        
        if (chapterMatch) {
            // 保存上一章
            if (currentChapter) {
                chapters.push(currentChapter)
            }
            // 创建新的一级章节
            currentChapter = {
                level: 1,
                number: chapterMatch[1],
                title: '第' + chapterMatch[1] + '章 ' + chapterMatch[2].trim(),
                children: []
            }
            currentSection = null
        } else if (sectionMatch && currentChapter) {
            // 检查是否已存在相同编号的二级目录
            const sectionNumber = `${sectionMatch[1]}.${sectionMatch[2]}`
            const existingSection = currentChapter.children.find(
                (child: any) => child.number === sectionNumber
            )
            
            if (existingSection) {
                // 如果已存在，更新当前二级目录引用
                currentSection = existingSection
            } else {
                // 创建新的二级目录
                currentSection = {
                    level: 2,
                    number: sectionNumber,
                    title: `${sectionMatch[1]}.${sectionMatch[2]} ${sectionMatch[3].trim()}`,
                    children: []
                }
                currentChapter.children.push(currentSection)
            }
        } else if (subSectionMatch && currentChapter) {
            // 三级目录需要属于某个二级目录
            const sectionNumber = `${subSectionMatch[1]}.${subSectionMatch[2]}`
            
            // 如果当前二级目录不匹配，查找正确的二级目录
            if (!currentSection || currentSection.number !== sectionNumber) {
                currentSection = currentChapter.children.find(
                    (child: any) => child.number === sectionNumber
                )
            }
            
            // 如果找到了对应的二级目录，添加三级目录
            if (currentSection) {
                const subSectionNumber = `${subSectionMatch[1]}.${subSectionMatch[2]}.${subSectionMatch[3]}`
                const existingSubSection = currentSection.children.find(
                    (child: any) => child.number === subSectionNumber
                )
                
                if (!existingSubSection) {
                    currentSection.children.push({
                        level: 3,
                        number: subSectionNumber,
                        title: `${subSectionMatch[1]}.${subSectionMatch[2]}.${subSectionMatch[3]} ${subSectionMatch[4].trim()}`,
                        children: []
                    })
                }
            }
        }
    }
    
    // 保存最后一章
    if (currentChapter) {
        chapters.push(currentChapter)
    }
    
    previewData.value = chapters
    console.log('解析结果:', chapters)
    
    return chapters
}

const clearFile = () => {
    fileName.value = ''
    formData.content = ''
    previewData.value = []
    uploadRef.value?.clearFiles()
}

const getLevelTagType = (level: number) => {
    const types: Record<number, string> = {
        1: 'danger',
        2: 'warning',
        3: 'success'
    }
    return types[level] || 'info'
}

const getLevelText = (level: number) => {
    const texts: Record<number, string> = {
        1: '一级',
        2: '二级',
        3: '三级'
    }
    return texts[level] || '未知'
}

const handleSubmit = async () => {
    await formRef.value?.validate()
    
    if (!formData.content) {
        feedback.msgError('请上传 txt 文件')
        return
    }
    
    // 重新解析并去重
    const deduplicatedData = parseAndPreview(formData.content)
    
    // 将去重后的内容重新格式化为文本
    const deduplicatedContent = formatChaptersToText(deduplicatedData)
    
    try {
        console.log('提交导入数据:', {
            library_uid: formData.library_uid,
            parent_uid: formData.parent_uid || '0',
            content_length: deduplicatedContent.length,
            chapters_count: deduplicatedData.length
        })
        
        const result = await apiTenantExamChapterImportFromTxt({
            library_uid: formData.library_uid,
            parent_uid: formData.parent_uid || '0',
            content: deduplicatedContent
        })
        
        console.log('导入结果:', result)
        feedback.msgSuccess('导入成功')
        popupRef.value?.close()
        emit('success')
    } catch (error) {
        console.error('导入失败:', error)
    }
}

// 将章节数据格式化为文本（用于后端导入）
const formatChaptersToText = (chapters: any[]) => {
    let text = ''
    for (const chapter of chapters) {
        text += chapter.title + '\n'
        for (const section of chapter.children || []) {
            text += section.title + '\n'
            for (const subSection of section.children || []) {
                text += subSection.title + '\n'
            }
        }
    }
    return text
}

const handleClose = () => {
    emit('close')
}

defineExpose({
    open
})
</script>

<style scoped>
.import-container {
    max-height: 600px;
    overflow-y: auto;
}

.preview-container {
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid #e4e7ed;
    border-radius: 4px;
    padding: 10px;
    width: 100%;
}

:deep(.el-upload-dragger) {
    width: 100%;
}
</style>
