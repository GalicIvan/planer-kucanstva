import { createRouter, createWebHistory } from 'vue-router'
import type { RouteRecordRaw } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import type { UserRole } from '@/types'

import AppLayout from '@/layouts/AppLayout.vue'
import AuthLayout from '@/layouts/AuthLayout.vue'

import LandingPage from '@/pages/LandingPage.vue'
import LoginPage from '@/pages/LoginPage.vue'
import RegisterPage from '@/pages/RegisterPage.vue'
import DashboardPage from '@/pages/DashboardPage.vue'
import HouseholdPage from '@/pages/HouseholdPage.vue'
import ExpensesPage from '@/pages/ExpensesPage.vue'
import ExpenseDetailsPage from '@/pages/ExpenseDetailsPage.vue'
import DebtsPage from '@/pages/DebtsPage.vue'
import TasksPage from '@/pages/TasksPage.vue'
import ShoppingListPage from '@/pages/ShoppingListPage.vue'
import UploadReceiptPage from '@/pages/UploadReceiptPage.vue'
import AdminUsersPage from '@/pages/AdminUsersPage.vue'
import ForbiddenPage from '@/pages/ForbiddenPage.vue'
import NotFoundPage from '@/pages/NotFoundPage.vue'

declare module 'vue-router' {
  interface RouteMeta {
    title?: string
    requiresAuth?: boolean
    guestOnly?: boolean
    roles?: UserRole[]
  }
}

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    name: 'landing',
    component: LandingPage,
    meta: { title: 'Planer kućanstva' },
  },
  {
    path: '/',
    component: AuthLayout,
    meta: { guestOnly: true },
    children: [
      { path: 'login', name: 'login', component: LoginPage, meta: { title: 'Prijava' } },
      { path: 'register', name: 'register', component: RegisterPage, meta: { title: 'Registracija' } },
    ],
  },
  {
    path: '/',
    component: AppLayout,
    meta: { requiresAuth: true },
    children: [
      { path: 'dashboard', name: 'dashboard', component: DashboardPage, meta: { title: 'Nadzorna ploča' } },
      { path: 'household', name: 'household', component: HouseholdPage, meta: { title: 'Kućanstvo' } },
      { path: 'expenses', name: 'expenses', component: ExpensesPage, meta: { title: 'Troškovi' } },
      {
        path: 'expenses/:id',
        name: 'expense-details',
        component: ExpenseDetailsPage,
        meta: { title: 'Detalji troška' },
      },
      { path: 'debts', name: 'debts', component: DebtsPage, meta: { title: 'Dugovi' } },
      { path: 'tasks', name: 'tasks', component: TasksPage, meta: { title: 'Zadaci' } },
      { path: 'shopping', name: 'shopping', component: ShoppingListPage, meta: { title: 'Popis za kupnju' } },
      {
        path: 'upload-receipt',
        name: 'upload-receipt',
        component: UploadReceiptPage,
        meta: { title: 'Učitaj račun' },
      },
      {
        path: 'admin/users',
        name: 'admin-users',
        component: AdminUsersPage,
        meta: { title: 'Korisnici', roles: ['admin', 'super_admin'] },
      },
    ],
  },
  {
    path: '/403',
    name: 'forbidden',
    component: ForbiddenPage,
    meta: { title: 'Pristup odbijen' },
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: NotFoundPage,
    meta: { title: 'Stranica nije pronađena' },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to) => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  if (to.meta.guestOnly && auth.isAuthenticated) {
    return { name: 'dashboard' }
  }

  if (to.meta.roles && !auth.hasRole(...to.meta.roles)) {
    return { name: 'forbidden' }
  }

  return true
})

export default router
