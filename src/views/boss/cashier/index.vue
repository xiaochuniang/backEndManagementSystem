<template>
  <div class="page">
    <el-card shadow="hover">
      <template #header>收银配置</template>
      <el-form label-width="120px">
        <el-form-item label="默认支付方式">
          <el-select placeholder="请选择">
            <el-option label="微信支付" value="wx" />
            <el-option label="支付宝" value="alipay" />
            <el-option label="现金" value="cash" />
          </el-select>
        </el-form-item>
        <el-form-item label="小票打印">
          <el-switch />
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>
