<template>
    <div class="edit-popup">
        <popup
            ref="popupRef"
            :title="popupTitle"
            :async="true"
            width="550px"
            @confirm="handleSubmit"
            @close="handleClose"
        >
            <el-form ref="formRef" :model="formData" label-width="90px" :rules="formRules">
                <el-form-item label="试题库" prop="library_uid">
                    <el-select
                        v-model="formData.library_uid"
                        clearable
                        placeholder="请选择试题库"
                        style="width: 100%"
                        @change="handleChangeLibrary($event)"
                    >
                        <el-option
                            v-for="item in libraryList"
                            :key="item.uid"
                            :label="item.title"
                            :value="item.uid"
                        />
                    </el-select>
                </el-form-item>
                <el-form-item label="章节分类" prop="chapter_uid">
                    <el-cascader
                        v-model="formData.chapter_path"
                        clearable
                        placeholder="请选择章节分类"
                        :options="chapterList"
                        :props="{ label: 'title', value: 'uid', children: 'children' }"
                        style="width: 100%"
                        @change="handleChangeChapter($event as any[])"
                    />
                </el-form-item>
                <el-form-item label="父级知识点" prop="parent_uid">
                    <el-tree-select
                        v-model="formData.parent_uid"
                        clearable
                        placeholder="请选择父级知识点（可选）"
                        :data="parentTreeList"
                        node-key="uid"
                        :props="{
                            label: 'title',
                            children: 'children'
                        }"
                        :default-expand-all="false"
                        check-strictly
                        style="width: 100%"
                        @change="handleParentChange"
                    />
                </el-form-item>
                <el-form-item label="知识点名称" prop="title">
                    <div class="w-full">
                        <el-input
                            v-model="formData.title"
                            type="textarea"
                            :rows="4"
                            placeholder="请输入知识点名称，支持多行输入，一行一个知识点名称"
                        />
                        <div class="mt-2">
                            <div class="text-sm text-gray-500 flex items-center">
                                <el-icon class="mr-1"><InfoFilled /></el-icon>
                                <span>支持多行输入，一行一个知识点名称，可以同时添加多个知识点,
                               知识点描述在下面输入。或者在此处用"："隔开（例如："知识点 1：描述 1"）二选一。</span>
                            </div>
                        </div>
                    </div>
                </el-form-item>

                <el-form-item label="知识点描述" prop="content">
                    <div class="w-full">
                        <exam-editor v-model="formData.content" :height="150" width="100%" />
                        <div class="mt-2">
                            <div class="text-sm text-gray-500 flex items-center">
                                <el-icon class="mr-1"><InfoFilled /></el-icon>
                                <span
                                    >和知识点名称一一对应，一行一个知识点描述，每个知识点描述和知识点名称之间用换行符隔开</span
                                >
                            </div>
                        </div>
                    </div>
                </el-form-item>
                <el-form-item label="显示状态" prop="is_show">
                    <el-radio-group v-model="formData.is_show" placeholder="请选择显示状态">
                        <el-radio
                            v-for="(item, index) in dictData.show_status"
                            :key="index"
                            :label="parseInt(item.value)"
                        >
                            {{ item.name }}
                        </el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="显示权重" prop="sort">
                    <el-input-number
                        :min="0"
                        :step="1"
                        v-model="formData.sort"
                        clearable
                        placeholder="请输入显示权重"
                        style="width: 100%"
                    />
                </el-form-item>
            </el-form>
        </popup>
    </div>
</template>

<script lang="ts" setup name="tenantExamKnowledgeEdit">
import type { FormInstance } from 'element-plus'
import type { PropType } from 'vue'

import { nextTick } from 'vue'
import { apiTenantExamChapterTree } from '@/api/exam/tenant_exam_chapter'
import {
    apiTenantExamKnowledgeAdd,
    apiTenantExamKnowledgeDetail,
    apiTenantExamKnowledgeEdit,
    apiTenantExamKnowledgeTree
} from '@/api/exam/tenant_exam_knowledge'
import { apiTenantExamLibraryLists } from '@/api/exam/tenant_exam_library'
import Popup from '@/components/popup/index.vue'

defineProps({
    dictData: {
        type: Object as PropType<Record<string, any[]>>,
        default: () => ({})
    }
})
const emit = defineEmits(['success', 'close'])
const formRef = shallowRef<FormInstance>()
const popupRef = shallowRef<InstanceType<typeof Popup>>()
const mode = ref('add')

