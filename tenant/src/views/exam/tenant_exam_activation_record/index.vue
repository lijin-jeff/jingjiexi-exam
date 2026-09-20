<template>
    <div>
        <el-card class="!border-none mb-4" shadow="never">
            <el-form class="mb-[-16px]" :model="queryParams" inline>
                <el-form-item label="租户ID" prop="tenant_id">
                    <el-input
                        class="w-[280px]"
                        v-model="queryParams.tenant_id"
                        clearable
                        placeholder="请输入租户ID"
                    />
                </el-form-item>
                <el-form-item label="激活用户ID" prop="user_id">
                    <el-input
                        class="w-[280px]"
                        v-model="queryParams.user_id"
                        clearable
                        placeholder="请输入激活用户ID"
                    />
                </el-form-item>
                <el-form-item label="激活用户姓名" prop="user_name">
                    <el-input
                        class="w-[280px]"
                        v-model="queryParams.user_name"
                        clearable
                        placeholder="请输入激活用户姓名"
                    />
                </el-form-item>
                <el-form-item label="激活时间" prop="activation_time">
                    <daterange-picker
                        v-model:startTime="queryParams.start_time"
                        v-model:endTime="queryParams.end_time"
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
                v-perms="['exam.tenant_exam_activation_record/delete']"
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
                    <el-table-column label="租户ID" prop="tenant_id" width="100" align="center" />
                    <el-table-column label="批次ID" prop="batch_id" width="100" align="center" />
                    <el-table-column label="激活码" prop="code" width="180" align="center">
                        <template #default="{ row }">
                            <el-tag type="success" effect="plain">
                                {{ row.code || '-' }}
                            </el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column label="用户信息" min-width="180" align="center">
                        <template #default="{ row }">
                            <div class="flex flex-col gap-1">
                                <div class="flex items-center justify-center gap-2">
                                    <el-icon><User /></el-icon>
                                    <span>{{ row.user_name || '未知' }}</span>
                                </div>
                                <el-text type="info" size="small">ID: {{ row.user_id }}</el-text>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="激活IP" prop="activation_ip" width="140" align="center">
                        <template #default="{ row }">
                            <el-tag
                                v-if="row.activation_ip"
                                type="info"
                                effect="plain"
                                size="small"
                            >
                                <el-icon><Location /></el-icon>
                                {{ row.activation_ip }}
                            </el-tag>
                            <span v-else class="text-gray-400">-</span>
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="激活时间"
                        prop="activation_time"
                        width="160"
                        align="center"
                    />
                    <el-table-column
                        label="过期时间"
                        prop="expiration_time"
                        width="160"
                        align="center"
                    >
                        <template #default="{ row }">
                            <div v-if="row.expiration_time">
                                <el-tag
                                    :type="isExpired(row.expiration_time) ? 'danger' : 'success'"
                                    size="small"
                                >
                                    {{ row.expiration_time }}
                                </el-tag>
                                <div
                                    v-if="isExpired(row.expiration_time)"
                                    class="text-xs text-red-500 mt-1"
                                >
                                    已过期
                                </div>
                            </div>
                            <span v-else class="text-gray-400">-</span>
                        </template>
                    </el-table-column>
                    <el-table-column label="操作" width="120" fixed="right" align="center">
                        <template #default="{ row }">
                            <el-button
                                v-perms="['exam.tenant_exam_activation_record/delete']"
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
        <edit-popup
            v-if="showEdit"
            ref="editRef"
            :dict-data="dictData"
            @success="getLists"
            @close="showEdit = false"
        />
    </div>
</template>

<script lang="ts" setup name="tenantExamActivationRecordLists">
import { Delete, Location, User } from '@element-plus/icons-vue'

import {
    apiTenantExamActivationRecordDelete,
    apiTenantExamActivationRecordLists
} from '@/api/exam/tenant_exam_activation_record'
import { useDictData } from '@/hooks/useDictOptions'
import { usePaging } from '@/hooks/usePaging'
import feedback from '@/utils/feedback'

import EditPopup from './edit.vue'

const editRef = shallowRef<InstanceType<typeof EditPopup>>()
// 是否显示编辑框
const showEdit = ref(false)

// 查询条件
const queryParams = reactive({
    tenant_id: '',
    user_id: '',
    user_name: '',
    activation_time: '',
    start_time: '',
    end_time: ''
})

// 选中数据
const selectData = ref<any[]>([])

// 表格选择后回调事件
const handleSelectionChange = (val: any[]) => {
    selectData.value = val.map(({ id }) => id)
}

// 获取字典数据
const { dictData } = useDictData('')

// 分页相关
const { pager, getLists, resetParams, resetPage } = usePaging({
    fetchFun: apiTenantExamActivationRecordLists,
    params: queryParams
})

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
    await apiTenantExamActivationRecordDelete({ id })
    getLists()
}

// 判断是否过期
const isExpired = (expirationTime: string) => {
    if (!expirationTime) return false
    return new Date(expirationTime) < new Date()
}

getLists()
</script>

<style scoped>
.flex {
    display: flex;
}

.flex-col {
    flex-direction: column;
}

.items-center {
    align-items: center;
}

.justify-center {
    justify-content: center;
}

.gap-1 {
    gap: 4px;
}

.gap-2 {
    gap: 8px;
}

.text-gray-400 {
    color: var(--el-text-color-placeholder);
}

.text-red-500 {
    color: var(--el-color-danger);
}

.text-xs {
    font-size: 12px;
}

.mt-1 {
    margin-top: 4px;
}
</style>
