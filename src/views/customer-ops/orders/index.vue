<script setup lang="ts">
import { ref, computed } from 'vue'
import { ElMessage } from 'element-plus'
import { Search, Plus, Minus, ShoppingCart, Delete } from '@element-plus/icons-vue'

interface Product {
  id: number
  name: string
  categoryId: number
  price: number
  emoji: string
  desc: string
}
interface CartItem {
  product: Product
  qty: number
}

const categories = [
  { id: 0, name: '全部' },
  { id: 1, name: '饮品' },
  { id: 2, name: '小吃' },
  { id: 3, name: '套餐' },
]

const allProducts = ref<Product[]>([
  { id: 1, name: '珍珠奶茶', categoryId: 1, price: 18, emoji: '🧋', desc: '香浓奶香，Q弹珍珠' },
  { id: 2, name: '芝士蛋糕', categoryId: 2, price: 28, emoji: '🎂', desc: '现烤芝士，入口即化' },
  { id: 3, name: '下午茶套餐', categoryId: 3, price: 45, emoji: '☕', desc: '饮品+小吃组合' },
  { id: 4, name: '美式咖啡', categoryId: 1, price: 22, emoji: '☕', desc: '醇正咖啡风味' },
  { id: 5, name: '抹茶拿铁', categoryId: 1, price: 25, emoji: '🍵', desc: '日式抹茶，清新回甘' },
  { id: 6, name: '薯条', categoryId: 2, price: 12, emoji: '🍟', desc: '外脆内软，现炸现吃' },
  { id: 7, name: '鸡翅', categoryId: 2, price: 20, emoji: '🍗', desc: '秘制腌制，香嫩多汁' },
  { id: 8, name: '双人套餐', categoryId: 3, price: 68, emoji: '🎁', desc: '两饮两食超值组合' },
  { id: 9, name: '焦糖玛奇朵', categoryId: 1, price: 30, emoji: '🫙', desc: '焦糖丝滑，香甜浓郁' },
  { id: 10, name: '草莓蛋糕', categoryId: 2, price: 35, emoji: '🍓', desc: '新鲜草莓，甜而不腻' },
])

const searchKeyword = ref('')
const activeCategoryId = ref(0)
const cart = ref<CartItem[]>([])
const checkoutVisible = ref(false)
const paySuccess = ref(false)
const cartDrawer = ref(false)

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
const cartCount = computed(() => cart.value.reduce((s, i) => s + i.qty, 0))

function addToCart(product: Product) {
  const existing = cart.value.find((i) => i.product.id === product.id)
  if (existing) {
    existing.qty++
  } else {
    cart.value.push({ product, qty: 1 })
  }
  ElMessage({ message: `已加入: ${product.name}`, type: 'success', duration: 800 })
}

function decreaseQty(item: CartItem) {
  if (item.qty > 1) item.qty--
  else cart.value = cart.value.filter((c) => c !== item)
}

function openCheckout() {
  if (cart.value.length === 0) {
    ElMessage.warning('购物车为空')
    return
  }
  cartDrawer.value = false
  checkoutVisible.value = true
}

function confirmPay() {
  checkoutVisible.value = false
  paySuccess.value = true
  setTimeout(() => {
    paySuccess.value = false
    cart.value = []
  }, 3000)
}

function getCartQty(productId: number) {
  return cart.value.find((i) => i.product.id === productId)?.qty ?? 0
}
</script>

