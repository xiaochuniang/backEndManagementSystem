<script setup lang="ts">
import { ref, reactive, computed } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Edit, Delete, Search } from '@element-plus/icons-vue'

interface Category {
  id: number
  name: string
  sort: number
  remark: string
}
interface Product {
  id: number
  name: string
  categoryId: number
  price: number
  stock: number
  barcode: string
  imageUrl: string
}

const categories = ref<Category[]>([
  { id: 1, name: '饮品', sort: 1, remark: '各类饮品' },
  { id: 2, name: '小吃', sort: 2, remark: '各类小吃' },
  { id: 3, name: '套餐', sort: 3, remark: '组合套餐' },
])

const products = ref<Product[]>([
  { id: 1, name: '珍珠奶茶', categoryId: 1, price: 18, stock: 100, barcode: '6901001000001', imageUrl: '' },
  { id: 2, name: '芝士蛋糕', categoryId: 2, price: 28, stock: 50, barcode: '6901001000002', imageUrl: '' },
  { id: 3, name: '下午茶套餐', categoryId: 3, price: 45, stock: 30, barcode: '6901001000003', imageUrl: '' },
  { id: 4, name: '美式咖啡', categoryId: 1, price: 22, stock: 80, barcode: '6901001000004', imageUrl: '' },
])

const activeTab = ref('products')
const searchName = ref('')
const filterCategory = ref<number | ''>('')

const filteredProducts = computed(() => {
  return products.value.filter((p) => {
    const matchName = !searchName.value || p.name.includes(searchName.value)
    const matchCat = !filterCategory.value || p.categoryId === filterCategory.value
    return matchName && matchCat
  })
})

function getCategoryName(id: number) {
  return categories.value.find((c) => c.id === id)?.name || '未知'
}

// Product dialog
const prodDialogVisible = ref(false)
const prodDialogTitle = ref('添加商品')
const prodForm = reactive<Omit<Product, 'id'> & { id?: number }>({
  name: '',
  categoryId: categories.value[0]?.id ?? 1,
  price: 0,
  stock: 0,
  barcode: '',
  imageUrl: '',
})
const prodFormRef = ref()
const prodRules = {
  name: [{ required: true, message: '请输入商品名称', trigger: 'blur' }],
  categoryId: [{ required: true, message: '请选择分类', trigger: 'change' }],
  price: [{ required: true, message: '请输入单价', trigger: 'blur' }],
}

function openAddProduct() {
  prodDialogTitle.value = '添加商品'
  Object.assign(prodForm, { id: undefined, name: '', categoryId: categories.value[0]?.id ?? 1, price: 0, stock: 0, barcode: '', imageUrl: '' })
  prodDialogVisible.value = true
}
function openEditProduct(row: Product) {
  prodDialogTitle.value = '编辑商品'
  Object.assign(prodForm, { ...row })
  prodDialogVisible.value = true
}
async function saveProd() {
  await prodFormRef.value?.validate()
  if (prodForm.id) {
    const idx = products.value.findIndex((p) => p.id === prodForm.id)
    if (idx !== -1) products.value[idx] = { ...prodForm, id: prodForm.id }
    ElMessage.success('更新成功')
  } else {
    products.value.push({ ...prodForm, id: Date.now() })
    ElMessage.success('添加成功')
  }
  prodDialogVisible.value = false
}
async function deleteProd(row: Product) {
  await ElMessageBox.confirm(`确定删除「${row.name}」？`, '提示', { type: 'warning' })
  products.value = products.value.filter((p) => p.id !== row.id)
  ElMessage.success('删除成功')
}

// Category dialog
const catDialogVisible = ref(false)
const catDialogTitle = ref('添加分类')
const catForm = reactive<Omit<Category, 'id'> & { id?: number }>({ name: '', sort: 1, remark: '' })
const catFormRef = ref()
const catRules = { name: [{ required: true, message: '请输入分类名称', trigger: 'blur' }] }

function openAddCat() {
  catDialogTitle.value = '添加分类'
  Object.assign(catForm, { id: undefined, name: '', sort: categories.value.length + 1, remark: '' })
  catDialogVisible.value = true
}
function openEditCat(row: Category) {
  catDialogTitle.value = '编辑分类'
  Object.assign(catForm, { ...row })
  catDialogVisible.value = true
}
async function saveCat() {
  await catFormRef.value?.validate()
  if (catForm.id) {
    const idx = categories.value.findIndex((c) => c.id === catForm.id)
    if (idx !== -1) categories.value[idx] = { ...catForm, id: catForm.id }
    ElMessage.success('更新成功')
  } else {
    categories.value.push({ ...catForm, id: Date.now() })
    ElMessage.success('添加成功')
  }
  catDialogVisible.value = false
}
async function deleteCat(row: Category) {
  await ElMessageBox.confirm(`确定删除「${row.name}」分类？`, '提示', { type: 'warning' })
  categories.value = categories.value.filter((c) => c.id !== row.id)
  ElMessage.success('删除成功')
}
</script>

