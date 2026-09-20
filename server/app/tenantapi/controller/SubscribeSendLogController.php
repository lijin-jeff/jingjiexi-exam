<?php
// +----------------------------------------------------------------------
// | likeadmin快速开发前后端分离管理后台（PHP版）
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 开源版本可自由商用，可去除界面版权logo
// | gitee下载：https://gitee.com/likeshop_gitee/likeadmin
// | github下载：https://github.com/likeshop-github/likeadmin
// | 访问官网：https://www.likeadmin.cn
// | likeadmin团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeadminTeam
// +----------------------------------------------------------------------

namespace app\tenantapi\controller;

use app\tenantapi\controller\BaseAdminController;
use app\common\model\SubscribeSendLog;
use app\common\annotation\AdminMenu;
use think\response\Json;
use think\facade\Db;

/**
 * 订阅消息发送记录控制器
 * Class SubscribeSendLogController
 * @package app\tenantapi\controller
 */
class SubscribeSendLogController extends BaseAdminController
{
    /**
     * @notes 列表
     * @return Json
     * @author 精解析题库
     * @date 2026/01/15
     */
    public function lists(): Json
    {
        $params = $this->request->param();
        
        $page = $params['page_no'] ?? 1;
        $limit = $params['page_size'] ?? 20;
        $type = $params['type'] ?? '';
        $sendStatus = $params['send_status'] ?? '';
        $startDate = $params['start_date'] ?? '';
        $endDate = $params['end_date'] ?? '';
        
        $query = SubscribeSendLog::alias('ssl')
            ->join('user u', 'u.id = ssl.user_id', 'LEFT')
            ->field([
                'ssl.*',
                'u.nickname'
            ])
            ->order('ssl.id', 'desc');
        
        // 按类型筛选
        if ($type) {
            $query = $query->where('ssl.type', $type);
        }
        
        // 按发送状态筛选
        if ($sendStatus !== '') {
            $query = $query->where('ssl.send_status', $sendStatus);
        }
        
        // 按时间范围筛选
        if ($startDate) {
            $query = $query->where('ssl.create_time', '>=', strtotime($startDate));
        }
        if ($endDate) {
            $query = $query->where('ssl.create_time', '<=', strtotime($endDate . ' 23:59:59'));
        }
        
        $paginator = $query->paginate([
            'page' => $page,
            'list_rows' => $limit
        ]);
        
        // 构建返回数据数组
        $result = [
            'lists'     => $paginator->items(),
            'page_no'   => $paginator->currentPage(),
            'page_size' => $limit,
            'count'     => $paginator->total(),
            'extend'    => []
        ];
        
        return $this->data($result);
    }
    
    /**
     * @notes 详情
     * @param int $id
     * @return Json
     * @author 精解析题库
     * @date 2026/01/15
     */
    public function detail(int $id): Json
    {
        $detail = SubscribeSendLog::find($id);
        if (!$detail) {
            return $this->fail('记录不存在');
        }
        
        return $this->data($detail);
    }
    
    /**
     * @notes 统计信息
     * @return Json
     * @author 精解析题库
     * @date 2026/01/15
     */
    public function stats(): Json
    {
        $stats = SubscribeSendLog::field([
            'COUNT(*) as total',
            'SUM(CASE WHEN send_status = 1 THEN 1 ELSE 0 END) as success',
            'SUM(CASE WHEN send_status = 2 THEN 1 ELSE 0 END) as failure',
            'AVG(retry_times) as avg_retry_times'
        ])->find();
        
        $stats['success_rate'] = $stats['total'] > 0 ? round(($stats['success'] / $stats['total']) * 100, 2) : 0;
        
        return $this->data($stats);
    }
    
    /**
     * @notes 创建日志记录
     * @return Json
     * @author 精解析题库
     * @date 2026/01/16
     */
    public function create(): Json
    {
        $params = $this->request->post();
        
        // 验证必填参数
        if (empty($params['user_id']) || empty($params['type']) || empty($params['template_id']) || empty($params['data'])) {
            return $this->fail('缺少必填参数');
        }
        
        try {
            $log = new SubscribeSendLog();
            $log->user_id = $params['user_id'];
            $log->type = $params['type'];
            $log->template_id = $params['template_id'];
            $log->data = $params['data'];
            $log->send_status = $params['send_status'] ?? 0;
            $log->retry_times = $params['retry_times'] ?? 0;
            $log->send_time = $params['send_time'] ?? 0;
            $log->error_code = $params['error_code'] ?? 0;
            $log->error_msg = $params['error_msg'] ?? '';
            $log->result = $params['result'] ?? '';
            $log->tenant_id = $params['tenant_id'] ?? $this->request->tenantId;
            $log->save();
            
            return $this->success('创建成功', ['id' => $log->id]);
        } catch (\Exception $e) {
            return $this->fail('创建失败：' . $e->getMessage());
        }
    }
    
    /**
     * @notes 更新日志记录
     * @param int $id
     * @return Json
     * @author 精解析题库
     * @date 2026/01/16
     */
    public function update(int $id): Json
    {
        $params = $this->request->post();
        
        $log = SubscribeSendLog::find($id);
        if (!$log) {
            return $this->fail('记录不存在');
        }
        
        try {
            if (isset($params['user_id'])) {
                $log->user_id = $params['user_id'];
            }
            if (isset($params['type'])) {
                $log->type = $params['type'];
            }
            if (isset($params['template_id'])) {
                $log->template_id = $params['template_id'];
            }
            if (isset($params['data'])) {
                $log->data = $params['data'];
            }
            if (isset($params['send_status'])) {
                $log->send_status = $params['send_status'];
            }
            if (isset($params['retry_times'])) {
                $log->retry_times = $params['retry_times'];
            }
            if (isset($params['send_time'])) {
                $log->send_time = $params['send_time'];
            }
            if (isset($params['error_code'])) {
                $log->error_code = $params['error_code'];
            }
            if (isset($params['error_msg'])) {
                $log->error_msg = $params['error_msg'];
            }
            if (isset($params['result'])) {
                $log->result = $params['result'];
            }
            if (isset($params['tenant_id'])) {
                $log->tenant_id = $params['tenant_id'];
            }
            $log->save();
            
            return $this->success('更新成功');
        } catch (\Exception $e) {
            return $this->fail('更新失败：' . $e->getMessage());
        }
    }
    
    /**
     * @notes 删除日志记录
     * @param int $id
     * @return Json
     * @author 精解析题库
     * @date 2026/01/16
     */
    public function delete(int $id): Json
    {
        $log = SubscribeSendLog::find($id);
        if (!$log) {
            return $this->fail('记录不存在');
        }
        
        try {
            $log->delete();
            return $this->success('删除成功');
        } catch (\Exception $e) {
            return $this->fail('删除失败：' . $e->getMessage());
        }
    }
    
    /**
     * @notes 批量删除日志记录
     * @return Json
     * @author 精解析题库
     * @date 2026/01/16
     */
    public function batchDelete(): Json
    {
        $ids = $this->request->post('ids', []);
        
        if (empty($ids) || !is_array($ids)) {
            return $this->fail('请选择要删除的记录');
        }
        
        try {
            SubscribeSendLog::destroy($ids);
            return $this->success('批量删除成功');
        } catch (\Exception $e) {
            return $this->fail('批量删除失败：' . $e->getMessage());
        }
    }
}