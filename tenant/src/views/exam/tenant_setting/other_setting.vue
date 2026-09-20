<template>
    <div>
        <el-card class="!border-none" shadow="never">
            <el-page-header content="前端设置" @back="$router.back()" />
        </el-card>
        <el-card class="!border-none mt-4" shadow="never">
            <el-tabs v-model="activeIndex" @tab-change="handleSelect">
                <el-tab-pane label="基础设置" name="1"></el-tab-pane>
                <el-tab-pane label="会员设置" name="4"></el-tab-pane>
            </el-tabs>
        </el-card>

        <el-card class="!border-none mt-4" shadow="never" v-loading="loading">
            <!-- 基础设置 -->
            <template v-if="activeIndex === '1'">
                <el-form ref="formRef" :model="formData" label-width="120px" :rules="formRules">
                    <el-divider content-position="left">
                        <span class="text-base font-semibold">客服配置</span>
                    </el-divider>

                    <!-- 上传客服图片 -->
                    <el-form-item label="客服二维码">
                        <div>
                            <material-picker v-model="formData.customer_qrcode" :limit="1" />
                        </div>
                    </el-form-item>
                    <el-form-item>
                        <div>
                            <div class="form-tips">
                                建议尺寸：178 x 178 像素，支持 JPG/PNG 格式，不超过 2MB
                            </div>
                        </div>
                    </el-form-item>
                    <el-form-item label="客服信息" prop="customer_name">
                        <div class="grid-layout">
                            <el-input
                                v-model="formData.customer_name"
                                placeholder="请输入客服姓名"
                                clearable
                            >
                                <template #prepend>客服姓名</template>
                            </el-input>
                            <el-input
                                v-model="formData.customer_wechat"
                                placeholder="请输入客服微信号"
                                clearable
                            >
                                <template #prepend>微信号</template>
                            </el-input>
                            <el-input
                                v-model="formData.customer_company"
                                placeholder="请输入所在公司"
                                clearable
                            >
                                <template #prepend>所在公司</template>
                            </el-input>
                            <el-input
                                v-model="formData.customer_position"
                                placeholder="请输入客服职位"
                                clearable
                            >
                                <template #prepend>客服职位</template>
                            </el-input>
                            <el-input
                                v-model="formData.customer_mobile"
                                placeholder="请输入客服手机号"
                                clearable
                            >
                                <template #prepend>手机号</template>
                            </el-input>
                        </div>
                    </el-form-item>

                    <el-divider content-position="left">
                        <span class="text-base font-semibold">平台配置</span>
                    </el-divider>

                    <!-- 版权信息 -->
                    <el-form-item label="版权信息" prop="copyright">
                        <div style="width: 100%">
                            <el-input
                                v-model="formData.copyright"
                                style="width: 480px"
                                :rows="2"
                                type="textarea"
                                placeholder="请输入版权信息"
                                clearable
                            />
                            <div class="form-tips">
                                版权信息会显示在考试平台的底部，如：Copyright © 2025 考试平台
                            </div>
                        </div>
                    </el-form-item>

                    <!-- 小程序订阅模板 -->
                    <el-form-item label="订阅模板ID" prop="template_id">
                        <div style="width: 100%">
                            <!-- 操作按钮区 -->
                            <div class="flex justify-between items-center mb-4">
                                <div class="form-tips">
                                    <el-alert type="info" :closable="false" show-icon>
                                        <template #title>
                                            <div class="flex items-center gap-2">
                                                <span
                                                    >温馨提示：获取前请先确认您已获得订阅消息的使用权限，并且订阅消息中没有任何数据。获取后请不要到小程序后台删除相应的订阅消息，否则会影响订阅消息正常使用。</span
                                                >
                                                <el-button
                                                    type="primary"
                                                    link
                                                    @click="showPreview = true"
                                                >
                                                    查看订阅消息示例
                                                </el-button>
                                            </div>
                                        </template>
                                    </el-alert>
                                </div>
                                <el-button type="primary" @click="openAddTemplateDialog">
                                    <el-icon><Plus /></el-icon>
                                    添加模板
                                </el-button>
                            </div>

                            <!-- 模板列表 -->
                            <el-table
                                :data="formData.template_id"
                                style="width: 100%"
                                border
                                stripe
                                empty-text="暂无模板ID，点击'添加模板'按钮添加"
                            >
                                <el-table-column prop="name" label="模板名称" min-width="150" />
                                <el-table-column
                                    prop="template_id"
                                    label="模板ID"
                                    min-width="300"
                                />
                                <el-table-column prop="type" label="模板类型" min-width="100" />
                                <el-table-column prop="is_default" label="是否默认" min-width="100">
                                    <template #default="scope">
                                        <el-switch
                                            v-model="scope.row.is_default"
                                            @change="setDefaultTemplate(scope.$index)"
                                            active-text="是"
                                            inactive-text="否"
                                        ></el-switch>
                                    </template>
                                </el-table-column>
                                <el-table-column label="操作" min-width="150" fixed="right">
                                    <template #default="scope">
                                        <el-button
                                            type="primary"
                                            text
                                            size="small"
                                            @click="openEditTemplateDialog(scope.$index)"
                                        >
                                            编辑
                                        </el-button>
                                        <el-button
                                            type="danger"
                                            text
                                            size="small"
                                            @click="deleteTemplate(scope.$index)"
                                        >
                                            删除
                                        </el-button>
                                    </template>
                                </el-table-column>
                            </el-table>

                            <!-- 预览图片 -->
                            <el-image-viewer
                                v-if="showPreview"
                                :url-list="srcList"
                                :initial-index="0"
                                @close="showPreview = false"
                            />
                        </div>
                    </el-form-item>

                    <!-- 模板编辑对话框 -->
                    <el-dialog
                        v-model="templateDialogVisible"
                        :title="isEditMode ? '编辑模板' : '添加模板'"
                        width="500px"
                        destroy-on-close
                    >
                        <el-form
                            ref="templateFormRef"
                            :model="templateFormData"
                            :rules="templateFormRules"
                            label-width="100px"
                        >
                            <el-form-item label="模板名称" prop="name">
                                <el-input
                                    v-model="templateFormData.name"
                                    placeholder="请输入模板名称"
                                />
                            </el-form-item>
                            <el-form-item label="模板ID" prop="template_id">
                                <el-input
                                    v-model="templateFormData.template_id"
                                    placeholder="请输入小程序订阅模板ID"
                                />
                            </el-form-item>
                            <el-form-item label="模板类型" prop="type">
                                <el-input
                                    v-model="templateFormData.type"
                                    placeholder="如：倒计时提醒、考试提醒等"
                                />
                            </el-form-item>
                            <el-form-item label="设为默认">
                                <el-switch v-model="templateFormData.is_default" />
                            </el-form-item>
                        </el-form>
                        <template #footer>
                            <span class="dialog-footer">
                                <el-button @click="templateDialogVisible = false">取消</el-button>
                                <el-button type="primary" @click="saveTemplate">确定</el-button>
                            </span>
                        </template>
                    </el-dialog>

                    <el-divider content-position="left">
                        <span class="text-base font-semibold">功能开关</span>
                    </el-divider>

                    <el-form-item label="免广告设置" prop="adConfig">
                        <div style="width: 100%">
                            <el-radio-group v-model="formData.adConfig">
                                <el-radio label="1">仅会员免广告</el-radio>
                                <el-radio label="2">全部用户免广告</el-radio>
                            </el-radio-group>
                            <div class="form-tips mt-2">
                                <el-icon><InfoFilled /></el-icon>
                                选择免广告用户组：仅开通会员的用户免广告或全部用户免广告
                            </div>
                        </div>
                    </el-form-item>

                    <el-form-item label="首页重定向" prop="homeRedirect">
                        <div style="width: 100%">
                            <el-radio-group v-model="formData.homeRedirect">
                                <el-radio label="1">开启</el-radio>
                                <el-radio label="2">关闭</el-radio>
                            </el-radio-group>
                            <div class="form-tips mt-2">
                                <el-icon><InfoFilled /></el-icon>
                                首页重定向：用于微信审核期间将首页重定向到其他页面
                            </div>
                        </div>
                    </el-form-item>
                </el-form>
            </template>

            <!-- 会员设置 -->
            <template v-if="activeIndex === '4'">
                <el-form
                    ref="memberFormRef"
                    :model="memberFormData"
                    label-width="120px"
                    :rules="memberFormRules"
                >
                    <el-divider content-position="left">
                        <span class="text-base font-semibold">激活方式</span>
                    </el-divider>

                    <el-form-item label="会员激活方式" prop="activation_type">
                        <div style="width: 100%">
                            <el-checkbox-group
                                v-model="memberFormData.activation_type"
                                @change="handleActivationTypeChange"
                            >
                                <el-checkbox label="1" string-value>
                                    <span class="flex items-center gap-1">
                                        <el-tag type="success" size="small">积分</el-tag>
                                        积分兑换模式
                                    </span>
                                </el-checkbox>
                                <el-checkbox label="2" string-value>
                                    <span class="flex items-center gap-1">
                                        <el-tag type="warning" size="small">激活码</el-tag>
                                        激活码验证模式
                                    </span>
                                </el-checkbox>
                                <el-checkbox label="3" string-value>
                                    <span class="flex items-center gap-1">
                                        <el-tag type="danger" size="small">支付</el-tag>
                                        支付金额开通模式
                                    </span>
                                </el-checkbox>
                            </el-checkbox-group>
                            <div class="form-tips mt-2">
                                <el-icon><InfoFilled /></el-icon>
                                提示：可同时选择多种激活方式，用户将根据可用方式进行会员激活
                            </div>
                        </div>
                    </el-form-item>

                    <el-divider content-position="left">
                        <span class="text-base font-semibold">套餐管理</span>
                    </el-divider>

                    <el-form-item label="会员套餐设置">
                        <el-card
                            v-for="(pkg, index) in memberFormData.packages"
                            :key="index"
                            class="mb-4"
                            shadow="hover"
                        >
                            <template #header>
                                <div class="card-header">
                                    <div class="flex items-center gap-2">
                                        <el-tag
                                            :type="pkg.is_enabled ? 'success' : 'info'"
                                            size="small"
                                        >
                                            {{ pkg.is_enabled ? '已启用' : '已禁用' }}
                                        </el-tag>
                                        <span class="font-semibold">{{ pkg.name }}套餐</span>
                                    </div>
                                    <el-button
                                        type="danger"
                                        text
                                        @click="removePackage(index)"
                                        v-if="memberFormData.packages.length > 1"
                                    >
                                        删除
                                    </el-button>
                                </div>
                            </template>
                            <div class="package-form">
                                <el-row :gutter="16" class="mb-3">
                                    <el-col :span="24">
                                        <el-input
                                            v-model="pkg.name"
                                            placeholder="套餐名称（如：月度会员）"
                                        >
                                            <template #prepend>套餐名称</template>
                                        </el-input>
                                    </el-col>
                                </el-row>
                                <el-row :gutter="16" class="mb-3">
                                    <el-col :span="12">
                                        <el-input-number
                                            v-model="pkg.duration"
                                            :min="1"
                                            placeholder="时长"
                                            class="w-full"
                                            controls-position="right"
                                        />
                                    </el-col>
                                    <el-col :span="12">
                                        <el-select
                                            v-model="pkg.duration_unit"
                                            placeholder="时长单位"
                                            class="w-full"
                                        >
                                            <el-option label="天" value="day"></el-option>
                                            <el-option label="月" value="month"></el-option>
                                            <el-option label="年" value="year"></el-option>
                                        </el-select>
                                    </el-col>
                                </el-row>
                                <el-row :gutter="16" class="mb-3">
                                    <el-col :span="8">
                                        <el-input-number
                                            v-model="pkg.price"
                                            :min="0"
                                            :precision="2"
                                            placeholder="现价"
                                            class="w-full"
                                            controls-position="right"
                                        >
                                            <template #prepend>¥</template>
                                        </el-input-number>
                                    </el-col>
                                    <el-col :span="8">
                                        <el-input v-model="pkg.old_price" placeholder="原价">
                                            <template #prepend>¥</template>
                                        </el-input>
                                    </el-col>
                                    <el-col :span="8">
                                        <el-input-number
                                            v-model="pkg.integral_required"
                                            :min="0"
                                            placeholder="积分"
                                            class="w-full"
                                            controls-position="right"
                                        >
                                            <template #append>积分</template>
                                        </el-input-number>
                                    </el-col>
                                </el-row>
                                <el-row>
                                    <el-col :span="24">
                                        <el-switch
                                            v-model="pkg.is_enabled"
                                            active-text="启用此套餐"
                                            inactive-text="禁用此套餐"
                                            inline-prompt
                                        ></el-switch>
                                    </el-col>
                                </el-row>
                            </div>
                        </el-card>
                        <el-button type="primary" @click="addPackage" :icon="Plus">
                            添加新套餐
                        </el-button>
                    </el-form-item>

                    <el-divider content-position="left">
                        <span class="text-base font-semibold">权限配置</span>
                    </el-divider>

                    <el-form-item label="会员权限设置">
                        <el-table
                            :data="memberFormData.privileges"
                            style="width: 100%"
                            class="mb-4"
                            stripe
                            border
                        >
                            <el-table-column label="权限名称" width="200" align="center">
                                <template #default="integral">
                                    <el-input
                                        v-model="integral.row.name"
                                        placeholder="请输入权限名称"
                                    ></el-input>
                                </template>
                            </el-table-column>
                            <el-table-column label="普通用户" align="center">
                                <template #default="integral">
                                    <el-input
                                        v-model="integral.row.normal_value"
                                        placeholder="普通用户权限值"
                                    ></el-input>
                                </template>
                            </el-table-column>
                            <el-table-column label="VIP用户" align="center">
                                <template #default="integral">
                                    <el-input
                                        v-model="integral.row.vip_value"
                                        placeholder="VIP用户权限值"
                                    ></el-input>
                                </template>
                            </el-table-column>
                            <el-table-column label="操作" width="100" align="center" fixed="right">
                                <template #default="integral">
                                    <el-button
                                        type="danger"
                                        text
                                        @click="removePrivilege(integral.$index)"
                                        >删除</el-button
                                    >
                                </template>
                            </el-table-column>
                        </el-table>
                        <el-button type="primary" @click="addPrivilege" :icon="Plus">
                            添加新权限
                        </el-button>
                    </el-form-item>
                </el-form>
            </template>

            <footer-btns v-perms="['exam.tenant_other_settings/apiTenantOtherSettingsEdit']">
                <el-button type="primary" @click="handleSubmit">保存</el-button>
            </footer-btns>
        </el-card>
    </div>
