<template>
    <div>
        <el-card class="!border-none mb-4" shadow="never">
            <el-form class="mb-[-16px]" :model="queryParams" inline>
                <el-form-item label="状态" prop="status">
                    <el-select
                        class="w-[280px]"
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
                <el-form-item>
                    <el-button type="primary" @click="resetPage">查询</el-button>
                    <el-button @click="resetParams">重置</el-button>
                </el-form-item>
            </el-form>
        </el-card>
        <el-card class="!border-none" v-loading="pager.loading" shadow="never">
            <el-button
                v-perms="['exam.tenant_exam_activation_code_batch/add']"
                type="primary"
                @click="handleAdd"
            >
                <template #icon>
                    <icon name="el-icon-Plus" />
                </template>
                新增
            </el-button>
            <el-button
                v-perms="['exam.tenant_exam_activation_code_batch/delete']"
                :disabled="!selectData.length"
                @click="handleDelete(selectData)"
            >
                删除
            </el-button>
            <div class="mt-4">
                <el-table :data="pager.lists" @selection-change="handleSelectionChange">
                    <el-table-column type="selection" width="55" />
                    <el-table-column label="ID" prop="id" width="60" />
                    <el-table-column label="开通时长" prop="duration_days" show-overflow-tooltip />
                    <el-table-column label="总数量" prop="total_count" show-overflow-tooltip />
                    <el-table-column label="已使用数量" prop="used_count" show-overflow-tooltip />
                    <el-table-column label="备注" prop="remark" show-overflow-tooltip />
                    <el-table-column label="状态" width="120" prop="status">
                        <template #default="{ row }">
                            <dict-value :options="dictData.system_disable" :value="row.status" />
                        </template>
                    </el-table-column>
                    <el-table-column label="操作" width="180" fixed="right">
                        <template #default="{ row }">
                            <el-button
                                v-perms="['exam.tenant_exam_activation_code/lists']"
                                :params="{ branch_id: row.id }"
                                type="primary"
                                link
                                @click="handleCodeLists(row)"
                            >
                                查看激活码
                            </el-button>
                            <el-button
                                v-perms="['exam.tenant_exam_activation_code_batch/edit']"
                                type="primary"
                                link
                                @click="handleEdit(row)"
                            >
                                编辑
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
        <!-- 添加 CodePopup 组件 -->
        <code-popup v-if="showCode" ref="codeRef" @close="showCode = false" />
    </div>
</template>

<script lang="ts" setup name="tenantExamActivationCodeBatchLists">
import {
    apiTenantExamActivationCodeBatchDelete,
    apiTenantExamActivationCodeBatchLists
} from '@/api/exam/tenant_exam_activation_code_batch'
import { useDictData } from '@/hooks/useDictOptions'
import { usePaging } from '@/hooks/usePaging'
import feedback from '@/utils/feedback'

import EditPopup from './edit.vue'

const editRef = shallowRef<InstanceType<typeof EditPopup>>()
// 是否显示编辑框
const showEdit = ref(false)

// 查询条件
const queryParams = reactive({
    remark: '',
    status: ''
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
    fetchFun: apiTenantExamActivationCodeBatchLists,
    params: queryParams
})

// 添加
const handleAdd = async () => {
    showEdit.value = true
    await nextTick()
    editRef.value?.open('add')
}

// 打开code.vue页面弹窗，查看激活码列表
import CodePopup from './code.vue'
const codeRef = shallowRef<InstanceType<typeof CodePopup>>()
const showCode = ref(false)
const handleCodeLists = async (data: any) => {
    //console.log(data);
    showCode.value = true
    await nextTick()
    if (codeRef.value) {
        ;(codeRef.value as any).open(data)
    }
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
    await apiTenantExamActivationCodeBatchDelete({ id })
    getLists()
}

getLists()
</script>
