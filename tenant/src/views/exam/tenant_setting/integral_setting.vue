<template>
    <div>
        <el-card class="!border-none" shadow="never">
            <el-form ref="formRef" :model="formData" label-width="90px" :rules="formRules">
                <!-- 积分文字自定义 -->
                <el-form-item label="自定义文字" prop="integral_text_custom">
                    <div>
                        <el-input
                            style="width: 240px"
                            v-model="formData.integral_text_custom"
                            type="text"
                            maxlength="6"
                            show-word-limit
                            clearable
                            placeholder="请输入积分文字自定义"
                        />
                        <div class="form-tips">最多输入6个字符</div>
                    </div>
                </el-form-item>

                <!-- 积分说明 -->
                <el-form-item label="积分说明" prop="user_integral_rule">
                    <div>
                        <el-input
                            style="width: 400px; height: 120px"
                            v-model="formData.user_integral_rule"
                            maxlength="120"
                            type="textarea"
                            show-word-limit
                            clearable
                            placeholder="请输入积分说明"
                        />
                        <div class="form-tips">最多输入120个字符</div>
                    </div>
                </el-form-item>

                <!-- 积分获取设置 -->
                <el-form-item label="积分获取">
                    <div class="integral-quantity-setting">
                        <!-- 每日答题获得积分上限 -->
                        <div class="integral-item">
                            <div class="item-title">每日答题积分上限</div>
                            <el-input-number
                                v-model="formData.daily_integral_limit"
                                :min="0"
                                :precision="0"
                                style="width: 160px"
                                placeholder="0表示无上限"
                            />
                            <div class="form-tips">每日答题获得积分数量上限，避免刷积分</div>
                        </div>
                        <!-- 注册赠送积分 -->
                        <div class="integral-item">
                            <div class="item-title">注册赠送积分</div>
                            <el-input-number
                                v-model="formData.register_integral"
                                :min="0"
                                :precision="0"
                                style="width: 160px"
                                placeholder="0表示不赠送"
                            />
                            <div class="form-tips">注册用户赠送的积分数量</div>
                        </div>
                        <!-- 登录赠送积分 -->
                        <div class="integral-item">
                            <div class="item-title">登录赠送积分</div>
                            <el-input-number
                                v-model="formData.login_integral"
                                :min="0"
                                :precision="0"
                                style="width: 160px"
                                placeholder="0表示不赠送"
                            />
                            <div class="form-tips">每日首次登录，赠送的积分数量</div>
                        </div>
                        <!-- 邀请新用户 -->
                        <div class="integral-item">
                            <div class="item-title">邀请新用户赠送积分</div>
                            <el-input-number
                                v-model="formData.invite_user_integral"
                                :min="0"
                                :precision="0"
                                style="width: 160px"
                                placeholder="0表示不赠送"
                            />
                            <div class="form-tips">邀请新用户注册，赠送的积分数量</div>
                        </div>
                        <!-- 分享小程序 -->
                        <div class="integral-item">
                            <div class="item-title">分享小程序赠送积分</div>
                            <el-input-number
                                v-model="formData.share_integral"
                                :min="0"
                                :precision="0"
                                style="width: 160px"
                                placeholder="0表示不赠送"
                            />
                            <div class="form-tips">分享小程序，赠送的积分数量</div>
                        </div>
                        <!-- 接收订阅消息积分 -->
                        <div class="integral-item">
                            <div class="item-title">接收订阅消息积分</div>
                            <el-input-number
                                v-model="formData.subscribe_integral"
                                :min="0"
                                :precision="0"
                                style="width: 160px"
                                placeholder="0表示不赠送"
                            />
                            <div class="form-tips">用户接收订阅消息，赠送的积分数量</div>
                        </div>
                    </div>
                </el-form-item>
                <!-- 积分消耗 -->

                <el-form-item label="积分消耗">
                    <div class="integral-quantity-setting">
                        <div class="integral-quantity-setting"></div>
                        <!-- 下载资源 -->
                        <div class="integral-item">
                            <div class="item-title">下载资源消耗积分</div>
                            <el-input-number
                                v-model="formData.download_integral"
                                :min="0"
                                :precision="0"
                                style="width: 160px"
                                placeholder="0表示不消耗"
                            />
                            <div class="form-tips">用户下载vip资源，消耗的积分</div>
                        </div>
                    </div>
                </el-form-item>

                <!-- 填空题、问答题、案例题积分计算规则 -->
                <el-form-item label="计算规则" prop="integral_count_rule">
                    <div>
                        <el-radio-group v-model="formData.integral_count_rule" style="width: 240px">
                            <el-radio :label="1">不进行计算</el-radio>
                            <el-radio :label="2">提交即计算（不管有没有填写答案）</el-radio>
                            <el-radio :label="3">提交且填写答案才计算</el-radio>
                        </el-radio-group>
                        <div class="form-tips">请选择填空题、问答题、案例题积分计算规则</div>
                    </div>
                </el-form-item>

                <!-- 按钮，清空积分 -->
                <el-form-item label="清空积分" prop="clear_integral">
                    <el-button type="primary" @click="clearIntegral">清空积分</el-button>
                </el-form-item>
            </el-form>

            <footer-btns v-perms="['exam.tenant_integral_settings/apiTenantIntegralSettingsEdit']">
                <el-button type="primary" @click="handleSubmit">保存</el-button>
            </footer-btns>
        </el-card>
    </div>
