<?php

namespace App\Filament\Resources\IncomeReportResource\Pages;

use App\Filament\Resources\IncomeReportResource;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class PrintIncomeReport extends Page
{
    protected static string $resource = IncomeReportResource::class;

    protected static string $view = 'filament.resources.income-report-resource.pages.print-income-report';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Filter Laporan Pemasukan')
                    ->description('Pilih periode laporan yang ingin dicetak')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('from_date')
                                    ->label('Dari Tanggal')
                                    ->placeholder('Pilih tanggal awal')
                                    ->helperText('Kosongkan jika menggunakan filter bulan/tahun'),
                                
                                Forms\Components\DatePicker::make('until_date')
                                    ->label('Sampai Tanggal')
                                    ->placeholder('Pilih tanggal akhir')
                                    ->helperText('Kosongkan jika menggunakan filter bulan/tahun')
                                    ->after('from_date'),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('bulan')
                                    ->label('Bulan')
                                    ->placeholder('Pilih bulan')
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
                                    ->helperText('Kosongkan jika menggunakan filter tanggal'),

                                Forms\Components\TextInput::make('tahun')
                                    ->label('Tahun')
                                    ->numeric()
                                    ->placeholder('Masukkan tahun')
                                    ->default(now()->year)
                                    ->helperText('Kosongkan jika menggunakan filter tanggal'),
                            ]),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    public function printReport(): void
    {
        $data = $this->form->getState();
        
        // Validasi input
        $hasDateRange = !empty($data['from_date']) && !empty($data['until_date']);
        $hasMonthYear = !empty($data['bulan']) && !empty($data['tahun']);
        
        if (!$hasDateRange && !$hasMonthYear) {
            Notification::make()
                ->title('Error')
                ->body('Harap pilih filter tanggal atau bulan/tahun')
                ->danger()
                ->send();
            return;
        }

        // Build URL parameters
        $params = [];
        
        if ($hasDateRange) {
            $params['from'] = $data['from_date'];
            $params['until'] = $data['until_date'];
        }
        
        if ($hasMonthYear) {
            $params['bulan'] = $data['bulan'];
            $params['tahun'] = $data['tahun'];
        }

        // Redirect to PDF generation
        $url = route('income-report.print-pdf', $params);
        
        // Redirect directly instead of opening new tab
        $this->redirect($url);
        
        Notification::make()
            ->title('Laporan Sedang Diproses')
            ->body('Laporan PDF akan segera diunduh')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        // Determine the correct route based on current panel
        $currentPanel = request()->segment(1);
        $routePrefix = $currentPanel === 'owner' ? 'filament.owner.resources.income-reports.index' : 'filament.admin.resources.income-reports.index';
        
        return [
            Action::make('back')
                ->label('Kembali')
                ->url(route($routePrefix))
                ->color('gray'),
            Action::make('print')
                ->label('Cetak Laporan')
                ->icon('heroicon-o-printer')
                ->color('warning')
                ->action('printReport')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Cetak Laporan')
                ->modalDescription('Apakah Anda yakin ingin mencetak laporan pemasukan dengan filter yang dipilih?')
                ->modalSubmitActionLabel('Ya, Cetak Sekarang'),
        ];
    }
} 