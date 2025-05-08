<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Relasi dengan tabel users
            $table->string('order_id')->unique(); // Order ID untuk transaksi
            $table->decimal('amount', 15, 2); // Jumlah pembayaran
            $table->string('status'); // Status pembayaran (pending, success, failed)
            $table->string('payment_method')->nullable(); // Metode pembayaran (misalnya: credit_card, bank_transfer)
            $table->string('payment_token')->nullable(); // Token pembayaran Midtrans
            $table->timestamps(); // Tanggal pembuatan dan pembaruan
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
