<template>
    <div class="edit-popup">
        <popup
            ref="popupRef"
            :title="popupTitle"
            :async="true"
            width="600px"
            @confirm="handleSubmit"
            @close="handleClose"
        >
            <el-form ref="formRef" :model="formData" label-width="90px" :rules="formRules">
                <el-form-item label="题库分类" prop="category_uid">
                    <el-cascader
                        v-model="formData.category_uid"
                        :options="categoryList"
                        :props="cascaderProps"
                        placeholder="请选择题库分类"
                        style="width: 100%"
                        clearable
                        filterable
                        @change="handleChange"
                    />
                </el-form-item>
                <el-form-item label="题库名称" prop="title">
                    <el-input v-model="formData.title" clearable placeholder="请输入" />
                </el-form-item>
                <el-form-item label="启用状态" prop="is_show">
                    <el-radio-group v-model="formData.is_show" placeholder="请选择">
                        <el-radio
                            v-for="(item, index) in dictData.show_status"
                            :key="index"
                            :label="parseInt(item.value)"
                        >
                            {{ item.name }}
                        </el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="是否推荐" prop="recommend_state">
                    <el-radio-group v-model="formData.recommend_state" placeholder="请选择">
                        <el-radio
                            v-for="(item, index) in dictData.recommend_state"
                            :key="index"
                            :label="parseInt(item.value)"
                        >
                            {{ item.name }}
                        </el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="是否热门" prop="hot_state">
                    <el-radio-group v-model="formData.hot_state" placeholder="请选择">
                        <el-radio
                            v-for="(item, index) in dictData.hot_state"
                            :key="index"
                            :label="parseInt(item.value)"
                        >
                            {{ item.name }}
                        </el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="显示权重" prop="sort">
                    <div style="width: 100%">
                        <el-input-number
                            v-model="formData.sort"
                            clearable
                            placeholder="请输入"
                            :min="0"
                            :step="1"
                            style="width: 100%"
                        />
                        <div class="form-tips">默认为0， 只能为大于0的整数。值越大越排前显示。</div>
                    </div>
                </el-form-item>
                <el-form-item label="题库封面" prop="image">
                    <material-picker v-model="formData.image" />
                </el-form-item>
                <el-form-item label="题库描述" prop="remark">
                    <el-input
                        class="flex-1"
                        v-model="formData.remark"
                        type="textarea"
                        :rows="4"
                        clearable
                        placeholder="请输入"
                    />
                </el-form-item>
                <el-form-item label="题库作者" prop="author">
                    <el-input v-model="formData.author" clearable placeholder="请输入" />
                </el-form-item>
                <el-form-item label="收费状态" prop="free_state">
                    <el-select
                        class="flex-1"
                        v-model="formData.free_state"
                        clearable
                        placeholder="请选择"
                    >
                        <el-option
                            v-for="(item, index) in dictData.price_typ"
                            :key="index"
                            :label="item.name"
                            :value="item.value"
                        />
                    </el-select>
                </el-form-item>
                <el-form-item label="题库价格" prop="money">
                    <div>
                        <div>
                            <el-input-number
                                :min="0"
                                :step="0.01"
                                v-model="formData.money"
                                clearable
                                placeholder="请输入题库价格"
                            />
                        </div>
                        <div><div class="form-tips">只有设置为付费题库，价格才会生效。</div></div>
                    </div>
                </el-form-item>
                <el-form-item label="折扣价格" prop="discount">
                    <div style="width: 100%">
                        <el-input-number
                            :min="0"
                            :step="0.01"
                            v-model="formData.discount"
                            clearable
                            placeholder="请输入折扣价格"
                        />
                        <div>
                            <div class="form-tips">
                                折扣价格不为空，或者大于 0 时，下单价格以折扣价格计算。
                            </div>
                        </div>
                    </div>
                </el-form-item>
                <el-form-item label="题库年份" prop="year">
                    <el-select
                        class="flex-1"
                        v-model="formData.year"
                        clearable
                        placeholder="请选择"
                    >
                        <el-option
                            v-for="(item, index) in dictData.data_year"
                            :key="index"
                            :label="item.name"
                            :value="parseInt(item.value)"
                        />
                    </el-select>
                </el-form-item>
            </el-form>
        </popup>
    </div>
</template>

<script lang="ts" setup name="tenantExamLibraryEdit">
import type { FormInstance } from 'element-plus'
import type { PropType } from 'vue'

import { apiExamCategoryTree } from '@/api/exam/exam_category'
import {
    apiTenantExamLibraryAdd,
    apiTenantExamLibraryDetail,
    apiTenantExamLibraryEdit
} from '@/api/exam/tenant_exam_library'
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
const categoryList = reactive([])

// Cascader 配置
const cascaderProps = {
    value: 'uid',
    label: 'title',
    children: 'children',
    checkStrictly: true, // 允许选择任意一级
    emitPath: false // 只返回选中节点的值，不返回路径数组
}

// 弹窗标题
const popupTitle = computed(() => {
    return mode.value == 'edit' ? '编辑题库管理' : '新增题库管理'
})

// 表单数据
const formData = reactive({
    id: '',
    title: '',
    is_show: 1,
    sort: 100,
    image: '',
    remark: '',
    category_uid: '',
    author: '',
    free_state: '',
    money: 0,
    discount: 0,
    year: '',
    recommend_state: 0,
    hot_state: 0,
    parent_uid: ''
})

// 表单验证
const formRules = reactive<any>({
    title: [
        {
            required: true,
            message: '请输入题库名称',
            trigger: ['blur']
        }
    ],
    is_show: [
        {
            required: true,
            message: '请选择题库显示状态',
            trigger: ['blur']
        }
    ],
    sort: [
        {
            required: true,
            message: '请输入题库显示权重',
            trigger: ['blur']
        }
    ],
    category_uid: [
        {
            required: true,
            message: '请选择题库分类',
            trigger: ['blur']
        }
    ],
    recommend_state: [
        {
            required: true,
            message: '请选择是否推荐',
            trigger: ['blur']
        }
    ],
    hot_state: [
        {
            required: true,
            message: '请选择是否热门',
            trigger: ['blur']
        }
    ]
})

const handleChange = (value: any) => {
    formData.category_uid = value || ''
    if (value) {
        formData.parent_uid = value
    }
}

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
    const data = await apiTenantExamLibraryDetail({
        id: row.id
    })
    setFormData(data)
}

// 提交按钮
const handleSubmit = async () => {
    await formRef.value?.validate()
    const data = { ...formData }
    mode.value == 'edit'
        ? await apiTenantExamLibraryEdit(data)
        : await apiTenantExamLibraryAdd(data)
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

const fetchExamCategoryList = async () => {
    await apiExamCategoryTree().then((res) => {
        Object.assign(categoryList, res)
    })
}
fetchExamCategoryList()

defineExpose({
    open,
    setFormData,
    getDetail
})
</script>
