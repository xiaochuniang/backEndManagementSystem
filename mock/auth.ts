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

type LoginBody = {
  username?: string
  password?: string
  role?: 'boss' | 'staff' | 'customerOps'
}

const users: MockUser[] = [
  {
    username: 'boss',
    password: 'boss123',
    token: 'mock-token-boss-xxxx',
    userInfo: {
      id: 1,
      username: 'boss',
      nickname: '老板账号',
      email: 'boss@example.com',
      phone: '13800138000',
      avatar: '',
      roles: ['boss'],
      permissions: [
        'boss:dashboard:view',
        'boss:cashier:view',
        'boss:member:view',
        'boss:store:view',
        'boss:report:view',
      ],
    },
  },
  {
    username: 'staff',
    password: 'staff123',
    token: 'mock-token-staff-xxxx',
    userInfo: {
      id: 2,
      username: 'staff',
      nickname: '员工账号',
      email: 'staff@example.com',
      phone: '13800138001',
      avatar: '',
      roles: ['staff'],
      permissions: [
        'staff:dashboard:view',
        'staff:cashier:view',
        'staff:member:view',
        'staff:order:view',
      ],
    },
  },
  {
    username: 'customer',
    password: 'customer123',
    token: 'mock-token-customer-xxxx',
    userInfo: {
      id: 3,
      username: 'customer',
      nickname: '顾客运营账号',
      email: 'customer@example.com',
      phone: '13800138002',
      avatar: '',
      roles: ['customerOps'],
      permissions: [
        'customerOps:dashboard:view',
        'customerOps:config:view',
        'customerOps:member:view',
        'customerOps:order:view',
      ],
    },
  },
]

const bossMenus = [
  {
    id: 1,
    parentId: 0,
    name: 'Boss',
    path: '/boss',
    component: 'Layout',
    redirect: '/boss/dashboard',
    icon: 'House',
    title: '老板端',
    hidden: false,
    keepAlive: false,
    type: 1,
    permission: '',
    sort: 1,
    children: [
      {
        id: 11,
        parentId: 1,
        name: 'BossDashboard',
        path: '/boss/dashboard',
        component: 'views/boss/dashboard/index',
        redirect: '',
        icon: 'DataBoard',
        title: '数据看板',
        hidden: false,
        keepAlive: true,
        type: 1,
        permission: 'boss:dashboard:view',
        sort: 1,
        children: [],
      },
      {
        id: 12,
        parentId: 1,
        name: 'BossProducts',
        path: '/boss/products',
        component: 'views/boss/products/index',
        redirect: '',
        icon: 'Goods',
        title: '商品管理',
        hidden: false,
        keepAlive: true,
        type: 1,
        permission: 'boss:product:view',
        sort: 2,
        children: [],
      },
      {
        id: 13,
        parentId: 1,
        name: 'BossOrders',
        path: '/boss/orders',
        component: 'views/boss/orders/index',
        redirect: '',
        icon: 'List',
        title: '订单管理',
        hidden: false,
        keepAlive: true,
        type: 1,
        permission: 'boss:order:view',
        sort: 3,
        children: [],
      },
      {
        id: 14,
        parentId: 1,
        name: 'BossMembers',
        path: '/boss/members',
        component: 'views/boss/members/index',
        redirect: '',
        icon: 'UserFilled',
        title: '会员管理',
        hidden: false,
        keepAlive: true,
        type: 1,
        permission: 'boss:member:view',
        sort: 4,
        children: [],
      },
      {
        id: 15,
        parentId: 1,
        name: 'BossDeposits',
        path: '/boss/deposits',
        component: 'views/boss/deposits/index',
        redirect: '',
        icon: 'Wallet',
        title: '储值管理',
        hidden: false,
        keepAlive: true,
        type: 1,
        permission: 'boss:deposit:view',
        sort: 5,
        children: [],
      },
      {
        id: 16,
        parentId: 1,
        name: 'BossPoints',
        path: '/boss/points',
        component: 'views/boss/points/index',
        redirect: '',
        icon: 'Star',
        title: '积分管理',
        hidden: false,
        keepAlive: true,
        type: 1,
        permission: 'boss:point:view',
        sort: 6,
        children: [],
      },
      {
        id: 17,
        parentId: 1,
        name: 'BossStaff',
        path: '/boss/staff',
        component: 'views/boss/staff/index',
        redirect: '',
        icon: 'Avatar',
        title: '员工管理',
        hidden: false,
        keepAlive: true,
        type: 1,
        permission: 'boss:staff:view',
        sort: 7,
        children: [],
      },
      {
        id: 18,
        parentId: 1,
        name: 'BossShift',
        path: '/boss/shift',
        component: 'views/boss/shift/index',
        redirect: '',
        icon: 'DocumentChecked',
        title: '交班对账',
        hidden: false,
        keepAlive: true,
        type: 1,
        permission: 'boss:shift:view',
        sort: 8,
        children: [],
      },
      {
        id: 19,
        parentId: 1,
        name: 'BossSettings',
        path: '/boss/settings',
        component: 'views/boss/settings/index',
        redirect: '',
        icon: 'Setting',
        title: '系统设置',
        hidden: false,
        keepAlive: false,
        type: 1,
        permission: 'boss:settings:view',
        sort: 9,
        children: [],
      },
    ],
  },
]

