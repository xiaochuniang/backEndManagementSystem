import { defineStore } from 'pinia'
import type { AppState, TagView } from '@/types/store'

export const useAppStore = defineStore('app', {
  state: (): AppState => ({
    sidebarCollapsed: false,
    theme: 'light',
    size: 'default',
    visitedViews: [],
  }),

  actions: {
    toggleSidebar() {
      this.sidebarCollapsed = !this.sidebarCollapsed
    },

    setTheme(theme: string) {
      this.theme = theme
    },

    setSize(size: string) {
      this.size = size
    },

    addVisitedView(view: TagView) {
      if (this.visitedViews.some((v) => v.path === view.path)) return
      this.visitedViews.push(view)
    },

    removeVisitedView(path: string) {
      const index = this.visitedViews.findIndex((v) => v.path === path)
      if (index > -1) {
        this.visitedViews.splice(index, 1)
      }
    },

    clearVisitedViews() {
      this.visitedViews = this.visitedViews.filter((v) => v.affix)
    },
  },
})
