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
        $todayStart = $now->copy()->startOfDay();
        $todayEnd = $now->copy()->endOfDay();

        $todayQuery = Transaction::whereBetween('created_at', [$todayStart, $todayEnd]);
        $dailyRevenue = (float) (clone $todayQuery)->sum('total_amount');
        $dailyTransactionCount = (int) (clone $todayQuery)->count();

        $weekStart = $now->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $weekEnd = $now->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

        $weekQuery = Transaction::whereBetween('created_at', [$weekStart, $weekEnd]);
        $weeklyRevenue = (float) (clone $weekQuery)->sum('total_amount');
        $weeklyTransactionCount = (int) (clone $weekQuery)->count();

        $transactions = Transaction::with(['user:id,name', 'details.product'])
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->latest()
            ->get();

        $timezone = config('app.timezone');

        $dailyBreakdown = $transactions
            ->groupBy(fn (Transaction $trx) => $trx->created_at->copy()->timezone($timezone)->format('Y-m-d'))
            ->map(function ($dayTransactions, string $dateKey): array {
                $dateObj = Carbon::createFromFormat('Y-m-d', $dateKey);

                return [
                    'date' => $dateKey,
                    'label' => $dateObj->translatedFormat('l, d M Y'),
                    'short_label' => $dateObj->translatedFormat('d M Y'),
                    'transaction_count' => $dayTransactions->count(),
                    'total_revenue' => (float) $dayTransactions->sum('total_amount'),
                ];
            })
            ->sortByDesc('date')
            ->values();

        $daysInMonth = $endOfMonth->day;
        $totalWeeks = (int) ceil($daysInMonth / 7);
        $groupedByWeek = $transactions->groupBy(
            fn (Transaction $trx) => (int) ceil($trx->created_at->copy()->timezone($timezone)->day / 7)
        );

        $weeklyBreakdown = collect(range(1, $totalWeeks))
            ->map(function (int $weekNumber) use ($groupedByWeek, $daysInMonth, $monthDate): array {
                $startDay = (($weekNumber - 1) * 7) + 1;
                $endDay = min($daysInMonth, $weekNumber * 7);
                $weekTransactions = $groupedByWeek->get($weekNumber, collect());

                return [
                    'week_number' => $weekNumber,
                    'label' => "Minggu {$weekNumber}",
                    'range_label' => "{$startDay}-{$endDay} " . $monthDate->translatedFormat('M Y'),
                    'transaction_count' => $weekTransactions->count(),
                    'total_revenue' => (float) $weekTransactions->sum('total_amount'),
                ];
            })
            ->values();

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
            'todayLabel' => $now->translatedFormat('d M Y'),
            'currentWeekLabel' => $weekStart->translatedFormat('d M') . ' - ' . $weekEnd->translatedFormat('d M Y'),
            'dailyRevenue' => $dailyRevenue,
            'dailyTransactionCount' => $dailyTransactionCount,
            'weeklyRevenue' => $weeklyRevenue,
            'weeklyTransactionCount' => $weeklyTransactionCount,
            'dailyBreakdown' => $dailyBreakdown,
            'weeklyBreakdown' => $weeklyBreakdown,
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

