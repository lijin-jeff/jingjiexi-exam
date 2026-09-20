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
                <el-form-item label="父级菜单" prop="parent_uid">
                    <el-tree-select
                        class="flex-1"
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
                        placeholder="请选择父级菜单"
                        check-strictly
                    />
                </el-form-item>
                <el-form-item label="章节名称" prop="title">
                    <div class="w-full">
                        <el-input
                            v-model="formData.title"
                            type="textarea"
                            :rows="6"
                            clearable
                            placeholder="请输入章节名称，一行一个"
                            resize="vertical"
                            show-word-limit
                            maxlength="500"
                        />
                        <div class="mt-2">
                            <div class="text-sm text-gray-500 flex items-center">
                                <el-icon class="mr-1"><InfoFilled /></el-icon>
                                <span>支持多行输入，一行一个章节名称，可以同时添加多个章节</span>
                            </div>
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

<script lang="ts" setup name="tenantExamChapterEdit">
import { InfoFilled } from '@element-plus/icons-vue'
import type { FormInstance } from 'element-plus'
import { cloneDeep } from 'lodash'
import type { PropType } from 'vue'

import {
    apiTenantExamChapterAdd,
    apiTenantExamChapterDetail,
    apiTenantExamChapterEdit,
    apiTenantExamChapterTree
} from '@/api/exam/tenant_exam_chapter'
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
const parentList = ref<any[]>([])
const route = useRoute()

// 弹窗标题
const popupTitle = computed(() => {
    return mode.value == 'edit' ? '编辑题库章节' : '新增题库章节'
})

// 表单数据
const formData = reactive({
    id: '',
    title: '',
    is_show: 1,
    sort: 100,
    parent_uid: 0,
    library_uid: route.query.library_uid
})

// 表单验证
const formRules = reactive<any>({
    title: [
        {
            required: true,
            message: '请输入章节名称',
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
    const data = await apiTenantExamChapterDetail({
        id: row.id
    })
    setFormData(data)
}

// 提交按钮
const handleSubmit = async () => {
    await formRef.value?.validate()
    const data = { ...formData }
    mode.value == 'edit'
        ? await apiTenantExamChapterEdit(data)
        : await apiTenantExamChapterAdd(data)
    popupRef.value?.close()
    emit('success')
}

//打开弹窗
const open = (type = 'add') => {
    mode.value = type
    popupRef.value?.open()
}

/* const fetchParentList = async () => {
    await apiTenantExamChapterTree({library_uid: route.query.library_uid}).then((res) => {
        Object.assign(parentList, res)
    })
} */

// const fetchParentList = async () => {
//     await apiTenantExamChapterTree().then((res) => {
//         Object.assign(parentList, res)
//     })
// }

const fetchParentList = async () => {
    try {
        const res: any = await apiTenantExamChapterTree({
            library_uid: route.query.library_uid
        })
        console.log('章节数据响应:', res)

        // 兼容两种数据结构：{ lists: [...] } 或 { data: { lists: [...] } }
        const lists = res?.lists || res?.data?.lists || []
        console.log('章节列表数据:', lists)

        // 添加顶级菜单
        const menu: any = { uid: 0, title: '顶级', children: lists }
        parentList.value = [menu]
        console.log('父级菜单数据:', parentList.value)
    } catch (error) {
        console.error('获取章节数据失败:', error)
        parentList.value = []
    }
}
// 关闭回调
const handleClose = () => {
    emit('close')
}

fetchParentList()

defineExpose({
    open,
    setFormData,
    getDetail
})
</script>
