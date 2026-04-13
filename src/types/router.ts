import 'vue-router'

declare module 'vue-router' {
  interface RouteMeta {
    title?: string
    icon?: string
    hidden?: boolean
    keepAlive?: boolean
    permission?: string
    activeMenu?: string
  }
}

export interface AppRouteRecord {
  path: string
  name: string
  component: string
  redirect?: string
  meta: {
    title: string
    icon?: string
    hidden?: boolean
    keepAlive?: boolean
    permission?: string
  }
  children?: AppRouteRecord[]
}
