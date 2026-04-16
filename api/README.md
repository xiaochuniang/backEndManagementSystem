# 顾客小程序端 PHP+YAF 接口文档

## 目录结构

```
api/
├── application/
│   ├── Bootstrap.php               # YAF 引导（CORS、DB 初始化）
│   ├── controllers/
│   │   ├── AuthController.php      # 登录 / 短信验证码
│   │   ├── HomeController.php      # 首页
│   │   ├── ProductController.php   # 商品搜索 / 列表
│   │   ├── OrderController.php     # 在线买单 / 结算 / 微信支付
│   │   ├── MemberController.php    # 会员中心
│   │   ├── CouponController.php    # 优惠券
│   │   └── AboutController.php     # 关于我们
│   ├── models/
│   │   ├── ShopModel.php           # 店铺配置
│   │   ├── ProductModel.php        # 商品 / 分类
│   │   ├── OrderModel.php          # 订单
│   │   ├── MemberModel.php         # 会员
│   │   └── CouponModel.php         # 优惠券
│   └── library/
│       ├── Db.php                  # PDO 单例
│       ├── Response.php            # 统一 JSON 响应
│       ├── Auth.php                # JWT 认证
│       └── WechatPay.php          # 微信支付 V3 封装
├── conf/
│   └── app.ini                     # 应用配置（需按实际修改）
└── public/
    ├── index.php                   # 入口文件
    └── .htaccess                   # URL 重写
```

## 环境要求

| 项目 | 版本 |
|------|------|
| PHP  | >= 8.0 |
| YAF  | >= 3.3 |
| MySQL| >= 5.7 |
| 扩展  | pdo_mysql, openssl, curl |

## 快速部署

1. **安装 YAF 扩展**

```bash
pecl install yaf
# php.ini 中添加：extension=yaf.so
```

2. **配置文件**

```bash
cp api/conf/app.ini api/conf/app.ini
# 修改 db.*、wechat.*、wxpay.* 为实际配置
```

3. **Web Server 配置（Nginx 示例）**

```nginx
server {
    listen 80;
    server_name api.your-domain.com;
    root /path/to/api/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    location ~ \.php$ {
        fastcgi_pass   127.0.0.1:9000;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include        fastcgi_params;
    }
}
```

4. **执行数据库建表**

```bash
mysql -u root -p shop_db < docs/database.sql
```

---

## 接口列表

> 所有接口统一返回格式：
> ```json
> { "code": 200, "message": "success", "data": { ... } }
> ```
> 鉴权失败返回 `code: 401`，参数错误返回 `code: 400`。

### 一、认证模块 `/auth`

| 方法 | 路径 | 说明 | 是否需要 Token |
|------|------|------|---------------|
| POST | `/auth/sendSms`  | 发送短信验证码 | ❌ |
| POST | `/auth/login`    | 手机号+验证码登录 | ❌ |
| POST | `/auth/refresh`  | 刷新 Token | ✅ |

#### POST `/auth/sendSms`

```json
// Request
{ "phone": "138xxxxxxxx" }

// Response
{ "code": 200, "message": "验证码已发送", "data": null }
```

#### POST `/auth/login`

```json
// Request
{
  "code":     "wx.login() 返回的临时 code",
  "phone":    "138xxxxxxxx",
  "sms_code": "123456"
}

// Response
{
  "code": 200,
  "data": {
    "token": "eyJ...",
    "member": {
      "id": 1, "phone": "138****0001",
      "level_name": "银卡", "balance": "328.50", "points": 1680
    }
  }
}
```

---

### 二、首页模块 `/home`

| 方法 | 路径 | 说明 | Token |
|------|------|------|-------|
| GET  | `/home/index` | 首页数据（店铺信息+热门商品+分类） | ❌ |

---

### 三、商品模块 `/product`

| 方法 | 路径 | 说明 | Token |
|------|------|------|-------|
| GET  | `/product/list`       | 商品列表（搜索+分类+分页） | ❌ |
| GET  | `/product/detail`     | 商品详情（?id=1）         | ❌ |
| GET  | `/product/categories` | 分类列表                  | ❌ |

#### GET `/product/list`

Query 参数：

| 参数 | 类型 | 说明 |
|------|------|------|
| keyword     | string | 搜索关键词（模糊匹配） |
| category_id | int    | 分类 ID，0=全部        |
| page        | int    | 页码，默认 1           |
| page_size   | int    | 每页条数，默认 20      |

---

### 四、在线买单 `/order`

| 方法 | 路径 | 说明 | Token |
|------|------|------|-------|
| POST | `/order/create`  | 创建订单（购物车结算） | ✅ |
| POST | `/order/prepay`  | 发起微信预支付         | ✅ |
| GET  | `/order/detail`  | 订单详情（?id=1）      | ✅ |
| POST | `/pay/notify`    | 微信支付结果回调       | ❌（微信服务器调用）|

#### POST `/order/create`

```json
// Request
{
  "items": [
    { "product_id": 1, "qty": 2 },
    { "product_id": 3, "qty": 1 }
  ],
  "coupon_id": 5
}

// Response
{
  "order_id": 123, "order_no": "MP20260416000001",
  "pay_amount": 45.00, "items": [...]
}
```

#### POST `/order/prepay`

```json
// Request  { "order_id": 123 }
// Response  wx.requestPayment() 参数
{
  "timeStamp": "1713229369",
  "nonceStr":  "abc123...",
  "package":   "prepay_id=wx...",
  "signType":  "RSA",
  "paySign":   "..."
}
```

---

### 五、会员中心 `/member`

| 方法 | 路径 | 说明 | Token |
|------|------|------|-------|
| GET  | `/member/info`           | 个人信息（余额/积分/等级） | ✅ |
| GET  | `/member/depositRules`   | 储值规则列表              | ✅ |
| POST | `/member/deposit`        | 发起储值支付              | ✅ |
| POST | `/member/depositNotify`  | 储值微信回调              | ❌ |
| GET  | `/member/consumeRecords` | 消费记录（分页）          | ✅ |
| GET  | `/member/depositRecords` | 储值记录（分页）          | ✅ |
| GET  | `/member/pointsRecords`  | 积分记录（分页）          | ✅ |

---

### 六、优惠券 `/coupon`

| 方法 | 路径 | 说明 | Token |
|------|------|------|-------|
| GET  | `/coupon/mine`       | 我的优惠券（?status=unused\|used\|expired） | ✅ |
| GET  | `/coupon/available`  | 可领取优惠券列表 | ✅ |
| POST | `/coupon/claim`      | 领取优惠券       | ✅ |

---

### 七、关于我们 `/about`

| 方法 | 路径 | 说明 | Token |
|------|------|------|-------|
| GET  | `/about/index` | 店铺信息（地址/电话/简介） | ❌ |

---

## 统一响应码

| code | 说明 |
|------|------|
| 200  | 成功 |
| 400  | 参数错误 |
| 401  | 未登录 / Token 过期 |
| 404  | 资源不存在 |
| 429  | 请求过于频繁 |
| 500  | 服务器内部错误 |
