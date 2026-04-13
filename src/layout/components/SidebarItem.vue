<script setup lang="ts">
import type { MenuItem } from '@/types/api'
import * as Icons from '@element-plus/icons-vue'
import { computed } from 'vue'

interface Props {
  item: MenuItem
  basePath?: string
}

const props = withDefaults(defineProps<Props>(), {
  basePath: '',
})

const iconComponent = computed(() => {
  const name = props.item.icon as keyof typeof Icons
  return Icons[name] || null
})

const visibleChildren = computed(() => {
  return props.item.children?.filter((child) => !child.hidden && child.type === 1) || []
})
</script>

<template>
  <template v-if="!item.hidden">
    <el-sub-menu v-if="visibleChildren.length > 0" :index="item.path">
      <template #title>
        <el-icon v-if="iconComponent">
          <component :is="iconComponent" />
        </el-icon>
        <span>{{ item.title }}</span>
      </template>
      <sidebar-item
        v-for="child in visibleChildren"
        :key="child.path"
        :item="child"
        :base-path="item.path"
      />
    </el-sub-menu>
    <el-menu-item v-else :index="item.path">
      <el-icon v-if="iconComponent">
        <component :is="iconComponent" />
      </el-icon>
      <template #title>
        <span>{{ item.title }}</span>
      </template>
    </el-menu-item>
  </template>
</template>
