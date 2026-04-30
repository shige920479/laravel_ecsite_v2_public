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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('order_number')->nullable()->unique();
            $table->unsignedInteger('total_ex_tax')->comment('税抜金額');
            $table->unsignedInteger('total_tax')->comment('税額');
            $table->unsignedInteger('total_in_tax')->comment('税込金額');
            $table->tinyInteger('payment_method')->default(0)->comment('0:カード決済, 1:振込, 2:QR決済');
            $table->string('stripe_session_id')->nullable();
            $table->tinyInteger('payment_status')->default(0)->comment('0:未払い, 1:支払済み, 2:キャンセル, 3:返金処理中, 4:返金済み');
            $table->dateTime('ordered_at')->useCurrent()->comment('注文日');
            $table->timestamps();

            $table->index(['user_id', 'ordered_at']);
            $table->index('ordered_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
