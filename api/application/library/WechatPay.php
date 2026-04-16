<?php
/**
 * WechatPay — 微信支付 V3 API 封装（小程序端 JSAPI 支付）
 *
 * 主要流程：
 *   1. 前端调用 /order/prepay → 后端调用 unified_order() → 返回小程序唤起支付所需参数
 *   2. 微信回调 /pay/notify    → 后端调用 handleNotify()  → 更新订单状态
 *
 * 注意：实际使用时需在 conf/app.ini 填写真实的 mchid / api_key / cert 路径。
 */
class WechatPay
{
    private string $appid;
    private string $mchid;
    private string $apiKey;    // V3 API 密钥
    private string $certPath;
    private string $keyPath;
    private string $notifyUrl;

    public function __construct()
    {
        $config = \Yaf\Registry::get('config');
        $this->appid     = $config->wechat->appid;
        $this->mchid     = $config->wxpay->mchid;
        $this->apiKey    = $config->wxpay->api_key;
        $this->certPath  = $config->wxpay->cert_path;
        $this->keyPath   = $config->wxpay->key_path;
        $this->notifyUrl = $config->wxpay->notify_url;
    }

    // ----------------------------------------------------------------
    // JSAPI 统一下单（V3）
    // ----------------------------------------------------------------

    /**
     * 创建预支付订单，返回小程序 wx.requestPayment() 所需参数
     *
     * @param  string $openid      用户 openid
     * @param  string $outTradeNo  商户侧订单号（唯一）
     * @param  int    $totalFee    支付金额（分）
     * @param  string $description 商品描述
     * @return array  包含 timeStamp / nonceStr / package / signType / paySign
     * @throws \RuntimeException
     */
    public function unifiedOrder(
        string $openid,
        string $outTradeNo,
        int    $totalFee,
        string $description = '在线买单'
    ): array {
        $url  = 'https://api.mch.weixin.qq.com/v3/pay/transactions/jsapi';
        $body = [
            'appid'        => $this->appid,
            'mchid'        => $this->mchid,
            'description'  => $description,
            'out_trade_no' => $outTradeNo,
            'notify_url'   => $this->notifyUrl,
            'amount'       => ['total' => $totalFee, 'currency' => 'CNY'],
            'payer'        => ['openid' => $openid],
        ];

        $response = $this->httpPost($url, $body);
        if (empty($response['prepay_id'])) {
            throw new \RuntimeException('微信统一下单失败：' . json_encode($response, JSON_UNESCAPED_UNICODE));
        }

        return $this->buildJsapiParams($response['prepay_id']);
    }

    // ----------------------------------------------------------------
    // 微信支付回调验签与解密
    // ----------------------------------------------------------------

    /**
     * 处理微信支付结果通知
     *
     * @param  string $rawBody  php://input 原始请求体
     * @param  array  $headers  请求头数组（含 Wechatpay-Signature 等）
     * @return array  解密后的通知资源数据
     * @throws \RuntimeException 签名验证失败
     */
    public function handleNotify(string $rawBody, array $headers): array
    {
        // 验证签名（简化实现：生产环境应严格按照微信 V3 签名规则校验）
        $timestamp  = $headers['Wechatpay-Timestamp'] ?? '';
        $nonce      = $headers['Wechatpay-Nonce'] ?? '';
        $signature  = $headers['Wechatpay-Signature'] ?? '';
        $serial     = $headers['Wechatpay-Serial'] ?? '';

        $message    = "{$timestamp}\n{$nonce}\n{$rawBody}\n";
        // TODO: 使用微信平台公钥验证 signature（需提前下载平台证书）
        // 此处仅做结构示意，生产必须补全签名验证
        if (empty($timestamp) || empty($nonce) || empty($signature)) {
            throw new \RuntimeException('微信回调缺少必要请求头');
        }

        $data     = json_decode($rawBody, true);
        $resource = $data['resource'] ?? [];

        // AES-256-GCM 解密
        $ciphertext     = base64_decode($resource['ciphertext'] ?? '');
        $associatedData = $resource['associated_data'] ?? '';
        $nonceStr       = $resource['nonce'] ?? '';
        $plaintext      = $this->decryptAesGcm($ciphertext, $this->apiKey, $nonceStr, $associatedData);

        return json_decode($plaintext, true) ?? [];
    }

    // ----------------------------------------------------------------
    // 构造小程序调起支付参数
    // ----------------------------------------------------------------

    private function buildJsapiParams(string $prepayId): array
    {
        $timeStamp = (string)time();
        $nonceStr  = $this->generateNonce();
        $package   = "prepay_id={$prepayId}";
        $signType  = 'RSA';

        $message   = "{$this->appid}\n{$timeStamp}\n{$nonceStr}\n{$package}\n";
        $paySign   = $this->rsaSign($message);

        return compact('timeStamp', 'nonceStr', 'package', 'signType', 'paySign');
    }

    // ----------------------------------------------------------------
    // HTTP 请求
    // ----------------------------------------------------------------

    private function httpPost(string $url, array $body): array
    {
        $jsonBody    = json_encode($body, JSON_UNESCAPED_UNICODE);
        $timestamp   = time();
        $nonce       = $this->generateNonce();
        $method      = 'POST';
        $urlParts    = parse_url($url);
        $canonicalUrl = $urlParts['path'] . (isset($urlParts['query']) ? '?' . $urlParts['query'] : '');

        $message   = "{$method}\n{$canonicalUrl}\n{$timestamp}\n{$nonce}\n{$jsonBody}\n";
        $signature = $this->rsaSign($message);
        $schema    = "WECHATPAY2-SHA256-RSA2048 mchid=\"{$this->mchid}\","
            . "nonce_str=\"{$nonce}\","
            . "timestamp=\"{$timestamp}\","
            . "serial_no=\"YOUR_CERT_SERIAL_NO\","
            . "signature=\"{$signature}\"";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $jsonBody,
            CURLOPT_SSLCERT        => $this->certPath,
            CURLOPT_SSLKEY         => $this->keyPath,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
                "Authorization: {$schema}",
            ],
        ]);
        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true) ?? [];
    }

    // ----------------------------------------------------------------
    // 签名与加解密
    // ----------------------------------------------------------------

    private function rsaSign(string $message): string
    {
        $privateKey = file_get_contents($this->keyPath);
        $key        = openssl_pkey_get_private($privateKey);
        openssl_sign($message, $signature, $key, OPENSSL_ALGO_SHA256);
        return base64_encode($signature);
    }

    private function decryptAesGcm(
        string $ciphertext,
        string $key,
        string $nonce,
        string $aad
    ): string {
        $key  = substr(hash('sha256', $key, true), 0, 32);
        $tag  = substr($ciphertext, -16);
        $data = substr($ciphertext, 0, -16);
        return openssl_decrypt($data, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $nonce, $tag, $aad);
    }

    private function generateNonce(int $length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
    }
}
