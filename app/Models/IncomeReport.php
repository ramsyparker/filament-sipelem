<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomeReport extends Model
{
    use HasFactory;

    // Tentukan tabel yang digunakan jika berbeda dengan nama model (optional)
    protected $table = 'income_reports';

    // Tentukan kolom yang dapat diisi
    protected $fillable = [
        'report_type',
        'revenue',
        'booking_count',
        'active_members',
    ];
}
