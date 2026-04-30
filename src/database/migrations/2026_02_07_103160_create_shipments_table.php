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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained();
            $table->string('shipping_name');
            $table->string('shipping_postcode');
            $table->string('shipping_address');
            $table->string('shipping_phone')->nullable();
            $table->string('shipping_status')->default('unshipped')->index();
            $table->dateTime('shipped_at')->nullable()->comment('出荷日');
            $table->dateTime('delivered_at')->nullable()->comment('配送完了日');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
