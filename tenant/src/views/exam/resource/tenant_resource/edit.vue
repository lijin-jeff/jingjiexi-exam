<template>
    <div class="edit-popup">
        <popup
            ref="popupRef"
            :title="popupTitle"
            :async="true"
            width="60%"
            @confirm="handleSubmit"
            @close="handleClose"
        >
            <el-form ref="formRef" :model="formData" label-width="90px" :rules="formRules">
                <el-form-item label="资源分类" prop="category_uid">
                    <el-cascader
                        v-model="formData.category_uid"
                        :options="categoryList"
                        :props="cascaderProps"
                        placeholder="请选择资源分类"
                        style="width: 100%"
                        clearable
                        filterable
                        @change="handleChange"
                    />
                </el-form-item>
                <el-form-item label="所属题库" prop="exam_category_uid">
                    <el-cascader
                        v-model="formData.exam_category_uid"
                        :options="examCategoryList"
                        :props="examCascaderProps"
                        placeholder="请选择题库"
                        style="width: 100%"
                        disabled
                        @change="handleChangeExamCategory"
                    />
                </el-form-item>
                <el-form-item label="资源名称" prop="title">
                    <el-input v-model="formData.title" clearable placeholder="请输入资源名称" />
                </el-form-item>
                <el-form-item label="上架状态" prop="is_show">
                    <el-radio-group v-model="formData.is_show" placeholder="请选择上架状态">
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
                        style="width: 100%"
                        v-model="formData.sort"
                        clearable
                        placeholder="请输入显示权重"
                    />
                </el-form-item>
                <el-form-item label="资源封面" prop="image">
                    <material-picker v-model="formData.image" />
                </el-form-item>
                <el-form-item label="资源附件" prop="file_url">
                    <!-- 切换按钮 -->
                    <el-radio-group
                        v-model="formData.file_input_type"
                        style="display: block; width: 100%"
                    >
                        <el-radio :label="1">上传文件</el-radio>
                        <el-radio :label="2">填写链接</el-radio>
                    </el-radio-group>

                    <!-- 上传模式 -->
                    <material-picker
                        v-if="formData.file_input_type === 1"
                        v-model="formData.file_url"
                        type="file"
                        style="display: block"
                    />

                    <!-- 链接模式 -->
                    <el-input
                        v-else
                        v-model="formData.file_url"
                        placeholder="请输入资源链接"
                        clearable
                        style="width: 100%"
                    />
                </el-form-item>
                <el-form-item label="资源作者" prop="author">
                    <el-input v-model="formData.author" clearable placeholder="请输入资源作者" />
                </el-form-item>
                <el-form-item label="付费状态" prop="free_state">
                    <el-select
                        class="flex-1"
                        v-model="formData.free_state"
                        clearable
                        placeholder="请选择付费状态"
                    >
                        <el-option
                            v-for="(item, index) in dictData.price_typ"
                            :key="index"
                            :label="item.name"
                            :value="item.value"
                        />
                    </el-select>
                </el-form-item>
                <el-form-item label="资源价格" prop="money">
                    <el-input-number
                        v-model="formData.money"
                        :min="0"
                        :step="0.01"
                        style="width: 100%"
                        clearable
                        placeholder="请输入资源价格"
                    />
                </el-form-item>
                <el-form-item label="资源年份" prop="year">
                    <el-select
                        class="flex-1"
                        v-model="formData.year"
                        clearable
                        placeholder="请选择资源年份"
                    >
                        <el-option
                            v-for="(item, index) in dictData.data_year"
                            :key="index"
                            :label="item.name"
                            :value="parseInt(item.value)"
                        />
                    </el-select>
                </el-form-item>
                <el-form-item label="资源描述" prop="remark">
                    <editor class="flex-1" v-model="formData.remark" :height="500" />
                </el-form-item>
            </el-form>
        </popup>
    </div>
</template>

<script lang="ts" setup name="tenantResourceEdit">
import type { FormInstance } from 'element-plus'
import type { PropType } from 'vue'

import { apiExamCategoryTree } from '@/api/exam/exam_category'
import {
    apiTenantResourceAdd,
    apiTenantResourceDetail,
    apiTenantResourceEdit
} from '@/api/exam/resource/tenant_resource'
import { apiTenantResourceCategoryTree } from '@/api/exam/resource/tenant_resource_category'
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
const categoryList = reactive<any[]>([])
const examCategoryList = reactive<any[]>([])

// 资源输入类型：1-上传文件，2-填写链接
const resourceInputType = ref(1)

// Cascader 配置 - 资源分类
const cascaderProps = {
    value: 'uid',
    label: 'title',
    children: 'children',
    checkStrictly: true, // 允许选择任意一级
    emitPath: true // 返回完整的路径数组，以便提取父级分类ID
}

// Cascader 配置 - 所属题库
const examCascaderProps = {
    value: 'uid',
    label: 'title',
    children: 'children',
    checkStrictly: true, // 允许选择任意一级
    emitPath: false // 只返回选中节点的值，不返回路径数组
}

// 弹窗标题
const popupTitle = computed(() => {
    return mode.value == 'edit' ? '编辑资源管理' : '新增资源管理'
})

