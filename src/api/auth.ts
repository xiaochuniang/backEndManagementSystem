import { request } from './request'
import type { LoginParams, LoginResult, UserInfo } from '@/types/api'

export function loginApi(data: LoginParams): Promise<LoginResult> {
  return request<LoginResult>({ url: '/auth/login', method: 'post', data })
}

export function getUserInfoApi(): Promise<UserInfo> {
  return request<UserInfo>({ url: '/auth/userinfo', method: 'get' })
}

export function logoutApi(): Promise<void> {
  return request<void>({ url: '/auth/logout', method: 'post' })
}
