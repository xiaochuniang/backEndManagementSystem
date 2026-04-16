# 门店收银 + 会员储值工程骨架（Monorepo）

基于 `Vue3 + TypeScript + Vite + Element Plus + Pinia + Vue Router` 的后台管理端，并在同仓库新增 `uni-app` 顾客小程序端，使用 `pnpm workspace` 组织。

## 项目结构

```text
.
├─ apps/
│  └─ miniapp-uni/              # uni-app 小程序端（Vue3 + TS）
├─ mock/                        # admin mock API
├─ src/                         # admin web 源码
│  ├─ layout/
│  ├─ router/
│  ├─ store/
│  └─ views/
│     ├─ boss/                  # 老板端
│     ├─ staff/                 # 员工端
│     └─ customer-ops/          # 顾客小程序后台运营端
├─ package.json                 # root + admin web scripts
└─ pnpm-workspace.yaml          # workspace 配置
```

## 后台管理端信息架构

- 老板端：`/boss/*`
  - Dashboard、收银、会员储值、门店管理、经营报表
- 员工端：`/staff/*`
  - Dashboard、收银、会员储值、订单列表
- 顾客小程序后台运营端：`/customer-ops/*`
  - Dashboard、小程序配置、会员运营、订单列表

登录页支持端切换（`boss / staff / customerOps`），并通过 mock 用户角色返回对应动态菜单路由。

## 安装与运行

> Node.js 18+，并启用 pnpm（可用 `corepack enable`）

```bash
pnpm install
```

### 启动后台管理端（admin web）

```bash
pnpm dev:admin
```

### 启动 uni-app（H5 预览）

```bash
pnpm dev:miniapp
```

### 构建

```bash
pnpm build:admin
pnpm build:miniapp
```

## uni-app 端说明

- 目录：`apps/miniapp-uni`
- 页面：
  - `pages/index/index` 首页（余额 + 储值入口）
  - `pages/member/index` 会员中心
  - `pages/recharge/index` 充值页
  - `pages/orders/index` 订单列表
- 当前使用本地 mock 数据（`apps/miniapp-uni/src/mock/member.ts`），后续可替换为真实接口。
- 如需小程序真机调试：可继续使用 `dev:mp-weixin` / `build:mp-weixin`，或用 HBuilderX 打开 `apps/miniapp-uni`。

## 后台 mock 测试账号

| 端 | 用户名 | 密码 |
|---|---|---|
| 老板端 | boss | boss123 |
| 员工端 | staff | staff123 |
| 顾客运营端 | customer | customer123 |
