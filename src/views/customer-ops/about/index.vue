<script setup lang="ts">
const shopInfo = {
  name: '城南茶饮·旗舰店',
  logo: '🍵',
  slogan: '好茶好心情，每一杯都用心',
  address: '城南区商业步行街 88 号 1F',
  phone: '0755-88888888',
  serviceHours: '09:00 - 22:00（全年无休）',
  wechat: 'chengnan-tea',
  intro: '城南茶饮创立于 2020 年，专注于手工制作精品茶饮和甜品。我们坚持选用优质原材料，每一杯都经过严格品控，力求给顾客带来最好的口感体验。目前已在城南区开设 3 家门店，欢迎您的到来！',
}

const branches = [
  { name: '城南旗舰店', address: '城南区商业步行街 88 号 1F', phone: '0755-88888888' },
  { name: '城南东区店', address: '城南区东环路 200 号购物中心 B1', phone: '0755-77777777' },
  { name: '城南大学城店', address: '大学城主干道 55 号', phone: '0755-66666666' },
]

function callPhone(phone: string) {
  window.location.href = `tel:${phone}`
}
</script>

<template>
  <div class="page miniapp-about">
    <!-- shop intro -->
    <el-card shadow="hover" class="brand-card">
      <div class="brand-header">
        <div class="brand-logo">{{ shopInfo.logo }}</div>
        <div class="brand-meta">
          <div class="brand-name">{{ shopInfo.name }}</div>
          <div class="brand-slogan">{{ shopInfo.slogan }}</div>
        </div>
      </div>
      <p class="brand-intro">{{ shopInfo.intro }}</p>
    </el-card>

    <!-- contact info -->
    <el-card shadow="hover" style="margin-top:12px">
      <template #header>联系我们</template>
      <el-descriptions :column="1" border size="small">
        <el-descriptions-item label="门店地址">
          <span>{{ shopInfo.address }}</span>
        </el-descriptions-item>
        <el-descriptions-item label="联系电话">
          <el-button link type="primary" @click="callPhone(shopInfo.phone)">
            📞 {{ shopInfo.phone }}
          </el-button>
        </el-descriptions-item>
        <el-descriptions-item label="营业时间">{{ shopInfo.serviceHours }}</el-descriptions-item>
        <el-descriptions-item label="官方微信">{{ shopInfo.wechat }}</el-descriptions-item>
      </el-descriptions>
    </el-card>

    <!-- all branches -->
    <el-card shadow="hover" style="margin-top:12px">
      <template #header>门店列表</template>
      <div v-for="(branch, idx) in branches" :key="idx" class="branch-item">
        <div class="branch-icon">📍</div>
        <div class="branch-info">
          <div class="branch-name">{{ branch.name }}</div>
          <div class="branch-addr">{{ branch.address }}</div>
        </div>
        <el-button link type="primary" size="small" @click="callPhone(branch.phone)">
          {{ branch.phone }}
        </el-button>
      </div>
    </el-card>

    <!-- wechat mini-program QR placeholder -->
    <el-card shadow="hover" style="margin-top:12px;text-align:center">
      <template #header>微信小程序</template>
      <div class="qr-placeholder">
        <div class="qr-icon">📱</div>
        <div style="color:#909399;font-size:13px;margin-top:8px">扫码进入小程序</div>
        <div style="color:#c0c4cc;font-size:12px;margin-top:4px">（小程序二维码占位）</div>
      </div>
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.miniapp-about {
  max-width: 480px;
  margin: 0 auto;
}

.brand-card {
  background: linear-gradient(135deg, #f0f9eb 0%, #ecf5ff 100%);
}

.brand-header {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 12px;
}

.brand-logo {
  font-size: 48px;
  line-height: 1;
}

.brand-name {
  font-size: 18px;
  font-weight: 700;
  color: #303133;
}

.brand-slogan {
  font-size: 13px;
  color: #909399;
  margin-top: 2px;
}

.brand-intro {
  font-size: 13px;
  color: #606266;
  line-height: 1.8;
  margin: 0;
}

.branch-item {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 10px 0;
  border-bottom: 1px solid #f5f5f5;

  &:last-child {
    border-bottom: none;
  }
}

.branch-icon {
  font-size: 20px;
  flex-shrink: 0;
  padding-top: 2px;
}

.branch-info {
  flex: 1;
}

.branch-name {
  font-size: 14px;
  font-weight: 600;
  color: #303133;
  margin-bottom: 2px;
}

.branch-addr {
  font-size: 12px;
  color: #909399;
}

.qr-placeholder {
  padding: 20px 0;
}

.qr-icon {
  font-size: 60px;
}
</style>
