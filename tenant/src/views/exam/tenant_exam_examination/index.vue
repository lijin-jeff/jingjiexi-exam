<template>
    <div>
        <el-card class="!border-none mb-4" shadow="never">
            <el-form class="mb-[-16px]" :model="queryParams" inline>
                <el-form-item label="考试名称" prop="title">
                    <el-input
                        style="width: 150px"
                        v-model="queryParams.title"
                        clearable
                        placeholder="请输入考试名称"
                    />
                </el-form-item>
                <el-form-item label="考试时间">
                    <daterange-picker
                        v-model:startTime="queryParams.start_time"
                        v-model:endTime="queryParams.end_time"
                    />
                </el-form-item>
                <el-form-item label="考试权限" prop="privilege">
                    <el-select
                        style="width: 100px"
                        v-model="queryParams.privilege"
                        clearable
                        placeholder="请选择考试权限"
                    >
                        <el-option label="全部" value=""></el-option>
                        <el-option
                            v-for="(item, index) in dictData.exam_privilege"
                            :key="index"
                            :label="item.name"
                            :value="item.value"
                        />
                    </el-select>
                </el-form-item>
                <el-form-item label="所属分类" prop="library_category_uid">
                    <el-cascader
                        v-model="queryParams.library_category_uid"
                        :options="categoryList"
                        :props="cascaderProps"
                        placeholder="请选择题库分类"
                        clearable
                        filterable
                        style="width: 100%"
                        @change="handleChangeLibraryCategory"
                    />
                </el-form-item>
                <el-form-item label="所属题库" prop="library_uid">
                    <el-select
                        v-model="queryParams.library_uid"
                        placeholder="请先选择题库分类"
                        clearable
                        filterable
                        style="width: 100%"
                        :disabled="!currentCategoryUid"
                        @change="handleChangeLibrary"
                    >
                        <el-option
                            v-for="item in filteredLibraryList"
                            :key="item.uid"
                            :label="item.title"
                            :value="item.uid"
                        />
                    </el-select>
                </el-form-item>
                <el-form-item label="是否显示" prop="is_show">
                    <el-select
                        style="width: 100px"
                        v-model="queryParams.is_show"
                        clearable
                        placeholder="请选择是否显示"
                    >
                        <el-option label="全部" value=""></el-option>
                        <el-option label="显示" value="1"></el-option>
                        <el-option label="不显示" value="0"></el-option>
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
                v-perms="['exam.tenant_exam_examination/add']"
                type="primary"
                @click="handleAdd"
            >
                <template #icon>
                    <icon name="el-icon-Plus" />
                </template>
                新增
            </el-button>
            <el-button
                v-perms="['exam.tenant_exam_examination/delete']"
                :disabled="!selectData.length"
                @click="handleDelete(selectData)"
            >
                删除
            </el-button>
            <div class="mt-4">
                <el-table
                    :data="pager.lists"
                    @selection-change="handleSelectionChange"
                    stripe
                    border
                >
                    <el-table-column type="selection" width="55" align="center" />
                    <el-table-column
                        label="考试名称"
                        prop="title"
                        min-width="150"
                        show-overflow-tooltip
                    />
                    <el-table-column label="考试封面" prop="image" width="100" align="center">
                        <template #default="{ row }">
                            <el-image
                                v-if="row.image"
                                style="width: 50px; height: 50px"
                                :src="row.image"
                                :preview-src-list="[row.image]"
                                preview-teleported
                                fit="cover"
                            />
                            <span v-else class="text-gray-400">无图片</span>
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="开始时间"
                        prop="start_time"
                        min-width="160"
                        show-overflow-tooltip
                    />
                    <el-table-column
                        label="结束时间"
                        prop="end_time"
                        min-width="160"
                        show-overflow-tooltip
                    />
                    <el-table-column label="考试权限" prop="privilege" width="100" align="center">
                        <template #default="{ row }">
                            <el-tag :type="row.privilege == 1 ? 'success' : 'warning'" size="small">
                                <dict-value
                                    :options="dictData.exam_privilege"
                                    :value="row.privilege"
                                />
                            </el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="答题类型"
                        prop="exam_submit_type"
                        width="100"
                        align="center"
                    >
                        <template #default="{ row }">
                            <dict-value
                                :options="dictData.exam_submit_type"
                                :value="row.exam_submit_type"
                            />
                        </template>
                    </el-table-column>
                    <el-table-column label="登录方式" prop="login_style" width="100" align="center">
                        <template #default="{ row }">
                            <dict-value
                                :options="dictData.exam_login_style"
                                :value="row.login_style"
                            />
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="绑定试卷"
                        prop="paper.title"
                        min-width="120"
                        show-overflow-tooltip
                    >
                        <template #default="{ row }">
                            <el-tag v-if="row.paper?.title" type="primary" size="small">
                                {{ row.paper.title }}
                            </el-tag>
                            <span v-else class="text-gray-400">未绑定</span>
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="创建时间"
                        prop="create_time"
                        min-width="160"
                        show-overflow-tooltip
                    />
                    <el-table-column label="操作" width="200" fixed="right" align="center">
                        <template #default="{ row }">
                            <el-button
                                v-perms="['exam.tenant_exam_examination/edit']"
                                type="primary"
                                link
                                size="small"
                                @click="handleEdit(row)"
                            >
                                <el-icon class="mr-1"><Edit /></el-icon>
                                编辑
                            </el-button>
                            <el-button
                                v-perms="['exam.tenant_exam_examination/edit']"
                                type="success"
                                link
                                size="small"
                                @click="paperSelect(row)"
                            >
                                <el-icon class="mr-1"><Document /></el-icon>
                                试卷
                            </el-button>
                            <el-button
                                v-perms="['exam.tenant_exam_examination/delete']"
                                type="danger"
                                link
                                size="small"
                                @click="handleDelete(row.id)"
                            >
                                <el-icon class="mr-1"><Delete /></el-icon>
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
        <!--      试卷开始-->
        <el-dialog title="试卷选择" v-model="dialogLibraryVisible" width="80%">
            <div class="mt-4">
                <el-card class="!border-none" shadow="never" :body-style="{ padding: 10 + 'px' }">
                    <el-form class="mb-[-16px]" :model="paperQueryParams" inline>
                        <el-form-item label="试卷名称" prop="title">
                            <el-input
                                class="w-[280px]"
                                v-model="paperQueryParams.title"
                                clearable
                                placeholder="请输入试卷名称"
                            />
                        </el-form-item>
                        <el-form-item label="启用状态" prop="is_show">
                            <el-select
                                class="w-[280px]"
                                style="width: 100px"
                                v-model="paperQueryParams.is_show"
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
                                v-model="paperQueryParams.is_rand"
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
                            <el-button type="primary" @click="paperListFun.resetPage"
                                >查询</el-button
                            >
                            <el-button @click="paperListFun.resetParams">重置</el-button>
                            <el-button type="warning" @click="paperConfirm">确认</el-button>
                        </el-form-item>
                    </el-form>
                </el-card>
                <el-card class="!border-none" v-loading="paperListFun.pager.loading" shadow="never">
                    <div class="mt-4">
                        <!--            <div class="mb-4">当前试卷：{{ selectPaperInfo.value.paper !== undefined ? selectPaperInfo.value.paper.title : '' }}</div>-->
                        <el-table
                            :data="paperListFun.pager.lists"
                            @selection-change="handlePaperSelectionChange"
                        >
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
                                    <dict-value
                                        :options="dictData.paper_rand"
                                        :value="row.is_rand"
                                    />
                                </template>
                            </el-table-column>
                            <el-table-column
                                label="试题总数"
                                prop="option_count"
                                show-overflow-tooltip
                            />
                            <el-table-column
                                label="试题总分"
                                prop="option_score"
                                show-overflow-tooltip
                            />
                            <el-table-column label="启用状态" prop="is_show">
                                <template #default="{ row }">
                                    <dict-value
                                        :options="dictData.show_status"
                                        :value="row.is_show"
                                    />
                                </template>
                            </el-table-column>
                            <el-table-column label="显示权重" prop="sort" show-overflow-tooltip />
                            <el-table-column
                                label="创建时间"
                                prop="create_time"
                                show-overflow-tooltip
                            />
                            <el-table-column
                                label="更新时间"
                                prop="update_time"
                                show-overflow-tooltip
                            />
                        </el-table>
                    </div>
                    <div class="flex mt-4 justify-end">
                        <pagination v-model="paperListFun.pager" @change="paperListFun.getLists" />
                    </div>
                </el-card>
            </div>
        </el-dialog>
        <!--      试卷结束-->
    </div>
