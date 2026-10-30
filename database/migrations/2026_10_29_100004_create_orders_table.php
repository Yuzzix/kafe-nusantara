<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 30)->unique();      // ORD-20261029-0001

            // Staff who took the order. If the staff account is deleted,
            // keep the order (sales history!) and just set this to NULL.
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Only dine-in orders have a table.
            $table->foreignId('dining_table_id')
                ->nullable()
                ->constrained('dining_tables')
                ->nullOnDelete();

            $table->string('customer_name', 100)->nullable();
            $table->enum('order_type', ['dine_in', 'takeaway'])->default('dine_in');
            $table->enum('status', ['pending', 'preparing', 'served', 'completed', 'cancelled'])->default('pending');
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid');

            // Money (whole Rupiah). Stored, not computed on the fly, so
            // historical orders never change if tax rates change later.
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedBigInteger('discount')->default(0);
            $table->unsignedBigInteger('service_charge')->default(0);
            $table->unsignedBigInteger('tax')->default(0);
            $table->unsignedBigInteger('total')->default(0);

            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('created_at');                       // daily / monthly reports
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
