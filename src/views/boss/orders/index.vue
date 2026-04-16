<script setup lang="ts">
import { ref, reactive, computed } from 'vue'
import { Search, View } from '@element-plus/icons-vue'

interface OrderItem {
  name: string
  qty: number
  price: number
}
interface Order {
  id: string
  orderNo: string
  amount: number
  payMethod: '微信支付' | '支付宝' | '现金' | '会员余额'
  createTime: string
  staffName: string
  status: '已完成' | '已退款' | '待支付'
  items: OrderItem[]
}

const allOrders = ref<Order[]>([
  {
    id: '1',
    orderNo: 'SO20260416001',
    amount: 64,
    payMethod: '微信支付',
    createTime: '2026-04-16 09:23',
    staffName: '张员工',
    status: '已完成',
    items: [
      { name: '珍珠奶茶', qty: 2, price: 18 },
      { name: '芝士蛋糕', qty: 1, price: 28 },
    ],
  },
  {
    id: '2',
    orderNo: 'SO20260416002',
    amount: 45,
    payMethod: '支付宝',
    createTime: '2026-04-16 10:05',
    staffName: '李员工',
    status: '已完成',
    items: [{ name: '下午茶套餐', qty: 1, price: 45 }],
  },
  {
    id: '3',
    orderNo: 'SO20260416003',
    amount: 22,
    payMethod: '现金',
    createTime: '2026-04-16 10:44',
    staffName: '张员工',
    status: '已退款',
    items: [{ name: '美式咖啡', qty: 1, price: 22 }],
  },
  {
    id: '4',
    orderNo: 'SO20260416004',
    amount: 36,
    payMethod: '会员余额',
    createTime: '2026-04-16 11:30',
    staffName: '王员工',
    status: '已完成',
    items: [{ name: '珍珠奶茶', qty: 2, price: 18 }],
  },
])

const filter = reactive({ dateRange: [] as string[], payMethod: '', status: '' })
const filterPayMethod = ['微信支付', '支付宝', '现金', '会员余额']
const filterStatus = ['已完成', '已退款', '待支付']

const filteredOrders = computed(() => {
  return allOrders.value.filter((o) => {
    if (filter.payMethod && o.payMethod !== filter.payMethod) return false
    if (filter.status && o.status !== filter.status) return false
    return true
  })
})

const drawerVisible = ref(false)
const currentOrder = ref<Order | null>(null)

function viewDetail(row: Order) {
  currentOrder.value = row
  drawerVisible.value = true
}

function statusType(status: string) {
  if (status === '已完成') return 'success'
  if (status === '已退款') return 'danger'
  return 'warning'
}

function resetFilter() {
  filter.dateRange = []
  filter.payMethod = ''
  filter.status = ''
}
</script>

<template>
  <div class="page">
    <el-card shadow="never" style="margin-bottom: 16px">
      <el-form inline>
        <el-form-item label="日期范围">
          <el-date-picker
            v-model="filter.dateRange"
            type="daterange"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
            style="width: 240px"
          />
        </el-form-item>
        <el-form-item label="支付方式">
          <el-select v-model="filter.payMethod" placeholder="全部" clearable style="width: 120px">
            <el-option v-for="m in filterPayMethod" :key="m" :label="m" :value="m" />
          </el-select>
        </el-form-item>
        <el-form-item label="订单状态">
          <el-select v-model="filter.status" placeholder="全部" clearable style="width: 120px">
            <el-option v-for="s in filterStatus" :key="s" :label="s" :value="s" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" :icon="Search">查询</el-button>
          <el-button @click="resetFilter">重置</el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <el-table :data="filteredOrders" border stripe>
      <el-table-column label="订单号" prop="orderNo" min-width="150" />
      <el-table-column label="消费金额" width="110">
        <template #default="{ row }">￥{{ row.amount.toFixed(2) }}</template>
      </el-table-column>
      <el-table-column label="支付方式" prop="payMethod" width="100" />
      <el-table-column label="消费时间" prop="createTime" min-width="160" />
      <el-table-column label="员工" prop="staffName" width="90" />
      <el-table-column label="状态" width="90">
        <template #default="{ row }">
          <el-tag :type="statusType(row.status)">{{ row.status }}</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="操作" width="90" fixed="right">
        <template #default="{ row }">
          <el-button size="small" :icon="View" @click="viewDetail(row)">详情</el-button>
        </template>
      </el-table-column>
    </el-table>

    <el-drawer v-model="drawerVisible" title="订单详情" size="480px" direction="rtl">
      <template v-if="currentOrder">
        <el-descriptions :column="2" border style="margin-bottom: 16px">
          <el-descriptions-item label="订单号">{{ currentOrder.orderNo }}</el-descriptions-item>
          <el-descriptions-item label="状态">
            <el-tag :type="statusType(currentOrder.status)">{{ currentOrder.status }}</el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="消费时间">{{
            currentOrder.createTime
          }}</el-descriptions-item>
          <el-descriptions-item label="支付方式">{{ currentOrder.payMethod }}</el-descriptions-item>
          <el-descriptions-item label="员工">{{ currentOrder.staffName }}</el-descriptions-item>
          <el-descriptions-item label="总金额"
            >￥{{ currentOrder.amount.toFixed(2) }}</el-descriptions-item
          >
        </el-descriptions>
        <div style="font-weight: 600; margin-bottom: 8px">订单商品</div>
        <el-table :data="currentOrder.items" border size="small">
          <el-table-column label="商品" prop="name" />
          <el-table-column label="单价" width="80">
            <template #default="{ row }">￥{{ row.price }}</template>
          </el-table-column>
          <el-table-column label="数量" prop="qty" width="60" />
          <el-table-column label="小计" width="80">
            <template #default="{ row }">￥{{ (row.price * row.qty).toFixed(2) }}</template>
          </el-table-column>
        </el-table>
      </template>
    </el-drawer>
  </div>
</template>
