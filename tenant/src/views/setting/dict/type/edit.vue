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
            <el-form
                class="ls-form"
                ref="formRef"
                :rules="rules"
                :model="formData"
                label-width="84px"
            >
                <el-form-item label="字典名称" prop="name">
                    <el-input v-model="formData.name" placeholder="请输入字典名称" clearable />
                </el-form-item>
                <el-form-item label="字典类型" prop="type">
                    <el-input v-model="formData.type" placeholder="请输入字典类型" clearable />
                </el-form-item>
                <el-form-item label="字典状态" required prop="status">
                    <el-radio-group v-model="formData.status">
                        <el-radio :value="1">正常</el-radio>
                        <el-radio :value="0">停用</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="备注" prop="remark">
                    <el-input
                        v-model="formData.remark"
                        type="textarea"
                        :autosize="{ minRows: 4, maxRows: 6 }"
                        clearable
                        maxlength="200"
                        show-word-limit
                    />
                </el-form-item>
            </el-form>
        </popup>
    </div>
</template>
<script lang="ts" setup>
import type { FormInstance } from 'element-plus'

import { dictTypeAdd, dictTypeDetail, dictTypeEdit } from '@/api/setting/dict'
import Popup from '@/components/popup/index.vue'

const emit = defineEmits(['success', 'close'])
const formRef = shallowRef<FormInstance>()
const popupRef = shallowRef<InstanceType<typeof Popup>>()
const mode = ref('add')
const popupTitle = computed(() => {
    return mode.value == 'edit' ? '编辑字典类型' : '新增字典类型'
})
const formData = reactive({
    id: '',
    name: '',
    type: '',
    status: 1,
    remark: ''
})

const rules = {
    name: [
        {
            required: true,
            message: '请输入字典名称',
            trigger: ['blur']
        }
    ],
    type: [
        {
            required: true,
            message: '请输入字典类型',
            trigger: ['blur']
        }
    ]
}

// 重置表单数据
const resetFormData = () => {
    formData.id = ''
    formData.name = ''
    formData.type = ''
    formData.status = 1
    formData.remark = ''
}

const handleSubmit = async () => {
    await formRef.value?.validate()
    mode.value == 'edit' ? await dictTypeEdit(formData) : await dictTypeAdd(formData)
    popupRef.value?.close()
    emit('success')
}

const handleClose = () => {
    emit('close')
}

const open = (type = 'add') => {
    mode.value = type
    resetFormData()
    formRef.value?.clearValidate()
    popupRef.value?.open()
}

const setFormData = (data: Record<any, any>) => {
    for (const key in formData) {
        if (data[key] != null && data[key] != undefined) {
            //@ts-ignore
            formData[key] = data[key]
        }
    }
}

const getDetail = async (row: Record<string, any>) => {
    const data = await dictTypeDetail({ id: row.id })
    setFormData(data)
}

defineExpose({
    open,
    setFormData,
    getDetail
})
</script>
