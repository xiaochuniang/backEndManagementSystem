import { defineStore } from 'pinia'
import { loginApi, getUserInfoApi, logoutApi } from '@/api/auth'
import { setToken, getToken, removeToken } from '@/utils/storage'
import type { LoginParams, MenuItem } from '@/types/api'
import type { UserState } from '@/types/store'
import { useAppStore } from '@/store/modules/app'

export const useUserStore = defineStore('user', {
  state: (): UserState => ({
    token: getToken(),
    userInfo: null,
    permissions: [],
    menus: [],
  }),

  getters: {
    isLoggedIn: (state): boolean => !!state.token,
    username: (state): string => state.userInfo?.nickname || '',
    roles: (state): string[] => state.userInfo?.roles || [],
  },

  actions: {
    async login(params: LoginParams) {
      const data = await loginApi(params)
      this.token = data.token
      const appStore = useAppStore()
      appStore.clearVisitedViews()
      setToken(data.token)
    },

    async getUserInfo() {
      const data = await getUserInfoApi()
      this.userInfo = {
        id: data.id,
        username: data.username,
        nickname: data.nickname,
        avatar: data.avatar,
        roles: data.roles,
      }
      this.permissions = data.permissions
      this.menus = data.menus
    },

    async logout() {
      try {
        await logoutApi()
      } finally {
        this.resetState()
      }
    },

    resetState() {
      this.token = ''
      this.userInfo = null
      this.permissions = []
      this.menus = []
      const appStore = useAppStore()
      appStore.clearVisitedViews()
      removeToken()
    },

    setMenus(menus: MenuItem[]) {
      this.menus = menus
    },
  },
})
