@extends('layouts.app')

@section('title', 'Kasir POS - Penjualan Figura')

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

<div x-data="posApp(@js($products), @js($recentTransactions), @js($paymentMethods), @js($customerTypes))" class="min-h-screen flex flex-col bg-stone-50">
    {{-- Top Gallery Walnut-Gold Header --}}
    <header class="bg-gradient-to-r from-stone-950 via-stone-900 to-amber-900 text-white shadow-lg shadow-stone-900/15 no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-md ring-1 ring-amber-300/30">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm4 4h8v6H8V9z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold tracking-tight">Kasir POS Penjualan Figura</h1>
                    <p class="text-xs text-amber-200/90">Kasir Aktif: <span class="font-semibold text-white">{{ auth()->user()->name }}</span></p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                <span class="hidden sm:inline-flex items-center gap-2 rounded-full bg-amber-500/15 border border-amber-400/25 px-3 py-1.5 text-xs font-semibold text-amber-100 backdrop-blur-sm">
                    <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Stok Tersedia: <span x-text="products.length"></span> Varian
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

    {{-- Main Two-Column POS Layout --}}
    <main class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-24 lg:pb-6 grid grid-cols-1 lg:grid-cols-12 gap-6 items-start no-print">
        {{-- Left Column: Available Frame Catalog (stock > 0) --}}
        <section class="lg:col-span-8 space-y-5">
            <div class="rounded-2xl bg-white p-4 shadow-sm border border-stone-200/80 space-y-3">
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <svg class="h-4 w-4 text-stone-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text"
                               x-model="search"
                               placeholder="Cari nama atau ukuran figura..."
                               class="w-full rounded-xl border border-stone-200 bg-stone-50 pl-10 pr-4 py-2.5 text-sm focus:border-amber-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-600/20">
                    </div>

                    <select x-model="selectedSize"
                            class="rounded-xl border border-stone-200 bg-stone-50 px-3.5 py-2.5 text-sm text-stone-700 focus:border-amber-600 focus:bg-white focus:outline-none">
                        <option value="">Semua Ukuran</option>
                        <template x-for="size in uniqueSizes" :key="size">
                            <option :value="size" x-text="size"></option>
                        </template>
                    </select>
                </div>
            </div>

            {{-- Product Grid --}}
            <div x-show="filteredProducts.length > 0" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                <template x-for="product in filteredProducts" :key="product.id">
                    <div class="group rounded-2xl bg-white p-4 border border-stone-200/90 shadow-sm hover:shadow-md hover:border-amber-400 transition flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-2">
                                <span class="inline-flex items-center rounded-lg bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800 border border-amber-200/70"
                                      x-text="product.size"></span>
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                      :class="product.stock <= 5 ? 'bg-orange-50 text-orange-700 border border-orange-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'">
                                    Stok: <span class="ml-1" x-text="product.stock"></span>
                                </span>
                            </div>

                            <h3 class="mt-3 text-base font-bold text-stone-900 leading-snug" x-text="product.name"></h3>
                        </div>

                        <button type="button"
                                @click="addToCart(product)"
                                :disabled="getCartQty(product.id) >= product.stock"
                                class="mt-4 min-h-[44px] w-full rounded-xl bg-gradient-to-r from-amber-700 via-amber-600 to-orange-600 hover:from-amber-800 hover:via-amber-700 hover:to-orange-700 active:scale-[0.99] disabled:from-stone-200 disabled:to-stone-300 disabled:text-stone-500 px-3 py-2.5 text-sm font-semibold text-white shadow-sm shadow-amber-700/20 transition cursor-pointer disabled:cursor-not-allowed select-none">
                            <span x-text="getCartQty(product.id) >= product.stock ? 'Batas Stok Tercapai' : (getCartQty(product.id) > 0 ? '+ Tambah Lagi (' + getCartQty(product.id) + ')' : '+ Tambah ke Keranjang')"></span>
                        </button>
                    </div>
                </template>
            </div>

            <div x-show="filteredProducts.length === 0" x-cloak
                 class="rounded-2xl bg-white p-10 text-center border border-dashed border-stone-300">
                <p class="text-sm font-semibold text-stone-600">Tidak ada figura tersedia yang cocok dengan filter pencarian.</p>
            </div>

            {{-- Recent Kasir Transactions --}}
            <div class="rounded-2xl bg-white p-5 border border-stone-200/80 shadow-sm">
                <h2 class="text-sm font-bold text-stone-800 mb-3">Transaksi Terakhir Anda</h2>
                <div x-show="recentTransactions.length === 0" class="text-xs text-stone-500">
                    Belum ada transaksi pada sesi ini.
                </div>
                <div class="space-y-2.5" x-show="recentTransactions.length > 0">
                    <template x-for="trx in recentTransactions" :key="trx.id">
                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-stone-50 px-3.5 py-2.5 border border-stone-200/70 text-xs">
                            <div>
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <p class="font-bold text-stone-800" x-text="trx.invoice_number"></p>
                                    <span class="rounded-md bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-800"
                                          x-text="trx.payment_method_label || formatPaymentLabel(trx.payment_method)"></span>
                                    <span class="rounded-md bg-stone-200/80 px-2 py-0.5 text-[11px] font-semibold text-stone-700"
                                          x-text="trx.customer_type_label || formatCustomerLabel(trx.customer_type)"></span>
                                </div>
                                <p class="text-stone-600 font-medium mt-0.5">
                                    Pembeli: <span x-text="trx.customer_name || '-'"></span>
                                    &bull; <span class="text-stone-500 font-normal" x-text="formatDate(trx.created_at)"></span>
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="font-bold text-amber-700" x-text="formatRupiah(trx.total_amount)"></span>
                                <button type="button"
                                        @click="openReceipt(trx)"
                                        class="min-h-[38px] rounded-lg border border-stone-300 bg-white hover:bg-amber-50 hover:border-amber-400 active:bg-amber-100 px-3 py-1.5 text-[11px] font-semibold text-stone-700 transition cursor-pointer">
                                    Cetak Struk
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </section>

        {{-- Right Column: Shopping Cart, Payment Method & Checkout --}}
        <aside id="pos-cart" class="lg:col-span-4 lg:sticky lg:top-6 scroll-mt-4 rounded-2xl bg-white border border-stone-200 shadow-lg shadow-stone-900/5 overflow-hidden">
            <div class="bg-gradient-to-r from-stone-900 via-stone-800 to-amber-900 px-5 py-4 text-white flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold">Keranjang Belanja</h2>
                    <p class="text-xs text-amber-200/90"><span x-text="totalItems"></span> unit dipilih</p>
                </div>
                <button type="button"
                        x-show="cart.length > 0"
                        @click="clearCart()"
                        class="min-h-[36px] px-2 text-xs font-semibold text-amber-200 hover:text-white underline cursor-pointer">
                    Kosongkan
                </button>
            </div>

            <div class="p-5 space-y-4">
                {{-- Error Alert --}}
                <div x-show="errorMessage" x-cloak
                     class="rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2.5 text-xs font-medium text-rose-700"
                     x-text="errorMessage"></div>

                {{-- Empty Cart State --}}
                <div x-show="cart.length === 0" class="py-10 text-center space-y-2">
                    <div class="mx-auto h-12 w-12 rounded-full bg-amber-50 flex items-center justify-center text-amber-700 border border-amber-200/60">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-stone-700">Keranjang masih kosong</p>
                    <p class="text-xs text-stone-400">Klik tombol tambah pada daftar figura di sebelah kiri.</p>
                </div>

                {{-- Buyer Name & Tier Selection (Shown when items are in cart) --}}
                <div x-show="cart.length > 0" class="rounded-xl border border-amber-200/80 bg-amber-50/50 p-3.5 space-y-3">
                    <div>
                        <label for="customer-name" class="block text-xs font-semibold text-stone-700 mb-1.5">Nama Pembeli</label>
                        <input id="customer-name"
                               type="text"
                               x-model="customerName"
                               maxlength="100"
                               placeholder="Masukkan nama pembeli..."
                               class="w-full min-h-[42px] rounded-xl border border-stone-200 bg-white px-3.5 py-2 text-sm text-stone-900 focus:border-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-600/20">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-stone-700 mb-1.5">Status Pembeli (Penentu Harga)</label>
                        <div class="grid grid-cols-3 gap-2">
                            <template x-for="(label, key) in customerTypes" :key="key">
                                <button type="button"
                                        @click="selectCustomerType(key)"
                                        :class="customerType === key
                                            ? 'border-amber-600 bg-gradient-to-r from-amber-700 to-orange-600 text-white shadow-sm'
                                            : 'border-stone-200 bg-white text-stone-700 hover:bg-amber-50 hover:border-amber-300'"
                                        class="min-h-[44px] rounded-xl border px-2 py-2 text-xs font-bold transition text-center cursor-pointer"
                                        x-text="label"></button>
                            </template>
                        </div>
                    </div>

                    <p x-show="!customerType" class="text-[11px] font-medium text-amber-800">
                        Pilih status pembeli (Sales, Agen, atau Umum) untuk menampilkan harga produk.
                    </p>
                </div>

                {{-- Cart Items List --}}
                <div x-show="cart.length > 0" class="max-h-72 overflow-y-auto divide-y divide-stone-100 pr-1">
                    <template x-for="item in cart" :key="item.product_id">
                        <div class="py-3 flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-stone-800 truncate" x-text="item.name"></p>
                                <p class="text-xs text-stone-500">
                                    <span x-text="item.size"></span>
                                    <template x-if="customerType">
                                        <span> &bull; <span class="font-semibold text-stone-700" x-text="formatRupiah(getItemUnitPrice(item))"></span></span>
                                    </template>
                                    <template x-if="!customerType">
                                        <span class="italic text-stone-400"> &bull; Pilih status untuk harga</span>
                                    </template>
                                </p>
                                <div class="mt-2 inline-flex items-center gap-2 rounded-lg border border-stone-200 bg-stone-50 px-1.5 py-1">
                                    <button type="button"
                                            @click="decrementQty(item.product_id)"
                                            class="h-9 w-9 sm:h-8 sm:w-8 rounded-md bg-white text-stone-700 font-bold shadow-xs hover:bg-amber-50 active:bg-amber-100 cursor-pointer">-</button>
                                    <span class="w-7 text-center text-xs font-bold text-stone-800" x-text="item.quantity"></span>
                                    <button type="button"
                                            @click="incrementQty(item.product_id)"
                                            :disabled="item.quantity >= item.max_stock"
                                            class="h-9 w-9 sm:h-8 sm:w-8 rounded-md bg-white text-stone-700 font-bold shadow-xs hover:bg-amber-50 active:bg-amber-100 disabled:opacity-40 cursor-pointer">+</button>
                                </div>
                            </div>

                            <div class="text-right">
                                <p x-show="customerType" class="text-sm font-bold text-amber-700" x-text="formatRupiah(getItemSubtotal(item))"></p>
                                <p x-show="!customerType" class="text-xs font-medium text-stone-400">-</p>
                                <button type="button"
                                        @click="removeItem(item.product_id)"
                                        class="mt-2 min-h-[36px] px-1 text-xs text-rose-500 hover:text-rose-700 font-medium cursor-pointer">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Total, Payment Method & Change Section (Shown once customer status is selected) --}}
                <div x-show="cart.length > 0 && customerType !== ''" class="pt-3 border-t border-stone-200 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-sm font-semibold text-stone-600">Total Tagihan</span>
                            <p class="text-[11px] font-medium text-amber-700">
                                Harga <span x-text="formatCustomerLabel(customerType)"></span>
                            </p>
                        </div>
                        <span class="text-xl font-extrabold text-amber-700" x-text="formatRupiah(grandTotal)"></span>
                    </div>

                    {{-- Payment Method Selection --}}
                    <div>
                        <label class="block text-xs font-semibold text-stone-700 mb-2">Metode Pembayaran</label>
                        <div class="grid grid-cols-2 gap-2">
                            <template x-for="(label, key) in paymentMethods" :key="key">
                                <button type="button"
                                        @click="selectPaymentMethod(key)"
                                        :class="paymentMethod === key
                                            ? 'border-amber-600 bg-amber-50 text-amber-900 ring-2 ring-amber-500/20'
                                            : 'border-stone-200 bg-stone-50 text-stone-600 hover:bg-stone-100'"
                                        class="min-h-[44px] rounded-xl border px-3 py-2 text-xs font-bold transition text-left cursor-pointer"
                                        x-text="label"></button>
                            </template>
                        </div>
                    </div>

                    {{-- Cash Tendered & Change Calculation --}}
                    <div x-show="paymentMethod === 'cash'" class="space-y-2.5">
                        <label class="block text-xs font-semibold text-stone-700">Uang Tunai Diterima (Rp)</label>
                        <input type="number"
                               min="0"
                               step="500"
                               x-model.number="cashReceived"
                               placeholder="Masukkan nominal bayar..."
                               class="w-full rounded-xl border border-stone-200 bg-stone-50 px-3.5 py-2.5 text-sm font-semibold text-stone-900 focus:border-amber-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-600/20">

                        <div class="flex flex-wrap gap-2">
                            <button type="button"
                                    @click="cashReceived = grandTotal"
                                    class="min-h-[36px] rounded-lg bg-amber-100 hover:bg-amber-200 active:bg-amber-300 px-3 py-1.5 text-xs font-bold text-amber-900 cursor-pointer">
                                Uang Pas
                            </button>
                            <button type="button"
                                    @click="cashReceived = (Number(cashReceived) || 0) + 50000"
                                    class="min-h-[36px] rounded-lg bg-stone-100 hover:bg-stone-200 active:bg-stone-300 px-3 py-1.5 text-xs font-semibold text-stone-700 cursor-pointer">
                                +50rb
                            </button>
                            <button type="button"
                                    @click="cashReceived = (Number(cashReceived) || 0) + 100000"
                                    class="min-h-[36px] rounded-lg bg-stone-100 hover:bg-stone-200 active:bg-stone-300 px-3 py-1.5 text-xs font-semibold text-stone-700 cursor-pointer">
                                +100rb
                            </button>
                            <button type="button"
                                    @click="cashReceived = (Number(cashReceived) || 0) + 200000"
                                    class="min-h-[36px] rounded-lg bg-stone-100 hover:bg-stone-200 active:bg-stone-300 px-3 py-1.5 text-xs font-semibold text-stone-700 cursor-pointer">
                                +200rb
                            </button>
                        </div>

                        <div x-show="cashReceived !== null && cashReceived !== ''"
                             class="rounded-xl px-3.5 py-2.5 text-xs flex items-center justify-between border"
                             :class="cashReceived >= grandTotal ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-700'">
                            <span class="font-semibold" x-text="cashReceived >= grandTotal ? 'Kembalian' : 'Kurang Bayar'"></span>
                            <span class="text-sm font-extrabold" x-text="formatRupiah(Math.abs((Number(cashReceived) || 0) - grandTotal))"></span>
                        </div>
                    </div>

                    {{-- Non-Cash Info Box --}}
                    <div x-show="paymentMethod !== 'cash'" x-cloak
                         class="rounded-xl bg-amber-50/70 border border-amber-200 px-3.5 py-2.5 text-xs text-amber-900 space-y-1">
                        <div class="flex justify-between font-semibold">
                            <span>Nominal <span x-text="formatPaymentLabel(paymentMethod)"></span></span>
                            <span x-text="formatRupiah(grandTotal)"></span>
                        </div>
                        <p class="text-[11px] text-amber-700">Pembayaran non-tunai diproses sesuai total tagihan (Kembalian Rp 0).</p>
                    </div>

                    <button type="button"
                            @click="submitCheckout()"
                            :disabled="isSubmitting || cart.length === 0 || !canCheckout"
                            class="min-h-[48px] w-full rounded-xl bg-gradient-to-r from-amber-700 via-amber-600 to-orange-600 hover:from-amber-800 hover:via-amber-700 hover:to-orange-700 disabled:opacity-50 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-amber-700/25 transition cursor-pointer disabled:cursor-not-allowed">
                        <span x-show="!isSubmitting">Checkout &amp; Simpan Transaksi</span>
                        <span x-show="isSubmitting">Memproses Transaksi...</span>
                    </button>
                    <p x-show="customerType && !customerName.trim()" class="text-[11px] font-medium text-rose-600 text-center">
                        Isi Nama Pembeli di atas terlebih dahulu untuk checkout.
                    </p>
                </div>
            </div>
        </aside>
    </main>

    {{-- Non-blocking Add-to-Cart Feedback Pill --}}
    <div x-show="cartNotice"
         x-cloak
         class="fixed top-4 inset-x-4 sm:inset-x-auto sm:right-6 z-40 pointer-events-none flex justify-center sm:justify-end no-print">
        <div class="rounded-xl bg-stone-900/95 text-white px-4 py-2.5 text-xs font-semibold shadow-xl border border-amber-500/40"
             x-text="cartNotice"></div>
    </div>

    {{-- Mobile & Tablet Sticky Bottom Cart Bar --}}
    <div x-show="cart.length > 0 && !lastReceipt"
         x-cloak
         class="lg:hidden fixed inset-x-0 bottom-0 z-30 border-t border-stone-200 bg-white/95 backdrop-blur-md px-4 py-3 shadow-[0_-8px_24px_-6px_rgba(28,25,23,0.12)] no-print">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-[11px] font-medium text-stone-500">
                    <span x-text="totalItems"></span> item<span x-show="customerType" x-text="' • ' + formatCustomerLabel(customerType)"></span>
                </p>
                <p x-show="customerType" class="text-base font-extrabold text-amber-700 truncate" x-text="formatRupiah(grandTotal)"></p>
                <p x-show="!customerType" class="text-xs font-bold text-amber-700 truncate">Pilih status pembeli di keranjang</p>
            </div>
            <button type="button"
                    @click="scrollToCart()"
                    class="min-h-[44px] shrink-0 rounded-xl bg-gradient-to-r from-amber-700 via-amber-600 to-orange-600 hover:from-amber-800 hover:via-amber-700 hover:to-orange-700 px-4 py-2.5 text-xs sm:text-sm font-bold text-white shadow-md shadow-amber-700/25 transition cursor-pointer">
                Lihat Keranjang &amp; Bayar
            </button>
        </div>
    </div>

    {{-- Receipt Success & Printable Modal --}}
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
                        <span class="font-bold text-stone-900 text-right" x-text="lastReceipt?.customer_type_label || formatCustomerLabel(lastReceipt?.customer_type)"></span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-stone-500 shrink-0">Pembayaran</span>
                        <span class="font-bold text-stone-900 text-right" x-text="lastReceipt?.payment_method_label || formatPaymentLabel(lastReceipt?.payment_method)"></span>
                    </div>
                </div>

                {{-- Line Items (Two-row per item so long names never break thermal column alignment) --}}
                <div class="py-2.5 border-b border-dashed border-stone-400 receipt-divider space-y-2 text-[11px]">
                    <template x-for="detail in (lastReceipt?.details || [])" :key="detail.id">
                        <div>
                            <div class="font-bold text-stone-900 break-words">
                                <span x-text="detail.product?.name || 'Produk'"></span>
                                (<span x-text="detail.product?.size || '-'"></span>)
                            </div>
                            <div class="flex justify-between gap-2 text-stone-700 pl-2">
                                <span>
                                    <span x-text="detail.quantity"></span> &times;
                                    <span x-text="formatRupiah(Number(detail.unit_price) > 0 ? detail.unit_price : (detail.subtotal / Math.max(1, detail.quantity)))"></span>
                                </span>
                                <span class="font-bold text-stone-900 whitespace-nowrap" x-text="formatRupiah(detail.subtotal)"></span>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Totals, Paid & Change --}}
                <div class="py-2.5 border-b border-dashed border-stone-400 receipt-divider space-y-1 text-[11px]">
                    <div class="flex justify-between gap-2 text-xs font-extrabold text-stone-900">
                        <span>TOTAL</span>
                        <span class="whitespace-nowrap" x-text="formatRupiah(lastReceipt?.total_amount || 0)"></span>
                    </div>
                    <div class="flex justify-between gap-2 text-stone-700">
                        <span x-text="'Bayar (' + (lastReceipt?.payment_method_label || formatPaymentLabel(lastReceipt?.payment_method)) + ')'"></span>
                        <span class="font-semibold whitespace-nowrap" x-text="formatRupiah(lastReceipt?.paid_amount ?? lastReceipt?.total_amount ?? 0)"></span>
                    </div>
                    <div class="flex justify-between gap-2 text-stone-900 font-bold">
                        <span>Kembalian</span>
                        <span class="whitespace-nowrap" x-text="formatRupiah(lastReceipt?.change_amount || 0)"></span>
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
    function posApp(initialProducts, initialRecentTransactions, paymentMethods, customerTypes) {
        return {
            products: initialProducts || [],
            recentTransactions: initialRecentTransactions || [],
            paymentMethods: paymentMethods || { cash: 'Cash (Tunai)' },
            customerTypes: customerTypes || { sales: 'Sales', agen: 'Agen', umum: 'Umum' },
            cart: [],
            search: '',
            selectedSize: '',
            customerName: '',
            customerType: '',
            paymentMethod: 'cash',
            cashReceived: null,
            isSubmitting: false,
            errorMessage: '',
            cartNotice: '',
            cartNoticeTimer: null,
            lastReceipt: null,
            receiptWidth: '80mm',

            get uniqueSizes() {
                return [...new Set(this.products.map(p => p.size))];
            },

            get filteredProducts() {
                const keyword = this.search.trim().toLowerCase();
                return this.products.filter(product => {
                    const matchesSearch = !keyword ||
                        product.name.toLowerCase().includes(keyword) ||
                        product.size.toLowerCase().includes(keyword);
                    const matchesSize = !this.selectedSize || product.size === this.selectedSize;
                    return product.stock > 0 && matchesSearch && matchesSize;
                });
            },

            get totalItems() {
                return this.cart.reduce((sum, item) => sum + item.quantity, 0);
            },

            get grandTotal() {
                if (!this.customerType) return 0;
                return this.cart.reduce((sum, item) => sum + this.getItemSubtotal(item), 0);
            },

            get canCheckout() {
                if (this.cart.length === 0) return false;
                if (!this.customerType) return false;
                if (!this.customerName.trim()) return false;
                if (this.paymentMethod === 'cash') {
                    return Number(this.cashReceived) >= this.grandTotal;
                }
                return true;
            },

            getItemUnitPrice(item) {
                if (this.customerType === 'sales') {
                    return Number(item.price_sales ?? item.price ?? 0);
                }
                if (this.customerType === 'agen') {
                    return Number(item.price_agen ?? item.price ?? 0);
                }
                if (this.customerType === 'umum') {
                    return Number(item.price_umum ?? item.price ?? 0);
                }
                return 0;
            },

            getItemSubtotal(item) {
                return this.getItemUnitPrice(item) * item.quantity;
            },

            selectCustomerType(type) {
                this.customerType = type;
                this.errorMessage = '';
                if (this.paymentMethod !== 'cash') {
                    this.cashReceived = this.grandTotal;
                }
            },

            selectPaymentMethod(method) {
                this.paymentMethod = method;
                this.errorMessage = '';
                if (method !== 'cash') {
                    this.cashReceived = this.grandTotal;
                } else if (this.cashReceived === null || this.cashReceived === '') {
                    this.cashReceived = this.grandTotal;
                }
            },

            getCartQty(productId) {
                const existing = this.cart.find(item => item.product_id === productId);
                return existing ? existing.quantity : 0;
            },

            showCartNotice(message) {
                this.cartNotice = message;
                if (this.cartNoticeTimer) {
                    clearTimeout(this.cartNoticeTimer);
                }
                this.cartNoticeTimer = setTimeout(() => {
                    this.cartNotice = '';
                }, 1800);
            },

            scrollToCart() {
                const cartEl = document.getElementById('pos-cart');
                if (cartEl) {
                    cartEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            },

            addToCart(product) {
                this.errorMessage = '';
                const existing = this.cart.find(item => item.product_id === product.id);
                if (existing) {
                    if (existing.quantity < product.stock) {
                        existing.quantity++;
                        if (this.paymentMethod !== 'cash' && this.customerType) {
                            this.cashReceived = this.grandTotal;
                        }
                        this.showCartNotice(`${product.name} (${existing.quantity}x) di keranjang`);
                    }
                    return;
                }

                this.cart.push({
                    product_id: product.id,
                    name: product.name,
                    size: product.size,
                    price: Number(product.price),
                    price_sales: Number(product.price_sales ?? product.price),
                    price_agen: Number(product.price_agen ?? product.price),
                    price_umum: Number(product.price_umum ?? product.price),
                    max_stock: Number(product.stock),
                    quantity: 1,
                });
                if (this.paymentMethod !== 'cash' && this.customerType) {
                    this.cashReceived = this.grandTotal;
                }
                this.showCartNotice(`${product.name} ditambahkan ke keranjang`);
            },

            incrementQty(productId) {
                const item = this.cart.find(i => i.product_id === productId);
                if (item && item.quantity < item.max_stock) {
                    item.quantity++;
                }
            },

            decrementQty(productId) {
                const item = this.cart.find(i => i.product_id === productId);
                if (!item) return;
                if (item.quantity > 1) {
                    item.quantity--;
                } else {
                    this.removeItem(productId);
                }
            },

            removeItem(productId) {
                this.cart = this.cart.filter(i => i.product_id !== productId);
            },

            clearCart() {
                this.cart = [];
                this.customerName = '';
                this.customerType = '';
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
                if (this.cart.length === 0 || this.isSubmitting) return;

                if (!this.customerName.trim()) {
                    this.errorMessage = 'Nama pembeli wajib diisi sebelum checkout.';
                    return;
                }

                if (!this.customerType) {
                    this.errorMessage = 'Pilih status pembeli (Sales, Agen, atau Umum) terlebih dahulu.';
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
                            items: this.cart.map(item => ({
                                product_id: item.product_id,
                                quantity: item.quantity,
                            })),
                        }),
                    });

                    const payload = await response.json();

                    if (!response.ok) {
                        const firstError =
                            payload?.errors?.customer_name?.[0] ||
                            payload?.errors?.customer_type?.[0] ||
                            payload?.errors?.paid_amount?.[0] ||
                            payload?.errors?.payment_method?.[0] ||
                            payload?.errors?.items?.[0] ||
                            payload?.message ||
                            'Gagal memproses checkout.';
                        this.errorMessage = firstError;
                        return;
                    }

                    this.products = payload.products || this.products;
                    this.lastReceipt = payload.transaction;
                    this.recentTransactions.unshift(payload.transaction);
                    this.recentTransactions = this.recentTransactions.slice(0, 5);
                    this.clearCart();
                } catch (error) {
                    this.errorMessage = 'Terjadi gangguan koneksi saat menyimpan transaksi.';
                } finally {
                    this.isSubmitting = false;
                }
            },

            formatCustomerLabel(key) {
                return this.customerTypes[key] || (key ? key.charAt(0).toUpperCase() + key.slice(1) : 'Umum');
            },

            formatPaymentLabel(key) {
                return this.paymentMethods[key] || (key ? key.toUpperCase() : 'Cash');
            },

            formatRupiah(value) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(Number(value) || 0));
            },

            formatDate(isoString) {
                if (!isoString) return '-';
                return new Date(isoString).toLocaleString('id-ID', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                });
            },
        };
    }
</script>
@endsection