<template>
  <div class="page">
    <el-tabs v-model="activeTab">
      <el-tab-pane label="商品列表" name="products">
        <div class="toolbar">
          <el-form inline>
            <el-form-item label="商品名称">
              <el-input v-model="searchName" placeholder="请输入商品名称" clearable :prefix-icon="Search" />
            </el-form-item>
            <el-form-item label="分类">
              <el-select v-model="filterCategory" placeholder="全部" clearable style="width:120px">
                <el-option v-for="c in categories" :key="c.id" :label="c.name" :value="c.id" />
              </el-select>
            </el-form-item>
            <el-form-item>
              <el-button type="primary" :icon="Plus" @click="openAddProduct">添加商品</el-button>
            </el-form-item>
          </el-form>
        </div>
        <el-table :data="filteredProducts" border stripe>
          <el-table-column label="商品名称" prop="name" min-width="120" />
          <el-table-column label="分类" min-width="80">
            <template #default="{ row }">{{ getCategoryName(row.categoryId) }}</template>
          </el-table-column>
          <el-table-column label="单价(元)" prop="price" width="100">
            <template #default="{ row }">￥{{ row.price.toFixed(2) }}</template>
          </el-table-column>
          <el-table-column label="库存" prop="stock" width="80" />
          <el-table-column label="条码" prop="barcode" min-width="140" />
          <el-table-column label="操作" width="140" fixed="right">
            <template #default="{ row }">
              <el-button size="small" :icon="Edit" @click="openEditProduct(row)">编辑</el-button>
              <el-button size="small" type="danger" :icon="Delete" @click="deleteProd(row)">删除</el-button>
            </template>
          </el-table-column>
        </el-table>
      </el-tab-pane>

      <el-tab-pane label="分类管理" name="categories">
        <div class="toolbar">
          <el-button type="primary" :icon="Plus" @click="openAddCat">添加分类</el-button>
        </div>
        <el-table :data="categories" border stripe>
          <el-table-column label="分类名称" prop="name" />
          <el-table-column label="排序" prop="sort" width="80" />
          <el-table-column label="备注" prop="remark" />
          <el-table-column label="操作" width="140" fixed="right">
            <template #default="{ row }">
              <el-button size="small" :icon="Edit" @click="openEditCat(row)">编辑</el-button>
              <el-button size="small" type="danger" :icon="Delete" @click="deleteCat(row)">删除</el-button>
            </template>
          </el-table-column>
        </el-table>
      </el-tab-pane>
    </el-tabs>

    <!-- Product Dialog -->
    <el-dialog v-model="prodDialogVisible" :title="prodDialogTitle" width="520px">
      <el-form ref="prodFormRef" :model="prodForm" :rules="prodRules" label-width="90px">
        <el-form-item label="商品名称" prop="name">
          <el-input v-model="prodForm.name" placeholder="请输入商品名称" />
        </el-form-item>
        <el-form-item label="分类" prop="categoryId">
          <el-select v-model="prodForm.categoryId" placeholder="请选择分类" style="width:100%">
            <el-option v-for="c in categories" :key="c.id" :label="c.name" :value="c.id" />
          </el-select>
        </el-form-item>
        <el-form-item label="单价(元)" prop="price">
          <el-input-number v-model="prodForm.price" :min="0" :precision="2" style="width:100%" />
        </el-form-item>
        <el-form-item label="库存">
          <el-input-number v-model="prodForm.stock" :min="0" style="width:100%" />
        </el-form-item>
        <el-form-item label="条码">
          <el-input v-model="prodForm.barcode" placeholder="请输入商品条码" />
        </el-form-item>
        <el-form-item label="商品图片">
          <el-upload action="#" list-type="picture-card" :auto-upload="false" :limit="1">
            <el-icon><Plus /></el-icon>
          </el-upload>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="prodDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="saveProd">确定</el-button>
      </template>
    </el-dialog>

    <!-- Category Dialog -->
    <el-dialog v-model="catDialogVisible" :title="catDialogTitle" width="420px">
      <el-form ref="catFormRef" :model="catForm" :rules="catRules" label-width="90px">
        <el-form-item label="分类名称" prop="name">
          <el-input v-model="catForm.name" placeholder="请输入分类名称" />
        </el-form-item>
        <el-form-item label="排序">
          <el-input-number v-model="catForm.sort" :min="1" style="width:100%" />
        </el-form-item>
        <el-form-item label="备注">
          <el-input v-model="catForm.remark" type="textarea" :rows="2" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="catDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="saveCat">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<style scoped>
.toolbar { margin-bottom: 12px; }
</style>
