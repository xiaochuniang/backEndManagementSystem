<script setup lang="ts">
import { ref, reactive } from 'vue'
import { ElMessage } from 'element-plus'
import { User } from '@element-plus/icons-vue'

interface ConsumeRecord { orderNo: string; amount: number; time: string; points: number }
interface DepositRecord  { amount: number; gift: number; time: string }
interface PointsRecord   { change: number; reason: string; time: string }

const memberInfo = reactive({
  phone: '138****0001',
  level: '银卡',
  balance: 328.5,
  points: 1680,
  registerTime: '2025-12-01',
})

const depositRules = [
  { id: 1, label: '充100赠10',  amount: 100, gift: 10 },
  { id: 2, label: '充300赠50',  amount: 300, gift: 50 },
  { id: 3, label: '充500赠100', amount: 500, gift: 100 },
  { id: 4, label: '充1000赠300',amount: 1000,gift: 300 },
]

const consumeRecords = ref<ConsumeRecord[]>([
  { orderNo: 'MP20260416001', amount: 45, time: '2026-04-16 10:05', points: 45 },
  { orderNo: 'MP20260410002', amount: 78, time: '2026-04-10 15:23', points: 78 },
  { orderNo: 'MP20260405003', amount: 25, time: '2026-04-05 14:30', points: 25 },
])

const depositRecords = ref<DepositRecord[]>([
  { amount: 300, gift: 50, time: '2026-03-20 10:00' },
  { amount: 100, gift: 10, time: '2026-01-05 14:00' },
])

const pointsRecords = ref<PointsRecord[]>([
  { change: +45,   reason: '消费积分',   time: '2026-04-16 10:05' },
  { change: +78,   reason: '消费积分',   time: '2026-04-10 15:23' },
  { change: -100,  reason: '积分兑换',   time: '2026-03-28 09:00' },
  { change: +200,  reason: '储值赠积分', time: '2026-03-20 10:00' },
])

const activeTab = ref('consume')

// deposit
const depositVisible = ref(false)
const selectedRule = ref<typeof depositRules[0] | null>(null)

function openDeposit() {
  selectedRule.value = null
  depositVisible.value = true
}

