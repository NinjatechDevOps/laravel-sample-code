<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index('orders_user_id_foreign');
            $table->unsignedBigInteger('user_address_id')->nullable()->index('orders_user_address_id_foreign');
            $table->unsignedBigInteger('currency_exchange_rate_id')->index('orders_currency_exchange_rate_id_foreign');
            $table->decimal('total_amount')->default(0);
            $table->decimal('rate')->default(1);
            $table->string('ip', 50)->nullable();
            $table->string('device', 50)->nullable();
            $table->text('message')->nullable();
            $table->enum('status', ['quote', 'pending', 'paid', 'failed'])->nullable();
            $table->string('stripe_payment_response', 255)->nullable();
            $table->string('payment_status', 15)->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('view')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
