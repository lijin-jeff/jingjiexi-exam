<template>
    <el-dialog title="激活码列表" v-model="visible" width="960px">
        <el-card class="!border-none mb-4" shadow="never">
            <el-form class="mb-[-16px]" :model="queryParams" inline>
                <el-form-item label="激活码" prop="code">
                    <el-input
                        class="w-[180px]"
                        v-model="queryParams.code"
                        clearable
                        placeholder="请输入激活码"
                    />
                </el-form-item>
                <el-form-item label="状态" prop="status">
                    <el-select
                        class="w-[180px]"
                        v-model="queryParams.status"
                        clearable
                        placeholder="请选择状态"
                    >
                        <el-option label="全部" value=""></el-option>
                        <el-option
                            v-for="(item, index) in dictData.system_disable"
                            :key="index"
                            :label="item.name"
                            :value="item.value"
                        />
                    </el-select>
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
                    <el-button @click="exportTxt">导出txt</el-button>
                </el-form-item>
            </el-form>
        </el-card>
        <el-card class="!border-none" v-loading="pager.loading" shadow="never">
            <div class="mt-4">
                <el-table
                    :data="pager.lists"
                    stripe
                    border
                    @selection-change="handleSelectionChange"
                >
                    <el-table-column label="ID" prop="id" width="80" align="center" />
                    <el-table-column label="激活码" prop="code" min-width="180" align="center">
                        <template #default="{ row }">
                            <div class="flex items-center gap-2">
                                <el-tag
                                    :type="row.activation_time ? 'success' : 'info'"
                                    effect="plain"
                                >
                                    {{ row.code }}
                                </el-tag>
                                <el-button
                                    size="small"
                                    type="primary"
                                    circle
                                    @click="copyCode(row.code)"
                                    title="复制"
                                >
                                    <el-icon><DocumentCopy /></el-icon>
                                </el-button>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="状态" width="120" align="center">
                        <template #default="{ row }">
                            <el-tag :type="getStatusTagType(row)" size="small">
                                {{ getStatusText(row) }}
                            </el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="激活时间"
                        prop="activation_time"
                        width="160"
                        align="center"
                    >
                        <template #default="{ row }">
                            <span v-if="row.activation_time">{{ row.activation_time }}</span>
                            <span v-else class="text-gray-400">-</span>
                        </template>
                    </el-table-column>
                    <el-table-column label="操作" width="160" align="center" fixed="right">
                        <template #default="{ row }">
                            <el-tooltip
                                v-if="row.activation_time"
                                content="已使用的激活码不能修改状态"
                                placement="top"
                            >
                                <el-switch
                                    class="ml-2"
                                    inline-prompt
                                    style="
                                        --el-switch-on-color: #13ce66;
                                        --el-switch-off-color: #ff4949;
                                    "
                                    active-text="启用"
                                    inactive-text="禁用"
                                    v-model="row.status"
                                    :active-value="0"
                                    :inactive-value="1"
                                    disabled
                                />
                            </el-tooltip>
                            <el-switch
                                v-else
                                class="ml-2"
                                inline-prompt
                                style="
                                    --el-switch-on-color: #13ce66;
                                    --el-switch-off-color: #ff4949;
                                "
                                active-text="启用"
                                inactive-text="禁用"
                                v-perms="['exam.tenant_exam_activation_code/updateStatus']"
                                v-model="row.status"
                                :active-value="0"
                                :inactive-value="1"
                                @change="changeStatus(row.id, row.status)"
                            />
                        </template>
                    </el-table-column>
                </el-table>
            </div>
            <div class="flex mt-4 justify-end">
                <pagination v-model="pager" @change="getLists" />
            </div>
        </el-card>
    </el-dialog>
</template>

<script lang="ts" setup name="tenantExamActivationCodeBatch">
import { DocumentCopy } from '@element-plus/icons-vue'
import { ElMessage } from 'element-plus'
import { ElIcon } from 'element-plus'
import { reactive, ref } from 'vue'

