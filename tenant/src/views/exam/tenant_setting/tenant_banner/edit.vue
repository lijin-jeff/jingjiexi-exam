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
                <el-form-item label="标题" prop="title">
                    <el-input v-model="formData.title" clearable placeholder="请输入标题" />
                </el-form-item>
                <el-form-item label="类型" prop="image_type">
                    <el-select
                        class="flex-1"
                        v-model="formData.image_type"
                        clearable
                        placeholder="请选择类型"
                    >
                        <el-option
                            v-for="(item, index) in dictData.image_type"
                            :key="index"
                            :label="item.name"
                            :value="item.value"
                        />
                    </el-select>
                </el-form-item>
                <el-form-item label="位置" prop="position">
                    <el-select
                        class="flex-1"
                        v-model="formData.position"
                        clearable
                        placeholder="请选择位置"
                    >
                        <el-option
                            v-for="(item, index) in dictData.image_position"
                            :key="index"
                            :label="item.name"
                            :value="item.value"
                        />
                    </el-select>
                </el-form-item>

                <!-- home_tip 位置特有配置 -->
                <template v-if="formData.position === 'home_tip'">
                    <el-form-item label="关闭按钮位置" prop="close_position">
                        <el-radio-group v-model="formData.close_position">
                            <el-radio :value="1">右上</el-radio>
                            <el-radio :value="2">左上</el-radio>
                            <el-radio :value="3">底部</el-radio>
                        </el-radio-group>
                    </el-form-item>

                    <el-form-item label="展示方式" prop="display_mode">
                        <el-radio-group v-model="formData.display_mode">
                            <el-radio :value="1">不自动展示</el-radio>
                            <el-radio :value="2">用户每次打开页面展示</el-radio>
                            <el-radio :value="3">用户每天首次打开页面展示</el-radio>
                        </el-radio-group>
                    </el-form-item>
                </template>

                <el-form-item label="平台" prop="client">
                    <!-- 复选框 -->
                    <el-checkbox-group v-model="formData.client" class="flex-1">
                        <el-checkbox
                            v-for="(item, index) in dictData.client"
                            :key="index"
                            :value="item.value"
                            :label="item.name"
                        />
                    </el-checkbox-group>
                </el-form-item>
                <el-form-item
                    v-if="formData.image_type == 'image_banner'"
                    label="图片"
                    prop="image"
                >
                    <material-picker v-model="formData.image" />
                </el-form-item>
                <el-form-item v-if="formData.image_type == 'image_menu'" label="icon" prop="icon">
                    <div style="width: 100%">
                        <el-input v-model="formData.icon" clearable placeholder="请输入icon" />
                        <div class="form-tips">
                            icon为图标名称，格式为：[图标名称]，在
                            <a
                                href="https://vue2.tuniaokj.com/components/icon.html"
                                target="_blank"
                                style="color: aqua"
                                >(图鸟官方图标库)</a
                            >中搜索直接复制
                        </div>
                    </div>
                </el-form-item>
                <el-form-item label="链接" prop="url">
                    <link-picker
                        v-model="formData.url"
                        :clearable="true"
                        placeholder="请输入链接"
                    />
                </el-form-item>
                <el-form-item label="排序" prop="sort">
                    <el-input v-model="formData.sort" clearable placeholder="请输入排序" />
                </el-form-item>
                <el-form-item label="是否显示" prop="is_show">
                    <el-radio-group v-model="formData.is_show" placeholder="请选择">
                        <el-radio
                            v-for="(item, index) in dictData.show_status"
                            :key="index"
                            :value="parseInt(item.value)"
                        >
                            {{ item.name }}
                        </el-radio>
                    </el-radio-group>
                </el-form-item>
            </el-form>
        </popup>
    </div>
</template>

<script lang="ts" setup name="tenantBannerEdit">
import type { FormInstance } from 'element-plus'

import {
    apiTenantBannerAdd,
    apiTenantBannerDetail,
    apiTenantBannerEdit
} from '@/api/exam/tenant_setting_banner'
import Popup from '@/components/popup/index.vue'
import { useDictData } from '@/hooks/useDictOptions'

// 获取字典数据
const { dictData } = useDictData('show_status,image_type,image_position,client')

const emit = defineEmits(['success', 'close'])
const formRef = shallowRef<FormInstance>()
const popupRef = shallowRef<InstanceType<typeof Popup>>()
const mode = ref('add')

// 弹窗标题
const popupTitle = computed(() => {
    return mode.value == 'edit' ? '编辑' : '新增'
})

// 表单数据
const formData = reactive({
    id: '',
    title: '',
    is_show: 1,
    sort: 0,
    position: '',
    client: [],
    image: '',
    image_type: '',
    url: {},
    icon: '',
    close_position: 1, // 关闭按钮位置：1-右上，2-左上，3-底部
    display_mode: 3 // 展示方式：1-不自动展示，2-每次打开，3-每天首次
})

// 表单验证
const formRules = reactive<any>({
    uid: [
        {
            required: true,
            message: '请输入',
            trigger: ['blur']
        }
    ],
    title: [
        {
            required: true,
            message: '请输入',
            trigger: ['blur']
        }
    ],
    is_show: [
        {
            required: true,
            message: '请选择',
            trigger: ['blur']
        }
    ],
    sort: [
        {
            required: true,
            message: '请输入',
            trigger: ['blur']
        }
    ],
    position: [
        {
            required: true,
            message: '请选择位置',
            trigger: ['blur']
        }
    ],
    client: [
        {
            required: true,
            message: '请选择平台',
            trigger: ['blur']
        }
    ],
    image_type: [
        {
            required: true,
            message: '请选择类型',
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
            if (key == 'client') {
                formData[key] = data[key].split(',')
            }
            if (key == 'url') {
                // 数组 /pages/search/search,搜索,shop
                // "path": "/pages/agreement/agreement",
                // "name": "服务协议",
                // "query": {
                //     "type": "service"
                // },
                // "type": "shop"

                formData[key] = JSON.parse(data[key])
            }
            // 确保close_position和display_mode为数字类型
            if (key === 'close_position' || key === 'display_mode') {
                //@ts-ignore
                formData[key] = Number(data[key])
            }
        }
    }
    console.log('formData after setFormData:', formData)
}

const getDetail = async (row: Record<string, any>) => {
    const data = await apiTenantBannerDetail({
        id: row.id
    })
    setFormData(data)
}

// 提交按钮
const handleSubmit = async () => {
    await formRef.value?.validate()
    const data = { ...formData }

    mode.value == 'edit' ? await apiTenantBannerEdit(data) : await apiTenantBannerAdd(data)
    popupRef.value?.close()
    emit('success')
}

//打开弹窗
const open = (type = 'add', row?: Record<string, any>) => {
    mode.value = type
    if (type === 'edit' && row) {
        // 编辑模式下，获取详情数据
        getDetail(row)
    }
    popupRef.value?.open()
}

// 关闭回调
const handleClose = () => {
    emit('close')
}
defineExpose({
    open,
    setFormData
})
</script>
