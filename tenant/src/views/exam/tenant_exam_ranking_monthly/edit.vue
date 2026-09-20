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
            </el-form>
        </popup>
    </div>
</template>

<script lang="ts" setup name="tenantExamRankingMonthlyEdit">
import type { FormInstance } from 'element-plus'
import type { PropType } from 'vue'

import {
    apiTenantExamRankingMonthlyDetail,
    apiTenantExamRankingMonthlyEdit
} from '@/api/exam/rank/tenant_exam_ranking'
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
    return '编辑每月答题排行榜（按月更新）'
})

// 表单数据
const formData = reactive({
    id: ''
})

// 表单验证
const formRules = reactive<any>({
    user_id: [
        {
            required: true,
            message: '请输入用户ID',
            trigger: ['blur']
        }
    ]
})

// 重置表单数据
const resetFormData = () => {
    formData.id = ''
}

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
    const data = await apiTenantExamRankingMonthlyDetail({
        id: row.id
    })
    setFormData(data)
}

// 提交按钮
const handleSubmit = async () => {
    await formRef.value?.validate()
    const data = { ...formData }
    await apiTenantExamRankingMonthlyEdit(data)
    popupRef.value?.close()
    emit('success')
}

// 打开弹窗
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
