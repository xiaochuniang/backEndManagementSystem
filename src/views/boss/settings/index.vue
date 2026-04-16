<script setup lang="ts">
import { reactive, ref } from 'vue'
import { ElMessage } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'

const activeTab = ref('shop')

const shopForm = reactive({
  name: '城南门店',
  address: '广东省深圳市南山区XX路100号',
  phone: '0755-12345678',
  desc: '专注门店收银与会员服务',
})

const payForm = reactive({
  wxAppId: 'wx_xxxxxxx',
  wxMchId: '1234567890',
  wxKey: '',
  aliAppId: '',
  aliPrivateKey: '',
})

const receiptForm = reactive({
  enabled: true,
  header: '欢迎光临城南门店',
  footer: '感谢您的惠顾，欢迎再次光临！',
})

const pwdForm = reactive({
  oldPwd: '',
  newPwd: '',
  confirmPwd: '',
})
const pwdFormRef = ref()

function saveShop() { ElMessage.success('店铺信息保存成功') }
function savePay() { ElMessage.success('支付设置保存成功') }
function saveReceipt() { ElMessage.success('小票设置保存成功') }
async function savePwd() {
  if (!pwdForm.oldPwd || !pwdForm.newPwd) return ElMessage.warning('请填写完整')
  if (pwdForm.newPwd !== pwdForm.confirmPwd) return ElMessage.error('两次密码不一致')
  ElMessage.success('密码修改成功')
  pwdForm.oldPwd = ''
  pwdForm.newPwd = ''
  pwdForm.confirmPwd = ''
}
</script>

<template>
  <div class="page">
    <el-tabs v-model="activeTab">
      <el-tab-pane label="店铺信息" name="shop">
        <el-form :model="shopForm" label-width="100px" style="max-width:600px">
          <el-form-item label="店铺名称">
            <el-input v-model="shopForm.name" />
          </el-form-item>
          <el-form-item label="Logo">
            <el-upload action="#" list-type="picture-card" :auto-upload="false" :limit="1">
              <el-icon><Plus /></el-icon>
            </el-upload>
          </el-form-item>
          <el-form-item label="地址">
            <el-input v-model="shopForm.address" />
          </el-form-item>
          <el-form-item label="联系电话">
            <el-input v-model="shopForm.phone" />
          </el-form-item>
          <el-form-item label="店铺介绍">
            <el-input v-model="shopForm.desc" type="textarea" :rows="3" />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="saveShop">保存</el-button>
          </el-form-item>
        </el-form>
      </el-tab-pane>

      <el-tab-pane label="支付设置" name="pay">
        <el-card shadow="never" header="微信支付" style="max-width:600px;margin-bottom:16px">
          <el-form :model="payForm" label-width="110px">
            <el-form-item label="微信AppID">
              <el-input v-model="payForm.wxAppId" />
            </el-form-item>
            <el-form-item label="商户号">
              <el-input v-model="payForm.wxMchId" />
            </el-form-item>
            <el-form-item label="API密钥">
              <el-input v-model="payForm.wxKey" type="password" show-password />
            </el-form-item>
          </el-form>
        </el-card>
        <el-card shadow="never" header="支付宝" style="max-width:600px;margin-bottom:16px">
          <el-form :model="payForm" label-width="110px">
            <el-form-item label="支付宝AppID">
              <el-input v-model="payForm.aliAppId" />
            </el-form-item>
            <el-form-item label="应用私钥">
              <el-input v-model="payForm.aliPrivateKey" type="textarea" :rows="4" />
            </el-form-item>
          </el-form>
        </el-card>
        <el-button type="primary" @click="savePay">保存支付设置</el-button>
      </el-tab-pane>

      <el-tab-pane label="小票设置" name="receipt">
        <el-form :model="receiptForm" label-width="100px" style="max-width:500px">
          <el-form-item label="自动打印">
            <el-switch v-model="receiptForm.enabled" />
          </el-form-item>
          <el-form-item label="小票抬头">
            <el-input v-model="receiptForm.header" />
          </el-form-item>
          <el-form-item label="小票尾部">
            <el-input v-model="receiptForm.footer" type="textarea" :rows="3" />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="saveReceipt">保存</el-button>
          </el-form-item>
        </el-form>
      </el-tab-pane>

      <el-tab-pane label="修改密码" name="password">
        <el-form ref="pwdFormRef" :model="pwdForm" label-width="100px" style="max-width:460px">
          <el-form-item label="原密码">
            <el-input v-model="pwdForm.oldPwd" type="password" show-password />
          </el-form-item>
          <el-form-item label="新密码">
            <el-input v-model="pwdForm.newPwd" type="password" show-password />
          </el-form-item>
          <el-form-item label="确认密码">
            <el-input v-model="pwdForm.confirmPwd" type="password" show-password />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="savePwd">修改密码</el-button>
          </el-form-item>
        </el-form>
      </el-tab-pane>
    </el-tabs>
  </div>
</template>
