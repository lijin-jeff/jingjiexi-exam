<template>
    <div>
        <el-card class="!border-none mb-4" shadow="never">
            <el-form class="mb-[-16px]" :model="queryParams" inline>
                <el-form-item label="做题类型" prop="questions_type">
                    <el-select
                        style="width: 100px"
                        v-model="queryParams.questions_type"
                        clearable
                        placeholder="请选择"
                    >
                        <el-option label="全部" value="0"></el-option>
                        <el-option
                            v-for="(item, index) in dictData.questions_type"
                            :key="index"
                            :label="item.name"
                            :value="item.value"
                        />
                    </el-select>
                </el-form-item>
                <el-form-item label="题库" prop="library_uid">
                    <el-select
                        style="width: 100px"
                        v-model="queryParams.library_uid"
                        clearable
                        placeholder="请选择"
                    >
                        <el-option label="全部" value="0"></el-option>
                        <el-option
                            v-for="(item, index) in libraryList"
                            :key="index"
                            :label="item.title"
                            :value="item.uid"
                        />
                    </el-select>
                </el-form-item>
                <el-form-item label="考试时间" prop="exam_time">
                    <daterange-picker
                        v-model:startTime="queryParams.exam_start_time"
                        v-model:endTime="queryParams.exam_end_time"
                    />
                </el-form-item>
                <el-form-item label="答题时间" prop="exam_submit_time">
                    <daterange-picker
                        v-model:startTime="queryParams.submit_start_time"
                        v-model:endTime="queryParams.submit_end_time"
                    />
                </el-form-item>
                <el-form-item label="试题 uid" prop="question_uid">
                    <el-input
                        class="w-[280px]"
                        v-model="queryParams.question_uid"
                        clearable
                        placeholder="请输入题库试题 uid"
                    />
                </el-form-item>
                <el-form-item label="用户 uid" prop="user_uid">
                    <el-input
                        class="w-[280px]"
                        v-model="queryParams.user_uid"
                        clearable
                        placeholder="请输入用户 uid"
                    />
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="resetPage">查询</el-button>
                    <el-button @click="resetParams">重置</el-button>
                </el-form-item>
            </el-form>
        </el-card>

        <el-card class="!border-none" v-loading="pager.loading" shadow="never">
            <el-tabs v-model="tabsActive">
                <el-tab-pane label="全部" value="0"></el-tab-pane>
                <el-tab-pane
                    v-for="(item, index) in dictData.questions_type"
                    :key="index"
                    :label="item.name"
                    :name="item.value"
                    lazy
                ></el-tab-pane>
            </el-tabs>
            <el-button
                v-perms="['exam.tenant_exam_record/delete']"
                :disabled="!selectData.length"
                @click="handleDelete(selectData)"
            >
                删除
            </el-button>
            <div class="mt-4">
                <el-table :data="pager.lists" @selection-change="handleSelectionChange">
                    <el-table-column type="selection" width="55" />
                    <el-table-column label="编号" prop="uid" show-overflow-tooltip width="120" />
                    <el-table-column
                        label="做题类型"
                        prop="questions_type"
                        show-overflow-tooltip
                        width="100"
                    >
                        <template #default="{ row }">
                            {{
                                (dictData.questions_type as any[]).find(
                                    (item) => item.value == row.questions_type
                                )?.name || '-'
                            }}
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="标题"
                        prop="title"
                        show-overflow-tooltip
                        min-width="150"
                    />
                    <el-table-column
                        label="用户"
                        prop="user_nickname"
                        show-overflow-tooltip
                        width="120"
                    >
                        <template #default="{ row }">
                            <div class="flex items-center">
                                <el-avatar :size="24" :src="row.user_avatar" class="mr-2" />
                                {{ row.user_nickname || '-' }}
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="题库/试卷"
                        prop="library_title"
                        show-overflow-tooltip
                        width="150"
                    />
                    <el-table-column label="得分/总分" show-overflow-tooltip width="120">
                        <template #default="{ row }">
                            <span
                                :class="
                                    row.user_score >= row.paper_score * 0.6
                                        ? 'text-green-600'
                                        : 'text-red-600'
                                "
                            >
                                {{ row.user_score }} / {{ row.paper_score }}
                            </span>
                        </template>
                    </el-table-column>
                    <el-table-column label="正确/错误" show-overflow-tooltip width="100">
                        <template #default="{ row }">
                            <span class="text-green-600">{{ row.correct_count }}</span>
                            /
                            <span class="text-red-600">{{ row.error_count }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="正确率"
                        prop="accuracy_rate"
                        show-overflow-tooltip
                        width="90"
                    >
                        <template #default="{ row }"> {{ row.accuracy_rate }}% </template>
                    </el-table-column>
                    <el-table-column label="做题进度" show-overflow-tooltip width="100">
                        <template #default="{ row }">
                            {{ row.answered_count || 0 }} / {{ row.total_count || 0 }}
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="练习时长"
                        prop="practice_duration"
                        show-overflow-tooltip
                        width="100"
                    >
                        <template #default="{ row }">
                            {{ formatDuration(row.practice_duration) }}
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="考试时间"
                        prop="exam_time"
                        show-overflow-tooltip
                        width="155"
                    />
                    <el-table-column
                        label="提交时间"
                        prop="exam_submit_time"
                        show-overflow-tooltip
                        width="155"
                    />
                    <el-table-column label="操作" width="120" fixed="right">
                        <template #default="{ row }">
                            <el-button
                                v-perms="['exam.tenant_exam_record/edit']"
                                type="primary"
                                link
                                @click="handleEdit(row)"
                            >
                                详情
                            </el-button>
                            <el-button
                                v-perms="['exam.tenant_exam_record/delete']"
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

<script lang="ts" setup name="tenantExamRecordLists">
import { ref } from 'vue'

import { apiTenantExamLibraryLists } from '@/api/exam/tenant_exam_library'
import { apiTenantExamRecordDelete, apiTenantExamRecordLists } from '@/api/exam/tenant_exam_record'
import { useDictData } from '@/hooks/useDictOptions'
import { usePaging } from '@/hooks/usePaging'
import feedback from '@/utils/feedback'

import EditPopup from './edit.vue'

const editRef = shallowRef<InstanceType<typeof EditPopup>>()
// 是否显示编辑框
const showEdit = ref(false)

// 查询条件
const queryParams = reactive({
    uid: '',
    questions_type: '',
    user_score: '',
    paper_score: '',
    exam_time: '',
    exam_start_time: '',
    exam_end_time: '',
    exam_submit_time: '',
    submit_start_time: '',
    submit_end_time: '',
    correct_count: '',
    error_count: '',
    library_uid: '',
    question_uid: '',
    user_uid: ''
})

// 选中数据
const selectData = ref<any[]>([])

// 表格选择后回调事件
const handleSelectionChange = (val: any[]) => {
    selectData.value = val.map(({ id }) => id)
}

const tabsActive = ref('')

// 添加初始化逻辑
onMounted(() => {
    // 显式设置默认选中状态
    tabsActive.value = '0'
})

watch(
    () => tabsActive.value,
    (newValue, oldValue) => {
        console.log('新值:', newValue)
        console.log('旧值:', oldValue)
        if (newValue !== oldValue) {
            queryParams.questions_type = newValue
            getLists()
        }
    }
)

// 获取字典数据
const { dictData } = useDictData('questions_type')

// 分页相关
const { pager, getLists, resetParams, resetPage } = usePaging({
    fetchFun: apiTenantExamRecordLists,
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
    await apiTenantExamRecordDelete({ id })
    getLists()
}

const libraryList = ref<any[]>([])

const getLibraryList = async () => {
    await apiTenantExamLibraryLists({}).then((res: any) => {
        libraryList.value = [...res.lists]
        //console.log(libraryList)
    })
}

// 格式化时长（秒转换为时分秒）
const formatDuration = (seconds: number | null | undefined): string => {
    if (!seconds || seconds <= 0) return '0秒'
    const hours = Math.floor(seconds / 3600)
    const minutes = Math.floor((seconds % 3600) / 60)
    const secs = seconds % 60

    if (hours > 0) {
        return `${hours}小时${minutes}分${secs}秒`
    } else if (minutes > 0) {
        return `${minutes}分${secs}秒`
    } else {
        return `${secs}秒`
    }
}

onMounted(() => {
    getLibraryList()
})

getLists()
</script>

<style scoped lang="scss"></style>
