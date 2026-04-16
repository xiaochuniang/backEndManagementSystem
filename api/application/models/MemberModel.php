<?php
/**
 * MemberModel — 会员相关操作
 *
 * 对应数据表：member、member_level、deposit_rule、deposit_record、
 *             member_consume_record、points_record
 */
class MemberModel
{
    private Db $db;

    public function __construct()
    {
        $this->db = \Yaf\Registry::get('db');
    }

    // ----------------------------------------------------------------
    // 登录 / 注册
    // ----------------------------------------------------------------

    /**
     * 根据手机号查询会员，不存在则自动注册
     *
     * @param  string $phone  手机号
     * @param  string $openid 微信 openid
     * @return array  会员信息
     */
    public function findOrCreate(string $phone, string $openid): array
    {
        $member = $this->db->fetchOne(
            'SELECT m.*, ml.name AS level_name, ml.discount
             FROM member m
             LEFT JOIN member_level ml ON ml.id = m.level_id
             WHERE m.phone = :phone',
            [':phone' => $phone]
        );

        if ($member) {
            // 更新 openid（防止换设备后 openid 变化）
            if ($member['openid'] !== $openid) {
                $this->db->execute(
                    'UPDATE member SET openid = :oid WHERE id = :id',
                    [':oid' => $openid, ':id' => $member['id']]
                );
                $member['openid'] = $openid;
            }
            return $member;
        }

        // 自动注册，默认普通会员（level_id=1）
        $memberId = $this->db->insert(
            'INSERT INTO member (phone, openid, level_id, balance, points, create_time)
             VALUES (:phone, :oid, 1, 0.00, 0, NOW())',
            [':phone' => $phone, ':oid' => $openid]
        );

        return $this->getMemberById($memberId);
    }

    // ----------------------------------------------------------------
    // 查询
    // ----------------------------------------------------------------

    public function getMemberById(int $id): ?array
    {
        return $this->db->fetchOne(
            'SELECT m.*, ml.name AS level_name, ml.discount
             FROM member m
             LEFT JOIN member_level ml ON ml.id = m.level_id
             WHERE m.id = :id',
            [':id' => $id]
        );
    }

    public function getMemberByOpenid(string $openid): ?array
    {
        return $this->db->fetchOne(
            'SELECT m.*, ml.name AS level_name, ml.discount
             FROM member m
             LEFT JOIN member_level ml ON ml.id = m.level_id
             WHERE m.openid = :oid',
            [':oid' => $openid]
        );
    }

    // ----------------------------------------------------------------
    // 储值规则
    // ----------------------------------------------------------------

    /**
     * 获取所有启用的储值规则
     */
    public function getDepositRules(): array
    {
        return $this->db->fetchAll(
            'SELECT id, remark AS label, amount, gift AS gift_amount, status,
                    COALESCE(sort, id) AS sort
             FROM deposit_rule
             WHERE status = 1
             ORDER BY COALESCE(sort, id) ASC'
        );
    }

    /**
     * 根据 ID 获取储值规则
     */
    public function getDepositRuleById(int $id): ?array
    {
        $row = $this->db->fetchOne(
            'SELECT id, remark AS label, amount, gift AS gift_amount, status
             FROM deposit_rule WHERE id = :id AND status = 1',
            [':id' => $id]
        );
        return $row;
    }

    // ----------------------------------------------------------------
    // 储值（支付完成后调用）
    // ----------------------------------------------------------------

