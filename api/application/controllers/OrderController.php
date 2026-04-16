<?php
/**
 * OrderController — 在线买单 / 结算 / 微信支付
 *
 * 路由（均需登录 Token）：
 *   POST /order/create     创建订单（校验购物车，生成订单记录）
 *   POST /order/prepay     发起微信预支付（JSAPI），返回小程序调起支付所需参数
 *   GET  /order/detail     订单详情
 *   POST /pay/notify       微信支付结果回调（无需 Token，由微信服务器调用）
 */

use Yaf\Controller_Abstract;

class OrderController extends Controller_Abstract
{
    // ----------------------------------------------------------------
    // POST /order/create
    // ----------------------------------------------------------------

    /**
     * 创建订单
     *
     * Request JSON:
     * {
     *   "items": [
     *     { "product_id": 1, "qty": 2 },
     *     { "product_id": 3, "qty": 1 }
     *   ],
     *   "coupon_id": 0          // 可选，member_coupon.id，0表示不使用
     * }
     *
     * Response:
     * {
     *   "order_id":    123,
     *   "order_no":    "MP20260416000001",
     *   "pay_amount":  58.00,
     *   "items":       [...]
     * }
     */
    public function createAction(): void
    {
        $payload = Auth::requireLogin();
        $body    = json_decode(file_get_contents('php://input'), true) ?? [];
        $items   = $body['items']     ?? [];
        $couponId = (int)($body['coupon_id'] ?? 0);

        if (empty($items) || !is_array($items)) {
            Response::error('购物车不能为空');
        }

        // 校验商品合法性 & 组装 cart items
        $productModel = new ProductModel();
        $ids          = array_column($items, 'product_id');
        $products     = $productModel->getProductsByIds($ids);

        $cartItems = [];
        foreach ($items as $item) {
            $pid = (int)$item['product_id'];
            $qty = (int)($item['qty'] ?? 1);
            if ($qty <= 0) {
                continue;
            }
            if (!isset($products[$pid])) {
                Response::error("商品 ID={$pid} 不存在或已下架");
            }
            $p          = $products[$pid];
            $cartItems[] = [
                'product_id' => $pid,
                'name'       => $p['name'],
                'price'      => $p['price'],
                'qty'        => $qty,
            ];
        }

        if (empty($cartItems)) {
            Response::error('购物车为空');
        }

        $orderModel = new OrderModel();
        $orderId    = $orderModel->createOrder(
            $payload['member_id'],
            $cartItems,
            $couponId
        );

        $order = $orderModel->getOrderById($orderId);
        $orderItems = $orderModel->getOrderItems($orderId);

        Response::success([
            'order_id'   => $orderId,
            'order_no'   => $order['order_no'],
            'pay_amount' => $order['pay_amount'],
            'items'      => $orderItems,
        ]);
    }

    // ----------------------------------------------------------------
    // POST /order/prepay
    // ----------------------------------------------------------------

    /**
     * 发起微信预支付
     *
     * Request JSON: { "order_id": 123 }
     *
     * Response: 微信小程序 wx.requestPayment() 所需参数
     * {
     *   "timeStamp": "...",
     *   "nonceStr":  "...",
     *   "package":   "prepay_id=...",
     *   "signType":  "RSA",
     *   "paySign":   "..."
     * }
     */
    public function prepayAction(): void
    {
        $payload = Auth::requireLogin();
        $body    = json_decode(file_get_contents('php://input'), true) ?? [];
        $orderId = (int)($body['order_id'] ?? 0);

        if ($orderId <= 0) {
            Response::error('参数错误：缺少 order_id');
        }

        $orderModel = new OrderModel();
        $order      = $orderModel->getOrderById($orderId);

        if (!$order || (int)$order['member_id'] !== (int)$payload['member_id']) {
            Response::notFound('订单不存在');
        }
        if ((int)$order['pay_status'] !== 0) {
            Response::error('订单已支付，无需重复发起');
        }

        // 获取会员 openid
        $db     = \Yaf\Registry::get('db');
        $member = $db->fetchOne(
            'SELECT openid FROM member WHERE id = :id',
            [':id' => $payload['member_id']]
        );
        if (!$member || empty($member['openid'])) {
            Response::error('未获取到微信授权信息，请重新登录');
        }

        $totalFee = (int)round((float)$order['pay_amount'] * 100);  // 转为分

        try {
            $wxPay  = new WechatPay();
            $params = $wxPay->unifiedOrder(
                $member['openid'],
                $order['order_no'],
                $totalFee,
                '在线买单'
            );
            Response::success($params);
        } catch (\RuntimeException $e) {
            Response::error('发起支付失败：' . $e->getMessage(), 500);
        }
    }

    // ----------------------------------------------------------------
    // GET /order/detail
    // ----------------------------------------------------------------

    /**
     * 订单详情
     *
     * Query params: id=123
     */
    public function detailAction(): void
    {
        $payload = Auth::requireLogin();
        $orderId = (int)$this->getRequest()->getQuery('id', 0);

        if ($orderId <= 0) {
            Response::error('参数错误：缺少订单 ID');
        }

        $orderModel = new OrderModel();
        $order      = $orderModel->getOrderById($orderId);

        if (!$order || (int)$order['member_id'] !== (int)$payload['member_id']) {
            Response::notFound('订单不存在');
        }

        $items = $orderModel->getOrderItems($orderId);

        Response::success(array_merge($order, ['items' => $items]));
    }

    // ----------------------------------------------------------------
    // POST /pay/notify  （微信回调，无需 Token）
    // ----------------------------------------------------------------

    /**
     * 微信支付结果通知
     *
     * 此 action 由微信服务器回调，不走 Auth 中间件。
     * 需在 nginx/Apache 路由中映射到 /pay/notify。
     */
    public function notifyAction(): void
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
            $wxPay  = new WechatPay();
            $data   = $wxPay->handleNotify($rawBody, $headers);

            if (($data['trade_state'] ?? '') === 'SUCCESS') {
                $orderNo      = $data['out_trade_no'] ?? '';
                $transactionId = $data['transaction_id'] ?? '';
                (new OrderModel())->markAsPaid($orderNo, $transactionId);
            }

            // 微信要求回调成功返回此结构
            header('Content-Type: application/json');
            echo json_encode(['code' => 'SUCCESS', 'message' => '成功']);
            exit;
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['code' => 'FAIL', 'message' => $e->getMessage()]);
            exit;
        }
    }
}
