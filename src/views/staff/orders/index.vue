<script setup lang="ts">
import { ref, computed } from 'vue'
import { View } from '@element-plus/icons-vue'

interface OrderItem {
  name: string
  qty: number
  price: number
}
interface Order {
  id: string
  orderNo: string
  amount: number
  payMethod: string
  createTime: string
  status: '已完成' | '已退款' | '待支付'
  items: OrderItem[]
}

// mock personal orders (today + history)
const allOrders = ref<Order[]>([
  {
    id: '1', orderNo: 'SO20260416001', amount: 64, payMethod: '微信支付',
    createTime: '2026-04-16 09:23', status: '已完成',
    items: [{ name: '珍珠奶茶', qty: 2, price: 18 }, { name: '芝士蛋糕', qty: 1, price: 28 }],
  },
  {
    id: '2', orderNo: 'SO20260416002', amount: 45, payMethod: '支付宝',
    createTime: '2026-04-16 10:05', status: '已完成',
    items: [{ name: '下午茶套餐', qty: 1, price: 45 }],
  },
  {
    id: '3', orderNo: 'SO20260416003', amount: 22, payMethod: '现金',
    createTime: '2026-04-16 10:44', status: '已退款',
    items: [{ name: '美式咖啡', qty: 1, price: 22 }],
  },
  {
    id: '4', orderNo: 'SO20260416004', amount: 36, payMethod: '会员余额',
    createTime: '2026-04-16 11:30', status: '已完成',
    items: [{ name: '抹茶拿铁', qty: 1, price: 25 }, { name: '薯条', qty: 1, price: 12 }],
  },
  {
    id: '5', orderNo: 'SO20260415001', amount: 68, payMethod: '微信支付',
    createTime: '2026-04-15 14:20', status: '已完成',
    items: [{ name: '双人套餐', qty: 1, price: 68 }],
  },
  {
    id: '6', orderNo: 'SO20260415002', amount: 50, payMethod: '支付宝',
    createTime: '2026-04-15 16:00', status: '已完成',
    items: [{ name: '焦糖玛奇朵', qty: 1, price: 30 }, { name: '草莓蛋糕', qty: 1, price: 35 }],
  },
])

const TODAY = '2026-04-16'
const todayOrders = computed(() =>
  allOrders.value.filter((o) => o.createTime.startsWith(TODAY)),
)

const todayRevenue = computed(() =>
  todayOrders.value.filter((o) => o.status === '已完成').reduce((s, o) => s + o.amount, 0),
)
const todayCount = computed(() => todayOrders.value.filter((o) => o.status === '已完成').length)
const todayRefund = computed(() =>
  todayOrders.value.filter((o) => o.status === '已退款').reduce((s, o) => s + o.amount, 0),
)

// pay method breakdown for today
const payMethodStats = computed(() => {
  const map: Record<string, number> = {}
  todayOrders.value
    .filter((o) => o.status === '已完成')
    .forEach((o) => {
      map[o.payMethod] = (map[o.payMethod] || 0) + o.amount
    })
  return Object.entries(map).map(([method, amount]) => ({ method, amount }))
})

// detail drawer
const drawerVisible = ref(false)
const currentOrder = ref<Order | null>(null)

function showDetail(order: Order) {
  currentOrder.value = order
  drawerVisible.value = true
}

const statusTagType: Record<string, 'success' | 'danger' | 'warning'> = {
  已完成: 'success',
  已退款: 'danger',
  待支付: 'warning',
}
</script>

