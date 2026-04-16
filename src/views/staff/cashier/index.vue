<script setup lang="ts">
import { ref, reactive, computed } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, Plus, Minus, Delete, User, ShoppingCart } from '@element-plus/icons-vue'

interface Category {
  id: number
  name: string
}
interface Product {
  id: number
  name: string
  categoryId: number
  price: number
  stock: number
}
interface CartItem {
  product: Product
  qty: number
}

// ── mock data ──────────────────────────────────────────────
const categories = ref<Category[]>([
  { id: 0, name: '全部' },
  { id: 1, name: '饮品' },
  { id: 2, name: '小吃' },
  { id: 3, name: '套餐' },
])

const allProducts = ref<Product[]>([
  { id: 1, name: '珍珠奶茶', categoryId: 1, price: 18, stock: 100 },
  { id: 2, name: '芝士蛋糕', categoryId: 2, price: 28, stock: 50 },
  { id: 3, name: '下午茶套餐', categoryId: 3, price: 45, stock: 30 },
  { id: 4, name: '美式咖啡', categoryId: 1, price: 22, stock: 80 },
  { id: 5, name: '抹茶拿铁', categoryId: 1, price: 25, stock: 60 },
  { id: 6, name: '薯条', categoryId: 2, price: 12, stock: 200 },
  { id: 7, name: '鸡翅', categoryId: 2, price: 20, stock: 40 },
  { id: 8, name: '双人套餐', categoryId: 3, price: 68, stock: 20 },
  { id: 9, name: '焦糖玛奇朵', categoryId: 1, price: 30, stock: 70 },
  { id: 10, name: '草莓蛋糕', categoryId: 2, price: 35, stock: 25 },
])

// ── state ──────────────────────────────────────────────────
const searchKeyword = ref('')
const activeCategoryId = ref(0)
const cart = ref<CartItem[]>([])

// member verification
const memberDialogVisible = ref(false)
const memberPhone = ref('')
const memberInfo = reactive({
  found: false,
  phone: '',
  level: '',
  balance: 0,
  points: 0,
})
const useBalance = ref(false)

// checkout dialog
const checkoutVisible = ref(false)
const payMethod = ref<'wechat' | 'alipay' | 'cash' | 'balance'>('wechat')

// ── computed ───────────────────────────────────────────────
const filteredProducts = computed(() => {
  let list = allProducts.value
  if (activeCategoryId.value !== 0) {
    list = list.filter((p) => p.categoryId === activeCategoryId.value)
  }
  if (searchKeyword.value) {
    list = list.filter((p) => p.name.includes(searchKeyword.value))
  }
  return list
})

const totalAmount = computed(() =>
  cart.value.reduce((sum, item) => sum + item.product.price * item.qty, 0),
)

const cartCount = computed(() => cart.value.reduce((sum, item) => sum + item.qty, 0))

const payableAmount = computed(() => {
  if (useBalance.value && memberInfo.found) {
    return Math.max(0, totalAmount.value - memberInfo.balance)
  }
  return totalAmount.value
})

// ── methods ────────────────────────────────────────────────
function addToCart(product: Product) {
  const existing = cart.value.find((item) => item.product.id === product.id)
  if (existing) {
    existing.qty++
  } else {
    cart.value.push({ product, qty: 1 })
  }
}

function decreaseQty(item: CartItem) {
  if (item.qty > 1) {
    item.qty--
  } else {
    removeFromCart(item)
  }
}

function removeFromCart(item: CartItem) {
  cart.value = cart.value.filter((c) => c !== item)
}

function clearCart() {
  ElMessageBox.confirm('确认清空购物车？', '提示', { type: 'warning' }).then(() => {
    cart.value = []
  })
}

// member lookup mock
function lookupMember() {
  if (!memberPhone.value) {
    ElMessage.warning('请输入手机号')
    return
  }
  const mockMembers: Record<string, typeof memberInfo> = {
    '138****0001': { found: true, phone: '138****0001', level: '银卡', balance: 328.5, points: 1680 },
    '139****0002': { found: true, phone: '139****0002', level: '金卡', balance: 1250, points: 5200 },
    '13811110001': { found: true, phone: '13811110001', level: '银卡', balance: 328.5, points: 1680 },
  }
  const found = mockMembers[memberPhone.value]
  if (found) {
    Object.assign(memberInfo, found)
  } else {
    memberInfo.found = false
    ElMessage.warning('未查询到该会员')
  }
}

function openMemberDialog() {
  memberPhone.value = ''
  memberInfo.found = false
  useBalance.value = false
  memberDialogVisible.value = true
}

function confirmMember() {
  memberDialogVisible.value = false
  if (memberInfo.found && useBalance.value) {
    payMethod.value = 'balance'
  }
}

function openCheckout() {
  if (cart.value.length === 0) {
    ElMessage.warning('购物车为空，请先添加商品')
    return
  }
  checkoutVisible.value = true
}

