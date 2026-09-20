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

namespace app\api\logic;

use app\common\enum\YesNoEnum;
use app\common\logic\BaseLogic;
use app\common\model\article\Article;
use app\common\model\exam\TenantOtherSettings;
use app\common\model\exam\TenantExamRankingSettings;
use app\common\model\exam\TenantIntegralSettings;
use app\common\model\exam\Collect;
use app\common\model\decorate\DecoratePage;
use app\common\model\decorate\DecorateTabbar;
use app\common\service\ConfigService;
use app\common\service\FileService;
use think\facade\Db;


/**
 * index
 * Class IndexLogic
 * @package app\api\logic
 */
class IndexLogic extends BaseLogic
{

    /**
     * @notes 首页数据
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/9/21 19:15
     */
    public static function getIndexData()
    {
        // 装修配置
        $decoratePage = DecoratePage::where(['type' => 1])->findOrEmpty();

        // 首页文章
        $field = [
            'id', 'title', 'desc', 'abstract', 'image',
            'author', 'click_actual', 'click_virtual', 'create_time'
        ];

        $article = Article::field($field)
            ->where(['is_show' => 1])
            ->order(['id' => 'desc'])
            ->limit(20)->append(['click'])
            ->hidden(['click_actual', 'click_virtual'])
            ->select()->toArray();

        return [
            'page' => $decoratePage,
            'article' => $article
        ];
    }


    /**
     * @notes 获取政策协议
     * @param string $type
     * @return array
     * @author 段誉
     * @date 2022/9/20 20:00
     */
    public static function getPolicyByType(string $type)
    {
        return [
            'title' => ConfigService::get('agreement', $type . '_title', ''),
            'content' => ConfigService::get('agreement', $type . '_content', ''),
        ];
    }


    /**
     * @notes 装修信息
     * @param $id
     * @return array
     * @author 段誉
     * @date 2022/9/21 18:37
     */
    public static function getDecorate($type)
    {
        return DecoratePage::where(['type' => $type])->field(['type', 'name', 'data', 'meta'])
            ->findOrEmpty()->toArray();
    }


    /**
     * @notes 获取配置
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/9/21 19:38
     */
    public static function getConfigData()
    {
        // 底部导航
        $tabbar = DecorateTabbar::getTabbarLists();
        // 导航颜色
        $style = ConfigService::get('tabbar', 'style', config('project.decorate.tabbar_style'));
        // 登录配置
        $loginConfig = [
            // 登录方式
            'login_way' => ConfigService::get('login', 'login_way', config('project.login.login_way')),
            // 注册强制绑定手机
            'coerce_mobile' => ConfigService::get('login', 'coerce_mobile', config('project.login.coerce_mobile')),
            // 政策协议
            'login_agreement' => ConfigService::get('login', 'login_agreement', config('project.login.login_agreement')),
            // 第三方登录 开关
            'third_auth' => ConfigService::get('login', 'third_auth', config('project.login.third_auth')),
            // 微信授权登录
            'wechat_auth' => ConfigService::get('login', 'wechat_auth', config('project.login.wechat_auth')),
            // qq授权登录
            'qq_auth' => ConfigService::get('login', 'qq_auth', config('project.login.qq_auth')),
        ];
        // 网址信息
        $website = [
            'h5_favicon' => FileService::getFileUrl(ConfigService::get('website', 'h5_favicon')),
            'shop_name' => ConfigService::get('website', 'shop_name'),
            'shop_logo' => FileService::getFileUrl(ConfigService::get('website', 'shop_logo')),
        ];
        // H5配置
        $webPage = [
            // 渠道状态 0-关闭 1-开启
            'status' => ConfigService::get('web_page', 'status', 1),
            // 关闭后渠道后访问页面 0-空页面 1-自定义链接
            'page_status' => ConfigService::get('web_page', 'page_status', 0),
            // 自定义链接
            'page_url' => ConfigService::get('web_page', 'page_url', ''),
            'url' => request()->domain() . '/mobile'
        ];

        // 备案信息
        $copyright = ConfigService::get('copyright', 'config', []);

        return [
            'domain' => FileService::getFileUrl(),
            'style' => $style,
            'tabbar' => $tabbar,
            'login' => $loginConfig,
            'website' => $website,
            'webPage' => $webPage,
            'version'=> config('project.version'),
            'copyright' => $copyright,
        ];
    }

