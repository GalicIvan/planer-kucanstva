<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'household_id',
        'paid_by_user_id',
        'title',
        'description',
        'amount',
        'category',
        'expense_date',
        'receipt_file_path',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function household()
    {
        return $this->belongsTo(Household::class);
    }

    public function payer()
    {
        return $this->belongsTo(User::class, 'paid_by_user_id');
    }

    public function shares()
    {
        return $this->hasMany(ExpenseShare::class);
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }
}
