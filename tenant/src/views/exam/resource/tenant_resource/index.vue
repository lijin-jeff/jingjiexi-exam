<template>
    <div>
        <el-card class="!border-none mb-4" shadow="never">
            <el-form class="mb-[-16px]" :model="queryParams" inline>
                <el-form-item label="资源名称" prop="title">
                    <el-input
                        class="w-[280px]"
                        v-model="queryParams.title"
                        clearable
                        placeholder="请输入资源名称"
                    />
                </el-form-item>
                <el-form-item label="资源作者" prop="author">
                    <el-input
                        class="w-[280px]"
                        v-model="queryParams.author"
                        clearable
                        placeholder="请输入资源作者"
                    />
                </el-form-item>
                <el-form-item label="资源分类" prop="category_uid">
                    <el-cascader
                        v-model="queryParams.category_uid"
                        :options="categoryList"
                        :props="cascaderProps"
                        placeholder="请选择资源分类"
                        style="width: 100%"
                        clearable
                        filterable
                        @change="handleChange"
                    />
                </el-form-item>
                <el-form-item label="题库分类" prop="exam_category_uid">
                    <el-cascader
                        v-model="queryParams.exam_category_uid"
                        :options="examCategoryList"
                        :props="examCascaderProps"
                        placeholder="请选择题库分类"
                        style="width: 100%"
                        clearable
                        filterable
                        @change="handleChangeExamCategory"
                    />
                </el-form-item>
                <el-form-item label="上架状态" prop="is_show">
                    <el-select
                        class="w-[150px]"
                        v-model="queryParams.is_show"
                        clearable
                        placeholder="请选择上架状态"
                    >
                        <el-option label="全部" value=""></el-option>
                        <el-option
                            v-for="(item, index) in dictData.show_status"
                            :key="index"
                            :label="item.name"
                            :value="item.value"
                        />
                    </el-select>
                </el-form-item>
                <el-form-item label="付费状态" prop="free_state">
                    <el-select
                        class="w-[150px]"
                        v-model="queryParams.free_state"
                        clearable
                        placeholder="请选择付费状态"
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
                <el-form-item label="资源年份" prop="year">
                    <el-select
                        class="w-[150px]"
                        v-model="queryParams.year"
                        clearable
                        placeholder="请选择资源年份"
                    >
                        <el-option label="全部" value=""></el-option>
                        <el-option
                            v-for="(item, index) in dictData.data_year"
                            :key="index"
                            :label="item.name"
                            :value="item.value"
                        />
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="resetPage">查询</el-button>
                    <el-button @click="resetParams">重置</el-button>
                </el-form-item>
            </el-form>
        </el-card>
        <el-card class="!border-none" v-loading="pager.loading" shadow="never">
            <el-button v-perms="['resource.tenant_resource/add']" type="primary" @click="handleAdd">
                <template #icon>
                    <icon name="el-icon-Plus" />
                </template>
                新增
            </el-button>
            <el-button
                v-perms="['resource.tenant_resource/edit']"
                :disabled="!selectData.length"
                @click="handleBatchCopy"
            >
                批量复制
            </el-button>
            <el-button
                v-perms="['resource.tenant_resource/delete']"
                :disabled="!selectData.length"
                @click="handleDelete(selectData)"
            >
                删除
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
                        label="资源编号"
                        prop="uid"
                        min-width="100"
                        show-overflow-tooltip
                    />
                    <el-table-column label="资源分类" min-width="150" show-overflow-tooltip>
                        <template #default="{ row }">
                            <span v-if="row.categoryParent || row.category">
                                {{
                                    row.categoryParent?.title
                                        ? row.categoryParent.title + ' / '
                                        : ''
                                }}{{ row.category.title }}
                            </span>
                            <span v-else> 无 </span>
                        </template>
                    </el-table-column>
                    <el-table-column label="题库分类" min-width="80" show-overflow-tooltip>
                        <template #default="{ row }">
                            <span v-if="row.examCategories?.title">
                                {{ row.examCategories.title }}
                            </span>
                            <span v-else-if="row.examCategory?.title">
                                {{ row.examCategory.title }}
                            </span>
                            <span v-else>
                                {{ row.exam_category_title || '无' }}
                            </span>
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="资源名称"
                        prop="title"
                        min-width="200"
                        show-overflow-tooltip
                    />
                    <el-table-column label="分类封面" prop="image" width="100" align="center">
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
                    <el-table-column label="上架状态" prop="is_show" width="100" align="center">
                        <template #default="{ row }">
                            <el-tag :type="row.is_show == 1 ? 'success' : 'info'" size="small">
                                {{ row.is_show == 1 ? '已上架' : '已下架' }}
                            </el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="资源作者"
                        prop="author"
                        min-width="100"
                        show-overflow-tooltip
                    />
                    <el-table-column label="付费状态" prop="free_state" width="80" align="center">
                        <template #default="{ row }">
                            <el-tag
                                :type="row.free_state == 1 ? 'success' : 'warning'"
                                size="small"
                            >
                                {{ row.free_state == 1 ? '免费' : '付费' }}
                            </el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column label="资源价格" prop="money" width="100" align="center">
                        <template #default="{ row }">
                            <span class="text-primary font-semibold">￥{{ row.money }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column label="资源年份" prop="year" width="80" align="center">
                        <template #default="{ row }">
                            <dict-value :options="dictData.data_year" :value="row.year" />
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="显示权重"
                        prop="sort"
                        width="80"
                        align="center"
                        sortable
                    />
                    <el-table-column
                        label="创建时间"
                        prop="create_time"
                        min-width="160"
                        show-overflow-tooltip
                    />
                    <el-table-column
                        label="更新时间"
                        prop="update_time"
                        min-width="160"
                        show-overflow-tooltip
                    />
                    <el-table-column label="操作" width="180" fixed="right" align="center">
                        <template #default="{ row }">
                            <el-button
                                v-perms="['resource.tenant_resource/edit']"
                                type="primary"
                                link
                                size="small"
                                @click="handleEdit(row)"
                            >
                                <el-icon class="mr-1"><Edit /></el-icon>
                                编辑
                            </el-button>
                            <el-button
                                v-perms="['resource.tenant_resource/edit']"
                                type="primary"
                                link
                                size="small"
                                @click="handleCopy(row)"
                            >
                                <el-icon class="mr-1"><Edit /></el-icon>
                                复制
                            </el-button>

                            <el-button
                                v-perms="['resource.tenant_resource/delete']"
                                type="danger"
                                link
                                size="small"
                                @click="handleDelete(row.id)"
                            >
                                <el-icon class="mr-1"><Delete /></el-icon>
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

<script lang="ts" setup name="tenantResourceLists">
import { Delete, Edit } from '@element-plus/icons-vue'

import { apiExamCategoryTree } from '@/api/exam/exam_category'
import {
    apiTenantResourceCopy,
    apiTenantResourceDelete,
    apiTenantResourceLists
} from '@/api/exam/resource/tenant_resource'
import { apiTenantResourceCategoryTree } from '@/api/exam/resource/tenant_resource_category'
import { useDictData } from '@/hooks/useDictOptions'
import { usePaging } from '@/hooks/usePaging'
import feedback from '@/utils/feedback'

import EditPopup from './edit.vue'

const editRef = shallowRef<InstanceType<typeof EditPopup>>()
// 是否显示编辑框
const showEdit = ref(false)
const categoryList = reactive<any[]>([])
const examCategoryList = reactive<any[]>([])

// Cascader 配置 - 资源分类
const cascaderProps = {
    value: 'uid',
    label: 'title',
    children: 'children',
    checkStrictly: true, // 允许选择任意一级
    emitPath: false // 只返回选中节点的值，不返回路径数组
}

// Cascader 配置 - 题库分类
const examCascaderProps = {
    value: 'uid',
    label: 'title',
    children: 'children',
    checkStrictly: true, // 允许选择任意一级
    emitPath: false // 只返回选中节点的值，不返回路径数组
}

// 查询条件
const queryParams = reactive({
    title: '',
    is_show: '',
    category_uid: '',
    exam_category_uid: '',
    author: '',
    free_state: '',
    year: ''
})

// 选中数据
const selectData = ref<any[]>([])

const handleChange = (value: any) => {
    queryParams.category_uid = value || ''
}

const handleChangeExamCategory = (value: any) => {
    queryParams.exam_category_uid = value || ''
}

// 表格选择后回调事件
const handleSelectionChange = (val: any[]) => {
    selectData.value = val.map(({ id }) => id)
}

// 获取字典数据
const { dictData } = useDictData('show_status,price_typ,data_year')

// 分页相关
const { pager, getLists, resetParams, resetPage } = usePaging({
    fetchFun: apiTenantResourceLists,
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

// 复制单个资源
const handleCopy = async (row: any) => {
    try {
        // 实现资源数据的深拷贝
        const copyData = JSON.parse(JSON.stringify(row))
        // 调用复制资源的 API
        await apiTenantResourceCopy({ id: copyData.id })
        // 复制成功后更新资源列表
        getLists()
        // 显示复制成功的提示
        feedback.msgSuccess('资源复制成功')
    } catch (error) {
        // 显示复制失败的提示
        feedback.msgError('资源复制失败，请稍后重试')
        console.error('资源复制失败:', error)
    }
}

// 批量复制资源
const handleBatchCopy = async () => {
    try {
        // 调用批量复制资源的 API
        await apiTenantResourceCopy({ id: selectData.value })
        // 复制成功后更新资源列表
        getLists()
        // 显示批量复制成功的提示
        feedback.msgSuccess(`成功复制 ${selectData.value.length} 个资源`)
    } catch (error) {
        // 显示批量复制失败的提示
        feedback.msgError('批量复制失败，请稍后重试')
        console.error('批量复制失败:', error)
    }
}

const fetchExamCategoryList = async () => {
    try {
        const res = await apiTenantResourceCategoryTree()
        // 清空旧数据并添加新数据
        categoryList.length = 0
        categoryList.push(...(res || []))
    } catch (error) {
        console.error('获取资源分类失败:', error)
    }
}
fetchExamCategoryList()

const fetchCategoryList = async () => {
    try {
        const res = await apiExamCategoryTree()

        // 清空旧数据并添加新数据
        examCategoryList.length = 0
        examCategoryList.push(...(res || []))
    } catch (error) {
        console.error('获取题库分类失败:', error)
    }
}
fetchCategoryList()

// 删除
const handleDelete = async (id: number | any[]) => {
    await feedback.confirm('确定要删除？')
    await apiTenantResourceDelete({ id })
    getLists()
}

getLists()
</script>
