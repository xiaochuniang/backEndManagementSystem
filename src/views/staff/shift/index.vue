<script setup lang="ts">
import { ref, reactive, computed } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Check, DocumentChecked } from '@element-plus/icons-vue'

interface ShiftRecord {
  id: number
  staffName: string
  startTime: string
  endTime: string
  revenue: number
  orderCount: number
  remark: string
}
interface ReconDetail {
  payMethod: string
  systemAmount: number
  actualAmount: number
  diff: number
  remark: string
}

// ── current shift state ────────────────────────────────────
const shiftActive = ref(false)
const shiftStartTime = ref('')

// recon form
const reconDetails = ref<ReconDetail[]>([
  { payMethod: '微信支付', systemAmount: 2180, actualAmount: 2180, diff: 0, remark: '' },
  { payMethod: '支付宝',   systemAmount: 960,  actualAmount: 960,  diff: 0, remark: '' },
  { payMethod: '现金',     systemAmount: 640,  actualAmount: 620,  diff: -20, remark: '找零误差' },
  { payMethod: '会员余额', systemAmount: 487,  actualAmount: 487,  diff: 0, remark: '' },
])

const reconRemark = ref('')

const systemTotal = computed(() => reconDetails.value.reduce((s, r) => s + r.systemAmount, 0))
const actualTotal = computed(() => reconDetails.value.reduce((s, r) => s + r.actualAmount, 0))
const totalDiff = computed(() => actualTotal.value - systemTotal.value)

function updateDiff(row: ReconDetail) {
  row.diff = row.actualAmount - row.systemAmount
}

// ── history ────────────────────────────────────────────────
const shiftRecords = ref<ShiftRecord[]>([
  {
    id: 1, staffName: '张员工', startTime: '2026-04-16 08:00',
    endTime: '2026-04-16 14:00', revenue: 3640, orderCount: 62, remark: '',
  },
  {
    id: 2, staffName: '李员工', startTime: '2026-04-16 14:00',
    endTime: '2026-04-16 20:00', revenue: 4980, orderCount: 81, remark: '无异常',
  },
])

