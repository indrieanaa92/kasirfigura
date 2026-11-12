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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price_sales', 12, 2)->default(0)->after('price');
            $table->decimal('price_agen', 12, 2)->default(0)->after('price_sales');
            $table->decimal('price_umum', 12, 2)->default(0)->after('price_agen');
        });

        DB::table('products')->select('id', 'price')->orderBy('id')->each(function (object $product): void {
            $basePrice = (float) $product->price;

            DB::table('products')
                ->where('id', $product->id)
                ->update([
                    'price_sales' => round($basePrice * 0.80),
                    'price_agen' => round($basePrice * 0.90),
                    'price_umum' => round($basePrice),
                ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['price_sales', 'price_agen', 'price_umum']);
        });
    }
};

