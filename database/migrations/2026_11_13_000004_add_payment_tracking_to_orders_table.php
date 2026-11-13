<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'payment_status')) {
                $table->string('payment_status', 20)->default('unpaid')->after('payment_method');
            }
            if (!Schema::hasColumn('orders', 'payment_gateway')) {
                $table->string('payment_gateway', 30)->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('orders', 'payment_reference')) {
                $table->unsignedBigInteger('payment_reference')->nullable()->unique()->after('payment_gateway');
            }
            if (!Schema::hasColumn('orders', 'payment_transaction_id')) {
                $table->string('payment_transaction_id', 120)->nullable()->after('payment_reference');
            }
            if (!Schema::hasColumn('orders', 'checkout_url')) {
                $table->text('checkout_url')->nullable()->after('payment_transaction_id');
            }
            if (!Schema::hasColumn('orders', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('checkout_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'payment_reference')) {
                try {
                    $table->dropUnique(['payment_reference']);
                } catch (\Throwable $e) {
                    // Index might already be removed on sqlite/custom drivers
                }
            }

            $columns = [
                'paid_at',
                'checkout_url',
                'payment_transaction_id',
                'payment_reference',
                'payment_gateway',
                'payment_status',
            ];

            $existing = array_values(array_filter($columns, fn (string $col) => Schema::hasColumn('orders', $col)));
            if (!empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
};
