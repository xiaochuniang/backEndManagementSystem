<template>
  <div class="page">
    <el-row :gutter="16">
      <el-col :span="8"><el-card shadow="hover">今日营收：￥6,820</el-card></el-col>
      <el-col :span="8"><el-card shadow="hover">储值金额：￥2,180</el-card></el-col>
      <el-col :span="8"><el-card shadow="hover">新增会员：26</el-card></el-col>
    </el-row>
    <el-card style="margin-top: 16px" shadow="hover">
      <template #header>老板端总览</template>
      <el-alert
        title="后续可接入门店利润、储值转化、复购分析等指标。"
        type="success"
        :closable="false"
      />
    </el-card>
  </div>
</template>
