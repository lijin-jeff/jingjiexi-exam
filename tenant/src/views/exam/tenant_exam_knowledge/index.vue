<template>
    <div>
        <el-card class="!border-none" shadow="never">
            <el-page-header content="知识点管理" @back="$router.back()" />
        </el-card>
        <el-card class="!border-none mb-4" shadow="never" :body-style="{ padding: 10 + 'px' }">
            <el-form class="mb-[-16px] margin-bottom20" :model="queryParams" inline>
                <el-form-item label="名称" prop="title">
                    <el-input
                        class="w-[280px]"
                        v-model="queryParams.title"
                        clearable
                        placeholder="请输入知识点名称"
                    />
                </el-form-item>
                <el-form-item label="题库" prop="library_uid" class="w-[280px]">
                    <el-select
                        class="flex-1"
                        v-model="queryParams.library_uid"
                        clearable
                        filterable
                        placeholder="请选择题库"
                        @change="handleChangeLibrary"
                    >
                        <el-option
                            v-for="item in libraryList"
                            :key="item.uid"
                            :label="item.title"
                            :value="item.uid"
                        />
                    </el-select>
                </el-form-item>
                <el-form-item label="章节" prop="chapter_uid" class="w-[280px]">
                    <el-tree-select
                        class="flex-1"
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
                    />
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="handleSearch">查询</el-button>
                    <el-button @click="handleReset">重置</el-button>
                </el-form-item>
            </el-form>
        </el-card>
        <el-card class="!border-none" v-loading="pager.loading" shadow="never">
            <div>
                <el-button
                    v-perms="['exam.tenant_exam_knowledge/add']"
                    type="primary"
                    @click="handleAdd"
                >
                    <template #icon>
                        <icon name="el-icon-Plus" />
                    </template>
                    新增
                </el-button>
                <el-button
                    v-perms="['exam.tenant_exam_knowledge/delete']"
                    :disabled="!selectData.length"
                    @click="handleDelete(selectData)"
                >
                    删除
                </el-button>
            </div>
            <div class="mt-4">
                <el-table
                    :data="knowledgeTreeData"
                    style="width: 100%; margin-bottom: 20px"
                    row-key="uid"
                    @selection-change="handleSelectionChange"
                    :default-expand-all="false"
                    :tree-props="{ children: 'children', hasChildren: 'hasChildren' }"
                    :cell-style="{ 'text-align': 'center' }"
                    :header-cell-style="{ 'text-align': 'center' }"
                >
                    <el-table-column type="selection" width="55" />
                    <el-table-column label="知识点名称" prop="title" show-overflow-tooltip>
                        <template #default="{ row }">
                            <span :style="{ paddingLeft: row.level > 0 ? row.level * 20 + 'px' : '0' }">
                                <el-icon v-if="row.level > 0" class="mr-1"><ArrowRight /></el-icon>
                                {{ row.title }}
                            </span>
                        </template>
                    </el-table-column>
                    <el-table-column label="章节名称" prop="chapter.title" show-overflow-tooltip />
                    <el-table-column label="显示状态" prop="is_show">
                        <template #default="{ row }">
                            <dict-value :options="dictData.show_status" :value="row.is_show" />
                        </template>
                    </el-table-column>
                    <el-table-column label="显示权重" prop="sort" show-overflow-tooltip />
                    <el-table-column label="操作" width="120" fixed="right">
                        <template #default="{ row }">
                            <el-button
                                v-perms="['exam.tenant_exam_knowledge/edit']"
                                type="primary"
                                link
                                @click="handleEdit(row)"
                            >
                                编辑
                            </el-button>
                            <el-button
                                v-perms="['exam.tenant_exam_knowledge/delete']"
                                type="danger"
                                link
                                @click="handleDelete(row.id)"
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
        <edit-popup
            v-if="showEdit"
            ref="editRef"
            :dict-data="dictData"
            @success="getLists"
            @close="showEdit = false"
        />
    </div>
</template>

<script lang="ts" setup name="tenantExamKnowledgeLists">
import { nextTick, shallowRef, watch } from 'vue'

import { apiTenantExamChapterTree } from '@/api/exam/tenant_exam_chapter'
import {
    apiTenantExamKnowledgeDelete,
    apiTenantExamKnowledgeLists
} from '@/api/exam/tenant_exam_knowledge'
import { apiTenantExamLibraryLists } from '@/api/exam/tenant_exam_library'
import { useDictData } from '@/hooks/useDictOptions'
import { usePaging } from '@/hooks/usePaging'
import feedback from '@/utils/feedback'

import EditPopup from './edit.vue'

const STORAGE_KEY = 'knowledge_search_params'

interface SearchParams {
    title: string
    library_uid: string
    chapter_uid: string
    timestamp: number
}

const saveSearchParams = (params: SearchParams) => {
    try {
        if (typeof localStorage === 'undefined') {
            console.warn('浏览器不支持 localStorage')
            return
        }
        const data = {
            ...params,
            timestamp: Date.now()
        }
        localStorage.setItem(STORAGE_KEY, JSON.stringify(data))
    } catch (error) {
        console.error('保存搜索参数失败:', error)
    }
}

const loadSearchParams = (): SearchParams | null => {
    try {
        if (typeof localStorage === 'undefined') {
            console.warn('浏览器不支持 localStorage')
            return null
        }
        const data = localStorage.getItem(STORAGE_KEY)
        if (!data) return null
        return JSON.parse(data) as SearchParams
    } catch (error) {
        console.error('读取搜索参数失败:', error)
        return null
    }
}

