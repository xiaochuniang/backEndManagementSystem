<?php
/**
 * ShopModel — 店铺基础信息
 *
 * 对应数据表：shop_config
 */
class ShopModel
{
    private Db $db;

    public function __construct()
    {
        $this->db = \Yaf\Registry::get('db');
    }

    /**
     * 获取店铺配置（全局唯一行，id=1）
     */
    public function getShopConfig(): ?array
    {
        return $this->db->fetchOne('SELECT * FROM shop_config WHERE id = 1');
    }
}
