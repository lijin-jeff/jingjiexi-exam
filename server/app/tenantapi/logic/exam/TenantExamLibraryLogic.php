<?php
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 精解析答题系统开发者版权所有，拥有最终解释权。

namespace app\tenantapi\logic\exam;


use app\common\logic\BaseLogic;
use app\common\model\exam\TenantExamLibrary;
use app\common\service\FileService;
use think\facade\Db;


/**
 * 题库管理逻辑
 * Class TenantExamLibraryLogic
 * @package app\platform\logic\exam
 */
class TenantExamLibraryLogic extends BaseLogic
{


    /**
     * @notes 添加题库管理
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/04/01 04:41
     */
    public static function add(array $params): bool
    {
        try {
            $model = TenantExamLibrary::create([
                'uid'             => uid(),
                'title'           => $params['title'],
                'is_show'         => $params['is_show'],
                'sort'            => $params['sort'],
                'image'           => $params['image'] ? FileService::setFileUrl($params['image']) : '',
                'remark'          => $params['remark'],
                'category_uid'    => $params['category_uid'],
                'author'          => $params['author'],
                'free_state'      => $params['free_state'],
                'money'           => $params['money'],
                'discount'        => $params['discount'],
                'year'            => $params['year'],
                'tenant_id'       => $params['tenant_id'],
                'recommend_state' => $params['recommend_state'],
                'hot_state'       => $params['hot_state']
            ]);
            if (!$model->getKey()) {
                return false;
            }
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }


    /**
     * @notes 编辑题库管理
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/04/01 04:41
     */
    public static function edit(array $params): bool
    {
        try {
            $row = TenantExamLibrary::where([
                ['id', '=', $params['id']],
                ['tenant_id', '=', $params['tenant_id']]
            ])->update([
                'title'           => $params['title'],
                'is_show'         => $params['is_show'],
                'sort'            => $params['sort'],
                'image'           => $params['image'] ? FileService::setFileUrl($params['image']) : '',
                'remark'          => $params['remark'],
                'category_uid'    => $params['category_uid'],
                'author'          => $params['author'],
                'free_state'      => $params['free_state'],
                'money'           => $params['money'],
                'discount'        => $params['discount'],
                'year'            => $params['year'],
                'recommend_state' => $params['recommend_state'],
                'hot_state'       => $params['hot_state'],
            ]);
            return $row > 0;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }


    /**
     * @notes 删除题库管理
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/04/01 04:41
     */
    public static function delete(array $params): bool
    {
        if (is_array($params["id"])) {
            return TenantExamLibrary::whereIn("id", $params["id"])->where([
                    ['tenant_id', '=', $params['tenant_id']]
                ])->update(['delete_time' => time()]) > 0;
        }
        return TenantExamLibrary::where([
                ['id', '=', $params['id']],
                ['tenant_id', '=', $params['tenant_id']]
            ])->update(['delete_time' => time()]) > 0;
    }


    /**
     * @notes 获取题库管理详情
     * @param $params
     * @return array
     * @author 精解析答题
     * @date 2025/04/01 04:41
     */
    public static function detail($params): array
    {
        $where = [];
        
        if (!empty($params['id'])) {
            $where[] = ['id', '=', $params['id']];
        }
        
        if (!empty($params['uid'])) {
            $where[] = ['uid', '=', $params['uid']];
        }
        
        if (!empty($params['tenant_id'])) {
            $where[] = ['tenant_id', '=', $params['tenant_id']];
        }
        
        return TenantExamLibrary::where($where)->find()->toArray();
    }

    /**
     * @notes 题库导出
     * @param array $params
     * @param int $tenantId
     * @return array
     * @author 精解析答题
     * @date 2026/01/18
     */


    /**
     * @notes 题库导入
     * @param mixed $file
     * @param int $tenantId
     * @return array
     * @author 精解析答题
     * @date 2026/01/18
     */
    public static function import($file, int $tenantId): array
    {
        try {
            // 初始化导入结果
            $result = [
                'success' => 0,
                'failed' => 0,
                'errors' => []
            ];

            // 获取文件信息
            $fileInfo = $file->getInfo();
            $ext = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));

            // 验证文件格式
            $validExts = ['xlsx', 'xls', 'csv'];
            if (!in_array($ext, $validExts)) {
                self::setError('只支持Excel和CSV格式的文件');
                return $result;
            }

            // 这里应该调用文件解析服务，解析上传的文件
            // 由于缺少具体的文件解析服务，这里使用模拟数据
            $parsedData = self::mockParsedData();

            // 验证并导入数据
            foreach ($parsedData as $index => $item) {
                try {
                    // 验证数据
                    self::validateImportData($item);

                    // 导入数据
                    $addResult = self::add(array_merge($item, [
                        'tenant_id' => $tenantId,
                        'image' => '', // 图片需要单独处理
                    ]));

                    if ($addResult) {
                        $result['success']++;
                    } else {
                        $result['failed']++;
                        $result['errors'][] = [
                            'row' => $index + 1,
                            'message' => self::getError()
                        ];
                    }
                } catch (\Exception $e) {
                    $result['failed']++;
                    $result['errors'][] = [
                        'row' => $index + 1,
                        'message' => $e->getMessage()
                    ];
                }
            }

            return $result;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return [
                'success' => 0,
                'failed' => 0,
                'errors' => [
                    [
                        'row' => 0,
                        'message' => $e->getMessage()
                    ]
                ]
            ];
        }
    }

