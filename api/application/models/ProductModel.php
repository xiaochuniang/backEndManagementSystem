<?php
/**
 * ProductModel — 商品相关操作
 *
 * 对应数据表：product、product_category
 */
class ProductModel
{
    private Db $db;

    public function __construct()
    {
        $this->db = \Yaf\Registry::get('db');
    }

    // ----------------------------------------------------------------
    // 分类
    // ----------------------------------------------------------------

    /**
     * 获取所有启用的商品分类
     */
    public function getCategories(): array
    {
        return $this->db->fetchAll(
            'SELECT id, name, sort FROM product_category WHERE status = 1 ORDER BY sort ASC'
        );
    }

    // ----------------------------------------------------------------
    // 商品列表
    // ----------------------------------------------------------------

    /**
     * 分页查询商品（支持关键词搜索 + 分类筛选）
     *
     * @param  string $keyword     搜索关键词（模糊匹配商品名称）
     * @param  int    $categoryId  分类 ID，0 表示全部
     * @param  int    $page        页码（从 1 开始）
     * @param  int    $pageSize    每页条数
     * @return array  [list => [...], total => int]
     */
    public function getProductList(
        string $keyword = '',
        int    $categoryId = 0,
        int    $page = 1,
        int    $pageSize = 20
    ): array {
        $where  = ['p.status = 1'];
        $params = [];

        if ($categoryId > 0) {
            $where[]  = 'p.category_id = :category_id';
            $params[':category_id'] = $categoryId;
        }

        if ($keyword !== '') {
            $where[]          = 'p.name LIKE :keyword';
            $params[':keyword'] = "%{$keyword}%";
        }

        $whereStr = 'WHERE ' . implode(' AND ', $where);
        $offset   = ($page - 1) * $pageSize;

        $total = (int)($this->db->fetchOne(
            "SELECT COUNT(*) AS cnt FROM product p {$whereStr}",
            $params
        )['cnt'] ?? 0);

        $list = $this->db->fetchAll(
            "SELECT p.id, p.name, p.price, p.image, p.description, p.stock,
                    pc.name AS category_name
             FROM product p
             LEFT JOIN product_category pc ON pc.id = p.category_id
             {$whereStr}
             ORDER BY p.sort ASC, p.id ASC
             LIMIT :limit OFFSET :offset",
            array_merge($params, [':limit' => $pageSize, ':offset' => $offset])
        );

        return ['list' => $list, 'total' => $total, 'page' => $page, 'pageSize' => $pageSize];
    }

    /**
     * 根据 ID 获取商品详情
     */
    public function getProductById(int $id): ?array
    {
        return $this->db->fetchOne(
            'SELECT p.*, pc.name AS category_name
             FROM product p
             LEFT JOIN product_category pc ON pc.id = p.category_id
             WHERE p.id = :id AND p.status = 1',
            [':id' => $id]
        );
    }

    /**
     * 批量获取商品（用于购物车校验）
     *
     * @param  int[] $ids
     * @return array  以 id 为键的商品数组
     */
    public function getProductsByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $rows = $this->db->fetchAll(
            "SELECT id, name, price, stock, status FROM product WHERE id IN ({$placeholders})",
            $ids
        );
        $result = [];
        foreach ($rows as $row) {
            $result[$row['id']] = $row;
        }
        return $result;
    }
}
