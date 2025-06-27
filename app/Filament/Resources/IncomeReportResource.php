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
use PhpParser\Node\Stmt\Label;
use PHPUnit\Framework\Constraint\IsFalse;

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
                // Filter rentang tanggal
                Tables\Filters\Filter::make('date_range')

                    ->label('Rentang Tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal')
                            ->required(),
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal')
                            ->required(),
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['from'] && $data['until']) {
                            return $query->whereBetween('created_at', [
                                \Carbon\Carbon::parse($data['from'])->startOfDay(),
                                \Carbon\Carbon::parse($data['until'])->endOfDay(),
                            ]);
                        }
                        return $query;
                    }),

                // Filter per bulan & tahun
                Tables\Filters\Filter::make('per_bulan')

                    ->label('Per Bulan')
                    ->form([
                        Forms\Components\Select::make('bulan')
                            ->label('Bulan')
                            ->options([
                                '01' => 'Januari',
                                '02' => 'Februari',
                                '03' => 'Maret',
                                '04' => 'April',
                                '05' => 'Mei',
                                '06' => 'Juni',
                                '07' => 'Juli',
                                '08' => 'Agustus',
                                '09' => 'September',
                                '10' => 'Oktober',
                                '11' => 'November',
                                '12' => 'Desember',
                            ])
                            ->default(now()->format('m'))
                            ->required(),

                        Forms\Components\TextInput::make('tahun')

                            ->label('Tahun')
                            ->numeric()
                            ->default(now()->year)
                            ->required(),
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['bulan'] && $data['tahun']) {
                            $start = \Carbon\Carbon::createFromDate($data['tahun'], $data['bulan'], 1)->startOfMonth();
                            $end = $start->copy()->endOfMonth();
                            return $query->whereBetween('created_at', [$start, $end]);
                        }
                        return $query;
                    }),
            ])
            ->filtersTriggerAction(
                fn() =>
                Tables\Actions\Action::make('filter')
                    ->label('Filter')
                    ->icon('heroicon-o-funnel')
            )
            
            ->actions([
                Tables\Actions\Action::make('export_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-printer')
                    ->url(fn ($record) => route('income-report.print-pdf', ['ids' => $record->id]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                //     Tables\Actions\Action::make('export_pdf')
                //         ->label('Export PDF')
                //         ->icon('heroicon-o-printer')
                //         ->action(function ($records) {
                //             $paymentIds = $records->pluck('id')->toArray();
                //             $url = route('income-report.print-pdf', [
                //                 'ids' => implode(',', $paymentIds),
                //             ]);
                //             return redirect($url);
                //         }),
                // ]),
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
