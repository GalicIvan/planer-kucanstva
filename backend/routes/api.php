<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DebtController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\HouseholdController;
use App\Http\Controllers\Api\ReceiptController;
use App\Http\Controllers\Api\ShoppingItemController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Planer kućanstva
|--------------------------------------------------------------------------
| All routes are prefixed with /api automatically.
| Auth: Laravel Sanctum (personal access tokens / Bearer auth).
*/

// ---- Public routes ----
Route::get('/', function () {
    return response()->json([
        'name' => 'Planer kucanstva API',
        'status' => 'ok',
    ]);
});

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// ---- Protected routes (any authenticated, active user) ----
Route::middleware(['auth:sanctum', 'active'])->group(function () {

    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Household (current user's household)
    Route::get('/household', [HouseholdController::class, 'show']);
    Route::post('/household', [HouseholdController::class, 'store']);
    Route::put('/household/{household}', [HouseholdController::class, 'update']);
    Route::get('/household/{household}/members', [HouseholdController::class, 'members']);

    // Expenses (CRUD + search + filters)
    Route::get('/expenses', [ExpenseController::class, 'index']);
    Route::post('/expenses', [ExpenseController::class, 'store']);
    Route::get('/expenses/{expense}', [ExpenseController::class, 'show']);
    Route::put('/expenses/{expense}', [ExpenseController::class, 'update']);
    Route::post('/expenses/{expense}', [ExpenseController::class, 'update']); // for multipart "update with file"
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy']);

    // Debts
    Route::get('/debts', [DebtController::class, 'index']);
    Route::patch('/debts/{share}/settle', [DebtController::class, 'settle']);

    // Tasks
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::put('/tasks/{task}', [TaskController::class, 'update']);
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus']);
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);

    // Shopping list
    Route::get('/shopping-items', [ShoppingItemController::class, 'index']);
    Route::post('/shopping-items', [ShoppingItemController::class, 'store']);
    Route::put('/shopping-items/{shoppingItem}', [ShoppingItemController::class, 'update']);
    Route::patch('/shopping-items/{shoppingItem}/purchased', [ShoppingItemController::class, 'markPurchased']);
    Route::delete('/shopping-items/{shoppingItem}', [ShoppingItemController::class, 'destroy']);

    // Receipts
    Route::get('/receipts', [ReceiptController::class, 'index']);
    Route::post('/receipts', [ReceiptController::class, 'store']);
    Route::delete('/receipts/{receipt}', [ReceiptController::class, 'destroy']);

    // ---- Admin + super_admin only ----
    Route::middleware(['role:admin,super_admin'])->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::put('/users/{user}', [UserController::class, 'update']);

        Route::post('/household/{household}/members', [HouseholdController::class, 'addMember']);
        Route::delete('/household/{household}/members/{user}', [HouseholdController::class, 'removeMember']);
    });

    // ---- super_admin only ----
    Route::middleware(['role:super_admin'])->group(function () {
        Route::patch('/users/{user}/role', [UserController::class, 'changeRole']);
        Route::patch('/users/{user}/deactivate', [UserController::class, 'deactivate']);
    });
});
