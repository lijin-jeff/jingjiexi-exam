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
                <el-form-item label="动作" prop="action">
                    <el-radio-group v-model="formData.action" placeholder="请选择动作">
                        <el-radio
                            v-for="(item, index) in dictData.integral_action"
                            :key="index"
                            :label="parseInt(item.value)"
                        >
                            {{ item.name }}
                        </el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="变动数量" prop="change_amount">
                    <el-input
                        v-model="formData.change_amount"
                        clearable
                        placeholder="请输入变动数量"
                    />
                </el-form-item>
                <el-form-item label="备注" prop="remark">
                    <el-input v-model="formData.remark" clearable placeholder="请输入备注" />
                </el-form-item>
                <el-form-item label="扩展" prop="extra">
                    <el-input v-model="formData.extra" clearable placeholder="请输入扩展" />
                </el-form-item>
                <el-form-item label="积分名称" prop="title">
                    <el-input v-model="formData.title" clearable placeholder="请输入积分名称" />
                </el-form-item>
                <el-form-item label="变动类型" prop="change_type">
                    <el-select
                        class="flex-1"
                        v-model="formData.change_type"
                        clearable
                        placeholder="请选择变动类型"
                    >
                        <el-option
                            v-for="(item, index) in dictData.integral_change_type"
                            :key="index"
                            :label="item.name"
                            :value="parseInt(item.value)"
                        />
                    </el-select>
                </el-form-item>
                <el-form-item label="操作类型" prop="action_type">
                    <el-radio-group v-model="formData.action_type" placeholder="请选择操作类型">
                        <el-radio
                            v-for="(item, index) in dictData.integral_action_type"
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

<script lang="ts" setup name="tenantUserIntegralLogEdit">
import type { FormInstance } from 'element-plus'
import type { PropType } from 'vue'

import {
    apiTenantUserIntegralLogAdd,
    apiTenantUserIntegralLogDetail,
    apiTenantUserIntegralLogEdit
} from '@/api/exam/tenant_user_integral_log'
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
    return mode.value == 'edit' ? '编辑积分记录' : '新增积分记录'
})

// 表单数据
const formData = reactive({
    id: '',
    tenant_id: '',
    user_id: '',
    action: '',
    change_amount: '',
    remark: '',
    extra: '',
    title: '',
    change_type: '',
    action_type: ''
})

// 重置表单数据
const resetFormData = () => {
    formData.id = ''
    formData.tenant_id = ''
    formData.user_id = ''
    formData.action = ''
    formData.change_amount = ''
    formData.remark = ''
    formData.extra = ''
    formData.title = ''
    formData.change_type = ''
    formData.action_type = ''
}

// 表单验证
const formRules = reactive<any>({
    user_id: [
        {
            required: true,
            message: '请输入用户ID',
            trigger: ['blur']
        }
    ],
    action: [
        {
            required: true,
            message: '请选择动作',
            trigger: ['blur']
        }
    ],
    change_amount: [
        {
            required: true,
            message: '请输入变动数量',
            trigger: ['blur']
        }
    ],
    title: [
        {
            required: true,
            message: '请输入积分名称',
            trigger: ['blur']
        }
    ],
    change_type: [
        {
            required: true,
            message: '请选择变动类型',
            trigger: ['blur']
        }
    ],
    action_type: [
        {
            required: true,
            message: '请选择操作类型',
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
    const data = await apiTenantUserIntegralLogDetail({
        id: row.id
    })
    setFormData(data)
}

// 提交按钮
const handleSubmit = async () => {
    await formRef.value?.validate()
    const data = { ...formData }
    mode.value == 'edit'
        ? await apiTenantUserIntegralLogEdit(data)
        : await apiTenantUserIntegralLogAdd(data)
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
