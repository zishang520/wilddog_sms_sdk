<?php
namespace luoyy\WilddogSmsSdk;

use luoyy\WilddogSmsSdk\JSObject;
use \StdClass;

/**
 * Request内核
 * @Author:zishang520
 * @Email:zishang520@gmail.com
 * @HomePage:http://www.luoyy.com
 * @version: 1.0 beta
 */
class Core
{
    /**
     * @var array
     */
    private $header = [
        'Cache-Control' => 'Cache-control: no-cache, no-store',
        'Upgrade-Insecure-Requests' => 'Upgrade-Insecure-Requests: 1',
        'Accept' => 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language' => 'Accept-Language: zh-CN,zh;q=0.8,en;q=0.6',
        'User-Agent' => 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.101 Safari/537.36',
        'Dnt' => 'Dnt: 1',
        'Accept-encoding' => 'Accept-encoding: gzip',
    ];
    /**
     * @var mixed
     */
    public $secure = false;
    /**
     * @var string
     */
    private $cookie = false;
    /**
     * @var string
     */
    private $cookie_file = 'cookie.ini';

    /**
     * @param $secure
     */
    public function __construct($secure = false)
    {
        $this->secure = $secure;
    }
    /**
     * @param $url
     * @param $post
     * @param array $header
     * @param $cookie
     * @return mixed
     */
    protected function curl($option = [])
    {
        $option = array_merge([
            'url' => null,
            'method' => 'GET',
            'data' => null,
            'header' => [],
        ], $option);
        $header = array_merge($this->header, $option['header']);
        if (strtoupper($option['method']) == 'GET') {
            if (!is_null($option['data']) && $option['data'] != '') {
                $option['url'] = vsprintf('%s?%s', [$option['url'], is_array($option['data']) ? http_build_query($option['data']) : $option['data']]);
            }
        }
        $ch = curl_init(); //初始化curl
        curl_setopt_array($ch,
            [
                CURLOPT_URL => $option['url'], //需要请求的地址
                // CURLOPT_PROXY => 'http://127.0.0.1:8080', // 不支持https
                CURLOPT_HTTPHEADER => $header, //设置header头
                CURLOPT_AUTOREFERER => true, // 自动设置跳转地址
                CURLOPT_FOLLOWLOCATION => true, //开启重定向
                CURLOPT_TIMEOUT => 30, //设置超时时间
                CURLOPT_RETURNTRANSFER => true, //设定是否显示头信息
                CURLOPT_HEADER => false, //设定是否输出页面内容
                CURLOPT_NOBODY => false, //是否设置为不显示html的body
                CURLOPT_ENCODING => "gzip", // Set Accept-encoding
            ]
        );
        if (strtoupper($option['method']) == 'POST' && !is_null($option['data'])) {
            $post = is_array($option['data']) ? http_build_query($option['data']) : $option['data'];
            curl_setopt_array($ch,
                [
                    CURLOPT_POST => true, //post提交方式
                    CURLOPT_POSTFIELDS => $post,
                ]
            );
        }
        if ($this->secure) {
            curl_setopt_array($ch,
                [
                    CURLOPT_SSL_VERIFYPEER => true, // 只信任CA颁布的证书
                    CURLOPT_CAINFO => __DIR__ . '/cacert.pem', // CA根证书（用来验证的网站证书是否是CA颁布）
                    CURLOPT_SSL_VERIFYHOST => 2,
                ]
            ); // 检查证书中是否设置域名，并且是否与提供的主机名匹配
        }
        if ($this->cookie) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file); //存储cookie信息
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file); // use cookie
        }
        $data = new StdClass(); //初始化空数组
        $data->body = curl_exec($ch);
        $data->header = curl_getinfo($ch);
        $data->http_code = $data->header['http_code'];
        curl_close($ch);
        return $data;
    }
    /**
     * @param $status
     * @param false $cookie_file
     */
    public function cookie($status = false, $cookie_file = 'cookie.blob')
    {
        $this->cookie = $status;
        $this->cookie_file = $cookie_file;
    }
    /**
     * @param $url
     * @param array $header
     * @param $cookie
     * @return mixed
     */
    public function get($url, $data = null, $header = [])
    {
        return $this->curl(['url' => $url, 'method' => 'GET', 'data' => $data, 'header' => $header]);
    }
    /**
     * @param $url
     * @param $post
     * @param array $header
     * @param $cookie
     * @return mixed
     */
    public function post($url, $data = null, $header = [])
    {
        return $this->curl(['url' => $url, 'method' => 'POST', 'data' => $data, 'header' => $header]);
    }
    /**
     * @param $str
     * @param $delete
     * @return mixed
     */
    protected function jsonp2json($str = '', $delete = '')
    {
        if (empty($str) || empty($delete)) {
            return false;
        }
        $per = '/' . $delete . '\((.*?)\)/sim';
        if (!preg_match($per, $str, $match)) {
            return false;
        }
        if (count($match) != 2) {
            return false;
        }
        return $match[1];
    }

    /**
     * @param $msg
     * @param $status
     */
    public function json_decode($msg = '', $status = true)
    {
        if (!empty($msg)) {
            return JSObject::decode($msg, $status);
        }
        return false;
    }
}