</template>

<script lang="ts" setup name="tenantExamExaminationLists">
import { Delete, Document, Edit } from '@element-plus/icons-vue'

import { apiExamCategoryTree } from '@/api/exam/exam_category'
import {
    apiTenantExamExaminationDelete,
    apiTenantExamExaminationLists,
    apiTenantExamExaminationSavePaper
} from '@/api/exam/tenant_exam_examination'
import { apiTenantExamLibraryLists } from '@/api/exam/tenant_exam_library'
import { apiTenantExamPaperLists } from '@/api/exam/tenant_exam_paper'
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
    library_category_uid: '',
    library_uid: '',
    is_show: '',
    admin_id: '',
    tenant_id: '',
    start_time: '',
    end_time: '',
    privilege: '',
    exam_time: '',
    exam_submit_type: '',
    login_style: ''
})

/**
 * 试卷处理逻辑开始
 */
const dialogLibraryVisible = ref(false)
const paperSelectData = ref<any[]>([])
const paperQueryParams = reactive({
    title: '',
    is_show: '',
    is_rand: ''
})
const examination_id = ref(0)
const selectPaperInfo = ref({})

const paperListFun = usePaging({
    fetchFun: apiTenantExamPaperLists,
    params: paperQueryParams
})

const paperSelect = async (data: any) => {
    await paperListFun.getLists()
    examination_id.value = data.id
    dialogLibraryVisible.value = true
    selectPaperInfo.value = data
}

