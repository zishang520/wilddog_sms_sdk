<?php

namespace luoyy\WilddogSmsSdk;

use luoyy\WilddogSmsSdk\Core as Request;
use luoyy\WilddogSmsSdk\WilddogSmsCodeMap;
use luoyy\WilddogSmsSdk\WilddogSmsConf;

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
    private static $SEND_CODE_URL;
    /**
     * @var 发送通知类短信地址
     */
    private static $SEND_NOTIFY_URL;
    /**
     * @var 校验验证码地址
     */
    private static $CHECK_CODE_URL;
    /**
     * @var 查询发送状态地址
     */
    private static $GET_STATUS_URL;
    /**
     * @var 查询账户余额地址
     */
    private static $GET_BALANCE_URL;

    /**
     * @var mixed
     */
    private $mobile = null;

    /**
     * @var mixed
     */
    private $templateId = null;
    /**
     * @var mixed
     */
    private $params = [];

    /**
     * @var mixed
     */
    private $request;
    /**
     * @var rrid
     */
    private $rrid;
    /**
     * [__construct 构建函数]
     * @Author    ZiShang520@gmail.com
     * @DateTime  2017-06-07T09:52:25+0800
     * @copyright (c)                      ZiShang520 All Rights Reserved
     */
    public function __construct($mobile = null, $templateId = null, array $params = [])
    {
        self::$SEND_CODE_URL = sprintf('https://api.wilddog.com/sms/v1/%s/code/send', self::APPID);
        self::$SEND_NOTIFY_URL = sprintf('https://api.wilddog.com/sms/v1/%s/notify/send', self::APPID);
        self::$CHECK_CODE_URL = sprintf('https://api.wilddog.com/sms/v1/%s/code/check', self::APPID);
        self::$GET_STATUS_URL = sprintf('https://api.wilddog.com/sms/v1/%s/status', self::APPID);
        self::$GET_BALANCE_URL = sprintf('https://api.wilddog.com/sms/v1/%s/getBalance', self::APPID);
        $this->mobile = $mobile;
        $this->templateId = $templateId;
        $this->params = $params;
        $this->request = new Request(true);
    }
    /**
     * [init 实例化构建函数]
     * @Author    ZiShang520@gmail.com
     * @DateTime  2017-06-07T09:55:36+0800
     * @copyright (c)                      ZiShang520    All Rights Reserved
     * @return    [type]                   [description]
     */
    public static function init($mobile = null, $templateId = null, array $params = [])
    {
        if (is_null(self::$init)) {
            return self::$init = new static($mobile, $templateId, $params);
        }
        return self::$init;
    }

    /**
     * [time 获取毫秒时间戳]
     * @Author    ZiShang520@gmail.com
     * @DateTime  2017-06-07T10:30:42+0800
     * @copyright (c)                      ZiShang520    All Rights Reserved
     * @return    [type]                   [description]
     */
    private function time()
    {
        return round(microtime(true) * 1000);
    }

    /**
     * @param array $data
     */
    private function sign_str(array $data)
    {
        // 排序
        ksort($data);
        // 生成签名字符串
        return hash("sha256", urldecode(vsprintf('%s&%s', [http_build_query($data), self::SIGN_KEY])));
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
    public function sendCode($mobile = null, $templateId = null, array $params = [])
    {
        if (!is_null($mobile)) {
            $this->mobile = $mobile;
        }
        if (is_null($this->mobile)) {
            return ['status' => false, 'message' => '手机号不能为空'];
            // throw new Exception("The phone number can not be empty", 1);
        }
        if (!is_null($templateId)) {
            $this->templateId = $templateId;
        }
        if (is_null($this->templateId)) {
            return ['status' => false, 'message' => '模板ID不能为空'];
            // throw new Exception("Template ID can not be empty", 1);
        }
        $data = ['mobile' => $this->mobile, 'templateId' => $this->templateId, 'timestamp' => $this->time()];
        // 判断是否需要添加自定义参数
        if (!empty($this->params) && is_array($this->params)) {
            $data['params'] = json_encode($this->params);
        }
        // 判断是否需要添加自定义参数覆盖
        if (!empty($params) && is_array($params)) {
            $data['params'] = json_encode($params);
        }
        $data['signature'] = $this->sign_str($data);
        $response = $this->request->post(self::$SEND_CODE_URL, $data);
        if ($body = $this->request->json_decode($response->body)) {
            if (array_key_exists('errcode', $body)) {
                return ['status' => false, 'message' => WilddogSmsCodeMap::getError($body['errcode']), 'body' => $response->body, 'request' => $data];
            }
            if (array_key_exists('status', $body) && $body['status'] == 'ok') {
                $this->rrid = $body['data']['rrid'];
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
    public function send(array $mobiles = [], $templateId = null, array $params = [])
    {
        if (!empty($mobiles) && is_array($mobiles)) {
            $this->mobile = $mobiles;
        }
        if (empty($this->mobile) || !is_array($this->mobile)) {
            return ['status' => false, 'message' => '手机号不能为空并且必须是一个数组'];
            // throw new Exception("The phone number can not be empty and must be an array", 1);
        }
        if (!is_null($templateId)) {
            $this->templateId = $templateId;
        }
        if (is_null($this->templateId)) {
            return ['status' => false, 'message' => '模板ID不能为空'];
            // throw new Exception("Template ID can not be empty", 1);
        }
        $data = ['mobiles' => json_encode($this->mobile), 'templateId' => $this->templateId, 'timestamp' => $this->time()];
        // 判断是否需要添加自定义参数
        if (!empty($params) && is_array($params)) {
            $this->params = $params;
        }
        // 处理不存在
        if (empty($this->params) || !is_array($this->params)) {
            return ['status' => false, 'message' => '自定义参数必须是数组并且不能为空'];
            // throw new Exception("Required String parameter params", 1);
        }
        $data['params'] = json_encode($this->params);
        $data['signature'] = $this->sign_str($data);
        $response = $this->request->post(self::$SEND_NOTIFY_URL, $data);
        if ($body = $this->request->json_decode($response->body)) {
            if (array_key_exists('errcode', $body)) {
                return ['status' => false, 'message' => WilddogSmsCodeMap::getError($body['errcode']), 'body' => $response->body, 'request' => $data];
            }
            if (array_key_exists('status', $body) && $body['status'] == 'ok') {
                $this->rrid = $body['data']['rrid'];
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
    public function checkCode($code = null, $mobile = null)
    {
        if (!is_null($mobile)) {
            $this->mobile = $mobile;
        }
        if (is_null($this->mobile) || !is_string($this->mobile)) {
            return ['status' => false, 'message' => '手机号码不能为空并且必须是字符串'];
            // throw new Exception("The verification code cannot be empty and must be a string", 1);
        }
        if (is_null($code)) {
            return ['status' => false, 'message' => '验证码不能为空'];
            // throw new Exception("The verification code cannot be empty", 1);
        }
        $data = ['code' => $code, 'mobile' => $this->mobile, 'timestamp' => $this->time()];
        $data['signature'] = $this->sign_str($data);
        $response = $this->request->post(self::$CHECK_CODE_URL, $data);
        if ($body = $this->request->json_decode($response->body)) {
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
    public function getStatus($rrid = null)
    {
        if (!is_null($rrid)) {
            $this->rrid = $rrid;
        }
        if (is_null($this->rrid)) {
            return ['status' => false, 'message' => 'RRID不能为空'];
            // throw new Exception("RRID can not be empty", 1);
        }
        $data = ['rrid' => $this->rrid];
        $data['signature'] = $this->sign_str($data);
        $response = $this->request->get(self::$GET_STATUS_URL, $data);
        if ($body = $this->request->json_decode($response->body)) {
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
    public function getBalance()
    {
        $data = ['timestamp' => $this->time()];
        $data['signature'] = $this->sign_str($data);
        $response = $this->request->get(self::$GET_BALANCE_URL, $data);
        if ($body = $this->request->json_decode($response->body)) {
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
