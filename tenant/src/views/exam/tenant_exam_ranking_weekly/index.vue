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
                <el-form-item label="周数" prop="week_number">
                    <el-input
                        class="w-[180px]"
                        v-model="queryParams.week_number"
                        clearable
                        placeholder="请输入周数（1-53）"
                    />
                </el-form-item>
                <el-form-item label="年份" prop="year">
                    <el-input
                        class="w-[180px]"
                        v-model="queryParams.year"
                        clearable
                        placeholder="请输入年份"
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
                v-perms="['exam.tenant_exam_ranking_weekly/delete']"
                :disabled="!selectData.length"
                @click="handleDelete(selectData)"
            >
                删除
            </el-button>
            <div class="mt-4">
                <el-table :data="pager.lists" @selection-change="handleSelectionChange">
                    <el-table-column type="selection" width="55" />
                    <el-table-column label="ID" prop="id" show-overflow-tooltip />
                    <el-table-column label="用户ID" prop="user_id" show-overflow-tooltip />
                    <el-table-column label="答对题数" prop="correct_count" show-overflow-tooltip />
                    <el-table-column label="排名" prop="ranking" show-overflow-tooltip />
                    <el-table-column
                        label="开始日期"
                        prop="week_start_date"
                        show-overflow-tooltip
                    />
                    <el-table-column label="结束日期" prop="week_end_date" show-overflow-tooltip />
                    <el-table-column label="周数" prop="week_number" show-overflow-tooltip />
                    <el-table-column label="年份" prop="year" show-overflow-tooltip />
                    <el-table-column label="操作" width="120" fixed="right">
                        <template #default="{ row }">
                            <el-button
                                v-perms="['exam.tenant_exam_ranking_weekly/delete']"
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

<script lang="ts" setup name="tenantExamRankingWeeklyLists">
import {
    apiTenantExamRankingWeeklyDelete,
    apiTenantExamRankingWeeklyLists
} from '@/api/exam/rank/tenant_exam_ranking'
import { useDictData } from '@/hooks/useDictOptions'
import { usePaging } from '@/hooks/usePaging'
import feedback from '@/utils/feedback'

import EditPopup from './edit.vue'

const editRef = shallowRef<InstanceType<typeof EditPopup>>()
// 是否显示编辑框
const showEdit = ref(false)

// 查询条件
const queryParams = reactive({
    user_id: '',
    week_number: '',
    year: ''
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
    fetchFun: apiTenantExamRankingWeeklyLists,
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
    await apiTenantExamRankingWeeklyDelete({ id })
    getLists()
}

getLists()
</script>
