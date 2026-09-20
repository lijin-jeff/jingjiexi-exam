<template>
    <div>
        <el-card class="!border-none mb-4" shadow="never">
            <el-form ref="formRef" :model="formData" label-width="120px" :rules="formRules">
                <el-form-item label="排行榜类型" prop="ranking_type">
                    <!-- 排行榜类型，从后台字典获取 -->
                    <el-checkbox-group v-model="formData.ranking_type">
                        <el-checkbox
                            v-for="(item, index) in dictData.ranking_type"
                            :key="index"
                            :label="item.value"
                        >
                            {{ item.name }}
                        </el-checkbox>
                    </el-checkbox-group>
                </el-form-item>

                <el-form-item label="展示数量" prop="display_top_count">
                    <div style="width: 100%">
                        <el-input-number
                            v-model="formData.display_top_count"
                            clearable
                            placeholder="请输入展示数量"
                            :min="10"
                            :max="100"
                            :step="1"
                            style="width: 200px"
                        />
                    </div>
                    <div class="form-tips">默认为30，只能为10-100的整数。</div>
                </el-form-item>

                <el-form-item label="显示维度" prop="ranking_dimension">
                    <el-radio-group v-model="formData.ranking_dimension">
                        <el-radio
                            v-for="(item, index) in dictData.ranking_dimension"
                            :key="index"
                            :label="item.value"
                        >
                            {{ item.name }}
                        </el-radio>
                    </el-radio-group>
                </el-form-item>

                <el-form-item label="重置时间" prop="reset_time">
                    <div style="margin: 0">
                        <!-- 重置时间控件，根据选择的排行榜类型动态显示 -->
                        <!-- 使用动态绑定减少重复代码 -->
                        <template
                            v-if="
                                formData.ranking_type.includes('day') ||
                                formData.ranking_type.includes('week') ||
                                formData.ranking_type.includes('month') ||
                                formData.ranking_type.includes('total')
                            "
                        >
                            <div class="flex gap-4">
                                <!-- 周榜选择框 -->
                                <div v-if="formData.ranking_type.includes('week')">
                                    <label class="block mb-2 text-sm font-medium"
                                        >周榜重置时间</label
                                    >
                                    <el-select
                                        v-model="formData.reset_time_week"
                                        placeholder="请选择周几"
                                        style="width: 150px"
                                        @change="validateResetTime"
                                    >
                                        <el-option label="周一" value="1" />
                                        <el-option label="周二" value="2" />
                                        <el-option label="周三" value="3" />
                                        <el-option label="周四" value="4" />
                                        <el-option label="周五" value="5" />
                                        <el-option label="周六" value="6" />
                                        <el-option label="周日" value="7" />
                                    </el-select>
                                </div>

                                <!-- 月榜选择框 -->
                                <div v-if="formData.ranking_type.includes('month')">
                                    <label class="block mb-2 text-sm font-medium"
                                        >月榜重置时间</label
                                    >
                                    <el-select
                                        v-model="formData.reset_time_month"
                                        placeholder="请选择日期"
                                        style="width: 150px"
                                        @change="validateResetTime"
                                    >
                                        <el-option
                                            v-for="day in 31"
                                            :key="day"
                                            :label="`${day}日`"
                                            :value="String(day)"
                                        />
                                    </el-select>
                                </div>
                            </div>
                        </template>
                        <template v-else-if="formData.ranking_type.includes('day')">
                            <!-- 只选择了日榜，显示固定时间 -->
                            <el-input
                                v-model="formData.reset_time_day"
                                placeholder="日榜默认每天00:00:00自动重置"
                                style="width: 200px"
                                disabled
                            />
                        </template>
                        <template v-else-if="formData.ranking_type.includes('total')">
                            <!-- 只选择了总榜，显示无需设置 -->
                            <el-input
                                v-model="formData.reset_time_total"
                                placeholder="总榜无需设置"
                                style="width: 200px"
                                disabled
                            />
                        </template>

                        <div class="form-tips mt-2">
                            <!-- 简化表单提示文本 -->
                            <template
                                v-if="
                                    formData.ranking_type.includes('week') &&
                                    formData.ranking_type.includes('month')
                                "
                            >
                                同时设置了周榜和月榜重置时间
                            </template>
                            <template v-else-if="formData.ranking_type.includes('week')">
                                选择周榜重置时间，每周指定天自动重置
                            </template>
                            <template v-else-if="formData.ranking_type.includes('month')">
                                选择月榜重置时间，每月指定日期自动重置
                            </template>
                            <template v-else-if="formData.ranking_type.includes('day')">
                                日榜默认每天00:00:00自动重置
                            </template>
                            <template v-else-if="formData.ranking_type.includes('total')">
                                总榜不重置
                            </template>
                            <template v-else> 请选择排行榜类型 </template>
                        </div>
                    </div>
                </el-form-item>

                <el-form-item label="奖励规则" prop="reward_rule">
                    <el-radio-group v-model="formData.reward_rule" placeholder="请选择奖励规则">
                        <el-radio label="integral" value="integral">积分奖励</el-radio>
                    </el-radio-group>
                </el-form-item>

                <el-form-item label="日榜奖励积分" prop="integral_count_day">
                    <div style="width: 100%">
                        <el-input
                            v-model="formData.integral_count_day"
                            style="width: 400px"
                            :rows="2"
                            type="textarea"
                            placeholder="请输入日榜奖励积分配置"
                        />
                        <div class="form-tips">
                            格式为：1|5,2|3,3|2。表示：第一名奖励5积分，第二名奖励3积分，第三名奖励2积分。
                        </div>
                    </div>
                </el-form-item>

                <el-form-item label="周榜奖励积分" prop="integral_count_week">
                    <div style="width: 100%">
                        <el-input
                            v-model="formData.integral_count_week"
                            style="width: 400px"
                            :rows="2"
                            type="textarea"
                            placeholder="请输入周榜奖励积分配置"
                        />
                        <div class="form-tips">
                            格式为：1|15,2|13,3|12。表示：第一名奖励15积分，第二名奖励13积分，第三名奖励12积分。
                        </div>
                    </div>
                </el-form-item>

                <el-form-item label="月榜奖励积分" prop="integral_count_month">
                    <div style="width: 100%">
                        <el-input
                            v-model="formData.integral_count_month"
                            style="width: 400px"
                            :rows="2"
                            type="textarea"
                            placeholder="请输入月榜奖励积分配置"
                        />
                        <div class="form-tips">
                            格式为：1|30,2|25,3|20。表示：第一名奖励30积分，第二名奖励25积分，第三名奖励20积分。
                        </div>
                    </div>
                </el-form-item>

                <el-form-item label="总榜奖励积分" prop="integral_count_total">
                    <div style="width: 100%">
                        <el-input
                            v-model="formData.integral_count_total"
                            style="width: 400px"
                            :rows="2"
                            type="textarea"
                            placeholder="请输入总榜奖励积分配置"
                        />
                        <div class="form-tips">
                            格式为：1|500,2|300,3|200。表示：第一名奖励500积分，第二名奖励300积分，第三名奖励200积分。
                        </div>
                    </div>
                </el-form-item>

                <el-form-item label="排行榜说明" prop="desc">
                    <div style="width: 100%">
                        <el-input
                            v-model="formData.desc"
                            style="width: 500px"
                            :rows="4"
                            type="textarea"
                            placeholder="请输入排行榜说明"
                        />
                    </div>
                </el-form-item>

                <el-form-item label="是否启用" prop="is_show">
                    <el-radio-group v-model="formData.is_show">
                        <el-radio
                            v-for="(item, index) in dictData.show_status"
                            :key="index"
                            :label="parseInt(item.value)"
                        >
                            {{ item.name }}
                        </el-radio>
                    </el-radio-group>
                </el-form-item>
            </el-form>
            <footer-btns
                v-perms="['exam.tenant_exam_ranking_settings/apiTenantExamRankingSettingsEdit']"
            >
                <el-button type="primary" @click="handleSubmit">保存</el-button>
                <el-button @click="resetForm">重置</el-button>
            </footer-btns>
        </el-card>
    </div>