<template>
  <div class="page">
    <!-- today stats -->
    <el-row :gutter="12" style="margin-bottom:16px">
      <el-col :xs="24" :sm="8">
        <el-card shadow="hover" class="stat-card">
          <div class="stat-num" style="color:#409eff">¥{{ todayRevenue.toFixed(2) }}</div>
          <div class="stat-label">今日营收</div>
        </el-card>
      </el-col>
      <el-col :xs="24" :sm="8">
        <el-card shadow="hover" class="stat-card">
          <div class="stat-num" style="color:#67c23a">{{ todayCount }}</div>
          <div class="stat-label">今日完成订单</div>
        </el-card>
      </el-col>
      <el-col :xs="24" :sm="8">
        <el-card shadow="hover" class="stat-card">
          <div class="stat-num" style="color:#f56c6c">¥{{ todayRefund.toFixed(2) }}</div>
          <div class="stat-label">今日退款金额</div>
        </el-card>
      </el-col>
    </el-row>

    <!-- pay method breakdown -->
    <el-card shadow="hover" style="margin-bottom:16px">
      <template #header>今日支付方式统计</template>
      <div class="pay-stats">
        <div v-for="item in payMethodStats" :key="item.method" class="pay-stat-item">
          <span class="pay-method">{{ item.method }}</span>
          <span class="pay-amount">¥{{ item.amount.toFixed(2) }}</span>
        </div>
        <div v-if="payMethodStats.length === 0" style="color:#909399;font-size:13px">暂无数据</div>
      </div>
    </el-card>

    <!-- order table -->
    <el-card shadow="hover">
      <template #header>个人收银记录（所有订单）</template>
      <el-table :data="allOrders" border stripe>
        <el-table-column prop="orderNo" label="订单号" min-width="150" />
        <el-table-column prop="createTime" label="时间" min-width="140" />
        <el-table-column prop="payMethod" label="支付方式" width="100" />
        <el-table-column prop="amount" label="金额" width="90">
          <template #default="{ row }">¥{{ row.amount }}</template>
        </el-table-column>
        <el-table-column prop="status" label="状态" width="90">
          <template #default="{ row }">
            <el-tag :type="statusTagType[row.status]" size="small">{{ row.status }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="80" fixed="right">
          <template #default="{ row }">
            <el-button link type="primary" :icon="View" @click="showDetail(row)">详情</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <!-- order detail drawer -->
    <el-drawer v-model="drawerVisible" title="订单详情" direction="rtl" size="380px">
      <template v-if="currentOrder">
        <el-descriptions :column="1" border size="small" style="margin-bottom:16px">
          <el-descriptions-item label="订单号">{{ currentOrder.orderNo }}</el-descriptions-item>
          <el-descriptions-item label="时间">{{ currentOrder.createTime }}</el-descriptions-item>
          <el-descriptions-item label="支付方式">{{ currentOrder.payMethod }}</el-descriptions-item>
          <el-descriptions-item label="状态">
            <el-tag :type="statusTagType[currentOrder.status]" size="small">{{ currentOrder.status }}</el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="合计">
            <span style="color:#f56c6c;font-weight:700">¥{{ currentOrder.amount }}</span>
          </el-descriptions-item>
        </el-descriptions>
        <div style="font-weight:600;margin-bottom:8px">商品明细</div>
        <el-table :data="currentOrder.items" size="small" border>
          <el-table-column prop="name" label="商品" />
          <el-table-column prop="qty" label="数量" width="60" />
          <el-table-column prop="price" label="单价" width="70">
            <template #default="{ row }">¥{{ row.price }}</template>
          </el-table-column>
          <el-table-column label="小计" width="70">
            <template #default="{ row }">¥{{ row.price * row.qty }}</template>
          </el-table-column>
        </el-table>
      </template>
    </el-drawer>
  </div>
</template>

<style scoped lang="scss">
.stat-card {
  text-align: center;
  padding: 8px 0;
  margin-bottom: 0;
}

.stat-num {
  font-size: 26px;
  font-weight: 700;
  line-height: 1.2;
}

.stat-label {
  font-size: 13px;
  color: #909399;
  margin-top: 4px;
}

.pay-stats {
  display: flex;
  gap: 24px;
  flex-wrap: wrap;
}

.pay-stat-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  min-width: 90px;
}

.pay-method {
  font-size: 12px;
  color: #909399;
}

.pay-amount {
  font-size: 18px;
  font-weight: 700;
  color: #303133;
}
</style>
