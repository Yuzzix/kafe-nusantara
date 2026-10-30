<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();

            // RESTRICT: a category that still has menus cannot be hard-deleted.
            // (We use soft deletes, so normal "delete" in the app never hits this.)
            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('name', 150);
            $table->string('slug', 170)->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('price');                  // whole Rupiah, e.g. 25000
            $table->string('image')->nullable();               // path in storage/app/public
            $table->boolean('is_available')->default(true);    // false = sold out today
            $table->boolean('is_featured')->default(false);    // "Best Seller" badge
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'is_available']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