// 定义类型接口
interface LibraryItem {
    uid: string
    title: string
}

interface ChapterItem {
    uid: string
    title: string
    children?: ChapterItem[]
    [key: string]: any
}

interface ParentItem {
    uid: string
    title: string
}

// 为数组添加明确的类型定义
const parentList = ref<ParentItem[]>([])
const parentTreeList = ref<any[]>([])
const route = useRoute()
const chapterList = ref<ChapterItem[]>([])
const libraryList = ref<LibraryItem[]>([])
// 弹窗标题
const popupTitle = computed(() => {
    return mode.value == 'edit' ? '编辑章节知识点' : '新增章节知识点'
})

// 表单数据
const formData = reactive({
    id: '',
    title: '',
    content: '',
    is_show: 1, // 默认显示状态为1（显示）
    sort: 0,
    parent_uid: '',
    chapter_uid: (route.query.chapter_uid as string) || '',
    chapter_path: [] as string[], // 用于级联选择器的路径数组
    library_uid: ''
})

// 表单验证
const formRules = reactive<any>({
    title: [
        {
            required: true,
            message: '请输入知识点名称',
            trigger: ['blur']
        }
    ],
    library_uid: [
        {
            required: true,
            message: '请选择试题库',
            trigger: ['blur', 'change']
        }
    ],
    is_show: [
        {
            required: true,
            message: '请选择显示状态',
            trigger: ['blur']
        }
    ],
    sort: [
        {
            required: true,
            message: '请输入显示权重',
            trigger: ['blur']
        }
    ]
})
/* 
const formData = ref({
  // ... existing code ...
  is_show: 1, // 默认值设置为 1
  // ... existing code ...
}) */
const handleParentChange = (value: any) => {
    formData.parent_uid = value || ''
}

// 辅助函数：根据章节ID查找完整路径
const findChapterPath = (chapterId: string, chapters: any[]): string[] => {
    for (const chapter of chapters) {
        if (chapter.uid === chapterId) {
            return [chapter.uid]
        }
        if (chapter.children && chapter.children.length > 0) {
            const path = findChapterPath(chapterId, chapter.children)
            if (path.length > 0) {
                return [chapter.uid, ...path]
            }
        }
    }
    return []
}

// 辅助函数：验证章节路径是否存在于章节树中
const isValidChapterPath = (path: string[], chapters: any[]): boolean => {
    if (!path || path.length === 0) return false
    let currentLevel = chapters
    for (const uid of path) {
        const found = currentLevel.find((item: any) => item.uid === uid)
        if (!found) return false
        currentLevel = found.children || []
    }
    return true
}

// 获取详情
const setFormData = async (data: Record<any, any>) => {
    // 检查data是否包含data字段，如果有则使用data.data，否则直接使用data
    const detailData = data.data || data

    for (const key in formData) {
        if (detailData[key] != null && detailData[key] != undefined) {
            //@ts-ignore
            formData[key] = detailData[key]
        }
    }

    // 编辑模式下，根据获取到的library_uid和chapter_uid设置级联选择器
    if (mode.value === 'edit' && detailData.library_uid && detailData.chapter_uid) {
        // 设置library_uid
        formData.library_uid = detailData.library_uid

        // 加载章节列表
        await fetchExamChapterList()

        // 构建章节路径
        const chapterPath = findChapterPath(detailData.chapter_uid, chapterList.value)
        formData.chapter_path = chapterPath
    }
}

const getDetail = async (row: Record<string, any>) => {
    const response = await apiTenantExamKnowledgeDetail({
        id: row.id
    })
    // 确保传递给setFormData的是包含data字段的完整响应
    await setFormData(response)
}

// 提交按钮
const handleSubmit = async () => {
    await formRef.value?.validate()
    const data = { ...formData }
    
    // 处理知识点名称：按行分割，去除空白行，不做其他处理
    if (data.title) {
        const lines = data.title.split('\n').map(line => line.trim()).filter(line => line)

        const processedLines = lines.map(line => {
            return line
        })
        // 重新组合成用换行符分隔的字符串
        data.title = processedLines.join('\n')
    }
    
    // 知识点描述保持原样，不做处理（富文本编辑器返回的是 HTML）
    // 后端会按换行符分割处理
    
    mode.value == 'edit'
        ? await apiTenantExamKnowledgeEdit(data)
        : await apiTenantExamKnowledgeAdd(data)
    popupRef.value?.close()
    emit('success')
}

