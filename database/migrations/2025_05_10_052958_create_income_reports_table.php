<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('income_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_type');  // Jenis laporan (harian/bulanan)
            $table->decimal('revenue', 15, 2);  // Pendapatan
            $table->integer('booking_count');  // Jumlah booking
            $table->integer('active_members');  // Jumlah member aktif
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('income_reports');
    }
};
