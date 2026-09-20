<template>
    <div>
        <el-card class="!border-none" shadow="never">
            <el-page-header content="题库管理" @back="$router.back()" />
        </el-card>
        <el-card class="!border-none mb-4" shadow="never" :body-style="{ padding: 10 + 'px' }">
            <el-form class="" :model="queryParams" inline>
                <el-form-item label="题库名称" prop="title">
                    <el-input
                        class="w-[120px]"
                        v-model="queryParams.title"
                        clearable
                        placeholder="请输入"
                    />
                </el-form-item>
                <!--                <el-form-item label="显示状态" prop="is_show">-->
                <!--                    <el-select-->
                <!--                        style="width: 100px"-->
                <!--                        v-model="queryParams.is_show"-->
                <!--                        clearable-->
                <!--                        placeholder="请选择"-->
                <!--                    >-->
                <!--                        <el-option label="全部" value=""></el-option>-->
                <!--                        <el-option-->
                <!--                            v-for="(item, index) in dictData.show_status"-->
                <!--                            :key="index"-->
                <!--                            :label="item.name"-->
                <!--                            :value="item.value"-->
                <!--                        />-->
                <!--                    </el-select>-->
                <!--                </el-form-item>-->
                <el-form-item label="题库分类" prop="category_uid">
                    <el-cascader
                        v-model="queryParams.category_uid"
                        :options="categoryList"
                        :props="cascaderProps"
                        placeholder="请选择题库分类"
                        style="width: 100%"
                        clearable
                        filterable
                        @change="handleChange"
                    />
                </el-form-item>
                <!--                <el-form-item label="题库作者" prop="author">-->
                <!--                    <el-input-->
                <!--                        style="width: 100px"-->
                <!--                        v-model="queryParams.author"-->
                <!--                        clearable-->
                <!--                        placeholder="请输入"-->
                <!--                    />-->
                <!--                </el-form-item>-->
                <el-form-item label="收费状态" prop="free_state">
                    <el-select
                        style="width: 100px"
                        v-model="queryParams.free_state"
                        clearable
                        placeholder="请选择"
                    >
                        <el-option label="全部" value=""></el-option>
                        <el-option
                            v-for="(item, index) in dictData.price_typ"
                            :key="index"
                            :label="item.name"
                            :value="item.value"
                        />
                    </el-select>
                </el-form-item>
                <el-form-item label="是否热门" prop="hot_state">
                    <el-select
                        style="width: 100px"
                        v-model="queryParams.hot_state"
                        clearable
                        placeholder="请选择"
                    >
                        <el-option label="全部" value=""></el-option>
                        <el-option
                            v-for="(item, index) in dictData.hot_state"
                            :key="index"
                            :label="item.name"
                            :value="item.value"
                        />
                    </el-select>
                </el-form-item>
                <el-form-item label="是否推荐" prop="recommend_state">
                    <el-select
                        style="width: 100px"
                        v-model="queryParams.recommend_state"
                        clearable
                        placeholder="请选择"
                    >
                        <el-option label="全部" value=""></el-option>
                        <el-option
                            v-for="(item, index) in dictData.recommend_state"
                            :key="index"
                            :label="item.name"
                            :value="item.value"
                        />
                    </el-select>
                </el-form-item>
                <!--                <el-form-item label="题库年份" prop="year">-->
                <!--                    <el-select-->
                <!--                        style="width: 100px"-->
                <!--                        v-model="queryParams.year"-->
                <!--                        clearable-->
                <!--                        placeholder="请选择"-->
                <!--                    >-->
                <!--                        <el-option label="全部" value=""></el-option>-->
                <!--                        <el-option-->
                <!--                            v-for="(item, index) in dictData.data_year"-->
                <!--                            :key="index"-->
                <!--                            :label="item.name"-->
                <!--                            :value="item.value"-->
                <!--                        />-->
                <!--                    </el-select>-->
                <!--                </el-form-item>-->
                <el-form-item>
                    <el-button type="primary" @click="resetPage">查询</el-button>
                    <el-button @click="resetParams">重置</el-button>
                    <!-- 导出功能 -->
                    <export-data
                        class="ml-2.5"
                        :params="queryParams"
                        :page-size="pager.size"
                        :fetch-fun="apiTenantExamLibraryExport"
                    />
                    <!-- 导入功能 -->
                    <el-upload
                        class="ml-2.5"
                        :style="{ height: '32px', lineHeight: '32px' }"
                        action=""
                        :auto-upload="false"
                        :on-change="handleFileChange"
                        :before-upload="beforeUpload"
                        accept=".xlsx,.xls,.csv"
                    >
                        <el-button type="success">
                            <el-icon><Document /></el-icon>
                            导入
                        </el-button>
                    </el-upload>
                </el-form-item>
            </el-form>
        </el-card>
        <el-card class="!border-none" v-loading="pager.loading" shadow="never">
            <el-button v-perms="['exam.tenant_exam_library/add']" type="primary" @click="handleAdd">
                <template #icon>
                    <icon name="el-icon-Plus" />
                </template>
                新增
            </el-button>
            <el-button
                v-perms="['exam.tenant_exam_library/delete']"
                :disabled="!selectData.length"
                @click="handleDelete(selectData)"
            >
                删除
            </el-button>

            <el-button
                v-if="importFile"
                type="primary"
                class="ml-2"
                @click="handleImport"
                :loading="isImporting"
            >
                <el-icon><Upload /></el-icon>
                确认导入
            </el-button>
            <div class="mt-4">
                <el-table
                    :data="pager.lists"
                    @selection-change="handleSelectionChange"
                    stripe
                    border
                >
                    <el-table-column type="selection" width="55" align="center" />
                    <el-table-column
                        label="题库编号"
                        prop="uid"
                        min-width="120"
                        show-overflow-tooltip
                    />
                    <el-table-column
                        label="题库分类"
                        prop="category.title"
                        min-width="120"
                        show-overflow-tooltip
                    />
                    <el-table-column
                        label="题库名称"
                        prop="title"
                        min-width="150"
                        show-overflow-tooltip
                    />
                    <el-table-column label="题库封面" prop="image" width="100" align="center">
                        <template #default="{ row }">
                            <el-image
                                v-if="row.image"
                                style="width: 50px; height: 50px"
                                :src="row.image"
                                :preview-src-list="[row.image]"
                                preview-teleported
                                fit="cover"
                            />
                            <span v-else class="text-gray-400">无图片</span>
                        </template>
                    </el-table-column>
                    <el-table-column label="收费状态" prop="free_state" width="100" align="center">
                        <template #default="{ row }">
                            <el-tag
                                :type="row.free_state == 1 ? 'success' : 'warning'"
                                size="small"
                            >
                                {{ row.free_state == 1 ? '免费题库' : '收费题库' }}
                            </el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column label="显示状态" prop="is_show" width="100" align="center">
                        <template #default="{ row }">
                            <el-tag :type="row.is_show == 1 ? 'success' : 'danger'" size="small">
                                {{ row.is_show == 1 ? '显示' : '隐藏' }}
                            </el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="创建时间"
                        prop="create_time"
                        min-width="160"
                        show-overflow-tooltip
                    />
                    <el-table-column label="操作" width="240" fixed="right" align="center">
                        <template #default="{ row }">
                            <el-button
                                v-perms="['exam.tenant_exam_library/edit']"
                                type="primary"
                                link
                                size="small"
                                @click="handleEdit(row)"
                            >
                                <el-icon class="mr-1"><Edit /></el-icon>
                                编辑
                            </el-button>
                            <el-button
                                v-perms="['exam.tenant_exam_library/delete']"
                                type="danger"
                                link
                                size="small"
                                @click="handleDelete(row.id)"
                            >
                                <el-icon class="mr-1"><Delete /></el-icon>
                                删除
                            </el-button>
                            <el-dropdown style="margin-left: 12px; height: 18px">
                                <el-button type="info" link size="small">
                                    <el-icon class="mr-1"><More /></el-icon>
                                    更多
                                </el-button>
                                <template #dropdown>
                                    <el-dropdown-menu>
                                        <el-dropdown-item>
                                            <el-button
                                                v-perms="['exam.tenant_exam_question/lists']"
                                                type="primary"
                                                link
                                                size="small"
                                                @click="handleQuestion(row)"
                                            >
                                                <el-icon class="mr-1"><Document /></el-icon>
                                                试题
                                            </el-button>
                                        </el-dropdown-item>
                                        <el-dropdown-item>
                                            <el-button
                                                v-perms="['exam.tenant_exam_chapter/lists']"
                                                type="success"
                                                link
                                                size="small"
                                                @click="handleChapter(row)"
                                            >
                                                <el-icon class="mr-1"><Folder /></el-icon>
                                                章节
                                            </el-button>
                                        </el-dropdown-item>
                                        <el-dropdown-item>
                                            <el-button
                                                v-perms="['exam.tenant_exam_label/lists']"
                                                type="warning"
                                                link
                                                size="small"
                                                @click="handleLabel(row)"
                                            >
                                                <el-icon class="mr-1"><CollectionTag /></el-icon>
                                                标签
                                            </el-button>
                                        </el-dropdown-item>
                                    </el-dropdown-menu>
                                </template>
                            </el-dropdown>
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

        <!-- 导入结果弹窗 -->
        <el-dialog
            v-model="showImportResult"
            title="导入结果"
            width="800px"
            :before-close="closeImportResult"
        >
            <div class="import-result-container">
                <div class="result-summary">
                    <div class="result-item success">
                        <el-icon class="mr-1"><CircleCheck /></el-icon>
                        成功：{{ importResult.success }} 条
                    </div>
                    <div class="result-item failed">
                        <el-icon class="mr-1"><CircleClose /></el-icon>
                        失败：{{ importResult.failed }} 条
                    </div>
                </div>

                <!-- 错误信息列表 -->
                <div v-if="importResult.failed > 0" class="errors-section">
                    <h3 class="section-title">错误详情</h3>
                    <el-table :data="importResult.errors" stripe border size="small" height="300">
                        <el-table-column prop="row" label="行号" width="80" align="center" />
                        <el-table-column prop="message" label="错误信息" show-overflow-tooltip />
                    </el-table>
                </div>
            </div>
            <template #footer>
                <span class="dialog-footer">
                    <el-button @click="closeImportResult">关闭</el-button>
                </span>
            </template>
        </el-dialog>
    </div>
