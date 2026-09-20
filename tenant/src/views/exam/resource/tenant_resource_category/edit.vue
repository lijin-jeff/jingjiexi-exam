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
                <el-form-item label="所属题库" prop="exam_category_uid">
                    <el-cascader
                        v-model="formData.exam_category_uid"
                        :options="examCategoryList"
                        :props="examCascaderProps"
                        placeholder="请选择题库分类"
                        style="width: 100%"
                        clearable
                        filterable
                        @change="handleChangeExamCategory"
                    />
                </el-form-item>
                <el-form-item label="上级分类" prop="parent_uid">
                    <el-tree-select
                        v-model="formData.parent_uid"
                        :data="parentList"
                        clearable
                        filterable
                        node-key="uid"
                        :props="{
                            label: 'title',
                            children: 'children'
                        }"
                        :default-expand-all="false"
                        placeholder="请选择上级分类"
                        check-strictly
                        style="width: 100%"
                    />
                </el-form-item>
                <el-form-item label="分类名称" prop="title">
                    <el-input v-model="formData.title" clearable placeholder="请输入分类名称" />
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
                        style="width: 100%"
                        v-model="formData.sort"
                        :step="1"
                        :min="0"
                        clearable
                        placeholder="请输入显示权重"
                    />
                </el-form-item>
                <el-form-item label="分类封面" prop="image">
                    <material-picker v-model="formData.image" />
                </el-form-item>
            </el-form>
        </popup>
    </div>
</template>

<script lang="ts" setup name="tenantResourceCategoryEdit">
import type { FormInstance } from 'element-plus'
import type { PropType } from 'vue'

import { apiExamCategoryTree } from '@/api/exam/exam_category'
import {
    apiTenantResourceCategoryAdd,
    apiTenantResourceCategoryDetail,
    apiTenantResourceCategoryEdit
} from '@/api/exam/resource/tenant_resource_category'
import { apiTenantResourceCategoryParent } from '@/api/exam/resource/tenant_resource_category'
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
const parentList = reactive<any[]>([])
const examCategoryList = reactive<any[]>([])

// 级联选择器配置 - 题库分类
const examCascaderProps = {
    value: 'uid',
    label: 'title',
    children: 'children',
    checkStrictly: true, // 允许选择任意一级
    emitPath: false // 只返回选中节点的值，不返回路径数组
}

// 弹窗标题
const popupTitle = computed(() => {
    return mode.value == 'edit' ? '编辑资源分类' : '新增资源分类'
})

// 表单数据
const formData = reactive({
    id: '',
    title: '',
    is_show: 1,
    sort: 0,
    parent_uid: '',
    image: '',
    exam_category_uid: ''
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
    ],
    exam_category_uid: [
        {
            required: true,
            message: '请选择题库分类',
            trigger: ['change', 'blur']
        }
    ]
})

// 获取详情
const setFormData = async (data: Record<any, any>) => {
    // 直接赋值已知字段，避免索引签名问题
    if (data.id != null) formData.id = data.id
    if (data.title != null) formData.title = data.title
    if (data.is_show != null) formData.is_show = data.is_show
    if (data.sort != null) formData.sort = data.sort
    if (data.parent_uid != null) formData.parent_uid = data.parent_uid
    if (data.image != null) formData.image = data.image
    if (data.exam_category_uid != null) formData.exam_category_uid = data.exam_category_uid
}

const getDetail = async (row: Record<string, any>) => {
    const data = await apiTenantResourceCategoryDetail({
        id: row.id
    })
    setFormData(data)
    // 根据获取到的题库ID获取关联分类
    fetchParentList(formData.exam_category_uid || '')
}

// 提交按钮
const handleSubmit = async () => {
    await formRef.value?.validate()
    const data = { ...formData }
    mode.value == 'edit'
        ? await apiTenantResourceCategoryEdit(data)
        : await apiTenantResourceCategoryAdd(data)
    popupRef.value?.close()
    emit('success')
}

//打开弹窗
const open = (type = 'add') => {
    mode.value = type
    popupRef.value?.open()
}

// 题库分类变化处理
const handleChangeExamCategory = (value: any) => {
    formData.exam_category_uid = value || ''
    // 清空之前选中的上级分类
    formData.parent_uid = ''
    // 根据选中的题库ID获取关联分类
    fetchParentList(formData.exam_category_uid)
}

// 关闭回调
const handleClose = () => {
    emit('close')
}

const fetchParentList = async (exam_category_uid: any = '') => {
    try {
        const res = await apiTenantResourceCategoryParent({ exam_category_uid: exam_category_uid })
        console.log('上级分类数据响应:', res)

        // 添加顶级选项并兼容多种数据格式
        const lists = res?.lists || res?.data?.lists || res || []
        const topLevel = { uid: '0', title: '顶级分类', children: lists }

        parentList.length = 0
        parentList.push(topLevel)
        console.log('上级分类树数据:', parentList)
    } catch (error) {
        console.error('获取上级分类失败:', error)
        parentList.length = 0
    }
}
fetchParentList()

// 获取题库分类数据
const fetchExamCategoryList = async () => {
    try {
        const res = await apiExamCategoryTree()
        console.log('题库分类响应:', res)
        // 清空旧数据并添加新数据
        examCategoryList.length = 0
        examCategoryList.push(...(res || []))
        console.log('题库分类数据:', examCategoryList)
    } catch (error) {
        console.error('获取题库分类失败:', error)
    }
}
fetchExamCategoryList()

defineExpose({
    open,
    setFormData,
    getDetail
})
</script>
