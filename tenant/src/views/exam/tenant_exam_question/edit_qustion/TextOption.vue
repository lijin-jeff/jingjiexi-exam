<!-- 文本导入 -->
<template>
    <div>
        <div class="margin-bottom20" style="display: flex">
            <div style="width: 50%">
                <div class="tip-head-title">
                    <el-button type="primary" icon="InfoFilled" link disabled>编辑区</el-button>
                </div>
                <el-input
                    type="textarea"
                    v-model="formData.content"
                    :autosize="{ minRows: 40, maxRows: 40 }"
                    :placeholder="placeholder"
                    resize="none"
                    :show-word-limit="true"
                    maxlength="65535"
                />
            </div>
            <div style="width: 50%; padding-left: 20px">
                <div class="tip-head-title">
                    <el-button type="primary" icon="View" link disabled>预览区</el-button>
                </div>
                <el-input
                    type="textarea"
                    v-model="formData.content"
                    :autosize="{ minRows: 40, maxRows: 40 }"
                    :placeholder="placeholder"
                    resize="none"
                    :show-word-limit="true"
                    maxlength="65535"
                    :disabled="true"
                />
            </div>
        </div>
        <div class="display-flex-start-center">
            <el-button type="primary" @click="saveForm">保存试题</el-button>
            <el-button type="warning">重置数据</el-button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { apiTenantExamQuestionTextAdd } from '@/api/exam/tenant_exam_question'

interface Props {
    libraryUid: string
}
const props = defineProps<Props>()
const placeholder = `下面是文本录入的试题格式，目前只支持单选题、多选题和判断题。每一道试题之间，需要换行。

题目：下列属于行政行为的是（ ）
A. 某县民政局建办公楼的行为
B. 某县民政局起诉建筑公司违约的行为
C. 某县民政局越权处罚违约的建筑公司的行为
D. 某县民政局依建筑合同奖励建筑公司的行为
答案：A
题型：单选题
解释：行政行为是指行政主体行使行政职权，作出的能够产生行政法律效果的行为。。

题目：下列属于行政行为的是（ ）
A. 某县民政局建办公楼的行为
B. 某县民政局起诉建筑公司违约的行为
C. 某县民政局越权处罚违约的建筑公司的行为
D. 某县民政局依建筑合同奖励建筑公司的行为
答案：A、B
题型：多选题
解释：行政行为是指行政主体行使行政职权，作出的能够产生行政法律效果的行为。。

题目：夏天小孩适合在河塘中游泳（ ）
A. 正确
B. 错误
答案：A
题型：判断题
解释：河塘水深，小孩应原理河塘。
`

const formData = reactive({
    chapter_uid: '',
    library_uid: props.libraryUid,
    content: ''
})

const saveForm = async () => {
    const data = { ...formData }
    await apiTenantExamQuestionTextAdd(data)
}
</script>

<style scoped>
.child-component {
    border: 1px solid #eee;
    padding: 20px;
    margin: 10px;
}

.tip-head-title {
    font-size: 30px;
}
</style>
