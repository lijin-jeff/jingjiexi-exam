<template>
    <div>
        <el-card class="!border-none mb-4" shadow="never">
            <el-menu
                :default-active="activeIndex"
                class="el-menu-demo"
                mode="horizontal"
                @select="handleSelect"
            >
                <el-menu-item index="1">试题评论</el-menu-item>
                <el-menu-item index="2">文章评论</el-menu-item>
                <el-menu-item index="3">资源评论</el-menu-item>
            </el-menu>
        </el-card>
        <el-card class="!border-none mb-4" shadow="never">
            <el-form ref="formRef" class="mb-[-16px]" :model="queryParams" inline>
                <el-form-item label="用户ID" prop="user_id">
                    <el-input
                        class="w-[180px]"
                        v-model="queryParams.user_id"
                        clearable
                        placeholder="请输入用户ID"
                    />
                </el-form-item>
                <el-form-item v-if="queryParams.type === 1" label="试题ID" prop="qid">
                    <el-input
                        class="w-[180px]"
                        v-model="queryParams.qid"
                        clearable
                        placeholder="请输入试题ID"
                    />
                </el-form-item>
                <el-form-item v-if="queryParams.type === 2" label="文章ID" prop="qid">
                    <el-input
                        class="w-[180px]"
                        v-model="queryParams.qid"
                        clearable
                        placeholder="请输入文章ID"
                    />
                </el-form-item>
                <el-form-item v-if="queryParams.type === 3" label="资源ID" prop="qid">
                    <el-input
                        class="w-[180px]"
                        v-model="queryParams.qid"
                        clearable
                        placeholder="请输入资源ID"
                    />
                </el-form-item>

                <el-form-item label="内容" prop="content">
                    <el-input
                        class="w-[180px]"
                        v-model="queryParams.content"
                        clearable
                        placeholder="请输入内容"
                    />
                </el-form-item>
                <el-form-item label="IP地址" prop="ip">
                    <el-input
                        class="w-[180px]"
                        v-model="queryParams.ip"
                        clearable
                        placeholder="请输入IP地址"
                    />
                </el-form-item>
                <el-form-item label="状态" prop="status">
                    <el-select
                        style="width: 150px"
                        v-model="queryParams.status"
                        clearable
                        placeholder="请选择状态"
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
                <el-form-item>
                    <el-button type="primary" @click="getLists">查询</el-button>
                    <el-button @click="resetParams">重置</el-button>
                </el-form-item>
            </el-form>
        </el-card>
        <el-card class="!border-none" shadow="never">
            <div>
                <el-button
                    v-perms="['exam.tenant_exam_comment/add']"
                    type="primary"
                    @click="handleAdd()"
                >
                    <template #icon>
                        <icon name="el-icon-Plus" />
                    </template>
                    新增
                </el-button>
                <el-button @click="handleExpand"> 展开/折叠 </el-button>
            </div>
            <div class="mt-4">
                <el-table
                    v-loading="loading"
                    ref="tableRef"
                    class="mt-4"
                    size="large"
                    :data="lists"
                    row-key="id"
                    :tree-props="{ children: 'children', hasChildren: 'hasChildren' }"
                >
                    <el-table-column label="用户ID" prop="user_id" show-overflow-tooltip />
                    <!--  -->
                    <el-table-column
                        v-if="queryParams.type === 1"
                        label="试题ID"
                        prop="qid"
                        show-overflow-tooltip
                    />
                    <el-table-column
                        v-if="queryParams.type === 2"
                        label="文章ID"
                        prop="qid"
                        show-overflow-tooltip
                    />
                    <el-table-column
                        v-if="queryParams.type === 3"
                        label="资源ID"
                        prop="qid"
                        show-overflow-tooltip
                    />
                    <el-table-column label="父ID" prop="pid" show-overflow-tooltip />
                    <el-table-column label="内容" prop="content" show-overflow-tooltip />
                    <el-table-column label="评论数" prop="comments" show-overflow-tooltip />
                    <el-table-column label="点赞数" prop="likes" show-overflow-tooltip />
                    <el-table-column label="IP地址" prop="ip" show-overflow-tooltip />
                    <el-table-column label="创建时间" prop="createtime" show-overflow-tooltip />
                    <el-table-column label="状态" prop="status">
                        <template #default="{ row }">
                            <dict-value :options="dictData.show_status" :value="row.status" />
                        </template>
                    </el-table-column>
                    <el-table-column label="操作" width="160" fixed="right">
                        <template #default="{ row }">
                            <el-button
                                v-perms="['exam.tenant_exam_comment/add']"
                                type="primary"
                                link
                                @click="handleAdd(row.id)"
                            >
                                新增
                            </el-button>
                            <el-button
                                v-perms="['exam.tenant_exam_comment/edit']"
                                type="primary"
                                link
                                @click="handleEdit(row)"
                            >
                                编辑
                            </el-button>
                            <el-button
                                v-perms="['exam.tenant_exam_comment/delete']"
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

<script lang="ts" setup name="tenantExamCommentLists">
import type { ElTable, FormInstance } from 'element-plus'

import {
    apiTenantExamCommentDelete,
    apiTenantExamCommentLists
} from '@/api/exam/tenant_exam_comment'
import { useDictData } from '@/hooks/useDictOptions'
import feedback from '@/utils/feedback'

import EditPopup from './edit.vue'

const tableRef = shallowRef<InstanceType<typeof ElTable>>()
const formRef = shallowRef<FormInstance>()
const editRef = shallowRef<InstanceType<typeof EditPopup>>()
let isExpand = false

// 是否显示编辑框
const showEdit = ref(false)
const loading = ref(false)
const lists = ref<any[]>([])

// 查询条件
const queryParams = reactive({
    user_id: '',
    qid: '',
    pid: '',
    content: '',
    ip: '',
    status: '',
    type: 1
})

const resetParams = () => {
    formRef.value?.resetFields()
    getLists({ type: queryParams.type })
}

const getLists = async (params: any) => {
    loading.value = true
    try {
        const data = await apiTenantExamCommentLists({ type: params.type })
        lists.value = data.lists
        loading.value = false
    } catch (error) {
        loading.value = false
    }
}

// 获取字典数据
const { dictData } = useDictData('show_status')

// 添加
const handleAdd = async (id?: number) => {
    showEdit.value = true
    await nextTick()
    if (id) {
        editRef.value?.setFormData({
            pid: id
        })
    }
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
    await apiTenantExamCommentDelete({ id })
    getLists({ type: queryParams.type })
}

const handleExpand = () => {
    isExpand = !isExpand
    toggleExpand(lists.value, isExpand)
}

const toggleExpand = (children: any[], unfold = true) => {
    for (const key in children) {
        tableRef.value?.toggleRowExpansion(children[key], unfold)
        if (children[key].children) {
            toggleExpand(children[key].children!, unfold)
        }
    }
}

//默认选中第一个tab
const activeIndex = ref('1')

const handleSelect = (key: string, keyPath: string[]) => {
    queryParams.type = Number(key)
    getLists({ type: Number(key) })
}

getLists({ type: 1 })
</script>
