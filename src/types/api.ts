export interface ApiResponse<T = unknown> {
  code: number
  message: string
  data: T
}

export interface PageResult<T = unknown> {
  list: T[]
  total: number
  page: number
  pageSize: number
}

export interface PageParams {
  page: number
  pageSize: number
}

export interface LoginParams {
  username: string
  password: string
  role: 'boss' | 'staff' | 'customerOps'
}

export interface LoginResult {
  token: string
}

export interface UserInfo {
  id: number
  username: string
  nickname: string
  email: string
  phone: string
  avatar: string
  roles: string[]
  permissions: string[]
  menus: MenuItem[]
}

export interface UserRecord {
  id: number
  username: string
  nickname: string
  email: string
  phone: string
  status: number
  createTime: string
  roleIds: number[]
}

export interface UserFormData {
  id?: number
  username: string
  nickname: string
  password?: string
  email: string
  phone: string
  status: number
  roleIds: number[]
}

export interface RoleRecord {
  id: number
  name: string
  code: string
  description: string
  status: number
  createTime: string
  menuIds: number[]
}

export interface RoleFormData {
  id?: number
  name: string
  code: string
  description: string
  status: number
  menuIds: number[]
}

export interface MenuItem {
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
  type: number // 1=menu 2=button
  permission: string
  sort: number
  children?: MenuItem[]
}

export interface MenuFormData {
  id?: number
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
}
