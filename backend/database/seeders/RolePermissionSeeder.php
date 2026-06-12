<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Seeds the `roles` and `permissions` tables and links them via
 * `role_permission`. This is the granular permissions layer used
 * by User::hasPermission(), on top of the simple `users.role` column
 * which is used by the route middleware for fast checks.
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['user', 'admin', 'super_admin'];

        $roleModels = [];
        foreach ($roles as $name) {
            $roleModels[$name] = Role::firstOrCreate(['name' => $name]);
        }

        $permissions = [
            'expenses.view_own' => 'View own expenses',
            'expenses.manage_own' => 'Create/update/delete own expenses',
            'expenses.manage_all' => 'Manage all expenses in the household',
            'tasks.manage_own' => 'Create and complete own tasks',
            'tasks.manage_all' => 'Manage all tasks in the household',
            'shopping.manage' => 'Manage shopping list items',
            'household.manage_members' => 'Add/remove household members',
            'users.manage' => 'View and edit users',
            'users.manage_roles' => 'Change user roles and activation status',
        ];

        $permissionModels = [];
        foreach ($permissions as $name => $description) {
            $permissionModels[$name] = Permission::firstOrCreate(
                ['name' => $name],
                ['description' => $description]
            );
        }

        $map = [
            'user' => [
                'expenses.view_own', 'expenses.manage_own',
                'tasks.manage_own', 'shopping.manage',
            ],
            'admin' => [
                'expenses.view_own', 'expenses.manage_own', 'expenses.manage_all',
                'tasks.manage_own', 'tasks.manage_all', 'shopping.manage',
                'household.manage_members', 'users.manage',
            ],
            'super_admin' => array_keys($permissions),
        ];

        foreach ($map as $roleName => $permissionNames) {
            $role = $roleModels[$roleName];
            $ids = array_map(fn ($p) => $permissionModels[$p]->id, $permissionNames);
            $role->permissions()->syncWithoutDetaching($ids);
        }
    }
}
