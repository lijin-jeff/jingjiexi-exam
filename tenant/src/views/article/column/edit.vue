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
            <el-form ref="formRef" :model="formData" label-width="84px" :rules="formRules">
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
                <el-form-item label="栏目名称" prop="name">
                    <el-input v-model="formData.name" placeholder="请输入栏目名称" clearable />
                </el-form-item>
                <el-form-item label="排序" prop="sort">
                    <div>
                        <el-input-number v-model="formData.sort" :min="0" :max="9999" />
                        <div class="form-tips">默认为0， 数值越大越排前</div>
                    </div>
                </el-form-item>
                <el-form-item label="状态" prop="is_show">
                    <el-switch v-model="formData.is_show" :active-value="1" :inactive-value="0" />
                </el-form-item>
            </el-form>
        </popup>
    </div>
</template>
<script lang="ts" setup name="articleColumnEdit">
import type { FormInstance } from 'element-plus'

import { articleCateAdd, articleCateDetail, articleCateEdit } from '@/api/article'
import { apiExamCategoryTree } from '@/api/exam/exam_category'
import Popup from '@/components/popup/index.vue'

const emit = defineEmits(['success', 'close'])
const formRef = shallowRef<FormInstance>()
const popupRef = shallowRef<InstanceType<typeof Popup>>()
const mode = ref('add')
const examCategoryList = reactive<any[]>([])

// 级联选择器配置 - 题库分类
const examCascaderProps = {
    value: 'uid',
    label: 'title',
    children: 'children',
    checkStrictly: true, // 允许选择任意一级
    emitPath: false // 只返回选中节点的值，不返回路径数组
}

const popupTitle = computed(() => {
    return mode.value == 'edit' ? '编辑栏目' : '新增栏目'
})
const formData = reactive({
    id: '',
    name: '',
    sort: 0,
    is_show: 1,
    exam_category_uid: ''
})

const formRules = {
    name: [
        {
            required: true,
            message: '请输入栏目名称',
            trigger: ['blur']
        }
    ],
    exam_category_uid: [
        {
            required: true,
            message: '请选择题库分类',
            trigger: ['blur']
        }
    ]
}

const handleSubmit = async () => {
    await formRef.value?.validate()
    mode.value == 'edit' ? await articleCateEdit(formData) : await articleCateAdd(formData)
    popupRef.value?.close()
    emit('success')
}

const open = (type = 'add') => {
    mode.value = type
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
    const data = await articleCateDetail({
        id: row.id
    })
    setFormData(data)
}

// 题库分类变化处理
const handleChangeExamCategory = (value: any) => {
    formData.exam_category_uid = value || ''
}

const handleClose = () => {
    emit('close')
}

// 获取题库分类数据
const fetchExamCategoryList = async () => {
    try {
        const res = await apiExamCategoryTree()
        examCategoryList.length = 0
        examCategoryList.push(...(res || []))
    } catch (error) {
        console.error('获取题库分类失败:', error)
    }
}

// 初始化获取题库分类数据
fetchExamCategoryList()

defineExpose({
    open,
    setFormData,
    getDetail
})
</script>
