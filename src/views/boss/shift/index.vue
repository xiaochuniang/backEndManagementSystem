<script setup lang="ts">
import { ref, reactive } from 'vue'
import { Search } from '@element-plus/icons-vue'

interface ShiftRecord {
  id: number
  staffName: string
  startTime: string
  endTime: string
  revenue: number
  orderCount: number
  remark: string
}

const activeTab = ref('records')

const shiftRecords = ref<ShiftRecord[]>([
  {
    id: 1,
    staffName: '张员工',
    startTime: '2026-04-16 08:00',
    endTime: '2026-04-16 14:00',
    revenue: 3640,
    orderCount: 62,
    remark: '',
  },
  {
    id: 2,
    staffName: '李员工',
    startTime: '2026-04-16 14:00',
    endTime: '2026-04-16 20:00',
    revenue: 4980,
    orderCount: 81,
    remark: '无异常',
  },
])

const reconFilter = reactive({ date: '' })
const reconSummary = reactive({
  systemRevenue: 8620,
  actualRevenue: 8600,
  diff: -20,
})

const reconDetails = ref([
  { payMethod: '微信支付', systemAmount: 4200, actualAmount: 4200, diff: 0 },
  { payMethod: '支付宝', systemAmount: 2100, actualAmount: 2100, diff: 0 },
  { payMethod: '现金', systemAmount: 1200, actualAmount: 1180, diff: -20, remark: '找零误差' },
  { payMethod: '会员余额', systemAmount: 1120, actualAmount: 1120, diff: 0 },
])
</script>

<template>
  <div class="page">
    <el-tabs v-model="activeTab">
      <el-tab-pane label="交班记录" name="records">
        <el-table :data="shiftRecords" border stripe>
          <el-table-column label="员工" prop="staffName" width="90" />
          <el-table-column label="开始时间" prop="startTime" />
          <el-table-column label="结束时间" prop="endTime" />
          <el-table-column label="营收(元)" width="100">
            <template #default="{ row }">￥{{ row.revenue.toLocaleString() }}</template>
          </el-table-column>
          <el-table-column label="单数" prop="orderCount" width="70" />
          <el-table-column label="备注" prop="remark" />
        </el-table>
      </el-tab-pane>

      <el-tab-pane label="营收对账" name="recon">
        <el-form inline style="margin-bottom: 12px">
          <el-form-item label="日期">
            <el-date-picker
              v-model="reconFilter.date"
              type="date"
              placeholder="选择日期"
              style="width: 160px"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" :icon="Search">查询</el-button>
          </el-form-item>
        </el-form>

        <el-row :gutter="16" style="margin-bottom: 16px">
          <el-col :span="8">
            <el-card shadow="hover" class="recon-card">
              <div class="recon-label">系统实收</div>
              <div class="recon-value blue">
                ￥{{ reconSummary.systemRevenue.toLocaleString() }}
              </div>
            </el-card>
          </el-col>
          <el-col :span="8">
            <el-card shadow="hover" class="recon-card">
              <div class="recon-label">实际营收</div>
              <div class="recon-value green">
                ￥{{ reconSummary.actualRevenue.toLocaleString() }}
              </div>
            </el-card>
          </el-col>
          <el-col :span="8">
            <el-card shadow="hover" class="recon-card">
              <div class="recon-label">差异金额</div>
              <div class="recon-value" :class="reconSummary.diff === 0 ? 'green' : 'red'">
                ￥{{ reconSummary.diff }}
              </div>
            </el-card>
          </el-col>
        </el-row>

        <el-table :data="reconDetails" border>
          <el-table-column label="支付方式" prop="payMethod" />
          <el-table-column label="系统金额(元)">
            <template #default="{ row }">￥{{ row.systemAmount }}</template>
          </el-table-column>
          <el-table-column label="实际金额(元)">
            <template #default="{ row }">￥{{ row.actualAmount }}</template>
          </el-table-column>
          <el-table-column label="差异" width="90">
            <template #default="{ row }">
              <span :style="{ color: row.diff === 0 ? '#67c23a' : '#f56c6c' }">{{ row.diff }}</span>
            </template>
          </el-table-column>
          <el-table-column label="说明" prop="remark" />
        </el-table>
      </el-tab-pane>
    </el-tabs>
  </div>
</template>

<style scoped>
.recon-card {
  text-align: center;
  padding: 8px 0;
}
.recon-label {
  font-size: 13px;
  color: #909399;
  margin-bottom: 6px;
}
.recon-value {
  font-size: 24px;
  font-weight: 700;
}
.blue {
  color: #409eff;
}
.green {
  color: #67c23a;
}
.red {
  color: #f56c6c;
}
</style>
