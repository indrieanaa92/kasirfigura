<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Services\SalesReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KasirFiguraPosTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_separated_login_prevents_cross_role_access(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.test',
            'password' => 'secret123',
        ]);

        $kasir = User::factory()->kasir()->create([
            'email' => 'kasir@example.test',
            'password' => 'secret123',
        ]);

        // Kasir account cannot log in through the Admin tab
        $this->post(route('login.attempt'), [
            'email' => $kasir->email,
            'password' => 'secret123',
            'login_as' => User::ROLE_ADMIN,
        ])->assertSessionHasErrors('email');
        $this->assertGuest();

        // Admin account logs in through Admin tab and cannot open Kasir POS
        $this->post(route('login.attempt'), [
            'email' => $admin->email,
            'password' => 'secret123',
            'login_as' => User::ROLE_ADMIN,
        ])->assertRedirect(route('admin.dashboard'));

        $this->get(route('pos.index'))->assertForbidden();
        $this->post(route('logout'))->assertRedirect(route('login'));

        // Kasir account logs in through Kasir tab and cannot open Admin Dashboard
        $this->post(route('login.attempt'), [
            'email' => $kasir->email,
            'password' => 'secret123',
            'login_as' => User::ROLE_KASIR,
        ])->assertRedirect(route('pos.index'));

        $this->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_admin_dashboard_loads_cleanly_when_no_transactions_exist(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Keuangan');
    }

    public function test_pos_shows_only_in_stock_products_and_checkout_updates_stock_atomically(): void
    {
        $kasir = User::factory()->kasir()->create();
        $available = Product::create([
            'name' => 'Figura Jati Test',
            'size' => '8R',
            'price' => 50000,
            'stock' => 5,
        ]);
        $outOfStock = Product::create([
            'name' => 'Figura Kosong Test',
            'size' => '4R',
            'price' => 30000,
            'stock' => 0,
        ]);

        $this->actingAs($kasir)
            ->get(route('pos.index'))
            ->assertOk()
            ->assertSee($available->name)
            ->assertDontSee($outOfStock->name);

        // Insufficient stock fails without creating transaction
        $this->actingAs($kasir)
            ->postJson(route('pos.checkout'), [
                'items' => [
                    ['product_id' => $available->id, 'quantity' => 6],
                ],
            ])
            ->assertStatus(422);

        $this->assertDatabaseCount('transactions', 0);
        $this->assertSame(5, $available->fresh()->stock);

        // Valid cash checkout with overpayment creates transaction + change and decrements stock
        $this->actingAs($kasir)
            ->postJson(route('pos.checkout'), [
                'payment_method' => Transaction::PAYMENT_CASH,
                'paid_amount' => 150000,
                'items' => [
                    ['product_id' => $available->id, 'quantity' => 2],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('transaction.total_amount', '100000.00')
            ->assertJsonPath('transaction.paid_amount', '150000.00')
            ->assertJsonPath('transaction.change_amount', '50000.00')
            ->assertJsonPath('transaction.payment_method', 'cash');

        // Cash checkout with insufficient tendered amount fails
        $this->actingAs($kasir)
            ->postJson(route('pos.checkout'), [
                'payment_method' => Transaction::PAYMENT_CASH,
                'paid_amount' => 20000,
                'items' => [
                    ['product_id' => $available->id, 'quantity' => 1],
                ],
            ])
            ->assertStatus(422);

        $this->assertDatabaseCount('transactions', 1);
        $this->assertDatabaseCount('transaction_details', 1);
        $this->assertSame(3, $available->fresh()->stock);
    }

    public function test_admin_can_crud_products_and_filter_monthly_reports(): void
    {
        $admin = User::factory()->admin()->create();
        $kasir = User::factory()->kasir()->create();

        // Create product
        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'name' => 'Figura Klasik Emas',
                'size' => '10R',
                'price' => 120000,
                'stock' => 15,
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::where('name', 'Figura Klasik Emas')->firstOrFail();

        // Update product
        $this->actingAs($admin)
            ->put(route('admin.products.update', $product), [
                'name' => 'Figura Klasik Emas Revisi',
                'size' => '12R',
                'price' => 135000,
                'stock' => 12,
            ])
            ->assertRedirect(route('admin.products.index'));

        $this->assertSame('12R', $product->fresh()->size);

        // Create current-month and previous-month transactions
        $currentTrx = Transaction::create([
            'invoice_number' => 'INV-NOW-001',
            'user_id' => $kasir->id,
            'total_amount' => 270000,
        ]);
        TransactionDetail::create([
            'transaction_id' => $currentTrx->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'subtotal' => 270000,
        ]);

        $prevDate = now()->subMonthsNoOverflow(2);
        $oldTrx = new Transaction([
            'invoice_number' => 'INV-OLD-001',
            'user_id' => $kasir->id,
            'total_amount' => 135000,
        ]);
        $oldTrx->created_at = $prevDate;
        $oldTrx->updated_at = $prevDate;
        $oldTrx->save();

        TransactionDetail::create([
            'transaction_id' => $oldTrx->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'subtotal' => 135000,
        ]);

        // Filter dashboard by previous month
        $this->actingAs($admin)
            ->get(route('admin.dashboard', ['month' => $prevDate->format('Y-m')]))
            ->assertOk()
            ->assertSee('INV-OLD-001')
            ->assertDontSee('INV-NOW-001');

        // Verify SalesReportService aggregations & export view rendering
        $report = app(SalesReportService::class)->getMonthlyReport(now()->format('Y-m'));
        $this->assertSame(270000.0, $report['weeklyRevenue']);
        $this->assertSame(270000.0, $report['monthlyRollingRevenue']);
        $this->assertSame(270000.0, $report['filteredRevenue']);
        $this->assertSame(2, (int) $report['topProducts']->first()->total_qty);

        // Soft delete product preserves historical report visibility
        $this->actingAs($admin)
            ->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'));

        $this->assertSoftDeleted('products', ['id' => $product->id]);
        $renderedExport = view('exports.monthly-report', $report)->render();
        $this->assertStringContainsString('INV-NOW-001', $renderedExport);
        $this->assertStringContainsString('Figura Klasik Emas Revisi', $renderedExport);
    }
}

