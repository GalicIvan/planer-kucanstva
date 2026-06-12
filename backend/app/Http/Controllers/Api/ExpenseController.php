<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Expense;
use App\Models\ExpenseShare;
use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    /**
     * GET /api/expenses
     * Supports: search (title), category, user (paid_by_user_id), date range,
     * which together fulfil the "minimum 3 simultaneous filters" requirement.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $household = $user->households()->first();

        if (!$household) {
            return response()->json(['data' => []]);
        }

        $query = Expense::query()
            ->where('household_id', $household->id)
            ->with(['payer:id,name', 'shares.user:id,name']);

        // Filter 1: search by title
        if ($search = $request->query('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        // Filter 2: category
        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }

        // Filter 3: paid by user
        if ($paidBy = $request->query('paid_by_user_id')) {
            $query->where('paid_by_user_id', $paidBy);
        }

        // Filter 4 & 5: date range
        if ($from = $request->query('date_from')) {
            $query->whereDate('expense_date', '>=', $from);
        }

        if ($to = $request->query('date_to')) {
            $query->whereDate('expense_date', '<=', $to);
        }

        $expenses = $query->orderByDesc('expense_date')->paginate(
            $request->query('per_page', 15)
        );

        return response()->json($expenses);
    }

    /**
     * POST /api/expenses
     */
    public function store(StoreExpenseRequest $request)
    {
        $user = $request->user();
        $household = $user->households()->first();

        if (!$household) {
            return response()->json(['message' => 'You must belong to a household before adding expenses.'], 422);
        }

        $path = null;
        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts', 'public');
        }

        $expense = Expense::create([
            'household_id' => $household->id,
            'paid_by_user_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'amount' => $request->amount,
            'category' => $request->category ?? 'other',
            'expense_date' => $request->expense_date,
            'receipt_file_path' => $path,
        ]);

        if ($path) {
            Receipt::create([
                'expense_id' => $expense->id,
                'user_id' => $user->id,
                'file_path' => $path,
                'file_type' => $request->file('receipt')->getClientMimeType(),
                'original_name' => $request->file('receipt')->getClientOriginalName(),
            ]);
        }

        $this->createShares($expense, $request->input('split_with', []), $household);

        return response()->json($expense->load(['payer:id,name', 'shares.user:id,name']), 201);
    }

    /**
     * GET /api/expenses/{expense}
     */
    public function show(Expense $expense)
    {
        return response()->json($expense->load(['payer:id,name', 'shares.user:id,name', 'receipts']));
    }

    /**
     * PUT/PATCH /api/expenses/{expense}
     */
    public function update(UpdateExpenseRequest $request, Expense $expense)
    {
        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts', 'public');
            $expense->receipt_file_path = $path;

            Receipt::create([
                'expense_id' => $expense->id,
                'user_id' => $request->user()->id,
                'file_path' => $path,
                'file_type' => $request->file('receipt')->getClientMimeType(),
                'original_name' => $request->file('receipt')->getClientOriginalName(),
            ]);
        }

        $expense->fill($request->only(['title', 'description', 'amount', 'category', 'expense_date']));
        $expense->save();

        if ($request->has('split_with')) {
            $expense->shares()->delete();
            $this->createShares($expense, $request->input('split_with', []), $expense->household);
        }

        return response()->json($expense->load(['payer:id,name', 'shares.user:id,name']));
    }

    /**
     * DELETE /api/expenses/{expense}
     */
    public function destroy(Expense $expense)
    {
        if ($expense->receipt_file_path) {
            Storage::disk('public')->delete($expense->receipt_file_path);
        }

        $expense->delete();

        return response()->json(['message' => 'Expense deleted.']);
    }

    /**
     * Splits the expense amount equally between the payer and the
     * given list of user ids (creating ExpenseShare rows).
     */
    private function createShares(Expense $expense, array $userIds, $household): void
    {
        $userIds = array_unique(array_merge($userIds, [$expense->paid_by_user_id]));
        $count = count($userIds);

        if ($count === 0) {
            return;
        }

        $share = round($expense->amount / $count, 2);

        foreach ($userIds as $userId) {
            ExpenseShare::create([
                'expense_id' => $expense->id,
                'user_id' => $userId,
                'amount' => $share,
                // the payer's own share is automatically considered settled
                'is_settled' => $userId == $expense->paid_by_user_id,
            ]);
        }
    }
}