import {
    apiTenantExamActivationCodeDisableEnable,
    apiTenantExamActivationCodeLists
} from '@/api/exam/tenant_exam_activation_code_batch'
import { useDictData } from '@/hooks/useDictOptions'
import { usePaging } from '@/hooks/usePaging'

// 添加 visible 响应式变量
const visible = ref(false)
// 存储批次数据
const batchData = ref<any>(null)

// 查询条件
const queryParams = reactive({
    code: '',
    status: '',
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
const { dictData } = useDictData('system_disable')

// 分页相关
const { pager, getLists, resetParams, resetPage } = usePaging({
    fetchFun: apiTenantExamActivationCodeLists,
    params: queryParams
})

// 切换状态
const changeStatus = (id: number, status: number) => {
    apiTenantExamActivationCodeDisableEnable({
        id: id,
        status: status
    }).then((res) => {
        if (res.code === 1) {
            ElMessage.success('操作成功')
            getLists()
        }
    })
}

// 获取状态文本
const getStatusText = (row: any) => {
    if (row.activation_time) {
        return '已使用'
    }
    return row.status === 0 ? '未使用' : '已禁用'
}

// 获取状态标签类型
const getStatusTagType = (row: any): 'success' | 'info' | 'warning' | 'danger' => {
    if (row.activation_time) {
        return 'success'
    }
    return row.status === 0 ? 'info' : 'danger'
}

// 添加 open 方法
const open = (data: any) => {
    batchData.value = data
    visible.value = true
    // 根据批次数据加载激活码列表
    // 给 queryParams 添加 batch_id 属性
    ;(queryParams as any).batch_id = data.id
    resetPage()
}

// 复制激活码到剪贴板
const copyCode = (code: string) => {
    navigator.clipboard
        .writeText(code)
        .then(() => {
            ElMessage.success('复制成功')
        })
        .catch((err) => {
            console.error('复制失败:', err)
            ElMessage.error('复制失败，请手动复制')
        })
}

// 导出为 txt 文件
const exportTxt = async () => {
    try {
        // 显示加载状态
        const loadingInstance = ElMessage({
            message: '正在导出激活码...',
            type: 'info',
            duration: 0
        } as any)

        // 获取激活码数据
        const response = await apiTenantExamActivationCodeLists({
            ...queryParams,
            page_no: 1,
            page_size: 9999
        })

        // 关闭加载状态
        setTimeout(() => {
            if (loadingInstance && typeof loadingInstance.close === 'function') {
                loadingInstance.close()
            }
        }, 300)

        // 检查响应
        if (response && response.lists) {
            const activationCodes = response.lists

            // 提取激活码，每行一个
            const txtContent = activationCodes.map((code: any) => code.code).join('\n')

            // 创建 Blob 对象
            const blob = new Blob([txtContent], { type: 'text/plain;charset=utf-8' })

            // 创建下载链接
            const link = document.createElement('a')
            link.href = URL.createObjectURL(blob)
            link.download = `激活码列表_${new Date().getTime()}.txt`

            // 触发下载
            document.body.appendChild(link)
            link.click()
            document.body.removeChild(link)

            // 释放 URL 对象
            URL.revokeObjectURL(link.href)

            ElMessage.success(`导出成功，共导出 ${activationCodes.length} 个激活码`)
        } else {
            // 处理错误或系统限制提示
            if (response && response.msg && response.msg.includes('已超出系统限制数量')) {
                ElMessage.error('数据量过大，请添加筛选条件后再导出')
            } else {
                ElMessage.error('导出失败，请稍后重试')
            }
        }
    } catch (error) {
        console.error('导出激活码失败:', error)
        ElMessage.error('导出失败，请稍后重试')
    }
}

// 暴露 open 方法
defineExpose({
    open
})
</script>

<style scoped>
.text-gray-400 {
    color: var(--el-text-color-placeholder);
}
</style>