//打开弹窗
const open = (type = 'add') => {
    mode.value = type
    // 重置表单数据
    formData.id = ''
    formData.title = ''
    formData.content = ''
    formData.is_show = 1
    formData.sort = 0
    formData.parent_uid = ''
    formData.chapter_uid = ''
    formData.chapter_path = []
    formData.library_uid = ''

    // 打开弹窗
    popupRef.value?.open()

    // 如果是新增模式，等待弹窗打开后自动选择上一次的题库和章节，没有则选择第一个
    if (type === 'add') {
        // 确保题库列表已加载
        if (libraryList.value.length === 0) {
            // 如果题库列表未加载，先加载题库列表
            fetchLibraryList().then(() => {
                // 加载完成后尝试获取上一次选择的题库
                if (libraryList.value.length > 0) {
                    const lastLibrary = localStorage.getItem('lastSelectedLibrary')
                    // 如果有上一次选择的题库且存在于列表中，则选择它
                    if (lastLibrary && libraryList.value.some(item => item.uid === lastLibrary)) {
                        formData.library_uid = lastLibrary
                    } else {
                        // 否则选择第一个题库
                        formData.library_uid = libraryList.value[0].uid
                    }
                    // 加载章节列表
                    fetchExamChapterList().then(async () => {
                        // 等待 DOM 更新
                        await nextTick()
                        // 尝试获取上一次选择的章节路径
                        const lastChapterPath = localStorage.getItem('lastSelectedChapterPath')
                        if (lastChapterPath && chapterList.value.length > 0) {
                            try {
                                const chapterPath = JSON.parse(lastChapterPath)
                                if (Array.isArray(chapterPath) && chapterPath.length > 0) {
                                    // 验证路径是否存在于当前章节树中
                                    if (isValidChapterPath(chapterPath, chapterList.value)) {
                                        formData.chapter_path = chapterPath
                                        formData.chapter_uid = chapterPath[chapterPath.length - 1]
                                        // 加载父级分类列表
                                        fetchParentList()
                                    } else {
                                        // 路径无效，清除本地存储
                                        localStorage.removeItem('lastSelectedChapterPath')
                                        localStorage.removeItem('lastSelectedChapter')
                                    }
                                }
                            } catch (e) {
                                // 解析失败，清除本地存储
                                localStorage.removeItem('lastSelectedChapterPath')
                                localStorage.removeItem('lastSelectedChapter')
                            }
                        }
                    })
                }
            })
        } else {
            // 尝试获取上一次选择的题库
            const lastLibrary = localStorage.getItem('lastSelectedLibrary')
            // 如果有上一次选择的题库且存在于列表中，则选择它
            if (lastLibrary && libraryList.value.some(item => item.uid === lastLibrary)) {
                formData.library_uid = lastLibrary
            } else {
                // 否则选择第一个题库
                formData.library_uid = libraryList.value[0].uid
            }
            // 加载章节列表
            fetchExamChapterList().then(async () => {
                // 等待 DOM 更新
                await nextTick()
                // 尝试获取上一次选择的章节路径
                const lastChapterPath = localStorage.getItem('lastSelectedChapterPath')
                if (lastChapterPath && chapterList.value.length > 0) {
                    try {
                        const chapterPath = JSON.parse(lastChapterPath)
                        if (Array.isArray(chapterPath) && chapterPath.length > 0) {
                            // 验证路径是否存在于当前章节树中
                            if (isValidChapterPath(chapterPath, chapterList.value)) {
                                formData.chapter_path = chapterPath
                                formData.chapter_uid = chapterPath[chapterPath.length - 1]
                                // 加载父级分类列表
                                fetchParentList()
                            } else {
                                // 路径无效，清除本地存储
                                localStorage.removeItem('lastSelectedChapterPath')
                                localStorage.removeItem('lastSelectedChapter')
                            }
                        }
                    } catch (e) {
                        // 解析失败，清除本地存储
                        localStorage.removeItem('lastSelectedChapterPath')
                        localStorage.removeItem('lastSelectedChapter')
                    }
                }
            })
        }
    }
}

