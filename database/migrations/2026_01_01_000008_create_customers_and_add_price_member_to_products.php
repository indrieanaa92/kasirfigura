<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100)->unique();
                $table->string('phone', 30)->nullable();
                $table->smallInteger('is_member')->default(0)->index();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('products') && ! Schema::hasColumn('products', 'price_member')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('price_member', 12, 2)->default(0)->after('price');
            });

            DB::table('products')->select('id', 'price', 'price_sales', 'price_umum')->orderBy('id')->each(function (object $product): void {
                $base = (float) ($product->price_umum ?: $product->price);
                $member = (float) ($product->price_sales > 0 ? $product->price_sales : round($base * 0.85));

                DB::table('products')->where('id', $product->id)->update([
                    'price_member' => $member,
                ]);
            });
        }

        // Backfill existing customer names from transactions table into customers
        if (Schema::hasTable('transactions') && Schema::hasTable('customers')) {
            $existingCustomers = DB::table('transactions')
                ->whereNotNull('customer_name')
                ->where('customer_name', '!=', '')
                ->select('customer_name', 'customer_type')
                ->distinct()
                ->get();

            foreach ($existingCustomers as $row) {
                $trimmed = trim((string) $row->customer_name);
                if ($trimmed === '' || $trimmed === '-') {
                    continue;
                }

                $isMember = in_array(strtolower((string) $row->customer_type), ['member', 'sales', 'agen'], true);
                $exists = DB::table('customers')->whereRaw('LOWER(name) = ?', [strtolower($trimmed)])->exists();
                if (! $exists) {
                    DB::table('customers')->insert([
                        'name' => $trimmed,
                        'is_member' => $isMember ? 1 : 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'price_member')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('price_member');
            });
        }

        Schema::dropIfExists('customers');
    }
};
