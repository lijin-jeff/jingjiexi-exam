import request from '@/utils/request'

// AI 识别试题
export function apiTenantExamAiRecognize(data: FormData) {
    return request.post({
        url: '/exam.tenant_exam_ai/recognize',
        data,
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    })
}
