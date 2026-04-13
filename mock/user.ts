import type { IncomingMessage, ServerResponse } from 'http'
import { parseRequestBody, success, parseQuery } from './utils'

interface MockUserRecord {
  id: number
  username: string
  nickname: string
  email: string
  phone: string
  status: number
  createTime: string
  roleIds: number[]
}

const userList: MockUserRecord[] = Array.from({ length: 56 }, (_, i) => ({
  id: i + 1,
  username: `user${i + 1}`,
  nickname: `用户${i + 1}`,
  email: `user${i + 1}@example.com`,
  phone: `138${String(i + 1).padStart(8, '0')}`,
  status: i % 5 === 0 ? 0 : 1,
  createTime: `2024-0${(i % 9) + 1}-${String((i % 28) + 1).padStart(2, '0')} 10:00:00`,
  roleIds: i === 0 ? [1] : [2],
}))

export function handleUserMock(
  url: string,
  method: string,
  req: IncomingMessage,
  res: ServerResponse,
): boolean {
  const basePath = url.split('?')[0]

  if (basePath === '/api/user/list' && method === 'GET') {
    const query = parseQuery(url)
    const page = parseInt(query.page || '1', 10)
    const pageSize = parseInt(query.pageSize || '10', 10)
    const username = query.username || ''

    let filtered = userList
    if (username) {
      filtered = userList.filter((u) => u.username.includes(username))
    }

    const start = (page - 1) * pageSize
    const list = filtered.slice(start, start + pageSize)

    res.setHeader('Content-Type', 'application/json')
    res.end(JSON.stringify(success({ list, total: filtered.length, page, pageSize })))
    return true
  }

  if (basePath === '/api/user/create' && method === 'POST') {
    handleCreate(req, res)
    return true
  }

  if (basePath === '/api/user/update' && method === 'PUT') {
    handleUpdate(req, res)
    return true
  }

  if (basePath.startsWith('/api/user/delete/') && method === 'DELETE') {
    const id = parseInt(basePath.split('/').pop() || '0', 10)
    const idx = userList.findIndex((u) => u.id === id)
    if (idx > -1) userList.splice(idx, 1)
    res.setHeader('Content-Type', 'application/json')
    res.end(JSON.stringify(success(null)))
    return true
  }

  return false
}

async function handleCreate(req: IncomingMessage, res: ServerResponse) {
  const body = await parseRequestBody(req)
  const newUser: MockUserRecord = {
    id: userList.length + 100,
    username: (body.username as string) || '',
    nickname: (body.nickname as string) || '',
    email: (body.email as string) || '',
    phone: (body.phone as string) || '',
    status: (body.status as number) ?? 1,
    createTime: new Date().toISOString().slice(0, 19).replace('T', ' '),
    roleIds: (body.roleIds as number[]) || [],
  }
  userList.unshift(newUser)
  res.setHeader('Content-Type', 'application/json')
  res.end(JSON.stringify(success(null)))
}

async function handleUpdate(req: IncomingMessage, res: ServerResponse) {
  const body = await parseRequestBody(req)
  const user = userList.find((u) => u.id === (body.id as number))
  if (user) {
    Object.assign(user, body)
  }
  res.setHeader('Content-Type', 'application/json')
  res.end(JSON.stringify(success(null)))
}
