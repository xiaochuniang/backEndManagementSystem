<?php
/**
 * CouponModel — 优惠券相关操作
 *
 * 对应数据表：coupon_template、member_coupon
 */
class CouponModel
{
    private Db $db;

    public function __construct()
    {
        $this->db = \Yaf\Registry::get('db');
    }

    // ----------------------------------------------------------------
    // 可领取的优惠券模板列表
    // ----------------------------------------------------------------

    /**
     * 获取当前可领取的优惠券模板（启用、活动期内、有剩余数量）
     *
     * @param  int $memberId  当前会员 ID，用于标记"是否已领取"
     * @return array
     */
    public function getAvailableTemplates(int $memberId): array
    {
        $templates = $this->db->fetchAll(
            "SELECT id, name, type, value, min_amount,
                    total_count, remain_count, end_time, valid_days, description
             FROM coupon_template
             WHERE status = 1
               AND start_time <= NOW()
               AND end_time >= NOW()
               AND (total_count = 0 OR remain_count > 0)
             ORDER BY id ASC"
        );

        if (empty($templates)) {
            return [];
        }

        // 查询当前会员已领取的模板 ID
        $templateIds  = array_column($templates, 'id');
        $placeholders = implode(',', array_fill(0, count($templateIds), '?'));
        $received     = $this->db->fetchAll(
            "SELECT DISTINCT template_id FROM member_coupon
             WHERE member_id = ? AND template_id IN ({$placeholders})",
            array_merge([$memberId], $templateIds)
        );
        $receivedIds  = array_column($received, 'template_id');

        foreach ($templates as &$t) {
            $t['received'] = in_array($t['id'], $receivedIds);
        }
        unset($t);

        return $templates;
    }

    // ----------------------------------------------------------------
    // 会员持有的优惠券
    // ----------------------------------------------------------------

    /**
     * 获取会员的优惠券列表，支持状态筛选
     *
     * @param  int         $memberId
     * @param  string|null $status  null=全部, 'unused'=未使用, 'used'=已使用, 'expired'=已过期
     * @param  int         $page
     * @param  int         $pageSize
     * @return array [list, total]
     */
    public function getMemberCoupons(
        int    $memberId,
        ?string $status = null,
        int    $page = 1,
        int    $pageSize = 20
    ): array {
        $where  = ['mc.member_id = :mid'];
        $params = [':mid' => $memberId];

        if ($status !== null) {
            $statusMap = ['unused' => 0, 'used' => 1, 'expired' => 2];
            if (isset($statusMap[$status])) {
                $where[]          = 'mc.status = :status';
                $params[':status'] = $statusMap[$status];
            }
        }

        $whereStr = 'WHERE ' . implode(' AND ', $where);
        $offset   = ($page - 1) * $pageSize;

        $total = (int)($this->db->fetchOne(
            "SELECT COUNT(*) AS cnt FROM member_coupon mc {$whereStr}",
            $params
        )['cnt'] ?? 0);

        $list = $this->db->fetchAll(
            "SELECT mc.id, mc.coupon_name, mc.type, mc.value, mc.min_amount,
                    mc.expire_time, mc.status, mc.used_time, mc.receive_time
             FROM member_coupon mc
             {$whereStr}
             ORDER BY mc.status ASC, mc.expire_time ASC
             LIMIT :lmt OFFSET :off",
            array_merge($params, [':lmt' => $pageSize, ':off' => $offset])
        );

        return ['list' => $list, 'total' => $total];
    }

    // ----------------------------------------------------------------
    // 领取优惠券
    // ----------------------------------------------------------------

    /**
     * 会员领取优惠券
     *
     * @param  int $memberId   会员 ID
     * @param  int $templateId 优惠券模板 ID
     * @return int 新记录 ID
     * @throws \RuntimeException
     */
    public function claimCoupon(int $memberId, int $templateId): int
    {
        $this->db->beginTransaction();
        try {
            // 加锁查询模板
            $template = $this->db->fetchOne(
                'SELECT * FROM coupon_template
                 WHERE id = :id AND status = 1
                   AND start_time <= NOW() AND end_time >= NOW()
                   AND (total_count = 0 OR remain_count > 0)
                 FOR UPDATE',
                [':id' => $templateId]
            );
            if (!$template) {
                throw new \RuntimeException('优惠券不存在或已领完');
            }

            // 检查是否已领取（每种模板每人限领一张）
            $existing = $this->db->fetchOne(
                'SELECT id FROM member_coupon WHERE member_id = :mid AND template_id = :tid',
                [':mid' => $memberId, ':tid' => $templateId]
            );
            if ($existing) {
                throw new \RuntimeException('您已领取过该优惠券');
            }

            // 计算过期时间
            $expireTime = date('Y-m-d H:i:s', strtotime("+{$template['valid_days']} days"));

            // 写入持券记录
            $couponId = $this->db->insert(
                'INSERT INTO member_coupon
                    (member_id, template_id, coupon_name, type, value, min_amount,
                     expire_time, status, receive_time)
                 VALUES
                    (:mid, :tid, :name, :type, :value, :min_amount,
                     :expire, 0, NOW())',
                [
                    ':mid'        => $memberId,
                    ':tid'        => $templateId,
                    ':name'       => $template['name'],
                    ':type'       => $template['type'],
                    ':value'      => $template['value'],
                    ':min_amount' => $template['min_amount'],
                    ':expire'     => $expireTime,
                ]
            );

            // 减少模板剩余数量
            if ((int)$template['total_count'] > 0) {
                $this->db->execute(
                    'UPDATE coupon_template SET remain_count = remain_count - 1 WHERE id = :id',
                    [':id' => $templateId]
                );
            }

            $this->db->commit();
            return $couponId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw new \RuntimeException($e->getMessage());
        }
    }

    // ----------------------------------------------------------------
    // 过期处理（可由定时任务调用）
    // ----------------------------------------------------------------

    /**
     * 将所有已到期但状态仍为 0（未使用）的优惠券标记为过期（2）
     *
     * @return int 更新行数
     */
    public function expireOverdueCoupons(): int
    {
        return $this->db->execute(
            'UPDATE member_coupon SET status = 2 WHERE status = 0 AND expire_time < NOW()'
        );
    }

    /**
     * 根据 ID 获取持券记录（含模板信息）
     */
    public function getMemberCouponById(int $id): ?array
    {
        return $this->db->fetchOne(
            'SELECT mc.*, ct.description
             FROM member_coupon mc
             LEFT JOIN coupon_template ct ON ct.id = mc.template_id
             WHERE mc.id = :id',
            [':id' => $id]
        );
    }
}
