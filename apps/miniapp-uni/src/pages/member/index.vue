<script setup lang="ts">
import { memberInfo } from '../../mock/member'
</script>

<template>
  <view class="page">
    <view class="card">
      <view class="title">会员中心</view>
      <view class="line">姓名：{{ memberInfo.name }}</view>
      <view class="line">等级：{{ memberInfo.level }}</view>
      <view class="line">余额：￥{{ memberInfo.balance.toFixed(2) }}</view>
      <view class="line">积分：{{ memberInfo.points }}</view>
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
  font-size: 32rpx;
  font-weight: 600;
  margin-bottom: 20rpx;
}
.line {
  margin-bottom: 14rpx;
  color: #606266;
}
</style>
