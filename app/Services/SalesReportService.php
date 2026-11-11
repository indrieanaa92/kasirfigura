<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalesReportService
{
    /**
     * Build financial summary, top-selling frames, and transaction history for a given month (YYYY-MM).
     *
     * @return array<string, mixed>
     */
    public function getMonthlyReport(?string $monthInput = null): array
    {
        $selectedMonth = $this->normalizeMonth($monthInput);
        $monthDate = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
        $startOfMonth = $monthDate->copy()->startOfMonth();
        $endOfMonth = $monthDate->copy()->endOfMonth();

        $now = now();
        $weeklyRevenue = (float) Transaction::whereBetween('created_at', [
            $now->copy()->subDays(6)->startOfDay(),
            $now->copy()->endOfDay(),
        ])->sum('total_amount');

        $monthlyRollingRevenue = (float) Transaction::whereBetween('created_at', [
            $now->copy()->subDays(29)->startOfDay(),
            $now->copy()->endOfDay(),
        ])->sum('total_amount');

        $transactions = Transaction::with(['user:id,name', 'details.product'])
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->latest()
            ->get();

        $topProducts = TransactionDetail::query()
            ->join('transactions', 'transactions.id', '=', 'transaction_details.transaction_id')
            ->join('products', 'products.id', '=', 'transaction_details.product_id')
            ->whereBetween('transactions.created_at', [$startOfMonth, $endOfMonth])
            ->groupBy('transaction_details.product_id', 'products.name', 'products.size')
            ->select([
                'transaction_details.product_id',
                'products.name as product_name',
                'products.size as product_size',
                DB::raw('SUM(transaction_details.quantity) as total_qty'),
                DB::raw('SUM(transaction_details.subtotal) as total_sales'),
            ])
            ->orderByDesc('total_qty')
            ->orderByDesc('total_sales')
            ->get();

        $availableMonths = Transaction::query()
            ->orderByDesc('created_at')
            ->pluck('created_at')
            ->map(fn ($createdAt) => Carbon::parse($createdAt)->format('Y-m'))
            ->push($now->format('Y-m'))
            ->push($selectedMonth)
            ->unique()
            ->sortDesc()
            ->values();

        return [
            'selectedMonth' => $selectedMonth,
            'currentMonth' => $now->format('Y-m'),
            'monthLabel' => $monthDate->translatedFormat('F Y'),
            'weeklyRevenue' => $weeklyRevenue,
            'monthlyRollingRevenue' => $monthlyRollingRevenue,
            'filteredRevenue' => (float) $transactions->sum('total_amount'),
            'filteredTransactionCount' => $transactions->count(),
            'filteredUnitsSold' => (int) $transactions->sum(
                fn (Transaction $transaction) => $transaction->details->sum('quantity')
            ),
            'transactions' => $transactions,
            'topProducts' => $topProducts,
            'availableMonths' => $availableMonths,
        ];
    }

    /**
     * Normalize requested month string into valid YYYY-MM format.
     */
    public function normalizeMonth(?string $monthInput): string
    {
        if (is_string($monthInput) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $monthInput) === 1) {
            return $monthInput;
        }

        return now()->format('Y-m');
    }
}

