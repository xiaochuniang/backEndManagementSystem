<template>
  <div class="page">
    <el-card shadow="hover">
      <template #header>小程序订单</template>
      <el-table :data="[{ orderNo: 'MP20260416001', channel: '小程序', amount: 58 }]" border>
        <el-table-column prop="orderNo" label="订单号" />
        <el-table-column prop="channel" label="渠道" />
        <el-table-column prop="amount" label="金额" />
      </el-table>
    </el-card>
  </div>
</template>