</template>

<script lang="ts" setup name="tenantOtherSettingsEdit">
import { InfoFilled, Plus } from '@element-plus/icons-vue'
import type { CheckboxValueType, FormInstance, UploadFile, UploadFiles } from 'element-plus'
import { ElLoading, ElMessage, ElMessageBox } from 'element-plus'
import type { PropType } from 'vue'
import { reactive, ref, shallowRef } from 'vue'

import {
    apiTenantOtherSettingsDetail,
    apiTenantOtherSettingsEdit
} from '@/api/exam/tenant_other_settings'
import FooterBtns from '@/components/footer-btns/index.vue'
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

const srcList = ['../../src/assets/images/reply_form_tpl.png']

const showPreview = ref(false)
const loading = ref(false)

// 定义模板ID类型
interface TemplateItem {
    template_id: string
    name: string
    type: string
    is_default: boolean
}

// 表单数据
const formData = reactive({
    id: '',
    customer_qrcode: '',
    customer_wechat: '',
    copyright: '',
    customer_name: '',
    customer_company: '',
    customer_position: '',
    customer_mobile: '',
    template_id: [] as TemplateItem[], // 改为对象数组，支持多模板ID管理
    adConfig: '1', // 免广告配置：1-仅会员，2-全部用户（字符串类型以匹配el-radio的label）
    homeRedirect: '2',
    member_settings: {}
})

