import { createRouter, createWebHistory } from 'vue-router'
import type { RouteRecordRaw } from 'vue-router'
import { constantRoutes, catchAllRoute } from './routes'
import { setupRouterGuard } from './guard'
import type { MenuItem } from '@/types/api'

const viewModules = import.meta.glob('../views/**/*.vue')

const router = createRouter({
  history: createWebHistory(),
  routes: constantRoutes,
  scrollBehavior: () => ({ top: 0 }),
})

export function generateRoutes(menus: MenuItem[]): RouteRecordRaw[] {
  const Layout = () => import('@/layout/index.vue')
  const routes: RouteRecordRaw[] = []

  function processMenu(menu: MenuItem, isTopLevel: boolean): RouteRecordRaw | null {
    const route: RouteRecordRaw = {
      path: menu.path,
      name: menu.name,
      meta: {
        title: menu.title,
        icon: menu.icon,
        hidden: menu.hidden,
        keepAlive: menu.keepAlive,
        permission: menu.permission,
      },
      component: undefined,
      children: [],
      redirect: menu.redirect || undefined,
    }

    if (isTopLevel && menu.children && menu.children.length > 0) {
      route.component = Layout
      route.children = menu.children
        .filter((child) => child.type === 1)
        .map((child) => processMenu(child, false))
        .filter(Boolean) as RouteRecordRaw[]
    } else if (isTopLevel && menu.component !== 'Layout') {
      // Top-level menu without children, wrap in Layout
      const childRoute: RouteRecordRaw = {
        path: menu.path,
        name: menu.name,
        component: resolveComponent(menu.component),
        meta: {
          title: menu.title,
          icon: menu.icon,
          hidden: menu.hidden,
          keepAlive: menu.keepAlive,
          permission: menu.permission,
        },
      }
      return {
        path: menu.path,
        component: Layout,
        name: `${menu.name}Parent`,
        meta: { title: menu.title, icon: menu.icon },
        children: [childRoute],
        redirect: menu.path,
      }
    } else {
      route.component = resolveComponent(menu.component)
    }

    return route
  }

  menus.forEach((menu) => {
    if (menu.type !== 1) return
    const route = processMenu(menu, true)
    if (route) routes.push(route)
  })

  return routes
}

function resolveComponent(component: string): (() => Promise<unknown>) {
  const path = `../views/${component.replace(/^views\//, '')}.vue`
  if (viewModules[path]) {
    return viewModules[path] as () => Promise<unknown>
  }
  console.warn(`Component not found: ${component}, path: ${path}`)
  return () => import('@/views/error/404.vue')
}

export function resetRouter(): void {
  const newRouter = createRouter({
    history: createWebHistory(),
    routes: constantRoutes,
  })
  // Reset matcher
  ;(router as unknown as { matcher: unknown }).matcher = (newRouter as unknown as { matcher: unknown }).matcher
}

export function addDynamicRoutes(menus: MenuItem[]): RouteRecordRaw[] {
  const dynamicRoutes = generateRoutes(menus)
  dynamicRoutes.forEach((route) => {
    router.addRoute(route)
  })
  router.addRoute(catchAllRoute)
  return dynamicRoutes
}

setupRouterGuard(router)

export default router
