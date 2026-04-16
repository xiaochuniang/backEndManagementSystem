export interface MemberInfo {
  name: string
  level: string
  balance: number
  points: number
}

export interface OrderInfo {
  id: string
  shopName: string
  amount: number
  status: '已完成' | '待支付' | '退款'
  createdAt: string
}

export const memberInfo: MemberInfo = {
  name: '李顾客',
  level: '银卡会员',
  balance: 328.5,
  points: 1680,
}

export const orderList: OrderInfo[] = [
  {
    id: 'SO20260416001',
    shopName: '城南店',
    amount: 78,
    status: '已完成',
    createdAt: '2026-04-15 18:23',
  },
  {
    id: 'SO20260414008',
    shopName: '城西店',
    amount: 120,
    status: '待支付',
    createdAt: '2026-04-14 20:11',
  },
]
