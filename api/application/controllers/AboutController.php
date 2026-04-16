<?php
/**
 * AboutController — 关于我们
 *
 * 路由：
 *   GET /about/index   店铺信息（地址、联系电话、营业时间等）
 *
 * 此接口无需登录，任何用户均可访问。
 */

use Yaf\Controller_Abstract;

class AboutController extends Controller_Abstract
{
    /**
     * GET /about/index
     *
     * 返回店铺公开信息，供小程序"关于我们"页面展示：
     *   - shop_name:      店铺名称
     *   - logo:           Logo 图片 URL
     *   - address:        门店地址
     *   - phone:          联系电话
     *   - service_hours:  营业时间
     *   - intro:          店铺简介
     *   - branches:       分店列表（JSON 字段，若有）
     */
    public function indexAction(): void
    {
        $shopConfig = (new ShopModel())->getShopConfig();

        if (!$shopConfig) {
            Response::success([]);
            return;
        }

        // branches 字段若为 JSON 字符串则解析，否则返回空数组
        $branches = [];
        if (!empty($shopConfig['branches'])) {
            $decoded  = json_decode($shopConfig['branches'], true);
            $branches = is_array($decoded) ? $decoded : [];
        }

        Response::success([
            'shop_name'     => $shopConfig['shop_name']     ?? '',
            'logo'          => $shopConfig['logo']          ?? '',
            'address'       => $shopConfig['address']       ?? '',
            'phone'         => $shopConfig['phone']         ?? '',
            'service_hours' => $shopConfig['service_hours'] ?? '',
            'intro'         => $shopConfig['intro']         ?? '',
            'wechat'        => $shopConfig['wechat']        ?? '',
            'branches'      => $branches,
        ]);
    }
}