function submitOrder() {
  checkoutVisible.value = false
  const orderNo = 'SO' + Date.now()
  ElMessage.success(`收银成功！订单号：${orderNo}，实收 ¥${payableAmount.value.toFixed(2)}`)
  cart.value = []
  memberInfo.found = false
  useBalance.value = false
  payMethod.value = 'wechat'
}

function getPayMethodLabel(m: string) {
  const map: Record<string, string> = {
    wechat: '微信支付',
    alipay: '支付宝',
    cash: '现金',
    balance: '会员余额',
  }
  return map[m] || m
}
</script>

<template>
  <div class="cashier-layout">
    <!-- left: product area -->
    <div class="product-panel">
      <!-- search -->
      <div class="search-bar">
        <el-input
          v-model="searchKeyword"
          placeholder="搜索商品名称"
          :prefix-icon="Search"
          clearable
        />
      </div>
      <!-- category tabs -->
      <div class="category-tabs">
        <span
          v-for="cat in categories"
          :key="cat.id"
          :class="['cat-tag', { active: activeCategoryId === cat.id }]"
          @click="activeCategoryId = cat.id"
        >{{ cat.name }}</span>
      </div>
      <!-- product grid -->
      <div class="product-grid">
        <div
          v-for="product in filteredProducts"
          :key="product.id"
          class="product-card"
          @click="addToCart(product)"
        >
          <div class="product-icon">🛒</div>
          <div class="product-name">{{ product.name }}</div>
          <div class="product-price">¥{{ product.price }}</div>
          <el-tag size="small" type="info">库存 {{ product.stock }}</el-tag>
        </div>
        <div v-if="filteredProducts.length === 0" class="empty-tip">暂无商品</div>
      </div>
    </div>

    <!-- right: cart area -->
    <div class="cart-panel">
      <div class="cart-header">
        <span><el-icon><ShoppingCart /></el-icon> 购物车（{{ cartCount }} 件）</span>
        <el-button v-if="cart.length > 0" link type="danger" :icon="Delete" size="small" @click="clearCart">清空</el-button>
      </div>

      <div class="cart-list">
        <div v-if="cart.length === 0" class="cart-empty">购物车为空，点击左侧商品加入</div>
        <div v-for="item in cart" :key="item.product.id" class="cart-item">
          <div class="cart-item-name">{{ item.product.name }}</div>
          <div class="cart-item-price">¥{{ item.product.price }}</div>
          <div class="cart-item-qty">
            <el-button :icon="Minus" circle size="small" @click="decreaseQty(item)" />
            <span class="qty-num">{{ item.qty }}</span>
            <el-button :icon="Plus" circle size="small" type="primary" @click="item.qty++" />
          </div>
          <div class="cart-item-subtotal">¥{{ (item.product.price * item.qty).toFixed(2) }}</div>
        </div>
      </div>

      <!-- member strip -->
      <div class="member-strip" @click="openMemberDialog">
        <el-icon><User /></el-icon>
        <span v-if="!memberInfo.found">点击核销会员</span>
        <span v-else>
          {{ memberInfo.level }} · 余额 ¥{{ memberInfo.balance }}
          <el-tag v-if="useBalance" size="small" type="success" style="margin-left:4px">余额支付</el-tag>
        </span>
      </div>

      <!-- total -->
      <div class="cart-total">
        <span>合计</span>
        <span class="total-price">¥{{ totalAmount.toFixed(2) }}</span>
      </div>

      <el-button type="primary" size="large" style="width:100%;margin-top:12px" @click="openCheckout">
        收银结账
      </el-button>
    </div>

    <!-- member dialog -->
    <el-dialog v-model="memberDialogVisible" title="会员核销" width="400px">
      <el-input
        v-model="memberPhone"
        placeholder="请输入会员手机号（如 13811110001）"
        :prefix-icon="User"
        style="margin-bottom:12px"
        @keyup.enter="lookupMember"
      />
      <el-button type="primary" @click="lookupMember">查 询</el-button>
      <div v-if="memberInfo.found" class="member-result">
        <el-descriptions :column="2" border size="small" style="margin-top:16px">
          <el-descriptions-item label="手机号">{{ memberInfo.phone }}</el-descriptions-item>
          <el-descriptions-item label="等级">{{ memberInfo.level }}</el-descriptions-item>
          <el-descriptions-item label="余额">¥{{ memberInfo.balance }}</el-descriptions-item>
          <el-descriptions-item label="积分">{{ memberInfo.points }}</el-descriptions-item>
        </el-descriptions>
        <el-checkbox v-model="useBalance" style="margin-top:12px">
          使用余额支付（余额 ¥{{ memberInfo.balance }}，需支付 ¥{{ payableAmount.toFixed(2) }}）
        </el-checkbox>
      </div>
      <template #footer>
        <el-button @click="memberDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="confirmMember">确认</el-button>
      </template>
    </el-dialog>

    <!-- checkout dialog -->
    <el-dialog v-model="checkoutVisible" title="收银结账" width="420px">
      <el-descriptions :column="1" border size="small">
        <el-descriptions-item label="商品总计">¥{{ totalAmount.toFixed(2) }}</el-descriptions-item>
        <el-descriptions-item v-if="memberInfo.found && useBalance" label="会员余额抵扣">
          -¥{{ Math.min(memberInfo.balance, totalAmount).toFixed(2) }}
        </el-descriptions-item>
        <el-descriptions-item label="实收金额">
          <span style="color:#f56c6c;font-size:18px;font-weight:700">¥{{ payableAmount.toFixed(2) }}</span>
        </el-descriptions-item>
      </el-descriptions>

      <div style="margin-top:16px">
        <div style="margin-bottom:8px;color:#606266">选择支付方式：</div>
        <el-radio-group v-model="payMethod">
          <el-radio value="wechat">微信支付</el-radio>
          <el-radio value="alipay">支付宝</el-radio>
          <el-radio value="cash">现金</el-radio>
          <el-radio v-if="memberInfo.found" value="balance">会员余额</el-radio>
        </el-radio-group>
      </div>

      <div v-if="cart.length > 0" style="margin-top:16px">
        <el-table :data="cart" size="small" :show-header="true">
          <el-table-column prop="product.name" label="商品" />
          <el-table-column prop="qty" label="数量" width="60" />
          <el-table-column label="小计" width="80">
            <template #default="{ row }">¥{{ (row.product.price * row.qty).toFixed(2) }}</template>
          </el-table-column>
        </el-table>
      </div>

      <template #footer>
        <el-button @click="checkoutVisible = false">取消</el-button>
        <el-button type="primary" @click="submitOrder">
          确认收款（{{ getPayMethodLabel(payMethod) }}）
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<style scoped lang="scss">
.cashier-layout {
  display: flex;
  gap: 16px;
  height: calc(100vh - 140px);
  min-height: 600px;
}

