@extends('layouts.app')

@section('title', 'Kelola Member & Pelanggan - Inul Wijaya Pigura')

@section('content')
<div x-data="{ sidebarOpen: false }" class="min-h-screen flex flex-col lg:flex-row bg-stone-50">
    @include('layouts.partials.admin-sidebar')

    <main x-data="customerManager(@js($customers))" class="flex-1 min-w-0 p-4 sm:p-6 lg:p-8 space-y-6">
        {{-- Header & Stats --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-stone-900">Kelola Member &amp; Pelanggan</h1>
                <p class="text-sm text-stone-500">Admin menentukan pelanggan yang berhak mendapatkan harga Member.</p>
            </div>

            <button type="button"
                    @click="openCreate()"
                    class="w-full sm:w-auto min-h-[44px] inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-700 via-amber-600 to-orange-600 hover:from-amber-800 hover:via-amber-700 hover:to-orange-700 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-amber-700/20 transition cursor-pointer">
                <span>+ Tambah Pelanggan / Member</span>
            </button>
        </div>

        {{-- Stat Summary Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="rounded-2xl bg-white border border-stone-200/80 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Total Pelanggan Terdata</p>
                <p class="mt-2 text-2xl font-extrabold text-stone-900">{{ $totalCount }} Orang</p>
                <p class="mt-1 text-xs text-stone-500">Seluruh pembeli yang tercatat</p>
            </div>

            <div class="rounded-2xl bg-gradient-to-br from-amber-700 via-amber-600 to-orange-600 p-5 text-white shadow-lg shadow-amber-700/20">
                <p class="text-xs font-semibold uppercase tracking-wider text-amber-100">Member Aktif (Diskon)</p>
                <p class="mt-2 text-2xl font-extrabold">{{ $memberCount }} Member</p>
                <p class="mt-1 text-xs text-amber-100/90">Mendapatkan Harga Member di kasir</p>
            </div>

            <div class="rounded-2xl bg-white border border-stone-200/80 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Pelanggan Umum</p>
                <p class="mt-2 text-2xl font-extrabold text-stone-700">{{ $umumCount }} Orang</p>
                <p class="mt-1 text-xs text-stone-500">Mendapatkan Harga Umum standar</p>
            </div>
        </div>

        {{-- Filter & Search Toolbar --}}
        <div class="rounded-2xl bg-white border border-stone-200/80 p-4 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
            {{-- Filter Tabs --}}
            <div class="flex items-center gap-1.5 p-1 rounded-xl bg-stone-100 w-full sm:w-auto">
                <a href="{{ route('admin.customers.index', ['filter' => 'all', 'search' => $search]) }}"
                   class="flex-1 sm:flex-initial text-center px-4 py-2 min-h-[38px] rounded-lg text-xs font-bold transition {{ $filter === 'all' ? 'bg-white text-stone-900 shadow-sm' : 'text-stone-600 hover:text-stone-900' }}">
                    Semua ({{ $totalCount }})
                </a>
                <a href="{{ route('admin.customers.index', ['filter' => 'member', 'search' => $search]) }}"
                   class="flex-1 sm:flex-initial text-center px-4 py-2 min-h-[38px] rounded-lg text-xs font-bold transition {{ $filter === 'member' ? 'bg-amber-600 text-white shadow-sm' : 'text-stone-600 hover:text-stone-900' }}">
                    ⭐ Member ({{ $memberCount }})
                </a>
                <a href="{{ route('admin.customers.index', ['filter' => 'umum', 'search' => $search]) }}"
                   class="flex-1 sm:flex-initial text-center px-4 py-2 min-h-[38px] rounded-lg text-xs font-bold transition {{ $filter === 'umum' ? 'bg-stone-800 text-white shadow-sm' : 'text-stone-600 hover:text-stone-900' }}">
                    Umum ({{ $umumCount }})
                </a>
            </div>

            {{-- Search Bar --}}
            <form method="GET" action="{{ route('admin.customers.index') }}" class="w-full sm:w-80">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <div class="relative">
                    <svg class="h-4 w-4 text-stone-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Cari nama atau nomor telepon..."
                           class="w-full min-h-[44px] rounded-xl border border-stone-200 bg-stone-50 pl-10 pr-4 py-2 text-sm focus:border-amber-600 focus:bg-white focus:outline-none">
                </div>
            </form>
        </div>

        {{-- Mobile Cards (< md) --}}
        <div class="grid grid-cols-1 gap-3 md:hidden">
            @forelse ($customers as $customer)
                <div class="rounded-2xl bg-white border border-stone-200/80 p-4 shadow-sm space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h3 class="text-base font-bold text-stone-900">{{ $customer->name }}</h3>
                            <p class="text-xs text-stone-500 mt-0.5">{{ $customer->phone ?: 'Tidak ada no. telp' }}</p>
                        </div>
                        @if ($customer->is_member)
                            <span class="inline-flex items-center gap-1 rounded-full bg-gradient-to-r from-amber-500 to-orange-500 px-3 py-1 text-xs font-bold text-white shadow-xs shrink-0">
                                ⭐ Member
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold text-stone-700 border border-stone-200 shrink-0">
                                👤 Umum
                            </span>
                        @endif
                    </div>

                    @if ($customer->notes)
                        <p class="text-xs text-stone-600 bg-stone-50 rounded-xl p-2.5 border border-stone-100 italic">
                            "{{ $customer->notes }}"
                        </p>
                    @endif

                    <div class="flex flex-wrap items-center justify-end gap-2 pt-2 border-t border-stone-100">
                        <form method="POST" action="{{ route('admin.customers.toggle-member', $customer) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    class="min-h-[38px] rounded-xl px-3 py-1.5 text-xs font-bold transition cursor-pointer {{ $customer->is_member ? 'bg-stone-100 hover:bg-stone-200 text-stone-700' : 'bg-amber-50 hover:bg-amber-100 border border-amber-300 text-amber-800' }}">
                                {{ $customer->is_member ? 'Cabut Member' : '⭐ Jadikan Member' }}
                            </button>
                        </form>

                        <button type="button"
                                @click="openEditById({{ $customer->id }})"
                                class="min-h-[38px] rounded-xl bg-stone-100 hover:bg-stone-200 px-3 py-1.5 text-xs font-semibold text-stone-700 transition cursor-pointer">
                            Edit
                        </button>

                        <form method="POST"
                              action="{{ route('admin.customers.destroy', $customer) }}"
                              onsubmit="return confirm('Hapus pelanggan {{ addslashes($customer->name) }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="min-h-[38px] rounded-xl bg-rose-50 hover:bg-rose-100 px-3 py-1.5 text-xs font-semibold text-rose-700 transition cursor-pointer">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl bg-white border border-stone-200/80 p-8 text-center text-sm text-stone-500">
                    Belum ada data pelanggan yang cocok.
                </div>
            @endforelse
        </div>

        {{-- Desktop Table (md+) --}}
        <div class="hidden md:block rounded-2xl bg-white border border-stone-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-amber-50/70 text-xs uppercase text-amber-900 font-semibold">
                        <tr>
                            <th class="px-6 py-3.5">Nama Pelanggan</th>
                            <th class="px-6 py-3.5">No. Telepon</th>
                            <th class="px-6 py-3.5">Status Member (Hak Harga)</th>
                            <th class="px-6 py-3.5">Catatan</th>
                            <th class="px-6 py-3.5 text-right">Aksi &amp; Atur Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($customers as $customer)
                            <tr class="hover:bg-amber-50/30 transition">
                                <td class="px-6 py-4 font-bold text-stone-900">{{ $customer->name }}</td>
                                <td class="px-6 py-4 text-xs font-mono text-stone-600">
                                    {{ $customer->phone ?: '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    @if ($customer->is_member)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-amber-500 to-orange-500 px-3 py-1 text-xs font-extrabold text-white shadow-xs">
                                            <span>⭐</span> MEMBER AKTIF
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold text-stone-700 border border-stone-200">
                                            <span>👤</span> Pelanggan Umum
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs text-stone-500 max-w-xs truncate">
                                    {{ $customer->notes ?: '-' }}
                                </td>
                                <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                    {{-- Toggle Member Button (Admin power) --}}
                                    <form method="POST" action="{{ route('admin.customers.toggle-member', $customer) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="min-h-[36px] rounded-lg px-3 py-1.5 text-xs font-bold transition cursor-pointer {{ $customer->is_member ? 'bg-stone-100 hover:bg-stone-200 text-stone-700 border border-stone-300' : 'bg-amber-50 hover:bg-amber-100 border border-amber-400 text-amber-800' }}">
                                            {{ $customer->is_member ? 'Cabut Member' : '⭐ Jadikan Member' }}
                                        </button>
                                    </form>

                                    <button type="button"
                                            @click="openEditById({{ $customer->id }})"
                                            class="min-h-[36px] rounded-lg bg-stone-100 hover:bg-stone-200 border border-stone-200 px-3 py-1.5 text-xs font-semibold text-stone-700 transition cursor-pointer">
                                        Edit
                                    </button>

                                    <form method="POST"
                                          action="{{ route('admin.customers.destroy', $customer) }}"
                                          class="inline"
                                          onsubmit="return confirm('Hapus pelanggan {{ addslashes($customer->name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="min-h-[36px] rounded-lg bg-rose-50 hover:bg-rose-100 px-3 py-1.5 text-xs font-semibold text-rose-700 transition cursor-pointer">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-stone-500">
                                    Belum ada data pelanggan yang sesuai filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Create / Edit Customer Modal --}}
        <div x-show="modalOpen"
             x-cloak
             class="fixed inset-0 z-50 flex items-start sm:items-center justify-center bg-stone-950/60 backdrop-blur-xs p-4 overflow-y-auto">
            <div class="my-auto w-full max-w-md rounded-2xl bg-white shadow-2xl overflow-hidden border border-stone-200" @click.away="modalOpen = false">
                <div class="bg-gradient-to-r from-stone-900 via-stone-800 to-amber-900 px-6 py-4 text-white flex items-center justify-between">
                    <h2 class="text-base font-bold" x-text="isEditing ? 'Edit Data Pelanggan' : 'Tambah Pelanggan / Member Baru'"></h2>
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
                        <label class="block text-xs font-semibold text-stone-700 mb-1">Nama Pembeli / Pelanggan</label>
                        <input type="text"
                               name="name"
                               x-model="form.name"
                               required
                               placeholder="Contoh: Budi Santoso"
                               class="w-full min-h-[44px] rounded-xl border border-stone-200 bg-stone-50 px-3.5 py-2.5 text-sm focus:border-amber-600 focus:bg-white focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-stone-700 mb-1">Nomor Telepon / WhatsApp (Opsional)</label>
                        <input type="text"
                               name="phone"
                               x-model="form.phone"
                               placeholder="Contoh: 081234567890"
                               class="w-full min-h-[44px] rounded-xl border border-stone-200 bg-stone-50 px-3.5 py-2.5 text-sm focus:border-amber-600 focus:bg-white focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-stone-700 mb-1.5">Jadikan Sebagai Member?</label>
                        <div class="flex items-center gap-3 p-3 rounded-xl border border-amber-200/80 bg-amber-50/50">
                            <input id="is-member-checkbox"
                                   type="checkbox"
                                   name="is_member"
                                   value="1"
                                   x-model="form.is_member"
                                   class="h-5 w-5 rounded-md border-stone-300 text-amber-600 focus:ring-amber-500 cursor-pointer">
                            <label for="is-member-checkbox" class="text-xs text-stone-800 cursor-pointer">
                                <span class="font-bold">Ya, jadikan Member Aktif</span>
                                <span class="block text-[11px] text-stone-500">Pelanggan ini otomatis akan mendapatkan Harga Member saat transaksi kasir.</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-stone-700 mb-1">Catatan Tambahan (Opsional)</label>
                        <textarea name="notes"
                                  x-model="form.notes"
                                  rows="2"
                                  placeholder="Contoh: Pemilik studio foto, langganan figura 10R"
                                  class="w-full rounded-xl border border-stone-200 bg-stone-50 px-3.5 py-2 text-sm focus:border-amber-600 focus:bg-white focus:outline-none"></textarea>
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
    function customerManager(initialCustomers) {
        return {
            customers: initialCustomers || [],
            modalOpen: false,
            isEditing: false,
            formAction: '{{ route("admin.customers.store") }}',
            form: {
                name: '',
                phone: '',
                is_member: false,
                notes: '',
            },

            openCreate() {
                this.isEditing = false;
                this.formAction = '{{ route("admin.customers.store") }}';
                this.form = { name: '', phone: '', is_member: true, notes: '' };
                this.modalOpen = true;
            },

            openEditById(customerId) {
                const customer = this.customers.find(c => Number(c.id) === Number(customerId));
                if (!customer) return;

                this.isEditing = true;
                this.formAction = '{{ url("/admin/customers") }}/' + customer.id;
                this.form = {
                    name: customer.name,
                    phone: customer.phone || '',
                    is_member: Boolean(customer.is_member),
                    notes: customer.notes || '',
                };
                this.modalOpen = true;
            },
        };
    }
</script>
@endsection
