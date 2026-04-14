<script setup lang="ts">
import { ref } from 'vue'
import { useUserStore } from '@/store/modules/user'
import { User, Goods, ShoppingCart, DataLine } from '@element-plus/icons-vue'

const userStore = useUserStore()

interface StatCard {
  title: string
  value: string
  icon: typeof User
  color: string
}

const stats = ref<StatCard[]>([
  { title: '用户总数', value: '2,580', icon: User, color: '#409eff' },
  { title: '订单总数', value: '12,680', icon: Goods, color: '#67c23a' },
  { title: '成交额', value: '￥580,230', icon: ShoppingCart, color: '#e6a23c' },
  { title: '访问量', value: '98,251', icon: DataLine, color: '#f56c6c' },
])
</script>

<template>
  <div class="dashboard">
    <el-row :gutter="16" class="stat-cards">
      <el-col v-for="stat in stats" :key="stat.title" :xs="24" :sm="12" :md="6">
        <el-card shadow="hover" class="stat-card">
          <div class="stat-content">
            <div class="stat-info">
              <div class="stat-title">{{ stat.title }}</div>
              <div class="stat-value">{{ stat.value }}</div>
            </div>
            <el-icon :size="48" :color="stat.color" class="stat-icon">
              <component :is="stat.icon" />
            </el-icon>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <el-row :gutter="16" style="margin-top: 16px">
      <el-col :span="24">
        <el-card shadow="hover">
          <template #header>
            <span>欢迎</span>
          </template>
          <p>
            您好，<strong>{{ userStore.username }}</strong
            >！欢迎使用 Vue3 Admin 后台管理系统。
          </p>
          <p style="margin-top: 12px; color: #909399">当前角色：{{ userStore.roles.join(', ') }}</p>
        </el-card>
      </el-col>
    </el-row>

    <el-row :gutter="16" style="margin-top: 16px">
      <el-col :xs="24" :md="12">
        <el-card shadow="hover">
          <template #header>
            <span>技术栈</span>
          </template>
          <el-tag
            v-for="tag in [
              'Vue 3.4',
              'TypeScript',
              'Vite',
              'Element Plus',
              'Pinia',
              'Vue Router 4',
            ]"
            :key="tag"
            style="margin: 4px"
          >
            {{ tag }}
          </el-tag>
        </el-card>
      </el-col>
      <el-col :xs="24" :md="12">
        <el-card shadow="hover">
          <template #header>
            <span>系统信息</span>
          </template>
          <el-descriptions :column="1" border>
            <el-descriptions-item label="系统名称">Vue3 Admin System</el-descriptions-item>
            <el-descriptions-item label="版本">1.0.0</el-descriptions-item>
            <el-descriptions-item label="框架">Vue 3.4 + TypeScript</el-descriptions-item>
            <el-descriptions-item label="构建工具">Vite 5</el-descriptions-item>
          </el-descriptions>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<style scoped lang="scss">
.stat-cards {
  .stat-card {
    .stat-content {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .stat-title {
      font-size: 14px;
      color: #909399;
    }

    .stat-value {
      font-size: 24px;
      font-weight: 700;
      color: #303133;
      margin-top: 8px;
    }

    .stat-icon {
      opacity: 0.8;
    }
  }
}
</style>
