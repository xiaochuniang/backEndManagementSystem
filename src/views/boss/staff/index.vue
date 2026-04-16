<script setup lang="ts">
import { ref, reactive } from 'vue'
import { Plus, Edit, Delete } from '@element-plus/icons-vue'
import { ElMessage, ElMessageBox } from 'element-plus'

interface Staff {
  id: number
  account: string
  name: string
  phone: string
  role: string
  permissions: string[]
  status: number
}

const permissionOptions = ['收银', '会员管理', '订单查询', '报表查看', '员工管理']

const staffList = ref<Staff[]>([
  { id: 1, account: 'zhang001', name: '张员工', phone: '13811112222', role: '收银员', permissions: ['收银', '会员管理'], status: 1 },
  { id: 2, account: 'li002', name: '李员工', phone: '13833334444', role: '收银员', permissions: ['收银', '订单查询'], status: 1 },
  { id: 3, account: 'wang003', name: '王员工', phone: '13855556666', role: '店长', permissions: ['收银', '会员管理', '订单查询', '报表查看', '员工管理'], status: 0 },
])

const dialogVisible = ref(false)
const dialogTitle = ref('添加员工')
const staffForm = reactive<Omit<Staff, 'id'> & { id?: number; confirmPassword: string }>({
  account: '', name: '', phone: '', role: '', permissions: [], status: 1, confirmPassword: '',
})
const staffFormRef = ref()
const staffRules = {
  account: [{ required: true, message: '请输入账号', trigger: 'blur' }],
  name: [{ required: true, message: '请输入姓名', trigger: 'blur' }],
  phone: [{ required: true, message: '请输入手机号', trigger: 'blur' }],
  role: [{ required: true, message: '请选择岗位', trigger: 'change' }],
}

function openAdd() {
  dialogTitle.value = '添加员工'
  Object.assign(staffForm, { id: undefined, account: '', name: '', phone: '', role: '', permissions: [], status: 1, confirmPassword: '' })
  dialogVisible.value = true
}
function openEdit(row: Staff) {
  dialogTitle.value = '编辑员工'
  Object.assign(staffForm, { ...row, confirmPassword: '' })
  dialogVisible.value = true
}
async function saveStaff() {
  await staffFormRef.value?.validate()
  if (staffForm.id) {
    const idx = staffList.value.findIndex((s) => s.id === staffForm.id)
    if (idx !== -1) staffList.value[idx] = { account: staffForm.account, name: staffForm.name, phone: staffForm.phone, role: staffForm.role, permissions: staffForm.permissions, status: staffForm.status, id: staffForm.id }
    ElMessage.success('更新成功')
  } else {
    staffList.value.push({ account: staffForm.account, name: staffForm.name, phone: staffForm.phone, role: staffForm.role, permissions: staffForm.permissions, status: staffForm.status, id: Date.now() })
    ElMessage.success('添加成功')
  }
  dialogVisible.value = false
}
async function deleteStaff(row: Staff) {
  await ElMessageBox.confirm(`确定删除员工「${row.name}」？`, '提示', { type: 'warning' })
  staffList.value = staffList.value.filter((s) => s.id !== row.id)
  ElMessage.success('删除成功')
}
</script>

<template>
  <div class="page">
    <div style="margin-bottom:12px;display:flex;gap:12px;align-items:center">
      <el-button type="primary" :icon="Plus" @click="openAdd">添加员工</el-button>
    </div>
    <el-table :data="staffList" border stripe>
      <el-table-column label="姓名" prop="name" width="90" />
      <el-table-column label="手机号" prop="phone" min-width="120" />
      <el-table-column label="岗位" prop="role" width="90" />
      <el-table-column label="权限" min-width="180">
        <template #default="{ row }">
          <el-tag v-for="p in row.permissions" :key="p" size="small" style="margin:2px">{{ p }}</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="状态" width="80">
        <template #default="{ row }">
          <el-tag :type="row.status ? 'success' : 'info'">{{ row.status ? '在职' : '离职' }}</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="操作" width="140" fixed="right">
        <template #default="{ row }">
          <el-button size="small" :icon="Edit" @click="openEdit(row)">编辑</el-button>
          <el-button size="small" type="danger" :icon="Delete" @click="deleteStaff(row)">删除</el-button>
        </template>
      </el-table-column>
    </el-table>

    <el-dialog v-model="dialogVisible" :title="dialogTitle" width="520px">
      <el-form ref="staffFormRef" :model="staffForm" :rules="staffRules" label-width="90px">
        <el-form-item label="账号" prop="account">
          <el-input v-model="staffForm.account" placeholder="登录账号" />
        </el-form-item>
        <el-form-item label="姓名" prop="name">
          <el-input v-model="staffForm.name" />
        </el-form-item>
        <el-form-item label="手机号" prop="phone">
          <el-input v-model="staffForm.phone" />
        </el-form-item>
        <el-form-item label="登录密码">
          <el-input v-model="staffForm.confirmPassword" type="password" placeholder="不填则不修改密码" show-password />
        </el-form-item>
        <el-form-item label="岗位" prop="role">
          <el-select v-model="staffForm.role" placeholder="请选择岗位" style="width:100%">
            <el-option label="收银员" value="收银员" />
            <el-option label="店长" value="店长" />
            <el-option label="运营" value="运营" />
          </el-select>
        </el-form-item>
        <el-form-item label="权限">
          <el-checkbox-group v-model="staffForm.permissions">
            <el-checkbox v-for="p in permissionOptions" :key="p" :label="p">{{ p }}</el-checkbox>
          </el-checkbox-group>
        </el-form-item>
        <el-form-item label="状态">
          <el-radio-group v-model="staffForm.status">
            <el-radio :value="1">在职</el-radio>
            <el-radio :value="0">离职</el-radio>
          </el-radio-group>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="saveStaff">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>
