<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('checkout_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checkout_request_id')->constrained('checkout_requests');
            $table->foreignId('cart_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('shop_id')->constrained();
            $table->foreignId('item_id')->constrained();
            $table->string('item_name');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('price_ex_tax');
            $table->decimal('tax_rate', 5, 4);
            $table->unsignedInteger('price_tax');
            $table->unsignedInteger('price_in_tax');
            $table->unsignedInteger('subtotal_ex_tax');
            $table->unsignedInteger('subtotal_tax');
            $table->unsignedInteger('subtotal_in_tax');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkout_items');
    }
};
