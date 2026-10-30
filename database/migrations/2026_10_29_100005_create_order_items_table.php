<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pivot between orders and menus (many-to-many) with extra data.
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            // Delete an order -> its lines go with it.
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            // A menu that appears in any order can never be hard-deleted
            // (it would break the order history). Use soft delete instead.
            $table->foreignId('menu_id')
                ->constrained('menus')
                ->restrictOnDelete();

            $table->unsignedSmallInteger('quantity')->default(1);
            $table->unsignedInteger('unit_price');             // price SNAPSHOT at order time
            $table->unsignedBigInteger('subtotal');            // quantity * unit_price
            $table->string('notes')->nullable();               // "less sugar", "tidak pedas"
            $table->timestamps();

            $table->index(['order_id', 'menu_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
