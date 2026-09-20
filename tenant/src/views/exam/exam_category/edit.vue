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
                <el-form-item label="上级分类" prop="parent_uid">
                    <el-select v-model="formData.parent_uid" clearable placeholder="请选择上级分类">
                        <el-option
                            v-for="item in parentList"
                            :key="item.uid"
                            :label="item.title"
                            :value="item.uid"
                        />
                    </el-select>
                </el-form-item>
                <el-form-item label="分类名称" prop="title">
                    <el-input v-model="formData.title" clearable placeholder="请输入分类名称" />
                </el-form-item>
                <el-form-item label="启用状态" prop="is_show">
                    <el-radio-group v-model="formData.is_show" placeholder="请选择启用状态">
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
                    <el-input v-model="formData.sort" clearable placeholder="请输入显示权重" />
                </el-form-item>
                <!-- <el-form-item label="分类封面" prop="cover">
                    <material-picker v-model="formData.cover" />
                </el-form-item> -->
                <el-form-item label="是否推荐" prop="is_recommend">
                    <el-radio-group v-model="formData.is_recommend" placeholder="请选择推荐状态">
                        <el-radio
                            v-for="(item, index) in dictData.is_recommend"
                            :key="index"
                            :label="parseInt(item.value)"
                        >
                            {{ item.name }}
                        </el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="分类icon" prop="icon">
                    <div>
                        <div class="flex items-center">
                            <icon-picker v-model="formData.icon" />
                        </div>
                        <div class="form-tips">
                            适用于用户端自定义图标的情况下，填写icon的名称，前端直接根据icon名称渲染图标
                        </div>
                    </div>
                </el-form-item>
                <el-form-item v-if="formData.parent_uid" label="考试时间" prop="exam_time">
                    <el-date-picker
                        v-model="formData.exam_time"
                        type="date"
                        placeholder="请选择考试时间"
                        value-format="YYYY-MM-DD"
                        size="default"
                    />
                </el-form-item>
            </el-form>
        </popup>
    </div>
</template>

<script lang="ts" setup name="examCategoryEdit">
import type { FormInstance } from 'element-plus'
import { computed, type PropType, reactive, ref, shallowRef } from 'vue'

import {
    apiExamCategoryAdd,
    apiExamCategoryDetail,
    apiExamCategoryEdit,
    apiExamCategoryParent
} from '@/api/exam/exam_category'
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

interface ParentItem {
    uid: string | number
    title: string
}

const parentList = reactive<ParentItem[]>([])

// 弹窗标题
const popupTitle = computed(() => {
    return mode.value == 'edit' ? '编辑试题分类' : '新增试题分类'
})

// 表单数据
const formData = reactive({
    id: '',
    uid: '',
    title: '',
    is_show: 1,
    sort: 0,
    cover: '',
    is_recommend: 1,
    icon: '',
    parent_uid: '',
    exam_time: '' // 改为空字符串，与后端返回的格式一致
})

// 表单验证
const formRules = reactive<any>({
    title: [
        {
            required: true,
            message: '请输入分类名称',
            trigger: ['blur']
        }
    ],
    is_show: [
        {
            required: true,
            message: '请选择启用状态',
            trigger: ['blur']
        }
    ],
    sort: [
        {
            required: true,
            message: '请输入显示权重',
            trigger: ['blur']
        },
        {
            type: 'number',
            message: '显示权重必须为数字',
            trigger: ['blur'],
            transform: (value: string) => Number(value)
        }
    ],
    is_recommend: [
        {
            required: true,
            message: '请选择是否推荐',
            trigger: ['blur']
        }
    ]
})

// 获取详情
const setFormData = async (data: Record<string, any>) => {
    // 直接赋值所有字段，确保类型正确
    for (const key in formData) {
        if (data[key] != null && data[key] != undefined) {
            //@ts-ignore
            formData[key] = data[key]
        }
    }
}

const getDetail = async (row: Record<string, any>) => {
    const res = await apiExamCategoryDetail({
        id: row.id
    })
    // request工具函数已经处理了响应，res就是data
    if (res) {
        setFormData(res)
    }
}

// 提交按钮
const handleSubmit = async () => {
    await formRef.value?.validate()
    const data = { ...formData }

    try {
        if (mode.value == 'edit') {
            await apiExamCategoryEdit(data)
        } else {
            await apiExamCategoryAdd(data)
        }
        // 成功后关闭弹窗并刷新列表
        popupRef.value?.close()
        emit('success')
    } catch (error) {
        console.error('提交失败:', error)
    }
}

const fetchParentList = async () => {
    try {
        const res = await apiExamCategoryParent()
        // 确保获取到正确的数据结构
        // 注意：request函数默认会直接返回response.data.data，所以res就是parentList数组
        if (Array.isArray(res)) {
            // 清空原有数据并添加新数据
            parentList.length = 0
            res.forEach((item: any) => parentList.push(item))
        }
    } catch (error) {
        console.error('获取上级分类失败:', error)
    }
}

// 重置表单数据
const resetFormData = () => {
    formData.id = ''
    formData.uid = ''
    formData.title = ''
    formData.is_show = 1
    formData.sort = 0
    formData.cover = ''
    formData.is_recommend = 1
    formData.icon = ''
    formData.parent_uid = ''
    formData.exam_time = ''
}

//打开弹窗
const open = async (type = 'add') => {
    mode.value = type
    // 重置表单数据
    resetFormData()
    // 清除表单验证状态
    formRef.value?.clearValidate()
    // 每次打开弹窗时重新获取上级分类列表，确保数据最新
    await fetchParentList()
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
