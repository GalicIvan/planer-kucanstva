// Core domain types for "Planer kućanstva"

export type UserRole = 'user' | 'admin' | 'super_admin'

export interface User {
  id: number
  name: string
  email: string
  role: UserRole
  is_active: boolean
  created_at?: string
  updated_at?: string
  households?: Household[]
}

export interface Role {
  id: number
  name: string
}

export interface Permission {
  id: number
  name: string
  description: string | null
}

export interface HouseholdMember {
  id: number
  household_id: number
  user_id: number
  member_role: 'member' | 'admin'
  joined_at: string | null
  user?: User
  name?: string // present when household.members are loaded as User w/ pivot
  email?: string
  pivot?: {
    member_role: 'member' | 'admin'
    joined_at: string | null
  }
}

export interface Household {
  id: number
  name: string
  description: string | null
  created_by: number
  members?: (User & { pivot?: { member_role: 'member' | 'admin'; joined_at: string | null } })[]
  created_at?: string
  updated_at?: string
}

export type ExpenseCategory =
  | 'utilities'
  | 'groceries'
  | 'household'
  | 'food'
  | 'rent'
  | 'transport'
  | 'other'

export interface ExpenseShare {
  id: number
  expense_id: number
  user_id: number
  amount: string | number
  is_settled: boolean
  user?: User
  expense?: Expense
}

export interface Expense {
  id: number
  household_id: number
  paid_by_user_id: number
  title: string
  description: string | null
  amount: string | number
  category: string
  expense_date: string
  receipt_file_path: string | null
  payer?: User
  shares?: ExpenseShare[]
  receipts?: Receipt[]
  created_at?: string
  updated_at?: string
}

export interface Debt {
  user_id: number
  user_name: string
  net_amount: number // positive = they owe me, negative = I owe them
}

export interface DebtsResponse {
  owed_by_me: ExpenseShare[]
  owed_to_me: ExpenseShare[]
  balances: Debt[]
}

export type TaskStatus = 'pending' | 'done'

export interface Task {
  id: number
  household_id: number
  assigned_to_user_id: number | null
  created_by_user_id: number
  title: string
  description: string | null
  status: TaskStatus
  due_date: string | null
  assignee?: User | null
  creator?: User
  created_at?: string
  updated_at?: string
}

export interface ShoppingItem {
  id: number
  household_id: number
  created_by_user_id: number
  name: string
  quantity: number
  is_purchased: boolean
  creator?: User
  created_at?: string
  updated_at?: string
}

export interface Receipt {
  id: number
  expense_id: number
  user_id: number
  file_path: string
  file_type: string
  original_name: string
  expense?: Expense
  user?: User
  created_at?: string
}

export interface AppNotification {
  id: number
  user_id: number
  title: string
  message: string
  is_read: boolean
  created_at?: string
}

export interface PaginatedResponse<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export interface DashboardData {
  total_expenses_this_month: number
  i_owe: number
  owed_to_me: number
  open_tasks_count: number
  latest_expenses: Expense[]
  latest_tasks: Task[]
  message?: string
}
