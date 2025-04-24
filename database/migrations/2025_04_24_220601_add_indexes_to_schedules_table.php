<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->index(['field_id', 'start_time'], 'schedules_field_time_index');
            $table->index('status', 'schedules_status_index');
        });
    }

    public function down()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex('schedules_field_time_index');
            $table->dropIndex('schedules_status_index');
        });
    }
};