// 模板管理相关变量
const templateDialogVisible = ref(false)
const templateFormRef = ref<FormInstance>()
const currentTemplateIndex = ref(-1)
const isEditMode = ref(false)

// 模板表单数据
const templateFormData = reactive({
    template_id: '',
    name: '',
    type: '',
    is_default: false
})

// 模板表单验证规则
const templateFormRules = reactive<any>({
    template_id: [
        { required: true, message: '请输入模板ID', trigger: 'blur' },
        { pattern: /^[a-zA-Z0-9_-]{32,}$/, message: '模板ID格式不正确', trigger: 'blur' },
        {
            validator: (rule: any, value: string, callback: Function) => {
                if (!value) return callback()
                // 检查模板ID是否重复
                const isDuplicate = formData.template_id.some((item: any, index: number) => {
                    return item.template_id === value && index !== currentTemplateIndex.value
                })
                if (isDuplicate) {
                    callback(new Error('模板ID已存在'))
                } else {
                    callback()
                }
            },
            trigger: 'blur'
        }
    ],
    name: [
        { required: true, message: '请输入模板名称', trigger: 'blur' },
        { min: 1, max: 20, message: '模板名称长度在 1 到 20 个字符', trigger: 'blur' }
    ],
    type: [
        { required: true, message: '请输入模板类型', trigger: 'blur' },
        { min: 1, max: 20, message: '模板类型长度在 1 到 20 个字符', trigger: 'blur' }
    ]
})

