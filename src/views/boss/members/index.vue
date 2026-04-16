<script setup lang="ts">
import { ref, reactive } from 'vue'
import { View, Plus, Edit } from '@element-plus/icons-vue'
import { ElMessage } from 'element-plus'

interface Member {
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
interface PointsRecord {
  change: number
  type: string
  remark: string
  time: string
}
interface Level {
  id: number
  name: string
  discount: number
  upgradeCondition: string
}

const members = ref<Member[]>([
  { id: 1, phone: '138****0001', level: '银卡', balance: 328.5, points: 1680, registerTime: '2025-12-01 10:00' },
  { id: 2, phone: '139****0002', level: '金卡', balance: 1250, points: 5200, registerTime: '2025-10-15 14:30' },
  { id: 3, phone: '136****0003', level: '普通', balance: 50, points: 200, registerTime: '2026-01-20 09:00' },
])

const levels = ref<Level[]>([
  { id: 1, name: '普通', discount: 1.0, upgradeCondition: '注册即享' },
  { id: 2, name: '银卡', discount: 0.95, upgradeCondition: '累计消费满500元' },
  { id: 3, name: '金卡', discount: 0.88, upgradeCondition: '累计消费满2000元' },
])

const activeTab = ref('list')

// Member detail
const drawerVisible = ref(false)
const currentMember = ref<Member | null>(null)
const detailTab = ref('consume')
const consumeRecords: ConsumeRecord[] = [
  { orderNo: 'SO20260410001', amount: 78, time: '2026-04-10 15:23' },
  { orderNo: 'SO20260408002', amount: 120, time: '2026-04-08 11:00' },
]
const depositRecords: DepositRecord[] = [
  { amount: 300, gift: 30, time: '2026-03-20 10:00' },
]
const pointsRecords: PointsRecord[] = [
  { change: +78, type: '消费获取', remark: '订单 SO20260410001', time: '2026-04-10 15:23' },
  { change: -100, type: '积分兑换', remark: '兑换饮品券', time: '2026-04-05 09:00' },
]

function viewMember(row: Member) {
  currentMember.value = row
  detailTab.value = 'consume'
  drawerVisible.value = true
}

// Level dialog
const levelDialogVisible = ref(false)
const levelDialogTitle = ref('添加等级')
const levelForm = reactive<Omit<Level, 'id'> & { id?: number }>({ name: '', discount: 1.0, upgradeCondition: '' })
const levelFormRef = ref()

function openAddLevel() {
  levelDialogTitle.value = '添加等级'
  Object.assign(levelForm, { id: undefined, name: '', discount: 1.0, upgradeCondition: '' })
  levelDialogVisible.value = true
}
function openEditLevel(row: Level) {
  levelDialogTitle.value = '编辑等级'
  Object.assign(levelForm, { ...row })
  levelDialogVisible.value = true
}
async function saveLevel() {
  await levelFormRef.value?.validate()
  if (levelForm.id) {
    const idx = levels.value.findIndex((l) => l.id === levelForm.id)
    if (idx !== -1) levels.value[idx] = { ...levelForm, id: levelForm.id }
  } else {
    levels.value.push({ ...levelForm, id: Date.now() })
  }
  ElMessage.success('保存成功')
  levelDialogVisible.value = false
}
</script>

<template>
  <div class="page">
    <el-tabs v-model="activeTab">
      <el-tab-pane label="会员列表" name="list">
        <el-table :data="members" border stripe>
          <el-table-column label="手机号" prop="phone" min-width="130" />
          <el-table-column label="会员等级" prop="level" width="90" />
          <el-table-column label="余额(元)" width="100">
            <template #default="{ row }">￥{{ row.balance.toFixed(2) }}</template>
          </el-table-column>
          <el-table-column label="积分" prop="points" width="80" />
          <el-table-column label="注册时间" prop="registerTime" min-width="160" />
          <el-table-column label="操作" width="90" fixed="right">
            <template #default="{ row }">
              <el-button size="small" :icon="View" @click="viewMember(row)">详情</el-button>
            </template>
          </el-table-column>
        </el-table>
      </el-tab-pane>

