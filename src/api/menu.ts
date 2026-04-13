import { request } from './request'
import type { MenuItem, MenuFormData } from '@/types/api'

export function getMenuTreeApi(): Promise<MenuItem[]> {
  return request<MenuItem[]>({ url: '/menu/tree', method: 'get' })
}

export function createMenuApi(data: MenuFormData): Promise<void> {
  return request<void>({ url: '/menu/create', method: 'post', data })
}

export function updateMenuApi(data: MenuFormData): Promise<void> {
  return request<void>({ url: '/menu/update', method: 'put', data })
}

export function deleteMenuApi(id: number): Promise<void> {
  return request<void>({ url: `/menu/delete/${id}`, method: 'delete' })
}
