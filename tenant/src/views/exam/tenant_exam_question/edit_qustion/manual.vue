<!-- 手动录入 -->
<template>
    <div class="">
        <el-form-item label="试题类型">
            <el-radio-group
                placeholder="请选择"
                v-model="examOptionType"
                @change="examOptionTypeChange"
            >
                <el-radio
                    v-for="(item, index) in dictData.exam_type"
                    :key="index"
                    :value="parseInt(item.value)"
                    :label="parseInt(item.value)"
                >
                    {{ item.name }}
                </el-radio>
            </el-radio-group>
        </el-form-item>
        <div style="margin-top: 20px">
            <!-- 单选试题 -->
            <RadioOption v-if="examOptionType === 1" :library-uid="library_uid" />
            <!-- 多选题 -->
            <CheckBoxOption v-else-if="examOptionType === 2" :library-uid="library_uid" />
            <!-- 判断题 -->
            <JudeOption v-else-if="examOptionType === 3" :library-uid="library_uid" />
            <!-- 填空题 -->
            <WriteOption v-else-if="examOptionType === 4" :library-uid="library_uid" />
            <!-- 问答题 -->
            <QuestionOption v-else-if="examOptionType === 5" :library-uid="library_uid" />
            <!-- 复合题 -->
            <CompoundOption v-else-if="examOptionType === 6" :library-uid="library_uid" />
            <!-- 完形填空 -->
            <ClozeOption v-else-if="examOptionType === 7" :library-uid="library_uid" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { useDictData } from '@/hooks/useDictOptions'

import CheckBoxOption from '../component/CheckBoxOption.vue'
import ClozeOption from '../component/ClozeOption.vue'
import CompoundOption from '../component/CompoundOption.vue'
import JudeOption from '../component/JudeOption.vue'
import QuestionOption from '../component/QuestionOption.vue'
import RadioOption from '../component/RadioOption.vue'
import WriteOption from '../component/WriteOption.vue'

const route = useRoute()

const examOptionType = ref(1)
// 正确处理 library_uid 类型，确保它是字符串
const library_uid = ref<string>(
    typeof route.query.library_uid === 'string' ? route.query.library_uid : ''
)

const { dictData } = useDictData('exam_type')
const examOptionTypeChange = (val: string | number | boolean | undefined) => {
    // 确保值是数字类型
    if (typeof val === 'number') {
        examOptionType.value = val
    } else if (typeof val === 'string') {
        examOptionType.value = parseInt(val) || 1
    }
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
