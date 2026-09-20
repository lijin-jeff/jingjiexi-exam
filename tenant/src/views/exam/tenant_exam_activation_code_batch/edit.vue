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
                <!-- 如果是编辑，禁用 -->
                <el-form-item label="开通时长" prop="duration_days">
                    <el-input
                        v-model="formData.duration_days"
                        clearable
                        :disabled="formData.id != ''"
                        placeholder="请输入开通时长(天)"
                    />
                </el-form-item>
                <!-- 如果是编辑，禁用 -->
                <el-form-item label="生成数量" prop="total_count">
                    <el-input
                        v-model="formData.total_count"
                        clearable
                        :disabled="formData.id != ''"
                        placeholder="请输入生成数量"
                    />
                </el-form-item>
                <el-form-item label="备注" prop="remark">
                    <el-input
                        class="flex-1"
                        v-model="formData.remark"
                        type="textarea"
                        :rows="4"
                        clearable
                        placeholder="请输入备注"
                    />
                </el-form-item>
                <el-form-item label="状态" prop="status">
                    <el-radio-group v-model="formData.status" placeholder="请选择状态">
                        <el-radio
                            v-for="(item, index) in dictData.system_disable"
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

<script lang="ts" setup name="tenantExamActivationCodeBatchEdit">
import type { FormInstance } from 'element-plus'
import type { PropType } from 'vue'

import {
    apiTenantExamActivationCodeBatchAdd,
    apiTenantExamActivationCodeBatchDetail,
    apiTenantExamActivationCodeBatchEdit
} from '@/api/exam/tenant_exam_activation_code_batch'
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
    return mode.value == 'edit' ? '编辑激活码批次表' : '新增激活码批次表'
})

// 表单数据
const formData = reactive({
    id: '',
    duration_days: '',
    total_count: '',
    remark: '',
    status: 0
})

// 重置表单数据
const resetFormData = () => {
    formData.id = ''
    formData.duration_days = ''
    formData.total_count = ''
    formData.remark = ''
    formData.status = 0
}

// 表单验证
const formRules = reactive<any>({
    duration_days: [
        {
            required: true,
            message: '请输入开通时长(天)',
            trigger: ['blur']
        }
    ],
    total_count: [
        {
            required: true,
            message: '请输入总数量',
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
    const data = await apiTenantExamActivationCodeBatchDetail({
        id: row.id
    })
    setFormData(data)
}

// 提交按钮
const handleSubmit = async () => {
    await formRef.value?.validate()
    const data = { ...formData }
    mode.value == 'edit'
        ? await apiTenantExamActivationCodeBatchEdit(data)
        : await apiTenantExamActivationCodeBatchAdd(data)
    popupRef.value?.close()
    emit('success')
}

//打开弹窗
const open = (type = 'add') => {
    mode.value = type
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