// 表单数据
const formData = reactive({
    id: '',
    title: '',
    is_show: 1,
    sort: 0,
    image: '',
    remark: '',
    category_parent_uid: '', // 资源分类父级ID
    category_uid: '',
    exam_category_uid: '',
    author: '精解析答题',
    free_state: '',
    money: 0,
    year: '',
    file_input_type: 1,
    file_url: ''
})

// 表单验证
const formRules = reactive<any>({
    title: [
        {
            required: true,
            message: '请输入资源名称',
            trigger: ['blur']
        }
    ],
    is_show: [
        {
            required: true,
            message: '请选择上架装',
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
    image: [
        {
            required: true,
            message: '请输入资源封面',
            trigger: ['blur']
        }
    ],
    remark: [
        {
            required: true,
            message: '请输入资源描述',
            trigger: ['blur']
        }
    ],
    category_uid: [
        {
            required: true,
            message: '请输入资源分类',
            trigger: ['blur']
        }
    ],
    author: [
        {
            required: true,
            message: '请输入资源作者',
            trigger: ['blur']
        }
    ],
    free_state: [
        {
            required: true,
            message: '请选择付费状态',
            trigger: ['blur']
        }
    ],
    file_url: [
        {
            required: true,
            message: '请上传资源',
            trigger: ['blur']
        }
    ],
    year: [
        {
            required: true,
            message: '请选择资源年份',
            trigger: ['blur']
        }
    ]
})

// 辅助函数：递归查找资源分类
const findCategory = (uid: string, categories: any[]): any => {
    for (const category of categories) {
        if (category.uid === uid) {
            return category
        }
        if (category.children && category.children.length > 0) {
            const found = findCategory(uid, category.children)
            if (found) {
                return found
            }
        }
    }
    return null
}

const handleChange = (value: any) => {
    if (Array.isArray(value) && value.length > 0) {
        // 提取当前分类ID（数组最后一个元素）
        formData.category_uid = value[value.length - 1]
        // 提取父级分类ID（如果是二级或更高级分类，则取数组倒数第二个元素；否则为空字符串）
        formData.category_parent_uid = value.length > 1 ? value[value.length - 2] : ''

        // 当选择了资源分类时，自动设置所属题库
        const category = findCategory(formData.category_uid, categoryList)
        if (category) {
            formData.exam_category_uid = category.exam_category_uid
        } else {
            formData.exam_category_uid = ''
        }
    } else {
        // 清空选择的资源分类时，也清空父级分类ID和所属题库
        formData.category_uid = ''
        formData.category_parent_uid = ''
        formData.exam_category_uid = ''
    }
}

const handleChangeExamCategory = (value: any) => {
    formData.exam_category_uid = value || ''
}
// 获取详情
// 辅助函数：递归查找分类并构建路径
const findCategoryPath = (uid: string, categories: any[], path: any[] = []): any[] => {
    for (const category of categories) {
        const newPath = [...path, category.uid]
        if (category.uid === uid) {
            return newPath
        }
        if (category.children && category.children.length > 0) {
            const foundPath = findCategoryPath(uid, category.children, newPath)
            if (foundPath.length > 0) {
                return foundPath
            }
        }
    }
    return []
}

const setFormData = async (data: Record<any, any>) => {
    // 直接赋值已知字段，避免类型错误
    if (data.id != null) formData.id = data.id
    if (data.title != null) formData.title = data.title
    if (data.is_show != null) formData.is_show = data.is_show
    if (data.sort != null) formData.sort = data.sort
    if (data.image != null) formData.image = data.image
    if (data.remark != null) formData.remark = data.remark
    if (data.category_uid != null) formData.category_uid = data.category_uid
    if (data.exam_category_uid != null) formData.exam_category_uid = data.exam_category_uid
    if (data.author != null) formData.author = data.author
    if (data.free_state != null) formData.free_state = data.free_state
    if (data.money != null) formData.money = data.money
    if (data.year != null) formData.year = data.year
    if (data.file_url != null) formData.file_url = data.file_url
    if (data.file_input_type != null) formData.file_input_type = data.file_input_type

    // 处理父级分类ID
    if (data.category_uid) {
        // 查找分类路径
        const categoryPath = findCategoryPath(data.category_uid, categoryList)
        if (categoryPath.length > 0) {
            // 提取父级分类ID（如果是二级或更高级分类，则取数组倒数第二个元素；否则为空字符串）
            formData.category_parent_uid =
                categoryPath.length > 1 ? categoryPath[categoryPath.length - 2] : ''
        } else {
            formData.category_parent_uid = ''
        }
    } else {
        formData.category_parent_uid = ''
    }
}

const getDetail = async (row: Record<string, any>) => {
    const data = await apiTenantResourceDetail({
        id: row.id
    })
    setFormData(data)
}

// 提交按钮
const handleSubmit = async () => {
    await formRef.value?.validate()
    const data = { ...formData }
    mode.value == 'edit' ? await apiTenantResourceEdit(data) : await apiTenantResourceAdd(data)
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

const fetchCategoryList = async () => {
    try {
        const res = await apiTenantResourceCategoryTree()
        console.log('资源分类响应:', res)
        // 清空旧数据并添加新数据
        categoryList.length = 0
        categoryList.push(...(res || []))
        console.log('资源分类数据:', categoryList)
    } catch (error) {
        console.error('获取资源分类失败:', error)
    }
}
fetchCategoryList()

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
