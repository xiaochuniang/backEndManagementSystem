<template>
  <div class="page">
    <el-card shadow="hover">
      <template #header>小程序配置</template>
      <el-form label-width="140px">
        <el-form-item label="首页公告">
          <el-input type="textarea" :rows="3" placeholder="请输入公告文案" />
        </el-form-item>
        <el-form-item label="储值入口开关">
          <el-switch />
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>
