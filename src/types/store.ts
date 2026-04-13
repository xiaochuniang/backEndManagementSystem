import type { MenuItem } from './api'

export interface UserState {
  token: string
  userInfo: {
    id: number
    username: string
    nickname: string
    avatar: string
    roles: string[]
  } | null
  permissions: string[]
  menus: MenuItem[]
}

export interface AppState {
  sidebarCollapsed: boolean
  theme: string
  size: string
  visitedViews: TagView[]
}

export interface TagView {
  path: string
  name: string
  title: string
  icon?: string
  affix?: boolean
}
