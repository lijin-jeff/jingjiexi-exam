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

                <el-form-item label="订阅类型" prop="type">
                    <el-select
                        class="w-[180px]"
                        v-model="queryParams.type"
                        clearable
                        placeholder="请选择订阅类型"
                    >
                        <el-option label="全部" value=""></el-option>
                        <el-option label="考试提醒" value="exam"></el-option>
                        <el-option label="倒计时提醒" value="countdown"></el-option>
                        <el-option label="版本更新" value="version_update"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="发送状态" prop="send_status">
                    <el-select
                        class="w-[180px]"
                        v-model="queryParams.send_status"
                        clearable
                        placeholder="请选择发送状态"
                    >
                        <el-option label="全部" value=""></el-option>
                        <el-option label="待发送" value="0"></el-option>
                        <el-option label="发送成功" value="1"></el-option>
                        <el-option label="发送失败" value="2"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="时间范围">
                    <el-date-picker
                        v-model="dateRange"
                        type="daterange"
                        range-separator="至"
                        start-placeholder="开始日期"
                        end-placeholder="结束日期"
                        format="YYYY-MM-DD"
                        value-format="YYYY-MM-DD"
                        class="w-[280px]"
                    />
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="resetPage">查询</el-button>
                    <el-button @click="resetParams">重置</el-button>
                </el-form-item>
            </el-form>
        </el-card>
        <el-card class="!border-none" v-loading="pager.loading" shadow="never">
            <div class="flex justify-between items-center mb-4">
                <el-button
                    type="danger"
                    @click="handleBatchDelete"
                    :disabled="selectData.length === 0"
                >
                    批量删除
                </el-button>
            </div>
            <div>
                <el-table
                    :data="pager.lists"
                    stripe
                    border
                    @selection-change="handleSelectionChange"
                >
                    <el-table-column type="selection" width="55" align="center" />
                    <el-table-column label="ID" prop="id" width="80" align="center" />
                    <el-table-column label="租户ID" prop="tenant_id" width="100" align="center" />
                    <el-table-column label="用户ID" prop="user_id" width="100" align="center" />
                    <el-table-column
                        label="OpenID"
                        prop="openid"
                        width="180"
                        show-overflow-tooltip
                    />
                    <el-table-column label="订阅类型" width="120" align="center">
                        <template #default="{ row }">
                            <el-tag :type="getTypeTagType(row.type)" size="small">
                                {{ getTypeText(row.type) }}
                            </el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column label="关联ID" prop="related_id" width="100" align="center" />
                    <el-table-column
                        label="模板ID"
                        prop="template_id"
                        width="180"
                        show-overflow-tooltip
                    />
                    <el-table-column label="发送状态" width="120" align="center">
                        <template #default="{ row }">
                            <el-tag :type="getSendStatusTagType(row.send_status)" size="small">
                                {{ getSendStatusText(row.send_status) }}
                            </el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="重试次数"
                        prop="retry_times"
                        width="100"
                        align="center"
                    />
                    <el-table-column label="发送时间" prop="send_time" width="180" align="center" />
                    <el-table-column
                        label="错误信息"
                        prop="error_msg"
                        min-width="180"
                        show-overflow-tooltip
                    />
                    <el-table-column
                        label="创建时间"
                        prop="create_time"
                        width="180"
                        align="center"
                    />
                    <el-table-column label="操作" width="120" align="center" fixed="right">
                        <template #default="{ row }">
                            <el-button
                                type="danger"
                                text
                                size="small"
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
    </div>
</template>

<script lang="ts" setup name="tenantSubscribeSendLogLists">
import { reactive, ref } from 'vue'

import {
    subscribeSendLogBatchDelete,
    subscribeSendLogDelete,
    subscribeSendLogLists
} from '@/api/exam/tenant_subscribe_send_log'
import { usePaging } from '@/hooks/usePaging'
import feedback from '@/utils/feedback'

// 选中数据
const selectData = ref<any[]>([])

// 日期范围
const dateRange = ref<string[]>([])

// 查询条件
const queryParams = reactive({
    tenant_id: '',
    user_id: '',
    type: '',
    send_status: '',
    start_date: '',
    end_date: '',
    page_no: 1,
    page_size: 15
})

// 表格选择后回调事件
const handleSelectionChange = (val: any[]) => {
    selectData.value = val.map(({ id }) => id)
}

// 订阅类型文本映射
const typeMap: Record<string, string> = {
    exam: '考试提醒',
    countdown: '倒计时提醒',
    version_update: '版本更新'
}

// 发送状态文本映射
const sendStatusMap: Record<number, string> = {
    0: '待发送',
    1: '发送成功',
    2: '发送失败'
}

// 获取订阅类型文本
const getTypeText = (type: string): string => {
    return typeMap[type] || type
}

// 获取发送状态文本
const getSendStatusText = (status: number): string => {
    return sendStatusMap[status] || '未知'
}

// 获取类型标签类型
const getTypeTagType = (type: string): 'success' | 'danger' | 'warning' | 'info' => {
    const typeMap: Record<string, 'success' | 'danger' | 'warning' | 'info'> = {
        exam: 'success',
        countdown: 'warning',
        version_update: 'info'
    }
    return typeMap[type] || 'info'
}

// 获取发送状态标签类型
const getSendStatusTagType = (status: number): 'success' | 'danger' | 'warning' | 'info' => {
    const statusMap: Record<number, 'success' | 'danger' | 'warning' | 'info'> = {
        0: 'warning',
        1: 'success',
        2: 'danger'
    }
    return statusMap[status] || 'info'
}

// 分页相关
const {
    pager,
    getLists,
    resetParams: resetPagingParams,
    resetPage: resetPagingPage
} = usePaging({
    fetchFun: subscribeSendLogLists,
    params: queryParams
})

// 处理日期范围
const handleDateRange = () => {
    if (dateRange.value && dateRange.value.length === 2) {
        queryParams.start_date = dateRange.value[0]
        queryParams.end_date = dateRange.value[1]
    } else {
        queryParams.start_date = ''
        queryParams.end_date = ''
    }
}

// 重置查询参数
const resetParams = () => {
    resetPagingParams()
    dateRange.value = []
    handleDateRange()
}

// 重置页码并查询
const resetPage = () => {
    handleDateRange()
    resetPagingPage()
}

// 获取列表数据
const getListData = () => {
    handleDateRange()
    getLists()
}

// 删除
const handleDelete = async (id: number) => {
    await feedback.confirm('确定要删除？')
    await subscribeSendLogDelete({ id })
    getLists()
}

// 批量删除
const handleBatchDelete = async () => {
    if (selectData.value.length === 0) {
        feedback.msgError('请选择要删除的记录')
        return
    }
    await feedback.confirm('确定要批量删除？')
    await subscribeSendLogBatchDelete({ ids: selectData.value })
    getLists()
}

getLists()
</script>

<style scoped>
.text-gray-400 {
    color: var(--el-text-color-placeholder);
}
</style>
