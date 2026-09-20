<template>
    <div>
        <el-card class="!border-none" shadow="never">
            <el-page-header content="章节管理" @back="$router.back()" />
        </el-card>
        <el-card class="!border-none mb-4" shadow="never" :body-style="{ padding: 10 + 'px' }">
            <el-form class="mb-[-16px] margin-bottom20" :model="queryParams" inline>
                <el-form-item label="章节名称" prop="title">
                    <el-input
                        class="w-[280px]"
                        v-model="queryParams.title"
                        clearable
                        placeholder="请输入章节名称"
                    />
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="resetPage">查询</el-button>
                    <el-button @click="resetParams">重置</el-button>
                    <!-- 导出功能 -->
                    <export-data
                        class="ml-2.5"
                        :params="queryParams"
                        :page-size="pager.size"
                        :fetch-fun="apiTenantExamChapterExport"
                    />
                </el-form-item>
            </el-form>
        </el-card>
        <el-card class="!border-none" v-loading="pager.loading" shadow="never">
            <div>
                <el-button
                    v-perms="['exam.tenant_exam_chapter/add']"
                    type="primary"
                    @click="handleAdd"
                >
                    <template #icon>
                        <icon name="el-icon-Plus" />
                    </template>
                    新增
                </el-button>
                <el-button
                    v-perms="['exam.tenant_exam_chapter/add']"
                    type="success"
                    @click="handleImport"
                >
                    <template #icon>
                        <icon name="el-icon-Upload" />
                    </template>
                    导入章节
                </el-button>
                <el-button
                    v-perms="['exam.tenant_exam_chapter/delete']"
                    :disabled="!selectData.length"
                    @click="handleDelete(selectData)"
                >
                    删除
                </el-button>
            </div>
            <div class="mt-4">
                <el-table
                    :data="pager.lists"
                    style="width: 100%; margin-bottom: 20px"
                    row-key="id"
                    @selection-change="handleSelectionChange"
                    :default-expand-all="false"
                    :tree-props="{ children: 'children', hasChildren: 'hasChildren' }"
                    :header-cell-style="{ 'text-align': 'center', 'font-weight': 'bold' }"
                    stripe
                >
                    <el-table-column type="selection" width="55" />
                    <el-table-column label="章节名称" prop="title" min-width="300">
                        <template #default="{ row }">
                            <div class="flex items-start">
                                <div
                                    class="flex-shrink-0 flex items-center justify-center w-8 h-8 mr-3 mt-1"
                                >
                                    <el-icon
                                        :size="20"
                                        class="text-blue-500"
                                        v-if="row.children && row.children.length > 0"
                                    >
                                        <Folder />
                                    </el-icon>
                                    <el-icon :size="20" class="text-green-500" v-else>
                                        <Document />
                                    </el-icon>
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold text-gray-800">
                                        {{ row.title }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        <span v-if="row.children && row.children.length > 0">
                                            包含 {{ row.children.length }} 个子章节
                                        </span>
                                        <span v-else> 末级章节 </span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="显示状态" prop="is_show" width="120" align="center">
                        <template #default="{ row }">
                            <el-tag :type="row.is_show === 1 ? 'success' : 'info'" size="small">
                                {{ row.is_show === 1 ? '显示' : '隐藏' }}
                            </el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column label="显示权重" prop="sort" width="120" align="center" />
                    <el-table-column
                        label="创建时间"
                        prop="create_time"
                        width="180"
                        align="center"
                        show-overflow-tooltip
                    />
                    <el-table-column
                        label="更新时间"
                        prop="update_time"
                        width="180"
                        align="center"
                        show-overflow-tooltip
                    />
                    <el-table-column label="操作" width="220" fixed="right" align="center">
                        <template #default="{ row }">
                            <!-- 第一行：知识点、试题 -->
                            <div class="flex justify-center space-x-2" style="margin-bottom: 10px">
                                <el-button
                                    v-perms="['exam.tenant_exam_knowledge/lists']"
                                    type="success"
                                    link
                                    size="small"
                                    :disabled="row.children && row.children.length > 0"
                                    @click="handleKnowledge(row)"
                                    :title="
                                        row.children && row.children.length > 0
                                            ? '只有末级章节可以添加知识点'
                                            : '管理知识点'
                                    "
                                >
                                    <el-icon class="mr-1"><Collection /></el-icon>
                                    知识点
                                </el-button>
                                <el-button
                                    v-perms="['exam.tenant_exam_question/index']"
                                    type="primary"
                                    link
                                    size="small"
                                    @click="handleTest(row)"
                                >
                                    <el-icon class="mr-1"><Document /></el-icon>
                                    试题
                                </el-button>
                            </div>
                            <!-- 第二行：新增、编辑、删除 -->
                            <div class="flex justify-center space-x-2">
                                <el-button
                                    v-perms="['exam.tenant_exam_chapter/add']"
                                    type="success"
                                    link
                                    size="small"
                                    @click="handleAdd(row)"
                                    :title="`在 '${row.title}' 下新增子章节`"
                                >
                                    <el-icon class="mr-1"><Plus /></el-icon>
                                    新增
                                </el-button>
                                <el-button
                                    v-perms="['exam.tenant_exam_chapter/edit']"
                                    type="primary"
                                    link
                                    size="small"
                                    @click="handleEdit(row)"
                                >
                                    <el-icon class="mr-1"><Edit /></el-icon>
                                    编辑
                                </el-button>
                                <el-button
                                    v-perms="['exam.tenant_exam_chapter/delete']"
                                    type="danger"
                                    link
                                    size="small"
                                    @click="handleDelete(row.id)"
                                >
                                    <el-icon class="mr-1"><Delete /></el-icon>
                                    删除
                                </el-button>
                            </div>
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
        <import-popup
            v-if="showImport"
            ref="importRef"
            @success="getLists"
            @close="showImport = false"
        />
    </div>
</template>

<script lang="ts" setup name="tenantExamChapterLists">
import { Collection, Delete, Document, Edit, Folder, Plus } from '@element-plus/icons-vue'

import {
    apiTenantExamChapterDelete,
    apiTenantExamChapterExport,
    apiTenantExamChapterLists
} from '@/api/exam/tenant_exam_chapter'
import ExportData from '@/components/export-data/index.vue'
import { useDictData } from '@/hooks/useDictOptions'
import { usePaging } from '@/hooks/usePaging'
import feedback from '@/utils/feedback'

import EditPopup from './edit.vue'
import ImportPopup from './import.vue'

const editRef = shallowRef<InstanceType<typeof EditPopup>>()
const importRef = shallowRef<InstanceType<typeof ImportPopup>>()
// 是否显示编辑框
const showEdit = ref(false)
// 是否显示导入框
const showImport = ref(false)
const route = useRoute()
const router = useRouter()
// 查询条件
const queryParams = reactive({
    title: '',
    is_show: '',
    library_uid: route.query.library_uid
})

// 选中数据
const selectData = ref<any[]>([])

// 表格选择后回调事件
const handleSelectionChange = (val: any[]) => {
    selectData.value = val.map(({ id }) => id)
}

// 获取字典数据
const { dictData } = useDictData('show_status')

// 分页相关
const { pager, getLists, resetParams, resetPage } = usePaging({
    fetchFun: apiTenantExamChapterLists,
    params: queryParams
})

// 添加
const handleAdd = async (row?: any) => {
    showEdit.value = true
    await nextTick()
    // 重置表单数据
    editRef.value?.setFormData({
        id: '',
        title: '',
        is_show: 1,
        sort: 100,
        parent_uid: row?.uid || 0
    })
    editRef.value?.open('add')
}

// 编辑
const handleEdit = async (data: any) => {
    showEdit.value = true
    await nextTick()
    editRef.value?.open('edit')
    editRef.value?.setFormData(data)
}

// 删除
const handleDelete = async (id: number | any[]) => {
    await feedback.confirm('确定要删除？')
    await apiTenantExamChapterDelete({ id })
    getLists()
}

// 导入章节
const handleImport = async () => {
    showImport.value = true
    await nextTick()
    const libraryUid = route.query.library_uid
    if (libraryUid && typeof libraryUid === 'string') {
        importRef.value?.open(libraryUid)
    } else {
        feedback.msgError('请选择题库')
    }
}

getLists()

const handleKnowledge = (data: any) => {
    router.push({
        path: '/exam/tenant_exam_knowledge',
        query: {
            chapter_uid: data.uid
        }
    })
}

const handleTest = (data: any) => {
    router.push({
        path: '/exam/library/question',
        query: {
            chapter_uid: data.uid,
            library_uid: route.query.library_uid
        }
    })
}
</script>
