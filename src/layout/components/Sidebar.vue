<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/store/modules/user'
import { useAppStore } from '@/store/modules/app'
import SidebarItem from './SidebarItem.vue'

const router = useRouter()
const userStore = useUserStore()
const appStore = useAppStore()

const menus = computed(() => userStore.menus)
const isCollapsed = computed(() => appStore.sidebarCollapsed)

function handleSelect(index: string) {
  router.push(index)
}
</script>

<template>
  <div class="sidebar-container" :class="{ 'is-collapsed': isCollapsed }">
    <div class="logo-container">
      <span v-if="!isCollapsed">Vue3 Admin</span>
      <span v-else>VA</span>
    </div>
    <el-scrollbar>
      <el-menu
        :default-active="$route.path"
        :collapse="isCollapsed"
        :collapse-transition="false"
        background-color="#304156"
        text-color="#bfcbd9"
        active-text-color="#409eff"
        :unique-opened="true"
        @select="handleSelect"
      >
        <sidebar-item v-for="menu in menus" :key="menu.path" :item="menu" />
      </el-menu>
    </el-scrollbar>
  </div>
</template>
