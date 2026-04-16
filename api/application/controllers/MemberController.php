<?php
/**
 * MemberController — 会员中心
 *
 * 路由（均需登录 Token）：
 *   GET  /member/info           个人信息（手机号、会员等级、余额、积分）
 *   GET  /member/depositRules   储值规则列表
 *   POST /member/deposit        发起储值（返回微信支付参数）
 *   POST /member/depositNotify  储值微信回调（无需 Token）
 *   GET  /member/consumeRecords 消费记录
 *   GET  /member/depositRecords 储值记录
 *   GET  /member/pointsRecords  积分记录
 */

use Yaf\Controller_Abstract;

class MemberController extends Controller_Abstract
{
    // ----------------------------------------------------------------
    // GET /member/info
    // ----------------------------------------------------------------

    /**
     * 获取当前登录会员的个人信息
     */
    public function infoAction(): void
    {
        $payload = Auth::requireLogin();
        $member  = (new MemberModel())->getMemberById($payload['member_id']);

        if (!$member) {
            Response::notFound('会员不存在');
        }

        Response::success([
            'id'         => $member['id'],
            'phone'      => substr_replace($member['phone'], '****', 3, 4),
            'level_name' => $member['level_name'] ?? '普通',
            'discount'   => $member['discount']   ?? 1.00,
            'balance'    => number_format((float)$member['balance'], 2),
            'points'     => (int)$member['points'],
            'create_time'=> $member['create_time'] ?? '',
        ]);
    }

    // ----------------------------------------------------------------
    // GET /member/depositRules
    // ----------------------------------------------------------------

    /**
     * 获取储值规则列表（选择储值金额时展示）
     */
    public function depositRulesAction(): void
    {
        Auth::requireLogin();
        $rules = (new MemberModel())->getDepositRules();
        Response::success($rules);
    }

    // ----------------------------------------------------------------
    // POST /member/deposit
    // ----------------------------------------------------------------

    /**
     * 发起储值支付（JSAPI）
     *
     * Request JSON: { "rule_id": 2 }
     *
     * Response: wx.requestPayment() 参数
     */
    public function depositAction(): void
    {
        $payload  = Auth::requireLogin();
        $body     = json_decode(file_get_contents('php://input'), true) ?? [];
        $ruleId   = (int)($body['rule_id'] ?? 0);

        if ($ruleId <= 0) {
            Response::error('请选择储值规则');
        }

        $memberModel = new MemberModel();
        $rule        = $memberModel->getDepositRuleById($ruleId);
        if (!$rule) {
            Response::error('储值规则不存在');
        }

        // 获取 openid
        $db     = \Yaf\Registry::get('db');
        $member = $db->fetchOne(
            'SELECT openid FROM member WHERE id = :id',
            [':id' => $payload['member_id']]
        );
        if (!$member || empty($member['openid'])) {
            Response::error('未获取到微信授权信息，请重新登录');
        }

        // 生成储值订单号
        $orderNo  = 'DEP' . date('Ymd') . str_pad((string)mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        $totalFee = (int)round((float)$rule['amount'] * 100);  // 分

        try {
            $wxPay  = new WechatPay();
            $params = $wxPay->unifiedOrder(
                $member['openid'],
                $orderNo,
                $totalFee,
                "储值：{$rule['label']}"
            );

            // 暂存储值意图，回调时用（简单实现：存入临时会话或数据库）
            // 生产环境建议用 Redis 或独立的 pending_deposit 表
            $db->execute(
                'INSERT INTO deposit_record
                    (member_id, rule_id, amount, gift_amount, order_no, transaction_id, create_time)
                 VALUES (:mid, :rid, :amt, :gift, :ono, "", NOW())
                 ON DUPLICATE KEY UPDATE order_no = VALUES(order_no)',
                [
                    ':mid'  => $payload['member_id'],
                    ':rid'  => $ruleId,
                    ':amt'  => $rule['amount'],
                    ':gift' => $rule['gift_amount'],
                    ':ono'  => $orderNo,
                ]
            );

            Response::success(array_merge($params, ['order_no' => $orderNo]));
        } catch (\RuntimeException $e) {
            Response::error('发起储值支付失败：' . $e->getMessage(), 500);
        }
    }

    // ----------------------------------------------------------------
    // POST /member/depositNotify  （微信储值回调，无需 Token）
    // ----------------------------------------------------------------

    public function depositNotifyAction(): void
    {
        $rawBody = file_get_contents('php://input');
        $headers = [];
        foreach ($_SERVER as $k => $v) {
            if (strpos($k, 'HTTP_') === 0) {
                $key           = str_replace('_', '-', substr($k, 5));
                $headers[$key] = $v;
            }
        }

        try {
            $wxPay = new WechatPay();
            $data  = $wxPay->handleNotify($rawBody, $headers);

            if (($data['trade_state'] ?? '') === 'SUCCESS') {
                $orderNo = $data['out_trade_no'] ?? '';
                $transId = $data['transaction_id'] ?? '';

                $db = \Yaf\Registry::get('db');
                $record = $db->fetchOne(
                    'SELECT member_id, rule_id FROM deposit_record WHERE order_no = :ono',
                    [':ono' => $orderNo]
                );
                if ($record) {
                    (new MemberModel())->completeDeposit(
                        (int)$record['member_id'],
                        (int)$record['rule_id'],
                        $orderNo,
                        $transId
                    );
                }
            }

            header('Content-Type: application/json');
            echo json_encode(['code' => 'SUCCESS', 'message' => '成功']);
            exit;
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['code' => 'FAIL', 'message' => $e->getMessage()]);
            exit;
        }
    }

    // ----------------------------------------------------------------
    // GET /member/consumeRecords
    // ----------------------------------------------------------------

    /**
     * Query params: page=1, page_size=20
     */
    public function consumeRecordsAction(): void
    {
        $payload  = Auth::requireLogin();
        $page     = max(1, (int)$this->getRequest()->getQuery('page', 1));
        $pageSize = min(50, max(1, (int)$this->getRequest()->getQuery('page_size', 20)));

        $result = (new MemberModel())->getConsumeRecords($payload['member_id'], $page, $pageSize);
        Response::success($result);
    }

    // ----------------------------------------------------------------
    // GET /member/depositRecords
    // ----------------------------------------------------------------

    public function depositRecordsAction(): void
    {
        $payload  = Auth::requireLogin();
        $page     = max(1, (int)$this->getRequest()->getQuery('page', 1));
        $pageSize = min(50, max(1, (int)$this->getRequest()->getQuery('page_size', 20)));

        $result = (new MemberModel())->getDepositRecords($payload['member_id'], $page, $pageSize);
        Response::success($result);
    }

    // ----------------------------------------------------------------
    // GET /member/pointsRecords
    // ----------------------------------------------------------------

    public function pointsRecordsAction(): void
    {
        $payload  = Auth::requireLogin();
        $page     = max(1, (int)$this->getRequest()->getQuery('page', 1));
        $pageSize = min(50, max(1, (int)$this->getRequest()->getQuery('page_size', 20)));

        $result = (new MemberModel())->getPointsRecords($payload['member_id'], $page, $pageSize);
        Response::success($result);
    }
}