    /**
     * @notes 验证导入数据
     * @param array $data
     * @return void
     * @throws Exception
     * @author 精解析答题
     * @date 2026/01/18
     */
    private static function validateImportData(array $data): void
    {
        // 验证必填字段
        $requiredFields = ['title', 'category_uid', 'free_state'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new \Exception("缺少必填字段：{$field}");
            }
        }

        // 验证数据格式
        if (!in_array($data['free_state'], [0, 1])) {
            throw new \Exception("free_state 必须是 0 或 1");
        }

        if (!in_array($data['is_show'] ?? 1, [0, 1])) {
            throw new \Exception("is_show 必须是 0 或 1");
        }

        if (!in_array($data['recommend_state'] ?? 0, [0, 1])) {
            throw new \Exception("recommend_state 必须是 0 或 1");
        }

        if (!in_array($data['hot_state'] ?? 0, [0, 1])) {
            throw new \Exception("hot_state 必须是 0 或 1");
        }

        if (!is_numeric($data['money'] ?? 0)) {
            throw new \Exception("money 必须是数字");
        }

        if (!is_numeric($data['discount'] ?? 0)) {
            throw new \Exception("discount 必须是数字");
        }

        if (!is_numeric($data['sort'] ?? 0)) {
            throw new \Exception("sort 必须是数字");
        }

        if (!is_numeric($data['year'] ?? '')) {
            throw new \Exception("year 必须是数字");
        }

        // 验证折扣范围
        $discount = (float)($data['discount'] ?? 0);
        if ($discount < 0 || $discount > 1) {
            throw new \Exception("discount 必须在 0 到 1 之间");
        }
    }

    /**
     * @notes 模拟解析数据
     * @return array
     * @author 精解析答题
     * @date 2026/01/18
     */
    private static function mockParsedData(): array
    {
        // 模拟解析的数据，实际实现中应该是从上传文件中解析出来的
        return [
            [
                'title' => '模拟题库1',
                'category_uid' => '1',
                'author' => '精解析答题',
                'free_state' => 1,
                'money' => 0,
                'discount' => 1,
                'year' => '2025',
                'recommend_state' => 0,
                'hot_state' => 0,
                'is_show' => 1,
                'sort' => 100,
                'remark' => '这是一个模拟题库',
            ],
            [
                'title' => '模拟题库2',
                'category_uid' => '1',
                'author' => '精解析答题',
                'free_state' => 0,
                'money' => 99,
                'discount' => 0.8,
                'year' => '2025',
                'recommend_state' => 1,
                'hot_state' => 1,
                'is_show' => 1,
                'sort' => 99,
                'remark' => '这是另一个模拟题库',
            ]
        ];
    }
}