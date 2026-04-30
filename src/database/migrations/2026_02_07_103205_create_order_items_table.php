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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->restrictOnDelete();
            $table->string('item_name')->comment('商品名');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('price_ex_tax')->comment('税抜単価');
            $table->decimal('tax_rate', 5, 4)->comment('税率');
            $table->unsignedInteger('price_tax')->comment('税額');
            $table->unsignedInteger('price_in_tax')->comment('税込単価');
            $table->unsignedInteger('subtotal_ex_tax')->comment('税抜合計');
            $table->unsignedInteger('subtotal_tax')->comment('税額計');
            $table->unsignedInteger('subtotal_in_tax')->comment('税込合計');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
