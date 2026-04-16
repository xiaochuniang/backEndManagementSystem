<template>
  <div class="page">
    <el-card shadow="hover">
      <template #header>会员储值办理</template>
      <el-form inline>
        <el-form-item label="手机号">
          <el-input placeholder="请输入会员手机号" />
        </el-form-item>
        <el-form-item label="储值金额">
          <el-input placeholder="请输入储值金额" />
        </el-form-item>
      </el-form>
      <el-button type="primary">确认储值</el-button>
    </el-card>
  </div>
</template>