// 试题章节知识点父级列表
/* const fetchParentList = async () => {
    await apiTenantExamKnowledgeTree({chapter_uid: route.query.chapter_uid}).then((res) => {
        Object.assign(parentList, res)
    })
} 
 */
const handleChangeLibrary = (value: string) => {
    if (value) {
        // 直接使用选中的值作为库ID
        const libraryUid = value
        formData.library_uid = libraryUid || ''
        // 当题库变化时，重置章节路径和上级分类
        formData.chapter_path = []
        formData.chapter_uid = ''
        // 清空父级分类列表
        parentList.value = []
        // 清除章节相关的本地存储（题库变化后章节路径失效）
        localStorage.removeItem('lastSelectedChapterPath')
        localStorage.removeItem('lastSelectedChapter')
        // 重新加载章节列表
        fetchExamChapterList()
        // 保存选择的题库到localStorage
        localStorage.setItem('lastSelectedLibrary', libraryUid)
    } else {
        // 清空所有相关字段
        formData.library_uid = ''
        formData.chapter_path = []
        formData.chapter_uid = ''
        // 清空章节和父级分类列表
        chapterList.value = []
        parentList.value = []
        // 清除所有本地存储
        localStorage.removeItem('lastSelectedLibrary')
        localStorage.removeItem('lastSelectedChapterPath')
        localStorage.removeItem('lastSelectedChapter')
    }
}

const handleChangeChapter = (value: any[]) => {
    if (value && Array.isArray(value) && value.length > 0) {
        // 取数组最后一个值作为章节ID
        const chapterUid = value[value.length - 1]
        formData.chapter_uid = chapterUid || ''
        // 重新加载父级分类列表
        fetchParentList()
        // 保存选择的章节路径到localStorage
        localStorage.setItem('lastSelectedChapterPath', JSON.stringify(value))
        localStorage.setItem('lastSelectedChapter', chapterUid)
    } else {
        // 清空章节和上级分类
        formData.chapter_uid = ''
        // 清空父级分类列表
        parentList.value = []
    }
}
const fetchParentList = async () => {
    const currentChapterUid = formData.chapter_uid || route.query.chapter_uid || ''

    if (!currentChapterUid) {
        parentList.value = []
        parentTreeList.value = []
        return
    }

    await apiTenantExamKnowledgeTree({
        chapter_uid: currentChapterUid
    }).then((res) => {
        const list = res || []
        parentList.value = list
        parentTreeList.value = buildParentTree(list)
    })
}

// 构建父级知识点树形结构
const buildParentTree = (list: any[]): any[] => {
    if (!list || list.length === 0) return []
    
    const result = [
        {
            uid: '',
            title: '无父级（作为顶级知识点）'
        },
        ...list.map(item => {
            const { children, ...rest } = item
            return {
                ...rest,
                disabled: false
            }
        })
    ]
    
    return result
}
// 关闭回调
const handleClose = () => {
    emit('close')
}

const fetchExamChapterList = async () => {
    // 只使用formData中的library_uid，当用户选择不同题库时会更新
    const currentLibraryUid = formData.library_uid || ''

    if (!currentLibraryUid) {
        // 如果没有题库uid，清空章节列表
        chapterList.value = []
        return
    }

    // 使用正确的参数名library_uid
    const params = {
        library_uid: currentLibraryUid
    }

    await apiTenantExamChapterTree(params).then(
        (res) => {
            // 直接使用API返回的数据，无需额外过滤
            const lists = res?.lists || []
            chapterList.value = lists
        },
        (error) => {
            chapterList.value = []
        }
    )
}

// 试题库列表
const fetchLibraryList = async () => {
    try {
        const res = await apiTenantExamLibraryLists({ category_uid: '' })
        // ref类型的数组需要通过.value访问和赋值
        libraryList.value = res?.lists || []

        // 如果没有选择题库，自动选择第一个题库
        if (libraryList.value.length > 0 && !formData.library_uid) {
            // 添加类型检查，确保数组元素存在
            const firstLibrary = libraryList.value[0]
            if (firstLibrary) {
                formData.library_uid = firstLibrary.uid
                // 加载选中题库的章节列表
                fetchExamChapterList()
            }
        }
    } catch (error) {
        console.error('获取题库列表失败:', error)
        libraryList.value = []
    }
}

// 执行初始化加载
fetchLibraryList()

defineExpose({
    open,
    setFormData,
    getDetail
})
</script>
