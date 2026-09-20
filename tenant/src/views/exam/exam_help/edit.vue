<template>
    <div class="article-edit">
        <el-card class="!border-none" shadow="never">
            <el-page-header :content="$route.meta.title" @back="$router.back()" />
        </el-card>
        <el-card class="mt-4 !border-none" shadow="never">
            <el-form
                ref="formRef"
                class="ls-form"
                :model="formData"
                label-width="85px"
                :rules="rules"
            >
                <div class="xl:flex">
                    <div>
                        <el-form-item label="标题" prop="title">
                            <div class="w-80">
                                <el-input
                                    v-model="formData.title"
                                    placeholder="请输入标题"
                                    type="textarea"
                                    :autosize="{ minRows: 2, maxRows: 3 }"
                                    maxlength="64"
                                    show-word-limit
                                    clearable
                                />
                            </div>
                        </el-form-item>
                        <el-form-item label="封面" prop="image">
                            <div>
                                <div>
                                    <material-picker v-model="formData.image" :limit="1" />
                                </div>
                                <div class="form-tips">建议尺寸：240*180px</div>
                            </div>
                        </el-form-item>
                        <el-form-item label="作者" prop="author">
                            <div class="w-80">
                                <el-input v-model="formData.author" placeholder="请输入作者名称" />
                            </div>
                        </el-form-item>
                        <el-form-item label="排序" prop="sort">
                            <div>
                                <el-input-number v-model="formData.sort" :min="0" :max="9999" />
                                <div class="form-tips">默认为0， 数值越大越排前</div>
                            </div>
                        </el-form-item>
                        <el-form-item label="初始浏览量" prop="click_virtual">
                            <div>
                                <el-input-number v-model="formData.click_virtual" :min="0" />
                            </div>
                        </el-form-item>
                        <el-form-item label="状态" required prop="is_show">
                            <el-radio-group v-model="formData.is_show">
                                <el-radio :value="1">显示</el-radio>
                                <el-radio :value="0">隐藏</el-radio>
                            </el-radio-group>
                        </el-form-item>
                    </div>
                    <div class="xl:ml-20">
                        <el-form-item label="内容" prop="content">
                            <editor v-model="formData.content" :height="667" :width="575" />
                        </el-form-item>
                    </div>
                </div>
            </el-form>
        </el-card>
        <footer-btns>
            <el-button type="primary" @click="handleSave">保存</el-button>
        </footer-btns>
    </div>
</template>

<script lang="ts" setup name="helpListsEdit">
import type { FormInstance } from 'element-plus'

import { helpAdd, helpDetail, helpEdit } from '@/api/exam/help'
import useMultipleTabs from '@/hooks/useMultipleTabs'

const route = useRoute()
const router = useRouter()
const formData = reactive({
    id: '',
    title: '',
    image: '',
    author: '',
    content: '',
    click_virtual: 0,
    sort: 0,
    is_show: 1
})

const { removeTab } = useMultipleTabs()
const formRef = shallowRef<FormInstance>()
const rules = reactive({
    title: [{ required: true, message: '请输入标题', trigger: 'blur' }]
})

const getDetails = async () => {
    const data = await helpDetail({
        id: route.query.id
    })
    Object.keys(formData).forEach((key) => {
        //@ts-ignore
        formData[key] = data[key]
    })
}

const handleSave = async () => {
    await formRef.value?.validate()
    if (route.query.id) {
        await helpEdit(formData)
    } else {
        await helpAdd(formData)
    }
    removeTab()
    router.back()
}

route.query.id && getDetails()
</script>
