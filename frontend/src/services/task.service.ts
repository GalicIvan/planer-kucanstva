import api from './api'
import type { Task, TaskStatus } from '@/types'

export interface TaskFilters {
  status?: TaskStatus | ''
  assigned_to_user_id?: number | string
}

export interface TaskPayload {
  title: string
  description?: string | null
  assigned_to_user_id?: number | null
  status?: TaskStatus
  due_date?: string | null
}

export const taskService = {
  list(filters: TaskFilters = {}) {
    return api.get<Task[]>('/tasks', { params: filters })
  },
  create(payload: TaskPayload) {
    return api.post<Task>('/tasks', payload)
  },
  update(id: number, payload: Partial<TaskPayload>) {
    return api.put<Task>(`/tasks/${id}`, payload)
  },
  updateStatus(id: number, status: TaskStatus) {
    return api.patch<Task>(`/tasks/${id}/status`, { status })
  },
  remove(id: number) {
    return api.delete(`/tasks/${id}`)
  },
}
