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
                <el-form-item label="租户ID" prop="tenant_id">
                    <el-input v-model="formData.tenant_id" clearable placeholder="请输入租户ID" />
                </el-form-item>
                <el-form-item label="用户ID" prop="user_id">
                    <el-input v-model="formData.user_id" clearable placeholder="请输入用户ID" />
                </el-form-item>
                <el-form-item label="OpenID" prop="openid">
                    <el-input v-model="formData.openid" clearable placeholder="请输入OpenID" />
                </el-form-item>
                <el-form-item label="订阅类型" prop="type">
                    <el-select v-model="formData.type" clearable placeholder="请选择订阅类型">
                        <el-option label="考试提醒" value="exam"></el-option>
                        <el-option label="倒计时提醒" value="countdown"></el-option>
                        <el-option label="版本更新" value="version_update"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="关联ID" prop="related_id">
                    <el-input v-model="formData.related_id" clearable placeholder="请输入关联ID" />
                </el-form-item>
                <el-form-item label="模板ID" prop="template_id">
                    <el-input v-model="formData.template_id" clearable placeholder="请输入模板ID" />
                </el-form-item>
                <el-form-item label="消息内容" prop="message_data">
                    <el-input
                        v-model="formData.message_data"
                        type="textarea"
                        :rows="4"
                        placeholder="请输入消息内容（JSON格式）"
                    />
                </el-form-item>
                <el-form-item label="发送状态" prop="send_status">
                    <el-select v-model="formData.send_status" placeholder="请选择发送状态">
                        <el-option label="待发送" value="0"></el-option>
                        <el-option label="发送成功" value="1"></el-option>
                        <el-option label="发送失败" value="2"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="重试次数" prop="retry_times">
                    <el-input
                        v-model="formData.retry_times"
                        type="number"
                        clearable
                        placeholder="请输入重试次数"
                    />
                </el-form-item>
                <el-form-item label="错误码" prop="error_code">
                    <el-input
                        v-model="formData.error_code"
                        type="number"
                        clearable
                        placeholder="请输入错误码"
                    />
                </el-form-item>
                <el-form-item label="错误信息" prop="error_msg">
                    <el-input v-model="formData.error_msg" clearable placeholder="请输入错误信息" />
                </el-form-item>
            </el-form>
        </popup>
    </div>
</template>

<script lang="ts" setup name="tenantSubscribeSendLogEdit">
import type { FormInstance } from 'element-plus'
import { computed, ref } from 'vue'

import {
    subscribeSendLogAdd,
    subscribeSendLogDetail,
    subscribeSendLogEdit
} from '@/api/exam/tenant_subscribe_send_log'
import Popup from '@/components/popup/index.vue'

const emit = defineEmits(['success', 'close'])
const formRef = ref<FormInstance>()
const popupRef = ref<InstanceType<typeof Popup>>()
const mode = ref('add')

// 弹窗标题
const popupTitle = computed(() => {
    return mode.value == 'edit' ? '编辑订阅发送记录' : '新增订阅发送记录'
})

// 表单数据
const formData = ref({
    id: '',
    tenant_id: '',
    user_id: '',
    openid: '',
    type: '',
    related_id: '',
    template_id: '',
    message_data: '',
    send_status: 0,
    send_time: '',
    retry_times: 0,
    error_code: 0,
    error_msg: '',
    response_data: '',
    create_time: '',
    update_time: ''
})

// 表单验证
const formRules = ref<any>({
    tenant_id: [
        {
            required: true,
            message: '请输入租户ID',
            trigger: ['blur']
        }
    ],
    user_id: [
        {
            required: true,
            message: '请输入用户ID',
            trigger: ['blur']
        }
    ],
    type: [
        {
            required: true,
            message: '请选择订阅类型',
            trigger: ['blur']
        }
    ],
    template_id: [
        {
            required: true,
            message: '请输入模板ID',
            trigger: ['blur']
        }
    ]
})

// 获取详情
const setFormData = async (data: Record<any, any>) => {
    for (const key in formData.value) {
        if (data[key] != null && data[key] != undefined) {
            //@ts-ignore
            formData.value[key] = data[key]
        }
    }
    // 处理字段映射
    if (data.data) {
        formData.value.message_data = data.data
    }
    if (data.result) {
        formData.value.response_data = data.result
    }
}

// 提交按钮
const handleSubmit = async () => {
    await formRef.value?.validate()

    // 转换字段名，适配API
    const { message_data, response_data, create_time, update_time, ...submitData } = formData.value

    // 添加转换后的字段
    const finalData = {
        ...submitData,
        data: message_data, // 转换为API需要的字段名
        result: response_data // 转换为API需要的字段名
    }

    // 根据模式调用不同的API
    if (mode.value == 'edit') {
        await subscribeSendLogEdit(finalData)
    } else {
        await subscribeSendLogAdd(finalData)
    }

    popupRef.value?.close()
    emit('success')
}

// 打开弹窗
const open = (type = 'add') => {
    mode.value = type
    popupRef.value?.open()
}

// 获取详情
const getDetail = async (id: number) => {
    const result = await subscribeSendLogDetail({ id })
    setFormData(result)
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
