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

<script lang="ts" setup name="tenantExamRankingTotalEdit">
import type { FormInstance } from 'element-plus'
import type { PropType } from 'vue'

import {
    apiTenantExamRankingTotalDetail,
    apiTenantExamRankingTotalEdit
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

// 弹窗标题
const popupTitle = computed(() => {
    return '编辑答题总排行榜（按年更新）'
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
    const data = await apiTenantExamRankingTotalDetail({
        id: row.id
    })
    setFormData(data)
}

// 提交按钮
const handleSubmit = async () => {
    await formRef.value?.validate()
    const data = { ...formData }
    await apiTenantExamRankingTotalEdit(data)
    popupRef.value?.close()
    emit('success')
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