</template>

<script lang="ts" setup name="tenantExamLibraryLists">
import {
    CircleCheck,
    CircleClose,
    CollectionTag,
    Delete,
    Document,
    Edit,
    Folder,
    Upload
} from '@element-plus/icons-vue'
import { shallowRef } from 'vue'
import { useRouter } from 'vue-router'

import { apiExamCategoryTree } from '@/api/exam/exam_category'
import {
    apiTenantExamLibraryDelete,
    apiTenantExamLibraryExport,
    apiTenantExamLibraryLists
} from '@/api/exam/tenant_exam_library'
import ExportData from '@/components/export-data/index.vue'
import { useDictData } from '@/hooks/useDictOptions'
import { usePaging } from '@/hooks/usePaging'
import feedback from '@/utils/feedback'

import EditPopup from './edit.vue'

const router = useRouter()

const editRef = shallowRef<InstanceType<typeof EditPopup>>()
// 是否显示编辑框
const showEdit = ref(false)

// 查询条件
const queryParams = reactive({
    title: '',
    is_show: '',
    category_uid: '',
    author: '',
    free_state: '',
    year: '',
    hot_state: '',
    recommend_state: ''
})
const categoryList = reactive([])

// Cascader 配置
const cascaderProps = {
    value: 'uid',
    label: 'title',
    children: 'children',
    checkStrictly: true, // 允许选择任意一级
    emitPath: false // 只返回选中节点的值，不返回路径数组
}

