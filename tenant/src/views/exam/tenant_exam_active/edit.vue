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
                <el-form-item label="用户编号" prop="user_sn">
                    <el-input v-model="formData.user_sn" clearable placeholder="请输入用户编号" />
                </el-form-item>
                <el-form-item label="题库编号" prop="exam_uid">
                    <el-input v-model="formData.exam_uid" clearable placeholder="请输入题库编号" />
                </el-form-item>
                <el-form-item label="开始时间" prop="start_time">
                    <el-date-picker
                        class="flex-1 !flex"
                        v-model="formData.start_time"
                        clearable
                        type="datetime"
                        value-format="YYYY-MM-DD HH:mm:ss"
                        placeholder="选择开始时间"
                    >
                    </el-date-picker>
                </el-form-item>

                <el-form-item label="结束时间" prop="end_time">
                    <el-date-picker
                        class="flex-1 !flex"
                        v-model="formData.end_time"
                        clearable
                        type="datetime"
                        value-format="YYYY-MM-DD HH:mm:ss"
                        placeholder="选择结束时间"
                    >
                    </el-date-picker>
                </el-form-item>

                <el-form-item label="激活状态" prop="active_status">
                    <el-radio-group v-model="formData.active_status" placeholder="请选择激活状态">
                        <el-radio
                            v-for="(item, index) in dictData.activite_status"
                            :key="index"
                            :label="parseInt(item.value)"
                        >
                            {{ item.name }}
                        </el-radio>
                    </el-radio-group>
                </el-form-item>
            </el-form>
        </popup>
    </div>
</template>

<script lang="ts" setup name="tenantExamActiveEdit">
import type { FormInstance } from 'element-plus'
import type { PropType } from 'vue'

import {
    apiTenantExamActiveAdd,
    apiTenantExamActiveDetail,
    apiTenantExamActiveEdit
} from '@/api/exam/tenant_exam_active'
import Popup from '@/components/popup/index.vue'
import { timeFormat } from '@/utils/util'
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
    return mode.value == 'edit' ? '编辑题库激活' : '新增题库激活'
})

// 表单数据
const formData = reactive({
    id: '',
    user_sn: '',
    exam_uid: '',
    start_time: '',
    end_time: '',
    active_status: 1
})

// 表单验证
const formRules = reactive<any>({
    user_sn: [
        {
            required: true,
            message: '请输入用户编号',
            trigger: ['blur']
        }
    ],
    exam_uid: [
        {
            required: true,
            message: '请输入题库编号',
            trigger: ['blur']
        }
    ],
    start_time: [
        {
            required: true,
            message: '请选择开始时间',
            trigger: ['blur']
        }
    ],
    end_time: [
        {
            required: true,
            message: '请选择结束时间',
            trigger: ['blur']
        }
    ],
    active_status: [
        {
            required: true,
            message: '请选择激活状态',
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

    //@ts-ignore
    formData.start_time = timeFormat(formData.start_time, 'yyyy-mm-dd hh:MM:ss')
    //@ts-ignore
    formData.end_time = timeFormat(formData.end_time, 'yyyy-mm-dd hh:MM:ss')
}

const getDetail = async (row: Record<string, any>) => {
    const data = await apiTenantExamActiveDetail({
        id: row.id
    })
    setFormData(data)
}

// 提交按钮
const handleSubmit = async () => {
    await formRef.value?.validate()
    const data = { ...formData }
    mode.value == 'edit' ? await apiTenantExamActiveEdit(data) : await apiTenantExamActiveAdd(data)
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
