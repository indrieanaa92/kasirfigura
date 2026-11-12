@extends('layouts.app')

@section('title', 'Manajemen Produk - Inul Wijaya Frame')

@section('content')
<div x-data="{ sidebarOpen: false }" class="min-h-screen flex flex-col lg:flex-row bg-stone-50">
    @include('layouts.partials.admin-sidebar')

    <main x-data="productManager(@js($products))" class="flex-1 min-w-0 p-4 sm:p-6 lg:p-8 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-stone-900">Manajemen Produk Figura</h1>
                <p class="text-sm text-stone-500">Tambah, ubah harga, kelola ukuran, dan pantau ketersediaan stok.</p>
            </div>

            <button type="button"
                    @click="openCreate()"
                    class="w-full sm:w-auto min-h-[44px] inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-700 via-amber-600 to-orange-600 hover:from-amber-800 hover:via-amber-700 hover:to-orange-700 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-amber-700/20 transition cursor-pointer">
                <span>+ Tambah Figura</span>
            </button>
        </div>

        {{-- Search Bar --}}
        <form method="GET" action="{{ route('admin.products.index') }}" class="flex flex-wrap gap-2.5 sm:gap-3">
            <input type="text"
                   name="search"
                   value="{{ $search }}"
                   placeholder="Cari nama atau ukuran figura..."
                   class="w-full sm:w-80 min-h-[44px] rounded-xl border border-stone-200 bg-white px-4 py-2.5 text-sm focus:border-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-600/20">
            <button type="submit"
                    class="min-h-[44px] rounded-xl bg-amber-100 hover:bg-amber-200 px-5 py-2.5 text-sm font-semibold text-amber-900 transition cursor-pointer">
                Cari
            </button>
            @if ($search !== '')
                <a href="{{ route('admin.products.index') }}"
                   class="min-h-[44px] inline-flex items-center rounded-xl border border-stone-200 bg-white px-4 py-2.5 text-sm font-semibold text-stone-600 hover:bg-stone-50">
                    Reset
                </a>
            @endif
        </form>

        {{-- Mobile Card List (< md) --}}
        <div class="grid grid-cols-1 gap-3 md:hidden">
            @forelse ($products as $product)
                <div class="rounded-2xl bg-white border border-stone-200/80 p-4 shadow-sm space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h3 class="text-sm font-bold text-stone-900">{{ $product->name }}</h3>
                            <span class="mt-1 inline-flex rounded-lg bg-amber-50 border border-amber-200/70 px-2 py-0.5 text-xs font-semibold text-amber-800">
                                {{ $product->size }}
                            </span>
                        </div>
                        @if ($product->stock === 0)
                            <span class="inline-flex rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-semibold text-rose-700 border border-rose-200 shrink-0">
                                Habis (0)
                            </span>
                        @elseif ($product->stock <= 5)
                            <span class="inline-flex rounded-full bg-orange-50 px-2.5 py-0.5 text-xs font-semibold text-orange-700 border border-orange-200 shrink-0">
                                Menipis ({{ $product->stock }})
                            </span>
                        @else
                            <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-200 shrink-0">
                                Tersedia ({{ $product->stock }})
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-3 gap-2 rounded-xl bg-stone-50 border border-stone-200/70 p-2.5 text-center text-[11px]">
                        <div>
                            <p class="text-stone-500 font-medium">Sales</p>
                            <p class="font-bold text-stone-800">Rp {{ number_format($product->price_sales ?: $product->price, 0, ',', '.') }}</p>
                        </div>
                        <div class="border-x border-stone-200 px-1">
                            <p class="text-stone-500 font-medium">Agen</p>
                            <p class="font-bold text-stone-800">Rp {{ number_format($product->price_agen ?: $product->price, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-stone-500 font-medium">Umum</p>
                            <p class="font-bold text-amber-700">Rp {{ number_format($product->price_umum ?: $product->price, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-stone-100">
                            <button type="button"
                                    @click="openEditById({{ $product->id }})"
                                    class="min-h-[40px] rounded-xl bg-amber-50 hover:bg-amber-100 border border-amber-200/70 px-4 py-2 text-xs font-semibold text-amber-800 transition cursor-pointer">
                                Edit
                            </button>

                            <form method="POST"
                                  action="{{ route('admin.products.destroy', $product) }}"
                                  onsubmit="return confirm('Hapus produk {{ addslashes($product->name) }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="min-h-[40px] rounded-xl bg-rose-50 hover:bg-rose-100 px-4 py-2 text-xs font-semibold text-rose-700 transition cursor-pointer">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl bg-white border border-stone-200/80 p-8 text-center text-sm text-stone-500">
                    Belum ada data produk figura.
                </div>
            @endforelse
        </div>

        {{-- Tablet & Desktop Products Table (md+) --}}
        <div class="hidden md:block rounded-2xl bg-white border border-stone-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-amber-50/70 text-xs uppercase text-amber-900 font-semibold">
                        <tr>
                            <th class="px-6 py-3.5">Nama Figura</th>
                            <th class="px-6 py-3.5">Ukuran</th>
                            <th class="px-6 py-3.5">Harga (Sales / Agen / Umum)</th>
                            <th class="px-6 py-3.5">Stok</th>
                            <th class="px-6 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($products as $product)
                            <tr class="hover:bg-amber-50/30 transition">
                                <td class="px-6 py-4 font-semibold text-stone-900">{{ $product->name }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-lg bg-amber-50 border border-amber-200/70 px-2.5 py-1 text-xs font-semibold text-amber-800">
                                        {{ $product->size }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs space-y-0.5">
                                    <div><span class="text-stone-500">Sales:</span> <span class="font-bold text-stone-800">Rp {{ number_format($product->price_sales ?: $product->price, 0, ',', '.') }}</span></div>
                                    <div><span class="text-stone-500">Agen:</span> <span class="font-bold text-stone-800">Rp {{ number_format($product->price_agen ?: $product->price, 0, ',', '.') }}</span></div>
                                    <div><span class="text-stone-500">Umum:</span> <span class="font-bold text-amber-700">Rp {{ number_format($product->price_umum ?: $product->price, 0, ',', '.') }}</span></div>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($product->stock === 0)
                                        <span class="inline-flex rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-semibold text-rose-700 border border-rose-200">
                                            Habis (0)
                                        </span>
                                    @elseif ($product->stock <= 5)
                                        <span class="inline-flex rounded-full bg-orange-50 px-2.5 py-0.5 text-xs font-semibold text-orange-700 border border-orange-200">
                                            Menipis ({{ $product->stock }})
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-200">
                                            Tersedia ({{ $product->stock }})
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                    <button type="button"
                                            @click="openEditById({{ $product->id }})"
                                            class="min-h-[36px] rounded-lg bg-amber-50 hover:bg-amber-100 border border-amber-200/70 px-3.5 py-1.5 text-xs font-semibold text-amber-800 transition cursor-pointer">
                                        Edit
                                    </button>

                                    <form method="POST"
                                          action="{{ route('admin.products.destroy', $product) }}"
                                          class="inline"
                                          onsubmit="return confirm('Hapus produk {{ addslashes($product->name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="min-h-[36px] rounded-lg bg-rose-50 hover:bg-rose-100 px-3.5 py-1.5 text-xs font-semibold text-rose-700 transition cursor-pointer">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-stone-500">
                                    Belum ada data produk figura.
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

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-stone-700 mb-1">Harga Sales (Rp)</label>
                            <input type="number"
                                   name="price_sales"
                                   min="0"
                                   step="500"
                                   x-model="form.price_sales"
                                   required
                                   class="w-full min-h-[44px] rounded-xl border border-stone-200 bg-stone-50 px-3.5 py-2.5 text-sm focus:border-amber-600 focus:bg-white focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-stone-700 mb-1">Harga Agen (Rp)</label>
                            <input type="number"
                                   name="price_agen"
                                   min="0"
                                   step="500"
                                   x-model="form.price_agen"
                                   required
                                   class="w-full min-h-[44px] rounded-xl border border-stone-200 bg-stone-50 px-3.5 py-2.5 text-sm focus:border-amber-600 focus:bg-white focus:outline-none">
                        </div>

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
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-stone-700 mb-1">Stok</label>
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
                price_sales: '',
                price_agen: '',
                price_umum: '',
                stock: '',
            },

            openCreate() {
                this.isEditing = false;
                this.formAction = '{{ route("admin.products.store") }}';
                this.form = { name: '', size: '', price_sales: '', price_agen: '', price_umum: '', stock: 10 };
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
                    price_sales: Math.round(Number(product.price_sales ?? product.price)),
                    price_agen: Math.round(Number(product.price_agen ?? product.price)),
                    price_umum: Math.round(Number(product.price_umum ?? product.price)),
                    stock: product.stock,
                };
                this.modalOpen = true;
            },
        };
    }
</script>
@endsection
