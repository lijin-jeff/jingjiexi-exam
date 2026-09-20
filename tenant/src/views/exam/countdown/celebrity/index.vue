<template>
    <div>
        <!-- 页面头部 -->
        <el-card class="!border-none" shadow="never">
            <el-page-header content="名人名言管理" @back="$router.back()" />
        </el-card>

        <!-- 查询卡片 -->
        <el-card class="!border-none mb-4" shadow="never" :body-style="{ padding: 10 + 'px' }">
            <el-form class="mb-[-16px] margin-bottom20" :model="queryParams" inline>
                <el-form-item label="内容或作者" prop="keyword">
                    <el-input
                        class="w-[280px]"
                        v-model="queryParams.keyword"
                        clearable
                        placeholder="请输入内容或作者搜索"
                    />
                </el-form-item>
                <el-form-item label="分类" prop="category">
                    <el-input
                        class="w-[280px]"
                        v-model="queryParams.category"
                        clearable
                        placeholder="请输入分类搜索"
                    />
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="resetPage">查询</el-button>
                    <el-button @click="resetParams">重置</el-button>
                </el-form-item>
            </el-form>
        </el-card>

        <!-- 数据卡片 -->
        <el-card class="!border-none" v-loading="pager.loading" shadow="never">
            <div>
                <el-button type="primary" @click="handleAdd">
                    <template #icon>
                        <el-icon><Plus /></el-icon>
                    </template>
                    新增名人名言
                </el-button>
            </div>

            <!-- 数据表格 -->
            <el-table
                :data="pager.lists"
                style="width: 100%; margin-bottom: 20px"
                row-key="id"
                size="large"
            >
                <el-table-column prop="id" label="ID" width="80" align="center" />
                <el-table-column
                    prop="content"
                    label="内容"
                    min-width="300"
                    show-overflow-tooltip
                    align="left"
                />
                <el-table-column prop="category" label="分类" width="120" align="center" />
                <el-table-column prop="create_time" label="创建时间" width="180" align="center" />
                <el-table-column prop="update_time" label="更新时间" width="180" align="center" />
                <el-table-column label="操作" width="180" fixed="right" align="center">
                    <template #default="{ row }">
                        <div class="flex items-center justify-center gap-2">
                            <el-button
                                type="primary"
                                size="small"
                                @click="handleEdit(row)"
                                class="flex items-center gap-1"
                            >
                                <el-icon><Edit /></el-icon>编辑
                            </el-button>
                            <el-button
                                type="danger"
                                size="small"
                                @click="handleDelete(row.id)"
                                class="flex items-center gap-1"
                            >
                                <el-icon><Delete /></el-icon>删除
                            </el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>

            <!-- 分页区域 -->
            <div class="flex mt-4 justify-end">
                <pagination v-model="pager" @change="getLists" />
            </div>
        </el-card>

        <!-- 编辑弹窗 -->
        <edit-popup v-if="showEdit" ref="editRef" @success="getLists" @close="showEdit = false" />
    </div>
</template>

<script setup lang="ts">
import { Delete, Edit, Plus } from '@element-plus/icons-vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { nextTick, reactive, ref, shallowRef } from 'vue'

import { celebrityQuoteDelete, celebrityQuoteLists } from '@/api/exam/countdown'
import Pagination from '@/components/pagination/index.vue'
import { usePaging } from '@/hooks/usePaging'

import EditPopup from './edit.vue'

const editRef = shallowRef<InstanceType<typeof EditPopup>>()
// 是否显示编辑框
const showEdit = ref(false)

// 查询条件
const queryParams = reactive({
    keyword: '',
    category: ''
})

// 表格数据
const { pager, getLists, resetPage, resetParams } = usePaging({
    fetchFun: celebrityQuoteLists,
    params: queryParams
})

// 添加
const handleAdd = async () => {
    showEdit.value = true
    await nextTick()
    editRef.value?.open('add')
}

// 编辑
const handleEdit = async (row: any) => {
    showEdit.value = true
    await nextTick()
    editRef.value?.open('edit')
    editRef.value?.setFormData(row)
}

// 删除
const handleDelete = async (id: number) => {
    await ElMessageBox.confirm('确定要删除该名人名言吗？', '提示', {
        type: 'warning'
    })
    try {
        const res = await celebrityQuoteDelete({ id })
        if (res.code === 1) {
            ElMessage.success('删除成功')
            getLists()
        }
    } catch (error) {
        // 取消删除操作不做处理
    }
}

// 初始化
getLists()
</script>

<style scoped>
.celebrity-quote-page {
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
    .celebrity-quote-page {
        padding: 1rem;
    }
}
</style>
