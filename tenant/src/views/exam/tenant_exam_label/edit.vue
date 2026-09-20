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
                ref="formRef"
                :model="formData"
                label-width="90px"
                :rules="formRules"
                size="large"
            >
                <!-- 所属题库 -->
                <el-form-item label="所属题库" prop="library_uid">
                    <el-select
                        v-model="formData.library_uid"
                        placeholder="请选择所属题库"
                        style="width: 100%"
                        size="large"
                        filterable
                    >
                        <el-option
                            v-for="item in libraryList"
                            :key="item.uid"
                            :label="item.title"
                            :value="item.uid"
                        />
                    </el-select>
                    <div class="mt-2">
                        <div class="text-sm text-gray-500 flex items-center">
                            <el-icon class="mr-1"><InfoFilled /></el-icon>
                            <span>不选择所属题库则为“公共标签”</span>
                        </div>
                    </div>
                </el-form-item>
                <el-form-item label="标签名称" prop="title">
                    <el-input
                        v-model="formData.title"
                        type="textarea"
                        :rows="6"
                        clearable
                        placeholder="请输入标签名称，一行一个"
                        resize="vertical"
                        show-word-limit
                        maxlength="500"
                        size="large"
                    />
                    <div class="mt-2">
                        <div class="text-sm text-gray-500 flex items-center">
                            <el-icon class="mr-1"><InfoFilled /></el-icon>
                            <span>支持多行输入，一行一个标签名称，可以同时添加多个标签</span>
                        </div>
                    </div>
                </el-form-item>
                <el-form-item label="显示状态" prop="is_show">
                    <el-radio-group v-model="formData.is_show" placeholder="请选择显示状态">
                        <el-radio
                            v-for="(item, index) in dictData.show_status"
                            :key="index"
                            :label="parseInt(item.value)"
                        >
                            {{ item.name }}
                        </el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="显示权重" prop="sort">
                    <el-input-number
                        :min="0"
                        :step="1"
                        v-model="formData.sort"
                        clearable
                        placeholder="请输入显示权重"
                        style="width: 100%"
                    />
                </el-form-item>
            </el-form>
        </popup>
    </div>
</template>

<script lang="ts" setup name="tenantExamLabelEdit">
import { InfoFilled } from '@element-plus/icons-vue'
import type { FormInstance } from 'element-plus'
import type { PropType } from 'vue'
import { computed, onMounted, reactive, ref, shallowRef } from 'vue'

import {
    apiTenantExamLabelAdd,
    apiTenantExamLabelDetail,
    apiTenantExamLabelEdit
} from '@/api/exam/tenant_exam_label'
import { apiTenantExamLibraryLists } from '@/api/exam/tenant_exam_library'
import Popup from '@/components/popup/index.vue'

// 定义props
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
const route = useRoute()

// 题库列表
const libraryList = ref<any[]>([])
// 获取题库列表
const fetchLibraryList = async () => {
    try {
        const params = { category_uid: '' }
        const res = await apiTenantExamLibraryLists(params)
        const lists = res?.lists || []
        // 添加公共标签选项
        lists.unshift({
            uid: '0',
            title: '公共标签'
        })
        // 正确赋值ref数组
        libraryList.value = lists
    } catch (error) {
        console.error('获取题库列表失败:', error)
        // 出错时仍添加公共标签选项
        libraryList.value = [
            {
                uid: '0',
                title: '公共标签'
            }
        ]
    }
}
// 初始化获取题库列表
onMounted(() => {
    fetchLibraryList()
})
// 弹窗标题
const popupTitle = computed(() => {
    return mode.value == 'edit' ? '编辑标签' : '新增标签'
})

// 表单数据
const formData = reactive({
    id: '',
    title: '',
    is_show: 1,
    sort: 50,
    library_uid: route.query.library_uid || '0'
})

// 重置表单数据
const resetFormData = () => {
    formData.id = ''
    formData.title = ''
    formData.is_show = 1
    formData.sort = 50
    formData.library_uid = route.query.library_uid || '0'
}

// 表单验证规则
const formRules = reactive<any>({
    title: [
        {
            required: true,
            message: '请输入标签名称',
            trigger: ['blur']
        }
    ],
    is_show: [
        {
            required: true,
            message: '请选择显示状态',
            trigger: ['blur']
        }
    ],
    sort: [
        {
            required: true,
            message: '请输入显示权重',
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
    const data = await apiTenantExamLabelDetail({
        id: row.id
    })
    setFormData(data)
}

// 提交按钮
const handleSubmit = async () => {
    await formRef.value?.validate()
    const data = { ...formData }
    mode.value == 'edit' ? await apiTenantExamLabelEdit(data) : await apiTenantExamLabelAdd(data)
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
