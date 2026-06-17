<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ReceiptSeeder extends Seeder
{
    public function run(): void
    {
        $expense = Expense::where('title', 'Namirnice - Konzum')->first() ?? Expense::first();
        $user = User::where('email', 'ana@planer.test')->first() ?? User::first();

        if (!$expense || !$user) {
            return;
        }

        $path = 'receipts/demo-racun.pdf';

        if (!Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, $this->demoPdf());
        }

        Receipt::firstOrCreate(
            ['expense_id' => $expense->id, 'file_path' => $path],
            [
                'user_id' => $user->id,
                'file_type' => 'application/pdf',
                'original_name' => 'demo-racun.pdf',
            ]
        );

        if (!$expense->receipt_file_path) {
            $expense->update(['receipt_file_path' => $path]);
        }
    }

    private function demoPdf(): string
    {
        return "%PDF-1.4\n"
            . "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n"
            . "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n"
            . "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 300 144] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >> endobj\n"
            . "4 0 obj << /Length 78 >> stream\nBT /F1 14 Tf 36 96 Td (Planer kucanstva - demo racun) Tj 0 -24 Td (Seeder primjer PDF racuna.) Tj ET\nendstream endobj\n"
            . "5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj\n"
            . "trailer << /Root 1 0 R >>\n%%EOF\n";
    }
}
