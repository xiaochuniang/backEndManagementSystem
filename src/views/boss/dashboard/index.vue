<script setup lang="ts">
import { computed } from 'vue'
import { ShoppingCart, User, Wallet, TrendCharts } from '@element-plus/icons-vue'

const stats = [
  { label: '今日营收(元)', value: 8620, icon: ShoppingCart, color: '#409eff', prefix: '￥' },
  { label: '今日订单数', value: 143, icon: TrendCharts, color: '#67c23a', prefix: '' },
  { label: '新增会员', value: 18, icon: User, color: '#e6a23c', prefix: '' },
  { label: '储值总额(元)', value: 38500, icon: Wallet, color: '#f56c6c', prefix: '￥' },
]

const trendData = [
  { day: '04/10', amount: 6200 },
  { day: '04/11', amount: 7400 },
  { day: '04/12', amount: 5800 },
  { day: '04/13', amount: 8100 },
  { day: '04/14', amount: 7200 },
  { day: '04/15', amount: 9300 },
  { day: '04/16', amount: 8620 },
]

const svgWidth = 620
const svgHeight = 180
const padLeft = 60
const padRight = 20
const padTop = 20
const padBottom = 40

const maxAmount = computed(() => Math.max(...trendData.map((d) => d.amount)))

const points = computed(() => {
  return trendData.map((d, i) => {
    const x = padLeft + (i * (svgWidth - padLeft - padRight)) / (trendData.length - 1)
    const y = padTop + ((1 - d.amount / maxAmount.value) * (svgHeight - padTop - padBottom))
    return { x, y, day: d.day, amount: d.amount }
  })
})

const polylinePoints = computed(() =>
  points.value.map((p) => `${p.x},${p.y}`).join(' ')
)

const areaPoints = computed(() => {
  const base = svgHeight - padBottom
  const first = points.value[0]
  const last = points.value[points.value.length - 1]
  return `${first.x},${base} ${polylinePoints.value} ${last.x},${base}`
})

const yAxisPositions = [0, 1, 2, 3].map(
  (i) => padTop + i * (svgHeight - padTop - padBottom) / 3
)
</script>

<template>
  <div class="page">
    <el-row :gutter="16" class="stat-row">
      <el-col v-for="s in stats" :key="s.label" :xs="24" :sm="12" :md="6">
        <el-card shadow="hover" class="stat-card">
          <div class="stat-inner">
            <div>
              <div class="stat-label">{{ s.label }}</div>
              <div class="stat-val" :style="{ color: s.color }">{{ s.prefix }}{{ s.value.toLocaleString() }}</div>
            </div>
            <el-icon :size="40" :color="s.color" style="opacity:0.6"><component :is="s.icon" /></el-icon>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <el-card shadow="hover" style="margin-top:16px">
      <template #header><span>近7天营收趋势</span></template>
      <svg :width="svgWidth" :height="svgHeight" style="display:block;margin:0 auto">
        <!-- Y axis lines -->
        <line v-for="(yPos, idx) in yAxisPositions" :key="idx"
          :x1="padLeft" :y1="yPos"
          :x2="svgWidth - padRight" :y2="yPos"
          stroke="#eee" stroke-width="1" />
        <!-- Area fill -->
        <polygon :points="areaPoints" fill="#409eff" fill-opacity="0.1" />
        <!-- Trend line -->
        <polyline :points="polylinePoints" fill="none" stroke="#409eff" stroke-width="2" stroke-linejoin="round" />
        <!-- Data points -->
        <circle v-for="p in points" :key="p.day" :cx="p.x" :cy="p.y" r="4" fill="#409eff" />
        <!-- X labels -->
        <text v-for="p in points" :key="'l'+p.day"
          :x="p.x" :y="svgHeight - 8"
          text-anchor="middle" font-size="11" fill="#909399">{{ p.day }}</text>
        <!-- Y label -->
        <text v-for="p in [points[0], points[points.length - 1]]" :key="'v'+p.day"
          :x="p.x" :y="p.y - 8"
          text-anchor="middle" font-size="10" fill="#606266">￥{{ p.amount }}</text>
      </svg>
    </el-card>
  </div>
</template>

<style scoped>
.stat-row { margin-bottom: 0; }
.stat-card { margin-bottom: 0; }
.stat-inner { display: flex; align-items: center; justify-content: space-between; }
.stat-label { font-size: 13px; color: #909399; margin-bottom: 8px; }
.stat-val { font-size: 26px; font-weight: 700; }
</style>
