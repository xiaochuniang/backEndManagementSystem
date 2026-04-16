<template>
  <div class="page">
    <el-card shadow="hover">
      <template #header>订单列表</template>
      <el-table :data="[{ orderNo: 'SO20260416001', amount: 99, status: '已完成' }]" border>
        <el-table-column prop="orderNo" label="订单号" />
        <el-table-column prop="amount" label="金额" />
        <el-table-column prop="status" label="状态" />
      </el-table>
    </el-card>
  </div>
</template>
