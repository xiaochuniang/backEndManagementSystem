import { useUserStore } from '@/store/modules/user'

export function usePermission() {
  const userStore = useUserStore()

  function hasPermission(permission: string | string[]): boolean {
    const permissions = userStore.permissions
    if (typeof permission === 'string') {
      return permissions.includes(permission)
    }
    return permission.some((p) => permissions.includes(p))
  }

  return { hasPermission }
}
