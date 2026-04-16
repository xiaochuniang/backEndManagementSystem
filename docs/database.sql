-- =============================================================
-- 门店收银 · 会员储值 后台管理系统 — 数据库建表脚本
-- 数据库：MySQL 8.0+
-- 字符集：utf8mb4  排序规则：utf8mb4_unicode_ci
-- 生成时间：2026-04-16
-- =============================================================

CREATE DATABASE IF NOT EXISTS shop_admin
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE shop_admin;

-- ===========================================================
-- 一、系统基础模块
-- ===========================================================

-- -------------------------------------------------------------
-- 1. 系统用户表（管理后台登录账号，含老板/员工/运营角色）
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS sys_user (
  id           BIGINT       UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  username     VARCHAR(64)  NOT NULL                        COMMENT '登录账号',
  password     VARCHAR(128) NOT NULL                        COMMENT '登录密码（bcrypt 加密）',
  nickname     VARCHAR(64)  NOT NULL DEFAULT ''             COMMENT '昵称/姓名',
  email        VARCHAR(128)          DEFAULT NULL           COMMENT '邮箱',
  phone        VARCHAR(20)           DEFAULT NULL           COMMENT '手机号',
  avatar       VARCHAR(512)          DEFAULT NULL           COMMENT '头像 URL',
  status       TINYINT(1)   NOT NULL DEFAULT 1              COMMENT '状态：1=启用  0=禁用',
  last_login   DATETIME              DEFAULT NULL           COMMENT '最后登录时间',
  create_time  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  update_time  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (id),
  UNIQUE KEY uk_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统用户表';


-- -------------------------------------------------------------
-- 2. 角色表
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS sys_role (
  id           BIGINT       UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  name         VARCHAR(64)  NOT NULL                        COMMENT '角色名称',
  code         VARCHAR(64)  NOT NULL                        COMMENT '角色编码（唯一标识）',
  description  VARCHAR(256)          DEFAULT ''             COMMENT '描述',
  status       TINYINT(1)   NOT NULL DEFAULT 1              COMMENT '状态：1=启用  0=禁用',
  create_time  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  update_time  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (id),
  UNIQUE KEY uk_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色表';


-- -------------------------------------------------------------
-- 3. 菜单/权限表
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS sys_menu (
  id           BIGINT       UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  parent_id    BIGINT       UNSIGNED NOT NULL DEFAULT 0     COMMENT '父菜单 ID，顶级为 0',
  name         VARCHAR(64)  NOT NULL                        COMMENT '路由组件名（英文唯一）',
  path         VARCHAR(256) NOT NULL DEFAULT ''             COMMENT '路由路径',
  component    VARCHAR(256)          DEFAULT NULL           COMMENT '前端组件路径',
  redirect     VARCHAR(256)          DEFAULT NULL           COMMENT '重定向地址',
  icon         VARCHAR(64)           DEFAULT NULL           COMMENT '菜单图标名',
  title        VARCHAR(64)  NOT NULL DEFAULT ''             COMMENT '菜单显示名称',
  hidden       TINYINT(1)   NOT NULL DEFAULT 0              COMMENT '是否隐藏：0=显示  1=隐藏',
  keep_alive   TINYINT(1)   NOT NULL DEFAULT 0              COMMENT '是否缓存：0=否  1=是',
  type         TINYINT(1)   NOT NULL DEFAULT 1              COMMENT '类型：1=菜单  2=按钮/权限',
  permission   VARCHAR(128)          DEFAULT NULL           COMMENT '权限标识符，如 boss:order:view',
  sort         INT          NOT NULL DEFAULT 0              COMMENT '显示排序',
  create_time  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  update_time  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='菜单与权限表';


-- -------------------------------------------------------------
-- 4. 用户 ↔ 角色 关联表
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS sys_user_role (
  user_id      BIGINT UNSIGNED NOT NULL COMMENT '用户 ID',
  role_id      BIGINT UNSIGNED NOT NULL COMMENT '角色 ID',
  PRIMARY KEY (user_id, role_id),
  KEY idx_role_id (role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户角色关联表';


-- -------------------------------------------------------------
-- 5. 角色 ↔ 菜单 关联表
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS sys_role_menu (
  role_id      BIGINT UNSIGNED NOT NULL COMMENT '角色 ID',
  menu_id      BIGINT UNSIGNED NOT NULL COMMENT '菜单/权限 ID',
  PRIMARY KEY (role_id, menu_id),
  KEY idx_menu_id (menu_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色菜单关联表';


-- ===========================================================
-- 二、商品管理模块
-- ===========================================================

-- -------------------------------------------------------------
-- 6. 商品分类表
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS product_category (
  id           BIGINT       UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  name         VARCHAR(64)  NOT NULL                        COMMENT '分类名称',
  sort         INT          NOT NULL DEFAULT 0              COMMENT '显示排序',
  remark       VARCHAR(256)          DEFAULT ''             COMMENT '备注',
  status       TINYINT(1)   NOT NULL DEFAULT 1              COMMENT '状态：1=启用  0=禁用',
  create_time  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  update_time  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商品分类表';


-- -------------------------------------------------------------
-- 7. 商品表
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS product (
  id           BIGINT       UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  category_id  BIGINT       UNSIGNED NOT NULL               COMMENT '所属分类 ID（product_category.id）',
  name         VARCHAR(128) NOT NULL                        COMMENT '商品名称',
  price        DECIMAL(10,2) NOT NULL DEFAULT 0.00          COMMENT '销售单价（元）',
  stock        INT          NOT NULL DEFAULT 0              COMMENT '当前库存数量',
  barcode      VARCHAR(64)           DEFAULT NULL           COMMENT '商品条码',
  image_url    VARCHAR(512)          DEFAULT NULL           COMMENT '商品图片 URL',
  status       TINYINT(1)   NOT NULL DEFAULT 1              COMMENT '状态：1=上架  0=下架',
  create_time  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  update_time  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (id),
  KEY idx_category (category_id),
  KEY idx_barcode (barcode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商品表';


-- ===========================================================
-- 三、订单管理模块
-- ===========================================================

-- -------------------------------------------------------------
-- 8. 订单主表
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS shop_order (
  id           BIGINT       UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  order_no     VARCHAR(32)  NOT NULL                        COMMENT '订单编号（唯一，如 SO20260416001）',
  member_id    BIGINT       UNSIGNED          DEFAULT NULL  COMMENT '会员 ID（非会员为 NULL）',
  staff_id     BIGINT       UNSIGNED NOT NULL               COMMENT '收银员工 ID（sys_user.id）',
  total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00          COMMENT '订单总金额（元）',
  pay_amount   DECIMAL(10,2) NOT NULL DEFAULT 0.00          COMMENT '实付金额（元，含折扣/积分抵扣）',
  pay_method   TINYINT(1)   NOT NULL DEFAULT 1              COMMENT '支付方式：1=微信支付  2=支付宝  3=现金  4=会员余额',
  status       TINYINT(1)   NOT NULL DEFAULT 1              COMMENT '订单状态：1=已完成  2=已退款  3=待支付',
  remark       VARCHAR(256)          DEFAULT ''             COMMENT '备注',
  create_time  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '下单时间',
  update_time  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (id),
  UNIQUE KEY uk_order_no (order_no),
  KEY idx_member (member_id),
  KEY idx_staff (staff_id),
  KEY idx_create_time (create_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单主表';


-- -------------------------------------------------------------
-- 9. 订单明细表
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS order_item (
  id           BIGINT       UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  order_id     BIGINT       UNSIGNED NOT NULL               COMMENT '订单 ID（shop_order.id）',
  product_id   BIGINT       UNSIGNED NOT NULL               COMMENT '商品 ID（product.id）',
  product_name VARCHAR(128) NOT NULL                        COMMENT '商品名称（下单时快照）',
  unit_price   DECIMAL(10,2) NOT NULL DEFAULT 0.00          COMMENT '商品单价（下单时快照，元）',
  quantity     INT          NOT NULL DEFAULT 1              COMMENT '购买数量',
  subtotal     DECIMAL(10,2) NOT NULL DEFAULT 0.00          COMMENT '小计金额（unit_price × quantity）',
  PRIMARY KEY (id),
  KEY idx_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单明细表';


-- ===========================================================
-- 四、会员管理模块
-- ===========================================================

-- -------------------------------------------------------------
-- 10. 会员等级表
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS member_level (
  id                 BIGINT       UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  name               VARCHAR(64)  NOT NULL                        COMMENT '等级名称（如 普通、银卡、金卡）',
  discount           DECIMAL(4,2) NOT NULL DEFAULT 1.00           COMMENT '折扣比例（1.00=不打折，0.88=88折）',
  upgrade_condition  VARCHAR(256)          DEFAULT ''             COMMENT '升级条件描述',
  sort               INT          NOT NULL DEFAULT 0              COMMENT '显示排序（数值越小越低级）',
  create_time        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  update_time        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='会员等级表';


-- -------------------------------------------------------------
-- 11. 会员表
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS member (
  id              BIGINT       UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  phone           VARCHAR(20)  NOT NULL                        COMMENT '手机号（唯一，用于登录/查询）',
  level_id        BIGINT       UNSIGNED NOT NULL DEFAULT 1     COMMENT '会员等级 ID（member_level.id）',
  balance         DECIMAL(10,2) NOT NULL DEFAULT 0.00          COMMENT '账户余额（储值余额，元）',
  points          INT          NOT NULL DEFAULT 0              COMMENT '当前积分',
  total_consume   DECIMAL(10,2) NOT NULL DEFAULT 0.00          COMMENT '累计消费金额（用于升级判断）',
  status          TINYINT(1)   NOT NULL DEFAULT 1              COMMENT '状态：1=正常  0=禁用',
  register_time   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '注册时间',
  update_time     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (id),
  UNIQUE KEY uk_phone (phone),
  KEY idx_level (level_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='会员表';


-- -------------------------------------------------------------
-- 12. 会员消费记录表
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS member_consume_record (
  id           BIGINT       UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  member_id    BIGINT       UNSIGNED NOT NULL               COMMENT '会员 ID（member.id）',
  order_id     BIGINT       UNSIGNED NOT NULL               COMMENT '订单 ID（shop_order.id）',
  order_no     VARCHAR(32)  NOT NULL                        COMMENT '订单编号（冗余，方便查询）',
  amount       DECIMAL(10,2) NOT NULL DEFAULT 0.00          COMMENT '本次消费金额（元）',
  create_time  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '消费时间',
  PRIMARY KEY (id),
  KEY idx_member (member_id),
  KEY idx_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='会员消费记录表';


-- ===========================================================
-- 五、储值管理模块
-- ===========================================================

-- -------------------------------------------------------------
-- 13. 储值规则表
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS deposit_rule (
  id           BIGINT       UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  amount       DECIMAL(10,2) NOT NULL                       COMMENT '充值金额（元）',
  gift         DECIMAL(10,2) NOT NULL DEFAULT 0.00          COMMENT '赠送金额（元）',
  remark       VARCHAR(256)          DEFAULT ''             COMMENT '规则说明（如：充300赠50）',
  status       TINYINT(1)   NOT NULL DEFAULT 1              COMMENT '状态：1=启用  0=禁用',
  create_time  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  update_time  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='储值规则表';


-- -------------------------------------------------------------
-- 14. 储值记录表
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS deposit_record (
  id           BIGINT       UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  member_id    BIGINT       UNSIGNED NOT NULL               COMMENT '会员 ID（member.id）',
  rule_id      BIGINT       UNSIGNED          DEFAULT NULL  COMMENT '使用的储值规则 ID（deposit_rule.id，自定义金额时为 NULL）',
  amount       DECIMAL(10,2) NOT NULL                       COMMENT '实际充值金额（元）',
  gift         DECIMAL(10,2) NOT NULL DEFAULT 0.00          COMMENT '赠送金额（元）',
  operator_id  BIGINT       UNSIGNED NOT NULL               COMMENT '操作员工 ID（sys_user.id）',
  create_time  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '充值时间',
  PRIMARY KEY (id),
  KEY idx_member (member_id),
  KEY idx_operator (operator_id),
  KEY idx_create_time (create_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='储值记录表';


-- ===========================================================
-- 六、积分管理模块
-- ===========================================================

-- -------------------------------------------------------------
-- 15. 积分规则配置表（单行配置表）
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS points_config (
  id            BIGINT       UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键（通常只有 1 行）',
  earn_rate     DECIMAL(6,2) NOT NULL DEFAULT 1.00           COMMENT '消费每 1 元获得积分数',
  redeem_rate   INT          NOT NULL DEFAULT 100            COMMENT '多少积分兑换 1 元',
  update_time   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='积分规则配置表';


-- -------------------------------------------------------------
-- 16. 积分变动记录表
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS points_record (
  id           BIGINT       UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  member_id    BIGINT       UNSIGNED NOT NULL               COMMENT '会员 ID（member.id）',
  change_val   INT          NOT NULL                        COMMENT '积分变动值（正=增加，负=减少）',
  type         TINYINT(1)   NOT NULL                        COMMENT '变动类型：1=消费获取  2=积分兑换  3=人工调整',
  remark       VARCHAR(256)          DEFAULT ''             COMMENT '说明（如：订单号或兑换商品名）',
  operator_id  BIGINT       UNSIGNED          DEFAULT NULL  COMMENT '操作员工 ID（人工调整时有值）',
  create_time  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '变动时间',
  PRIMARY KEY (id),
  KEY idx_member (member_id),
  KEY idx_create_time (create_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='积分变动记录表';


-- -------------------------------------------------------------
-- 17. 积分兑换商品表
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS points_exchange_item (
  id           BIGINT       UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  name         VARCHAR(128) NOT NULL                        COMMENT '兑换商品名称',
  points       INT          NOT NULL DEFAULT 0              COMMENT '所需积分数',
  stock        INT          NOT NULL DEFAULT 0              COMMENT '兑换库存',
  status       TINYINT(1)   NOT NULL DEFAULT 1              COMMENT '状态：1=上架  0=下架',
  create_time  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  update_time  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='积分兑换商品表';


-- ===========================================================
-- 七、员工管理模块
-- ===========================================================
-- 注：员工登录账号复用 sys_user 表，staff 表存储员工业务信息并关联 sys_user

-- -------------------------------------------------------------
-- 18. 员工信息表
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS staff (
  id           BIGINT       UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  user_id      BIGINT       UNSIGNED NOT NULL               COMMENT '关联系统用户 ID（sys_user.id）',
  name         VARCHAR(64)  NOT NULL                        COMMENT '员工姓名',
  phone        VARCHAR(20)           DEFAULT NULL           COMMENT '员工手机号',
  role         VARCHAR(64)  NOT NULL DEFAULT ''             COMMENT '员工岗位（如：收银员、店长、运营）',
  status       TINYINT(1)   NOT NULL DEFAULT 1              COMMENT '在职状态：1=在职  0=离职',
  create_time  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '入职/创建时间',
  update_time  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (id),
  UNIQUE KEY uk_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='员工信息表';


-- -------------------------------------------------------------
-- 19. 员工功能权限关联表
-- （存储界面级权限，如"收银/会员管理/订单查询/报表查看/员工管理"）
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS staff_permission (
  staff_id         BIGINT      UNSIGNED NOT NULL COMMENT '员工 ID（staff.id）',
  permission_code  VARCHAR(64) NOT NULL          COMMENT '权限码（如：收银、会员管理）',
  PRIMARY KEY (staff_id, permission_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='员工权限关联表';


-- ===========================================================
-- 八、系统设置模块
-- ===========================================================

-- -------------------------------------------------------------
-- 20. 店铺基础信息配置表（单行）
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS shop_config (
  id           INT          UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  shop_name    VARCHAR(128) NOT NULL DEFAULT ''             COMMENT '店铺名称',
  logo_url     VARCHAR(512)          DEFAULT NULL           COMMENT 'Logo 图片 URL',
  address      VARCHAR(256)          DEFAULT ''             COMMENT '门店地址',
  phone        VARCHAR(32)           DEFAULT ''             COMMENT '联系电话',
  description  VARCHAR(512)          DEFAULT ''             COMMENT '店铺介绍',
  update_time  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='店铺基础信息配置表';


-- -------------------------------------------------------------
-- 21. 支付方式配置表（单行，各支付渠道共用）
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payment_config (
  id              INT          UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  wx_app_id       VARCHAR(64)           DEFAULT NULL           COMMENT '微信支付 AppID',
  wx_mch_id       VARCHAR(64)           DEFAULT NULL           COMMENT '微信支付商户号',
  wx_api_key      VARCHAR(256)          DEFAULT NULL           COMMENT '微信支付 API 密钥（密文存储）',
  ali_app_id      VARCHAR(64)           DEFAULT NULL           COMMENT '支付宝 AppID',
  ali_private_key TEXT                  DEFAULT NULL           COMMENT '支付宝应用私钥（密文存储）',
  update_time     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='支付配置表';


-- -------------------------------------------------------------
-- 22. 小票打印配置表（单行）
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS receipt_config (
  id           INT          UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  enabled      TINYINT(1)   NOT NULL DEFAULT 1              COMMENT '是否自动打印：1=是  0=否',
  header       VARCHAR(256)          DEFAULT ''             COMMENT '小票抬头文字',
  footer       VARCHAR(512)          DEFAULT ''             COMMENT '小票底部文字',
  update_time  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='小票打印配置表';


-- ===========================================================
-- 九、交班对账模块
-- ===========================================================

-- -------------------------------------------------------------
-- 23. 交班记录表
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS shift_record (
  id           BIGINT       UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  staff_id     BIGINT       UNSIGNED NOT NULL               COMMENT '交班员工 ID（staff.id）',
  start_time   DATETIME     NOT NULL                        COMMENT '当班开始时间',
  end_time     DATETIME              DEFAULT NULL           COMMENT '当班结束时间（未结束为 NULL）',
  revenue      DECIMAL(10,2) NOT NULL DEFAULT 0.00          COMMENT '当班期间系统总营收（元）',
  order_count  INT          NOT NULL DEFAULT 0              COMMENT '当班期间订单数',
  remark       VARCHAR(512)          DEFAULT ''             COMMENT '交班备注/差异说明',
  create_time  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '记录创建时间',
  PRIMARY KEY (id),
  KEY idx_staff (staff_id),
  KEY idx_start_time (start_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='交班记录表';


-- -------------------------------------------------------------
-- 24. 营收对账记录表（按支付方式汇总，关联交班）
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS recon_record (
  id              BIGINT       UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  shift_id        BIGINT       UNSIGNED NOT NULL               COMMENT '交班记录 ID（shift_record.id）',
  pay_method      TINYINT(1)   NOT NULL                        COMMENT '支付方式：1=微信  2=支付宝  3=现金  4=会员余额',
  system_amount   DECIMAL(10,2) NOT NULL DEFAULT 0.00          COMMENT '系统统计金额（元）',
  actual_amount   DECIMAL(10,2) NOT NULL DEFAULT 0.00          COMMENT '实际收到金额（元）',
  diff_amount     DECIMAL(10,2) NOT NULL DEFAULT 0.00          COMMENT '差异金额（actual - system，元）',
  remark          VARCHAR(256)           DEFAULT ''            COMMENT '差异说明',
  create_time     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '记录时间',
  PRIMARY KEY (id),
  KEY idx_shift (shift_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='营收对账明细表';


-- ===========================================================
-- 十、短信验证码表（用于登录找回密码 / 手机号验证）
-- ===========================================================

-- -------------------------------------------------------------
-- 25. 短信验证码表
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS sms_verify_code (
  id           BIGINT       UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  phone        VARCHAR(20)  NOT NULL                        COMMENT '手机号',
  code         VARCHAR(10)  NOT NULL                        COMMENT '验证码',
  scene        VARCHAR(32)  NOT NULL DEFAULT 'login'        COMMENT '使用场景：login=登录  forgot_pwd=找回密码  member_verify=会员核销',
  used         TINYINT(1)   NOT NULL DEFAULT 0              COMMENT '是否已使用：0=未使用  1=已使用',
  expire_time  DATETIME     NOT NULL                        COMMENT '过期时间（通常为发送时间 + 5 分钟）',
  create_time  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '发送时间',
  PRIMARY KEY (id),
  KEY idx_phone_scene (phone, scene),
  KEY idx_expire_time (expire_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='短信验证码表';


-- ===========================================================
-- 十一、优惠券相关表（顾客小程序端）
-- ===========================================================

-- -------------------------------------------------------------
-- 26. 优惠券模板表（店铺配置的券种）
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS coupon_template (
  id           BIGINT       UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  name         VARCHAR(64)  NOT NULL                        COMMENT '券名称',
  type         TINYINT(1)   NOT NULL                        COMMENT '券类型：1=满减  2=折扣  3=免费赠品',
  value        DECIMAL(10,2) NOT NULL DEFAULT 0.00          COMMENT '优惠值：满减金额（元）或折扣率（如 0.9=9折）',
  min_amount   DECIMAL(10,2) NOT NULL DEFAULT 0.00          COMMENT '最低消费金额（元），0=无门槛',
  total_count  INT           NOT NULL DEFAULT 0             COMMENT '总发放数量，0=不限',
  remain_count INT           NOT NULL DEFAULT 0             COMMENT '剩余可领数量',
  start_time   DATETIME      NOT NULL                       COMMENT '活动开始时间',
  end_time     DATETIME      NOT NULL                       COMMENT '活动结束时间',
  valid_days   INT           NOT NULL DEFAULT 30            COMMENT '领取后有效天数',
  description  VARCHAR(256)           DEFAULT ''            COMMENT '券说明/使用须知',
  status       TINYINT(1)   NOT NULL DEFAULT 1              COMMENT '状态：0=停用  1=启用',
  create_time  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  update_time  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_status (status),
  KEY idx_end_time (end_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='优惠券模板表';

-- -------------------------------------------------------------
-- 27. 会员持券表（会员领取/获得的优惠券实例）
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS member_coupon (
  id           BIGINT       UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  member_id    BIGINT       UNSIGNED NOT NULL               COMMENT '会员 ID（member.id）',
  template_id  BIGINT       UNSIGNED NOT NULL               COMMENT '优惠券模板 ID（coupon_template.id）',
  coupon_name  VARCHAR(64)  NOT NULL                        COMMENT '券名称快照',
  type         TINYINT(1)   NOT NULL                        COMMENT '券类型快照',
  value        DECIMAL(10,2) NOT NULL DEFAULT 0.00          COMMENT '优惠值快照',
  min_amount   DECIMAL(10,2) NOT NULL DEFAULT 0.00          COMMENT '使用门槛快照',
  expire_time  DATETIME     NOT NULL                        COMMENT '过期时间',
  status       TINYINT(1)   NOT NULL DEFAULT 0              COMMENT '状态：0=未使用  1=已使用  2=已过期',
  order_id     BIGINT       UNSIGNED             DEFAULT NULL COMMENT '核销订单 ID（shop_order.id）',
  used_time    DATETIME                          DEFAULT NULL COMMENT '使用时间',
  receive_time DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '领取时间',
  PRIMARY KEY (id),
  KEY idx_member (member_id),
  KEY idx_template (template_id),
  KEY idx_status (status),
  KEY idx_expire_time (expire_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='会员持券表';


-- ===========================================================
-- ===========================================================

-- 默认会员等级
INSERT INTO member_level (id, name, discount, upgrade_condition, sort) VALUES
  (1, '普通', 1.00, '注册即享',         1),
  (2, '银卡', 0.95, '累计消费满500元',  2),
  (3, '金卡', 0.88, '累计消费满2000元', 3)
ON DUPLICATE KEY UPDATE update_time = NOW();

-- 默认积分规则配置
INSERT INTO points_config (id, earn_rate, redeem_rate) VALUES
  (1, 1.00, 100)
ON DUPLICATE KEY UPDATE update_time = NOW();

-- 默认店铺配置
INSERT INTO shop_config (id, shop_name, address) VALUES
  (1, '城南门店', '请在系统设置中完善店铺信息')
ON DUPLICATE KEY UPDATE update_time = NOW();

-- 默认小票配置
INSERT INTO receipt_config (id, enabled, header, footer) VALUES
  (1, 1, '欢迎光临', '感谢您的惠顾，欢迎再次光临！')
ON DUPLICATE KEY UPDATE update_time = NOW();

-- 默认角色
INSERT INTO sys_role (id, name, code, description, status) VALUES
  (1, '超级管理员', 'super_admin', '拥有全部权限', 1),
  (2, '老板',       'boss',        '老板端管理权限', 1),
  (3, '员工',       'staff',       '员工端操作权限', 1),
  (4, '顾客运营',   'customer_ops','顾客端运营权限', 1)
ON DUPLICATE KEY UPDATE update_time = NOW();

-- 默认优惠券模板示例
INSERT INTO coupon_template (id, name, type, value, min_amount, total_count, remain_count, start_time, end_time, valid_days, description, status) VALUES
  (1, '新人优惠券',  1, 10.00, 30.00,  1000, 980, '2026-01-01 00:00:00', '2026-12-31 23:59:59', 30, '新用户注册赠送，满30元可用', 1),
  (2, '春季特惠券',  1,  8.00, 50.00,  500,  420, '2026-04-01 00:00:00', '2026-04-30 23:59:59', 30, '春季活动专属，满50元减8元', 1),
  (3, '会员9折券',   2,  0.90,  0.00,  200,  150, '2026-01-01 00:00:00', '2026-12-31 23:59:59', 60, '会员专享9折优惠，无消费门槛', 1),
  (4, '下午茶8折券', 2,  0.80,  0.00,  300,  280, '2026-04-01 00:00:00', '2026-05-15 23:59:59', 45, '下午2-5点使用，全场8折', 1)
ON DUPLICATE KEY UPDATE update_time = NOW();
