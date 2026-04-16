<script setup lang="ts">
import { ref, reactive } from 'vue'
import { Plus, Edit, Delete } from '@element-plus/icons-vue'
import { ElMessage, ElMessageBox } from 'element-plus'

interface DepositRule {
  id: number
  amount: number
  gift: number
  remark: string
}
interface DepositRecord {
  id: number
  phone: string
  amount: number
  gift: number
  time: string
  operator: string
}

const activeTab = ref('rules')

const rules = ref<DepositRule[]>([
  { id: 1, amount: 100, gift: 10, remark: '充100赠10' },
  { id: 2, amount: 300, gift: 50, remark: '充300赠50' },
  { id: 3, amount: 500, gift: 100, remark: '充500赠100' },
])

const records = ref<DepositRecord[]>([
  { id: 1, phone: '138****0001', amount: 300, gift: 50, time: '2026-04-15 14:20', operator: '张员工' },
  { id: 2, phone: '139****0002', amount: 500, gift: 100, time: '2026-04-14 10:00', operator: '李员工' },
])

const dialogVisible = ref(false)
const dialogTitle = ref('添加规则')
const ruleForm = reactive<Omit<DepositRule, 'id'> & { id?: number }>({ amount: 0, gift: 0, remark: '' })
const ruleFormRef = ref()
const ruleRules = {
  amount: [{ required: true, message: '请输入充值金额', trigger: 'blur' }],
  gift: [{ required: true, message: '请输入赠送金额', trigger: 'blur' }],
}

function openAdd() {
  dialogTitle.value = '添加规则'
  Object.assign(ruleForm, { id: undefined, amount: 0, gift: 0, remark: '' })
  dialogVisible.value = true
}
function openEdit(row: DepositRule) {
  dialogTitle.value = '编辑规则'
  Object.assign(ruleForm, { ...row })
  dialogVisible.value = true
}
async function saveRule() {
  await ruleFormRef.value?.validate()
  if (ruleForm.id) {
    const idx = rules.value.findIndex((r) => r.id === ruleForm.id)
    if (idx !== -1) rules.value[idx] = { ...ruleForm, id: ruleForm.id }
  } else {
    rules.value.push({ ...ruleForm, id: Date.now() })
  }
  ElMessage.success('保存成功')
  dialogVisible.value = false
}
async function deleteRule(row: DepositRule) {
  await ElMessageBox.confirm('确定删除此规则？', '提示', { type: 'warning' })
  rules.value = rules.value.filter((r) => r.id !== row.id)
  ElMessage.success('删除成功')
}
</script>

<template>
  <div class="page">
    <el-tabs v-model="activeTab">
      <el-tab-pane label="储值规则" name="rules">
        <div style="margin-bottom:12px">
          <el-button type="primary" :icon="Plus" @click="openAdd">添加规则</el-button>
        </div>
        <el-table :data="rules" border stripe>
          <el-table-column label="充值金额(元)">
            <template #default="{ row }">￥{{ row.amount }}</template>
          </el-table-column>
          <el-table-column label="赠送金额(元)">
            <template #default="{ row }">￥{{ row.gift }}</template>
          </el-table-column>
          <el-table-column label="备注" prop="remark" />
          <el-table-column label="操作" width="140" fixed="right">
            <template #default="{ row }">
              <el-button size="small" :icon="Edit" @click="openEdit(row)">编辑</el-button>
              <el-button size="small" type="danger" :icon="Delete" @click="deleteRule(row)">删除</el-button>
            </template>
          </el-table-column>
        </el-table>
      </el-tab-pane>

      <el-tab-pane label="储值记录" name="records">
        <el-table :data="records" border stripe>
          <el-table-column label="会员手机" prop="phone" />
          <el-table-column label="充值金额(元)">
            <template #default="{ row }">￥{{ row.amount }}</template>
          </el-table-column>
          <el-table-column label="赠送金额(元)">
            <template #default="{ row }">￥{{ row.gift }}</template>
          </el-table-column>
          <el-table-column label="充值时间" prop="time" />
          <el-table-column label="操作员" prop="operator" />
        </el-table>
      </el-tab-pane>
    </el-tabs>

    <el-dialog v-model="dialogVisible" :title="dialogTitle" width="440px">
      <el-form ref="ruleFormRef" :model="ruleForm" :rules="ruleRules" label-width="100px">
        <el-form-item label="充值金额(元)" prop="amount">
          <el-input-number v-model="ruleForm.amount" :min="0" style="width:100%" />
        </el-form-item>
        <el-form-item label="赠送金额(元)" prop="gift">
          <el-input-number v-model="ruleForm.gift" :min="0" style="width:100%" />
        </el-form-item>
        <el-form-item label="备注">
          <el-input v-model="ruleForm.remark" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="saveRule">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>