      <el-tab-pane label="等级设置" name="levels">
        <div style="margin-bottom:12px">
          <el-button type="primary" :icon="Plus" @click="openAddLevel">添加等级</el-button>
        </div>
        <el-table :data="levels" border stripe>
          <el-table-column label="等级名称" prop="name" />
          <el-table-column label="折扣比例" width="100">
            <template #default="{ row }">{{ (row.discount * 10).toFixed(1) }}折</template>
          </el-table-column>
          <el-table-column label="升级条件" prop="upgradeCondition" />
          <el-table-column label="操作" width="90" fixed="right">
            <template #default="{ row }">
              <el-button size="small" :icon="Edit" @click="openEditLevel(row)">编辑</el-button>
            </template>
          </el-table-column>
        </el-table>
      </el-tab-pane>
    </el-tabs>

    <!-- Member detail drawer -->
    <el-drawer v-model="drawerVisible" title="会员详情" size="500px" direction="rtl">
      <template v-if="currentMember">
        <el-descriptions :column="2" border style="margin-bottom:16px">
          <el-descriptions-item label="手机号">{{ currentMember.phone }}</el-descriptions-item>
          <el-descriptions-item label="等级">{{ currentMember.level }}</el-descriptions-item>
          <el-descriptions-item label="余额">￥{{ currentMember.balance.toFixed(2) }}</el-descriptions-item>
          <el-descriptions-item label="积分">{{ currentMember.points }}</el-descriptions-item>
          <el-descriptions-item label="注册时间" :span="2">{{ currentMember.registerTime }}</el-descriptions-item>
        </el-descriptions>
        <el-tabs v-model="detailTab">
          <el-tab-pane label="消费记录" name="consume">
            <el-table :data="consumeRecords" size="small" border>
              <el-table-column label="订单号" prop="orderNo" />
              <el-table-column label="金额" width="80"><template #default="{ row }">￥{{ row.amount }}</template></el-table-column>
              <el-table-column label="时间" prop="time" />
            </el-table>
          </el-tab-pane>
          <el-tab-pane label="储值记录" name="deposit">
            <el-table :data="depositRecords" size="small" border>
              <el-table-column label="充值金额"><template #default="{ row }">￥{{ row.amount }}</template></el-table-column>
              <el-table-column label="赠送金额"><template #default="{ row }">￥{{ row.gift }}</template></el-table-column>
              <el-table-column label="时间" prop="time" />
            </el-table>
          </el-tab-pane>
          <el-tab-pane label="积分记录" name="points">
            <el-table :data="pointsRecords" size="small" border>
              <el-table-column label="变动" width="70"><template #default="{ row }"><span :style="{color: row.change>0?'#67c23a':'#f56c6c'}">{{ row.change>0?'+':'' }}{{ row.change }}</span></template></el-table-column>
              <el-table-column label="类型" prop="type" width="90" />
              <el-table-column label="说明" prop="remark" />
              <el-table-column label="时间" prop="time" />
            </el-table>
          </el-tab-pane>
        </el-tabs>
      </template>
    </el-drawer>

    <!-- Level Dialog -->
    <el-dialog v-model="levelDialogVisible" :title="levelDialogTitle" width="440px">
      <el-form ref="levelFormRef" :model="levelForm" label-width="90px">
        <el-form-item label="等级名称" prop="name" :rules="[{required:true,message:'请输入等级名称',trigger:'blur'}]">
          <el-input v-model="levelForm.name" />
        </el-form-item>
        <el-form-item label="折扣比例">
          <el-input-number v-model="levelForm.discount" :min="0.1" :max="1" :step="0.05" :precision="2" />
          <span style="margin-left:8px;color:#909399">（1.0=不打折，0.9=九折）</span>
        </el-form-item>
        <el-form-item label="升级条件">
          <el-input v-model="levelForm.upgradeCondition" placeholder="如：累计消费满500元" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="levelDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="saveLevel">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>