// 表单验证
const formRules = reactive<any>({})

const activeIndex = ref('1')

const handleSelect = (key: string | number) => {
    activeIndex.value = String(key)
}

// 打开添加模板对话框
const openAddTemplateDialog = () => {
    isEditMode.value = false
    currentTemplateIndex.value = -1
    // 重置表单数据
    templateFormData.template_id = ''
    templateFormData.name = ''
    templateFormData.type = ''
    templateFormData.is_default = false
    // 打开对话框
    templateDialogVisible.value = true
}

// 打开编辑模板对话框
const openEditTemplateDialog = (index: number) => {
    isEditMode.value = true
    currentTemplateIndex.value = index
    // 填充表单数据
    const template = formData.template_id[index]
    templateFormData.template_id = template.template_id
    templateFormData.name = template.name
    templateFormData.type = template.type
    templateFormData.is_default = template.is_default
    // 打开对话框
    templateDialogVisible.value = true
}

// 保存模板
const saveTemplate = async () => {
    if (!templateFormRef.value) return

    try {
        await templateFormRef.value.validate()

        const templateData = {
            template_id: templateFormData.template_id,
            name: templateFormData.name,
            type: templateFormData.type,
            is_default: templateFormData.is_default
        }

        if (isEditMode.value && currentTemplateIndex.value >= 0) {
            // 编辑模式：更新现有模板
            formData.template_id[currentTemplateIndex.value] = templateData
        } else {
            // 添加模式：添加新模板
            formData.template_id.push(templateData)
        }

        // 如果设置为默认模板，将其他模板设为非默认
        if (templateData.is_default) {
            setDefaultTemplate(
                isEditMode.value ? currentTemplateIndex.value : formData.template_id.length - 1
            )
        }

        // 关闭对话框
        templateDialogVisible.value = false
        ElMessage.success(isEditMode.value ? '模板更新成功' : '模板添加成功')
    } catch (error) {
        console.error('保存模板失败:', error)
    }
}

