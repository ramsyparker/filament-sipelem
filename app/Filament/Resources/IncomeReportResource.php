<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncomeReportResource\Pages;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Table;
use App\Export\IncomeReportExport as ExportsIncomeReportExport;

class IncomeReportResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $singularLabel = 'Pemasukan'; // Change this to your preferred name
    protected static ?string $pluralLabel = 'Pemasukan'; // Change this to your preferred plural name

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Pemasukan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Form schema jika diperlukan
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_id') // Order ID
                    ->label('Order ID')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('amount') // Jumlah pembayaran
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at') // Tanggal pembayaran
                    ->label('Tanggal')
                    ->sortable(),
            ])
            ->filters([
                // Anda bisa menambahkan filter sesuai kebutuhan
            ])
            ->actions([
                //     // ExportAction::make('Export to Excel')
                //     //     ->label('Export CSV')
                //     //     ->action(function () {
                //     //         // Menentukan Exporter untuk Excel
                //     //         return Excel::download(new ExportsIncomeReportExport, 'income_report.xlsx');
                //     //     })
                //     //     ->icon('heroicon-o-printer'),

                //     ExportAction::make('Export to PDF')
                //         ->label('Print PDF')
                //         ->action(function () {
                //             // Ambil data pembayaran
                //             $data = Payment::all();

                //             // Buat objek PDF
                //             $pdf = FacadePdf::loadView('pdf.income-report', ['data' => $data]);

                //             // Kembalikan hasil download PDF
                //             return $pdf->download('income_report.pdf');
                //         })
                //         ->icon('heroicon-o-printer'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Define relationships if needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIncomeReports::route('/'),
            'create' => Pages\CreateIncomeReport::route('/create'),
            'edit' => Pages\EditIncomeReport::route('/{record}/edit'),
        ];
    }
}
