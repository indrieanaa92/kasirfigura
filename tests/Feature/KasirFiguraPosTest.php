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
            'price_member' => 40000,
            'price_umum' => 50000,
            'stock' => 5,
        ]);
        $outOfStock = Product::create([
            'name' => 'Figura Kosong Test',
            'size' => '4R',
            'price' => 30000,
            'price_member' => 24000,
            'price_umum' => 30000,
            'stock' => 0,
        ]);

        // Register a Member customer
        $memberCustomer = \App\Models\Customer::create([
            'name' => 'Budi Santoso',
            'phone' => '08123456789',
            'is_member' => true,
        ]);

        $this->actingAs($kasir)
            ->get(route('pos.index'))
            ->assertOk()
            ->assertSee($available->name)
            ->assertSee('Terminal Transaksi Kasir');

        // Missing customer_name fails validation
        $this->actingAs($kasir)
            ->postJson(route('pos.checkout'), [
                'payment_method' => Transaction::PAYMENT_CASH,
                'paid_amount' => 100000,
                'items' => [
                    ['product_id' => $available->id, 'quantity' => 1],
                ],
            ])
            ->assertStatus(422);

        // Insufficient stock fails without creating transaction
        $this->actingAs($kasir)
            ->postJson(route('pos.checkout'), [
                'customer_name' => 'Budi Santoso',
                'payment_method' => Transaction::PAYMENT_CASH,
                'paid_amount' => 300000,
                'items' => [
                    ['product_id' => $available->id, 'quantity' => 6],
                ],
            ])
            ->assertStatus(422);

        $this->assertDatabaseCount('transactions', 0);
        $this->assertSame(5, $available->fresh()->stock);

        // Valid cash checkout as Member uses price_member (40.000 x 2 = 80.000), creates change, and decrements stock
        $this->actingAs($kasir)
            ->postJson(route('pos.checkout'), [
                'customer_name' => 'Budi Santoso',
                'payment_method' => Transaction::PAYMENT_CASH,
                'paid_amount' => 100000,
                'items' => [
                    ['product_id' => $available->id, 'quantity' => 2],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('transaction.customer_name', 'Budi Santoso')
            ->assertJsonPath('transaction.customer_type', Transaction::CUSTOMER_MEMBER)
            ->assertJsonPath('transaction.customer_type_label', 'Member')
            ->assertJsonPath('transaction.total_amount', '80000.00')
            ->assertJsonPath('transaction.paid_amount', '100000.00')
            ->assertJsonPath('transaction.change_amount', '20000.00')
            ->assertJsonPath('transaction.payment_method', 'cash')
            ->assertJsonPath('transaction.details.0.unit_price', '40000.00');

        // Valid cash checkout as non-member (Umum) uses price_umum (50.000 x 1 = 50.000)
        $this->actingAs($kasir)
            ->postJson(route('pos.checkout'), [
                'customer_name' => 'Pembeli Baru Umum',
                'payment_method' => Transaction::PAYMENT_CASH,
                'paid_amount' => 50000,
                'items' => [
                    ['product_id' => $available->id, 'quantity' => 1],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('transaction.customer_type', Transaction::CUSTOMER_UMUM)
            ->assertJsonPath('transaction.customer_type_label', 'Umum')
            ->assertJsonPath('transaction.total_amount', '50000.00')
            ->assertJsonPath('transaction.details.0.unit_price', '50000.00');

        $this->assertDatabaseCount('transactions', 2);
        $this->assertSame(2, $available->fresh()->stock);
    }

    public function test_admin_can_crud_products_and_filter_monthly_reports(): void
    {
        $admin = User::factory()->admin()->create();
        $kasir = User::factory()->kasir()->create();

        // Admin manages customer member status
        $this->actingAs($admin)
            ->post(route('admin.customers.store'), [
                'name' => 'Dewi Pelanggan',
                'phone' => '0877112233',
                'is_member' => 0,
            ])
            ->assertRedirect(route('admin.customers.index'));

        $customer = \App\Models\Customer::where('name', 'Dewi Pelanggan')->firstOrFail();
        $this->assertFalse($customer->is_member);

        // Admin toggles to Member
        $this->actingAs($admin)
            ->patch(route('admin.customers.toggle-member', $customer))
            ->assertRedirect(route('admin.customers.index'));
        $this->assertTrue($customer->fresh()->is_member);

        // Create product with tiered prices
        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'name' => 'Figura Klasik Emas',
                'size' => '10R',
                'price_member' => 95000,
                'price_umum' => 120000,
                'stock' => 15,
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::where('name', 'Figura Klasik Emas')->firstOrFail();

        // Update product with tiered prices
        $this->actingAs($admin)
            ->put(route('admin.products.update', $product), [
                'name' => 'Figura Klasik Emas Revisi',
                'size' => '12R',
                'price_member' => 105000,
                'price_umum' => 135000,
                'stock' => 12,
            ])
            ->assertRedirect(route('admin.products.index'));

        $freshProduct = $product->fresh();
        $this->assertSame('12R', $freshProduct->size);
        $this->assertEquals(105000, (float) $freshProduct->price_member);
        $this->assertEquals(135000, (float) $freshProduct->price_umum);
        $this->assertEquals(135000, (float) $freshProduct->price);

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
        $this->assertSame(270000.0, $report['dailyRevenue']);
        $this->assertSame(270000.0, $report['weeklyRevenue']);
        $this->assertSame(270000.0, $report['filteredRevenue']);
        $this->assertCount(1, $report['dailyBreakdown']);
        $this->assertGreaterThanOrEqual(4, $report['weeklyBreakdown']->count());
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

