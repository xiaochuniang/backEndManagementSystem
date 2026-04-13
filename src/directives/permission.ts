import type { App, Directive, DirectiveBinding } from 'vue'
import { useUserStore } from '@/store/modules/user'

function checkPermission(el: HTMLElement, binding: DirectiveBinding<string | string[]>): void {
  const { value } = binding
  const userStore = useUserStore()
  const permissions = userStore.permissions

  if (value) {
    const requiredPermissions = typeof value === 'string' ? [value] : value
    const hasPermission = requiredPermissions.some((p) => permissions.includes(p))

    if (!hasPermission && el.parentNode) {
      el.parentNode.removeChild(el)
    }
  }
}

const permissionDirective: Directive<HTMLElement, string | string[]> = {
  mounted(el, binding) {
    checkPermission(el, binding)
  },
  updated(el, binding) {
    checkPermission(el, binding)
  },
}

export function setupPermissionDirective(app: App): void {
  app.directive('permission', permissionDirective)
}
