<template>
    <div>
        <el-card class="!border-none mb-4" shadow="never">
            <el-form class="mb-[-16px]" :model="queryParams" inline>
                <el-form-item label="用户ID" prop="user_id">
                    <el-input
                        class="w-[180px]"
                        v-model="queryParams.user_id"
                        clearable
                        placeholder="请输入用户ID"
                    />
                </el-form-item>

                <el-form-item label="备注" prop="remark">
                    <el-input
                        class="w-[180px]"
                        v-model="queryParams.remark"
                        clearable
                        placeholder="请输入备注"
                    />
                </el-form-item>
                <el-form-item label="名称" prop="title">
                    <el-input
                        class="w-[180px]"
                        v-model="queryParams.title"
                        clearable
                        placeholder="请输入积分名称"
                    />
                </el-form-item>
                <el-form-item label="变动类型" prop="change_type">
                    <el-select
                        class="w-[180px]"
                        v-model="queryParams.change_type"
                        clearable
                        placeholder="请选择变动类型"
                    >
                        <el-option label="全部" value=""></el-option>
                        <el-option
                            v-for="(item, index) in dictData.integral_change_type"
                            :key="index"
                            :label="item.name"
                            :value="item.value"
                        />
                    </el-select>
                </el-form-item>
                <el-form-item label="动作" prop="action">
                    <el-select
                        class="w-[180px]"
                        v-model="queryParams.action"
                        clearable
                        placeholder="请选择动作"
                    >
                        <el-option label="全部" value=""></el-option>
                        <el-option
                            v-for="(item, index) in dictData.integral_action"
                            :key="index"
                            :label="item.name"
                            :value="item.value"
                        />
                    </el-select>
                </el-form-item>
                <el-form-item label="操作类型" prop="action_type">
                    <el-select
                        class="w-[280px]"
                        v-model="queryParams.action_type"
                        clearable
                        placeholder="请选择操作类型"
                    >
                        <el-option label="全部" value=""></el-option>
                        <el-option
                            v-for="(item, index) in dictData.integral_action_type"
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
            <el-button
                v-perms="['exam.tenant_user_integral_log/add']"
                type="primary"
                @click="handleAdd"
                :icon="Plus"
            >
                新增积分记录
            </el-button>
            <el-button
                v-perms="['exam.tenant_user_integral_log/delete']"
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
                    <el-table-column label="用户ID" prop="user_id" width="100" align="center" />
                    <el-table-column
                        label="积分名称"
                        prop="title"
                        min-width="150"
                        show-overflow-tooltip
                    />
                    <el-table-column label="动作" width="100" align="center">
                        <template #default="{ row }">
                            <el-tag :type="getActionTagType(row.action)" size="small">
                                <dict-value
                                    :options="dictData.integral_action"
                                    :value="row.action"
                                />
                            </el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column label="变动数量" width="120" align="center">
                        <template #default="{ row }">
                            <span :class="getAmountClass(row.change_amount)" class="font-semibold">
                                {{ formatAmount(row.change_amount) }}
                            </span>
                        </template>
                    </el-table-column>
                    <el-table-column label="变动类型" width="120" align="center">
                        <template #default="{ row }">
                            <el-tag
                                :type="getChangeTypeTagType(row.change_type)"
                                size="small"
                                effect="plain"
                            >
                                <dict-value
                                    :options="dictData.integral_change_type"
                                    :value="row.change_type"
                                />
                            </el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column label="操作类型" width="140" align="center">
                        <template #default="{ row }">
                            <el-tag type="info" size="small" effect="plain">
                                <dict-value
                                    :options="dictData.integral_action_type"
                                    :value="row.action_type"
                                />
                            </el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="备注"
                        prop="remark"
                        min-width="180"
                        show-overflow-tooltip
                    >
                        <template #default="{ row }">
                            <el-text v-if="row.remark" type="info" size="small">
                                <el-icon><Document /></el-icon>
                                {{ row.remark }}
                            </el-text>
                            <span v-else class="text-gray-400">-</span>
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="创建时间"
                        prop="create_time"
                        width="160"
                        align="center"
                    />
                    <el-table-column label="操作" width="150" fixed="right" align="center">
                        <template #default="{ row }">
                            <el-button
                                v-perms="['exam.tenant_user_integral_log/edit']"
                                type="primary"
                                link
                                @click="handleEdit(row)"
                                :icon="Edit"
                            >
                                编辑
                            </el-button>
                            <el-button
                                v-perms="['exam.tenant_user_integral_log/delete']"
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

<script lang="ts" setup name="tenantUserIntegralLogLists">
import { Delete, Document, Edit, Plus } from '@element-plus/icons-vue'

import {
    apiTenantUserIntegralLogDelete,
    apiTenantUserIntegralLogLists
} from '@/api/exam/tenant_user_integral_log'
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
    action: '',
    change_amount: '',
    remark: '',
    extra: '',
    title: '',
    change_type: '',
    action_type: ''
})

// 选中数据
const selectData = ref<any[]>([])

// 表格选择后回调事件
const handleSelectionChange = (val: any[]) => {
    selectData.value = val.map(({ id }) => id)
}

// 获取字典数据
const { dictData } = useDictData('integral_action,integral_change_type,integral_action_type')

// 分页相关
const { pager, getLists, resetParams, resetPage } = usePaging({
    fetchFun: apiTenantUserIntegralLogLists,
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
    await apiTenantUserIntegralLogDelete({ id })
    getLists()
}

// 获取动作标签类型
const getActionTagType = (action: string): 'success' | 'danger' | 'warning' | 'info' => {
    // 1:获取积分 2:消耗积分 3:赠送积分 4:退还积分
    const typeMap: Record<string, 'success' | 'danger' | 'warning' | 'info'> = {
        '1': 'success', // 获取
        '2': 'danger', // 消耗
        '3': 'warning', // 赠送
        '4': 'info' // 退还
    }
    return typeMap[action] || 'info'
}

// 获取变动类型标签类型
const getChangeTypeTagType = (type: string): 'success' | 'warning' | 'info' => {
    // 1:系统 2:人工
    const typeMap: Record<string, 'success' | 'warning' | 'info'> = {
        '1': 'success', // 系统
        '2': 'warning' // 人工
    }
    return typeMap[type] || 'info'
}

// 格式化数量显示
const formatAmount = (amount: number | string): string => {
    const num = Number(amount)
    if (isNaN(num)) return '0'
    return num > 0 ? `+${num}` : String(num)
}

// 获取数量样式类
const getAmountClass = (amount: number | string): string => {
    const num = Number(amount)
    if (num > 0) return 'text-green-600'
    if (num < 0) return 'text-red-600'
    return 'text-gray-600'
}

getLists()
</script>

<style scoped>
.text-gray-400 {
    color: var(--el-text-color-placeholder);
}

.text-green-600 {
    color: #16a34a;
}

.text-red-600 {
    color: #dc2626;
}

.text-gray-600 {
    color: var(--el-text-color-regular);
}

.font-semibold {
    font-weight: 600;
}
</style>