// 设置默认模板
const setDefaultTemplate = (index: number) => {
    // 将所有模板设为非默认
    formData.template_id.forEach((item: any) => {
        item.is_default = false
    })
    // 将当前模板设为默认
    if (formData.template_id[index]) {
        formData.template_id[index].is_default = true
    }
}

// 删除模板
const deleteTemplate = (index: number) => {
    ElMessageBox.confirm('确定要删除此模板吗？', '删除确认', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
    })
        .then(() => {
            formData.template_id.splice(index, 1)
            ElMessage.success('模板删除成功')
        })
        .catch(() => {
            // 取消删除
        })
}

// 会员设置表单数据
const memberFormRef = ref<FormInstance>()
const memberFormData = reactive({
    // 激活方式：1-积分兑换，2-激活码，3-支付，数组支持多选
    activation_type: ['1'],
    // 会员套餐
    packages: [
        {
            name: '月度',
            duration: 1,
            duration_unit: 'month',
            price: 10,
            old_price: 40,
            integral_required: 1000,
            is_enabled: true
        },
        {
            name: '季度',
            duration: 3,
            duration_unit: 'month',
            price: 32,
            old_price: 99,
            integral_required: 3000,
            is_enabled: true
        },
        {
            name: '年度',
            duration: 12,
            duration_unit: 'month',
            price: 99,
            old_price: 198,
            integral_required: 10000,
            is_enabled: true
        }
    ],
    // 会员权限
    privileges: [
        {
            name: '常规题库',
            normal_value: '1',
            vip_value: '1'
        },
        {
            name: '精品题库',
            normal_value: '0',
            vip_value: '1'
        },
        {
            name: '题库折扣',
            normal_value: '0',
            vip_value: '1'
        },
        {
            name: '常规资源',
            normal_value: '1',
            vip_value: '1'
        },
        {
            name: '精品资源',
            normal_value: '0',
            vip_value: '1'
        },
        {
            name: '广告限制',
            normal_value: '0',
            vip_value: '1'
        },
        {
            name: '存储空间',
            normal_value: '不限',
            vip_value: '不限'
        },
        {
            name: '答题历史',
            normal_value: '最近一个月',
            vip_value: '永久'
        }
    ],
    // 激活码设置
    activation_code_length: 16,
    activation_code_prefix: 'VIP',
    // 积分兑换设置
    integral_exchange_enabled: true,
    // 支付设置
    payment_enabled: true,
    payment_method: 'wechat'
})