const staffMenus = [
  {
    id: 2,
    parentId: 0,
    name: 'Staff',
    path: '/staff',
    component: 'Layout',
    redirect: '/staff/dashboard',
    icon: 'Avatar',
    title: '员工端',
    hidden: false,
    keepAlive: false,
    type: 1,
    permission: '',
    sort: 1,
    children: [
      {
        id: 21,
        parentId: 2,
        name: 'StaffDashboard',
        path: '/staff/dashboard',
        component: 'views/staff/dashboard/index',
        redirect: '',
        icon: 'DataBoard',
        title: 'Dashboard',
        hidden: false,
        keepAlive: true,
        type: 1,
        permission: 'staff:dashboard:view',
        sort: 1,
        children: [],
      },
      {
        id: 22,
        parentId: 2,
        name: 'StaffCashier',
        path: '/staff/cashier',
        component: 'views/staff/cashier/index',
        redirect: '',
        icon: 'ShoppingTrolley',
        title: '收银',
        hidden: false,
        keepAlive: true,
        type: 1,
        permission: 'staff:cashier:view',
        sort: 2,
        children: [],
      },
      {
        id: 23,
        parentId: 2,
        name: 'StaffMembers',
        path: '/staff/members',
        component: 'views/staff/members/index',
        redirect: '',
        icon: 'Wallet',
        title: '会员储值',
        hidden: false,
        keepAlive: true,
        type: 1,
        permission: 'staff:member:view',
        sort: 3,
        children: [],
      },
      {
        id: 24,
        parentId: 2,
        name: 'StaffOrders',
        path: '/staff/orders',
        component: 'views/staff/orders/index',
        redirect: '',
        icon: 'Tickets',
        title: '订单列表',
        hidden: false,
        keepAlive: true,
        type: 1,
        permission: 'staff:order:view',
        sort: 4,
        children: [],
      },
    ],
  },
]

const customerOpsMenus = [
  {
    id: 3,
    parentId: 0,
    name: 'CustomerOps',
    path: '/customer-ops',
    component: 'Layout',
    redirect: '/customer-ops/dashboard',
    icon: 'Monitor',
    title: '顾客小程序端',
    hidden: false,
    keepAlive: false,
    type: 1,
    permission: '',
    sort: 1,
    children: [
      {
        id: 31,
        parentId: 3,
        name: 'CustomerOpsDashboard',
        path: '/customer-ops/dashboard',
        component: 'views/customer-ops/dashboard/index',
        redirect: '',
        icon: 'DataLine',
        title: 'Dashboard',
        hidden: false,
        keepAlive: true,
        type: 1,
        permission: 'customerOps:dashboard:view',
        sort: 1,
        children: [],
      },
      {
        id: 32,
        parentId: 3,
        name: 'CustomerOpsConfig',
        path: '/customer-ops/config',
        component: 'views/customer-ops/config/index',
        redirect: '',
        icon: 'Tools',
        title: '小程序配置',
        hidden: false,
        keepAlive: true,
        type: 1,
        permission: 'customerOps:config:view',
        sort: 2,
        children: [],
      },
      {
        id: 33,
        parentId: 3,
        name: 'CustomerOpsMembers',
        path: '/customer-ops/members',
        component: 'views/customer-ops/members/index',
        redirect: '',
        icon: 'User',
        title: '会员运营',
        hidden: false,
        keepAlive: true,
        type: 1,
        permission: 'customerOps:member:view',
        sort: 3,
        children: [],
      },
      {
        id: 34,
        parentId: 3,
        name: 'CustomerOpsOrders',
        path: '/customer-ops/orders',
        component: 'views/customer-ops/orders/index',
        redirect: '',
        icon: 'Document',
        title: '订单列表',
        hidden: false,
        keepAlive: true,
        type: 1,
        permission: 'customerOps:order:view',
        sort: 4,
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
  const body = (await parseRequestBody(req)) as LoginBody
  const roleToUsername = {
    boss: 'boss',
    staff: 'staff',
    customerOps: 'customer',
  }
  const expectedUsername = body.role ? roleToUsername[body.role] : body.username
  const user = users.find((u) => u.username === expectedUsername && u.password === body.password)
  res.setHeader('Content-Type', 'application/json')
  if (user) {
    res.end(JSON.stringify(success({ token: user.token })))
  } else {
    res.end(JSON.stringify(fail('用户名、密码或登录端错误', 400)))
  }
}

function handleUserInfo(req: IncomingMessage, res: ServerResponse) {
  const auth = req.headers.authorization || ''
  const token = auth.replace('Bearer ', '')
  const user = tokenStore[token]
  res.setHeader('Content-Type', 'application/json')
  if (user) {
    let userMenus = staffMenus
    if (user.userInfo.roles.includes('boss')) {
      userMenus = bossMenus
    } else if (user.userInfo.roles.includes('customerOps')) {
      userMenus = customerOpsMenus
    }
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
