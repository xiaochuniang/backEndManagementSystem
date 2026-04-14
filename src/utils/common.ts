import { debounce, throttle } from 'lodash-es'

/**
 * 防抖函数
 */
export function useDebounce<T extends (...args: unknown[]) => unknown>(
  fn: T,
  wait: number = 300,
): (...args: Parameters<T>) => void {
  return debounce(fn, wait)
}

/**
 * 节流函数
 */
export function useThrottle<T extends (...args: unknown[]) => unknown>(
  fn: T,
  wait: number = 300,
): (...args: Parameters<T>) => void {
  return throttle(fn, wait)
}

/**
 * 深拷贝
 */
export function deepClone<T>(obj: T): T {
  if (obj === null || typeof obj !== 'object') return obj

  const clone: Record<string, unknown> = Array.isArray(obj) ? [] : {}

  for (const key in obj) {
    if (Object.prototype.hasOwnProperty.call(obj, key)) {
      const value = obj[key]
      clone[key] = value && typeof value === 'object' ? deepClone(value) : value
    }
  }

  return clone as T
}

/**
 * 生成随机字符串
 */
export function randomString(length: number = 16): string {
  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'
  let result = ''
  for (let i = 0; i < length; i++) {
    result += chars.charAt(Math.floor(Math.random() * chars.length))
  }
  return result
}

/**
 * 判断是否为空值
 */
export function isEmpty(value: unknown): boolean {
  if (value === null || value === undefined) return true
  if (typeof value === 'string') return value.trim() === ''
  if (Array.isArray(value)) return value.length === 0
  if (typeof value === 'object') return Object.keys(value as object).length === 0
  return false
}

/**
 * 获取数据类型
 */
export function getType(value: unknown): string {
  return Object.prototype.toString.call(value).slice(8, -1).toLowerCase()
}

/**
 * 手机号脱敏
 */
export function maskPhone(phone: string): string {
  if (!phone || phone.length !== 11) return phone
  return phone.replace(/(\d{3})\d{4}(\d{4})/, '$1****$2')
}

/**
 * 邮箱脱敏
 */
export function maskEmail(email: string): string {
  if (!email || !email.includes('@')) return email
  const [name, domain] = email.split('@')
  if (name.length <= 3) return email
  return name.slice(0, 3) + '***@' + domain
}

/**
 * 金额格式化
 */
export function formatMoney(amount: number | string, decimals: number = 2): string {
  const num = Number(amount)
  if (isNaN(num)) return '0.00'
  return num.toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
}

/**
 * 判断路径是否为外部链接
 */
export function isExternal(path: string): boolean {
  return /^(https?:|mailto:|tel:)/.test(path)
}
