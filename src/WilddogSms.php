<?php

namespace luoyy\WilddogSmsSdk;

use luoyy\WilddogSmsSdk\Core as Request;
use luoyy\WilddogSmsSdk\WilddogSmsCodeMap;
use luoyy\WilddogSmsSdk\WilddogSmsConf;
use \Exception;

/**
 * 野狗短信Api
 * @Author:zishang520
 * @Email:zishang520@gmail.com
 * @HomePage:http://www.luoyy.com
 * @version: 1.0 beta
 */

class WilddogSms extends WilddogSmsConf
{
    /**
     * @var mixed
     */
    private static $init = null;
    /**
     * @var 发送验证码短信地址
     */
    private static $SEND_CODE_URL = 'https://api.wilddog.com/sms/v1/%s/code/send';
    /**
     * @var 发送通知类短信地址
     */
    private static $SEND_NOTIFY_URL = 'https://api.wilddog.com/sms/v1/%s/notify/send';
    /**
     * @var 校验验证码地址
     */
    private static $CHECK_CODE_URL = 'https://api.wilddog.com/sms/v1/%s/code/check';
    /**
     * @var 查询发送状态地址
     */
    private static $GET_STATUS_URL = 'https://api.wilddog.com/sms/v1/%s/status';
    /**
     * @var 查询账户余额地址
     */
    private static $GET_BALANCE_URL = 'https://api.wilddog.com/sms/v1/%s/getBalance';

    /**
     * @var mixed
     */
    private static $mobile = null;

    /**
     * @var mixed
     */
    private static $templateId = null;
    /**
     * @var mixed
     */
    private static $params = [];

    /**
     * @var mixed
     */
    private static $request;
    /**
     * @var rrid
     */
    private static $rrid;
    /**
     * [$APPID default appid]
     * @var [type]
     */
    private static $APPID = self::APPID;
    /**
     * [$APPID default sign_key]
     * @var [type]
     */
    private static $SIGN_KEY = self::SIGN_KEY;
    /**
     * [__construct 构建函数]
     * @Author    ZiShang520@gmail.com
     * @DateTime  2017-06-07T09:52:25+0800
     * @copyright (c)                      ZiShang520 All Rights Reserved
     */
    public function __construct($mobile = null, $templateId = null, array $params = [])
    {
        self::$mobile = $mobile;
        self::$templateId = $templateId;
        self::$params = $params;
    }
    /**
     * [Request Request]
     * @Author    ZiShang520@gmail.com
     * @DateTime  2017-06-09T10:59:36+0800
     * @copyright (c)                      ZiShang520 All Rights Reserved
     */
    private static function Request()
    {
        if (is_null(self::$request)) {
            self::$request = new Request(true);
        }
        return self::$request;
    }

    /**
     * [setConf 设置配置]
     * @Author    ZiShang520@gmail.com
     * @DateTime  2017-06-09T11:17:06+0800
     * @copyright (c)                      ZiShang520 All           Rights Reserved
     * @param     [type]                   $appid     [description]
     * @param     [type]                   $sign_key  [description]
     */
    public static function setConf($appid = null, $sign_key = null)
    {
        if (empty($appid) && empty($sign_key)) {
            throw new Exception("Appid and Sign_key can not be empty", 1);
        }
        self::$APPID = $appid;
        self::$SIGN_KEY = $sign_key;
    }
    /**
     * [time 获取毫秒时间戳]
     * @Author    ZiShang520@gmail.com
     * @DateTime  2017-06-07T10:30:42+0800
     * @copyright (c)                      ZiShang520    All Rights Reserved
     * @return    [type]                   [description]
     */
    private static function time()
    {
        return round(microtime(true) * 1000);
    }

