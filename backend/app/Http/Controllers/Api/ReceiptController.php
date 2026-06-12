<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReceiptController extends Controller
{
    /**
     * GET /api/receipts
     * Lists receipts for the current user's household.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $household = $user->households()->first();

        if (!$household) {
            return response()->json([]);
        }

        $receipts = Receipt::query()
            ->whereHas('expense', function ($q) use ($household) {
                $q->where('household_id', $household->id);
            })
            ->with(['expense:id,title', 'user:id,name'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($receipts);
    }

    /**
     * POST /api/receipts
     * Uploads an image/pdf receipt and attaches it to an existing expense.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'expense_id' => ['required', 'integer', 'exists:expenses,id'],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $expense = Expense::findOrFail($data['expense_id']);
        $path = $request->file('file')->store('receipts', 'public');

        $receipt = Receipt::create([
            'expense_id' => $expense->id,
            'user_id' => $request->user()->id,
            'file_path' => $path,
            'file_type' => $request->file('file')->getClientMimeType(),
            'original_name' => $request->file('file')->getClientOriginalName(),
        ]);

        // Keep the expense's "main" receipt path in sync if it had none yet.
        if (!$expense->receipt_file_path) {
            $expense->update(['receipt_file_path' => $path]);
        }

        return response()->json($receipt->load(['expense:id,title', 'user:id,name']), 201);
    }

    /**
     * DELETE /api/receipts/{receipt}
     */
    public function destroy(Receipt $receipt)
    {
        Storage::disk('public')->delete($receipt->file_path);
        $receipt->delete();

        return response()->json(['message' => 'Receipt deleted.']);
    }
}