// ── methods ────────────────────────────────────────────────
function startShift() {
  shiftStartTime.value = new Date().toLocaleString('zh-CN').replace(/\//g, '-').slice(0, 16)
  shiftActive.value = true
  ElMessage.success(`开班成功，开始时间：${shiftStartTime.value}`)
}

function submitShift() {
  ElMessageBox.confirm('确认提交交班记录？请确保已核对营收数据。', '提交交班', {
    confirmButtonText: '确认交班',
    cancelButtonText: '返回检查',
    type: 'warning',
  }).then(() => {
    const endTime = new Date().toLocaleString('zh-CN').replace(/\//g, '-').slice(0, 16)
    const newRecord: ShiftRecord = {
      id: shiftRecords.value.length + 1,
      staffName: '当前员工',
      startTime: shiftStartTime.value,
      endTime,
      revenue: actualTotal.value,
      orderCount: 47,
      remark: reconRemark.value,
    }
    shiftRecords.value.unshift(newRecord)
    shiftActive.value = false
    shiftStartTime.value = ''
    reconRemark.value = ''
    // reset recon
    reconDetails.value.forEach((r) => {
      r.actualAmount = r.systemAmount
      r.diff = 0
      r.remark = ''
    })
    ElMessage.success('交班成功！')
  })
}

function diffType(diff: number) {
  if (diff === 0) return 'success'
  if (diff < 0) return 'danger'
  return 'warning'
}
</script>

<template>
  <div class="page">
    <!-- shift status banner -->
    <el-card shadow="hover" style="margin-bottom:16px">
      <div class="shift-banner">
        <div class="shift-info">
          <el-tag :type="shiftActive ? 'success' : 'info'" size="large">
            {{ shiftActive ? '当班中' : '未开班' }}
          </el-tag>
          <span v-if="shiftActive" style="color:#606266;font-size:13px;margin-left:12px">
            开班时间：{{ shiftStartTime }}
          </span>
        </div>
        <el-button
          v-if="!shiftActive"
          type="primary"
          :icon="Check"
          @click="startShift"
        >开始当班</el-button>
      </div>
    </el-card>

    <!-- recon form (only when active) -->
    <template v-if="shiftActive">
      <!-- revenue summary -->
      <el-row :gutter="12" style="margin-bottom:16px">
        <el-col :xs="24" :sm="8">
          <el-card shadow="hover" class="stat-card">
            <div class="stat-num" style="color:#409eff">¥{{ systemTotal.toFixed(2) }}</div>
            <div class="stat-label">系统应收</div>
          </el-card>
        </el-col>
        <el-col :xs="24" :sm="8">
          <el-card shadow="hover" class="stat-card">
            <div class="stat-num" style="color:#67c23a">¥{{ actualTotal.toFixed(2) }}</div>
            <div class="stat-label">实际收款</div>
          </el-card>
        </el-col>
        <el-col :xs="24" :sm="8">
          <el-card shadow="hover" class="stat-card">
            <div
              class="stat-num"
              :style="{ color: totalDiff === 0 ? '#67c23a' : totalDiff < 0 ? '#f56c6c' : '#e6a23c' }"
            >
              {{ totalDiff >= 0 ? '+' : '' }}¥{{ totalDiff.toFixed(2) }}
            </div>
            <div class="stat-label">差异金额</div>
          </el-card>
        </el-col>
      </el-row>

      <!-- recon detail table -->
      <el-card shadow="hover" style="margin-bottom:16px">
        <template #header>营收核对明细</template>
        <el-table :data="reconDetails" border>
          <el-table-column prop="payMethod" label="支付方式" width="110" />
          <el-table-column prop="systemAmount" label="系统金额（元）" width="130">
            <template #default="{ row }">¥{{ row.systemAmount.toFixed(2) }}</template>
          </el-table-column>
          <el-table-column label="实际金额（元）" width="160">
            <template #default="{ row }">
              <el-input-number
                v-model="row.actualAmount"
                :min="0"
                :precision="2"
                size="small"
                style="width:130px"
                @change="updateDiff(row)"
              />
            </template>
          </el-table-column>
          <el-table-column label="差异" width="100">
            <template #default="{ row }">
              <el-tag :type="diffType(row.diff)" size="small">
                {{ row.diff >= 0 ? '+' : '' }}¥{{ row.diff.toFixed(2) }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column label="备注">
            <template #default="{ row }">
              <el-input v-model="row.remark" placeholder="差异说明" size="small" />
            </template>
          </el-table-column>
        </el-table>

        <el-form style="margin-top:16px">
          <el-form-item label="交班备注">
            <el-input
              v-model="reconRemark"
              type="textarea"
              :rows="2"
              placeholder="填写本班次备注事项（可选）"
            />
          </el-form-item>
        </el-form>

        <el-button type="primary" :icon="DocumentChecked" size="large" @click="submitShift">
          提交交班记录
        </el-button>
      </el-card>
    </template>

    <!-- shift history -->
    <el-card shadow="hover">
      <template #header>历史交班记录</template>
      <el-table :data="shiftRecords" border stripe>
        <el-table-column prop="staffName" label="员工" width="90" />
        <el-table-column prop="startTime" label="开班时间" min-width="140" />
        <el-table-column prop="endTime" label="交班时间" min-width="140" />
        <el-table-column prop="orderCount" label="订单数" width="80" />
        <el-table-column prop="revenue" label="营收（元）" width="100">
          <template #default="{ row }">
            <span style="color:#409eff;font-weight:600">¥{{ row.revenue.toFixed(2) }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="remark" label="备注" min-width="120">
          <template #default="{ row }">
            <span v-if="row.remark">{{ row.remark }}</span>
            <span v-else style="color:#c0c4cc">—</span>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>

<style scoped lang="scss">
.shift-banner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
}

.shift-info {
  display: flex;
  align-items: center;
}

.stat-card {
  text-align: center;
  padding: 8px 0;
}

.stat-num {
  font-size: 26px;
  font-weight: 700;
  line-height: 1.2;
}

.stat-label {
  font-size: 13px;
  color: #909399;
  margin-top: 4px;
}
</style>