    /**
     * @param array $data
     */
    private static function sign_str(array $data)
    {
        // 排序
        ksort($data);
        // 生成签名字符串
        return hash("sha256", urldecode(vsprintf('%s&%s', [http_build_query($data), self::$SIGN_KEY])));
    }
    /**
     * [sendCode 发送验证码短信]
     * @Author    ZiShang520@gmail.com
     * @DateTime  2017-06-07T12:06:28+0800
     * @copyright (c)                      ZiShang520  All           Rights Reserved
     * @param     [type]                   $mobile     [手机号码]
     * @param     [type]                   $templateId [模板id]
     * @param     [type]                   $params     [参数]
     * @return    [type]                               [返回数据]
     */
    public static function sendCode($mobile = null, $templateId = null, array $params = [])
    {
        if (!is_null($mobile)) {
            self::$mobile = $mobile;
        }
        if (is_null(self::$mobile)) {
            return ['status' => false, 'message' => '手机号不能为空'];
            // throw new Exception("The phone number can not be empty", 1);
        }
        if (!is_null($templateId)) {
            self::$templateId = $templateId;
        }
        if (is_null(self::$templateId)) {
            return ['status' => false, 'message' => '模板ID不能为空'];
            // throw new Exception("Template ID can not be empty", 1);
        }
        $data = ['mobile' => self::$mobile, 'templateId' => self::$templateId, 'timestamp' => self::time()];
        // 判断是否需要添加自定义参数
        if (!empty(self::$params) && is_array(self::$params)) {
            $data['params'] = json_encode(self::$params);
        }
        // 判断是否需要添加自定义参数覆盖
        if (!empty($params) && is_array($params)) {
            $data['params'] = json_encode($params);
        }
        $data['signature'] = self::sign_str($data);
        $response = self::Request()->post(sprintf(self::$SEND_CODE_URL, self::$APPID), $data);
        if ($body = self::Request()->json_decode($response->body)) {
            if (array_key_exists('errcode', $body)) {
                return ['status' => false, 'message' => WilddogSmsCodeMap::getError($body['errcode']), 'body' => $response->body, 'request' => $data];
            }
            if (array_key_exists('status', $body) && $body['status'] == 'ok') {
                self::$rrid = $body['data']['rrid'];
                return ['status' => true, 'rrid' => $body['data']['rrid'], 'message' => $body['status']];
            }
        }
        if ($response->http_code != 200) {
            return ['status' => false, 'message' => WilddogSmsCodeMap::getHttpCode($response->http_code), 'body' => $response->body, 'request' => $data];
        }
    }
    /**
     * [send 发送通知类短信]
     * @Author    ZiShang520@gmail.com
     * @DateTime  2017-06-07T13:02:16+0800
     * @copyright (c)                      ZiShang520  All           Rights Reserved
     * @param     array                    $mobiles    [description]
     * @param     [type]                   $templateId [description]
     * @param     array                    $params     [description]
     * @return    [type]                               [description]
     */
    public static function send(array $mobiles = [], $templateId = null, array $params = [])
    {
        if (!empty($mobiles) && is_array($mobiles)) {
            self::$mobile = $mobiles;
        }
        if (empty(self::$mobile) || !is_array(self::$mobile)) {
            return ['status' => false, 'message' => '手机号不能为空并且必须是一个数组'];
            // throw new Exception("The phone number can not be empty and must be an array", 1);
        }
        if (!is_null($templateId)) {
            self::$templateId = $templateId;
        }
        if (is_null(self::$templateId)) {
            return ['status' => false, 'message' => '模板ID不能为空'];
            // throw new Exception("Template ID can not be empty", 1);
        }
        $data = ['mobiles' => json_encode(self::$mobile), 'templateId' => self::$templateId, 'timestamp' => self::time()];
        // 判断是否需要添加自定义参数
        if (!empty($params) && is_array($params)) {
            self::$params = $params;
        }
        // 处理不存在
        if (empty(self::$params) || !is_array(self::$params)) {
            return ['status' => false, 'message' => '自定义参数必须是数组并且不能为空'];
            // throw new Exception("Required String parameter params", 1);
        }
        $data['params'] = json_encode(self::$params);
        $data['signature'] = self::sign_str($data);
        $response = self::Request()->post(sprintf(self::$SEND_NOTIFY_URL, self::$APPID), $data);
        if ($body = self::Request()->json_decode($response->body)) {
            if (array_key_exists('errcode', $body)) {
                return ['status' => false, 'message' => WilddogSmsCodeMap::getError($body['errcode']), 'body' => $response->body, 'request' => $data];
            }
            if (array_key_exists('status', $body) && $body['status'] == 'ok') {
                self::$rrid = $body['data']['rrid'];
                return ['status' => true, 'rrid' => $body['data']['rrid'], 'message' => $body['status']];
            }
        }
        if ($response->http_code != 200) {
            return ['status' => false, 'message' => WilddogSmsCodeMap::getHttpCode($response->http_code), 'body' => $response->body, 'request' => $data];
        }
    }