const route = useRoute()
const editRef = shallowRef<InstanceType<typeof EditPopup>>()
const showEdit = ref(false)
const chapterList = ref<any[]>([])
const libraryList = ref<any[]>([])

const queryParams = reactive({
    title: '',
    is_show: '',
    library_uid: (route.query.library_uid as string) || '',
    chapter_uid: (route.query.chapter_uid as string) || ''
})

const selectData = ref<any[]>([])
const knowledgeTreeData = ref<any[]>([])

const handleSelectionChange = (val: any[]) => {
    selectData.value = val.map(({ id }) => id)
}

const { dictData } = useDictData('show_status')

const { pager, getLists, resetParams, resetPage } = usePaging({
    fetchFun: apiTenantExamKnowledgeLists,
    params: queryParams
})

// 将扁平数据转换为树形结构
const buildKnowledgeTree = (list: any[]): any[] => {
    if (!list || list.length === 0) return []
    
    // 先找出所有顶级知识点（parent_uid为空）
    const topLevel = list.filter(item => !item.parent_uid || item.parent_uid === '')
    
    // 递归构建子节点
    const buildChildren = (parentUid: string, level: number): any[] => {
        const children = list.filter(item => item.parent_uid === parentUid)
        return children.map(child => ({
            ...child,
            level,
            children: buildChildren(child.uid, level + 1)
        }))
    }
    
    return topLevel.map(item => ({
        ...item,
        level: 0,
        children: buildChildren(item.uid, 1)
    }))
}

// 监听列表数据变化，构建树形结构
watch(() => pager.lists, (newList) => {
    knowledgeTreeData.value = buildKnowledgeTree(newList)
}, { immediate: true, deep: true })

const handleAdd = async () => {
    showEdit.value = true
    await nextTick()
    editRef.value?.open('add')
}

const handleEdit = async (data: any) => {
    showEdit.value = true
    await nextTick()
    editRef.value?.open('edit')
    editRef.value?.setFormData(data)
}

const handleDelete = async (id: number | any[]) => {
    await feedback.confirm('确定要删除？')
    await apiTenantExamKnowledgeDelete({ id, chapter_uid: queryParams.chapter_uid })
    getLists()
}

const handleSearch = () => {
    saveSearchParams({
        title: queryParams.title,
        library_uid: queryParams.library_uid,
        chapter_uid: queryParams.chapter_uid,
        timestamp: Date.now()
    })
    resetPage()
}

const handleReset = () => {
    resetParams()
    saveSearchParams({
        title: '',
        library_uid: '',
        chapter_uid: '',
        timestamp: Date.now()
    })
}
const fetchLibraryList = async () => {
    try {
        const params = { category_uid: '' }
        const res = await apiTenantExamLibraryLists(params)

        const lists = res?.lists || []

        libraryList.value = lists

        // 尝试从本地存储恢复搜索参数
        const savedParams = loadSearchParams()
        if (savedParams) {
            // 恢复搜索参数
            if (savedParams.title) queryParams.title = savedParams.title
            if (savedParams.library_uid && lists.some((item: any) => item.uid === savedParams.library_uid)) {
                queryParams.library_uid = savedParams.library_uid
            }
        }

        // 如果路由中有library_uid参数，优先使用路由参数
        if (route.query.library_uid && !queryParams.library_uid) {
            queryParams.library_uid = Array.isArray(route.query.library_uid)
                ? route.query.library_uid[0] || ''
                : (route.query.library_uid as string) || ''
        }

        // 如果没有选择题库但有题库列表，自动选择第一个
        if (lists.length > 0 && !queryParams.library_uid) {
            queryParams.library_uid = lists[0].uid
        }

        // 题库列表加载完成后，加载章节列表
        if (queryParams.library_uid) {
            await fetchExamChapterList(queryParams.library_uid as string | undefined)
        }

        // 恢复章节选择
        if (savedParams?.chapter_uid) {
            queryParams.chapter_uid = savedParams.chapter_uid
        }

        // 搜索参数恢复完成后，再加载数据列表
        getLists()
    } catch (error) {
        console.error('获取题库列表失败:', error)
        libraryList.value = []
        getLists()
    }
}
fetchLibraryList()

// 处理题库变化事件
const handleChangeLibrary = (value: string) => {
    // 重置章节选择
    queryParams.chapter_uid = ''
    // 重新加载章节列表
    fetchExamChapterList(value)
}

const fetchExamChapterList = async (libraryUid?: string) => {
    try {
        // 优先使用传入的libraryUid，否则使用queryParams.library_uid，最后使用route.query.library_uid
        const currentLibraryUid =
            libraryUid || queryParams.library_uid || route.query.library_uid || ''

        if (!currentLibraryUid) {
            // 如果没有题库uid，清空章节列表
            chapterList.value = []
            return
        }

        const res: any = await apiTenantExamChapterTree({
            library_uid: currentLibraryUid
        })

        // 直接使用API返回的数据，无需额外转换
        const lists = res?.lists || []

        // 直接使用API返回的树形结构，无需额外转换
        chapterList.value = lists

        // 章节列表加载完成后，如果有选中的章节，确保组件显示正确的章节标题
        if (queryParams.chapter_uid) {
            // 使用setTimeout确保在DOM更新后执行，让组件有足够时间处理数据
            setTimeout(() => {
                const temp = queryParams.chapter_uid
                queryParams.chapter_uid = ''
                nextTick(() => {
                    queryParams.chapter_uid = temp
                })
            }, 0)
        }
    } catch (error) {
        console.error('获取章节数据失败:', error)
        chapterList.value = []
    }
}
</script>