<template>
    <div class="edit-popup">
        <popup
            ref="popupRef"
            :title="popupTitle"
            :async="true"
            width="700px"
            @confirm="handleSubmit"
            @close="handleClose"
        >
            <el-form ref="formRef" :model="formData" label-width="90px" :rules="formRules">
                <el-form-item label="试卷名称" prop="title">
                    <el-input v-model="formData.title" clearable placeholder="请输入试卷名称" />
                </el-form-item>
                <el-form-item label="启用状态" prop="is_show">
                    <el-radio-group v-model="formData.is_show" placeholder="请选择启用状态">
                        <el-radio
                            v-for="(item, index) in dictData.show_status"
                            :key="index"
                            :label="parseInt(item.value)"
                        >
                            {{ item.name }}
                        </el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="显示权重" prop="sort">
                    <el-input-number
                        style="width: 100%"
                        v-model="formData.sort"
                        clearable
                        placeholder="请输入显示权重"
                        type="number"
                    />
                </el-form-item>
                <el-form-item label="随机状态" prop="is_rand">
                    <el-radio-group v-model="formData.is_rand" placeholder="请选择随机状态">
                        <el-radio
                            v-for="(item, index) in dictData.paper_rand"
                            :key="index"
                            :label="parseInt(item.value)"
                        >
                            {{ item.name }}
                        </el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="试卷封面" prop="image">
                    <material-picker v-model="formData.image" />
                </el-form-item>
                <el-form-item label="试卷描述" prop="remark">
                    <editor class="flex-1" v-model="formData.remark" :height="500" />
                </el-form-item>
            </el-form>
        </popup>
    </div>
</template>

<script lang="ts" setup name="tenantExamPaperEdit">
import type { FormInstance } from 'element-plus'
import type { PropType } from 'vue'

import {
    apiTenantExamPaperAdd,
    apiTenantExamPaperDetail,
    apiTenantExamPaperEdit
} from '@/api/exam/tenant_exam_paper'
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

// 弹窗标题
const popupTitle = computed(() => {
    return mode.value == 'edit' ? '编辑试卷管理' : '新增试卷管理'
})

// 表单数据
const formData = reactive({
    id: '',
    title: '',
    is_show: 1, // 默认显示状态为1（显示）
    sort: 0, // 默认显示权重为0
    is_rand: 0, // 默认随机状态为0（不随机）
    image: '',
    remark: ''
})

// 重置表单数据
const resetFormData = () => {
    formData.id = ''
    formData.title = ''
    formData.is_show = 1
    formData.sort = 0
    formData.is_rand = 0
    formData.image = ''
    formData.remark = ''
}

// 表单验证
const formRules = reactive<any>({
    title: [
        {
            required: true,
            message: '请输入试卷名称',
            trigger: ['blur']
        }
    ],
    is_show: [
        {
            required: true,
            message: '请选择启用状态',
            trigger: ['blur']
        }
    ],
    sort: [
        {
            required: true,
            message: '请输入显示权重',
            trigger: ['blur']
        }
    ],
    is_rand: [
        {
            required: true,
            message: '请选择随机状态',
            trigger: ['blur']
        }
    ],
    image: [
        {
            required: true,
            message: '请选择试卷封面',
            trigger: ['blur']
        }
    ],
    remark: [
        {
            required: true,
            message: '请输入试卷描述',
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
    const data = await apiTenantExamPaperDetail({
        id: row.id
    })
    setFormData(data)
}

// 提交按钮
const handleSubmit = async () => {
    await formRef.value?.validate()
    const data = { ...formData }
    mode.value == 'edit' ? await apiTenantExamPaperEdit(data) : await apiTenantExamPaperAdd(data)
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

defineExpose({
    open,
    setFormData,
    getDetail
})
</script>
