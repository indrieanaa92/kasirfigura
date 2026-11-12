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
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('customer_name', 100)->default('Umum')->after('user_id');
            $table->string('customer_type', 20)->default('umum')->after('customer_name');
        });

        Schema::table('transaction_details', function (Blueprint $table) {
            $table->decimal('unit_price', 12, 2)->default(0)->after('quantity');
        });

        DB::table('transaction_details')
            ->select('id', 'quantity', 'subtotal')
            ->orderBy('id')
            ->each(function (object $detail): void {
                $qty = max(1, (int) $detail->quantity);
                $unitPrice = round(((float) $detail->subtotal) / $qty, 2);

                DB::table('transaction_details')
                    ->where('id', $detail->id)
                    ->update(['unit_price' => $unitPrice]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_details', function (Blueprint $table) {
            $table->dropColumn('unit_price');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['customer_name', 'customer_type']);
        });
    }
};

