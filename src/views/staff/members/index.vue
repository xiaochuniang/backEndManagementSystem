<script setup lang="ts">
import { ref, reactive } from 'vue'
import { ElMessage } from 'element-plus'
import { Search, User } from '@element-plus/icons-vue'

interface MemberInfo {
  id: number
  phone: string
  level: string
  balance: number
  points: number
  registerTime: string
}
interface ConsumeRecord {
  orderNo: string
  amount: number
  time: string
}
interface DepositRecord {
  amount: number
  gift: number
  time: string
}

// mock member database
const mockMembers: Record<string, MemberInfo> = {
  '13811110001': { id: 1, phone: '13811110001', level: '银卡', balance: 328.5, points: 1680, registerTime: '2025-12-01 10:00' },
  '13912340002': { id: 2, phone: '13912340002', level: '金卡', balance: 1250, points: 5200, registerTime: '2025-10-15 14:30' },
  '13611110003': { id: 3, phone: '13611110003', level: '普通', balance: 50, points: 200, registerTime: '2026-01-20 09:00' },
}

const mockConsumeRecords: ConsumeRecord[] = [
  { orderNo: 'SO20260410001', amount: 78, time: '2026-04-10 15:23' },
  { orderNo: 'SO20260408002', amount: 120, time: '2026-04-08 11:00' },
  { orderNo: 'SO20260405003', amount: 45, time: '2026-04-05 14:30' },
]
const mockDepositRecords: DepositRecord[] = [
  { amount: 300, gift: 30, time: '2026-03-20 10:00' },
]

// ── state ──────────────────────────────────────────────────
const phoneInput = ref('')
const loading = ref(false)
const currentMember = ref<MemberInfo | null>(null)
const activeTab = ref('consume')
const detailVisible = ref(false)

// deposit dialog
const depositVisible = ref(false)
const depositAmount = ref<number | undefined>(undefined)

// points adjust dialog
const pointsVisible = ref(false)
const pointsChange = ref<number | undefined>(undefined)
const pointsRemark = ref('')

// balance payment dialog
const payVisible = ref(false)
const payAmount = ref<number | undefined>(undefined)

const levelTag: Record<string, 'warning' | 'success' | ''> = {
  普通: '',
  银卡: 'warning',
  金卡: 'success',
}

// ── methods ────────────────────────────────────────────────
function searchMember() {
  if (!phoneInput.value) {
    ElMessage.warning('请输入会员手机号')
    return
  }
  loading.value = true
  setTimeout(() => {
    loading.value = false
    const found = mockMembers[phoneInput.value]
    if (found) {
      currentMember.value = { ...found }
      detailVisible.value = true
    } else {
      ElMessage.warning('未查询到该会员，请检查手机号')
      currentMember.value = null
    }
  }, 400)
}

function openDeposit() {
  depositAmount.value = undefined
  depositVisible.value = true
}

function confirmDeposit() {
  if (!depositAmount.value || depositAmount.value <= 0) {
    ElMessage.warning('请输入有效储值金额')
    return
  }
  const gift = depositAmount.value >= 500 ? 100 : depositAmount.value >= 300 ? 50 : depositAmount.value >= 100 ? 10 : 0
  currentMember.value!.balance += depositAmount.value + gift
  ElMessage.success(`储值成功！充入 ¥${depositAmount.value}，赠送 ¥${gift}`)
  depositVisible.value = false
}

function openBalancePay() {
  payAmount.value = undefined
  payVisible.value = true
}

function confirmBalancePay() {
  if (!payAmount.value || payAmount.value <= 0) {
    ElMessage.warning('请输入支付金额')
    return
  }
  if (payAmount.value > currentMember.value!.balance) {
    ElMessage.error('余额不足')
    return
  }
  currentMember.value!.balance -= payAmount.value
  const earned = Math.floor(payAmount.value)
  currentMember.value!.points += earned
  ElMessage.success(`余额支付 ¥${payAmount.value} 成功，获得 ${earned} 积分`)
  payVisible.value = false
}

function adjustPoints() {
  pointsChange.value = undefined
  pointsRemark.value = ''
  pointsVisible.value = true
}

function confirmPoints() {
  if (pointsChange.value === undefined) {
    ElMessage.warning('请输入积分变动值')
    return
  }
  currentMember.value!.points += pointsChange.value
  if (currentMember.value!.points < 0) currentMember.value!.points = 0
  ElMessage.success('积分调整成功')
  pointsVisible.value = false
}
</script>

