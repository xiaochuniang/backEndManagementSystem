<script setup lang="ts">
import { reactive, ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useUserStore } from '@/store/modules/user'
import { ElMessage } from 'element-plus'
import type { FormInstance, FormRules } from 'element-plus'
import { User, Lock, Switch, Key, Phone, ArrowLeft } from '@element-plus/icons-vue'

const router = useRouter()
const route = useRoute()
const userStore = useUserStore()

const formRef = ref<FormInstance>()
const loading = ref(false)

// captcha
const captchaCode = ref(generateCaptcha())
// captchaCanvas removed

function generateCaptcha() {
  const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'
  let result = ''
  for (let i = 0; i < 4; i++) result += chars[Math.floor(Math.random() * chars.length)]
  return result
}

function refreshCaptcha() {
  captchaCode.value = generateCaptcha()
}

const captchaStyle = computed(() => {
  // simple inline "image" via CSS gradient noise
  return {
    display: 'inline-flex',
    alignItems: 'center',
    justifyContent: 'center',
    width: '100px',
    height: '40px',
    background: 'linear-gradient(135deg, #e8f4fd 0%, #dbeeff 100%)',
    borderRadius: '4px',
    border: '1px solid #dcdfe6',
    fontFamily: 'monospace',
    fontSize: '20px',
    fontWeight: 700,
    letterSpacing: '4px',
    color: '#409eff',
    cursor: 'pointer',
    userSelect: 'none' as const,
  }
})

// page mode: login | forgot
const mode = ref<'login' | 'forgot'>('login')

const loginForm = reactive({
  username: 'boss',
  password: 'boss123',
  captcha: '',
  role: 'boss' as 'boss' | 'staff' | 'customerOps',
})

const forgotForm = reactive({
  phone: '',
  smsCode: '',
  newPassword: '',
  confirmPassword: '',
})

const countdownSec = ref(0)
let countdownTimer: ReturnType<typeof setInterval> | null = null

function sendSms() {
  if (!forgotForm.phone) {
    ElMessage.warning('请输入手机号')
    return
  }
  countdownSec.value = 60
  countdownTimer = setInterval(() => {
    countdownSec.value--
    if (countdownSec.value <= 0) {
      clearInterval(countdownTimer!)
      countdownTimer = null
    }
  }, 1000)
  ElMessage.success('验证码已发送（mock：1234）')
}

const rules: FormRules = {
  role: [{ required: true, message: '请选择登录端', trigger: 'change' }],
  username: [{ required: true, message: '请输入用户名', trigger: 'blur' }],
  password: [{ required: true, message: '请输入密码', trigger: 'blur' }],
  captcha: [
    { required: true, message: '请输入验证码', trigger: 'blur' },
    {
      validator: (_rule: unknown, value: string, callback: (err?: Error) => void) => {
        if (value.toUpperCase() !== captchaCode.value) {
          callback(new Error('验证码错误'))
        } else {
          callback()
        }
      },
      trigger: 'blur',
    },
  ],
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
    refreshCaptcha()
    loginForm.captcha = ''
  } finally {
    loading.value = false
  }
}

function handleResetPwd() {
  if (!forgotForm.phone || !forgotForm.smsCode) {
    ElMessage.warning('请填写手机号和验证码')
    return
  }
  if (forgotForm.smsCode !== '1234') {
    ElMessage.error('验证码错误（mock 验证码为 1234）')
    return
  }
  if (!forgotForm.newPassword) {
    ElMessage.warning('请输入新密码')
    return
  }
  if (forgotForm.newPassword !== forgotForm.confirmPassword) {
    ElMessage.error('两次密码不一致')
    return
  }
  ElMessage.success('密码重置成功（mock），请重新登录')
  mode.value = 'login'
}
</script>

<template>
  <div class="login-container">
    <div class="login-card">
      <!-- Login mode -->
      <template v-if="mode === 'login'">
        <h2 class="login-title">门店收银 · 会员储值</h2>
        <p class="login-subtitle">后台管理系统</p>
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
          <el-form-item prop="captcha">
            <div class="captcha-row">
              <el-input
                v-model="loginForm.captcha"
                placeholder="请输入验证码"
                :prefix-icon="Key"
                style="flex: 1"
                @keyup.enter="handleLogin"
              />
              <div :style="captchaStyle" title="点击刷新" @click="refreshCaptcha">
                {{ captchaCode }}
              </div>
            </div>
          </el-form-item>
          <el-form-item>
            <div class="form-actions">
              <el-button link type="primary" size="small" @click="mode = 'forgot'"
                >忘记密码?</el-button
              >
            </div>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" :loading="loading" style="width: 100%" @click="handleLogin">
              登 录
            </el-button>
          </el-form-item>
        </el-form>
        <div class="login-hints">
          <p>老板端：boss / boss123</p>
          <p>员工端：staff / staff123</p>
          <p>顾客运营端：customer / customer123</p>
        </div>
      </template>

      <!-- Forgot password mode -->
      <template v-else>
        <div class="back-row">
          <el-button :icon="ArrowLeft" link @click="mode = 'login'">返回登录</el-button>
        </div>
        <h2 class="login-title" style="margin-top: 0">找回密码</h2>
        <el-form :model="forgotForm" size="large">
          <el-form-item>
            <el-input
              v-model="forgotForm.phone"
              placeholder="请输入注册手机号"
              :prefix-icon="Phone"
            />
          </el-form-item>
          <el-form-item>
            <div class="captcha-row">
              <el-input
                v-model="forgotForm.smsCode"
                placeholder="请输入短信验证码"
                :prefix-icon="Key"
                style="flex: 1"
              />
              <el-button
                type="primary"
                plain
                :disabled="countdownSec > 0"
                style="width: 110px; flex-shrink: 0"
                @click="sendSms"
              >
                {{ countdownSec > 0 ? `${countdownSec}s后重发` : '获取验证码' }}
              </el-button>
            </div>
          </el-form-item>
          <el-form-item>
            <el-input
              v-model="forgotForm.newPassword"
              type="password"
              show-password
              placeholder="请输入新密码"
              :prefix-icon="Lock"
            />
          </el-form-item>
          <el-form-item>
            <el-input
              v-model="forgotForm.confirmPassword"
              type="password"
              show-password
              placeholder="再次确认新密码"
              :prefix-icon="Lock"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" style="width: 100%" @click="handleResetPwd"
              >重置密码</el-button
            >
          </el-form-item>
        </el-form>
      </template>
    </div>
  </div>
</template>

<style scoped lang="scss">
.login-subtitle {
  text-align: center;
  color: #909399;
  font-size: 13px;
  margin-bottom: 24px;
  margin-top: -20px;
}

.captcha-row {
  display: flex;
  gap: 10px;
  width: 100%;
  align-items: center;
}

.form-actions {
  width: 100%;
  display: flex;
  justify-content: flex-end;
}

.login-hints {
  color: #999;
  font-size: 12px;
  text-align: center;
  p {
    margin: 2px 0;
  }
}

.back-row {
  margin-bottom: 16px;
}
</style>
