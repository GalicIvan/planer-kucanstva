<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    // ---- Role helpers ----

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin'], true);
    }

    // ---- Relations ----

    public function households()
    {
        return $this->belongsToMany(Household::class, 'household_members')
            ->withPivot(['member_role', 'joined_at'])
            ->withTimestamps();
    }

    public function householdMemberships()
    {
        return $this->hasMany(HouseholdMember::class);
    }

    public function createdHouseholds()
    {
        return $this->hasMany(Household::class, 'created_by');
    }

    public function paidExpenses()
    {
        return $this->hasMany(Expense::class, 'paid_by_user_id');
    }

    public function expenseShares()
    {
        return $this->hasMany(ExpenseShare::class);
    }

    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'assigned_to_user_id');
    }

    public function createdTasks()
    {
        return $this->hasMany(Task::class, 'created_by_user_id');
    }

    public function shoppingItems()
    {
        return $this->hasMany(ShoppingItem::class, 'created_by_user_id');
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the household the user currently belongs to (first one).
     */
    public function primaryHousehold()
    {
        return $this->households()->first();
    }

    /**
     * Check whether the user (via their role -> roles/permissions tables)
     * has a given permission name.
     */
    public function hasPermission(string $permissionName): bool
    {
        // super_admin always has every permission
        if ($this->role === 'super_admin') {
            return true;
        }

        $role = Role::where('name', $this->role)->first();

        if (!$role) {
            return false;
        }

        return $role->permissions()->where('name', $permissionName)->exists();
    }
}