const handlePaperSelectionChange = (val: any[]) => {
    paperSelectData.value = val.map(({ id }) => id)
    if (paperSelectData.value.length > 1) {
        feedback.msgError('只能选择一个试卷')
        paperSelectData.value = []
    }
}

const paperConfirm = () => {
    if (paperSelectData.value.length > 1) {
        feedback.msgError('只能选择一个试卷')
        return
    }
    apiTenantExamExaminationSavePaper({
        id: examination_id.value,
        paper_id: paperSelectData.value[0]
    }).then(() => {
        feedback.msgSuccess('绑定成功')
        dialogLibraryVisible.value = false
    })
}
/**
 * 试卷处理结束
 */

// 选中数据
const selectData = ref<any[]>([])

// 表格选择后回调事件
const handleSelectionChange = (val: any[]) => {
    selectData.value = val.map(({ id }) => id)
}

// 获取字典数据
const { dictData } = useDictData(
    'show_status,exam_privilege,exam_login_style,exam_submit_type,paper_rand'
)

// 分页相关
const { pager, getLists, resetParams, resetPage } = usePaging({
    fetchFun: apiTenantExamExaminationLists,
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
    await apiTenantExamExaminationDelete({ id })
    getLists()
}

getLists()

const currentCategoryUid = ref('')

const handleChangeLibraryCategory = (value: any) => {
    console.log('选择题库分类:', value)
    currentCategoryUid.value = value || ''
    queryParams.library_uid = ''
    // 清空题库列表
    allLibraryList.length = 0
    if (value) {
        // 获取该分类下的题库列表
        fetchExamlibraryList(value)
    }
}

interface CategoryItem {
    value: string
    label: string
    children?: CategoryItem[]
}

interface LibraryItem {
    id: number
    uid: string
    title: string
    category_uid: string
    exam_count: number
    is_show: number
}

// Cascader 配置
const cascaderProps = {
    value: 'uid',
    label: 'title',
    children: 'children',
    checkStrictly: true, // 允许选择任意一级
    emitPath: false // 只返回选中节点的值，不返回路径数组
}

const categoryList = reactive<CategoryItem[]>([])
const fetchExamCategoryList = async () => {
    const res = await apiExamCategoryTree()
    // API返回格式：直接是数组，包含 uid、title、children 字段
    // Cascader需要的格式与API返回格式一致，直接使用即可
    Object.assign(categoryList, res || [])
}

const allLibraryList = reactive<LibraryItem[]>([])
const fetchExamlibraryList = async (categoryUid?: string) => {
    try {
        const params = categoryUid ? { category_uid: categoryUid, page_type: 0 } : { page_type: 0 }
        console.log('请求题库列表参数:', params)
        const res = await apiTenantExamLibraryLists(params)
        console.log('题库列表响应:', res)

        // 兼容两种数据结构：{ lists: [...] } 或 { data: { lists: [...] } }
        const lists = res?.lists || res?.data?.lists || []

        if (lists && lists.length >= 0) {
            // 清空旧数据
            allLibraryList.length = 0
            // 添加新数据
            allLibraryList.push(...lists)
            console.log('题库列表数据:', allLibraryList)
        }
    } catch (error) {
        console.error('获取题库列表失败:', error)
    }
}

const filteredLibraryList = computed(() => {
    if (!currentCategoryUid.value) {
        return allLibraryList
    }
    return allLibraryList.filter((item) => item.category_uid === currentCategoryUid.value)
})

const handleChangeLibrary = (value: any) => {
    console.log('选择题库:', value)
}

fetchExamCategoryList()
fetchExamlibraryList()
</script>