// 会员设置表单验证
const memberFormRules = reactive<any>({
    activation_type: [
        { required: true, message: '请选择至少一种会员激活方式', trigger: 'change' },
        { type: 'array', min: 1, message: '请选择至少一种会员激活方式', trigger: 'change' }
    ]
})

// 处理激活方式变更
const handleActivationTypeChange = (value: CheckboxValueType[]) => {
    console.log('激活方式变更为:', value)
}

// 添加会员套餐
const addPackage = () => {
    memberFormData.packages.push({
        name: '新套餐',
        duration: 1,
        duration_unit: 'month',
        price: 0,
        old_price: 0,
        integral_required: 0,
        is_enabled: true
    })
}

// 删除会员套餐
const removePackage = (index: number) => {
    if (memberFormData.packages.length > 1) {
        memberFormData.packages.splice(index, 1)
    }
}

// 添加会员权限
const addPrivilege = () => {
    memberFormData.privileges.push({
        name: '新权限',
        normal_value: '0',
        vip_value: '1'
    })
}

// 删除会员权限
const removePrivilege = (index: number) => {
    memberFormData.privileges.splice(index, 1)
}

// 获取详情
const setFormData = (data: Record<any, any>) => {
    for (const key in formData) {
        if (data[key] != null && data[key] != undefined) {
            if (key === 'template_id') {
                // 特殊处理template_id，确保为对象数组格式
                let templateData = data[key]
                try {
                    if (typeof templateData === 'string') {
                        // 如果是字符串，解析为JSON
                        templateData = JSON.parse(templateData)
                    }

                    if (Array.isArray(templateData)) {
                        // 如果是数组，直接使用
                        formData.template_id = templateData
                    } else if (templateData && typeof templateData === 'object') {
                        // 如果是单个对象，转换为数组
                        formData.template_id = [templateData]
                    } else if (templateData) {
                        // 如果是字符串ID，转换为对象数组
                        formData.template_id = [
                            {
                                template_id: templateData,
                                name: '',
                                type: '',
                                is_default: false
                            }
                        ]
                    } else {
                        // 其他情况，设为空数组
                        formData.template_id = []
                    }
                } catch (error) {
                    console.error('解析模板ID数据失败:', error)
                    // 解析失败时，设为空数组
                    formData.template_id = []
                }
            } else {
                //@ts-ignore
                formData[key] = data[key]
            }
        }
    }

    // 处理会员设置数据，将JSON字符串解析为对象并赋值给memberFormData
    if (data.member_settings) {
        try {
            // 如果是字符串，解析为对象；如果已经是对象，直接使用
            const memberSettings =
                typeof data.member_settings === 'string'
                    ? JSON.parse(data.member_settings)
                    : data.member_settings

            // 将memberSettings中的属性赋值给memberFormData
            Object.assign(memberFormData, memberSettings)
        } catch (error) {
            console.error('解析会员设置数据失败:', error)
        }
    }

    // 确保adConfig是字符串类型
    if (data.adConfig !== undefined && data.adConfig !== null) {
        formData.adConfig = String(data.adConfig)
    }
    if (data.homeRedirect !== undefined && data.homeRedirect !== null) {
        formData.homeRedirect = String(data.homeRedirect)
    }
}

