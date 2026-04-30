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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_category_id')->constrained();
            $table->string('name')->comment('商品名');
            $table->text('information')->comment('商品情報');
            $table->unsignedInteger('price_ex_tax')->comment('税抜単価');
            $table->unsignedInteger('stock_current')->default(0)->comment('在庫数');
            $table->boolean('is_selling')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['shop_id', 'name']);
            $table->index(['item_category_id', 'price_ex_tax']);
            $table->index('price_ex_tax');
            $table->index(['is_selling', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
