<template>
    <div class="tenant-exam-label-page p-4 md:p-6">
        <!-- 页面头部 -->
        <el-card class="mb-4 shadow-sm border rounded-lg">
            <el-page-header content="标签管理" @back="$router.back()" class="border-b-0" />
        </el-card>

        <!-- 查询卡片 -->
        <el-card class="mb-4 shadow-sm border rounded-lg">
            <el-form
                class="query-form space-y-4 md:space-y-0 md:flex flex-wrap gap-4 items-center"
                :model="queryParams"
                inline
            >
                <el-form-item label="标签名称" prop="title" class="flex-1 min-w-[280px]">
                    <el-input
                        v-model="queryParams.title"
                        clearable
                        placeholder="请输入标签名称"
                        size="large"
                        class="w-full"
                    />
                </el-form-item>
                <el-form-item label="所属题库" prop="library_uid" class="flex-1 min-w-[280px]">
                    <el-select
                        v-model="queryParams.library_uid"
                        placeholder="请选择所属题库"
                        size="large"
                        class="w-full"
                        filterable
                    >
                        <el-option
                            v-for="item in libraryList"
                            :key="item.uid"
                            :label="item.title"
                            :value="item.uid"
                        />
                    </el-select>
                </el-form-item>
                <el-form-item class="flex gap-2 mt-2 md:mt-0">
                    <el-button type="primary" @click="resetPage" size="large" class="w-32">
                        查询
                    </el-button>
                    <el-button @click="resetParams" size="large" class="w-32"> 重置 </el-button>
                </el-form-item>
            </el-form>
        </el-card>

        <!-- 数据卡片 -->
        <el-card class="shadow-sm border rounded-lg" v-loading="pager.loading">
            <!-- 操作按钮区域 -->
            <div class="flex flex-wrap justify-between items-center mb-4 pb-3 border-b">
                <div class="flex gap-2 mb-2 md:mb-0">
                    <el-button
                        v-perms="['exam.tenant_exam_label/add']"
                        type="primary"
                        @click="handleAdd"
                        size="large"
                        class="flex items-center gap-2"
                    >
                        <el-icon><Plus /></el-icon>
                        新增标签
                    </el-button>
                    <el-button
                        v-perms="['exam.tenant_exam_label/delete']"
                        :disabled="!selectData.length"
                        @click="handleDelete(selectData)"
                        type="danger"
                        size="large"
                        class="flex items-center gap-2"
                    >
                        <el-icon><Delete /></el-icon>
                        删除选中
                    </el-button>
                </div>
                <div class="text-sm text-gray-500">共 {{ pager.count }} 条记录</div>
            </div>

            <!-- 数据表格 -->
            <el-table
                :data="pager.lists"
                style="width: 100%"
                row-key="id"
                @selection-change="handleSelectionChange"
                :default-expand-all="false"
                :tree-props="{ children: 'children', hasChildren: 'hasChildren' }"
                size="large"
                class="mb-4"
                v-loading="pager.loading"
            >
                <el-table-column type="selection" width="60" align="center" reserve-selection />
                <el-table-column
                    label="标签名称"
                    prop="title"
                    show-overflow-tooltip
                    min-width="200"
                    align="left"
                    class-name="font-medium"
                />
                <!-- 所属题库名称，若未关联题库，则显示“公共标签” -->
                <el-table-column label="所属题库" prop="library_uid" min-width="150" align="center">
                    <template #default="{ row }">
                        <el-tag :type="row.library_uid == '0' ? 'success' : 'info'" size="small">
                            {{
                                row.library_uid == '0' ? '公共标签' : row.library?.title || '未关联'
                            }}
                        </el-tag>
                    </template>
                </el-table-column>
                <el-table-column label="显示状态" prop="is_show" width="120" align="center">
                    <template #default="{ row }">
                        <dict-value :options="dictData.show_status" :value="row.is_show" />
                    </template>
                </el-table-column>
                <el-table-column label="显示权重" prop="sort" width="120" align="center">
                    <template #default="{ row }">
                        <div class="flex items-center justify-center">
                            <el-input-number
                                v-model="row.sort"
                                :min="0"
                                :max="100"
                                :step="1"
                                size="small"
                                @change="handleSortChange"
                                class="w-20"
                            />
                        </div>
                    </template>
                </el-table-column>
                <el-table-column
                    label="操作"
                    width="200"
                    fixed="right"
                    align="center"
                    class-name="operation-column"
                >
                    <template #default="{ row }">
                        <div class="flex items-center justify-center gap-2">
                            <el-button
                                v-perms="['exam.tenant_exam_label/edit']"
                                type="primary"
                                link
                                @click="handleEdit(row)"
                                size="small"
                                class="text-primary hover:text-primary/90"
                            >
                                编辑
                            </el-button>
                            <el-button
                                v-perms="['exam.tenant_exam_label/delete']"
                                type="danger"
                                link
                                @click="handleDelete(row.id)"
                                size="small"
                                class="text-danger hover:text-danger/90"
                            >
                                删除
                            </el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>

            <!-- 分页区域 -->
            <div class="flex items-center justify-between pt-4 border-t">
                <div class="text-sm text-gray-500">
                    显示第 {{ pager.page }} 页，共 {{ Math.ceil(pager.count / pager.size) }} 页
                </div>
                <pagination
                    v-model="pager"
                    @change="getLists"
                    background
                    layout="total, sizes, prev, pager, next, jumper"
                    :page-sizes="[10, 20, 50, 100]"
                    hide-on-single-page
                />
            </div>
        </el-card>

        <!-- 编辑弹窗 -->
        <edit-popup
            v-if="showEdit"
            ref="editRef"
            :dict-data="dictData"
            @success="getLists"
            @close="showEdit = false"
        />
    </div>
