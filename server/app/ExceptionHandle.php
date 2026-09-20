<?php
namespace app;
 
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\InvalidArgumentException;
use think\exception\ValidateException;
use think\Response;
use Throwable;
use ParseError; // 语法错误
use think\db\exception\PDOException; // 数据库连接错误
use think\db\exception\DbException; // 数据库模型访问错误，比如方法不存在
use think\exception\RouteNotFoundException;
use think\exception\ClassNotFoundException;
use \hg\apidoc\exception\HttpException as ApidocHttpException;
 
 
/**
 * 应用异常处理类
 */
class ExceptionHandle extends Handle
{
    /**
     * 不需要记录信息（日志）的异常类列表
     * @var array
     */
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
    ];
 
    /**
     * 记录异常信息（包括日志或者其它方式记录）
     *
     * @access public
     * @param  Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        // 使用内置的方式记录异常日志
        parent::report($exception);
    }
 
    /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param \think\Request   $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        // ①请求异常
        if ($e instanceof \think\exception\HttpException && $request->isAjax()) {
            return self::result($e->getMessage(), [], $e->getStatusCode());
        }

        // ②错误的数据类型 或 缺失参数 413
        if ($e instanceof InvalidArgumentException ) {
            $fileUrlArr = explode(DIRECTORY_SEPARATOR, $e->getFile());
            $data = [
                'err_msg' => $e->getMessage(),
                'file' => $fileUrlArr[count($fileUrlArr) - 1],
                'line' => $e->getLine()
            ];
            return self::result('参数错误',$data,413);
        }
        // ③参数验证错误 422
        if ($e instanceof ValidateException) {
            return self::result('参数验证不通过',$e->getError(), 422);
        }
        // 2.方法、资源未匹配到 404
        if(($e instanceof ClassNotFoundException || $e instanceof RouteNotFoundException) || ($e instanceof HttpException && $e->getStatusCode()==404)){
            $data = [
                'err_msg' => $e -> getMessage(),
            ];
            return self::result('方法或资源未找到，请检查',$data, 404);
        }
        // ④语法错误
        if ($e instanceof ParseError) {
            $fileUrlArr = explode(DIRECTORY_SEPARATOR, $e->getFile());
            $data = [
                'err_msg' => $e->getMessage(),
                'file' => $fileUrlArr[count($fileUrlArr) - 1],
                'line' => $e->getLine()
            ];
            return self::result('服务器异常-语法错误',$data,411);
        }
        // ⑤数据库错误
        if ($e instanceof PDOException || $e instanceof DbException) {
            $fileUrlArr = explode(DIRECTORY_SEPARATOR, $e->getFile());
            $data = [
                'err_msg' => $e->getMessage(),
                'file' => $fileUrlArr[count($fileUrlArr) - 1],
                'line' => $e->getLine()
            ];
            return self::result('服务器异常-数据库错误',$data,412);
        }
 
        // apidoc的异常未被正确响应
// 先引入hg\apidoc\exception\HttpException类
        if ($e instanceof \think\exception\HttpException) {
            $data = [
                'err_msg' => $e->getMessage(),
                'line' => $e->getLine()
            ];
            return self::result('参数错误',$data, $e->getCode());
        }
        // 其他错误交给系统处理
        return parent::render($request, $e);
    }
 
    /**
     * 统一返回处理
    */
   public function result(string $msg = 'error', array $data = [], int $code = 200, string $type = 'json'): Response
    {
        $result = [
            "code" => $code,
            "msg" => $msg,
            "data" => $data
        ];
        // 打印返回数据
        // print_r('result is:' . $result);
        // 调用Response的create方法，指定code可以改变请求的返回状态码
        return Response::create($result, $type)->code($code);
    }
}