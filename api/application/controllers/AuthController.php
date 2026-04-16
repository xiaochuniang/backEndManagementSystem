<?php
/**
 * AuthController — 登录 / 短信验证码
 *
 * 路由：
 *   POST /auth/sendSms    发送短信验证码
 *   POST /auth/login      手机号+验证码登录（微信小程序端）
 *   POST /auth/refresh    刷新 Token
 */

use Yaf\Controller_Abstract;

class AuthController extends Controller_Abstract
{
    // ----------------------------------------------------------------
    // POST /auth/sendSms
    // ----------------------------------------------------------------

    /**
     * 发送短信验证码
     *
     * Request JSON: { "phone": "138xxxxxxxx" }
     * Response:     { "code": 200, "message": "发送成功" }
     */
    public function sendSmsAction(): void
    {
        $body  = json_decode(file_get_contents('php://input'), true) ?? [];
        $phone = trim($body['phone'] ?? '');

        if (!preg_match('/^1[3-9]\d{9}$/', $phone)) {
            Response::error('手机号格式不正确');
        }

        // 生成 6 位验证码
        $code       = str_pad((string)mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        $expireTime = date('Y-m-d H:i:s', time() + 300);   // 5 分钟有效

        $db = \Yaf\Registry::get('db');

        // 限流：同一手机号 1 分钟内只能发送一次
        $last = $db->fetchOne(
            "SELECT create_time FROM sms_verify_code
             WHERE phone = :phone AND scene = 'login' AND used = 0
               AND create_time > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
             ORDER BY create_time DESC LIMIT 1",
            [':phone' => $phone]
        );
        if ($last) {
            Response::error('发送过于频繁，请稍后再试', 429);
        }

        // 写入验证码（实际环境需调用短信服务商 SDK）
        $db->execute(
            "INSERT INTO sms_verify_code (phone, code, scene, used, expire_time, create_time)
             VALUES (:phone, :code, 'login', 0, :expire, NOW())",
            [':phone' => $phone, ':code' => $code, ':expire' => $expireTime]
        );

        // TODO: 调用短信服务商 SDK 发送 $code 到 $phone
        // SmsService::send($phone, $code);

        Response::success(null, '验证码已发送');
    }

    // ----------------------------------------------------------------
    // POST /auth/login
    // ----------------------------------------------------------------

    /**
     * 微信小程序登录
     *
     * 前端先通过 wx.login() 获取 code，再携带 code + 手机号 + 验证码请求此接口。
     *
     * Request JSON:
     * {
     *   "code":  "wx.login 返回的临时 code",
     *   "phone": "138xxxxxxxx",
     *   "sms_code": "123456"
     * }
     *
     * Response:
     * {
     *   "token":  "JWT token",
     *   "member": { id, phone, level_name, balance, points, ... }
     * }
     */
    public function loginAction(): void
    {
        $body    = json_decode(file_get_contents('php://input'), true) ?? [];
        $code    = trim($body['code']     ?? '');
        $phone   = trim($body['phone']    ?? '');
        $smsCode = trim($body['sms_code'] ?? '');

        if ($code === '' || $phone === '' || $smsCode === '') {
            Response::error('参数不完整');
        }

        // 1. 校验短信验证码
        $db = \Yaf\Registry::get('db');
        $record = $db->fetchOne(
            "SELECT id FROM sms_verify_code
             WHERE phone = :phone AND code = :code AND scene = 'login'
               AND used = 0 AND expire_time > NOW()
             ORDER BY create_time DESC LIMIT 1",
            [':phone' => $phone, ':code' => $smsCode]
        );
        if (!$record) {
            Response::error('验证码错误或已过期');
        }

        // 标记验证码已使用
        $db->execute(
            'UPDATE sms_verify_code SET used = 1 WHERE id = :id',
            [':id' => $record['id']]
        );

        // 2. 通过 code 换取微信 openid（session_key）
        $openid = $this->code2Openid($code);
        if ($openid === '') {
            Response::error('微信授权失败，请重试');
        }

        // 3. 查询或创建会员
        $memberModel = new MemberModel();
        $member      = $memberModel->findOrCreate($phone, $openid);

        // 4. 生成 JWT
        $token = Auth::generateToken(['member_id' => $member['id'], 'phone' => $member['phone']]);

        Response::success([
            'token'  => $token,
            'member' => $this->formatMember($member),
        ]);
    }

    // ----------------------------------------------------------------
    // POST /auth/refresh
    // ----------------------------------------------------------------

    /**
     * 刷新 Token（Token 未过期时可调用）
     */
    public function refreshAction(): void
    {
        $payload = Auth::requireLogin();
        $token   = Auth::generateToken(['member_id' => $payload['member_id'], 'phone' => $payload['phone']]);
        Response::success(['token' => $token]);
    }

    // ----------------------------------------------------------------
    // 私有辅助
    // ----------------------------------------------------------------

    private function code2Openid(string $code): string
    {
        $config = \Yaf\Registry::get('config');
        $url    = "https://api.weixin.qq.com/sns/jscode2session"
            . "?appid={$config->wechat->appid}"
            . "&secret={$config->wechat->secret}"
            . "&js_code={$code}"
            . "&grant_type=authorization_code";

        $res = @file_get_contents($url);
        if ($res === false) {
            return '';
        }
        $data = json_decode($res, true);
        return $data['openid'] ?? '';
    }

    private function formatMember(array $m): array
    {
        return [
            'id'         => $m['id'],
            'phone'      => substr_replace($m['phone'], '****', 3, 4),
            'level_name' => $m['level_name'] ?? '普通',
            'balance'    => number_format((float)($m['balance'] ?? 0), 2),
            'points'     => (int)($m['points'] ?? 0),
        ];
    }
}