<template>
  <div class="page miniapp-order">
    <!-- search + category -->
    <el-card shadow="hover" style="margin-bottom:12px">
      <el-input
        v-model="searchKeyword"
        placeholder="搜索商品"
        :prefix-icon="Search"
        clearable
        style="margin-bottom:10px"
      />
      <div class="category-tabs">
        <span
          v-for="cat in categories"
          :key="cat.id"
          :class="['cat-tag', { active: activeCategoryId === cat.id }]"
          @click="activeCategoryId = cat.id"
        >{{ cat.name }}</span>
      </div>
    </el-card>

    <!-- product list -->
    <el-card shadow="hover" style="margin-bottom:70px">
      <div v-if="filteredProducts.length === 0" style="text-align:center;color:#909399;padding:30px 0">
        暂无商品
      </div>
      <div v-for="product in filteredProducts" :key="product.id" class="product-row">
        <div class="product-emoji">{{ product.emoji }}</div>
        <div class="product-info">
          <div class="product-name">{{ product.name }}</div>
          <div class="product-desc">{{ product.desc }}</div>
          <div class="product-price">¥{{ product.price }}</div>
        </div>
        <div class="product-actions">
          <template v-if="getCartQty(product.id) > 0">
            <el-button :icon="Minus" circle size="small" @click="decreaseQty(cart.find(i => i.product.id === product.id)!)" />
            <span class="qty-badge">{{ getCartQty(product.id) }}</span>
          </template>
          <el-button :icon="Plus" circle size="small" type="primary" @click="addToCart(product)" />
        </div>
      </div>
    </el-card>

    <!-- floating cart bar -->
    <div v-if="cart.length > 0" class="cart-bar" @click="cartDrawer = true">
      <div class="cart-bar-left">
        <el-badge :value="cartCount" type="primary">
          <el-icon size="24"><ShoppingCart /></el-icon>
        </el-badge>
        <span style="margin-left:12px">已选 {{ cartCount }} 件</span>
      </div>
      <div class="cart-bar-right">
        <span class="cart-total-price">¥{{ totalAmount.toFixed(2) }}</span>
        <el-button type="primary" size="small" style="margin-left:10px;border-radius:16px" @click.stop="openCheckout">
          去结算
        </el-button>
      </div>
    </div>

    <!-- cart drawer -->
    <el-drawer v-model="cartDrawer" title="购物车" direction="btt" size="50%">
      <div v-for="item in cart" :key="item.product.id" class="cart-drawer-item">
        <span class="cdi-emoji">{{ item.product.emoji }}</span>
        <span class="cdi-name">{{ item.product.name }}</span>
        <span class="cdi-price">¥{{ item.product.price }}</span>
        <el-button :icon="Minus" circle size="small" @click="decreaseQty(item)" />
        <span class="cdi-qty">{{ item.qty }}</span>
        <el-button :icon="Plus" circle size="small" type="primary" @click="item.qty++" />
        <span class="cdi-sub">¥{{ (item.product.price * item.qty).toFixed(2) }}</span>
        <el-button :icon="Delete" circle size="small" type="danger" plain @click="cart = cart.filter(c => c !== item)" />
      </div>
      <div class="cart-drawer-total">
        合计：<span style="color:#f56c6c;font-weight:700;font-size:18px">¥{{ totalAmount.toFixed(2) }}</span>
      </div>
      <el-button type="primary" size="large" style="width:100%;border-radius:24px;margin-top:12px" @click="openCheckout">
        去结算（¥{{ totalAmount.toFixed(2) }}）
      </el-button>
    </el-drawer>

    <!-- checkout dialog -->
    <el-dialog v-model="checkoutVisible" title="确认订单" width="380px">
      <el-table :data="cart" size="small" border style="margin-bottom:12px">
        <el-table-column label="商品">
          <template #default="{ row }">{{ row.product.emoji }} {{ row.product.name }}</template>
        </el-table-column>
        <el-table-column prop="qty" label="数量" width="60" />
        <el-table-column label="小计" width="70">
          <template #default="{ row }">¥{{ (row.product.price * row.qty).toFixed(2) }}</template>
        </el-table-column>
      </el-table>
      <div style="text-align:right;font-size:16px;font-weight:700;margin-bottom:12px">
        合计：<span style="color:#f56c6c">¥{{ totalAmount.toFixed(2) }}</span>
      </div>
      <el-alert title="支付方式：微信支付" type="info" :closable="false" />
      <template #footer>
        <el-button @click="checkoutVisible = false">返回修改</el-button>
        <el-button type="primary" @click="confirmPay">确认支付（微信）</el-button>
      </template>
    </el-dialog>

    <!-- pay success dialog -->
    <el-dialog v-model="paySuccess" title="支付成功" width="300px" :show-close="false" center>
      <div style="text-align:center;font-size:48px">✅</div>
      <div style="text-align:center;font-size:16px;margin-top:8px">支付成功！感谢您的惠顾</div>
      <div style="text-align:center;color:#909399;font-size:13px;margin-top:4px">3 秒后自动关闭</div>
    </el-dialog>
  </div>
</template>

<style scoped lang="scss">
.miniapp-order {
  max-width: 480px;
  margin: 0 auto;
}

.category-tabs {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
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
}

.product-row {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 0;
  border-bottom: 1px solid #f5f5f5;

  &:last-child {
    border-bottom: none;
  }
}

.product-emoji {
  font-size: 36px;
  flex-shrink: 0;
}

.product-info {
  flex: 1;
}

.product-name {
  font-size: 14px;
  font-weight: 600;
  color: #303133;
}

.product-desc {
  font-size: 12px;
  color: #909399;
  margin: 2px 0;
}

.product-price {
  font-size: 15px;
  color: #f56c6c;
  font-weight: 700;
}

.product-actions {
  display: flex;
  align-items: center;
  gap: 4px;
  flex-shrink: 0;
}

.qty-badge {
  width: 22px;
  text-align: center;
  font-weight: 700;
  font-size: 13px;
}

.cart-bar {
  position: fixed;
  bottom: 16px;
  left: 50%;
  transform: translateX(-50%);
  width: calc(min(480px, 100vw) - 32px);
  background: #303133;
  color: #fff;
  border-radius: 32px;
  padding: 10px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  cursor: pointer;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25);
  z-index: 100;
}

.cart-bar-left {
  display: flex;
  align-items: center;
  font-size: 14px;
}

.cart-bar-right {
  display: flex;
  align-items: center;
}

.cart-total-price {
  font-size: 16px;
  font-weight: 700;
  color: #ffd04b;
}

.cart-drawer-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 0;
  border-bottom: 1px solid #f5f5f5;
  font-size: 13px;
}

.cdi-emoji { font-size: 22px; }
.cdi-name  { flex: 1; font-weight: 500; }
.cdi-price { width: 46px; color: #909399; }
.cdi-qty   { width: 24px; text-align: center; font-weight: 700; }
.cdi-sub   { width: 60px; text-align: right; color: #f56c6c; font-weight: 600; }

.cart-drawer-total {
  text-align: right;
  font-size: 15px;
  margin-top: 12px;
  padding-top: 10px;
  border-top: 1px solid #ebeef5;
}
</style>
