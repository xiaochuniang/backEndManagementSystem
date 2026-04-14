import { request } from './request'
import type { PageParams, PageResult, UserRecord, UserFormData } from '@/types/api'

export function getUserListApi(
  params: PageParams & { username?: string },
): Promise<PageResult<UserRecord>> {
  return request<PageResult<UserRecord>>({ url: '/user/list', method: 'get', params })
}

export function createUserApi(data: UserFormData): Promise<void> {
  return request<void>({ url: '/user/create', method: 'post', data })
}

export function updateUserApi(data: UserFormData): Promise<void> {
  return request<void>({ url: '/user/update', method: 'put', data })
}

export function deleteUserApi(id: number): Promise<void> {
  return request<void>({ url: `/user/delete/${id}`, method: 'delete' })
}
