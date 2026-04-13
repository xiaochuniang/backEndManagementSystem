<script setup lang="ts">
import { computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAppStore } from '@/store/modules/app'
import { Close } from '@element-plus/icons-vue'

const router = useRouter()
const route = useRoute()
const appStore = useAppStore()

const visitedViews = computed(() => appStore.visitedViews)

function isActive(path: string): boolean {
  return route.path === path
}

function handleClick(path: string) {
  router.push(path)
}

function handleClose(path: string) {
  appStore.removeVisitedView(path)
  if (route.path === path) {
    const lastView = visitedViews.value[visitedViews.value.length - 1]
    if (lastView) {
      router.push(lastView.path)
    } else {
      router.push('/dashboard')
    }
  }
}
</script>

<template>
  <div class="tags-view-container">
    <div
      v-for="tag in visitedViews"
      :key="tag.path"
      class="tag-item"
      :class="{ active: isActive(tag.path) }"
      @click="handleClick(tag.path)"
    >
      {{ tag.title }}
      <span v-if="!tag.affix" class="close-btn" @click.stop="handleClose(tag.path)">
        <el-icon :size="12"><Close /></el-icon>
      </span>
    </div>
  </div>
</template>
