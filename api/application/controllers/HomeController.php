<?php
/**
 * HomeController — 小程序首页
 *
 * 路由：
 *   GET /home/index      首页数据（店铺信息 + 公告 + 热门商品）
 */

use Yaf\Controller_Abstract;

class HomeController extends Controller_Abstract
{
    /**
     * GET /home/index
     *
     * 返回首页所需全部数据（一次性请求，减少小程序端网络调用次数）：
     *   - shop:       店铺基础信息（名称、Logo URL、地址、电话、公告）
     *   - hot_products: 首页热推商品（最多 8 条，按 sort 升序）
     *   - categories: 商品分类列表（供买单页使用）
     */
    public function indexAction(): void
    {
        $shopModel    = new ShopModel();
        $productModel = new ProductModel();

        $shopConfig = $shopModel->getShopConfig();

        // 热门商品（取前 8 条上架商品）
        $hotProducts = (new ProductModel())->getProductList('', 0, 1, 8)['list'];

        // 商品分类
        $categories = $productModel->getCategories();

        Response::success([
            'shop'         => $shopConfig ? [
                'name'       => $shopConfig['shop_name']    ?? '',
                'logo'       => $shopConfig['logo']         ?? '',
                'address'    => $shopConfig['address']      ?? '',
                'phone'      => $shopConfig['phone']        ?? '',
                'notice'     => $shopConfig['notice']       ?? '',
                'slogan'     => $shopConfig['slogan']       ?? '',
            ] : [],
            'hot_products' => $hotProducts,
            'categories'   => $categories,
        ]);
    }
}
