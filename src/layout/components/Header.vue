<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/store/modules/user'
import { useAppStore } from '@/store/modules/app'
import { Fold, Expand, SwitchButton, UserFilled } from '@element-plus/icons-vue'
import { ElMessageBox } from 'element-plus'

const router = useRouter()
const userStore = useUserStore()
const appStore = useAppStore()

const isCollapsed = computed(() => appStore.sidebarCollapsed)
const nickname = computed(() => userStore.username)

function toggleSidebar() {
  appStore.toggleSidebar()
}

async function handleLogout() {
  try {
    await ElMessageBox.confirm('确定要退出登录吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning',
    })
    await userStore.logout()
    router.push('/login')
  } catch {
    // User cancelled
  }
}
</script>

<template>
  <div class="header-container">
    <div class="left-section">
      <el-icon class="toggle-btn" :size="20" @click="toggleSidebar">
        <Fold v-if="!isCollapsed" />
        <Expand v-else />
      </el-icon>
    </div>
    <div class="right-section">
      <el-dropdown trigger="click">
        <span class="user-info">
          <el-icon :size="16"><UserFilled /></el-icon>
          <span class="username">{{ nickname }}</span>
        </span>
        <template #dropdown>
          <el-dropdown-menu>
            <el-dropdown-item @click="handleLogout">
              <el-icon><SwitchButton /></el-icon>
              退出登录
            </el-dropdown-item>
          </el-dropdown-menu>
        </template>
      </el-dropdown>
    </div>
  </div>
</template>

<style scoped lang="scss">
.left-section {
  display: flex;
  align-items: center;
}

.toggle-btn {
  cursor: pointer;
  color: #606266;

  &:hover {
    color: $primary-color;
  }
}

.right-section {
  display: flex;
  align-items: center;
}

.user-info {
  display: flex;
  align-items: center;
  cursor: pointer;
  color: #606266;
  gap: 6px;

  .username {
    font-size: 14px;
  }
}
</style>