const getDetail = async () => {
    loading.value = true
    try {
        const data = await apiTenantOtherSettingsDetail({})
        setFormData(data)
    } finally {
        loading.value = false
    }
}

// 处理子组件成功事件
const handleSuccess = () => {
    emit('success')
    // 可以根据需要添加其他处理逻辑
}

// 提交按钮
const handleSubmit = async () => {
    loading.value = true
    try {
        if (activeIndex.value === '4') {
            // 提交会员设置
            await memberFormRef.value?.validate()
            // 将会员设置数据作为member_settings字段添加到formData中
            // 转换为JSON字符串以防止后端多次编码
            const submitData = {
                ...formData,
                member_settings: JSON.stringify(memberFormData),
                template_id: JSON.stringify(formData.template_id) // 将模板ID数组转换为JSON字符串
            } as any
            // 调用apiTenantOtherSettingsEdit提交包含会员设置的数据
            console.log('提交会员设置:', submitData)
            await apiTenantOtherSettingsEdit(submitData)
            ElMessage.success('会员设置保存成功')
            emit('success')
        } else {
            // 提交基础设置
            await formRef.value?.validate()
            // 创建新的提交数据对象，避免类型冲突
            const data = {
                ...formData,
                // 确保adConfig是字符串类型
                adConfig: String(formData.adConfig),
                homeRedirect: String(formData.homeRedirect),
                // 将模板ID数组转换为JSON字符串
                template_id: JSON.stringify(formData.template_id)
            } as any
            mode.value = await apiTenantOtherSettingsEdit(data)
            popupRef.value?.close()
            emit('success')
            ElMessage.success('基础设置保存成功')
            getDetail()
        }
    } catch (error) {
        console.error('保存失败:', error)
    } finally {
        loading.value = false
    }
}