</template>

<script lang="ts" setup name="tenantIntegralSettingsEdit">
import type { FormInstance } from 'element-plus'
import type { PropType } from 'vue'
import { reactive, ref, shallowRef } from 'vue'

import {
    apiTenantIntegralSettingsClear,
    apiTenantIntegralSettingsDetail,
    apiTenantIntegralSettingsEdit
} from '@/api/exam/tenant_integral_settings'

defineProps({
    dictData: {
        type: Object as PropType<Record<string, any[]>>,
        default: () => ({})
    }
})
const emit = defineEmits(['success', 'close'])
const formRef = shallowRef<FormInstance>()
const mode = ref('add')

// 表单数据
const formData = reactive({
    id: '',
    integral_text_custom: '积分',
    user_integral_rule:
        '系统积分可以通过新用户注册、在线答题刷题、邀请新用户和接收系统消息推送等系统涉及积分的功能获取。获取到的积分可以用来下载资源、充值兑换和积分商城兑换等积分相关的功能。具体获取或消耗根据系统功能而定，积分的最终解释权归平台所有',
    daily_integral_limit: 0, // 每日答题获得积分上限，0表示无上限
    register_integral: 0, // 注册赠送积分，0表示不赠送
    login_integral: 0, // 登录赠送积分，0表示不赠送
    invite_user_integral: 0, // 邀请新用户注册赠送积分，0表示不赠送
    subscribe_integral: 0, // 会员订阅赠送积分，0表示不赠送
    share_integral: 0, // 分享给好友获得积分，0表示不赠送
    download_integral: 0, // 下载资源消耗积分，0表示不消耗
    integral_count_rule: 2 // 填空题、问答题、案例题积分计算规则：1.不计算，2.提交即计算，3.提交且有答案才计算
})

// 表单验证
const formRules = reactive<any>({})

// 获取详情
const setFormData = async (data: Record<any, any>) => {
    for (const key in formData) {
        if (data[key] != null && data[key] != undefined) {
            //@ts-ignore
            formData[key] = data[key]
        }
    }
}

const getDetail = async () => {
    const data = await apiTenantIntegralSettingsDetail({})
    setFormData(data)
}

// 提交按钮
const handleSubmit = async () => {
    await formRef.value?.validate()
    const data = { ...formData }
    mode.value = await apiTenantIntegralSettingsEdit(data)
    emit('success')
    getDetail()
}

// 清空积分
const clearIntegral = async () => {
    await apiTenantIntegralSettingsClear({})
}

getDetail()

defineExpose({
    setFormData,
    getDetail
})
</script>

<style scoped>
.integral-quantity-setting {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.integral-item {
    display: flex;
    flex-direction: column;
    width: 180px;
}

.item-title {
    font-weight: 500;
    margin-bottom: 10px;
    color: #333;
}

.form-tips {
    margin-top: 8px;
    font-size: 12px;
    color: #999;
    line-height: 1.4;
}
</style>
