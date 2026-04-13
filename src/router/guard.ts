import type { Router } from 'vue-router'
import NProgress from 'nprogress'
import 'nprogress/nprogress.css'
import { useUserStore } from '@/store/modules/user'
import { useAppStore } from '@/store/modules/app'
import { addDynamicRoutes, resetRouter } from './index'

NProgress.configure({ showSpinner: false })

const whiteList = ['/login', '/404']

let dynamicRoutesAdded = false

export function setupRouterGuard(router: Router): void {
  router.beforeEach(async (to, _from, next) => {
    NProgress.start()

    const userStore = useUserStore()

    if (userStore.token) {
      if (to.path === '/login') {
        next({ path: '/dashboard' })
        NProgress.done()
        return
      }

      if (!userStore.userInfo) {
        try {
          await userStore.getUserInfo()
          resetRouter()
          addDynamicRoutes(userStore.menus)
          dynamicRoutesAdded = true
          next({ ...to, replace: true })
        } catch {
          userStore.resetState()
          next({ path: '/login', query: { redirect: to.fullPath } })
          NProgress.done()
        }
        return
      }

      next()
    } else {
      if (whiteList.includes(to.path)) {
        next()
      } else {
        next({ path: '/login', query: { redirect: to.fullPath } })
        NProgress.done()
      }
    }
  })

  router.afterEach((to) => {
    NProgress.done()

    const appStore = useAppStore()
    if (to.meta.title && to.name) {
      document.title = `${to.meta.title} - Vue3 Admin`
      appStore.addVisitedView({
        path: to.path,
        name: to.name as string,
        title: (to.meta.title as string) || '',
        icon: to.meta.icon as string,
        affix: to.path === '/dashboard',
      })
    }
  })
}

export function isDynamicRoutesAdded(): boolean {
  return dynamicRoutesAdded
}

export function setDynamicRoutesAdded(value: boolean): void {
  dynamicRoutesAdded = value
}
