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
                        <el-form-item label="文章标题" prop="title">
                            <div style="width: 80%">
                                <el-input
                                    v-model="formData.title"
                                    placeholder="请输入文章标题"
                                    type="textarea"
                                    :autosize="{ minRows: 3, maxRows: 3 }"
                                    maxlength="64"
                                    show-word-limit
                                    clearable
                                />
                            </div>
                        </el-form-item>
                        <el-form-item label="文章栏目" prop="cid">
                            <el-select
                                class="w-80"
                                v-model="formData.cid"
                                placeholder="请选择文章栏目"
                                clearable
                                @change="handleChangeArticleCate"
                            >
                                <el-option
                                    v-for="item in optionsData.article_cate"
                                    :key="item.id"
                                    :label="item.name"
                                    :value="item.id"
                                />
                            </el-select>
                        </el-form-item>
                        <el-form-item label="所属题库" prop="exam_category_uid">
                            <el-cascader
                                class="w-80"
                                v-model="formData.exam_category_uid"
                                :options="examCategoryList"
                                :props="examCascaderProps"
                                placeholder="请选择题库分类"
                                disabled
                                @change="handleChangeExamCategory"
                            />
                        </el-form-item>
                        <el-form-item label="文章简介" prop="desc">
                            <div style="width: 80%">
                                <el-input
                                    v-model="formData.desc"
                                    placeholder="请输入文章简介"
                                    type="textarea"
                                    :autosize="{ minRows: 3, maxRows: 6 }"
                                    :maxlength="200"
                                    show-word-limit
                                    clearable
                                />
                            </div>
                        </el-form-item>
                        <el-form-item label="摘要" prop="abstract">
                            <div style="width: 80%">
                                <el-input
                                    type="textarea"
                                    :autosize="{ minRows: 6, maxRows: 6 }"
                                    v-model="formData.abstract"
                                    maxlength="200"
                                    show-word-limit
                                    clearable
                                />
                            </div>
                        </el-form-item>
                        <el-form-item label="文章封面" prop="image">
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
                        <el-form-item label="文章状态" required prop="is_show">
                            <el-radio-group v-model="formData.is_show">
                                <el-radio :value="1">显示</el-radio>
                                <el-radio :value="0">隐藏</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item label="外部链接" prop="is_external_link">
                            <el-radio-group
                                v-model="formData.is_external_link"
                                @change="handleIsExternalLinkChange"
                            >
                                <el-radio :value="0">否</el-radio>
                                <el-radio :value="1">是</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <!-- 外部链接 -->
                        <el-form-item label="链接地址" prop="external_link">
                            <div style="width: 80%">
                                <el-input
                                    v-model="formData.external_link"
                                    placeholder="请输入外部链接"
                                    @input="handleExternalLinkChange"
                                    :disabled="formData.is_external_link === 0"
                                />
                            </div>
                        </el-form-item>
                    </div>
                    <div class="xl:ml-20">
                        <el-form-item label="文章内容" prop="content">
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

<script lang="ts" setup name="articleListsEdit">
import type { FormInstance } from 'element-plus'

import { articleAdd, articleCateAll, articleDetail, articleEdit } from '@/api/article'
import { apiExamCategoryTree } from '@/api/exam/exam_category'
import { useDictOptions } from '@/hooks/useDictOptions'
import useMultipleTabs from '@/hooks/useMultipleTabs'

const route = useRoute()
const router = useRouter()
const formData = reactive({
    id: '',
    title: '',
    image: '',
    cid: '',
    desc: '',
    author: '',
    content: '',
    click_virtual: 0,
    sort: 0,
    is_show: 1,
    external_link: '',
    is_external_link: 0,
    abstract: '',
    exam_category_uid: ''
})

const { removeTab } = useMultipleTabs()
const formRef = shallowRef<FormInstance>()
const rules = reactive({
    title: [{ required: true, message: '请输入文章标题', trigger: 'blur' }],
    cid: [{ required: true, message: '请选择文章栏目', trigger: 'blur' }],
    exam_category_uid: [{ required: true, message: '请选择题库分类', trigger: ['blur', 'change'] }],
    external_link: [
        {
            required: true,
            validator: (rule: any, value: string, callback: (error?: Error | string) => void) => {
                if (formData.is_external_link === 1 && !value) {
                    callback(new Error('请输入外部链接'))
                } else if (
                    value &&
                    !/^(https?:\/\/)[\w.-]+(?:\.[\w.-]+)+[\w\-._~:/?#[\]@!$&'()*+,;=.]+$/.test(
                        value
                    )
                ) {
                    callback(new Error('请输入正确的URL格式'))
                } else {
                    callback()
                }
            },
            trigger: ['blur', 'change']
        }
    ]
})

// 题库分类相关
const examCategoryList = reactive<any[]>([])

// 级联选择器配置 - 题库分类
const examCascaderProps = {
    value: 'uid',
    label: 'title',
    children: 'children',
    checkStrictly: true, // 允许选择任意一级
    emitPath: false // 只返回选中节点的值，不返回路径数组
}

// 文章栏目变化处理
const handleChangeArticleCate = (value: any) => {
    formData.cid = value || ''

    // 当添加或编辑文章且选择了文章栏目时，自动设置题库分类
    if (value) {
        const articleCate = optionsData.article_cate.find((item) => item.id === value)
        if (articleCate && articleCate.exam_category_uid) {
            formData.exam_category_uid = articleCate.exam_category_uid
        } else {
            formData.exam_category_uid = ''
        }
    }
}

// 外部链接变化处理
const handleExternalLinkChange = () => {
    if (formData.external_link) {
        formData.is_external_link = 1
    } else {
        formData.is_external_link = 0
    }
}

// 是否是外部链接变化处理
const handleIsExternalLinkChange = () => {
    if (formData.is_external_link === 0) {
        formData.external_link = ''
    }
}

// 题库分类变化处理
const handleChangeExamCategory = (value: any) => {
    formData.exam_category_uid = value || ''
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

const getDetails = async () => {
    const data = await articleDetail({
        id: route.query.id
    })
    Object.keys(formData).forEach((key) => {
        //@ts-ignore
        formData[key] = data[key]
    })
}

const { optionsData } = useDictOptions<{
    article_cate: any[]
}>({
    article_cate: {
        api: articleCateAll
    }
})

const handleSave = async () => {
    await formRef.value?.validate()
    if (route.query.id) {
        await articleEdit(formData)
    } else {
        await articleAdd(formData)
    }
    removeTab()
    router.back()
}

// 初始化获取题库分类数据
fetchExamCategoryList()

route.query.id && getDetails()
</script>