    /**
     * 完成储值：增加余额 + 写入储值记录
     *
     * @param  int    $memberId  会员 ID
     * @param  int    $ruleId    储值规则 ID
     * @param  string $orderNo   储值订单号（前端发起的微信支付订单号）
     * @param  string $transId   微信支付流水号
     */
    public function completeDeposit(
        int    $memberId,
        int    $ruleId,
        string $orderNo,
        string $transId
    ): void {
        $rule = $this->getDepositRuleById($ruleId);
        if (!$rule) {
            throw new \RuntimeException("储值规则不存在：{$ruleId}");
        }

        $this->db->beginTransaction();
        try {
            $creditAmount = (float)$rule['amount'] + (float)$rule['gift_amount'];

            // 增加余额
            $this->db->execute(
                'UPDATE member SET balance = balance + :amt WHERE id = :id',
                [':amt' => $creditAmount, ':id' => $memberId]
            );

            // 写入储值记录
            $this->db->execute(
                'INSERT INTO deposit_record
                    (member_id, rule_id, amount, gift, order_no, transaction_id, operator_id, create_time)
                 VALUES
                    (:mid, :rid, :amt, :gift, :ono, :tid, 0, NOW())',
                [
                    ':mid'  => $memberId,
                    ':rid'  => $ruleId,
                    ':amt'  => $rule['amount'],
                    ':gift' => $rule['gift_amount'],
                    ':ono'  => $orderNo,
                    ':tid'  => $transId,
                ]
            );

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw new \RuntimeException('储值失败：' . $e->getMessage());
        }
    }

    // ----------------------------------------------------------------
    // 消费记录
    // ----------------------------------------------------------------

    /**
     * 分页获取消费记录
     *
     * @param  int $memberId
     * @param  int $page
     * @param  int $pageSize
     * @return array [list, total]
     */
    public function getConsumeRecords(int $memberId, int $page = 1, int $pageSize = 20): array
    {
        $offset = ($page - 1) * $pageSize;
        $total  = (int)($this->db->fetchOne(
            'SELECT COUNT(*) AS cnt FROM member_consume_record WHERE member_id = :mid',
            [':mid' => $memberId]
        )['cnt'] ?? 0);

        $list = $this->db->fetchAll(
            'SELECT mcr.*, so.order_no
             FROM member_consume_record mcr
             LEFT JOIN shop_order so ON so.id = mcr.order_id
             WHERE mcr.member_id = :mid
             ORDER BY mcr.create_time DESC
             LIMIT :lmt OFFSET :off',
            [':mid' => $memberId, ':lmt' => $pageSize, ':off' => $offset]
        );

        return ['list' => $list, 'total' => $total];
    }

    // ----------------------------------------------------------------
    // 储值记录
    // ----------------------------------------------------------------

    public function getDepositRecords(int $memberId, int $page = 1, int $pageSize = 20): array
    {
        $offset = ($page - 1) * $pageSize;
        $total  = (int)($this->db->fetchOne(
            'SELECT COUNT(*) AS cnt FROM deposit_record WHERE member_id = :mid',
            [':mid' => $memberId]
        )['cnt'] ?? 0);

        $list = $this->db->fetchAll(
            'SELECT dr.*, rl.remark AS rule_label
             FROM deposit_record dr
             LEFT JOIN deposit_rule rl ON rl.id = dr.rule_id
             WHERE dr.member_id = :mid
             ORDER BY dr.create_time DESC
             LIMIT :lmt OFFSET :off',
            [':mid' => $memberId, ':lmt' => $pageSize, ':off' => $offset]
        );

        return ['list' => $list, 'total' => $total];
    }

    // ----------------------------------------------------------------
    // 积分记录
    // ----------------------------------------------------------------

    public function getPointsRecords(int $memberId, int $page = 1, int $pageSize = 20): array
    {
        $offset = ($page - 1) * $pageSize;
        $total  = (int)($this->db->fetchOne(
            'SELECT COUNT(*) AS cnt FROM points_record WHERE member_id = :mid',
            [':mid' => $memberId]
        )['cnt'] ?? 0);

        $list = $this->db->fetchAll(
            'SELECT * FROM points_record
             WHERE member_id = :mid
             ORDER BY create_time DESC
             LIMIT :lmt OFFSET :off',
            [':mid' => $memberId, ':lmt' => $pageSize, ':off' => $offset]
        );

        return ['list' => $list, 'total' => $total];
    }
}
