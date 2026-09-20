<?php
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 精解析答题系统开发者版权所有，拥有最终解释权。

namespace app\tenantapi\logic\exam;

use app\common\logic\BaseLogic;
use think\facade\Config;

/**
 * AI 试题识别逻辑
 * Class TenantExamAiLogic
 * @package app\tenantapi\logic\exam
 */
class TenantExamAiLogic extends BaseLogic
{
    /**
     * 默认提示词
     */
    const DEFAULT_PROMPT = '请识别这张图片中的试题，提取出题型（单选题、多选题、判断题、填空题、问答题、案例题）、题干、选项（如果有）、正确答案和解析内容，以及笔记第一条的内容部分（没有笔记的话就不识别，不含用户名）。用JSON格式输出。题型字段为exam_type_name。';

    /**
     * @notes AI 识别试题
     * @param array $params
     * @return array
     * @author 精解析答题
     * @date 2025/04/12 15:53
     */
    public static function recognize(array $params): array
    {
        try {
            $imageFile = $params['image'] ?? null;
            $prompt = $params['prompt'] ?? self::DEFAULT_PROMPT;
            
            if (!$imageFile) {
                return ['error' => '请上传图片'];
            }
            
            // 获取配置
            $apiKey = Config::get('project.ai.doubao_api_key', '');
            $modelName = Config::get('project.ai.doubao_model', 'doubao-seed-1-8-251228');
            $apiUrl = Config::get('project.ai.doubao_api_url', 'https://ark.cn-beijing.volces.com/api/v3/responses');
            
            if (empty($apiKey)) {
                return ['error' => 'AI API密钥未配置'];
            }
            
            // 上传图片获取 file_id
            $fileId = self::uploadImageToDoubao($imageFile, $apiKey);
            
            if (isset($fileId['error'])) {
                return $fileId;
            }
            
            // 调用豆包API进行识别
            $result = self::callDoubaoAPI($fileId, $prompt, $apiKey, $modelName, $apiUrl);
            
            return $result;
        } catch (\Exception $e) {
            return ['error' => '识别失败：' . $e->getMessage()];
        }
    }

    /**
     * 上传图片到豆包获取 file_id
     * @param $imageFile
     * @param string $apiKey
     * @return string|array
     */
    private static function uploadImageToDoubao($imageFile, string $apiKey)
    {
        try {
            $uploadUrl = 'https://ark.cn-beijing.volces.com/api/v3/files';
            
            // 获取文件信息
            $filePath = $imageFile->getPathname();
            $fileName = $imageFile->getOriginalName();
            $mimeType = $imageFile->getMime();
            
            // 构建 multipart/form-data 请求
            $boundary = uniqid();
            $body = '';
            
            // 添加 purpose 字段
            $body .= "--{$boundary}\r\n";
            $body .= "Content-Disposition: form-data; name=\"purpose\"\r\n\r\n";
            $body .= "user_data\r\n";
            
            // 添加 file 字段
            $body .= "--{$boundary}\r\n";
            $body .= "Content-Disposition: form-data; name=\"file\"; filename=\"{$fileName}\"\r\n";
            $body .= "Content-Type: {$mimeType}\r\n\r\n";
            $body .= file_get_contents($filePath) . "\r\n";
            $body .= "--{$boundary}--\r\n";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $uploadUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: multipart/form-data; boundary=' . $boundary
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                return ['error' => '上传图片失败，HTTP状态码：' . $httpCode];
            }
            
            $data = json_decode($response, true);
            
            // 解析响应获取 file_id
            if (isset($data['id'])) {
                return $data['id'];
            }
            
            if (isset($data['data']['file_id'])) {
                return $data['data']['file_id'];
            }
            
            return ['error' => '上传图片失败：无法获取文件ID'];
        } catch (\Exception $e) {
            return ['error' => '上传图片失败：' . $e->getMessage()];
        }
    }

    /**
     * 调用豆包API进行识别
     * @param string $fileId
     * @param string $prompt
     * @param string $apiKey
     * @param string $modelName
     * @param string $apiUrl
     * @return array
     */
    private static function callDoubaoAPI(string $fileId, string $prompt, string $apiKey, string $modelName, string $apiUrl): array
    {
        try {
            $requestData = [
                'model' => $modelName,
                'input' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'input_image',
                                'file_id' => $fileId
                            ],
                            [
                                'type' => 'input_text',
                                'text' => $prompt
                            ]
                        ]
                    ]
                ]
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                return ['error' => 'API调用失败，HTTP状态码：' . $httpCode];
            }
            
            $data = json_decode($response, true);
            
            if (!$data) {
                return ['error' => '解析API响应失败'];
            }
            
            // 解析响应结果
            $output = $data['output'] ?? [];
            $usage = $data['usage'] ?? [
                'input_tokens' => 0,
                'output_tokens' => 0,
                'total_tokens' => 0
            ];
            $model = $data['model'] ?? $modelName;
            
            // 查找 message 类型的输出
            $messageObj = null;
            foreach ($output as $item) {
                if ($item['type'] === 'message') {
                    $messageObj = $item;
                    break;
                }
            }
            
            if (!$messageObj || !isset($messageObj['content'])) {
                return ['error' => 'API响应中没有找到有效的识别结果'];
            }
            
            // 查找 output_text 类型的内容
            $outputText = null;
            foreach ($messageObj['content'] as $content) {
                if ($content['type'] === 'output_text') {
                    $outputText = $content['text'];
                    break;
                }
            }
            
            if (!$outputText) {
                // 尝试直接使用 content
                if (is_string($messageObj['content'])) {
                    $outputText = $messageObj['content'];
                } else {
                    return ['error' => 'API响应中没有找到有效的文本内容'];
                }
            }
            
            // 解析 JSON 结果
            $result = json_decode($outputText, true);
            
            if (!$result) {
                return ['error' => '解析识别结果失败，返回内容：' . substr($outputText, 0, 200)];
            }
            
            return [
                'result' => $result,
                'usage' => $usage,
                'model' => $model
            ];
        } catch (\Exception $e) {
            return ['error' => 'API调用失败：' . $e->getMessage()];
        }
    }
}
