import { request } from './request'
import type { PageParams, PageResult, RoleRecord, RoleFormData } from '@/types/api'

export function getRoleListApi(
  params: PageParams & { name?: string },
): Promise<PageResult<RoleRecord>> {
  return request<PageResult<RoleRecord>>({ url: '/role/list', method: 'get', params })
}

export function getAllRolesApi(): Promise<RoleRecord[]> {
  return request<RoleRecord[]>({ url: '/role/all', method: 'get' })
}

export function createRoleApi(data: RoleFormData): Promise<void> {
  return request<void>({ url: '/role/create', method: 'post', data })
}

export function updateRoleApi(data: RoleFormData): Promise<void> {
  return request<void>({ url: '/role/update', method: 'put', data })
}

export function deleteRoleApi(id: number): Promise<void> {
  return request<void>({ url: `/role/delete/${id}`, method: 'delete' })
}
