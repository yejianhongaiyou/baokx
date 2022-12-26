<?php

namespace BaOkx;

class Binance
{
    /**
     * 周期对应的时间秒数
     * @var array|int[]
     */
    private array $TIME_PERIOD
        = [
            '1h'  => 3600,
            '2h'  => 7200,
            '4h'  => 14400,
            '6h'  => 21600,
            '8h'  => 28800,
            '12h' => 43200,
            '1d'  => 86400,
            '3d'  => 259200,
            '1w'  => 604800
        ];

    /**
     * 是否需要发送时间戳参数
     * @var bool
     */
    private bool $need_timestamp = false;

    /**
     * 构造方法
     * @param $NEED_TIMESTAMP bool 是否需要发送时间戳参数， True 需要；False 不需要
     */
    public function __construct(bool $NEED_TIMESTAMP = false)
    {
        $this->need_timestamp = $NEED_TIMESTAMP;
    }

    /**
     * APIKEY
     * @var string
     */
    private string $API_KEY = 'Euax3oUFFXoGp1AySAh5AuM9xS53W8fJBCFuzTYaq1wl0FBFwRM06TN7mEQ9KLsu';

    /**
     * API秘钥
     * @var string
     */
    private string $API_SECRET = 'JoUJEePD2njvCQLOrlu2QKGBK0eAY9Wm4drLCAFFoxdrjbNohqcWpfgvrY9rwDzj';

    /**
     * Binance请求域名
     * @var string
     */
    private string $DOMAIN = 'https://fapi.binance.com';


    /**
     * 签名
     * @param $params array 参数
     * @param $secret string 秘钥
     * @return string
     */
    private function _signTrue(array $params, string $secret): string
    {
        $str = http_build_query($params);
        return hash_hmac("sha256", $str, $secret);
    }

    /**
     * CURL 请求
     * @param $url string 请求地址
     * @param $header array 请求头参数
     * @param $data array 数据参数
     * @return mixed
     */
    private function curl_request(string $url, array $header = [], array $data = [], bool $delete = false)
    {
        $ch = curl_init();
        //设置浏览器，把参数url传到浏览器的设置当中
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        //以字符串形式返回到浏览器当中
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //禁止https协议验证域名，0就是禁止验证域名且兼容php5.6
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        //禁止https协议验证ssl安全认证证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //判断data是否有数据，如果有data数据传入那么就把curl的请求方式设置为POST请求方式
        if (!empty($data)) {
            if ($delete) {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            } else {
                //设置POST请求方式
                @curl_setopt($ch, CURLOPT_POST, true);
            }
            //设置POST的数据包
            @curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        } else {
            //设置GET请求方式
            @curl_setopt($ch, CURLOPT_POST, false);
        }
        if (!empty($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        //让curl发起请求
        $str = curl_exec($ch);
        //关闭curl浏览器
        curl_close($ch);
        //把请求回来的数据返回
        return json_decode($str, true);
    }

    /**
     * @param $url string
     * @param array $data
     * @param string $method
     * @param $auth_mod string NONE | TRADE, USER_DATA | USER_STREAM, MARKET_DATA
     * @return mixed
     */
    public function sendBinanceApi(string $url, array $data = [], string $method = 'GET', string $auth_mod = 'NONE')
    {
        $fullUrl = $this->DOMAIN . $url;
        $header  = [];
        // 无需任何鉴权
        if ($auth_mod !== 'NONE') {
            // 需要KEY鉴权
            $header = [
                "X-MBX-APIKEY:{$this->API_KEY}",
            ];
        }
        if ($this->need_timestamp) $data['timestamp'] = (string)getMSecTime();
        if (in_array($auth_mod, ['TRADE', 'USER_DATA'])) {
            // 需要签名鉴权
            $data['signature'] = $this->_signTrue($data, $this->API_SECRET);
        }
        if ($method === 'GET') {
            $fullUrl .= "?" . http_build_query($data);
            return $this->curl_request($fullUrl, $header);
        }
        return $this->curl_request($fullUrl, $header, $data);
    }

}