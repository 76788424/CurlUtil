<?php
/**
 * Class CurlUtil
 * Time 2014.8.15
 */
class CurlUtil
{
    //单例
    private static $instance = null;
    //curl 句柄
    private static $ch = null;
    //默认配置
    private static $defaults = [];
    //配置条件
    private static $options = [];
    //HTTP 状态码
    private static $httpCode = 0;
    //请求结果
    private static $response = null;
    //CURL信息
    private static $curlInfo = [];
    //头信息
    private static $header = null;

    /**
     * CurlUtil constructor.
     * @param $options
     */
    private function __construct($options)
    {
        //默认配置在此设置
        self::$defaults['CURLOPT_CONNECTTIMEOUT'] = 30;
        self::$defaults['CURLOPT_HEADER'] = 0;
        self::$defaults['CURLOPT_RETURNTRANSFER'] = 1;
        //合并配置参数
        self::$options = array_merge(self::$defaults, $options);
        //初始化
        self::init();
    }

    /**
     * 单例实例化
     * @param array $options
     * @return CurlUtil|null
     */
    public static function getInstance($options = [])
    {
        self::$instance or self::$instance = new self($options);
        return self::$instance;
    }

    /**
     * 初始化，开启句柄
     */
    private static function init()
    {
        $options = [];
        self::$ch = curl_init();
        foreach (self::$options as $k => $v) {
            $options[constant($k)] = $v;
        }
        curl_setopt_array(self::$ch, $options);    //批量配置设置
    }

    /**
     * 发送请求获取报文
     * @return bool|null|string
     */
    private static function request()
    {
        self::$response = self::toUtf8(curl_exec(self::$ch));
        if (curl_errno(self::$ch)) {
            self::sendError(curl_error(self::$ch));
            return false;
        }
        if (self::$options['CURLOPT_HEADER']) {    //开启头信息
            $headerSize = curl_getinfo(self::$ch, CURLINFO_HEADER_SIZE);
            self::$header = substr(self::$response, 0, $headerSize);    //存储头信息
            return substr(self::$response, $headerSize);    //返回body
        }
        return self::$response;
    }

    /**
     * GET 操作
     * @param $url
     * @param string $query
     * @return bool|null|string
     */
    public static function get($url, $query = '')
    {
        if (!empty($query)) {
            $url .= strpos($url, '?') === false ? '?' : '&';
            $url .= is_array($query) ? http_build_query($query) : $query;
        }
        curl_setopt(self::$ch, CURLOPT_HTTPGET, 1);    //GET
        curl_setopt(self::$ch, CURLOPT_URL, $url);
        return self::request();
    }

    /**
     * POST 操作，支持 form 和 json 两种形式
     * @param $url
     * @param string $query
     * @param bool $isJson
     * @return bool|null|string
     */
    public static function post($url, $query = '', $isJson = false)
    {
        if (!empty($query)) {
            curl_setopt(self::$ch, CURLOPT_POST, 1);    //POST
            curl_setopt(self::$ch, CURLOPT_URL, $url);
            if (!$isJson) {
                //form
                curl_setopt(self::$ch, CURLOPT_HTTPHEADER, ["Content-Type: application/x-www-form-urlencoded; charset=utf-8"]);
            } else {
                //json
                curl_setopt(self::$ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json; charset=utf-8"]);
            }
            curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
        } else {
            return self::get($url);
        }
        return self::request();
    }

    /**
     * SSL 安全连接，链式操作
     * @return null
     */
    public static function ssl()
    {
        //是否检测证书，默认1。从证书中检查SSL加密算法是否存在，
        //0-不检查，1-检查证书中是否有CN(common name)字段，2-在1的基础上校验当前的域名是否与CN匹配
        curl_setopt(self::$ch, CURLOPT_SSL_VERIFYPEER, 0);
        //[0、2]，1貌似不支持，经常被开发者用错，所以去掉了，默认2
        curl_setopt(self::$ch, CURLOPT_SSL_VERIFYHOST, 0);
        return self::$instance;
    }

    /**
     * 获取报文头
     * @return null
     */
    public static function getHeader()
    {
        return self::$header;
    }

    /**
     * 获取 HTTP 状态码
     * @return int|mixed
     */
    public static function getHttpCode()
    {
        if (is_resource(self::$ch)) {
            self::$httpCode =  curl_getinfo(self::$ch, CURLINFO_HTTP_CODE);
        }
        return self::$httpCode;
    }

    /**
     * 获取 CURL 信息
     * @return array|mixed
     */
    public static function getCurlInfo()
    {
        if (is_resource(self::$ch)) {
            self::$curlInfo =  curl_getinfo(self::$ch);
        }
        return self::$curlInfo;
    }

    /**
     * 将报文转为 UTF-8 编码
     * @param $str
     * @return string
     */
    private static function toUtf8($str)
    {
        if (json_encode($str) == 'null') {
            return iconv('GB2312', 'UTF-8//IGNORE', $str);
        }
        return $str;
    }

    /**
     * 打印错误
     * @param $errMsg
     */
    private static function sendError($errMsg)
    {
        echo "<br/>ERROR_INFO:{$errMsg}<br/>";
    }

    /**
     * 关闭句柄
     */
    private static function close()
    {
        if (is_resource(self::$ch)) {
            curl_close(self::$ch);
        }
    }

    /**
     * 防止克隆对象
     */
    private function __clone()
    {
        //防止 clone 函数克隆对象，破坏单例模式
    }

    /**
     * 析构
     */
    public function __destruct()
    {
        self::close();
    }

}
