{{-- Mobile & Tablet Sticky Top Bar (< lg) --}}
<header class="lg:hidden sticky top-0 z-30 flex items-center justify-between bg-gradient-to-r from-stone-950 via-stone-900 to-amber-950 px-4 py-3 text-white shadow-md">
    <div class="flex items-center gap-3 min-w-0">
        <div class="h-9 w-9 shrink-0 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow ring-1 ring-amber-300/30">
            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm4 4h8v6H8V9z" />
            </svg>
        </div>
        <div class="min-w-0">
            <p class="text-sm font-bold truncate">Inul Wijaya Frame</p>
            <p class="text-[11px] text-amber-200/80 truncate">{{ auth()->user()->name }}</p>
        </div>
    </div>

    <button type="button"
            @click="sidebarOpen = !sidebarOpen"
            aria-label="Toggle Menu Admin"
            class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-white transition cursor-pointer">
        <svg x-show="!sidebarOpen" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
        <svg x-show="sidebarOpen" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</header>

{{-- Mobile & Tablet Drawer Backdrop --}}
<div x-show="sidebarOpen"
     x-cloak
     @click="sidebarOpen = false"
     x-transition.opacity
     class="fixed inset-0 z-30 bg-stone-950/60 backdrop-blur-xs lg:hidden"></div>

{{-- Sidebar: Drawer on Mobile/Tablet (< lg), Sticky Fixed Column on Desktop (lg+) --}}
<aside :class="sidebarOpen ? 'translate-x-0 pointer-events-auto' : '-translate-x-full pointer-events-none lg:translate-x-0 lg:pointer-events-auto'"
       class="fixed inset-y-0 left-0 z-40 w-72 max-w-[82vw] lg:w-64 lg:static lg:sticky lg:top-0 lg:h-screen lg:overflow-y-auto shrink-0 bg-gradient-to-b from-stone-950 via-stone-900 to-amber-950 text-white flex flex-col justify-between shadow-2xl lg:shadow-xl transition-transform duration-200 ease-out">
    <div>
        <div class="p-6 border-b border-white/10 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg shadow-amber-900/40 ring-1 ring-amber-300/30">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm4 4h8v6H8V9z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold tracking-wide text-white">Inul Wijaya Frame</p>
                    <p class="text-xs text-amber-200/80">Admin Panel POS</p>
                </div>
            </div>

            <button type="button"
                    @click="sidebarOpen = false"
                    aria-label="Tutup Menu"
                    class="lg:hidden inline-flex h-9 w-9 items-center justify-center rounded-lg bg-white/10 text-stone-200 hover:text-white cursor-pointer">
                &times;
            </button>
        </div>

        <nav class="p-4 space-y-2">
            <a href="{{ route('admin.dashboard') }}"
               @click="sidebarOpen = false"
               class="flex items-center gap-3 px-4 py-3 min-h-[44px] rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.dashboard') ? 'bg-amber-500/20 text-amber-100 shadow-inner backdrop-blur-sm border border-amber-400/30' : 'text-stone-300 hover:bg-white/10 hover:text-white' }}">
                <svg class="h-5 w-5 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z" />
                </svg>
                <span>Dashboard &amp; Laporan</span>
            </a>

            <a href="{{ route('admin.products.index') }}"
               @click="sidebarOpen = false"
               class="flex items-center gap-3 px-4 py-3 min-h-[44px] rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.products.*') ? 'bg-amber-500/20 text-amber-100 shadow-inner backdrop-blur-sm border border-amber-400/30' : 'text-stone-300 hover:bg-white/10 hover:text-white' }}">
                <svg class="h-5 w-5 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <span>Kelola Produk Figura</span>
            </a>
        </nav>
    </div>

    <div class="p-4 border-t border-white/10">
        <div class="mb-3 px-3 py-2 rounded-xl bg-white/5 border border-white/10">
            <p class="text-xs text-amber-200/80">Masuk sebagai Admin</p>
            <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="w-full min-h-[44px] flex items-center justify-center gap-2 rounded-xl bg-white/10 hover:bg-rose-600/85 px-4 py-2.5 text-sm font-semibold text-white transition cursor-pointer">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Keluar
            </button>
        </form>
    </div>
</aside>