getDetail()

defineExpose({
    setFormData,
    getDetail
})
</script>

<style scoped lang="scss">
/* 基础布局 */
.grid-layout {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    width: 100%;

    .el-input {
        flex: 1;
        min-width: 280px;
    }
}

/* 表单提示样式 */
.form-tips {
    font-size: 13px;
    color: var(--el-text-color-secondary);
    line-height: 1.6;
    display: flex;
    align-items: center;
    gap: 6px;

    .el-icon {
        font-size: 14px;
    }
}

/* 头像上传样式 */
.avatar-uploader {
    :deep(.el-upload) {
        border: 2px dashed var(--el-border-color);
        border-radius: 8px;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        transition: var(--el-transition-duration-fast);

        &:hover {
            border-color: var(--el-color-primary);
        }
    }
}

.avatar-uploader-icon {
    font-size: 32px;
    color: var(--el-text-color-placeholder);
    width: 178px;
    height: 178px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.avatar {
    width: 178px;
    height: 178px;
    display: block;
    object-fit: cover;
}

/* 会员套餐样式 */
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;

    .flex {
        display: flex;
        align-items: center;
    }
}

.package-form {
    padding: 16px 0;

    .el-row {
        margin-bottom: 0;
    }

    .mb-3 {
        margin-bottom: 16px;
    }
}

/* 分割线样式 */
:deep(.el-divider) {
    margin: 32px 0 24px;

    .el-divider__text {
        padding: 0 16px;
        background-color: var(--el-bg-color);
    }
}

/* Tabs 样式 */
:deep(.el-tabs) {
    .el-tabs__nav-wrap {
        padding: 0;
    }
}

/* 工具类 */
.flex {
    display: flex;
}

.items-center {
    align-items: center;
}

.justify-between {
    justify-content: space-between;
}

.gap-1 {
    gap: 4px;
}

.gap-2 {
    gap: 8px;
}

/* 模板管理样式 */
.mb-4 {
    margin-bottom: 16px;
}

/* 表格样式优化 */
:deep(.el-table) {
    .el-table__header-wrapper {
        .el-table__header {
            th {
                background-color: var(--el-color-primary-light-9);
                font-weight: 600;
            }
        }
    }

    .el-table__body-wrapper {
        .el-table__body {
            td {
                padding: 12px 0;
            }
        }
    }

    .el-switch {
        margin: 0;
    }
}

/* 对话框样式优化 */
:deep(.el-dialog) {
    .el-dialog__header {
        border-bottom: 1px solid var(--el-border-color);
        padding: 20px 24px;
    }

    .el-dialog__title {
        font-size: 16px;
        font-weight: 600;
    }

    .el-dialog__body {
        padding: 24px;
    }

    .el-dialog__footer {
        border-top: 1px solid var(--el-border-color);
        padding: 16px 24px;
    }
}

/* 表单样式优化 */
:deep(.el-form) {
    .el-form-item {
        margin-bottom: 20px;
    }

    .el-form-item__label {
        font-weight: 500;
    }
}

/* 按钮样式优化 */
:deep(.el-button) {
    margin-left: 8px;

    &:first-child {
        margin-left: 0;
    }
}

.w-full {
    width: 100%;
}

.text-base {
    font-size: 14px;
}

.font-semibold {
    font-weight: 600;
}

.mt-2 {
    margin-top: 8px;
}

.mt-4 {
    margin-top: 16px;
}

.mb-3 {
    margin-bottom: 12px;
}

.mb-4 {
    margin-bottom: 16px;
}

/* 响应式设计 */
@media (max-width: 768px) {
    .grid-layout {
        grid-template-columns: 1fr;
    }

    :deep(.el-form-item__label) {
        text-align: left !important;
    }

    .package-form {
        :deep(.el-col) {
            margin-bottom: 12px;
        }
    }
}
</style>
