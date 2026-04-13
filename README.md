# Vue3 Admin System

基于 Vue 3.4 + TypeScript + Vite + Element Plus + Pinia + Vue Router 4 的后台管理系统。

## 技术栈

- **Vue 3.4** — Composition API + `<script setup>`
- **TypeScript** — 全项目类型安全，无 any
- **Vite 5** — 快速构建工具
- **Element Plus** — 按需引入（unplugin-vue-components + unplugin-auto-import）
- **Pinia** — 状态管理
- **Vue Router 4** — 动态路由 + 菜单权限控制
- **Axios** — 统一封装，请求/响应拦截、错误处理、token 注入、取消请求
- **Mock 数据** — 自定义 Vite 插件，无需真实后端即可运行

## 功能

- ✅ 登录页（账号密码登录，Mock 接口）
- ✅ Layout 布局（侧边栏 + 顶部导航 + 多标签页）
- ✅ 动态路由、菜单权限控制（根据用户角色动态生成路由和菜单）
- ✅ 按钮权限指令 `v-permission`（支持 string / string[]）
- ✅ Axios 封装（请求/响应拦截、统一错误提示、取消请求、泛型返回体）
- ✅ Pinia 状态管理（用户模块 + 应用配置模块）
- ✅ 工具函数（dayjs 时间格式化、防抖、节流）
- ✅ 示例页面：Dashboard、用户管理、角色管理、菜单管理
- ✅ 完整 TypeScript 类型定义
- ✅ ESLint + Prettier 代码规范

## 安装与启动

```bash
# 安装依赖
pnpm install

# 启动开发服务器
pnpm dev

# 构建生产版本
pnpm build

# 预览生产版本
pnpm preview

# 代码检查
pnpm lint

# 代码格式化
pnpm format
```

## 测试账号

| 角色 | 用户名 | 密码 | 说明 |
|------|--------|------|------|
| 管理员 | admin | admin123 | 拥有所有权限，可见所有菜单和按钮 |
| 编辑员 | editor | editor123 | 仅有查看权限，部分按钮不可见 |

## 权限说明

### 菜单权限
- 登录后根据角色获取对应的菜单树
- 动态生成路由，未授权的菜单不可见且不可访问
- `admin` 角色可访问所有页面
- `editor` 角色仅可访问首页和用户管理页面

### 按钮权限
- 使用 `v-permission` 指令控制按钮显示
- 支持传入单个权限字符串或权限字符串数组
- 示例：`v-permission="'system:user:create'"` 或 `v-permission="['system:user:create', 'system:user:update']"`
- `admin` 角色拥有增删改查所有按钮
- `editor` 角色只有查看权限，新增/编辑/删除按钮不可见

## 项目结构

```
src/
├── api/            # 接口封装（Axios 请求函数）
├── assets/         # 静态资源
├── components/     # 公共组件
├── directives/     # 自定义指令（v-permission）
├── hooks/          # 常用 Hooks
├── layout/         # 布局组件（侧边栏 + 顶部 + 标签页）
├── router/         # 路由配置（动态路由 + 路由守卫）
├── store/          # Pinia 状态管理
├── styles/         # 全局样式
├── types/          # TypeScript 类型定义
├── utils/          # 工具函数
├── views/          # 页面组件
├── App.vue         # 根组件
└── main.ts         # 入口文件
```

## Mock 数据

项目使用自定义 Vite 插件实现 Mock 数据，无需真实后端：
- `/api/auth/login` — 登录接口
- `/api/auth/userinfo` — 获取用户信息、权限、菜单
- `/api/auth/logout` — 退出登录
- `/api/user/list` — 用户列表（分页 + 搜索）
- `/api/user/create` — 新增用户
- `/api/user/update` — 更新用户
- `/api/user/delete/:id` — 删除用户
- `/api/role/list` — 角色列表
- `/api/role/all` — 所有角色
- `/api/role/create` — 新增角色
- `/api/role/update` — 更新角色
- `/api/role/delete/:id` — 删除角色
- `/api/menu/tree` — 菜单树
- `/api/menu/create` — 新增菜单
- `/api/menu/update` — 更新菜单
- `/api/menu/delete/:id` — 删除菜单
