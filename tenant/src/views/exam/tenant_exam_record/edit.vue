<template>
    <div class="edit-popup">
        <popup
            ref="popupRef"
            title="做题记录详情"
            :async="false"
            width="800px"
            :show-confirm="false"
            @close="handleClose"
        >
            <el-descriptions :column="2" border>
                <el-descriptions-item label="记录编号">
                    {{ formData.uid }}
                </el-descriptions-item>
                <el-descriptions-item label="做题类型">
                    {{ getQuestionsTypeName(formData.questions_type) }}
                </el-descriptions-item>
                <el-descriptions-item label="标题" :span="2">
                    {{ formData.title || '-' }}
                </el-descriptions-item>
                <el-descriptions-item label="用户信息">
                    {{ formData.user_nickname || '-' }} ({{ formData.user_sn || '-' }})
                </el-descriptions-item>
                <el-descriptions-item label="题库/试卷">
                    {{ formData.library_title || '-' }}
                </el-descriptions-item>
                <el-descriptions-item label="答题得分">
                    <el-tag
                        :type="
                            formData.user_score >= formData.paper_score * 0.6 ? 'success' : 'danger'
                        "
                    >
                        {{ formData.user_score }} / {{ formData.paper_score }}
                    </el-tag>
                </el-descriptions-item>
                <el-descriptions-item label="答题积分">
                    {{ formData.user_integral || 0 }}
                </el-descriptions-item>
                <el-descriptions-item label="正确题数">
                    <span class="text-green-600">{{ formData.correct_count }}</span>
                </el-descriptions-item>
                <el-descriptions-item label="错误题数">
                    <span class="text-red-600">{{ formData.error_count }}</span>
                </el-descriptions-item>
                <el-descriptions-item label="总题数">
                    {{ formData.total_count || 0 }}
                </el-descriptions-item>
                <el-descriptions-item label="已答题数">
                    {{ formData.answered_count || 0 }}
                </el-descriptions-item>
                <el-descriptions-item label="正确率">
                    {{ formData.accuracy_rate }}%
                </el-descriptions-item>
                <el-descriptions-item label="练习时长">
                    {{ formatDuration(formData.practice_duration) }}
                </el-descriptions-item>
                <el-descriptions-item label="考试时间">
                    {{ formData.exam_time || '-' }}
                </el-descriptions-item>
                <el-descriptions-item label="提交时间">
                    {{ formData.exam_submit_time || '-' }}
                </el-descriptions-item>
                <el-descriptions-item label="是否随机">
                    <el-tag :type="formData.is_rand == 1 ? 'success' : 'info'">
                        {{ formData.is_rand == 1 ? '是' : '否' }}
                    </el-tag>
                </el-descriptions-item>
            </el-descriptions>

            <div v-if="formData.options && formData.options.length > 0" class="mt-4">
                <el-divider content-position="left">答题详情</el-divider>
                <el-table :data="formData.options" border stripe>
                    <el-table-column type="index" label="序号" width="60" />
                    <el-table-column label="题目" prop="question_title" show-overflow-tooltip />
                    <el-table-column label="用户答案" prop="user_answer" width="100" />
                    <el-table-column label="正确答案" prop="correct_answer" width="100" />
                    <el-table-column label="是否正确" width="100" align="center">
                        <template #default="{ row }">
                            <el-tag :type="row.is_correct ? 'success' : 'danger'">
                                {{ row.is_correct ? '✓ 正确' : '✗ 错误' }}
                            </el-tag>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
        </popup>
    </div>
</template>

<script lang="ts" setup name="tenantExamRecordEdit">
import type { PropType } from 'vue'

import Popup from '@/components/popup/index.vue'

const props = defineProps({
    dictData: {
        type: Object as PropType<Record<string, any[]>>,
        default: () => ({})
    }
})
const emit = defineEmits(['success', 'close'])
const popupRef = shallowRef<InstanceType<typeof Popup>>()

// 表单数据
const formData = reactive({
    id: '',
    uid: '',
    questions_type: '',
    user_score: 0,
    paper_score: 0,
    user_integral: 0,
    tenant_id: '',
    exam_time: '',
    exam_submit_time: '',
    correct_count: 0,
    error_count: 0,
    total_count: 0,
    answered_count: 0,
    accuracy_rate: 0,
    practice_duration: 0,
    library_title: '',
    user_nickname: '',
    user_sn: '',
    user_avatar: '',
    title: '',
    is_rand: 2,
    options: [] as any[]
})

// 获取详情
const setFormData = async (data: Record<any, any>) => {
    for (const key in formData) {
        if (data[key] != null && data[key] != undefined) {
            //@ts-ignore
            formData[key] = data[key]
        }
    }

    // 解析 options JSON 数据
    if (data.options) {
        try {
            formData.options =
                typeof data.options === 'string' ? JSON.parse(data.options) : data.options
        } catch (e) {
            formData.options = []
        }
    }
}

// 获取做题类型名称
const getQuestionsTypeName = (value: string | number): string => {
    const type = props.dictData.questions_type?.find((item: any) => item.value == value)
    return type?.name || '-'
}

// 格式化时长
const formatDuration = (seconds: number | null | undefined): string => {
    if (!seconds || seconds <= 0) return '0秒'
    const hours = Math.floor(seconds / 3600)
    const minutes = Math.floor((seconds % 3600) / 60)
    const secs = seconds % 60

    if (hours > 0) {
        return `${hours}小时${minutes}分${secs}秒`
    } else if (minutes > 0) {
        return `${minutes}分${secs}秒`
    } else {
        return `${secs}秒`
    }
}

//打开弹窗
const open = (type = 'edit') => {
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
