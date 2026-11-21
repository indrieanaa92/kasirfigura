<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    /**
     * Display the list of customers/members with search and filter.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $filter = $request->query('filter', 'all');

        $query = Customer::query();

        if ($search !== '') {
            $pattern = '%' . mb_strtolower($search) . '%';
            $query->where(function ($sub) use ($pattern) {
                $sub->whereRaw('LOWER(name) LIKE ?', [$pattern])
                    ->orWhereRaw('LOWER(phone) LIKE ?', [$pattern])
                    ->orWhereRaw('LOWER(notes) LIKE ?', [$pattern]);
            });
        }

        if ($filter === 'member') {
            $query->where('is_member', true);
        } elseif ($filter === 'umum') {
            $query->where('is_member', false);
        }

        $customers = $query->orderBy('name')->get();

        $totalCount = Customer::count();
        $memberCount = Customer::where('is_member', true)->count();
        $umumCount = Customer::where('is_member', false)->count();

        return view('admin.customers.index', [
            'customers' => $customers,
            'search' => $search,
            'filter' => $filter,
            'totalCount' => $totalCount,
            'memberCount' => $memberCount,
            'umumCount' => $umumCount,
        ]);
    }

    /**
     * Store a newly created customer.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:customers,name'],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_member' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Nama pelanggan / pembeli wajib diisi.',
            'name.unique' => 'Nama pelanggan ini sudah terdaftar.',
            'name.max' => 'Nama pelanggan maksimal 100 karakter.',
        ]);

        $validated['is_member'] = $request->boolean('is_member');

        $customer = Customer::create($validated);

        $statusText = $customer->is_member ? 'Member' : 'Pelanggan Umum';

        return redirect()
            ->route('admin.customers.index')
            ->with('success', "Pelanggan {$customer->name} berhasil ditambahkan sebagai {$statusText}.");
    }

    /**
     * Update an existing customer.
     */
    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:customers,name,' . $customer->id],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_member' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Nama pelanggan / pembeli wajib diisi.',
            'name.unique' => 'Nama pelanggan ini sudah digunakan.',
        ]);

        $validated['is_member'] = $request->boolean('is_member');

        $customer->update($validated);

        return redirect()
            ->route('admin.customers.index')
            ->with('success', "Data pelanggan {$customer->name} berhasil diperbarui.");
    }

    /**
     * Toggle member status (Admin decides whether customer is a member or not).
     */
    public function toggleMember(Customer $customer): RedirectResponse
    {
        $customer->update([
            'is_member' => ! $customer->is_member,
        ]);

        $newStatus = $customer->is_member ? 'MEMBER' : 'PELANGGAN UMUM';

        return redirect()
            ->route('admin.customers.index')
            ->with('success', "Status {$customer->name} berhasil diubah menjadi {$newStatus}.");
    }

    /**
     * Delete a customer.
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        $name = $customer->name;
        $customer->delete();

        return redirect()
            ->route('admin.customers.index')
            ->with('success', "Pelanggan {$name} berhasil dihapus.");
    }
}
