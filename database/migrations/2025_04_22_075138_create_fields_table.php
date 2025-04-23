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
        Schema::create('fields', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(false)->comment('Nama Lapangan');
            $table->enum('type', ['Sintetis', 'Vynil'])->default('Sintetis')->comment('Tipe Lapangan');
            $table->double('price')->default(0)->comment('Harga (Rp)');
            $table->string('image')->nullable()->comment('Gambar Lapangan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fields');
    }
};