    /**
     * [checkCode 检测验证码是否有效]
     * @Author    ZiShang520@gmail.com
     * @DateTime  2017-06-07T14:37:46+0800
     * @copyright (c)                      ZiShang520 All           Rights Reserved
     * @param     string|null              $code      [验证码]
     * @param     [type]                   $mobile    [手机号，为空调用初始化的]
     * @return    [type]                              [返回状态]
     */
    public static function checkCode($code = null, $mobile = null)
    {
        if (!is_null($mobile)) {
            self::$mobile = $mobile;
        }
        if (is_null(self::$mobile) || !is_string(self::$mobile)) {
            return ['status' => false, 'message' => '手机号码不能为空并且必须是字符串'];
            // throw new Exception("The verification code cannot be empty and must be a string", 1);
        }
        if (is_null($code)) {
            return ['status' => false, 'message' => '验证码不能为空'];
            // throw new Exception("The verification code cannot be empty", 1);
        }
        $data = ['code' => $code, 'mobile' => self::$mobile, 'timestamp' => self::time()];
        $data['signature'] = self::sign_str($data);
        $response = self::Request()->post(sprintf(self::$CHECK_CODE_URL, self::$APPID), $data);
        if ($body = self::Request()->json_decode($response->body)) {
            if (array_key_exists('errcode', $body)) {
                return ['status' => false, 'message' => WilddogSmsCodeMap::getError($body['errcode']), 'body' => $response->body, 'request' => $data];
            }
            if (array_key_exists('status', $body) && $body['status'] == 'ok') {
                return ['status' => true, 'message' => $body['status']];
            }
        }
        if ($response->http_code != 200) {
            return ['status' => false, 'message' => WilddogSmsCodeMap::getHttpCode($response->http_code), 'body' => $response->body, 'request' => $data];
        }
    }

    /**
     * [status 获取发送状态]
     * @Author    ZiShang520@gmail.com
     * @DateTime  2017-06-07T14:41:32+0800
     * @copyright (c)                      ZiShang520 All           Rights Reserved
     * @param     [type]                   $rrid      [rrid，为空使用之前发送后生成的]
     * @return    [type]                              [description]
     */
    public static function getStatus($rrid = null)
    {
        if (!is_null($rrid)) {
            self::$rrid = $rrid;
        }
        if (is_null(self::$rrid)) {
            return ['status' => false, 'message' => 'RRID不能为空'];
            // throw new Exception("RRID can not be empty", 1);
        }
        $data = ['rrid' => self::$rrid];
        $data['signature'] = self::sign_str($data);
        $response = self::Request()->request(['url' => sprintf(self::$GET_STATUS_URL, self::$APPID), 'data' => $data]);
        if ($body = self::Request()->json_decode($response->body)) {
            if (array_key_exists('status', $body) && $body['status'] == 'ok') {
                if (!empty($body['data']) && is_array($body['data'])) {
                    // 替换状态字符串
                    foreach ($body['data'] as $key => &$value) {
                        $body['data'][$key]['deliveryStatus'] = WilddogSmsCodeMap::getStatusCode($body['data'][$key]['deliveryStatus']);
                    }
                }
                return ['status' => true, 'message' => $body['status'], 'data' => $body['data']];
            }
        }
        if ($response->http_code != 200) {
            return ['status' => false, 'message' => WilddogSmsCodeMap::getHttpCode($response->http_code), 'body' => $response->body, 'request' => $data];
        }
    }

    /**
     * [getBalance 获取账户金额，该方法请无公开使用]
     * @Author    ZiShang520@gmail.com
     * @DateTime  2017-06-07T16:03:56+0800
     * @copyright (c)                      ZiShang520    All Rights Reserved
     * @return    [type]                   [返回的data就是具体内容]
     */
    public static function getBalance()
    {
        $data = ['timestamp' => self::time()];
        $data['signature'] = self::sign_str($data);
        $response = self::Request()->request(['url' => sprintf(self::$GET_BALANCE_URL, self::$APPID), 'data' => $data]);
        if ($body = self::Request()->json_decode($response->body)) {
            if (array_key_exists('errcode', $body)) {
                return ['status' => false, 'message' => WilddogSmsCodeMap::getError($body['errcode']), 'body' => $response->body, 'request' => $data];
            }
            if (array_key_exists('status', $body) && $body['status'] == 'ok') {
                return ['status' => true, 'message' => $body['status'], 'data' => $body['data']];
            }
        }
        if ($response->http_code != 200) {
            return ['status' => false, 'message' => WilddogSmsCodeMap::getHttpCode($response->http_code), 'body' => $response->body, 'request' => $data];
        }
    }
}
