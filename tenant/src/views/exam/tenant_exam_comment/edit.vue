<template>
    <div class="edit-popup">
        <popup
            ref="popupRef"
            :title="popupTitle"
            :async="true"
            width="550px"
            @confirm="handleSubmit"
            @close="handleClose"
        >
            <el-form ref="formRef" :model="formData" label-width="90px" :rules="formRules">
                <el-form-item label="用户ID" prop="user_id">
                    <el-input v-model="formData.user_id" clearable placeholder="请输入用户ID" />
                </el-form-item>
                <el-form-item label="试题ID" prop="qid">
                    <el-input v-model="formData.qid" clearable placeholder="请输入试题ID" />
                </el-form-item>
                <el-form-item label="父ID" prop="pid">
                    <el-tree-select
                        class="flex-1"
                        v-model="formData.pid"
                        :data="treeList"
                        clearable
                        node-key="id"
                        :props="{ label: 'content', value: 'id', children: 'children' }"
                        :default-expand-all="true"
                        placeholder="请选择父ID"
                        check-strictly
                    />
                </el-form-item>
                <el-form-item label="内容" prop="content">
                    <el-input v-model="formData.content" clearable placeholder="请输入内容" />
                </el-form-item>
                <el-form-item label="IP地址" prop="ip">
                    <el-input v-model="formData.ip" clearable placeholder="请输入IP地址" />
                </el-form-item>
                <el-form-item label="订阅" prop="subscribe">
                    <el-input v-model="formData.subscribe" clearable placeholder="请输入订阅" />
                </el-form-item>
                <el-form-item label="状态" prop="status">
                    <el-select
                        class="flex-1"
                        v-model="formData.status"
                        clearable
                        placeholder="请选择状态"
                    >
                        <el-option
                            v-for="(item, index) in dictData.show_status"
                            :key="index"
                            :label="item.name"
                            :value="parseInt(item.value)"
                        />
                    </el-select>
                </el-form-item>
            </el-form>
        </popup>
    </div>
</template>

<script lang="ts" setup name="tenantExamCommentEdit">
import type { FormInstance } from 'element-plus'
import type { PropType } from 'vue'

import {
    apiTenantExamCommentAdd,
    apiTenantExamCommentDetail,
    apiTenantExamCommentEdit,
    apiTenantExamCommentLists
} from '@/api/exam/tenant_exam_comment'
import Popup from '@/components/popup/index.vue'

defineProps({
    dictData: {
        type: Object as PropType<Record<string, any[]>>,
        default: () => ({})
    }
})
const emit = defineEmits(['success', 'close'])
const formRef = shallowRef<FormInstance>()
const popupRef = shallowRef<InstanceType<typeof Popup>>()
const mode = ref('add')
const treeList = ref<any[]>([])

// 弹窗标题
const popupTitle = computed(() => {
    return mode.value == 'edit' ? '编辑试题评论表' : '新增试题评论表'
})

// 表单数据
const formData = reactive({
    id: '',
    user_id: '',
    qid: '',
    pid: '',
    content: '',
    ip: '',
    subscribe: '',
    status: ''
})

// 重置表单数据
const resetFormData = () => {
    formData.id = ''
    formData.user_id = ''
    formData.qid = ''
    formData.pid = ''
    formData.content = ''
    formData.ip = ''
    formData.subscribe = ''
    formData.status = ''
}

// 表单验证
const formRules = reactive<any>({
    user_id: [
        {
            required: true,
            message: '请输入用户ID',
            trigger: ['blur']
        }
    ],
    qid: [
        {
            required: true,
            message: '请输入试题ID',
            trigger: ['blur']
        }
    ],
    content: [
        {
            required: true,
            message: '请输入内容',
            trigger: ['blur']
        }
    ],
    comments: [
        {
            required: true,
            message: '请输入评论数',
            trigger: ['blur']
        }
    ],
    likes: [
        {
            required: true,
            message: '请输入点赞数',
            trigger: ['blur']
        }
    ],
    ip: [
        {
            required: true,
            message: '请输入IP地址',
            trigger: ['blur']
        }
    ],
    subscribe: [
        {
            required: true,
            message: '请输入订阅',
            trigger: ['blur']
        }
    ],
    create_time: [
        {
            required: true,
            message: '请输入创建时间',
            trigger: ['blur']
        }
    ],
    update_time: [
        {
            required: true,
            message: '请输入更新时间',
            trigger: ['blur']
        }
    ],
    status: [
        {
            required: true,
            message: '请选择状态',
            trigger: ['blur']
        }
    ]
})

// 获取详情
const setFormData = async (data: Record<any, any>) => {
    for (const key in formData) {
        if (data[key] != null && data[key] != undefined) {
            //@ts-ignore
            formData[key] = data[key]
        }
    }
}

const getDetail = async (row: Record<string, any>) => {
    const data = await apiTenantExamCommentDetail({
        id: row.id
    })
    setFormData(data)
}

// 提交按钮
const handleSubmit = async () => {
    await formRef.value?.validate()
    const data = { ...formData }
    mode.value == 'edit'
        ? await apiTenantExamCommentEdit(data)
        : await apiTenantExamCommentAdd(data)
    popupRef.value?.close()
    emit('success')
}

//打开弹窗
const open = (type = 'add') => {
    mode.value = type
    resetFormData()
    formRef.value?.clearValidate()
    popupRef.value?.open()
}

// 关闭回调
const handleClose = () => {
    emit('close')
}

const getLists = async () => {
    const data: any = await apiTenantExamCommentLists()
    const item = { id: 0, content: '顶级', children: [] }
    item.children = data.lists
    treeList.value.push(item)
}

getLists()

defineExpose({
    open,
    setFormData,
    getDetail
})
</script>
