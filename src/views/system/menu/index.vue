<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { getMenuTreeApi, createMenuApi, updateMenuApi, deleteMenuApi } from '@/api/menu'
import type { MenuItem, MenuFormData } from '@/types/api'
import type { FormInstance, FormRules } from 'element-plus'
import { ElMessage, ElMessageBox } from 'element-plus'

const loading = ref(false)
const tableData = ref<MenuItem[]>([])

const dialogVisible = ref(false)
const dialogTitle = ref('新增菜单')
const formRef = ref<FormInstance>()
const formData = ref<MenuFormData>({
  parentId: 0,
  name: '',
  path: '',
  component: '',
  redirect: '',
  icon: '',
  title: '',
  hidden: false,
  keepAlive: false,
  type: 1,
  permission: '',
  sort: 0,
})

const rules: FormRules = {
  name: [{ required: true, message: '请输入菜单名称', trigger: 'blur' }],
  title: [{ required: true, message: '请输入菜单标题', trigger: 'blur' }],
}

async function fetchData() {
  loading.value = true
  try {
    tableData.value = await getMenuTreeApi()
  } finally {
    loading.value = false
  }
}

function handleAdd(parentId = 0) {
  dialogTitle.value = '新增菜单'
  resetForm()
  formData.value.parentId = parentId
  dialogVisible.value = true
}

function handleEdit(row: MenuItem) {
  dialogTitle.value = '编辑菜单'
  formData.value = {
    id: row.id,
    parentId: row.parentId,
    name: row.name,
    path: row.path,
    component: row.component,
    redirect: row.redirect,
    icon: row.icon,
    title: row.title,
    hidden: row.hidden,
    keepAlive: row.keepAlive,
    type: row.type,
    permission: row.permission,
    sort: row.sort,
  }
  dialogVisible.value = true
}

async function handleDelete(row: MenuItem) {
  try {
    await ElMessageBox.confirm(`确定删除菜单 "${row.title}" 吗？`, '提示', {
      type: 'warning',
    })
    await deleteMenuApi(row.id)
    ElMessage.success('删除成功')
    fetchData()
  } catch {
    // cancelled
  }
}

async function handleSubmit() {
  const valid = await formRef.value?.validate().catch(() => false)
  if (!valid) return

  try {
    if (formData.value.id) {
      await updateMenuApi(formData.value)
      ElMessage.success('更新成功')
    } else {
      await createMenuApi(formData.value)
      ElMessage.success('新增成功')
    }
    dialogVisible.value = false
    fetchData()
  } catch {
    // error handled
  }
}

function resetForm() {
  formData.value = {
    parentId: 0,
    name: '',
    path: '',
    component: '',
    redirect: '',
    icon: '',
    title: '',
    hidden: false,
    keepAlive: false,
    type: 1,
    permission: '',
    sort: 0,
  }
}

onMounted(() => {
  fetchData()
})
</script>

<template>
  <div>
    <el-card shadow="never" class="page-header">
      <div class="search-form">
        <el-button v-permission="'system:menu:create'" type="success" @click="handleAdd(0)">
          新增顶级菜单
        </el-button>
      </div>
    </el-card>

    <el-card shadow="never" style="margin-top: 12px">
      <el-table
        v-loading="loading"
        :data="tableData"
        row-key="id"
        border
        default-expand-all
        :tree-props="{ children: 'children' }"
      >
        <el-table-column prop="title" label="菜单标题" min-width="180" />
        <el-table-column prop="name" label="路由名称" width="120" />
        <el-table-column prop="path" label="路由路径" width="160" />
        <el-table-column prop="icon" label="图标" width="80" />
        <el-table-column prop="permission" label="权限标识" width="160" />
        <el-table-column prop="sort" label="排序" width="80" />
        <el-table-column prop="type" label="类型" width="80">
          <template #default="{ row }">
            <el-tag :type="row.type === 1 ? 'primary' : 'warning'">
              {{ row.type === 1 ? '菜单' : '按钮' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="240" fixed="right">
          <template #default="{ row }">
            <el-button
              v-permission="'system:menu:create'"
              type="primary"
              link
              @click="handleAdd(row.id)"
            >
              新增子菜单
            </el-button>
            <el-button
              v-permission="'system:menu:update'"
              type="primary"
              link
              @click="handleEdit(row)"
            >
              编辑
            </el-button>
            <el-button
              v-permission="'system:menu:delete'"
              type="danger"
              link
              @click="handleDelete(row)"
            >
              删除
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <el-dialog v-model="dialogVisible" :title="dialogTitle" width="600px" destroy-on-close>
      <el-form ref="formRef" :model="formData" :rules="rules" label-width="100px">
        <el-form-item label="菜单类型">
          <el-radio-group v-model="formData.type">
            <el-radio :value="1">菜单</el-radio>
            <el-radio :value="2">按钮</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="菜单标题" prop="title">
          <el-input v-model="formData.title" />
        </el-form-item>
        <el-form-item label="路由名称" prop="name">
          <el-input v-model="formData.name" />
        </el-form-item>
        <el-form-item v-if="formData.type === 1" label="路由路径">
          <el-input v-model="formData.path" />
        </el-form-item>
        <el-form-item v-if="formData.type === 1" label="组件路径">
          <el-input v-model="formData.component" placeholder="例如: views/system/user/index" />
        </el-form-item>
        <el-form-item v-if="formData.type === 1" label="重定向">
          <el-input v-model="formData.redirect" />
        </el-form-item>
        <el-form-item label="图标">
          <el-input v-model="formData.icon" />
        </el-form-item>
        <el-form-item label="权限标识">
          <el-input v-model="formData.permission" placeholder="例如: system:user:list" />
        </el-form-item>
        <el-form-item label="排序">
          <el-input-number v-model="formData.sort" :min="0" />
        </el-form-item>
        <el-form-item v-if="formData.type === 1" label="隐藏">
          <el-switch v-model="formData.hidden" />
        </el-form-item>
        <el-form-item v-if="formData.type === 1" label="缓存">
          <el-switch v-model="formData.keepAlive" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>
