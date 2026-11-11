@extends('layouts.app')

@section('title', 'Dashboard Admin - Kasir Figura POS')

@section('content')
<div x-data="{ sidebarOpen: false }" class="min-h-screen flex flex-col lg:flex-row bg-stone-50">
    @include('layouts.partials.admin-sidebar')

    <main class="flex-1 min-w-0 p-4 sm:p-6 lg:p-8 space-y-6">
        {{-- Walnut & Gold Gradient Header Banner + Month Filter & Excel Export --}}
        <section class="rounded-3xl bg-gradient-to-r from-stone-900 via-stone-800 to-amber-900 p-6 sm:p-8 text-white shadow-xl shadow-stone-900/15 border border-amber-500/20">
            <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-6">
                <div>
                    <span class="inline-flex items-center rounded-full bg-amber-500/20 border border-amber-400/30 px-3 py-1 text-xs font-semibold text-amber-200 backdrop-blur-sm">
                        Periode Laporan: {{ $monthLabel }}
                    </span>
                    <h1 class="mt-3 text-2xl sm:text-3xl font-extrabold tracking-tight">Dashboard Keuangan &amp; Penjualan</h1>
                    <p class="mt-1 text-sm text-stone-300">
                        Pantau omzet mingguan, bulanan, produk terlaris, dan arsip pembukuan per bulan.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                        <input type="month"
                               name="month"
                               value="{{ $selectedMonth }}"
                               onchange="this.form.submit()"
                               style="color-scheme: dark;"
                               class="min-h-[44px] flex-1 sm:flex-initial rounded-xl border border-amber-400/30 bg-white/10 px-3.5 py-2.5 text-sm font-semibold text-white backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-400/60 cursor-pointer">

                        @if ($selectedMonth !== $currentMonth)
                            <a href="{{ route('admin.dashboard') }}"
                               class="min-h-[44px] inline-flex items-center rounded-xl bg-white/15 hover:bg-white/25 border border-white/25 px-3.5 py-2.5 text-xs font-semibold text-white transition">
                                Reset ke Bulan Ini
                            </a>
                        @endif
                    </form>

                    <a href="{{ route('admin.reports.export', ['month' => $selectedMonth]) }}"
                       class="w-full sm:w-auto min-h-[44px] inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-stone-950 px-4 py-2.5 text-sm font-bold shadow-md transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export Excel (.xlsx)
                    </a>
                </div>
            </div>
        </section>

        {{-- Revenue Widgets --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
            <div class="rounded-2xl bg-gradient-to-br from-amber-700 via-amber-600 to-orange-600 p-5 text-white shadow-lg shadow-amber-700/20">
                <p class="text-xs font-semibold uppercase tracking-wider text-amber-100">Penghasilan 1 Minggu Terakhir</p>
                <p class="mt-2 text-2xl font-extrabold">Rp {{ number_format($weeklyRevenue, 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-amber-100/90">Akumulasi 7 hari terakhir</p>
            </div>

            <div class="rounded-2xl bg-white p-5 border border-stone-200/80 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Penghasilan 1 Bulan Terakhir</p>
                <p class="mt-2 text-2xl font-extrabold text-amber-700">Rp {{ number_format($monthlyRollingRevenue, 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-stone-500">Akumulasi 30 hari terakhir</p>
            </div>

            <div class="rounded-2xl bg-white p-5 border border-stone-200/80 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Penghasilan ({{ $monthLabel }})</p>
                <p class="mt-2 text-2xl font-extrabold text-stone-900">Rp {{ number_format($filteredRevenue, 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-stone-500">Sesuai filter bulan aktif</p>
            </div>

            <div class="rounded-2xl bg-white p-5 border border-stone-200/80 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Volume ({{ $monthLabel }})</p>
                <p class="mt-2 text-2xl font-extrabold text-stone-900">{{ $filteredTransactionCount }} Transaksi</p>
                <p class="mt-1 text-xs text-stone-500">{{ $filteredUnitsSold }} unit figura terjual</p>
            </div>
        </section>

        {{-- Top Selling Frames & Monthly Transactions --}}
        <section class="grid grid-cols-1 xl:grid-cols-12 gap-6">
            {{-- Top Selling Products Card --}}
            <div class="xl:col-span-5 rounded-2xl bg-white border border-stone-200/80 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-stone-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-stone-900">Figura Paling Banyak Terjual</h2>
                        <p class="text-xs text-stone-500">Periode {{ $monthLabel }}</p>
                    </div>
                </div>

                <div class="p-6 space-y-4">
                    @forelse ($topProducts as $index => $item)
                        @php
                            $maxQty = max(1, (int) ($topProducts->first()->total_qty ?? 1));
                            $percentage = min(100, round(((int) $item->total_qty / $maxQty) * 100));
                        @endphp
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between gap-2 text-sm">
                                <div class="min-w-0">
                                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-amber-100 text-xs font-bold text-amber-800 mr-1.5">
                                        {{ $index + 1 }}
                                    </span>
                                    <span class="font-semibold text-stone-800">{{ $item->product_name }}</span>
                                    <span class="text-xs text-stone-500">({{ $item->product_size }})</span>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="font-bold text-amber-700">{{ $item->total_qty }} pcs</span>
                                </div>
                            </div>
                            <div class="h-2 w-full rounded-full bg-stone-100 overflow-hidden">
                                <div class="h-full rounded-full bg-gradient-to-r from-amber-600 to-orange-500"
                                     style="width: {{ $percentage }}%"></div>
                            </div>
                            <p class="text-xs text-stone-500 text-right">
                                Kontribusi: Rp {{ number_format($item->total_sales, 0, ',', '.') }}
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-stone-500 text-center py-8">Belum ada penjualan figura pada bulan {{ $monthLabel }}.</p>
                    @endforelse
                </div>
            </div>

            {{-- Monthly Transactions Table --}}
            <div class="xl:col-span-7 rounded-2xl bg-white border border-stone-200/80 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-stone-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-stone-900">Riwayat Transaksi</h2>
                        <p class="text-xs text-stone-500">Menampilkan semua transaksi bulan {{ $monthLabel }}</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-amber-50/70 text-xs uppercase text-amber-900 font-semibold">
                            <tr>
                                <th class="px-5 py-3">Invoice</th>
                                <th class="px-5 py-3">Tanggal</th>
                                <th class="px-5 py-3">Kasir</th>
                                <th class="px-5 py-3">Metode</th>
                                <th class="px-5 py-3">Item</th>
                                <th class="px-5 py-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @forelse ($transactions as $trx)
                                <tr class="hover:bg-amber-50/30 transition">
                                    <td class="px-5 py-3.5 font-mono text-xs font-bold text-stone-800">
                                        {{ $trx->invoice_number }}
                                    </td>
                                    <td class="px-5 py-3.5 text-xs text-stone-500 whitespace-nowrap">
                                        {{ $trx->created_at->format('d M Y, H:i') }}
                                    </td>
                                    <td class="px-5 py-3.5 text-xs font-medium text-stone-700">
                                        {{ $trx->user?->name ?? '-' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-xs whitespace-nowrap">
                                        <span class="inline-flex rounded-lg bg-amber-50 border border-amber-200/70 px-2 py-0.5 font-semibold text-amber-800">
                                            {{ $trx->payment_method_label }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-xs text-stone-600">
                                        @foreach ($trx->details as $detail)
                                            <div>{{ $detail->product?->name ?? 'Produk Dihapus' }} ({{ $detail->product?->size }}) &times; {{ $detail->quantity }}</div>
                                        @endforeach
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-bold text-amber-700 whitespace-nowrap">
                                        Rp {{ number_format($trx->total_amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-10 text-center text-sm text-stone-500">
                                        Belum ada data transaksi pada bulan {{ $monthLabel }}.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</div>
@endsection
