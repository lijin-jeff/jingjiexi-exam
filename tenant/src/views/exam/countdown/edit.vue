<template>
    <div class="edit-popup">
        <el-dialog
            v-model="dialogVisible"
            :title="dialogTitle"
            width="600px"
            destroy-on-close
            @close="handleClose"
        >
            <el-form
                ref="formRef"
                :model="formData"
                :rules="formRules"
                label-width="100px"
                size="large"
            >
                <el-form-item label="标题" prop="title">
                    <el-input
                        v-model="formData.title"
                        placeholder="请输入标题"
                        maxlength="255"
                        show-word-limit
                        size="large"
                    />
                </el-form-item>
                <el-form-item label="目标日期" prop="target_date">
                    <el-date-picker
                        v-model="formData.target_date"
                        type="date"
                        placeholder="请选择目标日期"
                        style="width: 100%"
                        value-format="YYYY-MM-DD"
                        size="default"
                    />
                </el-form-item>
                <el-form-item label="描述">
                    <el-input
                        v-model="formData.description"
                        placeholder="请输入描述"
                        type="textarea"
                        :rows="4"
                        maxlength="500"
                        show-word-limit
                        size="large"
                    />
                </el-form-item>
                <el-form-item label="排序">
                    <el-input-number
                        v-model="formData.sort"
                        :min="0"
                        :max="9999"
                        placeholder="请输入排序"
                        size="large"
                    />
                </el-form-item>
                <el-form-item label="状态">
                    <el-switch
                        v-model="formData.status"
                        :active-value="1"
                        :inactive-value="0"
                        size="large"
                    />
                </el-form-item>
            </el-form>
            <template #footer>
                <el-button @click="handleClose">取消</el-button>
                <el-button type="primary" @click="handleSubmit">确定</el-button>
            </template>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import type { FormInstance } from 'element-plus'
import { ElMessage } from 'element-plus'
import { computed, reactive, ref } from 'vue'

import { countdownAdd, countdownDetail, countdownEdit } from '@/api/exam/countdown'

// 定义emit事件
const emit = defineEmits(['success', 'close'])
const formRef = ref<FormInstance>()
const dialogVisible = ref(false)
const mode = ref('add')

// 弹窗标题
const dialogTitle = computed(() => {
    return mode.value === 'edit' ? '编辑倒计时' : '添加倒计时'
})

// 表单数据
const formData = reactive({
    id: '',
    title: '',
    target_date: '',
    description: '',
    sort: 0,
    status: 1
})

// 表单规则
const formRules = {
    title: [
        { required: true, message: '请输入标题', trigger: 'blur' },
        { max: 255, message: '标题不能超过255个字符', trigger: 'blur' }
    ],
    target_date: [{ required: true, message: '请选择目标日期', trigger: 'blur' }]
}

// 设置表单数据
const setFormData = async (data: Record<string, any>) => {
    Object.assign(formData, data)
}

// 获取详情
const getDetail = async (row: Record<string, any>) => {
    try {
        const res = await countdownDetail({ id: row.id })
        if (res.code === 1) {
            setFormData(res.data)
        }
    } catch (error) {
        ElMessage.error('获取详情失败')
    }
}

// 打开弹窗
const open = (type: 'add' | 'edit') => {
    mode.value = type
    dialogVisible.value = true
}

// 提交表单
const handleSubmit = async () => {
    await formRef.value?.validate()
    try {
        let res
        let submitData
        if (formData.id) {
            submitData = { ...formData }
            res = await countdownEdit(submitData)
        } else {
            // 添加模式下不包含id字段
            const { id, ...rest } = formData
            submitData = rest
            res = await countdownAdd(submitData)
        }
        if (res.code === 1) {
            ElMessage.success(formData.id ? '编辑成功' : '添加成功')
            dialogVisible.value = false
            emit('success')
        }
    } catch (error) {
        // 表单验证失败不做处理
    }
}

// 关闭弹窗
const handleClose = () => {
    dialogVisible.value = false
    emit('close')
}

// 暴露方法
defineExpose({
    open,
    setFormData,
    getDetail
})
</script>

<style scoped>
.edit-popup {
    .el-dialog__body {
        padding: 20px;
    }
}
</style>