</template>

<script lang="ts" setup name="tenantExamRankingSettingsEdit">
import type { FormInstance } from 'element-plus'
import { ElMessage } from 'element-plus'
import { reactive, shallowRef, watch } from 'vue'

import {
    apiTenantExamRankingSettingsDetail,
    apiTenantExamRankingSettingsEdit
} from '@/api/exam/tenant_exam_ranking_settings'
import { useDictData } from '@/hooks/useDictOptions'

const formRef = shallowRef<FormInstance>()

// 表单数据
const formData = reactive({
    id: '',
    ranking_type: ['week'], // 改为数组，支持多选
    display_top_count: 30,
    ranking_dimension: 'correct_count', // 改为字符串，单选
    reset_time_day: '00:00:00', // 日榜重置时间
    reset_time_week: '7', // 周榜重置时间
    reset_time_month: '1', // 月榜重置时间
    reset_time_total: '0', // 总榜重置时间
    reward_rule: 'integral',
    integral_count_day: '1|5,2|3,3|2',
    integral_count_week: '1|15,2|13,3|12',
    integral_count_month: '1|30,2|25,3|20',
    integral_count_total: '1|500,2|300,3|200',
    desc: '积分奖励排行榜说明',
    is_show: 1
})

// 表单验证
const formRules = reactive<any>({
    ranking_type: [
        {
            required: true,
            type: 'array',
            min: 1,
            message: '请至少选择一个排行榜类型',
            trigger: ['change']
        }
    ],
    display_top_count: [
        {
            required: true,
            message: '请输入展示数量',
            trigger: ['blur', 'change']
        },
        {
            type: 'number',
            min: 10,
            max: 100,
            message: '展示数量必须在10-100之间',
            trigger: ['blur', 'change']
        }
    ],
    ranking_dimension: [
        {
            required: true,
            message: '请选择显示维度',
            trigger: ['change']
        }
    ],
    reward_rule: [
        {
            required: true,
            message: '请选择奖励规则',
            trigger: ['blur', 'change']
        }
    ],
    is_show: [
        {
            required: true,
            message: '请选择是否启用',
            trigger: ['blur', 'change']
        }
    ]
})
// 验证重置时间
const validateResetTime = () => {
    // 多选模式下，重置时间验证
    // 同时选择了周榜和月榜
    if (formData.ranking_type.includes('week') && formData.ranking_type.includes('month')) {
        // 验证周榜重置时间
        const weekDay = Number(formData.reset_time_week)
        if (isNaN(weekDay) || weekDay < 0 || weekDay > 8) {
            ElMessage.warning('周榜重置时间必须是1-7之间的数字')
            formData.reset_time_week = '7' // 默认周日
            return false
        }

        // 验证月榜重置时间
        const day = Number(formData.reset_time_month)
        if (isNaN(day) || day < 0 || day > 32) {
            ElMessage.warning('月榜重置时间必须是1-31之间的数字')
            formData.reset_time_month = '1' // 默认1日
            return false
        }
    }
    return true
}