<template>
  <div class="page">
    <!-- search bar -->
    <el-card shadow="hover" style="margin-bottom:16px">
      <div class="search-area">
        <span class="search-label">会员核销</span>
        <el-input
          v-model="phoneInput"
          placeholder="请输入会员手机号（如 13811110001）"
          :prefix-icon="Search"
          style="max-width:320px"
          clearable
          @keyup.enter="searchMember"
        />
        <el-button type="primary" :loading="loading" :icon="User" @click="searchMember">查询会员</el-button>
      </div>
      <div style="color:#909399;font-size:12px;margin-top:8px">
        测试账号：13811110001（银卡）/ 13912340002（金卡）/ 13611110003（普通）
      </div>
    </el-card>

    <!-- member detail drawer -->
    <el-drawer
      v-model="detailVisible"
      title="会员信息"
      direction="rtl"
      size="440px"
    >
      <template v-if="currentMember">
        <!-- info card -->
        <el-card shadow="never" style="margin-bottom:16px">
          <div class="member-header">
            <el-avatar :size="56" icon="User" />
            <div class="member-meta">
              <div class="member-phone">{{ currentMember.phone }}</div>
              <el-tag :type="levelTag[currentMember.level]">{{ currentMember.level }}</el-tag>
            </div>
          </div>
          <div class="stat-row">
            <div class="stat-item">
              <div class="stat-val">¥{{ currentMember.balance.toFixed(2) }}</div>
              <div class="stat-key">账户余额</div>
            </div>
            <div class="stat-item">
              <div class="stat-val">{{ currentMember.points }}</div>
              <div class="stat-key">积分</div>
            </div>
            <div class="stat-item">
              <div class="stat-val">{{ currentMember.registerTime.slice(0, 10) }}</div>
              <div class="stat-key">注册日期</div>
            </div>
          </div>
        </el-card>

        <!-- action buttons -->
        <div class="action-row">
          <el-button type="primary" @click="openDeposit">储值充值</el-button>
          <el-button type="success" @click="openBalancePay">余额支付</el-button>
          <el-button @click="adjustPoints">积分调整</el-button>
        </div>

        <!-- history tabs -->
        <el-tabs v-model="activeTab" style="margin-top:12px">
          <el-tab-pane label="消费记录" name="consume">
            <el-table :data="mockConsumeRecords" size="small">
              <el-table-column prop="orderNo" label="订单号" />
              <el-table-column prop="amount" label="金额" width="80">
                <template #default="{ row }">¥{{ row.amount }}</template>
              </el-table-column>
              <el-table-column prop="time" label="时间" width="130" />
            </el-table>
          </el-tab-pane>
          <el-tab-pane label="储值记录" name="deposit">
            <el-table :data="mockDepositRecords" size="small">
              <el-table-column prop="amount" label="充值金额" width="90">
                <template #default="{ row }">¥{{ row.amount }}</template>
              </el-table-column>
              <el-table-column prop="gift" label="赠送" width="70">
                <template #default="{ row }">¥{{ row.gift }}</template>
              </el-table-column>
              <el-table-column prop="time" label="时间" />
            </el-table>
          </el-tab-pane>
        </el-tabs>
      </template>
    </el-drawer>

    <!-- deposit dialog -->
    <el-dialog v-model="depositVisible" title="储值充值" width="360px">
      <el-form label-width="90px">
        <el-form-item label="会员">{{ currentMember?.phone }}</el-form-item>
        <el-form-item label="当前余额">¥{{ currentMember?.balance.toFixed(2) }}</el-form-item>
        <el-form-item label="充值金额">
          <el-input-number v-model="depositAmount" :min="1" :precision="2" placeholder="请输入" style="width:100%" />
        </el-form-item>
        <el-form-item label="储值规则">
          <el-tag size="small">充100赠10 · 充300赠50 · 充500赠100</el-tag>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="depositVisible = false">取消</el-button>
        <el-button type="primary" @click="confirmDeposit">确认储值</el-button>
      </template>
    </el-dialog>

    <!-- balance pay dialog -->
    <el-dialog v-model="payVisible" title="余额支付" width="360px">
      <el-form label-width="90px">
        <el-form-item label="会员">{{ currentMember?.phone }}</el-form-item>
        <el-form-item label="当前余额">¥{{ currentMember?.balance.toFixed(2) }}</el-form-item>
        <el-form-item label="支付金额">
          <el-input-number v-model="payAmount" :min="0.01" :max="currentMember?.balance" :precision="2" placeholder="请输入" style="width:100%" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="payVisible = false">取消</el-button>
        <el-button type="success" @click="confirmBalancePay">确认支付</el-button>
      </template>
    </el-dialog>

    <!-- points dialog -->
    <el-dialog v-model="pointsVisible" title="积分调整" width="360px">
      <el-form label-width="90px">
        <el-form-item label="会员">{{ currentMember?.phone }}</el-form-item>
        <el-form-item label="当前积分">{{ currentMember?.points }}</el-form-item>
        <el-form-item label="变动值">
          <el-input-number v-model="pointsChange" placeholder="正数增加，负数减少" style="width:100%" />
        </el-form-item>
        <el-form-item label="备注">
          <el-input v-model="pointsRemark" placeholder="操作原因" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="pointsVisible = false">取消</el-button>
        <el-button type="primary" @click="confirmPoints">确认调整</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<style scoped lang="scss">
.search-area {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.search-label {
  font-size: 15px;
  font-weight: 600;
  color: #303133;
  flex-shrink: 0;
}

.member-header {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 16px;
}

.member-meta {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.member-phone {
  font-size: 16px;
  font-weight: 600;
  color: #303133;
}

.stat-row {
  display: flex;
  gap: 0;
  text-align: center;
}

.stat-item {
  flex: 1;
  padding: 8px 0;
  border-right: 1px solid #f0f0f0;

  &:last-child {
    border-right: none;
  }
}

.stat-val {
  font-size: 16px;
  font-weight: 700;
  color: #409eff;
}

.stat-key {
  font-size: 12px;
  color: #909399;
  margin-top: 2px;
}

.action-row {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  margin-bottom: 4px;
}
</style>
