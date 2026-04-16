-- =====================================================================
-- 顾客小程序端接口所需的数据库扩展迁移脚本
-- 在已执行 database.sql 的基础上追加运行此脚本
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1. 扩展 shop_config 表（新增小程序首页 / 关于我们所需字段）
-- ---------------------------------------------------------------------
ALTER TABLE shop_config
  ADD COLUMN IF NOT EXISTS slogan        VARCHAR(256) DEFAULT '' COMMENT '店铺宣传语',
  ADD COLUMN IF NOT EXISTS notice        TEXT         DEFAULT NULL COMMENT '首页公告文本',
  ADD COLUMN IF NOT EXISTS service_hours VARCHAR(128) DEFAULT '' COMMENT '营业时间，如 09:00-22:00',
  ADD COLUMN IF NOT EXISTS intro         TEXT         DEFAULT NULL COMMENT '店铺简介',
  ADD COLUMN IF NOT EXISTS wechat        VARCHAR(64)  DEFAULT '' COMMENT '官方微信号',
  ADD COLUMN IF NOT EXISTS branches      JSON         DEFAULT NULL COMMENT '分店列表 JSON';

-- 将已有 logo_url 字段的值映射到 logo（小程序端使用 logo 字段名）
ALTER TABLE shop_config
  ADD COLUMN IF NOT EXISTS logo VARCHAR(512) DEFAULT NULL COMMENT 'Logo 图片 URL（小程序用）';

UPDATE shop_config SET logo = logo_url WHERE logo IS NULL AND logo_url IS NOT NULL;

-- ---------------------------------------------------------------------
-- 2. 扩展 shop_order 表（新增小程序端订单所需字段）
-- ---------------------------------------------------------------------
ALTER TABLE shop_order
  MODIFY COLUMN staff_id     BIGINT UNSIGNED DEFAULT NULL COMMENT '收银员工 ID（小程序自助下单时为 NULL）',
  ADD COLUMN IF NOT EXISTS discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT '优惠抵扣金额（元）',
  ADD COLUMN IF NOT EXISTS coupon_id       BIGINT UNSIGNED DEFAULT NULL       COMMENT '使用的优惠券 ID（member_coupon.id）',
  ADD COLUMN IF NOT EXISTS pay_status      TINYINT(1) NOT NULL DEFAULT 0      COMMENT '支付状态：0=待支付  1=已支付  2=已退款',
  ADD COLUMN IF NOT EXISTS order_status    TINYINT(1) NOT NULL DEFAULT 1      COMMENT '订单状态：1=待接单  2=已完成  3=待支付（小程序）',
  ADD COLUMN IF NOT EXISTS channel         VARCHAR(32) NOT NULL DEFAULT 'pos' COMMENT '下单渠道：pos=门店收银  miniapp=小程序',
  ADD COLUMN IF NOT EXISTS transaction_id  VARCHAR(64) DEFAULT NULL           COMMENT '微信支付流水号',
  ADD COLUMN IF NOT EXISTS pay_time        DATETIME DEFAULT NULL              COMMENT '支付完成时间';

-- ---------------------------------------------------------------------
-- 3. 扩展 deposit_record 表（新增小程序端储值所需字段）
-- ---------------------------------------------------------------------
ALTER TABLE deposit_record
  MODIFY COLUMN operator_id BIGINT UNSIGNED DEFAULT NULL COMMENT '操作员工 ID（小程序自助储值时为 NULL）',
  ADD COLUMN IF NOT EXISTS order_no       VARCHAR(32) DEFAULT NULL COMMENT '储值支付订单号',
  ADD COLUMN IF NOT EXISTS transaction_id VARCHAR(64) DEFAULT NULL COMMENT '微信支付流水号';

-- ---------------------------------------------------------------------
-- 4. 扩展 deposit_rule 表（新增排序字段）
-- ---------------------------------------------------------------------
ALTER TABLE deposit_rule
  ADD COLUMN IF NOT EXISTS sort TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序（升序展示）';

-- 初始化 sort 值（按 amount 升序）
UPDATE deposit_rule dr
JOIN (
  SELECT id, ROW_NUMBER() OVER (ORDER BY amount ASC) AS rn FROM deposit_rule
) ranked ON ranked.id = dr.id
SET dr.sort = ranked.rn
WHERE dr.sort = 0;

-- ---------------------------------------------------------------------
-- 5. 扩展 member_consume_record 表（关联 order_id）
-- ---------------------------------------------------------------------
ALTER TABLE member_consume_record
  ADD COLUMN IF NOT EXISTS order_id BIGINT UNSIGNED DEFAULT NULL COMMENT '关联订单 ID（shop_order.id）';
