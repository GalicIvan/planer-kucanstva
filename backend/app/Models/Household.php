<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Household extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'created_by'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'household_members')
            ->withPivot(['member_role', 'joined_at'])
            ->withTimestamps();
    }

    public function householdMembers()
    {
        return $this->hasMany(HouseholdMember::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function shoppingItems()
    {
        return $this->hasMany(ShoppingItem::class);
    }
}
