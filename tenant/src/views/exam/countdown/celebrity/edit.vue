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
                <el-form-item label="内容" prop="content">
                    <div class="w-full">
                        <el-input
                            v-model="formData.content"
                            placeholder="请输入名人名言内容，支持多行输入，一行一条"
                            type="textarea"
                            :rows="5"
                            maxlength="500"
                            show-word-limit
                            resize="vertical"
                            size="large"
                        />
                        <div class="mt-2">
                            <div class="text-sm text-gray-500 flex items-center">
                                <el-icon class="mr-1"><InfoFilled /></el-icon>
                                <span
                                    >支持多行输入，一行一条名人名言，可以同时添加多条，每条不超过255个字符</span
                                >
                            </div>
                        </div>
                    </div>
                </el-form-item>
                <el-form-item label="分类" prop="category">
                    <el-input
                        v-model="formData.category"
                        placeholder="请输入分类"
                        maxlength="50"
                        show-word-limit
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
import { InfoFilled } from '@element-plus/icons-vue'
import type { FormInstance } from 'element-plus'
import { ElMessage } from 'element-plus'
import { computed, reactive, ref } from 'vue'

import { celebrityQuoteAdd, celebrityQuoteDetail, celebrityQuoteEdit } from '@/api/exam/countdown'

// 定义emit事件
const emit = defineEmits(['success', 'close'])
const formRef = ref<FormInstance>()
const dialogVisible = ref(false)
const mode = ref('add')

// 弹窗标题
const dialogTitle = computed(() => {
    return mode.value === 'edit' ? '编辑名人名言' : '添加名人名言'
})

// 表单数据
const formData = reactive({
    id: '',
    content: '',
    category: ''
})

// 表单规则
const formRules = {
    content: [
        { required: true, message: '请输入内容', trigger: 'blur' },
        { max: 500, message: '内容不能超过500个字符', trigger: 'blur' }
    ],
    category: [{ max: 50, message: '分类不能超过50个字符', trigger: 'blur' }]
}

// 设置表单数据
const setFormData = async (data: Record<string, any>) => {
    Object.assign(formData, data)
}

// 获取详情
const getDetail = async (row: Record<string, any>) => {
    try {
        const res = await celebrityQuoteDetail({ id: row.id })
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
            res = await celebrityQuoteEdit(submitData)
        } else {
            // 添加模式下不包含id字段
            const { id, ...rest } = formData
            submitData = rest
            res = await celebrityQuoteAdd(submitData)
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
