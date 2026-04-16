<?php
/**
 * Auth — JWT Token 认证工具
 *
 * 使用 HMAC-SHA256 实现轻量级 JWT（不依赖第三方库）。
 * 生成格式：Base64URL(header).Base64URL(payload).Base64URL(signature)
 */
class Auth
{
    private static string $secret = '';

    public static function init(): void
    {
        $config = \Yaf\Registry::get('config');
        self::$secret = $config->jwt->secret ?? 'default_secret';
    }

    // ----------------------------------------------------------------
    // Token 生成
    // ----------------------------------------------------------------

    /**
     * 生成 JWT Token
     *
     * @param  array $payload 载荷（不要放敏感字段）
     * @param  int   $ttl     有效期（秒），默认取配置
     * @return string
     */
    public static function generateToken(array $payload, int $ttl = 0): string
    {
        if (self::$secret === '') {
            self::init();
        }
        $config = \Yaf\Registry::get('config');
        if ($ttl <= 0) {
            $ttl = (int)($config->jwt->ttl ?? 7200);
        }

        $header  = self::base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload['iat'] = time();
        $payload['exp'] = time() + $ttl;
        $payloadStr = self::base64UrlEncode(json_encode($payload, JSON_UNESCAPED_UNICODE));
        $signature  = self::base64UrlEncode(
            hash_hmac('sha256', "{$header}.{$payloadStr}", self::$secret, true)
        );

        return "{$header}.{$payloadStr}.{$signature}";
    }

    // ----------------------------------------------------------------
    // Token 验证
    // ----------------------------------------------------------------

    /**
     * 验证 Token 并返回 payload，失败返回 null
     *
     * @param  string $token
     * @return array|null
     */
    public static function verifyToken(string $token): ?array
    {
        if (self::$secret === '') {
            self::init();
        }

        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }
        [$header, $payload, $sig] = $parts;

        $expectedSig = self::base64UrlEncode(
            hash_hmac('sha256', "{$header}.{$payload}", self::$secret, true)
        );
        if (!hash_equals($expectedSig, $sig)) {
            return null;
        }

        $data = json_decode(self::base64UrlDecode($payload), true);
        if (!$data || $data['exp'] < time()) {
            return null;
        }

        return $data;
    }

    // ----------------------------------------------------------------
    // 从 HTTP 请求头中提取并验证 Token
    // ----------------------------------------------------------------

    /**
     * 从 Authorization: Bearer <token> 头中获取并校验 Token
     * 校验失败则直接调用 Response::unauthorized() 结束响应
     *
     * @return array payload 数组
     */
    public static function requireLogin(): array
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (strpos($header, 'Bearer ') === 0) {
            $token = substr($header, 7);
        } else {
            $token = '';
        }

        if ($token === '') {
            Response::unauthorized();
        }

        $payload = self::verifyToken($token);
        if ($payload === null) {
            Response::unauthorized('Token 无效或已过期，请重新登录');
        }

        return $payload;
    }

    // ----------------------------------------------------------------
    // 辅助方法
    // ----------------------------------------------------------------

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
