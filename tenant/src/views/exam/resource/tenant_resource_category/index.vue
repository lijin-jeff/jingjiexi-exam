<template>
    <div>
        <el-card class="!border-none mb-4" shadow="never">
            <el-form class="mb-[-16px]" :model="queryParams" inline>
                <el-form-item label="分类名称" prop="title">
                    <el-input
                        class="w-[280px]"
                        v-model="queryParams.title"
                        clearable
                        placeholder="请输入分类名称"
                    />
                </el-form-item>
                <el-form-item label="显示状态" prop="is_show">
                    <el-select
                        class="w-[280px]"
                        style="width: 100px"
                        v-model="queryParams.is_show"
                        clearable
                        placeholder="请选择显示状态"
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
                <el-form-item label="所属题库" prop="exam_category_uid">
                    <el-cascader
                        v-model="queryParams.exam_category_uid"
                        :options="examCategoryList"
                        :props="examCascaderProps"
                        placeholder="请选择题库分类"
                        style="width: 280px"
                        clearable
                        filterable
                    />
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="resetPage">查询</el-button>
                    <el-button @click="resetParams">重置</el-button>
                </el-form-item>
            </el-form>
        </el-card>
        <el-card class="!border-none" v-loading="pager.loading" shadow="never">
            <el-button
                v-perms="['resource.tenant_resource_category/add']"
                type="primary"
                @click="handleAdd"
            >
                <template #icon>
                    <icon name="el-icon-Plus" />
                </template>
                新增
            </el-button>
            <el-button
                v-perms="['resource.tenant_resource_category/delete']"
                :disabled="!selectData.length"
                @click="handleDelete(selectData)"
            >
                删除
            </el-button>
            <div class="mt-4">
                <el-table
                    :data="pager.lists"
                    style="width: 100%; margin-bottom: 20px"
                    row-key="id"
                    @selection-change="handleSelectionChange"
                    :default-expand-all="false"
                    :tree-props="{ children: 'children', hasChildren: 'hasChildren' }"
                    :cell-style="{ 'text-align': 'center' }"
                    :header-cell-style="{ 'text-align': 'center' }"
                >
                    <el-table-column type="selection" width="55" />
                    <el-table-column label="分类名称" prop="title" show-overflow-tooltip />
                    <el-table-column
                        label="所属题库"
                        prop="examCategory.title"
                        show-overflow-tooltip
                    />
                    <el-table-column label="分类封面" prop="image">
                        <template #default="{ row }">
                            <el-image style="width: 50px; height: 50px" :src="row.image" />
                        </template>
                    </el-table-column>
                    <el-table-column label="显示状态" prop="is_show">
                        <template #default="{ row }">
                            <dict-value :options="dictData.show_status" :value="row.is_show" />
                        </template>
                    </el-table-column>
                    <el-table-column label="显示权重" prop="sort" show-overflow-tooltip />
                    <el-table-column label="更新时间" prop="update_time" show-overflow-tooltip />
                    <el-table-column label="操作" width="120" fixed="right">
                        <template #default="{ row }">
                            <el-button
                                v-perms="['resource.tenant_resource_category/edit']"
                                type="primary"
                                link
                                @click="handleEdit(row)"
                            >
                                编辑
                            </el-button>
                            <el-button
                                v-perms="['resource.tenant_resource_category/delete']"
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

<script lang="ts" setup name="tenantResourceCategoryLists">
import { apiExamCategoryTree } from '@/api/exam/exam_category'
import {
    apiTenantResourceCategoryDelete,
    apiTenantResourceCategoryLists
} from '@/api/exam/resource/tenant_resource_category'
import { useDictData } from '@/hooks/useDictOptions'
import { usePaging } from '@/hooks/usePaging'
import feedback from '@/utils/feedback'

import EditPopup from './edit.vue'

const editRef = shallowRef<InstanceType<typeof EditPopup>>()
// 是否显示编辑框
const showEdit = ref(false)

// 题库分类数据
const examCategoryList = reactive<any[]>([])

// 级联选择器配置 - 题库分类
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
    exam_category_uid: ''
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
    fetchFun: apiTenantResourceCategoryLists,
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
    await apiTenantResourceCategoryDelete({ id })
    getLists()
}

// 获取题库分类数据
const fetchExamCategoryList = async () => {
    try {
        const res = await apiExamCategoryTree()
        examCategoryList.length = 0
        examCategoryList.push(...(res || []))
    } catch (error) {
        console.error('获取题库分类失败:', error)
    }
}
fetchExamCategoryList()

getLists()
</script>
