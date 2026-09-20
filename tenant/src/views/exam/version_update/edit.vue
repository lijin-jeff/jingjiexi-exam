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
                <el-form-item label="版本号" prop="version">
                    <el-input
                        v-model="formData.version"
                        clearable
                        placeholder="请输入版本号（格式：x.y.z）"
                        maxlength="20"
                        show-word-limit
                    />
                </el-form-item>
                <el-form-item label="更新内容" prop="info">
                    <el-input
                        v-model="formData.info"
                        clearable
                        placeholder="请输入更新内容"
                        type="textarea"
                        :autosize="{ minRows: 4, maxRows: 8 }"
                        maxlength="500"
                        show-word-limit
                    />
                </el-form-item>
                <el-form-item label="状态" prop="status">
                    <el-radio-group v-model="formData.status" placeholder="请选择状态">
                        <el-radio :value="1">已发布</el-radio>
                        <el-radio :value="0">未发布</el-radio>
                    </el-radio-group>
                </el-form-item>
            </el-form>
        </popup>
    </div>
</template>

<script lang="ts" setup name="versionUpdateEdit">
import type { FormInstance } from 'element-plus'
import type { PropType } from 'vue'
import { computed, reactive, ref } from 'vue'

import {
    versionUpdateAdd,
    versionUpdateEdit as apiVersionUpdateEdit
} from '@/api/exam/version_update'
import Popup from '@/components/popup/index.vue'

const emit = defineEmits(['success', 'close'])
const formRef = shallowRef<FormInstance>()
const popupRef = shallowRef<InstanceType<typeof Popup>>()
const mode = ref('add')

// 弹窗标题
const popupTitle = computed(() => {
    return mode.value == 'edit' ? '编辑版本更新' : '新增版本更新'
})

// 表单数据
const formData = reactive({
    id: '',
    version: '',
    info: '',
    status: 0,
    release_time: 0
})

// 表单验证规则
const formRules = reactive<any>({
    version: [
        { required: true, message: '请输入版本号', trigger: ['blur'] },
        { pattern: /^\d+\.\d+\.\d+$/, message: '版本号格式错误（应为x.y.z）', trigger: ['blur'] }
    ],
    info: [{ required: true, message: '请输入更新内容', trigger: ['blur'] }],
    status: [{ required: true, message: '请选择状态', trigger: ['blur'] }]
})

// 设置表单数据
const setFormData = async (data: Record<any, any>) => {
    for (const key in formData) {
        if (data[key] != null && data[key] != undefined) {
            //@ts-ignore
            formData[key] = data[key]
        }
    }
}

// 提交按钮
const handleSubmit = async () => {
    await formRef.value?.validate()

    // 如果是发布状态，设置发布时间
    if (formData.status === 1 && !formData.release_time) {
        formData.release_time = Math.floor(Date.now() / 1000)
    }

    const data = { ...formData }
    mode.value == 'edit' ? await apiVersionUpdateEdit(data) : await versionUpdateAdd(data)
    popupRef.value?.close()
    emit('success')
}

// 打开弹窗
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
    setFormData
})
</script>
