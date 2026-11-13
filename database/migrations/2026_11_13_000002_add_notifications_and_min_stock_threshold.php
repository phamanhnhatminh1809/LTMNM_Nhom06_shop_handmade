<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'min_stock_threshold')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedInteger('min_stock_threshold')->default(5)->after('stock_quantity');
            });
        }

        if (!Schema::hasTable('admin_notifications')) {
            Schema::create('admin_notifications', function (Blueprint $table) {
                $table->id();
                $table->string('type', 40); // new_order | low_stock
                $table->string('severity', 20)->default('info'); // info | warning | danger
                $table->string('title', 180);
                $table->string('message', 500);
                $table->string('action_url', 255);
                $table->string('reference_type', 60)->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();

                $table->index(['type', 'created_at'], 'admin_notifications_type_created_idx');
                $table->index(['type', 'reference_id', 'resolved_at'], 'admin_notifications_ref_idx');
            });
        }

        if (!Schema::hasTable('admin_notification_reads')) {
            Schema::create('admin_notification_reads', function (Blueprint $table) {
                $table->id();
                $table->foreignId('notification_id')
                    ->constrained('admin_notifications')
                    ->cascadeOnDelete();
                $table->foreignId('user_id')
                    ->constrained('users')
                    ->cascadeOnDelete();
                $table->timestamp('read_at')->useCurrent();

                $table->unique(['notification_id', 'user_id'], 'admin_notification_reads_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_notification_reads');
        Schema::dropIfExists('admin_notifications');

        if (Schema::hasColumn('products', 'min_stock_threshold')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('min_stock_threshold');
            });
        }
    }
};
