<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->enum('method', ['cash', 'qris', 'debit_card', 'credit_card', 'e_wallet']);
            $table->unsignedBigInteger('amount');                      // amount applied to the bill
            $table->unsignedBigInteger('received_amount')->nullable(); // cash handed over by customer
            $table->unsignedBigInteger('change_amount')->default(0);   // kembalian
            $table->string('reference_number', 100)->nullable();       // QRIS / card / e-wallet ref
            $table->timestamp('paid_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
