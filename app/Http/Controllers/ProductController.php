<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display list of frame products with optional search query.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $products = Product::query()
            ->when($search !== '', function ($query) use ($search) {
                $pattern = '%' . mb_strtolower($search) . '%';
                $query->where(function ($subQuery) use ($pattern) {
                    $subQuery->whereRaw('LOWER(name) LIKE ?', [$pattern])
                        ->orWhereRaw('LOWER(size) LIKE ?', [$pattern]);
                });
            })
            ->orderBy('name')
            ->get();

        return view('admin.products.index', [
            'products' => $products,
            'search' => $search,
        ]);
    }

    /**
     * Store a newly created frame product.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProduct($request);

        Product::create($validated);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk figura berhasil ditambahkan.');
    }

    /**
     * Update an existing frame product.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validateProduct($request);

        $product->update($validated);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Data produk figura berhasil diperbarui.');
    }

    /**
     * Soft-delete a frame product so historical transactions remain intact.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk figura berhasil dihapus.');
    }

    /**
     * Validate product payload consistently for create and update actions.
     *
     * @return array<string, mixed>
     */
    private function validateProduct(Request $request): array
    {
        $fallbackPrice = $request->input('price');

        $request->merge([
            'price_umum' => $request->input('price_umum', $fallbackPrice),
            'price_member' => $request->input('price_member', $request->input('price_sales', $fallbackPrice)),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'size' => ['required', 'string', 'max:50'],
            'price_umum' => ['required', 'numeric', 'min:0'],
            'price_member' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
        ], [
            'name.required' => 'Nama figura wajib diisi.',
            'size.required' => 'Ukuran figura wajib diisi.',
            'price_umum.required' => 'Harga umum figura wajib diisi.',
            'price_member.required' => 'Harga member figura wajib diisi.',
            'stock.required' => 'Stok figura wajib diisi.',
        ]);

        $validated['price'] = $validated['price_umum'];

        return $validated;
    }
}