</template>

<script lang="ts" setup name="tenantExamLabelLists">
import { Delete, Plus } from '@element-plus/icons-vue'
import { nextTick, reactive, ref, shallowRef } from 'vue'

import { apiTenantExamLabelDelete, apiTenantExamLabelLists } from '@/api/exam/tenant_exam_label'
import { apiTenantExamLibraryLists } from '@/api/exam/tenant_exam_library'
import { useDictData } from '@/hooks/useDictOptions'
import { usePaging } from '@/hooks/usePaging'
import feedback from '@/utils/feedback'

import EditPopup from './edit.vue'

const editRef = shallowRef<InstanceType<typeof EditPopup>>()
// 是否显示编辑框
const showEdit = ref(false)
const route = useRoute()
// 查询条件
const queryParams = reactive({
    title: '',
    is_show: '',
    library_uid: route.query.library_uid || '0'
})

// 题库列表
const libraryList = ref<any[]>([])

// 选中数据
const selectData = ref<any[]>([])

// 表格选择后回调事件
const handleSelectionChange = (val: any[]) => {
    selectData.value = val.map(({ id }) => id)
}

// 处理显示权重变化
const handleSortChange = async (value: number | undefined, prevValue: number | undefined) => {
    // 这里可以添加更新权重的API调用
    console.log('权重变化:', value, prevValue)
}

// 获取字典数据
const { dictData } = useDictData('show_status')

// 分页相关
const { pager, getLists, resetParams, resetPage } = usePaging({
    fetchFun: apiTenantExamLabelLists,
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
    await apiTenantExamLabelDelete({ id })
    getLists()
}

const fetchLibraryList = async () => {
    try {
        const params = { category_uid: '' }
        const res = await apiTenantExamLibraryLists(params)
        const lists = res?.lists || []
        // 添加公共标签选项
        lists.unshift({
            uid: '0',
            title: '公共标签'
        })
        // 正确赋值ref数组
        libraryList.value = lists
    } catch (error) {
        console.error('获取题库列表失败:', error)
        libraryList.value = []
        // 出错时仍添加公共标签选项
        libraryList.value = [
            {
                uid: '0',
                title: '公共标签'
            }
        ]
    }
}
fetchLibraryList()

getLists()
</script>

<style scoped>
.tenant-exam-label-page {
    min-height: calc(100vh - 2rem);
    background-color: #f5f7fa;
}

.query-form {
    .el-form-item {
        margin-bottom: 0;
    }
}

.operation-column .el-button {
    margin: 0;
    padding: 0 8px;
    font-size: 13px;
}

/* 响应式设计优化 */
@media (max-width: 768px) {
    .tenant-exam-label-page {
        padding: 1rem;
    }

    .operation-column {
        min-width: 120px;
    }
}
</style>
