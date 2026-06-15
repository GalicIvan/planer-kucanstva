import { defineStore } from 'pinia'
import { taskService, type TaskFilters, type TaskPayload } from '@/services/task.service'
import { useDashboardStore } from './dashboard'
import type { Task, TaskStatus } from '@/types'

interface TaskState {
  tasks: Task[]
  loading: boolean
  error: string | null
}

export const useTaskStore = defineStore('task', {
  state: (): TaskState => ({
    tasks: [],
    loading: false,
    error: null,
  }),

  actions: {
    async fetchTasks(filters: TaskFilters = {}) {
      this.loading = true
      this.error = null
      try {
        const { data } = await taskService.list(filters)
        this.tasks = data
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Greška prilikom dohvaćanja zadataka.'
      } finally {
        this.loading = false
      }
    },

    async createTask(payload: TaskPayload) {
      const { data } = await taskService.create(payload)
      this.tasks.unshift(data)
      await useDashboardStore().refresh().catch(() => undefined)
      return data
    },

    async updateTask(id: number, payload: Partial<TaskPayload>) {
      const { data } = await taskService.update(id, payload)
      const idx = this.tasks.findIndex((t) => t.id === id)
      if (idx !== -1) this.tasks[idx] = data
      await useDashboardStore().refresh().catch(() => undefined)
      return data
    },

    async markDone(id: number, status: TaskStatus = 'done') {
      const { data } = await taskService.updateStatus(id, status)
      const idx = this.tasks.findIndex((t) => t.id === id)
      if (idx !== -1) this.tasks[idx] = data
      await useDashboardStore().refresh().catch(() => undefined)
      return data
    },

    async deleteTask(id: number) {
      await taskService.remove(id)
      this.tasks = this.tasks.filter((t) => t.id !== id)
      await useDashboardStore().refresh().catch(() => undefined)
    },
  },
})
