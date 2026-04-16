<?php
/**
 * OrderModel — 订单相关操作
 *
 * 对应数据表：shop_order、order_item、points_record
 */
class OrderModel
{
    private Db $db;

    public function __construct()
    {
        $this->db = \Yaf\Registry::get('db');
    }

    // ----------------------------------------------------------------
    // 创建订单（事务）
    // ----------------------------------------------------------------

    /**
     * 创建订单
     *
     * @param  int    $memberId    会员 ID
     * @param  array  $cartItems   [['product_id'=>int,'qty'=>int,'price'=>float,'name'=>string], ...]
     * @param  int    $couponId    使用的优惠券 ID（member_coupon.id），0 表示不使用
     * @param  float  $totalAmount 原始总金额（前端传入，后端二次校验）
     * @return int    新订单 ID
     * @throws \RuntimeException
     */
    public function createOrder(
        int   $memberId,
        array $cartItems,
        int   $couponId = 0,
        float $totalAmount = 0.0
    ): int {
        $this->db->beginTransaction();
        try {
            // 生成订单号：MP + 年月日 + 6位随机数
            $orderNo = 'MP' . date('Ymd') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);

            // 后端重算总金额
            $calcTotal = 0.0;
            foreach ($cartItems as $item) {
                $calcTotal += round((float)$item['price'] * (int)$item['qty'], 2);
            }

            // 处理优惠券抵扣
            $discountAmount = 0.0;
            if ($couponId > 0) {
                $discountAmount = $this->applyCoupon($couponId, $calcTotal);
            }

            $payAmount = max(0.0, round($calcTotal - $discountAmount, 2));

            // 写入订单主表
            $orderId = $this->db->insert(
                'INSERT INTO shop_order
                    (order_no, member_id, staff_id, total_amount, discount_amount, pay_amount,
                     coupon_id, pay_status, order_status, channel, create_time)
                 VALUES
                    (:order_no, :member_id, 0, :total_amount, :discount_amount, :pay_amount,
                     :coupon_id, 0, 3, "miniapp", NOW())',
                [
                    ':order_no'       => $orderNo,
                    ':member_id'      => $memberId,
                    ':total_amount'   => $calcTotal,
                    ':discount_amount'=> $discountAmount,
                    ':pay_amount'     => $payAmount,
                    ':coupon_id'      => $couponId ?: null,
                ]
            );

            // 写入订单明细
            foreach ($cartItems as $item) {
                $this->db->execute(
                    'INSERT INTO order_item (order_id, product_id, product_name, price, qty, sub_total)
                     VALUES (:order_id, :product_id, :name, :price, :qty, :sub)',
                    [
                        ':order_id'   => $orderId,
                        ':product_id' => $item['product_id'],
                        ':name'       => $item['name'],
                        ':price'      => $item['price'],
                        ':qty'        => $item['qty'],
                        ':sub'        => round((float)$item['price'] * (int)$item['qty'], 2),
                    ]
                );
            }

            $this->db->commit();
            return $orderId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw new \RuntimeException('创建订单失败：' . $e->getMessage());
        }
    }

    // ----------------------------------------------------------------
    // 支付状态更新（微信回调时调用）
    // ----------------------------------------------------------------

    /**
     * 更新订单为已支付
     *
     * @param  string $orderNo     商户订单号
     * @param  string $transactionId 微信支付流水号
     */
    public function markAsPaid(string $orderNo, string $transactionId): void
    {
        $this->db->beginTransaction();
        try {
            $order = $this->db->fetchOne(
                'SELECT id, member_id, pay_amount, coupon_id FROM shop_order WHERE order_no = :no',
                [':no' => $orderNo]
            );
            if (!$order || $order === null) {
                throw new \RuntimeException("订单不存在：{$orderNo}");
            }

            // 更新支付状态
            $this->db->execute(
                'UPDATE shop_order
                 SET pay_status = 1, transaction_id = :tid, pay_time = NOW(), order_status = 2
                 WHERE order_no = :no AND pay_status = 0',
                [':tid' => $transactionId, ':no' => $orderNo]
            );

            // 写入消费积分记录
            $points = (int)$order['pay_amount'];  // 1元=1积分
            if ($points > 0) {
                $this->db->execute(
                    'INSERT INTO points_record (member_id, change_points, reason, order_id, create_time)
                     VALUES (:mid, :pts, "消费积分", :oid, NOW())',
                    [':mid' => $order['member_id'], ':pts' => $points, ':oid' => $order['id']]
                );
                $this->db->execute(
                    'UPDATE member SET points = points + :pts WHERE id = :mid',
                    [':pts' => $points, ':mid' => $order['member_id']]
                );
            }

            // 写入消费记录
            $this->db->execute(
                'INSERT INTO member_consume_record (member_id, order_id, amount, create_time)
                 VALUES (:mid, :oid, :amt, NOW())',
                [':mid' => $order['member_id'], ':oid' => $order['id'], ':amt' => $order['pay_amount']]
            );

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw new \RuntimeException('更新支付状态失败：' . $e->getMessage());
        }
    }

    // ----------------------------------------------------------------
    // 查询
    // ----------------------------------------------------------------

    public function getOrderById(int $id): ?array
    {
        return $this->db->fetchOne('SELECT * FROM shop_order WHERE id = :id', [':id' => $id]);
    }

    public function getOrderByNo(string $orderNo): ?array
    {
        return $this->db->fetchOne(
            'SELECT * FROM shop_order WHERE order_no = :no',
            [':no' => $orderNo]
        );
    }

    public function getOrderItems(int $orderId): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM order_item WHERE order_id = :oid',
            [':oid' => $orderId]
        );
    }

    // ----------------------------------------------------------------
    // 私有：优惠券抵扣
    // ----------------------------------------------------------------

    private function applyCoupon(int $couponId, float $totalAmount): float
    {
        $coupon = $this->db->fetchOne(
            'SELECT mc.*, ct.type, ct.value, ct.min_amount
             FROM member_coupon mc
             JOIN coupon_template ct ON ct.id = mc.template_id
             WHERE mc.id = :id AND mc.status = 0 AND mc.expire_time > NOW()',
            [':id' => $couponId]
        );
        if (!$coupon) {
            return 0.0;
        }
        if ($totalAmount < (float)$coupon['min_amount']) {
            return 0.0;
        }

        $discount = 0.0;
        if ((int)$coupon['type'] === 1) {
            // 满减
            $discount = (float)$coupon['value'];
        } elseif ((int)$coupon['type'] === 2) {
            // 折扣
            $discount = round($totalAmount * (1 - (float)$coupon['value']), 2);
        }

        // 标记优惠券为已使用
        $this->db->execute(
            'UPDATE member_coupon SET status = 1, used_time = NOW() WHERE id = :id',
            [':id' => $couponId]
        );

        return $discount;
    }
}
