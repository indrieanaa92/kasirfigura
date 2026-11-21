<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PosController extends Controller
{
    /**
     * Display the Kasir POS dashboard with only in-stock frames (stock > 0).
     */
    public function index(Request $request): View
    {
        $products = Product::available()
            ->orderBy('name')
            ->get(['id', 'name', 'size', 'price', 'price_member', 'price_umum', 'stock']);

        $customers = Customer::orderBy('name')
            ->get(['id', 'name', 'phone', 'is_member']);

        $recentTransactions = Transaction::with(['user:id,name', 'details.product:id,name,size,price,price_member,price_umum'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->take(6)
            ->get();

        return view('pos.index', [
            'products' => $products,
            'customers' => $customers,
            'recentTransactions' => $recentTransactions,
            'paymentMethods' => Transaction::PAYMENT_METHODS,
            'customerTypes' => Transaction::CUSTOMER_TYPES,
        ]);
    }

    /**
     * Process POS checkout atomically: create transaction + details, calculate payment/change, and decrement stock.
     */
    public function checkout(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_type' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'payment_method' => ['nullable', 'string', Rule::in(array_keys(Transaction::PAYMENT_METHODS))],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
        ], [
            'customer_name.required' => 'Nama pembeli wajib diisi.',
            'customer_name.max' => 'Nama pembeli maksimal 100 karakter.',
            'items.required' => 'Daftar produk masih kosong.',
            'items.min' => 'Tambahkan minimal 1 figura sebelum checkout.',
            'items.*.quantity.min' => 'Jumlah pembelian minimal 1 unit.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',
            'paid_amount.min' => 'Nominal pembayaran tidak boleh negatif.',
        ]);

        $paymentMethod = $validated['payment_method'] ?? Transaction::PAYMENT_CASH;
        $customerName = trim((string) $validated['customer_name']);

        // Check if buyer is registered as Member in the customers database (controlled by Admin)
        $existingCustomer = Customer::whereRaw('LOWER(name) = ?', [mb_strtolower($customerName)])->first();

        if ($existingCustomer && $existingCustomer->is_member) {
            $customerType = Transaction::CUSTOMER_MEMBER;
        } else {
            $customerType = Transaction::CUSTOMER_UMUM;

            // Automatically record new customer so Admin can easily manage and grant Member status later
            if (! $existingCustomer) {
                Customer::create([
                    'name' => $customerName,
                    'is_member' => false,
                ]);
            }
        }

        // Merge duplicate product_ids so stock validation checks aggregate requested quantity.
        $requestedQuantities = collect($validated['items'])
            ->groupBy('product_id')
            ->map(fn ($entries) => (int) $entries->sum('quantity'))
            ->sortKeys();

        $transaction = DB::transaction(function () use ($request, $requestedQuantities, $paymentMethod, $customerType, $customerName, $validated): Transaction {
            $products = Product::whereIn('id', $requestedQuantities->keys()->all())
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $totalAmount = 0.0;
            $lineItems = [];

            foreach ($requestedQuantities as $productId => $quantity) {
                $product = $products->get($productId);

                if (! $product) {
                    throw ValidationException::withMessages([
                        'items' => ['Salah satu produk figura sudah tidak tersedia.'],
                    ]);
                }

                if ($product->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => [
                            "Stok {$product->name} ({$product->size}) tidak mencukupi. Tersisa {$product->stock} unit.",
                        ],
                    ]);
                }

                $unitPrice = $product->getPriceForCustomerType($customerType);
                $subtotal = round($unitPrice * $quantity, 2);
                $totalAmount = round($totalAmount + $subtotal, 2);

                $lineItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ];
            }

            if ($paymentMethod === Transaction::PAYMENT_CASH) {
                $rawPaid = $validated['paid_amount'] ?? null;
                $paidAmount = $rawPaid !== null ? round((float) $rawPaid, 2) : $totalAmount;

                if ($paidAmount < $totalAmount) {
                    $formattedTotal = number_format($totalAmount, 0, ',', '.');
                    throw ValidationException::withMessages([
                        'paid_amount' => ["Uang tunai yang diterima kurang dari total tagihan (Rp {$formattedTotal})."],
                    ]);
                }

                $changeAmount = round($paidAmount - $totalAmount, 2);
            } else {
                $paidAmount = $totalAmount;
                $changeAmount = 0.0;
            }

            $transaction = Transaction::create([
                'invoice_number' => $this->generateUniqueInvoiceNumber(),
                'user_id' => $request->user()->id,
                'customer_name' => $customerName,
                'customer_type' => $customerType,
                'total_amount' => $totalAmount,
                'payment_method' => $paymentMethod,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
            ]);

            foreach ($lineItems as $item) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ]);

                $item['product']->decrement('stock', $item['quantity']);
            }

            return $transaction->load(['user:id,name', 'details.product:id,name,size,price,price_member,price_umum']);
        });

        if ($request->expectsJson()) {
            $availableProducts = Product::available()
                ->orderBy('name')
                ->get(['id', 'name', 'size', 'price', 'price_member', 'price_umum', 'stock']);

            $allCustomers = Customer::orderBy('name')
                ->get(['id', 'name', 'phone', 'is_member']);

            return response()->json([
                'message' => 'Transaksi berhasil disimpan.',
                'transaction' => $transaction,
                'products' => $availableProducts,
                'customers' => $allCustomers,
            ], 201);
        }

        return redirect()
            ->route('pos.index')
            ->with('success', "Transaksi {$transaction->invoice_number} berhasil disimpan!");
    }

    /**
     * Generate a unique invoice number for each checkout.
     */
    private function generateUniqueInvoiceNumber(): string
    {
        do {
            $invoice = 'INV-' . now()->format('Ymd-His') . '-' . strtoupper(Str::random(4));
        } while (Transaction::where('invoice_number', $invoice)->exists());

        return $invoice;
    }

    /**
     * Cancel/delete a cashier transaction, restore product stock, and refresh catalog/recents.
     */
    public function destroyTransaction(Request $request, Transaction $transaction): JsonResponse|RedirectResponse
    {
        if ($transaction->user_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            abort(403, 'Anda tidak memiliki hak untuk menghapus transaksi ini.');
        }

        DB::transaction(function () use ($transaction): void {
            foreach ($transaction->details as $detail) {
                if ($detail->product) {
                    $detail->product->increment('stock', $detail->quantity);
                }
            }

            $transaction->delete();
        });

        if ($request->expectsJson()) {
            $availableProducts = Product::available()
                ->orderBy('name')
                ->get(['id', 'name', 'size', 'price', 'price_member', 'price_umum', 'stock']);

            return response()->json([
                'message' => "Transaksi {$transaction->invoice_number} berhasil dihapus dan stok produk dikembalikan.",
                'products' => $availableProducts,
            ]);
        }

        return redirect()
            ->route('pos.index')
            ->with('success', "Transaksi {$transaction->invoice_number} berhasil dihapus dan stok produk dikembalikan.");
    }
}
