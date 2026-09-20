<template>
    <div>
        <el-card class="!border-none mb-4" shadow="never">
            <el-form class="mb-[-16px]" :model="queryParams" inline>
                <el-form-item label="试卷名称" prop="title">
                    <el-input
                        class="w-[280px]"
                        v-model="queryParams.title"
                        clearable
                        placeholder="请输入试卷名称"
                    />
                </el-form-item>
                <el-form-item label="启用状态" prop="is_show">
                    <el-select
                        class="w-[280px]"
                        style="width: 100px"
                        v-model="queryParams.is_show"
                        clearable
                        placeholder="请选择启用状态"
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
                <el-form-item label="随机状态" prop="is_rand">
                    <el-select
                        class="w-[280px]"
                        style="width: 100px"
                        v-model="queryParams.is_rand"
                        clearable
                        placeholder="请选择随机状态"
                    >
                        <el-option label="全部" value=""></el-option>
                        <el-option
                            v-for="(item, index) in dictData.paper_rand"
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
            <el-button v-perms="['exam.tenant_exam_paper/add']" type="primary" @click="handleAdd">
                <template #icon>
                    <icon name="el-icon-Plus" />
                </template>
                新增
            </el-button>
            <el-button
                v-perms="['exam.tenant_exam_paper/delete']"
                :disabled="!selectData.length"
                @click="handleDelete(selectData)"
            >
                删除
            </el-button>
            <div class="mt-4">
                <el-table :data="pager.lists" @selection-change="handleSelectionChange">
                    <el-table-column type="selection" width="55" />
                    <el-table-column label="试卷ID" prop="id" show-overflow-tooltip />
                    <el-table-column label="试卷编号" prop="uid" show-overflow-tooltip />
                    <el-table-column label="试卷封面" prop="image">
                        <template #default="{ row }">
                            <el-image style="width: 50px; height: 50px" :src="row.image" />
                        </template>
                    </el-table-column>
                    <el-table-column label="试卷名称" prop="title" show-overflow-tooltip />
                    <el-table-column label="随机状态" prop="is_rand">
                        <template #default="{ row }">
                            <dict-value :options="dictData.paper_rand" :value="row.is_rand" />
                        </template>
                    </el-table-column>
                    <el-table-column label="试题总数" prop="option_count" show-overflow-tooltip />
                    <el-table-column label="试题总分" prop="option_score" show-overflow-tooltip />
                    <el-table-column label="启用状态" prop="is_show">
                        <template #default="{ row }">
                            <dict-value :options="dictData.show_status" :value="row.is_show" />
                        </template>
                    </el-table-column>
                    <el-table-column label="显示权重" prop="sort" show-overflow-tooltip />
                    <el-table-column label="创建时间" prop="create_time" show-overflow-tooltip />
                    <el-table-column label="更新时间" prop="update_time" show-overflow-tooltip />
                    <el-table-column label="操作" width="200" fixed="right">
                        <template #default="{ row }">
                            <el-button
                                v-perms="['exam.tenant_exam_paper/edit']"
                                type="primary"
                                link
                                @click="handleEdit(row)"
                            >
                                编辑
                            </el-button>
                            <el-button
                                v-perms="['exam.tenant_exam_paper/paper']"
                                type="primary"
                                link
                                @click="handlePaper(row)"
                            >
                                组卷
                            </el-button>
                            <el-button
                                v-perms="['exam.tenant_exam_paper/delete']"
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

<script lang="ts" setup name="tenantExamPaperLists">
import { useRouter } from 'vue-router'

import { apiTenantExamPaperDelete, apiTenantExamPaperLists } from '@/api/exam/tenant_exam_paper'
import { useDictData } from '@/hooks/useDictOptions'
import { usePaging } from '@/hooks/usePaging'
import feedback from '@/utils/feedback'

import EditPopup from './edit.vue'

const editRef = shallowRef<InstanceType<typeof EditPopup>>()
// 是否显示编辑框
const showEdit = ref(false)
const router = useRouter()

// 查询条件
const queryParams = reactive({
    title: '',
    is_show: '',
    is_rand: '',
    image: ''
})

// 选中数据
const selectData = ref<any[]>([])

// 表格选择后回调事件
const handleSelectionChange = (val: any[]) => {
    selectData.value = val.map(({ id }) => id)
}

// 获取字典数据
const { dictData } = useDictData('show_status,paper_rand')

// 分页相关
const { pager, getLists, resetParams, resetPage } = usePaging({
    fetchFun: apiTenantExamPaperLists,
    params: queryParams
})

// 组卷跳转
const handlePaper = (row: any) => {
    router.push({
        path: 'tenant_exam_paper/paper',
        query: {
            id: row.id
        }
    })
}

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
    await apiTenantExamPaperDelete({ id })
    getLists()
}

getLists()
</script>
