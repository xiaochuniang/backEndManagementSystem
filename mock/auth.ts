import type { IncomingMessage, ServerResponse } from 'http'
import { parseRequestBody, success, fail } from './utils'

interface MockUser {
  username: string
  password: string
  token: string
  userInfo: {
    id: number
    username: string
    nickname: string
    email: string
    phone: string
    avatar: string
    roles: string[]
    permissions: string[]
  }
}

const users: MockUser[] = [
  {
    username: 'admin',
    password: 'admin123',
    token: 'mock-token-admin-xxxx',
    userInfo: {
      id: 1,
      username: 'admin',
      nickname: '超级管理员',
      email: 'admin@example.com',
      phone: '13800138000',
      avatar: '',
      roles: ['admin'],
      permissions: [
        'system:user:list',
        'system:user:create',
        'system:user:update',
        'system:user:delete',
        'system:role:list',
        'system:role:create',
        'system:role:update',
        'system:role:delete',
        'system:menu:list',
        'system:menu:create',
        'system:menu:update',
        'system:menu:delete',
      ],
    },
  },
  {
    username: 'editor',
    password: 'editor123',
    token: 'mock-token-editor-xxxx',
    userInfo: {
      id: 2,
      username: 'editor',
      nickname: '编辑员',
      email: 'editor@example.com',
      phone: '13800138001',
      avatar: '',
      roles: ['editor'],
      permissions: ['system:user:list', 'system:role:list', 'system:menu:list'],
    },
  },
]

const menus = [
  {
    id: 1,
    parentId: 0,
    name: 'Dashboard',
    path: '/dashboard',
    component: 'views/dashboard/index',
    redirect: '',
    icon: 'Odometer',
    title: '首页',
    hidden: false,
    keepAlive: true,
    type: 1,
    permission: '',
    sort: 1,
    children: [],
  },
  {
    id: 2,
    parentId: 0,
    name: 'System',
    path: '/system',
    component: 'Layout',
    redirect: '/system/user',
    icon: 'Setting',
    title: '系统管理',
    hidden: false,
    keepAlive: false,
    type: 1,
    permission: '',
    sort: 2,
    children: [
      {
        id: 21,
        parentId: 2,
        name: 'User',
        path: '/system/user',
        component: 'views/system/user/index',
        redirect: '',
        icon: 'User',
        title: '用户管理',
        hidden: false,
        keepAlive: true,
        type: 1,
        permission: 'system:user:list',
        sort: 1,
        children: [],
      },
      {
        id: 22,
        parentId: 2,
        name: 'Role',
        path: '/system/role',
        component: 'views/system/role/index',
        redirect: '',
        icon: 'UserFilled',
        title: '角色管理',
        hidden: false,
        keepAlive: true,
        type: 1,
        permission: 'system:role:list',
        sort: 2,
        children: [],
      },
      {
        id: 23,
        parentId: 2,
        name: 'Menu',
        path: '/system/menu',
        component: 'views/system/menu/index',
        redirect: '',
        icon: 'Menu',
        title: '菜单管理',
        hidden: false,
        keepAlive: true,
        type: 1,
        permission: 'system:menu:list',
        sort: 3,
        children: [],
      },
    ],
  },
]

const editorMenus = [
  {
    id: 1,
    parentId: 0,
    name: 'Dashboard',
    path: '/dashboard',
    component: 'views/dashboard/index',
    redirect: '',
    icon: 'Odometer',
    title: '首页',
    hidden: false,
    keepAlive: true,
    type: 1,
    permission: '',
    sort: 1,
    children: [],
  },
  {
    id: 2,
    parentId: 0,
    name: 'System',
    path: '/system',
    component: 'Layout',
    redirect: '/system/user',
    icon: 'Setting',
    title: '系统管理',
    hidden: false,
    keepAlive: false,
    type: 1,
    permission: '',
    sort: 2,
    children: [
      {
        id: 21,
        parentId: 2,
        name: 'User',
        path: '/system/user',
        component: 'views/system/user/index',
        redirect: '',
        icon: 'User',
        title: '用户管理',
        hidden: false,
        keepAlive: true,
        type: 1,
        permission: 'system:user:list',
        sort: 1,
        children: [],
      },
    ],
  },
]

const tokenStore: Record<string, MockUser> = {}
users.forEach((u) => {
  tokenStore[u.token] = u
})

export function handleAuthMock(
  url: string,
  method: string,
  req: IncomingMessage,
  res: ServerResponse,
): boolean {
  if (url === '/api/auth/login' && method === 'POST') {
    handleLogin(req, res)
    return true
  }
  if (url === '/api/auth/userinfo' && method === 'GET') {
    handleUserInfo(req, res)
    return true
  }
  if (url === '/api/auth/logout' && method === 'POST') {
    res.setHeader('Content-Type', 'application/json')
    res.end(JSON.stringify(success(null)))
    return true
  }
  return false
}

async function handleLogin(req: IncomingMessage, res: ServerResponse) {
  const body = await parseRequestBody(req) as { username?: string; password?: string }
  const user = users.find((u) => u.username === body.username && u.password === body.password)
  res.setHeader('Content-Type', 'application/json')
  if (user) {
    res.end(JSON.stringify(success({ token: user.token })))
  } else {
    res.end(JSON.stringify(fail('用户名或密码错误', 400)))
  }
}

function handleUserInfo(req: IncomingMessage, res: ServerResponse) {
  const auth = req.headers.authorization || ''
  const token = auth.replace('Bearer ', '')
  const user = tokenStore[token]
  res.setHeader('Content-Type', 'application/json')
  if (user) {
    const userMenus = user.userInfo.roles.includes('admin') ? menus : editorMenus
    res.end(
      JSON.stringify(
        success({
          ...user.userInfo,
          menus: userMenus,
        }),
      ),
    )
  } else {
    res.statusCode = 200
    res.end(JSON.stringify(fail('token 无效', 401)))
  }
}
