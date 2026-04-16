<script setup lang="ts">
import { ref, computed } from 'vue'

interface StatCard {
  label: string
  value: string
  color: string
  icon: string
}

const TODAY = '2026-04-16'

const stats = ref<StatCard[]>([
  { label: '今日营收', value: '¥ 4,267', color: '#409eff', icon: '💰' },
  { label: '今日订单', value: '68 单', color: '#67c23a', icon: '📋' },
  { label: '今日会员储值', value: '¥ 1,200', color: '#e6a23c', icon: '💳' },
  { label: '当前当班时长', value: '3h 24m', color: '#909399', icon: '⏱' },
])

const recentOrders = ref([
  { orderNo: 'SO20260416001', amount: 64, status: '已完成', time: '09:23' },
  { orderNo: 'SO20260416002', amount: 45, status: '已完成', time: '10:05' },
  { orderNo: 'SO20260416003', amount: 22, status: '已退款', time: '10:44' },
  { orderNo: 'SO20260416004', amount: 36, status: '已完成', time: '11:30' },
  { orderNo: 'SO20260416005', amount: 28, status: '已完成', time: '12:10' },
])

const statusType: Record<string, 'success' | 'danger' | 'warning'> = {
  已完成: 'success',
  已退款: 'danger',
  待支付: 'warning',
}

// quick nav
const quickLinks = [
  { label: '收银台', path: '/staff/cashier', icon: '🛒', color: '#ecf5ff' },
  { label: '会员核销', path: '/staff/members', icon: '👤', color: '#f0f9eb' },
  { label: '订单记录', path: '/staff/orders', icon: '📄', color: '#fdf6ec' },
  { label: '交班对账', path: '/staff/shift', icon: '✅', color: '#fef0f0' },
]

import { useRouter } from 'vue-router'
const router = useRouter()
</script>

<template>
  <div class="page">
    <!-- greeting -->
    <el-alert
      title="今日目标：完成 100 单收银，会员储值 15 单。加油！"
      type="info"
      :closable="false"
      style="margin-bottom:16px"
    />

    <!-- stat cards -->
    <el-row :gutter="12" style="margin-bottom:16px">
      <el-col v-for="card in stats" :key="card.label" :xs="12" :sm="6">
        <el-card shadow="hover" class="stat-card">
          <div class="card-icon">{{ card.icon }}</div>
          <div class="card-num" :style="{ color: card.color }">{{ card.value }}</div>
          <div class="card-label">{{ card.label }}</div>
        </el-card>
      </el-col>
    </el-row>

    <!-- quick nav -->
    <el-card shadow="hover" style="margin-bottom:16px">
      <template #header>快捷入口</template>
      <div class="quick-links">
        <div
          v-for="link in quickLinks"
          :key="link.label"
          class="quick-item"
          :style="{ background: link.color }"
          @click="router.push(link.path)"
        >
          <div class="quick-icon">{{ link.icon }}</div>
          <div class="quick-label">{{ link.label }}</div>
        </div>
      </div>
    </el-card>

    <!-- recent orders -->
    <el-card shadow="hover">
      <template #header>最近收银记录</template>
      <el-table :data="recentOrders" size="small" border>
        <el-table-column prop="orderNo" label="订单号" />
        <el-table-column prop="time" label="时间" width="70" />
        <el-table-column prop="amount" label="金额" width="80">
          <template #default="{ row }">¥{{ row.amount }}</template>
        </el-table-column>
        <el-table-column prop="status" label="状态" width="80">
          <template #default="{ row }">
            <el-tag :type="statusType[row.status]" size="small">{{ row.status }}</el-tag>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.stat-card {
  text-align: center;
  padding: 4px 0;
  margin-bottom: 0;
  cursor: default;
}

.card-icon {
  font-size: 24px;
  margin-bottom: 4px;
}

.card-num {
  font-size: 20px;
  font-weight: 700;
  line-height: 1.2;
}

.card-label {
  font-size: 12px;
  color: #909399;
  margin-top: 2px;
}

.quick-links {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.quick-item {
  width: 100px;
  height: 80px;
  border-radius: 10px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: transform 0.15s;

  &:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }
}

.quick-icon {
  font-size: 26px;
  margin-bottom: 4px;
}

.quick-label {
  font-size: 13px;
  color: #303133;
  font-weight: 500;
}
</style>
