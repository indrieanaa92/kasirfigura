@extends('layouts.app')

@section('title', 'Manajemen Produk - Inul Wijaya Frame')

@section('content')
<div x-data="{ sidebarOpen: false }" class="min-h-screen flex flex-col lg:flex-row bg-stone-50">
    @include('layouts.partials.admin-sidebar')

    <main x-data="productManager(@js($products))" class="flex-1 min-w-0 p-4 sm:p-6 lg:p-8 space-y-6">
        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-stone-900">Manajemen Produk Figura</h1>
                <p class="text-sm text-stone-500">Kelola katalog bingkai, atur harga Umum &amp; Member, serta pantau ketersediaan stok.</p>
            </div>

            <button type="button"
                    @click="openCreate()"
                    class="w-full sm:w-auto min-h-[44px] inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-700 via-amber-600 to-orange-600 hover:from-amber-800 hover:via-amber-700 hover:to-orange-700 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-amber-700/20 transition cursor-pointer">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>+ Tambah Figura Baru</span>
            </button>
        </div>

        {{-- Stat Summary Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="rounded-2xl bg-white border border-stone-200/80 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Total Varian Figura</p>
                <p class="mt-2 text-2xl font-extrabold text-stone-900">{{ $products->count() }} Varian</p>
                <p class="mt-1 text-xs text-stone-500">Terdaftar di katalog toko</p>
            </div>

            <div class="rounded-2xl bg-gradient-to-br from-amber-700 via-amber-600 to-orange-600 p-5 text-white shadow-lg shadow-amber-700/20">
                <p class="text-xs font-semibold uppercase tracking-wider text-amber-100">Stok Siap Jual</p>
                <p class="mt-2 text-2xl font-extrabold">{{ $products->where('stock', '>', 5)->count() }} Varian</p>
                <p class="mt-1 text-xs text-amber-100/90">Kondisi stok aman &amp; tersedia</p>
            </div>

            <div class="rounded-2xl bg-white border border-stone-200/80 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Perlu Restok</p>
                <p class="mt-2 text-2xl font-extrabold text-rose-600">{{ $products->where('stock', '<=', 5)->count() }} Varian</p>
                <p class="mt-1 text-xs text-stone-500">Stok menipis atau habis</p>
            </div>
        </div>

        {{-- Search & Filter Toolbar --}}
        <div class="rounded-2xl bg-white border border-stone-200/80 p-4 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
            <form method="GET" action="{{ route('admin.products.index') }}" class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
                <div class="relative w-full sm:w-80">
                    <svg class="h-4 w-4 text-stone-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Cari nama atau ukuran figura..."
                           class="w-full min-h-[42px] rounded-xl border border-stone-200 bg-stone-50 pl-10 pr-4 py-2 text-sm focus:border-amber-600 focus:bg-white focus:outline-none">
                </div>

                <button type="submit"
                        class="min-h-[42px] rounded-xl bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 text-xs font-bold transition cursor-pointer">
                    Cari
                </button>

                @if ($search !== '')
                    <a href="{{ route('admin.products.index') }}"
                       class="min-h-[42px] inline-flex items-center rounded-xl border border-stone-200 bg-white hover:bg-stone-50 px-3.5 py-2 text-xs font-semibold text-stone-600">
                        Reset
                    </a>
                @endif
            </form>

            <span class="text-xs font-medium text-stone-500">
                Menampilkan <strong class="text-stone-800">{{ $products->count() }}</strong> produk figura
            </span>
        </div>

        {{-- Mobile Cards (< md) --}}
        <div class="grid grid-cols-1 gap-3 md:hidden">
            @forelse ($products as $product)
                <div class="rounded-2xl bg-white border border-stone-200/80 p-4 shadow-sm space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h3 class="text-base font-bold text-stone-900 leading-snug">{{ $product->name }}</h3>
                            <span class="mt-1 inline-flex rounded-lg bg-amber-50 border border-amber-200/70 px-2.5 py-0.5 text-xs font-semibold text-amber-800">
                                {{ $product->size }}
                            </span>
                        </div>
                        @if ($product->stock === 0)
                            <span class="inline-flex rounded-full bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-700 border border-rose-200 shrink-0">
                                Habis (0)
                            </span>
                        @elseif ($product->stock <= 5)
                            <span class="inline-flex rounded-full bg-orange-50 px-2.5 py-1 text-xs font-bold text-orange-700 border border-orange-200 shrink-0">
                                Menipis ({{ $product->stock }})
                            </span>
                        @else
                            <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 border border-emerald-200 shrink-0">
                                Stok: {{ $product->stock }}
                            </span>
                        @endif
                    </div>

                    {{-- Price Grid --}}
                    <div class="grid grid-cols-2 gap-2 rounded-xl bg-stone-50 border border-stone-200/70 p-3 text-center text-xs">
                        <div class="pr-2">
                            <p class="text-stone-500 font-medium text-[11px] uppercase tracking-wider">Harga Umum</p>
                            <p class="font-bold font-mono text-sm text-stone-900 mt-0.5">
                                Rp {{ number_format($product->price_umum ?: $product->price, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="border-l border-stone-200 pl-2">
                            <p class="text-amber-800 font-bold text-[11px] uppercase tracking-wider">⭐ Harga Member</p>
                            <p class="font-bold font-mono text-sm text-amber-700 mt-0.5">
                                Rp {{ number_format($product->price_member ?: ($product->price_sales ?: $product->price), 0, ',', '.') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-stone-100">
                        <button type="button"
                                @click="openEditById({{ $product->id }})"
                                class="min-h-[38px] rounded-xl bg-amber-50 hover:bg-amber-100 border border-amber-300 px-4 py-1.5 text-xs font-bold text-amber-800 transition cursor-pointer">
                            Edit
                        </button>

                        <form method="POST"
                              action="{{ route('admin.products.destroy', $product) }}"
                              onsubmit="return confirm('Hapus produk {{ addslashes($product->name) }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="min-h-[38px] rounded-xl bg-rose-50 hover:bg-rose-100 px-4 py-1.5 text-xs font-semibold text-rose-700 transition cursor-pointer">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl bg-white border border-stone-200/80 p-8 text-center text-sm text-stone-500">
                    Belum ada data produk figura.
                </div>
            @endforelse
        </div>

        {{-- Desktop Products Table (md+) - Neat & Structured --}}
        <div class="hidden md:block rounded-2xl bg-white border border-stone-200/90 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm table-fixed">
                    <thead class="bg-amber-50/80 text-xs uppercase text-amber-900 font-bold border-b border-amber-200/70">
                        <tr>
                            <th class="px-4 py-3.5 w-14 text-center">No</th>
                            <th class="px-5 py-3.5 w-64">Nama Figura</th>
                            <th class="px-4 py-3.5 w-32 text-center">Ukuran</th>
                            <th class="px-5 py-3.5 w-40 text-right">Harga Umum</th>
                            <th class="px-5 py-3.5 w-40 text-right">Harga Member</th>
                            <th class="px-4 py-3.5 w-36 text-center">Status Stok</th>
                            <th class="px-5 py-3.5 w-40 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($products as $index => $product)
                            <tr class="hover:bg-amber-50/25 transition">
                                {{-- No --}}
                                <td class="px-4 py-4 text-center font-bold text-stone-400 text-xs">
                                    {{ $index + 1 }}
                                </td>

                                {{-- Nama Figura --}}
                                <td class="px-5 py-4">
                                    <div class="font-bold text-stone-900 leading-snug">{{ $product->name }}</div>
                                </td>

                                {{-- Ukuran --}}
                                <td class="px-4 py-4 text-center">
                                    <span class="inline-flex rounded-lg bg-amber-50 border border-amber-200/70 px-2.5 py-1 text-xs font-semibold text-amber-800">
                                        {{ $product->size }}
                                    </span>
                                </td>

                                {{-- Harga Umum --}}
                                <td class="px-5 py-4 text-right font-mono font-bold text-stone-900 text-sm">
                                    Rp {{ number_format($product->price_umum ?: $product->price, 0, ',', '.') }}
                                </td>

                                {{-- Harga Member --}}
                                <td class="px-5 py-4 text-right font-mono font-bold text-amber-700 text-sm">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="text-[10px] text-amber-600 font-sans">⭐</span>
                                        <span>Rp {{ number_format($product->price_member ?: ($product->price_sales ?: $product->price), 0, ',', '.') }}</span>
                                    </span>
                                </td>

                                {{-- Status Stok --}}
                                <td class="px-4 py-4 text-center">
                                    @if ($product->stock === 0)
                                        <span class="inline-flex items-center rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-700 border border-rose-200">
                                            Habis (0)
                                        </span>
                                    @elseif ($product->stock <= 5)
                                        <span class="inline-flex items-center rounded-full bg-orange-50 px-3 py-1 text-xs font-bold text-orange-700 border border-orange-200">
                                            Menipis ({{ $product->stock }})
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 border border-emerald-200">
                                            Tersedia ({{ $product->stock }})
                                        </span>
                                    @endif
                                </td>

                                {{-- Aksi --}}
                                <td class="px-5 py-4 text-center space-x-1.5 whitespace-nowrap">
                                    <button type="button"
                                            @click="openEditById({{ $product->id }})"
                                            class="min-h-[34px] rounded-lg bg-amber-50 hover:bg-amber-100 border border-amber-300 px-3 py-1 text-xs font-bold text-amber-800 transition cursor-pointer">
                                        Edit
                                    </button>

                                    <form method="POST"
                                          action="{{ route('admin.products.destroy', $product) }}"
                                          class="inline"
                                          onsubmit="return confirm('Hapus produk {{ addslashes($product->name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="min-h-[34px] rounded-lg bg-rose-50 hover:bg-rose-100 border border-rose-200 px-3 py-1 text-xs font-semibold text-rose-700 transition cursor-pointer">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-sm text-stone-500">
                                    Belum ada data produk figura yang sesuai.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Create / Edit Modal --}}
        <div x-show="modalOpen"
             x-cloak
             class="fixed inset-0 z-50 flex items-start sm:items-center justify-center bg-stone-950/60 backdrop-blur-xs p-4 overflow-y-auto">
            <div class="my-auto w-full max-w-md rounded-2xl bg-white shadow-2xl overflow-hidden border border-stone-200" @click.away="modalOpen = false">
                <div class="bg-gradient-to-r from-stone-900 via-stone-800 to-amber-900 px-6 py-4 text-white flex items-center justify-between">
                    <h2 class="text-base font-bold" x-text="isEditing ? 'Edit Produk Figura' : 'Tambah Figura Baru'"></h2>
                    <button type="button"
                            @click="modalOpen = false"
                            class="h-9 w-9 inline-flex items-center justify-center rounded-lg text-amber-200 hover:text-white hover:bg-white/10 text-xl leading-none cursor-pointer">&times;</button>
                </div>

                <form method="POST" :action="formAction" class="p-6 space-y-4">
                    @csrf
                    <template x-if="isEditing">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div>
                        <label class="block text-xs font-semibold text-stone-700 mb-1">Nama Figura</label>
                        <input type="text"
                               name="name"
                               x-model="form.name"
                               required
                               placeholder="Contoh: Figura Kayu Jati Minimalis"
                               class="w-full min-h-[44px] rounded-xl border border-stone-200 bg-stone-50 px-3.5 py-2.5 text-sm focus:border-amber-600 focus:bg-white focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-stone-700 mb-1">Ukuran</label>
                        <input type="text"
                               name="size"
                               x-model="form.size"
                               required
                               placeholder="Contoh: 10R (25x30 cm)"
                               class="w-full min-h-[44px] rounded-xl border border-stone-200 bg-stone-50 px-3.5 py-2.5 text-sm focus:border-amber-600 focus:bg-white focus:outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-stone-700 mb-1">Harga Umum (Rp)</label>
                            <input type="number"
                                   name="price_umum"
                                   min="0"
                                   step="500"
                                   x-model="form.price_umum"
                                   required
                                   class="w-full min-h-[44px] rounded-xl border border-stone-200 bg-stone-50 px-3.5 py-2.5 text-sm focus:border-amber-600 focus:bg-white focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-stone-700 mb-1">Harga Member (Rp)</label>
                            <input type="number"
                                   name="price_member"
                                   min="0"
                                   step="500"
                                   x-model="form.price_member"
                                   required
                                   class="w-full min-h-[44px] rounded-xl border border-stone-200 bg-stone-50 px-3.5 py-2.5 text-sm focus:border-amber-600 focus:bg-white focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-stone-700 mb-1">Jumlah Stok (Unit)</label>
                        <input type="number"
                               name="stock"
                               min="0"
                               x-model="form.stock"
                               required
                               class="w-full min-h-[44px] rounded-xl border border-stone-200 bg-stone-50 px-3.5 py-2.5 text-sm focus:border-amber-600 focus:bg-white focus:outline-none">
                    </div>

                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button"
                                @click="modalOpen = false"
                                class="min-h-[44px] rounded-xl border border-stone-200 px-4 py-2 text-sm font-semibold text-stone-600 hover:bg-stone-50 cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                class="min-h-[44px] rounded-xl bg-gradient-to-r from-amber-700 to-orange-600 hover:from-amber-800 hover:to-orange-700 px-5 py-2 text-sm font-bold text-white shadow-md cursor-pointer">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
    function productManager(initialProducts) {
        return {
            products: initialProducts || [],
            modalOpen: false,
            isEditing: false,
            formAction: '{{ route("admin.products.store") }}',
            form: {
                name: '',
                size: '',
                price_umum: '',
                price_member: '',
                stock: '',
            },

            openCreate() {
                this.isEditing = false;
                this.formAction = '{{ route("admin.products.store") }}';
                this.form = { name: '', size: '', price_umum: '', price_member: '', stock: 10 };
                this.modalOpen = true;
            },

            openEditById(productId) {
                const product = this.products.find(p => Number(p.id) === Number(productId));
                if (!product) return;

                this.isEditing = true;
                this.formAction = '{{ url("/admin/products") }}/' + product.id;
                this.form = {
                    name: product.name,
                    size: product.size,
                    price_umum: Math.round(Number(product.price_umum ?? product.price)),
                    price_member: Math.round(Number(product.price_member ?? product.price_sales ?? product.price)),
                    stock: product.stock,
                };
                this.modalOpen = true;
            },
        };
    }
</script>
@endsection
