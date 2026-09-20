<template>
    <div>
        <el-card class="!border-none mb-4" shadow="never">
            <el-form class="mb-[-16px]" :model="queryParams" inline>
                <el-form-item label="版本号">
                    <el-input
                        class="w-[200px]"
                        v-model="queryParams.version"
                        clearable
                        placeholder="请输入版本号"
                    />
                </el-form-item>

                <el-form-item label="状态">
                    <el-select
                        class="w-[200px]"
                        v-model="queryParams.status"
                        clearable
                        placeholder="请选择状态"
                    >
                        <el-option label="已发布" :value="1" />
                        <el-option label="未发布" :value="0" />
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="resetPage">查询</el-button>
                    <el-button @click="resetParams">重置</el-button>
                </el-form-item>
            </el-form>
        </el-card>
        <el-card class="!border-none" v-loading="pager.loading" shadow="never">
            <el-button
                v-perms="['exam.version_update/add']"
                type="primary"
                @click="handleAdd"
                :icon="Plus"
            >
                新增版本
            </el-button>
            <el-button
                v-perms="['exam.version_update/delete']"
                :disabled="!selectData.length"
                @click="handleDelete(selectData)"
                :icon="Delete"
            >
                批量删除
            </el-button>
            <div class="mt-4">
                <el-table
                    :data="pager.lists"
                    stripe
                    border
                    @selection-change="handleSelectionChange"
                >
                    <el-table-column type="selection" width="55" align="center" />
                    <el-table-column label="ID" prop="id" width="80" align="center" />
                    <el-table-column
                        label="版本号"
                        prop="version"
                        min-width="120"
                        show-overflow-tooltip
                    />
                    <el-table-column
                        label="更新内容"
                        prop="info"
                        min-width="250"
                        show-overflow-tooltip
                    />
                    <el-table-column label="状态" width="200" align="center">
                        <template #default="{ row }">
                            <el-switch
                                v-model="row.status"
                                :active-value="1"
                                :inactive-value="0"
                                active-text="已发布"
                                inactive-text="未发布"
                                @change="handleStatusChange(row)"
                                v-perms="['exam.version_update/updateStatus']"
                            />
                        </template>
                    </el-table-column>
                    <el-table-column label="发布时间" width="180" align="center">
                        <template #default="{ row }">
                            <span v-if="row.release_time">{{ row.release_time }}</span>
                            <span v-else class="text-gray-400">未发布</span>
                        </template>
                    </el-table-column>
                    <el-table-column label="创建时间" width="180" align="center">
                        <template #default="{ row }">
                            {{ row.create_time }}
                        </template>
                    </el-table-column>
                    <el-table-column label="操作" width="150" fixed="right" align="center">
                        <template #default="{ row }">
                            <el-button
                                v-perms="['exam.version_update/edit']"
                                type="primary"
                                link
                                @click="handleEdit(row)"
                                :icon="Edit"
                            >
                                编辑
                            </el-button>
                            <el-button
                                v-perms="['exam.version_update/delete']"
                                type="danger"
                                link
                                @click="handleDelete(row.id)"
                                :icon="Delete"
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
        <edit-popup v-if="showEdit" ref="editRef" @success="getLists" @close="showEdit = false" />
    </div>
</template>

<script lang="ts" setup name="versionUpdateIndex">
import { Delete, Edit, Plus } from '@element-plus/icons-vue'

import {
    versionUpdateDelete,
    versionUpdateLists,
    versionUpdateStatus
} from '@/api/exam/version_update'
import { usePaging } from '@/hooks/usePaging'
import feedback from '@/utils/feedback'

import EditPopup from './edit.vue'

const editRef = shallowRef<InstanceType<typeof EditPopup>>()
// 是否显示编辑框
const showEdit = ref(false)

// 查询条件
const queryParams = reactive({
    version: '',
    status: ''
})

// 选中数据
const selectData = ref<any[]>([])

// 表格选择后回调事件
const handleSelectionChange = (val: any[]) => {
    selectData.value = val.map(({ id }) => id)
}

// 分页相关
const { pager, getLists, resetParams, resetPage } = usePaging({
    fetchFun: versionUpdateLists,
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
    await versionUpdateDelete({ id })
    getLists()
}

// 状态变化
const handleStatusChange = async (row: any) => {
    try {
        await versionUpdateStatus({
            id: row.id,
            status: row.status,
            release_time: row.status === 1 ? Math.floor(Date.now() / 1000) : 0
        })
        getLists()
    } catch (error) {
        // 恢复原状态
        getLists()
        console.error('状态更新失败', error)
    }
}

getLists()
</script>

<style scoped>
.text-gray-400 {
    color: var(--el-text-color-placeholder);
}
</style>
