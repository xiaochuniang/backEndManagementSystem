<?php
/**
 * CouponController — 优惠券
 *
 * 路由（均需登录 Token）：
 *   GET  /coupon/mine       我的优惠券列表
 *   GET  /coupon/available  可领取的优惠券列表
 *   POST /coupon/claim      领取优惠券
 */

use Yaf\Controller_Abstract;

class CouponController extends Controller_Abstract
{
    // ----------------------------------------------------------------
    // GET /coupon/mine
    // ----------------------------------------------------------------

    /**
     * 我的优惠券
     *
     * Query params:
     *   status    string  unused / used / expired（可选，默认全部）
     *   page      int     页码（默认1）
     *   page_size int     每页条数（默认20）
     */
    public function mineAction(): void
    {
        $payload  = Auth::requireLogin();
        $status   = $this->getRequest()->getQuery('status', null);
        $page     = max(1, (int)$this->getRequest()->getQuery('page', 1));
        $pageSize = min(50, max(1, (int)$this->getRequest()->getQuery('page_size', 20)));

        // 触发过期标记（懒加载方式，轻量处理）
        (new CouponModel())->expireOverdueCoupons();

        $result = (new CouponModel())->getMemberCoupons(
            $payload['member_id'],
            $status ?: null,
            $page,
            $pageSize
        );

        Response::success($result);
    }

    // ----------------------------------------------------------------
    // GET /coupon/available
    // ----------------------------------------------------------------

    /**
     * 可领取的优惠券列表（含"是否已领取"标记）
     */
    public function availableAction(): void
    {
        $payload   = Auth::requireLogin();
        $templates = (new CouponModel())->getAvailableTemplates($payload['member_id']);
        Response::success($templates);
    }

    // ----------------------------------------------------------------
    // POST /coupon/claim
    // ----------------------------------------------------------------

    /**
     * 领取优惠券
     *
     * Request JSON: { "template_id": 1 }
     *
     * Response: { "coupon_id": 123 }
     */
    public function claimAction(): void
    {
        $payload    = Auth::requireLogin();
        $body       = json_decode(file_get_contents('php://input'), true) ?? [];
        $templateId = (int)($body['template_id'] ?? 0);

        if ($templateId <= 0) {
            Response::error('参数错误：缺少 template_id');
        }

        try {
            $couponId = (new CouponModel())->claimCoupon($payload['member_id'], $templateId);
            Response::success(['coupon_id' => $couponId], '领取成功');
        } catch (\RuntimeException $e) {
            Response::error($e->getMessage());
        }
    }
}