// 导入相关状态
const importFile = ref<File | null>(null)
const importResult = reactive({
    success: 0,
    failed: 0,
    errors: [] as { row: number; message: string }[]
})
const showImportResult = ref(false)
const isImporting = ref(false)

// 导入文件变更
const handleFileChange = (file: any) => {
    importFile.value = file.raw
}

// 上传前验证
const beforeUpload = (file: any) => {
    const validTypes = ['.xlsx', '.xls', '.csv']
    const ext = file.name.substring(file.name.lastIndexOf('.'))
    if (!validTypes.includes(ext)) {
        feedback.msgError('只支持Excel和CSV格式的文件')
        return false
    }

    // 验证文件大小（限制为10MB）
    const maxSize = 10 * 1024 * 1024
    if (file.size > maxSize) {
        feedback.msgError('文件大小不能超过10MB')
        return false
    }

    return true
}

// 处理导入
const handleImport = async () => {
    if (!importFile.value) {
        feedback.msgError('请选择要导入的文件')
        return
    }

    isImporting.value = true
    feedback.loading('导入中，请稍候...')

    try {
        // 注意：当前API文件中不存在apiTenantExamLibraryImport方法，需要后端实现
        // const { apiTenantExamLibraryImport } = await import('@/api/exam/tenant_exam_library')
        // const formData = new FormData()
        // formData.append('file', importFile.value)
        //
        // const res = await apiTenantExamLibraryImport(formData)

        // 模拟导入结果
        const res = {
            success: 8,
            failed: 2,
            errors: [
                { row: 3, message: '题库名称不能为空' },
                { row: 7, message: '题库分类不存在' }
            ]
        }

        // 处理导入结果
        importResult.success = res.success || 0
        importResult.failed = res.failed || 0
        importResult.errors = res.errors || []
        showImportResult.value = true

        if (res.success > 0) {
            feedback.msgSuccess(`导入成功，共导入${res.success}条数据，失败${res.failed}条`)
            // 刷新列表
            getLists()
        } else {
            feedback.msgError(`导入失败，共${res.failed}条数据错误`)
        }
    } catch (error: any) {
        feedback.msgError(error.message || '导入失败，请重试')
    } finally {
        isImporting.value = false
        feedback.closeLoading()
    }
}

