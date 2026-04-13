import type { IncomingMessage, ServerResponse } from 'http'
import { parseRequestBody, success } from './utils'

interface MockMenuItem {
  id: number
  parentId: number
  name: string
  path: string
  component: string
  redirect: string
  icon: string
  title: string
  hidden: boolean
  keepAlive: boolean
  type: number
  permission: string
  sort: number
  children: MockMenuItem[]
}

const menuTree: MockMenuItem[] = [
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

export function handleMenuMock(
  url: string,
  method: string,
  req: IncomingMessage,
  res: ServerResponse,
): boolean {
  const basePath = url.split('?')[0]

  if (basePath === '/api/menu/tree' && method === 'GET') {
    res.setHeader('Content-Type', 'application/json')
    res.end(JSON.stringify(success(menuTree)))
    return true
  }

  if (basePath === '/api/menu/create' && method === 'POST') {
    handleCreate(req, res)
    return true
  }

  if (basePath === '/api/menu/update' && method === 'PUT') {
    handleUpdate(req, res)
    return true
  }

  if (basePath.startsWith('/api/menu/delete/') && method === 'DELETE') {
    const id = parseInt(basePath.split('/').pop() || '0', 10)
    removeMenuById(menuTree, id)
    res.setHeader('Content-Type', 'application/json')
    res.end(JSON.stringify(success(null)))
    return true
  }

  return false
}

function removeMenuById(menus: MockMenuItem[], id: number): boolean {
  for (let i = 0; i < menus.length; i++) {
    if (menus[i].id === id) {
      menus.splice(i, 1)
      return true
    }
    if (menus[i].children && removeMenuById(menus[i].children, id)) {
      return true
    }
  }
  return false
}

async function handleCreate(req: IncomingMessage, res: ServerResponse) {
  const body = await parseRequestBody(req)
  const newMenu: MockMenuItem = {
    id: Date.now(),
    parentId: (body.parentId as number) || 0,
    name: (body.name as string) || '',
    path: (body.path as string) || '',
    component: (body.component as string) || '',
    redirect: (body.redirect as string) || '',
    icon: (body.icon as string) || '',
    title: (body.title as string) || '',
    hidden: (body.hidden as boolean) || false,
    keepAlive: (body.keepAlive as boolean) || false,
    type: (body.type as number) || 1,
    permission: (body.permission as string) || '',
    sort: (body.sort as number) || 0,
    children: [],
  }

  if (newMenu.parentId === 0) {
    menuTree.push(newMenu)
  } else {
    const parent = findMenuById(menuTree, newMenu.parentId)
    if (parent) {
      parent.children.push(newMenu)
    }
  }

  res.setHeader('Content-Type', 'application/json')
  res.end(JSON.stringify(success(null)))
}

function findMenuById(menus: MockMenuItem[], id: number): MockMenuItem | null {
  for (const menu of menus) {
    if (menu.id === id) return menu
    if (menu.children) {
      const found = findMenuById(menu.children, id)
      if (found) return found
    }
  }
  return null
}

async function handleUpdate(req: IncomingMessage, res: ServerResponse) {
  const body = await parseRequestBody(req)
  const menu = findMenuById(menuTree, body.id as number)
  if (menu) {
    Object.assign(menu, body)
  }
  res.setHeader('Content-Type', 'application/json')
  res.end(JSON.stringify(success(null)))
}
