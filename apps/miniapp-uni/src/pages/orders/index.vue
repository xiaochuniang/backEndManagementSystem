<script setup lang="ts">
import { orderList } from '../../mock/member'
</script>

<template>
  <view class="page">
    <view v-for="order in orderList" :key="order.id" class="card">
      <view class="row">
        <text class="id">{{ order.id }}</text>
        <text class="status">{{ order.status }}</text>
      </view>
      <view class="row info">门店：{{ order.shopName }}</view>
      <view class="row info">时间：{{ order.createdAt }}</view>
      <view class="row amount">金额：￥{{ order.amount.toFixed(2) }}</view>
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
  padding: 24rpx;
  margin-bottom: 20rpx;
}
.row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10rpx;
}
.id {
  font-weight: 600;
}
.status {
  color: #409eff;
}
.info {
  color: #606266;
}
.amount {
  color: #303133;
  font-weight: 600;
}
</style>