    /**
     * @description 数据查询
     * @param array $params
     * @return array
     */
    public static function dataQuery(array $params): array
    {
        $pageNo = (int)(isset($params['page_no']) ? $params['page_no'] : 1);
        $pageSize = (int)(isset($params['page_size']) ? $params['page_size'] : 20);
        $pageSize = min($pageSize, 20);
        $items = Db::name('data_search')
            ->where(function ($query) use ($params) {
                if (!empty($params['data_type'])) {
                    $query->whereRaw("data_type COLLATE utf8mb4_general_ci = ?", [$params['data_type']]);
                }
                if (!empty($params['title'])) {
                    $query->whereLike('title', '%' . $params['title'] . '%');
                }
            })->order('publish_time', 'desc')
            ->paginate([
                'list_rows' => $pageSize,
                'page'      => $pageNo
            ]);
        $dataArray = $items->items();
        foreach ($dataArray as &$value) {
            $value['publish_time'] = date("Y-m-d", $value['publish_time']);
            switch ($value['data_type']) {
                case 'article':
                    $value['data_type_title'] = '资讯文章';
                    break;
                case 'exam_library':
                    $value['data_type_title'] = '在线题库';
                    break;
                case 'resource':
                    $value['data_type_title'] = '资料文件';
                    break;
            }
        }
        return [
            'lists'     => $dataArray,
            'page_no'   => $items->currentPage(),
            'page_size' => $pageSize,
            'count'     => $items->total(),
            'extend'    => []
        ];
    }

    /**
     * @description 获取排行榜设置
     * @return array
     * @author 精解析题库
     * @date 2025/08/26 09:13
     */
    public static function rankSettings(): array
    {
        // 从缓存中获取排行榜设置
        $rankSettings = \think\facade\Cache::get('tenant_rank_settings_' . request()->tenantId);
        
        // 如果缓存中不存在排行榜设置，从数据库中查询当前租户的排行榜设置
        if (!$rankSettings) {
            $model = TenantExamRankingSettings::where('tenant_id', '=', request()->tenantId)
                ->find();
            $data = $model ? $model->toArray() : [];
            
            // 将排行榜设置存入全局变量
            config('tenant_rank_settings_' . request()->tenantId, $data);
            \think\facade\Cache::set('tenant_rank_settings_' . request()->tenantId, $data);
        } else {
            // 确保缓存中的数据也是数组格式
            $data = is_array($rankSettings) ? $rankSettings : $rankSettings->toArray();
        }
        
        // 确保返回数组格式
       return $data;
    }

    /**
     * @description 获取其他设置
     * @return array
     * @author 精解析题库
     * @date 2025/08/26 09:13
     */
    public static function otherSettings(): array
    {
        // 从缓存中获取其他设置
        $otherSettings = \think\facade\Cache::get('tenant_other_settings_' . request()->tenantId);
        
        // 如果缓存中不存在其他设置，从数据库中查询当前租户的其他设置
        if (!$otherSettings) {
            $model = TenantOtherSettings::where('tenant_id', '=', request()->tenantId)   
                ->find();
            $data = $model ? $model->toArray() : [];
            
            // 将其他设置存入全局变量
            config('tenant_other_settings_' . request()->tenantId, $data);
            \think\facade\Cache::set('tenant_other_settings_' . request()->tenantId, $data);
        } else {
            // 确保缓存中的数据也是数组格式
            $data = is_array($otherSettings) ? $otherSettings : $otherSettings->toArray();
        }
        
        // 确保返回数组格式
       return $data;
    }

    /**
     * @description 获取积分设置
     * @return array
     * @author 精解析题库
     * @date 2025/08/26 09:13
     */
    public static function integralSettings(): array
    {
        // 从缓存中获取积分设置
        $integralSettings = \think\facade\Cache::get('tenant_integral_settings_' . request()->tenantId);
        
        // 如果缓存中不存在积分设置，从数据库中查询当前租户的积分设置
        if (!$integralSettings) {
            $model = TenantIntegralSettings::where('tenant_id', '=', request()->tenantId)->find();
            $data = $model ? $model->toArray() : [];
            
            // 将积分设置存入全局变量
            config('tenant_integral_settings_' . request()->tenantId, $data);
            \think\facade\Cache::set('tenant_integral_settings_' . request()->tenantId, $data);
        } else {
            // 确保缓存中的数据也是数组格式
            $data = is_array($integralSettings) ? $integralSettings : $integralSettings->toArray();
        }
        
        // 确保返回数组格式
        return $data;
    }

