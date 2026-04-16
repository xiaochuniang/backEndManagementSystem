<script setup lang="ts">
import { ref, reactive } from 'vue'
import { ElMessage } from 'element-plus'
import { Plus, Edit, Delete } from '@element-plus/icons-vue'
import { ElMessageBox } from 'element-plus'

interface PointsRecord {
  id: number
  phone: string
  change: number
  type: string
  remark: string
  time: string
}
interface ExchangeItem {
  id: number
  name: string
  points: number
  stock: number
}

const activeTab = ref('rules')

const pointsConfig = reactive({
  earnRate: 1,
  redeemRate: 100,
})

function saveRules() {
  ElMessage.success('积分规则保存成功')
}

const pointsRecords = ref<PointsRecord[]>([
  { id: 1, phone: '138****0001', change: +78, type: '消费获取', remark: '订单SO20260410001', time: '2026-04-10 15:23' },
  { id: 2, phone: '139****0002', change: -100, type: '积分兑换', remark: '兑换饮品券', time: '2026-04-08 10:00' },
  { id: 3, phone: '136****0003', change: +45, type: '消费获取', remark: '订单SO20260407003', time: '2026-04-07 16:00' },
])

const exchangeItems = ref<ExchangeItem[]>([
  { id: 1, name: '饮品折扣券', points: 100, stock: 50 },
  { id: 2, name: '蛋糕兑换券', points: 300, stock: 20 },
])

const exDialogVisible = ref(false)
const exDialogTitle = ref('添加兑换商品')
const exForm = reactive<Omit<ExchangeItem, 'id'> & { id?: number }>({ name: '', points: 0, stock: 0 })
const exFormRef = ref()
const exRules = {
  name: [{ required: true, message: '请输入商品名称', trigger: 'blur' }],
  points: [{ required: true, message: '请输入所需积分', trigger: 'blur' }],
}

function openAddEx() {
  exDialogTitle.value = '添加兑换商品'
  Object.assign(exForm, { id: undefined, name: '', points: 0, stock: 0 })
  exDialogVisible.value = true
}
function openEditEx(row: ExchangeItem) {
  exDialogTitle.value = '编辑兑换商品'
  Object.assign(exForm, { ...row })
  exDialogVisible.value = true
}
async function saveEx() {
  await exFormRef.value?.validate()
  if (exForm.id) {
    const idx = exchangeItems.value.findIndex((e) => e.id === exForm.id)
    if (idx !== -1) exchangeItems.value[idx] = { ...exForm, id: exForm.id }
  } else {
    exchangeItems.value.push({ ...exForm, id: Date.now() })
  }
  ElMessage.success('保存成功')
  exDialogVisible.value = false
}
async function deleteEx(row: ExchangeItem) {
  await ElMessageBox.confirm('确定删除此兑换商品？', '提示', { type: 'warning' })
  exchangeItems.value = exchangeItems.value.filter((e) => e.id !== row.id)
  ElMessage.success('删除成功')
}
</script>

<template>
  <div class="page">
    <el-tabs v-model="activeTab">
      <el-tab-pane label="积分规则" name="rules">
        <el-card shadow="never" style="max-width:500px">
          <el-form :model="pointsConfig" label-width="160px">
            <el-form-item label="消费1元获得积分">
              <el-input-number v-model="pointsConfig.earnRate" :min="0" :precision="1" />
              <span style="margin-left:8px;color:#909399">积分</span>
            </el-form-item>
            <el-form-item label="积分兑换比例">
              <el-input-number v-model="pointsConfig.redeemRate" :min="1" />
              <span style="margin-left:8px;color:#909399">积分 = 1元</span>
            </el-form-item>
            <el-form-item>
              <el-button type="primary" @click="saveRules">保存规则</el-button>
            </el-form-item>
          </el-form>
        </el-card>
      </el-tab-pane>

      <el-tab-pane label="积分记录" name="records">
        <el-table :data="pointsRecords" border stripe>
          <el-table-column label="会员手机" prop="phone" />
          <el-table-column label="积分变动" width="100">
            <template #default="{ row }">
              <span :style="{color: row.change>0?'#67c23a':'#f56c6c',fontWeight:600}">
                {{ row.change > 0 ? '+' : '' }}{{ row.change }}
              </span>
            </template>
          </el-table-column>
          <el-table-column label="类型" prop="type" width="100" />
          <el-table-column label="说明" prop="remark" />
          <el-table-column label="时间" prop="time" />
        </el-table>
      </el-tab-pane>

      <el-tab-pane label="兑换商品" name="exchange">
        <div style="margin-bottom:12px">
          <el-button type="primary" :icon="Plus" @click="openAddEx">添加兑换商品</el-button>
        </div>
        <el-table :data="exchangeItems" border stripe>
          <el-table-column label="商品名称" prop="name" />
          <el-table-column label="所需积分" prop="points" width="100" />
          <el-table-column label="库存" prop="stock" width="80" />
          <el-table-column label="操作" width="140" fixed="right">
            <template #default="{ row }">
              <el-button size="small" :icon="Edit" @click="openEditEx(row)">编辑</el-button>
              <el-button size="small" type="danger" :icon="Delete" @click="deleteEx(row)">删除</el-button>
            </template>
          </el-table-column>
        </el-table>
      </el-tab-pane>
    </el-tabs>

    <el-dialog v-model="exDialogVisible" :title="exDialogTitle" width="420px">
      <el-form ref="exFormRef" :model="exForm" :rules="exRules" label-width="90px">
        <el-form-item label="商品名称" prop="name">
          <el-input v-model="exForm.name" />
        </el-form-item>
        <el-form-item label="所需积分" prop="points">
          <el-input-number v-model="exForm.points" :min="0" style="width:100%" />
        </el-form-item>
        <el-form-item label="库存">
          <el-input-number v-model="exForm.stock" :min="0" style="width:100%" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="exDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="saveEx">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>
