import type { IncomingMessage, ServerResponse } from 'http'
import { parseRequestBody, success, parseQuery } from './utils'

interface MockRoleRecord {
  id: number
  name: string
  code: string
  description: string
  status: number
  createTime: string
  menuIds: number[]
}

const roleList: MockRoleRecord[] = [
  {
    id: 1,
    name: '超级管理员',
    code: 'admin',
    description: '拥有所有权限',
    status: 1,
    createTime: '2024-01-01 00:00:00',
    menuIds: [1, 2, 21, 22, 23],
  },
  {
    id: 2,
    name: '编辑员',
    code: 'editor',
    description: '仅查看权限',
    status: 1,
    createTime: '2024-01-02 00:00:00',
    menuIds: [1, 2, 21],
  },
  {
    id: 3,
    name: '访客',
    code: 'guest',
    description: '访客角色',
    status: 0,
    createTime: '2024-03-15 00:00:00',
    menuIds: [1],
  },
]

export function handleRoleMock(
  url: string,
  method: string,
  req: IncomingMessage,
  res: ServerResponse,
): boolean {
  const basePath = url.split('?')[0]

  if (basePath === '/api/role/list' && method === 'GET') {
    const query = parseQuery(url)
    const page = parseInt(query.page || '1', 10)
    const pageSize = parseInt(query.pageSize || '10', 10)
    const name = query.name || ''

    let filtered = roleList
    if (name) {
      filtered = roleList.filter((r) => r.name.includes(name))
    }

    const start = (page - 1) * pageSize
    const list = filtered.slice(start, start + pageSize)

    res.setHeader('Content-Type', 'application/json')
    res.end(JSON.stringify(success({ list, total: filtered.length, page, pageSize })))
    return true
  }

  if (basePath === '/api/role/all' && method === 'GET') {
    res.setHeader('Content-Type', 'application/json')
    res.end(JSON.stringify(success(roleList)))
    return true
  }

  if (basePath === '/api/role/create' && method === 'POST') {
    handleCreate(req, res)
    return true
  }

  if (basePath === '/api/role/update' && method === 'PUT') {
    handleUpdate(req, res)
    return true
  }

  if (basePath.startsWith('/api/role/delete/') && method === 'DELETE') {
    const id = parseInt(basePath.split('/').pop() || '0', 10)
    const idx = roleList.findIndex((r) => r.id === id)
    if (idx > -1) roleList.splice(idx, 1)
    res.setHeader('Content-Type', 'application/json')
    res.end(JSON.stringify(success(null)))
    return true
  }

  return false
}

async function handleCreate(req: IncomingMessage, res: ServerResponse) {
  const body = await parseRequestBody(req)
  const newRole: MockRoleRecord = {
    id: roleList.length + 100,
    name: (body.name as string) || '',
    code: (body.code as string) || '',
    description: (body.description as string) || '',
    status: (body.status as number) ?? 1,
    createTime: new Date().toISOString().slice(0, 19).replace('T', ' '),
    menuIds: (body.menuIds as number[]) || [],
  }
  roleList.unshift(newRole)
  res.setHeader('Content-Type', 'application/json')
  res.end(JSON.stringify(success(null)))
}

async function handleUpdate(req: IncomingMessage, res: ServerResponse) {
  const body = await parseRequestBody(req)
  const role = roleList.find((r) => r.id === (body.id as number))
  if (role) {
    Object.assign(role, body)
  }
  res.setHeader('Content-Type', 'application/json')
  res.end(JSON.stringify(success(null)))
}
