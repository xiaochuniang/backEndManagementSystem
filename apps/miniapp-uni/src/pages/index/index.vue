<script setup lang="ts">
import { memberInfo } from '../../mock/member'

function goRecharge() {
  uni.navigateTo({ url: '/pages/recharge/index' })
}

function goMemberCenter() {
  uni.navigateTo({ url: '/pages/member/index' })
}
</script>

<template>
  <view class="page">
    <view class="card highlight">
      <view class="title">当前会员余额</view>
      <view class="balance">￥{{ memberInfo.balance.toFixed(2) }}</view>
      <button class="btn" type="primary" @click="goRecharge">立即储值</button>
    </view>

    <view class="card">
      <view class="title">会员权益</view>
      <view class="desc">等级：{{ memberInfo.level }} ｜ 积分：{{ memberInfo.points }}</view>
      <button class="btn" @click="goMemberCenter">进入会员中心</button>
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
  margin-bottom: 24rpx;
}

.highlight {
  background: linear-gradient(135deg, #4f80ff, #7d9cff);
  color: #fff;
}

.title {
  font-size: 28rpx;
}

.balance {
  font-size: 56rpx;
  margin: 16rpx 0 22rpx;
  font-weight: 700;
}

.desc {
  font-size: 26rpx;
  margin: 10rpx 0 20rpx;
}

.btn {
  font-size: 28rpx;
}
</style>