// 关闭导入结果弹窗
const closeImportResult = () => {
    showImportResult.value = false
    importFile.value = null
    importResult.success = 0
    importResult.failed = 0
    importResult.errors = []
}

// 选中数据
const selectData = ref<any[]>([])

// 表格选择后回调事件
const handleSelectionChange = (val: any[]) => {
    selectData.value = val.map(({ id }) => id)
}
const handleChange = (value: any) => {
    queryParams.category_uid = value || ''
}

// 跳转题库页面
const handleQuestion = (data: any) => {
    router.push({
        path: 'question',
        query: {
            library_uid: data.uid
        }
    })
}

//跳转章节页面
const handleChapter = (data: any) => {
    router.push({
        path: '/exam/tenant_exam_chapter', // 修改为绝对路径
        query: {
            library_uid: data.uid
        }
    })
}

//跳转标签页面
const handleLabel = (data: any) => {
    router.push({
        path: '/exam/tenant_exam_label',
        query: {
            library_uid: data.uid // 确保这里正确传递了uid
        }
    })
}
// 获取字典数据
const { dictData } = useDictData('show_status,price_typ,data_year,hot_state,recommend_state')

// 分页相关
const { pager, getLists, resetParams, resetPage } = usePaging({
    fetchFun: apiTenantExamLibraryLists,
    params: queryParams
})

// 添加
const handleAdd = async () => {
    showEdit.value = true
    await nextTick()
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
    await apiTenantExamLibraryDelete({ id }).catch((error) => {
        console.error('删除失败:', error)
        throw error
    })
    getLists()
}
const fetchExamCategoryList = async () => {
    await apiExamCategoryTree()
        .then((res) => {
            Object.assign(categoryList, res)
        })
        .catch((error) => {
            console.error('获取试题分类树失败:', error)
            throw error
        })
}
fetchExamCategoryList()
getLists()
</script>
<style scoped></style>
