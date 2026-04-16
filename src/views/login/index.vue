<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useUserStore } from '@/store/modules/user'
import { ElMessage } from 'element-plus'
import type { FormInstance, FormRules } from 'element-plus'
import { User, Lock, Switch } from '@element-plus/icons-vue'

const router = useRouter()
const route = useRoute()
const userStore = useUserStore()

const formRef = ref<FormInstance>()
const loading = ref(false)

const loginForm = reactive({
  username: 'boss',
  password: 'boss123',
  role: 'boss' as 'boss' | 'staff' | 'customerOps',
})

const rules: FormRules = {
  role: [{ required: true, message: '请选择登录端', trigger: 'change' }],
  username: [{ required: true, message: '请输入用户名', trigger: 'blur' }],
  password: [{ required: true, message: '请输入密码', trigger: 'blur' }],
}

function handleRoleChange(role: 'boss' | 'staff' | 'customerOps') {
  const accountMap = {
    boss: { username: 'boss', password: 'boss123' },
    staff: { username: 'staff', password: 'staff123' },
    customerOps: { username: 'customer', password: 'customer123' },
  }
  loginForm.username = accountMap[role].username
  loginForm.password = accountMap[role].password
}

async function handleLogin() {
  const valid = await formRef.value?.validate().catch(() => false)
  if (!valid) return

  loading.value = true
  try {
    await userStore.login({
      username: loginForm.username,
      password: loginForm.password,
      role: loginForm.role,
    })
    const redirect = (route.query.redirect as string) || '/'
    router.push(redirect)
    ElMessage.success('登录成功')
  } catch {
    ElMessage.error('登录失败，请检查用户名和密码')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="login-container">
    <div class="login-card">
      <h2 class="login-title">Vue3 Admin System</h2>
      <el-form ref="formRef" :model="loginForm" :rules="rules" size="large">
        <el-form-item prop="role">
          <el-select
            v-model="loginForm.role"
            placeholder="请选择登录端"
            :prefix-icon="Switch"
            style="width: 100%"
            @change="handleRoleChange"
          >
            <el-option label="老板端（boss）" value="boss" />
            <el-option label="员工端（staff）" value="staff" />
            <el-option label="顾客端运营（customerOps）" value="customerOps" />
          </el-select>
        </el-form-item>
        <el-form-item prop="username">
          <el-input v-model="loginForm.username" placeholder="请输入用户名" :prefix-icon="User" />
        </el-form-item>
        <el-form-item prop="password">
          <el-input
            v-model="loginForm.password"
            type="password"
            placeholder="请输入密码"
            show-password
            :prefix-icon="Lock"
            @keyup.enter="handleLogin"
          />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" :loading="loading" style="width: 100%" @click="handleLogin">
            登 录
          </el-button>
        </el-form-item>
      </el-form>
      <div style="color: #999; font-size: 12px; text-align: center">
        <p>老板端：boss / boss123</p>
        <p>员工端：staff / staff123</p>
        <p>顾客运营端：customer / customer123</p>
      </div>
    </div>
  </div>
</template>
