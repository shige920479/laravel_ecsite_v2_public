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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders');
            $table->tinyInteger('star')->unsigned()->comment('1～5');
            $table->string('title')->nullable();
            $table->text('review');
            $table->boolean('verified_purchase')->default(false);
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
