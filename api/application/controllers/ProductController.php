<?php
/**
 * ProductController — 商品搜索 / 列表（供在线买单模块使用）
 *
 * 路由：
 *   GET /product/list        商品列表（搜索 + 分类筛选 + 分页）
 *   GET /product/detail/:id  商品详情
 *   GET /product/categories  商品分类列表
 */

use Yaf\Controller_Abstract;

class ProductController extends Controller_Abstract
{
    /**
     * GET /product/list
     *
     * Query params:
     *   keyword     string  搜索关键词（可选）
     *   category_id int     分类 ID，0=全部（可选，默认0）
     *   page        int     页码（默认1）
     *   page_size   int     每页条数（默认20，最大50）
     */
    public function listAction(): void
    {
        $keyword    = trim($this->getRequest()->getQuery('keyword', ''));
        $categoryId = (int)$this->getRequest()->getQuery('category_id', 0);
        $page       = max(1, (int)$this->getRequest()->getQuery('page', 1));
        $pageSize   = min(50, max(1, (int)$this->getRequest()->getQuery('page_size', 20)));

        $model  = new ProductModel();
        $result = $model->getProductList($keyword, $categoryId, $page, $pageSize);

        Response::success($result);
    }

    /**
     * GET /product/detail
     *
     * Query params:
     *   id  int  商品 ID（必填）
     */
    public function detailAction(): void
    {
        $id = (int)$this->getRequest()->getQuery('id', 0);
        if ($id <= 0) {
            Response::error('参数错误：缺少商品 ID');
        }

        $product = (new ProductModel())->getProductById($id);
        if (!$product) {
            Response::notFound('商品不存在或已下架');
        }

        Response::success($product);
    }

    /**
     * GET /product/categories
     *
     * 获取全部启用的商品分类列表
     */
    public function categoriesAction(): void
    {
        $categories = (new ProductModel())->getCategories();
        Response::success($categories);
    }
}
