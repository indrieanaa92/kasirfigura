@extends('layouts.app')

@section('title', 'Kasir POS - Inul Wijaya Pigura')

@section('content')
<style>
    .receipt-paper {
        font-variant-numeric: tabular-nums;
    }

    @media print {
        @page {
            margin: 4mm;
        }

        html, body {
            margin: 0 !important;
            padding: 0 !important;
            background: #ffffff !important;
        }

        .no-print {
            display: none !important;
        }

        #receipt-modal-backdrop {
            position: static !important;
            display: block !important;
            background: transparent !important;
            padding: 0 !important;
            margin: 0 !important;
            inset: auto !important;
        }

        #printable-receipt {
            width: 100% !important;
            max-width: 72mm !important;
            margin: 0 auto !important;
            padding: 2mm 1mm !important;
            box-shadow: none !important;
            border: none !important;
            border-radius: 0 !important;
            background: #ffffff !important;
            color: #000000 !important;
            font-size: 11px !important;
            line-height: 1.35 !important;
        }

        #printable-receipt.receipt-58 {
            max-width: 50mm !important;
            font-size: 10px !important;
        }

        #printable-receipt * {
            color: #000000 !important;
            background: transparent !important;
        }

        #printable-receipt .receipt-divider {
            border-color: #000000 !important;
        }
    }
</style>

