<template>
    <div>
        <el-card class="!border-none mb-4" shadow="never" :body-style="{ padding: 10 + 'px' }">
            <el-form class="mb-[-16px]" :model="queryParams" inline>
                <el-form-item label="分类名称" prop="title">
                    <el-input
                        class="w-[120px]"
                        v-model="queryParams.title"
                        clearable
                        placeholder="请输入分类名称"
                    />
                </el-form-item>
                <!--                <el-form-item label="启用状态" prop="is_show">-->
                <!--                    <el-select-->
                <!--                        class="w-[280px]"-->
                <!--                        v-model="queryParams.is_show"-->
                <!--                        clearable-->
                <!--                        style="width: 100px;"-->
                <!--                        placeholder="请选择启用状态"-->
                <!--                    >-->
                <!--                        <el-option label="全部" value=""></el-option>-->
                <!--                        <el-option-->
                <!--                            v-for="(item, index) in dictData.show_status"-->
                <!--                            :key="index"-->
                <!--                            :label="item.name"-->
                <!--                            :value="item.value"-->
                <!--                        />-->
                <!--                    </el-select>-->
                <!--                </el-form-item>-->
                <!--                <el-form-item label="是否推荐" prop="is_recommend">-->
                <!--                    <el-select-->
                <!--                        class="w-[280px]"-->
                <!--                        v-model="queryParams.is_recommend"-->
                <!--                        clearable-->
                <!--                        style="width: 100px;"-->
                <!--                        placeholder="请选择是否推荐"-->
                <!--                    >-->
                <!--                        <el-option label="全部" value=""></el-option>-->
                <!--                        <el-option-->
                <!--                            v-for="(item, index) in dictData.is_recommend"-->
                <!--                            :key="index"-->
                <!--                            :label="item.name"-->
                <!--                            :value="item.value"-->
                <!--                        />-->
                <!--                    </el-select>-->
                <!--                </el-form-item>-->
                <el-form-item>
                    <el-button type="primary" @click="resetPage">查询</el-button>
                    <el-button @click="resetParams">重置</el-button>
                </el-form-item>
            </el-form>
        </el-card>
        <el-card class="!border-none" v-loading="pager.loading" shadow="never">
            <el-button v-perms="['exam.exam_category/add']" type="primary" @click="handleAdd">
                <template #icon>
                    <icon name="el-icon-Plus" />
                </template>
                新增
            </el-button>
            <el-button
                v-perms="['exam.exam_category/delete']"
                :disabled="!selectData.length"
                @click="handleDelete(selectData)"
            >
                删除
            </el-button>
            <div class="mt-4">
                <!--                <el-table :data="pager.lists" @selection-change="handleSelectionChange">-->
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
                    <el-table-column prop="title" label="分类名称" />
                    <!--                    <el-table-column prop="icon" label="分类图标" />-->
                    <!-- <el-table-column label="分类图片" prop="cover">
                        <template #default="{ row }">
                            <el-image
                                v-if="row.cover !== ''"
                                style="width: 50px; height: 50px"
                                :src="row.cover"
                                :preview-src-list="[row.cover]"
                                preview-teleported
                            />
                        </template>
                    </el-table-column> -->
                    <!-- 考试时间 -->
                    <el-table-column prop="exam_time" label="考试时间" />
                    <el-table-column prop="category_count" label="题库数量" />
                    <el-table-column label="启用状态" prop="is_show">
                        <template #default="{ row }">
                            <el-switch
                                v-perms="['exam.exam_category/updateStatus']"
                                v-model="row.is_show"
                                :active-value="1"
                                :inactive-value="0"
                                @change="changeStatus($event, row.id, row.uid)"
                            />
                        </template>
                    </el-table-column>
                    <!--                    <el-table-column label="推荐状态" prop="is_recommend">-->
                    <!--                        <template #default="{ row }">-->
                    <!--                            <dict-value :options="dictData.is_recommend" :value="row.is_recommend" />-->
                    <!--                        </template>-->
                    <!--                    </el-table-column>-->
                    <!--                    <el-table-column label="显示权重" prop="sort" show-overflow-tooltip />-->
                    <el-table-column label="操作" width="180" fixed="right">
                        <template #default="{ row }">
                            <el-button
                                v-perms="['exam.exam_category/edit']"
                                type="primary"
                                link
                                @click="handleEdit(row)"
                            >
                                编辑
                            </el-button>
                            <el-button
                                v-perms="['exam.exam_category/delete']"
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

<script lang="ts" setup name="examCategoryLists">
import {
    apiExamCategoryDelete,
    apiExamCategoryLists,
    apiExamCategoryStatus
} from '@/api/exam/exam_category'
import { useDictData } from '@/hooks/useDictOptions'
import { usePaging } from '@/hooks/usePaging'
import feedback from '@/utils/feedback'

import EditPopup from './edit.vue'

const editRef = shallowRef<InstanceType<typeof EditPopup>>()
// 是否显示编辑框
const showEdit = ref(false)

// 查询条件
const queryParams = reactive({
    title: '',
    is_show: '',
    is_recommend: ''
})

// 选中数据
const selectData = ref<any[]>([])

// 表格选择后回调事件
const handleSelectionChange = (val: any[]) => {
    selectData.value = val.map(({ id }) => id)
}

// 获取字典数据
const { dictData } = useDictData('show_status,is_recommend')

// 分页相关
const { pager, getLists } = usePaging({
    fetchFun: apiExamCategoryLists,
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
    // 等待open函数完成，确保parentList加载完成后再获取详情数据
    await editRef.value?.open('edit')
    // 调用后端详情接口获取完整数据，而不是直接使用列表数据
    await editRef.value?.getDetail(data)
}

// 删除
const handleDelete = async (id: number | any[]) => {
    await feedback.confirm('确定要删除？')
    await apiExamCategoryDelete({ id })
    getLists()
}

const changeStatus = async (is_show: any, id: number, uid: number) => {
    try {
        await apiExamCategoryStatus({ id, uid, is_show })
        getLists()
    } catch (error) {
        getLists()
    }
}

getLists()
</script>
