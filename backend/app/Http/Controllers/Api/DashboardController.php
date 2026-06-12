<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseShare;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Provides aggregated data for DashboardPage cards:
 *  - total expenses this month (household)
 *  - how much the current user owes
 *  - how much others owe the current user
 *  - number of open (pending) tasks
 *  - latest expenses
 *  - latest tasks
 */
class DashboardController extends Controller
{
    /**
     * GET /api/dashboard
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $household = $user->households()->first();

        if (!$household) {
            return response()->json([
                'message' => 'You are not part of a household yet.',
                'total_expenses_this_month' => 0,
                'i_owe' => 0,
                'owed_to_me' => 0,
                'open_tasks_count' => 0,
                'latest_expenses' => [],
                'latest_tasks' => [],
            ]);
        }

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $totalThisMonth = Expense::where('household_id', $household->id)
            ->whereBetween('expense_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $iOwe = ExpenseShare::whereHas('expense', function ($q) use ($household) {
                $q->where('household_id', $household->id);
            })
            ->where('user_id', $user->id)
            ->where('is_settled', false)
            ->whereHas('expense', function ($q) use ($user) {
                $q->where('paid_by_user_id', '!=', $user->id);
            })
            ->sum('amount');

        $owedToMe = ExpenseShare::whereHas('expense', function ($q) use ($household, $user) {
                $q->where('household_id', $household->id)
                  ->where('paid_by_user_id', $user->id);
            })
            ->where('user_id', '!=', $user->id)
            ->where('is_settled', false)
            ->sum('amount');

        $openTasksCount = Task::where('household_id', $household->id)
            ->where('status', 'pending')
            ->count();

        $latestExpenses = Expense::where('household_id', $household->id)
            ->with('payer:id,name')
            ->orderByDesc('expense_date')
            ->limit(5)
            ->get();

        $latestTasks = Task::where('household_id', $household->id)
            ->with('assignee:id,name')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return response()->json([
            'total_expenses_this_month' => (float) $totalThisMonth,
            'i_owe' => (float) $iOwe,
            'owed_to_me' => (float) $owedToMe,
            'open_tasks_count' => $openTasksCount,
            'latest_expenses' => $latestExpenses,
            'latest_tasks' => $latestTasks,
        ]);
    }
}
