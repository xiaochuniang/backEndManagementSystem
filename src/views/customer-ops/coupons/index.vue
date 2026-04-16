<script setup lang="ts">
import { ref } from 'vue'
import { ElMessage } from 'element-plus'

interface Coupon {
  id: number
  name: string
  desc: string
  type: 'discount' | 'reduce' | 'free'  // 折扣/满减/免费
  value: number        // 折扣率 or 减免金额
  minAmount: number    // 最低消费
  expireDate: string
  status?: 'unused' | 'used' | 'expired'
  received?: boolean
}

// my coupons
const myCoupons = ref<Coupon[]>([
  {
    id: 1, name: '新人优惠券', desc: '满30元可用', type: 'reduce', value: 10,
    minAmount: 30, expireDate: '2026-05-31', status: 'unused',
  },
  {
    id: 2, name: '9折优惠券', desc: '全场9折', type: 'discount', value: 0.9,
    minAmount: 0, expireDate: '2026-04-30', status: 'unused',
  },
  {
    id: 3, name: '满100减20', desc: '满100元减20元', type: 'reduce', value: 20,
    minAmount: 100, expireDate: '2026-03-31', status: 'expired',
  },
  {
    id: 4, name: '生日专享券', desc: '满50元减15元', type: 'reduce', value: 15,
    minAmount: 50, expireDate: '2026-02-28', status: 'used',
  },
])

// available coupons to claim
const availableCoupons = ref<Coupon[]>([
  {
    id: 101, name: '春季特惠券', desc: '满50元减8元', type: 'reduce', value: 8,
    minAmount: 50, expireDate: '2026-04-30', received: false,
  },
  {
    id: 102, name: '下午茶8折券', desc: '下午2-5点使用', type: 'discount', value: 0.8,
    minAmount: 0, expireDate: '2026-05-15', received: false,
  },
  {
    id: 103, name: '会员专享券', desc: '满80减20', type: 'reduce', value: 20,
    minAmount: 80, expireDate: '2026-05-31', received: false,
  },
  {
    id: 104, name: '首单免运费券', desc: '在线下单满20元免配送费', type: 'free', value: 5,
    minAmount: 20, expireDate: '2026-04-20', received: true,
  },
])

const activeTab = ref('mine')

function claimCoupon(coupon: Coupon) {
  coupon.received = true
  myCoupons.value.unshift({
    ...coupon,
    id: Date.now(),
    status: 'unused',
  })
  ElMessage.success(`「${coupon.name}」领取成功！`)
}

function couponValueLabel(coupon: Coupon) {
  if (coupon.type === 'reduce') return `¥${coupon.value}`
  if (coupon.type === 'discount') return `${coupon.value * 10}折`
  return `免¥${coupon.value}`
}

function couponColor(coupon: Coupon) {
  if (coupon.status === 'used' || coupon.status === 'expired') return '#c0c4cc'
  if (coupon.type === 'discount') return '#67c23a'
  if (coupon.type === 'free') return '#e6a23c'
  return '#f56c6c'
}
</script>

<template>
  <div class="page miniapp-coupon">
    <el-tabs v-model="activeTab">
      <!-- my coupons -->
      <el-tab-pane label="我的优惠券" name="mine">
        <div v-if="myCoupons.length === 0" style="text-align:center;color:#909399;padding:40px 0">
          暂无优惠券，快去领取吧
        </div>
        <div v-for="coupon in myCoupons" :key="coupon.id" class="coupon-card" :class="coupon.status">
          <div class="coupon-left" :style="{ background: couponColor(coupon) }">
            <div class="coupon-value">{{ couponValueLabel(coupon) }}</div>
            <div class="coupon-min" v-if="coupon.minAmount > 0">满{{ coupon.minAmount }}元用</div>
            <div class="coupon-min" v-else>无门槛</div>
          </div>
          <div class="coupon-right">
            <div class="coupon-name">{{ coupon.name }}</div>
            <div class="coupon-desc">{{ coupon.desc }}</div>
            <div class="coupon-expire">有效期至 {{ coupon.expireDate }}</div>
          </div>
          <div class="coupon-status-badge">
            <el-tag v-if="coupon.status === 'used'" type="info" size="small">已使用</el-tag>
            <el-tag v-else-if="coupon.status === 'expired'" type="danger" size="small">已过期</el-tag>
            <el-tag v-else type="success" size="small">可使用</el-tag>
          </div>
        </div>
      </el-tab-pane>

      <!-- available to claim -->
      <el-tab-pane label="领取优惠券" name="available">
        <div v-for="coupon in availableCoupons" :key="coupon.id" class="coupon-card available">
          <div class="coupon-left" :style="{ background: couponColor(coupon) }">
            <div class="coupon-value">{{ couponValueLabel(coupon) }}</div>
            <div class="coupon-min" v-if="coupon.minAmount > 0">满{{ coupon.minAmount }}元用</div>
            <div class="coupon-min" v-else>无门槛</div>
          </div>
          <div class="coupon-right">
            <div class="coupon-name">{{ coupon.name }}</div>
            <div class="coupon-desc">{{ coupon.desc }}</div>
            <div class="coupon-expire">有效期至 {{ coupon.expireDate }}</div>
          </div>
          <div class="coupon-action">
            <el-button
              v-if="!coupon.received"
              type="primary"
              size="small"
              round
              @click="claimCoupon(coupon)"
            >立即领取</el-button>
            <el-tag v-else type="info" size="small">已领取</el-tag>
          </div>
        </div>
      </el-tab-pane>
    </el-tabs>
  </div>
</template>

<style scoped lang="scss">
.miniapp-coupon {
  max-width: 480px;
  margin: 0 auto;
}

.coupon-card {
  display: flex;
  align-items: stretch;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-bottom: 12px;
  background: #fff;
  position: relative;

  &.used, &.expired {
    opacity: 0.55;
    filter: grayscale(30%);
  }
}

.coupon-left {
  width: 80px;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #fff;
  padding: 10px 4px;
}

.coupon-value {
  font-size: 22px;
  font-weight: 900;
  line-height: 1;
}

.coupon-min {
  font-size: 11px;
  margin-top: 4px;
  opacity: 0.85;
}

.coupon-right {
  flex: 1;
  padding: 12px 10px;
}

.coupon-name {
  font-size: 14px;
  font-weight: 700;
  color: #303133;
  margin-bottom: 2px;
}

.coupon-desc {
  font-size: 12px;
  color: #909399;
  margin-bottom: 4px;
}

.coupon-expire {
  font-size: 11px;
  color: #c0c4cc;
}

.coupon-status-badge,
.coupon-action {
  display: flex;
  align-items: center;
  padding-right: 12px;
  flex-shrink: 0;
}
</style>