    /**
     * @description 检查模板是否存在
     * @param string $templateId 模板ID
     * @return bool
     * @author 精解析题库
     * @date 2026/01/12
     */
    private static function checkTemplateExists($templateId): bool
    {
        $templates = self::getSubscribeTemplates();
        
        // 检查模板是否存在于配置中
        if (empty($templates)) {
            return false;
        }
        
        // 支持对象数组格式：[{template_id: 'template_id1'}, {template_id: 'template_id2'}]
        foreach ($templates as $template) {
            if (is_array($template) && isset($template['template_id']) && $template['template_id'] === $templateId) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * @description 获取模板订阅设置
     * @param array $params 请求参数
     * @return array
     * @author 精解析题库
     * @date 2025/08/26 09:13
     */
    public static function templateSubscribe(array $params): array
    {
        // 检查是否提供了template_id参数
        if (isset($params['template_id'])) {
            $templateId = $params['template_id'];
            // 检查模板是否存在
            if (!self::checkTemplateExists($templateId)) {
                // 模板不存在，返回错误信息
                return [
                    'code' => -10003,
                    'msg' => '未添加订阅消息模板',
                    'data' => []
                ];
            }
        }
        
        $data = Db::name('tenant_exam_other_settings')
            ->where('tenant_id', '=', request()->tenantId)
            ->field('template_id')
            ->find();
        
        return [
            'code' => 0,
            'msg' => 'success',
            'data' => $data ?: []
        ];
    }
    
    /**
     * @description 获取订阅模板ID列表
     * @return array
     * @author 精解析题库
     * @date 2025/08/26 09:13
     */
    public static function getSubscribeTemplates(): array
    {
        $data = Db::name('tenant_exam_other_settings')
            ->where('tenant_id', '=', request()->tenantId)
            ->field('template_id')
            ->find();
        
        if (!$data || empty($data['template_id'])) {
            return [];
        }
        
        // 将字符串格式的模板ID转换为数组
        $templateData = json_decode($data['template_id'], true);
        
        // 处理单个模板ID的情况
        if (!is_array($templateData)) {
            // 如果不是数组，可能是单个模板ID字符串
            return [];
        }
        
        // 转换为前端期望的格式：[{template_id: 'template_id1'}, {template_id: 'template_id2'}]
        $result = [];
        foreach ($templateData as $item) {
            // 检查是否是对象且包含template_id字段
            if (is_array($item) && isset($item['template_id']) && !empty($item['template_id'])) {
                $result[] = ['template_id' => $item['template_id']];
            } else if (!is_array($item) && !empty($item)) {
                // 兼容旧格式：直接是模板ID字符串
                $result[] = ['template_id' => $item];
            }
        }
        
        return $result;
    }
    
    /**
     * @description 处理模板订阅错误
     * @param int $errorCode 错误码
     * @param string $errorMsg 错误信息
     * @return array
     * @author 精解析题库
     * @date 2026/01/12
     */
    public static function handleSubscribeError($errorCode, $errorMsg): array
    {
        // 错误码映射
        $errorMap = [
            '-10001' => '系统错误',
            '-10002' => '内容安全校验不通过',
            '-10003' => '未添加订阅消息模板',
            '-10004' => '用户拒收此模板',
            '-10005' => '消息下发过于频繁被拦截',
            '43101' => '用户拒绝接受消息',
            '43102' => '用户接收的消息数达到上限',
            '43103' => '无效的模板ID',
            '40003' => '无效的openid'
        ];
        
        $msg = $errorMap[$errorCode] ?? $errorMsg;
        
        // 如果是用户拒收模板，记录到数据库
        if ($errorCode == '-10004' || $errorCode == '43101') {
            // 这里需要根据实际情况获取template_id和user_id
            // 假设params中包含这些信息
            $templateId = isset($params['template_id']) ? $params['template_id'] : '';
            $userId = isset($params['user_id']) ? $params['user_id'] : 0;
            
            if (!empty($templateId) && $userId > 0) {
                // 获取当前租户ID，默认值为1
                $tenantId = 1;
                // 调用MessageService的handleUnsubscribe方法，传递tenant_id
                \app\common\logic\user\MessageService::handleUnsubscribe($userId, $templateId, $tenantId);
            }
        }
        
        return [
            'code' => $errorCode,
            'msg' => $msg
        ];
    }

        /**
     * @notes 加入收藏
     * @param $userId
     * @param $qid
     * @author 精解析题库
     * @date 2025/08/26 09:13
     */
    public static function addCollect($qid, $userId, $type)
    {
        $where = ['user_id' => $userId, 'qid' => $qid];
        $collect = Collect::where($where)->findOrEmpty();
        if ($collect->isEmpty()) {
            Collect::create([
                'user_id' => $userId,
                'qid' => $qid,
                'type' => $type,
                'status' => YesNoEnum::YES
            ]);
        } else {
            Collect::update([
                'status' => YesNoEnum::YES
            ], ['id' => $collect['id']]);
        }
    }


    /**
     * @notes 取消收藏
     * @param $qid
     * @param $userId
     * @author 精解析题库
     * @date 2025/08/26 09:13
     */
    public static function cancelCollect($qid, $userId)
    {
        Collect::update(['status' => YesNoEnum::NO], [
            'user_id' => $userId,
            'qid' => $qid,
            'status' => YesNoEnum::YES
        ]);
    }

}