<div x-data="posApp(@js($products), @js($customers), @js($recentTransactions), @js($paymentMethods), @js($customerTypes))"
     class="min-h-screen flex flex-col bg-stone-100/70">

    {{-- Sticky Top Header --}}
    <header class="sticky top-0 z-30 bg-gradient-to-r from-stone-950 via-stone-900 to-amber-900 text-white shadow-lg shadow-stone-900/15 no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-md ring-1 ring-amber-300/30">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm4 4h8v6H8V9z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold tracking-tight">Inul Wijaya Pigura</h1>
                    <p class="text-xs text-amber-200/90">Kasir POS &bull; Kasir Aktif: <span class="font-semibold text-white">{{ auth()->user()->name }}</span></p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                <span class="hidden sm:inline-flex items-center gap-2 rounded-full bg-amber-500/15 border border-amber-400/25 px-3 py-1.5 text-xs font-semibold text-amber-100 backdrop-blur-sm">
                    <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span x-text="products.length"></span> Varian Figura Siap
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="min-h-[40px] rounded-xl bg-white/10 hover:bg-white/20 active:bg-white/25 border border-white/15 px-4 py-2 text-xs font-semibold text-white transition cursor-pointer">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </header>

    {{-- Main Container Kasir --}}
    <main class="max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6 no-print">

        {{-- Toast / Notice Feedback Pill --}}
        <div x-show="cartNotice"
             x-cloak
             x-transition
             class="fixed top-20 right-4 z-50 rounded-xl bg-stone-900/95 text-white border border-amber-400/40 px-4 py-2.5 text-xs font-semibold shadow-xl backdrop-blur-sm flex items-center gap-2">
            <svg class="h-4 w-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span x-text="cartNotice"></span>
        </div>

        {{-- CONTAINER UTAMA TRANSAKSI KASIR --}}
        <div class="rounded-3xl bg-white border border-stone-200/90 shadow-xl shadow-stone-900/5 overflow-hidden">
            {{-- Container Header --}}
            <div class="bg-gradient-to-r from-stone-900 via-stone-800 to-amber-900 px-6 py-4 text-white flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-xl bg-amber-500/20 border border-amber-400/30 flex items-center justify-center text-amber-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold">Terminal Transaksi Kasir</h2>
                        <p class="text-xs text-amber-200/90">Formulir pemesanan &amp; perhitungan otomatis harga Member / Umum</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button"
                            @click="resetForm()"
                            class="min-h-[36px] rounded-xl bg-white/10 hover:bg-white/20 active:bg-white/25 border border-white/20 px-3.5 py-1.5 text-xs font-semibold text-amber-100 transition cursor-pointer">
                        Kosongkan Form
                    </button>
                </div>
            </div>

            <div class="p-6 sm:p-8 space-y-6">

                {{-- Alert Error --}}
                <div x-show="errorMessage" x-cloak
                     class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-medium text-rose-700 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 shrink-0 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span x-text="errorMessage"></span>
                    </div>
                    <button type="button" @click="errorMessage = ''" class="text-rose-500 hover:text-rose-800 font-bold text-base">&times;</button>
                </div>

                {{-- SECTION 1: IDENTITAS PEMBELI & DETEKSI STATUS MEMBER --}}
                <div class="rounded-2xl border border-stone-200/90 bg-stone-50/80 p-5 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <label for="pos-customer-name" class="block text-xs font-bold uppercase tracking-wider text-stone-700">
                                1. Nama Pembeli / Pelanggan
                            </label>
                            <p class="text-[11px] text-stone-500">
                                Ketik nama pembeli atau pilih dari pelanggan terdaftar. Status Member ditentukan oleh Admin.
                            </p>
                        </div>

                        {{-- Real-time Status Badge --}}
                        <div>
                            <template x-if="isMember">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-amber-500 to-orange-500 px-3.5 py-1 text-xs font-extrabold text-white shadow-sm ring-1 ring-amber-300/50">
                                    <span>⭐</span> STATUS: MEMBER TERDAFTAR (HARGA MEMBER)
                                </span>
                            </template>
                            <template x-if="!isMember && customerName.trim() !== ''">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-stone-200 text-stone-800 px-3.5 py-1 text-xs font-bold">
                                    <span>👤</span> STATUS: UMUM (HARGA NORMAL)
                                </span>
                            </template>
                            <template x-if="customerName.trim() === ''">
                                <span class="inline-flex items-center rounded-full bg-stone-100 text-stone-500 px-3 py-1 text-[11px] font-semibold border border-stone-200">
                                    Menunggu Nama Pembeli...
                                </span>
                            </template>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                        <div class="md:col-span-8 relative">
                            <input id="pos-customer-name"
                                   type="text"
                                   x-model="customerName"
                                   list="registered-customers-list"
                                   placeholder="Ketik nama pembeli di sini..."
                                   class="w-full min-h-[46px] rounded-xl border border-stone-300 bg-white px-4 py-2.5 text-sm font-semibold text-stone-900 focus:border-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-600/20 shadow-xs">

                            {{-- Datalist for fast browser autocomplete of all registered customers --}}
                            <datalist id="registered-customers-list">
                                <template x-for="c in customers" :key="c.id">
                                    <option :value="c.name" x-text="c.is_member ? c.name + ' (Member)' : c.name + ' (Umum)'"></option>
                                </template>
                            </datalist>
                        </div>

                        <div class="md:col-span-4">
                            <select @change="selectCustomerFromDropdown($event.target.value); $event.target.value = ''"
                                    class="w-full min-h-[46px] rounded-xl border border-stone-300 bg-white px-3 py-2 text-xs font-semibold text-stone-700 focus:border-amber-600 focus:outline-none shadow-xs">
                                <option value="">-- Atau Pilih Cepat Pelanggan --</option>
                                <optgroup label="⭐ Member Terdaftar">
                                    <template x-for="c in memberCustomers" :key="'m-' + c.id">
                                        <option :value="c.name" x-text="c.name + ' (Member)'"></option>
                                    </template>
                                </optgroup>
                                <optgroup label="👤 Pelanggan Umum">
                                    <template x-for="c in umumCustomers" :key="'u-' + c.id">
                                        <option :value="c.name" x-text="c.name + ' (Umum)'"></option>
                                    </template>
                                </optgroup>
                            </select>
                        </div>
                    </div>

                    {{-- Member Status Notice --}}
                    <div class="text-xs">
                        <template x-if="isMember">
                            <p class="text-amber-800 font-semibold flex items-center gap-1.5">
                                <svg class="h-4 w-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Terverifikasi sebagai Member oleh Admin. Seluruh item otomatis menggunakan <strong>Harga Member</strong>.</span>
                            </p>
                        </template>
                        <template x-if="!isMember && customerName.trim() !== ''">
                            <p class="text-stone-600 flex items-center gap-1.5">
                                <svg class="h-4 w-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Pembeli belum berstatus Member. Diterapkan <strong>Harga Umum</strong> normal. (Admin dapat menjadikannya Member di menu Kelola Member).</span>
                            </p>
                        </template>
                    </div>
                </div>

                {{-- SECTION 2: TABEL DAFTAR PRODUK FIGURA (TANPA CARD) --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-stone-700">
                                2. Rincian Pembelian Produk Figura
                            </h3>
                            <p class="text-[11px] text-stone-500">
                                Cari produk, tentukan jumlah (quantity), dan harga satuan akan otomatis disesuaikan dengan status pembeli.
                            </p>
                        </div>

                        <button type="button"
                                @click="addRow()"
                                class="min-h-[40px] inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-amber-700 to-orange-600 hover:from-amber-800 hover:to-orange-700 px-4 py-2 text-xs font-bold text-white shadow-md shadow-amber-700/20 transition cursor-pointer">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>+ Tambah Baris Produk</span>
                        </button>
                    </div>

                    {{-- Table Container --}}
                    <div class="rounded-2xl border border-stone-200/90 overflow-hidden shadow-xs">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-amber-50/80 text-xs uppercase text-amber-900 font-bold border-b border-amber-200/70">
                                    <tr>
                                        <th class="px-4 py-3.5 w-12 text-center">No</th>
                                        <th class="px-4 py-3.5 min-w-[280px]">Nama Produk Figura &amp; Ukuran</th>
                                        <th class="px-4 py-3.5 w-28 text-center">Stok</th>
                                        <th class="px-4 py-3.5 w-36 text-center">Jumlah (Qty)</th>
                                        <th class="px-4 py-3.5 w-40 text-right">Harga Satuan</th>
                                        <th class="px-4 py-3.5 w-44 text-right">Total Pembelian</th>
                                        <th class="px-4 py-3.5 w-16 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-stone-100">
                                    <template x-for="(row, index) in items" :key="row.row_id">
                                        <tr class="hover:bg-amber-50/20 transition">
                                            {{-- Row Number --}}
                                            <td class="px-4 py-3.5 text-center font-bold text-stone-500 text-xs" x-text="index + 1"></td>

                                            {{-- Product Selector --}}
                                            <td class="px-4 py-3.5">
                                                <select x-model.number="row.product_id"
                                                        @change="onProductSelected(row)"
                                                        class="w-full min-h-[42px] rounded-xl border border-stone-200 bg-white px-3 py-2 text-xs font-semibold text-stone-800 focus:border-amber-600 focus:outline-none">
                                                    <option value="">-- Pilih / Cari Produk Figura --</option>
                                                    <template x-for="p in products" :key="p.id">
                                                        <option :value="p.id"
                                                                :disabled="p.stock <= 0 && p.id !== row.product_id"
                                                                x-text="p.name + ' (' + p.size + ') — Stok: ' + p.stock + (p.stock <= 0 ? ' [Habis]' : '')">
                                                        </option>
                                                    </template>
                                                </select>
                                                <template x-if="row.product">
                                                    <div class="mt-1 flex items-center gap-2 text-[11px] text-stone-500">
                                                        <span>Ukuran: <strong class="text-stone-800" x-text="row.product.size"></strong></span>
                                                        <span>&bull;</span>
                                                        <span x-show="isMember" class="text-amber-800 font-bold">Harga Member Aktif</span>
                                                        <span x-show="!isMember" class="text-stone-600">Harga Umum</span>
                                                    </div>
                                                </template>
                                            </td>

                                            {{-- Stock Badge --}}
                                            <td class="px-4 py-3.5 text-center">
                                                <template x-if="row.product">
                                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold"
                                                          :class="row.product.stock <= 0 ? 'bg-rose-50 text-rose-700 border border-rose-200' : (row.product.stock <= 5 ? 'bg-orange-50 text-orange-700 border border-orange-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200')"
                                                          x-text="row.product.stock + ' unit'"></span>
                                                </template>
                                                <template x-if="!row.product">
                                                    <span class="text-xs text-stone-400">-</span>
                                                </template>
                                            </td>

                                            {{-- Quantity Input with +/- --}}
                                            <td class="px-4 py-3.5">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    <button type="button"
                                                            @click="decrementQty(row)"
                                                            :disabled="!row.product || row.quantity <= 1"
                                                            class="h-8 w-8 rounded-lg border border-stone-200 bg-white hover:bg-stone-100 disabled:opacity-40 font-bold text-stone-700 flex items-center justify-center cursor-pointer disabled:cursor-not-allowed">
                                                        -
                                                    </button>
                                                    <input type="number"
                                                           min="1"
                                                           :max="row.product ? row.product.stock : 999"
                                                           x-model.number="row.quantity"
                                                           @input="validateRowQty(row)"
                                                           :disabled="!row.product"
                                                           class="w-16 h-8 text-center rounded-lg border border-stone-200 bg-white text-xs font-bold text-stone-900 focus:border-amber-600 focus:outline-none">
                                                    <button type="button"
                                                            @click="incrementQty(row)"
                                                            :disabled="!row.product || row.quantity >= row.product.stock"
                                                            class="h-8 w-8 rounded-lg border border-stone-200 bg-white hover:bg-stone-100 disabled:opacity-40 font-bold text-stone-700 flex items-center justify-center cursor-pointer disabled:cursor-not-allowed">
                                                        +
                                                    </button>
                                                </div>
                                            </td>

                                            {{-- Unit Price --}}
                                            <td class="px-4 py-3.5 text-right font-mono text-xs">
                                                <template x-if="row.product">
                                                    <div>
                                                        <span class="font-bold text-stone-900" x-text="formatRupiah(getRowUnitPrice(row))"></span>
                                                        <span class="block text-[10px] font-semibold"
                                                              :class="isMember ? 'text-amber-800' : 'text-stone-500'"
                                                              x-text="isMember ? 'Harga Member' : 'Harga Umum'"></span>
                                                    </div>
                                                </template>
                                                <template x-if="!row.product">
                                                    <span class="text-stone-400">-</span>
                                                </template>
                                            </td>

                                            {{-- Subtotal / Total Pembelian Item --}}
                                            <td class="px-4 py-3.5 text-right font-mono text-xs font-bold text-amber-700">
                                                <template x-if="row.product">
                                                    <span x-text="formatRupiah(getRowSubtotal(row))"></span>
                                                </template>
                                                <template x-if="!row.product">
                                                    <span class="text-stone-400">-</span>
                                                </template>
                                            </td>

                                            {{-- Delete Button --}}
                                            <td class="px-4 py-3.5 text-center">
                                                <button type="button"
                                                        @click="removeRow(row.row_id)"
                                                        class="h-8 w-8 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 transition flex items-center justify-center cursor-pointer mx-auto"
                                                        title="Hapus baris ini">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Add Row Shortcut --}}
                    <div class="flex items-center justify-between pt-1">
                        <button type="button"
                                @click="addRow()"
                                class="text-xs font-bold text-amber-700 hover:text-amber-800 underline flex items-center gap-1 cursor-pointer">
                            <span>+ Tambah Baris Figura Lain</span>
                        </button>
                        <span class="text-xs text-stone-500">
                            Total Item Dipilih: <strong class="text-stone-800" x-text="totalQuantity"></strong> unit
                        </span>
                    </div>
                </div>

                {{-- SECTION 3: TOTAL PEMBELIAN & PEMBAYARAN CONTAINER --}}
                <div class="rounded-2xl border border-amber-200/80 bg-gradient-to-br from-amber-50/60 via-white to-orange-50/40 p-6 space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                        {{-- Left Column: Ringkasan Total & Metode Pembayaran --}}
                        <div class="lg:col-span-6 space-y-5">
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-stone-700">
                                    3. Ringkasan Total Pembelian
                                </h3>
                                <div class="mt-3 p-4 rounded-2xl bg-white border border-amber-200/80 shadow-xs space-y-2">
                                    <div class="flex items-center justify-between text-xs text-stone-600">
                                        <span>Status Harga Pembeli:</span>
                                        <span class="font-bold" :class="isMember ? 'text-amber-800' : 'text-stone-800'"
                                              x-text="isMember ? '⭐ MEMBER AKTIF (Harga Khusus)' : '👤 UMUM (Harga Normal)'"></span>
                                    </div>
                                    <template x-if="isMember && memberSavings > 0">
                                        <div class="flex items-center justify-between text-xs text-emerald-700 bg-emerald-50 px-2.5 py-1.5 rounded-lg border border-emerald-200/80">
                                            <span class="font-semibold">Potongan Hemat Member:</span>
                                            <span class="font-bold font-mono" x-text="'- ' + formatRupiah(memberSavings)"></span>
                                        </div>
                                    </template>
                                    <div class="pt-2 border-t border-stone-100 flex items-baseline justify-between">
                                        <div>
                                            <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Total Pembelian</span>
                                            <p class="text-[11px] text-stone-400"><span x-text="totalQuantity"></span> unit figura</p>
                                        </div>
                                        <span class="text-2xl sm:text-3xl font-black font-mono text-amber-700"
                                              x-text="formatRupiah(grandTotal)"></span>
                                    </div>
                                </div>
                            </div>

                            {{-- Payment Method Selection --}}
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-2">
                                    Metode Pembayaran
                                </label>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    <template x-for="(label, key) in paymentMethods" :key="key">
                                        <button type="button"
                                                @click="paymentMethod = key"
                                                :class="paymentMethod === key
                                                    ? 'border-amber-600 bg-gradient-to-r from-amber-700 to-orange-600 text-white shadow-sm font-bold'
                                                    : 'border-stone-200 bg-white text-stone-700 hover:bg-amber-50 hover:border-amber-300 font-semibold'"
                                                class="min-h-[44px] rounded-xl border p-2 text-xs transition text-center cursor-pointer"
                                                x-text="label"></button>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Right Column: Cash Tendered & Selesaikan Pembayaran --}}
                        <div class="lg:col-span-6 space-y-4">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-stone-700">
                                4. Penyelesaian Pembayaran
                            </h3>

                            {{-- Cash Payment Inputs --}}
                            <div x-show="paymentMethod === 'cash'" class="p-4 rounded-2xl bg-white border border-stone-200 shadow-xs space-y-3">
                                <div>
                                    <label class="block text-xs font-semibold text-stone-700 mb-1">
                                        Nominal Uang Diterima (Rp)
                                    </label>
                                    <input type="number"
                                           min="0"
                                           step="1000"
                                           x-model.number="cashReceived"
                                           placeholder="Masukkan uang yang diterima..."
                                           class="w-full min-h-[44px] rounded-xl border border-stone-300 bg-stone-50 px-3.5 py-2 text-base font-bold font-mono text-stone-900 focus:border-amber-600 focus:bg-white focus:outline-none">
                                </div>

                                {{-- Quick Money Buttons --}}
                                <div class="flex flex-wrap gap-1.5 text-xs">
                                    <button type="button"
                                            @click="cashReceived = grandTotal"
                                            class="rounded-lg bg-stone-100 hover:bg-stone-200 px-2.5 py-1 text-stone-700 font-semibold transition cursor-pointer">
                                        Uang Pas
                                    </button>
                                    <button type="button"
                                            @click="cashReceived = 50000"
                                            class="rounded-lg bg-stone-100 hover:bg-stone-200 px-2.5 py-1 text-stone-700 font-semibold transition cursor-pointer">
                                        50rb
                                    </button>
                                    <button type="button"
                                            @click="cashReceived = 100000"
                                            class="rounded-lg bg-stone-100 hover:bg-stone-200 px-2.5 py-1 text-stone-700 font-semibold transition cursor-pointer">
                                        100rb
                                    </button>
                                    <button type="button"
                                            @click="cashReceived = 200000"
                                            class="rounded-lg bg-stone-100 hover:bg-stone-200 px-2.5 py-1 text-stone-700 font-semibold transition cursor-pointer">
                                        200rb
                                    </button>
                                    <button type="button"
                                            @click="cashReceived = 500000"
                                            class="rounded-lg bg-stone-100 hover:bg-stone-200 px-2.5 py-1 text-stone-700 font-semibold transition cursor-pointer">
                                        500rb
                                    </button>
                                </div>

                                {{-- Change Amount Calculation --}}
                                <div class="pt-2 border-t border-stone-100 flex items-center justify-between">
                                    <span class="text-xs font-semibold text-stone-600">Kembalian:</span>
                                    <span class="text-base font-bold font-mono"
                                          :class="changeAmount >= 0 ? 'text-emerald-700' : 'text-rose-600'"
                                          x-text="changeAmount >= 0 ? formatRupiah(changeAmount) : 'Kurang ' + formatRupiah(Math.abs(changeAmount))"></span>
                                </div>
                            </div>

                            {{-- Non-Cash Payment Notice --}}
                            <div x-show="paymentMethod !== 'cash'" class="p-4 rounded-2xl bg-white border border-stone-200 shadow-xs text-xs space-y-1">
                                <p class="font-bold text-stone-800">Pembayaran Non-Tunai:</p>
                                <p class="text-stone-600">
                                    Total pembayaran disesuaikan otomatis tepat sebesar <strong class="text-amber-700" x-text="formatRupiah(grandTotal)"></strong> tanpa uang kembalian.
                                </p>
                            </div>

                            {{-- Submit Checkout Button --}}
                            <button type="button"
                                    @click="submitCheckout()"
                                    :disabled="!canCheckout || isSubmitting"
                                    class="w-full min-h-[52px] rounded-2xl bg-gradient-to-r from-amber-700 via-amber-600 to-orange-600 hover:from-amber-800 hover:via-amber-700 hover:to-orange-700 active:scale-[0.99] disabled:from-stone-300 disabled:to-stone-400 disabled:text-stone-500 py-3.5 px-6 text-sm font-extrabold text-white shadow-lg shadow-amber-700/25 transition cursor-pointer disabled:cursor-not-allowed select-none">
                                <span x-show="!isSubmitting">PROSES PEMBAYARAN &amp; CETAK STRUK</span>
                                <span x-show="isSubmitting" class="inline-flex items-center gap-2">
                                    <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    Memproses Transaksi...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- RIWAYAT TRANSAKSI TERAKHIR KASIR --}}
        <div class="rounded-3xl bg-white border border-stone-200/90 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-stone-900">Riwayat Transaksi Kasir Hari Ini</h2>
                    <p class="text-xs text-stone-500">Daftar transaksi yang Anda selesaikan pada sesi ini</p>
                </div>
            </div>

            <div x-show="recentTransactions.length === 0" class="text-xs text-stone-400 py-4 text-center">
                Belum ada transaksi pada sesi ini.
            </div>

            <div class="space-y-2.5" x-show="recentTransactions.length > 0">
                <template x-for="trx in recentTransactions" :key="trx.id">
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-stone-50 px-4 py-3 border border-stone-200/70 text-xs">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-bold font-mono text-stone-900" x-text="trx.invoice_number"></span>
                                <span class="rounded-md bg-amber-100 px-2 py-0.5 text-[11px] font-bold text-amber-800"
                                      x-text="trx.payment_method_label || formatPaymentLabel(trx.payment_method)"></span>
                                <span class="rounded-md px-2 py-0.5 text-[11px] font-bold"
                                      :class="(trx.customer_type === 'member' || trx.customer_type === 'sales' || trx.customer_type === 'agen') ? 'bg-gradient-to-r from-amber-500 to-orange-500 text-white' : 'bg-stone-200 text-stone-700'"
                                      x-text="trx.customer_type_label || (trx.customer_type === 'member' ? 'Member' : 'Umum')"></span>
                            </div>
                            <p class="text-stone-600 mt-1">
                                Pembeli: <strong class="text-stone-900" x-text="trx.customer_name || '-'"></strong>
                                &bull; <span class="text-stone-400" x-text="formatDate(trx.created_at)"></span>
                            </p>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="font-bold font-mono text-base text-amber-700" x-text="formatRupiah(trx.total_amount)"></span>
                            <button type="button"
                                    @click="openReceipt(trx)"
                                    class="min-h-[38px] rounded-xl border border-stone-300 bg-white hover:bg-amber-50 hover:border-amber-400 active:bg-amber-100 px-3.5 py-1.5 text-xs font-semibold text-stone-700 transition cursor-pointer">
                                Cetak Struk
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

    </main>

    {{-- MODAL STRUK CETAK THERMAL (58mm / 80mm) --}}
    <div id="receipt-modal-backdrop"
         x-show="lastReceipt"
         x-cloak
         class="fixed inset-0 z-50 flex items-start sm:items-center justify-center bg-stone-950/60 backdrop-blur-xs p-4 overflow-y-auto">
        <div class="w-full max-w-md my-auto space-y-3">
            {{-- Screen-only Top Bar (Width Switcher + Status) --}}
            <div class="flex items-center justify-between rounded-2xl bg-stone-900 px-4 py-2.5 text-white shadow-lg no-print">
                <span class="inline-flex items-center gap-2 text-xs font-semibold text-emerald-300">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Struk Siap Dicetak
                </span>
                <div class="inline-flex items-center gap-1 rounded-lg bg-white/10 p-0.5 text-[11px]">
                    <button type="button"
                            @click="receiptWidth = '58mm'"
                            :class="receiptWidth === '58mm' ? 'bg-amber-500 text-stone-950 font-bold' : 'text-stone-200'"
                            class="rounded-md px-2 py-1 transition cursor-pointer">58mm</button>
                    <button type="button"
                            @click="receiptWidth = '80mm'"
                            :class="receiptWidth === '80mm' ? 'bg-amber-500 text-stone-950 font-bold' : 'text-stone-200'"
                            class="rounded-md px-2 py-1 transition cursor-pointer">80mm</button>
                </div>
            </div>

            {{-- Clean Thermal-Ready Receipt Card --}}
            <div id="printable-receipt"
                 :class="receiptWidth === '58mm' ? 'receipt-58 max-w-[260px]' : 'receipt-80 max-w-[340px]'"
                 class="receipt-paper mx-auto w-full rounded-2xl bg-white p-5 text-stone-900 shadow-2xl border border-stone-200 font-mono text-xs leading-relaxed">

                {{-- Store Header & Address --}}
                <div class="text-center border-b border-dashed border-stone-400 receipt-divider pb-3">
                    <h3 class="text-sm font-extrabold tracking-wider uppercase text-stone-900">{{ config('store.name') }}</h3>
                    <p class="mt-1 text-[11px] text-stone-600 leading-snug">{{ config('store.address') }}</p>
                    <p class="mt-0.5 text-[11px] text-stone-600">Telp: {{ config('store.phone') }}</p>
                </div>

                {{-- Transaction Metadata --}}
                <div class="py-2.5 border-b border-dashed border-stone-400 receipt-divider space-y-1 text-[11px]">
                    <div class="flex justify-between gap-2">
                        <span class="text-stone-500 shrink-0">No. Struk</span>
                        <span class="font-bold text-stone-900 text-right break-all" x-text="lastReceipt?.invoice_number"></span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-stone-500 shrink-0">Tanggal</span>
                        <span class="text-right" x-text="formatDate(lastReceipt?.created_at)"></span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-stone-500 shrink-0">Kasir</span>
                        <span class="font-bold text-stone-900 text-right" x-text="lastReceipt?.user?.name || '{{ auth()->user()->name }}'"></span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-stone-500 shrink-0">Pembeli</span>
                        <span class="font-bold text-stone-900 text-right break-words" x-text="lastReceipt?.customer_name || '-'"></span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-stone-500 shrink-0">Status</span>
                        <span class="font-bold text-stone-900 text-right" x-text="lastReceipt?.customer_type_label || (lastReceipt?.customer_type === 'member' ? 'Member' : 'Umum')"></span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-stone-500 shrink-0">Pembayaran</span>
                        <span class="font-bold text-stone-900 text-right" x-text="lastReceipt?.payment_method_label || formatPaymentLabel(lastReceipt?.payment_method)"></span>
                    </div>
                </div>

                {{-- Purchased Items --}}
                <div class="py-2.5 border-b border-dashed border-stone-400 receipt-divider space-y-2">
                    <template x-for="detail in (lastReceipt?.details || [])" :key="detail.id">
                        <div class="text-[11px] leading-snug">
                            <div class="font-bold text-stone-900" x-text="detail.product?.name || 'Figura'"></div>
                            <div class="flex justify-between text-stone-600 mt-0.5">
                                <span>
                                    <span x-text="detail.product?.size || '-'"></span> &times; <span x-text="detail.quantity"></span>
                                    <span class="text-stone-400">@ <span x-text="formatRupiah(detail.unit_price || ((detail.subtotal || 0) / (detail.quantity || 1)))"></span></span>
                                </span>
                                <span class="font-semibold text-stone-900 font-mono" x-text="formatRupiah(detail.subtotal)"></span>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Payment Financials --}}
                <div class="py-2.5 border-b border-dashed border-stone-400 receipt-divider space-y-1 text-xs">
                    <div class="flex justify-between font-extrabold text-stone-900 pt-0.5">
                        <span>TOTAL TAGIHAN</span>
                        <span class="font-mono text-sm" x-text="formatRupiah(lastReceipt?.total_amount)"></span>
                    </div>
                    <div class="flex justify-between text-stone-700 pt-1">
                        <span>DITERIMA</span>
                        <span class="font-mono" x-text="formatRupiah(lastReceipt?.paid_amount)"></span>
                    </div>
                    <div class="flex justify-between text-stone-700">
                        <span>KEMBALIAN</span>
                        <span class="font-mono font-bold" x-text="formatRupiah(lastReceipt?.change_amount)"></span>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="pt-3 text-center text-[10px] text-stone-500 space-y-0.5">
                    <p class="font-bold text-stone-700">Terima Kasih Atas Kunjungan Anda</p>
                    <p>Barang yang sudah dibeli tidak dapat dikembalikan</p>
                </div>
            </div>

            {{-- Screen-only Action Buttons --}}
            <div class="mx-auto max-w-[340px] grid grid-cols-2 gap-2.5 no-print">
                <button type="button"
                        @click="printReceipt()"
                        class="min-h-[44px] w-full rounded-xl bg-stone-900 hover:bg-stone-800 py-2.5 text-xs font-bold text-white shadow-lg transition cursor-pointer">
                    Cetak Struk
                </button>
                <button type="button"
                        @click="lastReceipt = null"
                        class="min-h-[44px] w-full rounded-xl bg-gradient-to-r from-amber-700 to-orange-600 hover:from-amber-800 hover:to-orange-700 py-2.5 text-xs font-bold text-white shadow-lg transition cursor-pointer">
                    Transaksi Baru
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function posApp(initialProducts, initialCustomers, initialRecentTransactions, paymentMethods, customerTypes) {
        return {
            products: initialProducts || [],
            customers: initialCustomers || [],
            recentTransactions: initialRecentTransactions || [],
            paymentMethods: paymentMethods || { cash: 'Cash (Tunai)' },
            customerTypes: customerTypes || { member: 'Member', umum: 'Umum' },

            customerName: '',
            paymentMethod: 'cash',
            cashReceived: null,
            isSubmitting: false,
            errorMessage: '',
            cartNotice: '',
            cartNoticeTimer: null,
            lastReceipt: null,
            receiptWidth: '80mm',

            // Dynamic items container rows
            rowCounter: 1,
            items: [
                { row_id: 1, product_id: '', product: null, quantity: 1 }
            ],

            init() {
                // If products exist, initialize first row empty so cashier can pick immediately
            },

            get memberCustomers() {
                return this.customers.filter(c => Boolean(c.is_member));
            },

            get umumCustomers() {
                return this.customers.filter(c => !c.is_member);
            },

            // Auto-detect member status from customerName
            get matchedCustomer() {
                const name = this.customerName.trim().toLowerCase();
                if (!name) return null;
                return this.customers.find(c => c.name.toLowerCase() === name);
            },

            get isMember() {
                return Boolean(this.matchedCustomer && this.matchedCustomer.is_member);
            },

            get customerType() {
                return this.isMember ? 'member' : 'umum';
            },

            selectCustomerFromDropdown(name) {
                if (!name) return;
                this.customerName = name;
                if (this.isMember) {
                    this.showNotice(`Pelanggan ${name} terdeteksi sebagai MEMBER (Harga Member Aktif)`);
                } else {
                    this.showNotice(`Pelanggan ${name} (Harga Umum)`);
                }
            },

            addRow() {
                this.rowCounter++;
                this.items.push({
                    row_id: this.rowCounter,
                    product_id: '',
                    product: null,
                    quantity: 1
                });
            },

            removeRow(rowId) {
                if (this.items.length === 1) {
                    // Reset single row instead of empty array
                    this.items[0] = { row_id: this.rowCounter, product_id: '', product: null, quantity: 1 };
                    return;
                }
                this.items = this.items.filter(r => r.row_id !== rowId);
            },

            onProductSelected(row) {
                const product = this.products.find(p => Number(p.id) === Number(row.product_id));
                row.product = product || null;
                row.quantity = 1;
                if (product) {
                    this.showNotice(`${product.name} dipilih`);
                }
            },

            incrementQty(row) {
                if (!row.product) return;
                if (row.quantity < row.product.stock) {
                    row.quantity++;
                }
            },

            decrementQty(row) {
                if (!row.product) return;
                if (row.quantity > 1) {
                    row.quantity--;
                }
            },

            validateRowQty(row) {
                if (!row.product) return;
                if (row.quantity < 1) row.quantity = 1;
                if (row.quantity > row.product.stock) {
                    row.quantity = row.product.stock;
                    this.showNotice(`Maksimal stok ${row.product.name} adalah ${row.product.stock} unit`);
                }
            },

            getRowUnitPrice(row) {
                if (!row.product) return 0;
                if (this.isMember) {
                    return Number(row.product.price_member ?? row.product.price_sales ?? Math.round(Number(row.product.price) * 0.85));
                }
                return Number(row.product.price_umum ?? row.product.price ?? 0);
            },

            getRowSubtotal(row) {
                return this.getRowUnitPrice(row) * (row.quantity || 1);
            },

            get validItems() {
                return this.items.filter(r => r.product && r.product_id && r.quantity > 0);
            },

            get totalQuantity() {
                return this.validItems.reduce((sum, r) => sum + Number(r.quantity), 0);
            },

            get grandTotal() {
                return this.validItems.reduce((sum, r) => sum + this.getRowSubtotal(r), 0);
            },

            get memberSavings() {
                if (!this.isMember) return 0;
                const normalTotal = this.validItems.reduce((sum, r) => {
                    const normalPrice = Number(r.product.price_umum ?? r.product.price ?? 0);
                    return sum + (normalPrice * Number(r.quantity));
                }, 0);
                return Math.max(0, normalTotal - this.grandTotal);
            },

            get changeAmount() {
                if (this.paymentMethod !== 'cash') return 0;
                if (this.cashReceived === null || this.cashReceived === '') return 0;
                return Number(this.cashReceived) - this.grandTotal;
            },

            get canCheckout() {
                if (this.validItems.length === 0) return false;
                if (!this.customerName.trim()) return false;
                if (this.paymentMethod === 'cash') {
                    return Number(this.cashReceived) >= this.grandTotal;
                }
                return true;
            },

            showNotice(msg) {
                this.cartNotice = msg;
                if (this.cartNoticeTimer) clearTimeout(this.cartNoticeTimer);
                this.cartNoticeTimer = setTimeout(() => {
                    this.cartNotice = '';
                }, 3000);
            },

            resetForm() {
                this.customerName = '';
                this.items = [{ row_id: ++this.rowCounter, product_id: '', product: null, quantity: 1 }];
                this.paymentMethod = 'cash';
                this.cashReceived = null;
                this.errorMessage = '';
            },

            openReceipt(transaction) {
                this.lastReceipt = transaction;
            },

            printReceipt() {
                window.print();
            },

            async submitCheckout() {
                if (!this.canCheckout || this.isSubmitting) return;

                if (!this.customerName.trim()) {
                    this.errorMessage = 'Nama pembeli wajib diisi sebelum memproses pembayaran.';
                    return;
                }

                if (this.validItems.length === 0) {
                    this.errorMessage = 'Pilih minimal 1 produk figura terlebih dahulu.';
                    return;
                }

                if (this.paymentMethod === 'cash' && Number(this.cashReceived) < this.grandTotal) {
                    this.errorMessage = 'Nominal uang tunai yang diterima kurang dari total tagihan.';
                    return;
                }

                this.isSubmitting = true;
                this.errorMessage = '';

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const payloadItems = this.validItems.map(item => ({
                        product_id: Number(item.product_id),
                        quantity: Number(item.quantity)
                    }));

                    const response = await fetch('{{ route("pos.checkout") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({
                            customer_name: this.customerName.trim(),
                            customer_type: this.customerType,
                            payment_method: this.paymentMethod,
                            paid_amount: this.paymentMethod === 'cash' ? Number(this.cashReceived) : this.grandTotal,
                            items: payloadItems,
                        }),
                    });

                    const payload = await response.json();

                    if (!response.ok) {
                        const firstError = payload?.errors
                            ? Object.values(payload.errors)[0]?.[0]
                            : null;
                        this.errorMessage = firstError || payload?.message || 'Gagal memproses transaksi kasir.';
                        this.isSubmitting = false;
                        return;
                    }

                    // Success: update state and show receipt modal
                    this.lastReceipt = payload.transaction;
                    if (Array.isArray(payload.products)) {
                        this.products = payload.products;
                    }
                    if (Array.isArray(payload.customers)) {
                        this.customers = payload.customers;
                    }

                    // Prepend to recent transactions
                    this.recentTransactions.unshift(payload.transaction);
                    if (this.recentTransactions.length > 8) {
                        this.recentTransactions.pop();
                    }

                    // Reset order entry
                    this.resetForm();
                    this.showNotice('Transaksi berhasil diselesaikan!');
                } catch (error) {
                    this.errorMessage = 'Terjadi gangguan jaringan atau sesi kedaluwarsa. Silakan muat ulang halaman.';
                } finally {
                    this.isSubmitting = false;
                }
            },

            formatRupiah(amount) {
                const numeric = Number(amount) || 0;
                return 'Rp ' + numeric.toLocaleString('id-ID');
            },

            formatPaymentLabel(method) {
                return this.paymentMethods[method] || method || 'Tunai';
            },

            formatDate(isoString) {
                if (!isoString) return '-';
                const d = new Date(isoString);
                return d.toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                });
            }
        };
    }
</script>
@endsection