.product-panel {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: #fff;
  border-radius: 8px;
  padding: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  overflow: hidden;
}

.search-bar {
  margin-bottom: 12px;
}

.category-tabs {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  margin-bottom: 12px;
}

.cat-tag {
  padding: 4px 14px;
  border-radius: 20px;
  border: 1px solid #dcdfe6;
  cursor: pointer;
  font-size: 13px;
  color: #606266;
  transition: all 0.2s;

  &.active {
    background: #409eff;
    color: #fff;
    border-color: #409eff;
  }

  &:hover:not(.active) {
    border-color: #409eff;
    color: #409eff;
  }
}

.product-grid {
  flex: 1;
  overflow-y: auto;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
  gap: 10px;
  align-content: start;
}

.product-card {
  border: 1px solid #ebeef5;
  border-radius: 8px;
  padding: 12px 10px;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;

  &:hover {
    border-color: #409eff;
    box-shadow: 0 2px 8px rgba(64, 158, 255, 0.2);
    transform: translateY(-2px);
  }
}

.product-icon {
  font-size: 28px;
  margin-bottom: 6px;
}

.product-name {
  font-size: 13px;
  font-weight: 600;
  color: #303133;
  margin-bottom: 4px;
}

.product-price {
  font-size: 14px;
  color: #f56c6c;
  font-weight: 700;
  margin-bottom: 4px;
}

.empty-tip {
  grid-column: 1 / -1;
  text-align: center;
  color: #909399;
  padding: 40px 0;
}

// Cart panel
.cart-panel {
  width: 320px;
  display: flex;
  flex-direction: column;
  background: #fff;
  border-radius: 8px;
  padding: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.cart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 14px;
  font-weight: 600;
  margin-bottom: 10px;
  padding-bottom: 10px;
  border-bottom: 1px solid #f0f0f0;
}

.cart-list {
  flex: 1;
  overflow-y: auto;
}

.cart-empty {
  text-align: center;
  color: #909399;
  font-size: 13px;
  padding: 30px 0;
}

.cart-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 0;
  border-bottom: 1px solid #f5f5f5;
  font-size: 13px;
}

.cart-item-name {
  flex: 1;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  color: #303133;
}

.cart-item-price {
  width: 46px;
  color: #909399;
  flex-shrink: 0;
}

.cart-item-qty {
  display: flex;
  align-items: center;
  gap: 4px;
  flex-shrink: 0;
}

.qty-num {
  width: 24px;
  text-align: center;
  font-weight: 600;
}

.cart-item-subtotal {
  width: 56px;
  text-align: right;
  color: #f56c6c;
  font-weight: 600;
  flex-shrink: 0;
}

.member-strip {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 10px;
  background: #f0f7ff;
  border-radius: 6px;
  cursor: pointer;
  margin-top: 10px;
  font-size: 13px;
  color: #409eff;

  &:hover {
    background: #e1f0ff;
  }
}

.cart-total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 12px;
  font-size: 15px;
  font-weight: 600;
  color: #303133;
}

.total-price {
  font-size: 22px;
  color: #f56c6c;
}
</style>
