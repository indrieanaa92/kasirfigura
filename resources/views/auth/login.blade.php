@extends('layouts.app')

@section('title', 'Masuk - Inul Wijaya Frame')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-stone-950 via-stone-900 to-amber-950 p-4 sm:p-6">
    <div class="w-full max-w-4xl grid grid-cols-1 md:grid-cols-2 rounded-3xl overflow-hidden bg-white shadow-2xl shadow-black/50 border border-amber-500/20">
        {{-- Gallery Brand Panel (Appears below form on small screens, left on tablet/desktop) --}}
        <div class="order-2 md:order-1 relative flex flex-col justify-between p-6 sm:p-8 lg:p-10 bg-gradient-to-br from-stone-900 via-stone-800 to-amber-900 text-white">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-amber-500/15 border border-amber-400/30 px-3 py-1 text-xs font-semibold text-amber-200 backdrop-blur-sm">
                    <span class="h-2 w-2 rounded-full bg-amber-400"></span>
                    Sistem Point of Sale
                </div>
                <h1 class="mt-4 sm:mt-6 text-2xl sm:text-3xl font-extrabold tracking-tight leading-tight text-white">
                    Inul Wijaya<br>
                    <span class="text-amber-300">Frame</span>
                </h1>
                <p class="mt-3 text-sm text-stone-300 leading-relaxed">
                    Kelola transaksi kasir lebih cepat, pantau stok bingkai &amp; figura secara real-time, serta unduh laporan keuangan bulanan dalam satu klik.
                </p>
            </div>

            <div class="mt-6 sm:mt-8 rounded-2xl bg-black/25 p-4 backdrop-blur-sm border border-amber-400/20 text-xs space-y-1.5 text-stone-200">
                <p class="font-semibold text-amber-300">Akun Demo (Seeder):</p>
                <p>Admin: <span class="font-mono text-white break-all">admin@kasirfigura.test</span></p>
                <p>Kasir: <span class="font-mono text-white break-all">kasir@kasirfigura.test</span></p>
                <p>Password: <span class="font-mono text-white">password</span></p>
            </div>
        </div>

        {{-- Login Form Panel (Appears first on mobile so immediately usable) --}}
        <div class="order-1 md:order-2 p-6 sm:p-8 lg:p-10 flex flex-col justify-center bg-white"
             x-data="{ role: '{{ $activeRole }}', submitting: false }">
            <div class="mb-5 sm:mb-6">
                <h2 class="text-2xl font-bold text-stone-900">Selamat Datang</h2>
                <p class="mt-1 text-sm text-stone-500">Pilih portal login sesuai peran Anda.</p>
            </div>

            {{-- Role Switcher Tabs --}}
            <div class="grid grid-cols-2 gap-2 p-1 rounded-xl bg-stone-100 mb-6">
                <button type="button"
                        @click="role = 'kasir'"
                        :class="role === 'kasir'
                            ? 'bg-gradient-to-r from-amber-700 to-orange-600 text-white shadow-md'
                            : 'text-stone-600 hover:text-stone-900'"
                        class="min-h-[44px] rounded-lg py-2.5 text-sm font-semibold transition cursor-pointer">
                    Login Kasir
                </button>
                <button type="button"
                        @click="role = 'admin'"
                        :class="role === 'admin'
                            ? 'bg-gradient-to-r from-amber-700 to-orange-600 text-white shadow-md'
                            : 'text-stone-600 hover:text-stone-900'"
                        class="min-h-[44px] rounded-lg py-2.5 text-sm font-semibold transition cursor-pointer">
                    Login Admin
                </button>
            </div>

            <form method="POST" action="{{ route('login.attempt') }}" @submit="submitting = true" class="space-y-4">
                @csrf
                <input type="hidden" name="login_as" :value="role">

                <div>
                    <label for="email" class="block text-sm font-semibold text-stone-700 mb-1.5">Email</label>
                    <input id="email"
                           name="email"
                           type="email"
                           value="{{ old('email') }}"
                           required
                           autofocus
                           placeholder="nama@kasirfigura.test"
                           class="w-full min-h-[44px] rounded-xl border border-stone-200 bg-stone-50 px-4 py-2.5 text-sm text-stone-900 focus:border-amber-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-600/20">
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-stone-700 mb-1.5">Kata Sandi</label>
                    <input id="password"
                           name="password"
                           type="password"
                           required
                           placeholder="••••••••"
                           class="w-full min-h-[44px] rounded-xl border border-stone-200 bg-stone-50 px-4 py-2.5 text-sm text-stone-900 focus:border-amber-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-600/20">
                </div>

                <div class="flex items-center justify-between text-xs text-stone-500 gap-2">
                    <label class="inline-flex items-center gap-2 py-1 cursor-pointer">
                        <input type="checkbox" name="remember" class="h-4 w-4 rounded border-stone-300 text-amber-700 focus:ring-amber-600">
                        <span>Ingat saya</span>
                    </label>
                    <span class="font-semibold text-amber-700 text-right" x-text="role === 'admin' ? 'Portal Administrator' : 'Portal Kasir POS'"></span>
                </div>

                <button type="submit"
                        :disabled="submitting"
                        class="w-full min-h-[46px] rounded-xl bg-gradient-to-r from-amber-700 via-amber-600 to-orange-600 hover:from-amber-800 hover:via-amber-700 hover:to-orange-700 disabled:opacity-60 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-amber-700/25 transition cursor-pointer disabled:cursor-not-allowed">
                    <span x-show="!submitting" x-text="role === 'admin' ? 'Masuk ke Dashboard Admin' : 'Masuk ke Kasir POS'"></span>
                    <span x-show="submitting" x-cloak>Memproses Login...</span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
