<script setup lang="ts">
import { useRouter } from 'vue-router'

const router = useRouter()

const shopInfo = {
  name: '城南茶饮·旗舰店',
  logo: '🍵',
  slogan: '好茶好心情，每一杯都用心',
  address: '城南区商业步行街 88 号',
  phone: '0755-88888888',
}

const quickEntries = [
  { label: '在线买单', icon: '🛒', path: '/customer-ops/orders', color: '#ecf5ff', border: '#409eff' },
  { label: '会员中心', icon: '👤', path: '/customer-ops/members', color: '#f0f9eb', border: '#67c23a' },
  { label: '领优惠券', icon: '🎫', path: '/customer-ops/coupons', color: '#fdf6ec', border: '#e6a23c' },
  { label: '关于我们', icon: 'ℹ️', path: '/customer-ops/about', color: '#fef0f0', border: '#f56c6c' },
]

const notices = [
  '🎉 新品上市：焦糖布丁奶茶，限时特惠 ¥15',
  '💳 储值 300 元送 50 元，活动截止 4/30',
  '🎁 消费满 5 次赠优惠券一张',
]

const hotProducts = [
  { name: '珍珠奶茶', price: 18, emoji: '🧋', tag: '热销' },
  { name: '抹茶拿铁', price: 25, emoji: '🍵', tag: '新品' },
  { name: '焦糖布丁奶茶', price: 15, emoji: '🍮', tag: '特惠' },
  { name: '双人套餐', price: 68, emoji: '🎁', tag: '推荐' },
]
</script>

<template>
  <div class="page miniapp-home">
    <!-- shop banner -->
    <el-card shadow="hover" class="shop-banner">
      <div class="shop-header">
        <div class="shop-logo">{{ shopInfo.logo }}</div>
        <div class="shop-meta">
          <div class="shop-name">{{ shopInfo.name }}</div>
          <div class="shop-slogan">{{ shopInfo.slogan }}</div>
        </div>
      </div>
      <!-- main buy button -->
      <el-button
        type="primary"
        size="large"
        class="buy-btn"
        @click="router.push('/customer-ops/orders')"
      >
        🛒 立即买单
      </el-button>
    </el-card>

    <!-- quick entries -->
    <el-card shadow="hover" style="margin-top:12px">
      <template #header>快捷入口</template>
      <div class="entry-grid">
        <div
          v-for="entry in quickEntries"
          :key="entry.label"
          class="entry-item"
          :style="{ background: entry.color, borderColor: entry.border }"
          @click="router.push(entry.path)"
        >
          <div class="entry-icon">{{ entry.icon }}</div>
          <div class="entry-label">{{ entry.label }}</div>
        </div>
      </div>
    </el-card>

    <!-- notices -->
    <el-card shadow="hover" style="margin-top:12px">
      <template #header>最新公告</template>
      <div v-for="(n, i) in notices" :key="i" class="notice-item">{{ n }}</div>
    </el-card>

    <!-- hot products -->
    <el-card shadow="hover" style="margin-top:12px">
      <template #header>热门商品</template>
      <div class="hot-grid">
        <div
          v-for="p in hotProducts"
          :key="p.name"
          class="hot-item"
          @click="router.push('/customer-ops/orders')"
        >
          <div class="hot-emoji">{{ p.emoji }}</div>
          <div class="hot-name">{{ p.name }}</div>
          <div class="hot-footer">
            <span class="hot-price">¥{{ p.price }}</span>
            <el-tag size="small" type="warning">{{ p.tag }}</el-tag>
          </div>
        </div>
      </div>
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.miniapp-home {
  max-width: 480px;
  margin: 0 auto;
}

.shop-banner {
  text-align: center;
}

.shop-header {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 16px;
}

.shop-logo {
  font-size: 48px;
  line-height: 1;
}

.shop-meta {
  text-align: left;
}

.shop-name {
  font-size: 18px;
  font-weight: 700;
  color: #303133;
}

.shop-slogan {
  font-size: 13px;
  color: #909399;
  margin-top: 2px;
}

.buy-btn {
  width: 100%;
  height: 48px;
  font-size: 16px;
  font-weight: 600;
  border-radius: 24px;
}

.entry-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 10px;
}

.entry-item {
  border: 1px solid;
  border-radius: 10px;
  padding: 14px 8px;
  text-align: center;
  cursor: pointer;
  transition: transform 0.15s;

  &:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }
}

.entry-icon {
  font-size: 26px;
  margin-bottom: 6px;
}

.entry-label {
  font-size: 12px;
  color: #303133;
  font-weight: 500;
}

.notice-item {
  padding: 8px 0;
  border-bottom: 1px solid #f5f5f5;
  font-size: 13px;
  color: #606266;

  &:last-child {
    border-bottom: none;
  }
}

.hot-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
}

.hot-item {
  border: 1px solid #ebeef5;
  border-radius: 8px;
  padding: 12px;
  cursor: pointer;
  transition: all 0.15s;

  &:hover {
    border-color: #409eff;
    box-shadow: 0 2px 8px rgba(64, 158, 255, 0.15);
  }
}

.hot-emoji {
  font-size: 32px;
  text-align: center;
  margin-bottom: 6px;
}

.hot-name {
  font-size: 13px;
  font-weight: 600;
  color: #303133;
  margin-bottom: 6px;
  text-align: center;
}

.hot-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.hot-price {
  font-size: 15px;
  color: #f56c6c;
  font-weight: 700;
}
</style>
