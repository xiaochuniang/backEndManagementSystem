<script setup lang="ts">
import { ref } from 'vue'

const amount = ref(100)
const options = [100, 200, 500, 1000]

function submitRecharge() {
  uni.showToast({ title: `已提交储值￥${amount.value}`, icon: 'none' })
}
</script>

<template>
  <view class="page">
    <view class="card">
      <view class="title">充值金额</view>
      <view class="amount-grid">
        <view
          v-for="item in options"
          :key="item"
          class="amount-item"
          :class="{ active: amount === item }"
          @click="amount = item"
        >
          ￥{{ item }}
        </view>
      </view>
      <button class="btn" type="primary" @click="submitRecharge">确认充值</button>
    </view>
  </view>
</template>

<style scoped>
.page {
  padding: 24rpx;
}
.card {
  background: #fff;
  border-radius: 16rpx;
  padding: 28rpx;
}
.title {
  font-size: 30rpx;
  margin-bottom: 20rpx;
}
.amount-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 18rpx;
  margin-bottom: 28rpx;
}
.amount-item {
  border: 2rpx solid #dcdfe6;
  border-radius: 12rpx;
  text-align: center;
  padding: 20rpx;
  color: #606266;
}
.amount-item.active {
  border-color: #409eff;
  color: #409eff;
  font-weight: 600;
}
.btn {
  font-size: 28rpx;
}
</style>