function confirmDeposit() {
  if (!selectedRule.value) {
    ElMessage.warning('请选择储值规则')
    return
  }
  memberInfo.balance += selectedRule.value.amount + selectedRule.value.gift
  depositRecords.value.unshift({
    amount: selectedRule.value.amount,
    gift: selectedRule.value.gift,
    time: new Date().toLocaleString('zh-CN').replace(/\//g, '-').slice(0, 16),
  })
  ElMessage.success(`储值成功！充入 ¥${selectedRule.value.amount}，赠送 ¥${selectedRule.value.gift}`)
  depositVisible.value = false
}

const levelTag: Record<string, 'warning' | 'success' | ''> = { 普通: '', 银卡: 'warning', 金卡: 'success' }
</script>

<template>
  <div class="page miniapp-member">
    <!-- personal info card -->
    <el-card shadow="hover" class="profile-card">
      <div class="profile-header">
        <el-avatar :size="56" :icon="User" />
        <div class="profile-meta">
          <div class="profile-phone">{{ memberInfo.phone }}</div>
          <el-tag :type="levelTag[memberInfo.level]">{{ memberInfo.level }}会员</el-tag>
          <div class="profile-date">注册：{{ memberInfo.registerTime }}</div>
        </div>
      </div>

      <div class="balance-row">
        <div class="balance-item">
          <div class="balance-val">¥{{ memberInfo.balance.toFixed(2) }}</div>
          <div class="balance-key">账户余额</div>
        </div>
        <div class="divider-v" />
        <div class="balance-item">
          <div class="balance-val">{{ memberInfo.points }}</div>
          <div class="balance-key">积分</div>
        </div>
      </div>

      <el-button type="primary" style="width:100%;border-radius:24px;margin-top:12px" @click="openDeposit">
        💳 余额充值 / 储值
      </el-button>
    </el-card>

    <!-- records tabs -->
    <el-card shadow="hover" style="margin-top:12px">
      <el-tabs v-model="activeTab">
        <el-tab-pane label="消费记录" name="consume">
          <el-table :data="consumeRecords" size="small">
            <el-table-column prop="orderNo" label="订单号" min-width="130" />
            <el-table-column prop="amount" label="金额" width="70">
              <template #default="{ row }">¥{{ row.amount }}</template>
            </el-table-column>
            <el-table-column prop="points" label="积分" width="60">
              <template #default="{ row }">+{{ row.points }}</template>
            </el-table-column>
            <el-table-column prop="time" label="时间" min-width="130" />
          </el-table>
        </el-tab-pane>

        <el-tab-pane label="储值记录" name="deposit">
          <el-table :data="depositRecords" size="small">
            <el-table-column prop="amount" label="充值额" width="80">
              <template #default="{ row }">¥{{ row.amount }}</template>
            </el-table-column>
            <el-table-column prop="gift" label="赠送" width="70">
              <template #default="{ row }">+¥{{ row.gift }}</template>
            </el-table-column>
            <el-table-column prop="time" label="时间" />
          </el-table>
        </el-tab-pane>

        <el-tab-pane label="积分记录" name="points">
          <el-table :data="pointsRecords" size="small">
            <el-table-column prop="reason" label="说明" />
            <el-table-column prop="change" label="积分" width="80">
              <template #default="{ row }">
                <span :style="{ color: row.change > 0 ? '#67c23a' : '#f56c6c' }">
                  {{ row.change > 0 ? '+' : '' }}{{ row.change }}
                </span>
              </template>
            </el-table-column>
            <el-table-column prop="time" label="时间" min-width="130" />
          </el-table>
        </el-tab-pane>
      </el-tabs>
    </el-card>

    <!-- deposit dialog -->
    <el-dialog v-model="depositVisible" title="余额充值" width="380px">
      <div style="margin-bottom:12px;color:#606266;font-size:13px">当前余额：¥{{ memberInfo.balance.toFixed(2) }}</div>
      <div class="rule-grid">
        <div
          v-for="rule in depositRules"
          :key="rule.id"
          :class="['rule-item', { selected: selectedRule?.id === rule.id }]"
          @click="selectedRule = rule"
        >
          <div class="rule-amount">¥{{ rule.amount }}</div>
          <div class="rule-gift">赠 ¥{{ rule.gift }}</div>
        </div>
      </div>
      <div v-if="selectedRule" style="margin-top:12px;text-align:center;color:#909399;font-size:13px">
        实付 ¥{{ selectedRule.amount }}，到账 ¥{{ selectedRule.amount + selectedRule.gift }}
      </div>
      <template #footer>
        <el-button @click="depositVisible = false">取消</el-button>
        <el-button type="primary" @click="confirmDeposit">微信支付充值</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<style scoped lang="scss">
.miniapp-member {
  max-width: 480px;
  margin: 0 auto;
}

.profile-card {
  background: linear-gradient(135deg, #ecf5ff 0%, #f0f9eb 100%);
}

.profile-header {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 16px;
}

.profile-meta {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.profile-phone {
  font-size: 16px;
  font-weight: 700;
  color: #303133;
}

.profile-date {
  font-size: 12px;
  color: #909399;
}

.balance-row {
  display: flex;
  align-items: center;
  background: #fff;
  border-radius: 10px;
  padding: 14px 0;
}

.balance-item {
  flex: 1;
  text-align: center;
}

.balance-val {
  font-size: 22px;
  font-weight: 700;
  color: #409eff;
}

.balance-key {
  font-size: 12px;
  color: #909399;
  margin-top: 2px;
}

.divider-v {
  width: 1px;
  height: 40px;
  background: #ebeef5;
}

.rule-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
}

.rule-item {
  border: 2px solid #ebeef5;
  border-radius: 10px;
  padding: 14px 10px;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;

  &:hover {
    border-color: #409eff;
  }

  &.selected {
    border-color: #409eff;
    background: #ecf5ff;
  }
}

.rule-amount {
  font-size: 22px;
  font-weight: 700;
  color: #303133;
}

.rule-gift {
  font-size: 12px;
  color: #f56c6c;
  margin-top: 4px;
}
</style>
