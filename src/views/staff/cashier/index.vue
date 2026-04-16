<template>
  <div class="page">
    <el-card shadow="hover">
      <template #header>收银台</template>
      <el-form label-width="100px">
        <el-form-item label="订单号">
          <el-input placeholder="请输入订单号" />
        </el-form-item>
        <el-form-item label="应收金额">
          <el-input placeholder="￥0.00" />
        </el-form-item>
      </el-form>
      <el-button type="primary">提交收银</el-button>
    </el-card>
  </div>
</template>