// 监听排行榜类型变化，更新重置时间默认值
watch(
    () => formData.ranking_type,
    (newTypes) => {
        // 多选模式下，更新所有相关的重置时间字段

        // 更新周榜重置时间
        if (newTypes.includes('week')) {
            formData.reset_time_week = '7' // 默认周日
        }

        // 更新月榜重置时间
        if (newTypes.includes('month')) {
            formData.reset_time_month = '31' // 默认1日
        }
    }
)

// 获取字典数据
const { dictData } = useDictData('ranking_type,ranking_dimension,reward_rule,show_status')

// 获取详情
const setFormData = async (data: Record<any, any>) => {
    for (const key in formData) {
        if (data[key] != null && data[key] != undefined) {
            if (key === 'ranking_type') {
                // 确保排行榜类型是数组格式
                //@ts-ignore
                formData[key] = Array.isArray(data[key]) ? data[key] : [data[key]]
            } else if (key === 'ranking_dimension') {
                // 显示维度改为单选，确保是字符串
                //@ts-ignore
                formData[key] = Array.isArray(data[key]) ? data[key][0] : data[key]
            } else {
                //@ts-ignore
                formData[key] = data[key]
            }
        }
    }

    // 确保所有重置时间字段是字符串类型
    formData.reset_time_week = formData.reset_time_week ? String(formData.reset_time_week) : '7'
    formData.reset_time_month = formData.reset_time_month ? String(formData.reset_time_month) : '1'

    // 验证重置时间
    validateResetTime()
}

const getDetail = async () => {
    try {
        const data = await apiTenantExamRankingSettingsDetail({})
        setFormData(data)
    } catch (error) {
        console.error('获取排行榜设置详情失败:', error)
    }
}

// 提交按钮
const handleSubmit = async () => {
    try {
        // 验证重置时间
        if (!validateResetTime()) {
            return
        }

        await formRef.value?.validate()

        // 准备提交数据，确保reset_time是字符串类型，适配数据库varchar字段
        const submitData = {
            ...formData
        }

        await apiTenantExamRankingSettingsEdit(submitData)
        await getDetail() // 刷新数据，确保显示最新配置
        ElMessage.success('保存成功')
    } catch (error: any) {
        console.error('保存排行榜设置失败:', error)
        // 处理不同类型的错误
        if (error.message?.includes('Network Error')) {
            ElMessage.error('网络错误，请检查网络连接后重试')
        } else if (error.response?.data?.code) {
            // 处理服务器返回的错误
            const errorMsg = error.response.data.msg || '保存失败，请重试'
            ElMessage.error(errorMsg)
        } else {
            ElMessage.error('保存失败，请重试')
        }
    }
}

// 重置表单
const resetForm = () => {
    formRef.value?.resetFields()
    getDetail()
}

getDetail()

defineExpose({
    setFormData,
    getDetail
})
</script>

<style scoped>
.form-tips {
    color: #909399;
    font-size: 12px;
    margin-top: 4px;
}
</style>
