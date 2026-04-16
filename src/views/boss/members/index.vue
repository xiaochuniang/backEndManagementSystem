<template>
  <div class="page">
    <el-card shadow="hover">
      <template #header>会员储值管理</template>
      <el-table
        :data="[
          { level: '银卡', recharge: 100 },
          { level: '金卡', recharge: 300 },
        ]"
        border
      >
        <el-table-column prop="level" label="会员等级" />
        <el-table-column prop="recharge" label="建议储值门槛(元)" />
        <el-table-column label="状态">
          <template #default>
            <el-tag type="success">启用中</el-tag>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>
