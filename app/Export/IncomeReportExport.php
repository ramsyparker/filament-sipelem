<?php
namespace App\Export;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromCollection;

class IncomeReportExport implements FromCollection
{
    public function collection()
    {
        return Payment::all();
    }
}
