<template>
    <el-card class="!border-none margin-bottom20" shadow="never">
        <el-page-header content="试题添加" @back="$router.back()" />
    </el-card>
    <el-card class="border-none mb-4" shadow="never">
        <div style="display: flex">
            <el-button type="primary" @click="examChange('manual')">手动录入</el-button>
            <el-button type="warning" @click="examChange('ai')">AI录入</el-button>
            <el-button type="danger" @click="examChange('text')">文本录入</el-button>
        </div>
        <div style="margin-top: 20px">
            <Manual :library-uid="library_uid" v-if="inputType === 'manual'" />
            <AiOption :library-uid="library_uid as string" v-if="inputType === 'ai'" />
            <TextOption :library-uid="library_uid as string" v-if="inputType === 'text'" />
        </div>
    </el-card>
</template>

<script lang="ts" setup name="tenantExamQuestionEdit">
import AiOption from './edit_qustion/AiOption.vue'
import Manual from './edit_qustion/manual.vue'
import TextOption from './edit_qustion/TextOption.vue'

const route = useRoute()

const inputType = ref('manual')

const library_uid = ref(route.query.library_uid)
//console.log(library_uid)

const examChange = (type: string) => {
    inputType.value = type
}
</script>
