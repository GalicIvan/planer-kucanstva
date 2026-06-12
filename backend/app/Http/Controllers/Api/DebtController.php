<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExpenseShare;
use Illuminate\Http\Request;

/**
 * Handles the "who owes whom" DebtsPage.
 */
class DebtController extends Controller
{
    /**
     * GET /api/debts
     * Returns:
     *  - "owed_by_me": shares where the current user owes someone else money
     *  - "owed_to_me": shares where someone else owes the current user money
     *  - "balances": net amount per other household member
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $household = $user->households()->first();

        if (!$household) {
            return response()->json(['owed_by_me' => [], 'owed_to_me' => [], 'balances' => []]);
        }

        // Shares belonging to expenses of this household, not yet settled,
        // where the share owner is NOT the one who paid (i.e. an actual debt).
        $shares = ExpenseShare::whereHas('expense', function ($q) use ($household) {
                $q->where('household_id', $household->id);
            })
            ->with(['expense:id,title,paid_by_user_id,amount,expense_date', 'expense.payer:id,name', 'user:id,name'])
            ->where('is_settled', false)
            ->get()
            ->filter(function ($share) {
                return $share->user_id != $share->expense->paid_by_user_id;
            })
            ->values();

        $owedByMe = $shares->where('user_id', $user->id)->values();
        $owedToMe = $shares->filter(function ($share) use ($user) {
            return $share->expense->paid_by_user_id === $user->id;
        })->values();

        // Net balances per counterpart user
        $balances = [];
        foreach ($shares as $share) {
            $debtor = $share->user_id;
            $creditor = $share->expense->paid_by_user_id;

            if ($debtor === $user->id) {
                $other = $creditor;
                $sign = -1; // I owe them
            } elseif ($creditor === $user->id) {
                $other = $debtor;
                $sign = 1; // they owe me
            } else {
                continue;
            }

            $balances[$other] = ($balances[$other] ?? 0) + ($sign * (float) $share->amount);
        }

        $balanceList = [];
        foreach ($balances as $userId => $amount) {
            $otherUser = \App\Models\User::find($userId);
            $balanceList[] = [
                'user_id' => $userId,
                'user_name' => $otherUser?->name,
                'net_amount' => round($amount, 2), // positive = they owe me, negative = I owe them
            ];
        }

        return response()->json([
            'owed_by_me' => $owedByMe,
            'owed_to_me' => $owedToMe,
            'balances' => $balanceList,
        ]);
    }

    /**
     * PATCH /api/debts/{share}/settle
     * Marks an expense share as settled (paid back).
     */
    public function settle(ExpenseShare $share)
    {
        $share->update(['is_settled' => true]);

        return response()->json($share->load(['expense:id,title', 'user:id,name']));
    }
}
